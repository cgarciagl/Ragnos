<?php

namespace App\Controllers;

use App\Libraries\Passwords;
use Ragnos\Controllers\BaseController;


class Admin extends BaseController
{

    function __construct()
    {
        helper(['form', 'Ragnos\Helpers\ragnos_helper']);
    }

    public function index()
    {
        //cargar el helper utiles
        helper('Ragnos\Helpers\ragnos_helper');
        $this->checkLogin();

        // Sin la demo Classicmodels, el panel de la tienda no tiene datos que
        // mostrar: se presenta el panel de inicio genérico del sistema.
        if (! \App\Libraries\DemoData::isInstalled()) {
            $resumen = new \App\Models\SystemSummary();

            return view('admin/inicio', [
                'contadores'      => $resumen->counters(),
                'actividad'       => $resumen->recentActivity(),
                'esAdministrador' => service('Admin_aut')->isUserInGroup('administrador'),
                'aviso'           => session()->getFlashdata('warning'),
            ]);
        }

        $model                                            = new \App\Models\Dashboard();
        $data['ventasultimos12meses']                     = $model->ventasultimos12meses();
        $data['estadosDeCuenta']                          = $model->estadosDeCuenta();
        $data['ventasporlinea']                           = $model->ventasporlinea();
        $data['empleadosConMasVentasEnElUltimoTrimestre'] = $model->empleadosConMasVentasEnElUltimoTrimestre();
        $data['productosConMenorRotacion']                = $model->productosConMenorRotacion();
        $data['margenDeGananciaPorLinea']                 = $model->margenDeGananciaPorLinea();
        $data['datosinfobox']                             = $model->datosAtomicosDashboard();
        return view('admin/dashboard', $data);
    }

    public function ventasPorPais()
    {
        $this->checkLogin();
        $model               = new \App\Models\Dashboard();
        $data['mapa_ventas'] = $model->ventasPorPais();
        return view('admin/ventas_por_pais', $data);
    }

    public function perfil()
    {
        $this->checkLogin();

        $id = (int) service('Admin_aut')->id();
        $db = db_connect();

        $usuario = $db->table('gen_usuarios u')
            ->select('u.usu_id, u.usu_login, u.usu_nombre, u.usu_activo, u.usu_avatar, g.gru_nombre')
            ->join('gen_gruposdeusuarios g', 'g.gru_id = u.usu_grupo', 'left')
            ->where('u.usu_id', $id)
            ->get()->getFirstRow('array') ?? [];

        $actividad = $db->table('gen_audit_logs')
            ->where('user_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $estadisticas = [
            'total'   => $db->table('gen_audit_logs')->where('user_id', $id)->countAllResults(),
            'mes'     => $db->table('gen_audit_logs')->where('user_id', $id)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-30 days')))->countAllResults(),
            'ultimo'  => $actividad[0]['created_at'] ?? null,
        ];

        return view('admin/perfil', [
            'usuario'      => $usuario,
            'actividad'    => $actividad,
            'estadisticas' => $estadisticas,
        ]);
    }

    public function login()
    {
        $validation = \Config\Services::validation();
        $token      = '';

        if (!$this->request->is('post') && !isApiCall()) {
            return view('admin/login', [
                'errors' => [],
            ]);
        }

        $validation->setRule('usuario', 'Usuario', 'required');
        $validation->setRule(
            'pword',
            'Contraseña',
            [
                'required',
                static function ($value, $data, &$error, $field) {
                    $request  = request();
                    $session  = session();
                    $usuario  = strtoupper((string) getInputValue('usuario'));
                    $password = (string) getInputValue('pword');
                    $db       = db_connect();
                    $query    = $db->table('gen_usuarios')
                        ->select('usu_id, usu_nombre, usu_pword, gru_nombre')
                        ->join('gen_gruposdeusuarios', 'usu_grupo=gru_id', 'inner')
                        ->where(['usu_login' => $usuario, 'usu_activo' => 'S'])
                        ->get();
                    $cuenta = $query->getFirstRow('array');

                    if ($cuenta === null || ! Passwords::verify($password, (string) $cuenta['usu_pword'])) {
                        $error = 'Usuario ó contraseña incorrectos!';
                        return false;
                    }

                    // Migra en silencio los hashes antiguos (md5) a password_hash().
                    if (Passwords::needsRehash((string) $cuenta['usu_pword']) && Passwords::storageSupportsModernHashes()) {
                        $db->table('gen_usuarios')->update(['usu_pword' => Passwords::hash($password)], ['usu_id' => $cuenta['usu_id']]);
                    }

                    // Nuevo identificador de sesión al autenticarse (evita la fijación de sesión).
                    $session->regenerate();
                    $session->set('usu_id', $cuenta['usu_id']);
                    $session->set('usu_nombre', $cuenta['usu_nombre']);
                    $session->set('gru_nombre', $cuenta['gru_nombre']);
                    $token = bin2hex(random_bytes(32)); // Genera un token seguro de 64 caracteres
                    $session->set('usu_token', $token);
                    $db->table('gen_usuarios')->update(['usu_token' => $token], ['usu_id' => $cuenta['usu_id']]);

                    return true;
                },
            ]
        );

        if (!$validation->withRequest($this->request)->run()) {

            if (isApiCall()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Validation failed',
                    'errors'  => $validation->getErrors(),
                ]);
            }

            return view('admin/login', [
                'errors' => $validation->getErrors(),
            ]);
        } else {
            if (isApiCall()) {
                return $this->response->setStatusCode(200)->setJSON([
                    'status'     => 'success',
                    'message'    => 'Login successful',
                    'token'      => sessionValue('usu_token'),
                    'user_id'    => sessionValue('usu_id'),
                    'user_name'  => sessionValue('usu_nombre'),
                    'user_group' => sessionValue('gru_nombre'),
                ]);
            }

            $defaultRoute     = 'admin/index';
            $sessionBeforeUri = sessionValue('bef_uri', $defaultRoute);
            $restrictedPaths  = ['getFormData', 'getAjaxGridData'];

            foreach ($restrictedPaths as $path) {
                if (strpos($sessionBeforeUri, $path) !== false) {
                    $sessionBeforeUri = $defaultRoute;
                    break;
                }
            }

            return redirect()->to($sessionBeforeUri);
        }
    }

    public function busqueda()
    {
        checkAjaxRequest();
        $this->checkLogin();

        $valorabuscar = getInputValue('valorabuscar');
        $ruta         = getInputValue('ruta');
        $params       = getInputValue('params');
        $pparams      = [
            'valorabuscar' => $valorabuscar,
            'ruta'         => $ruta,
            'params'       => $params,
        ];
        return view('admin/busqueda', $pparams);
    }

    public function logout()
    {
        service('Admin_aut')->logout();
        return redirect()->to('admin/login');
    }


    /**
     * Datos de la sesión actual para Ragnos.Http.getSession() (utiles.js).
     * Requiere una sesión iniciada.
     */
    public function sess()
    {
        checkAjaxRequest();
        $this->checkLogin();

        return $this->response->setStatusCode(200)->setJSON(session()->get());
    }

    /**
     * Cambio de la contraseña propia. Exige la contraseña actual; aplica
     * también al superusuario (id 1), que no puede editarse desde Usuarios.
     */
    public function cambiar_password()
    {
        checkAjaxRequest();
        $this->checkLogin();

        $id     = service('Admin_aut')->id();
        $actual = (string) getInputValue('actual');
        $nueva  = (string) getInputValue('password');

        if (mb_strlen($nueva) < 8) {
            returnAsJSON(['result' => 'error', 'errors' => ['password' => 'La nueva contraseña debe tener al menos 8 caracteres']]);
        }

        $db     = db_connect();
        $cuenta = $db->table('gen_usuarios')->select('usu_pword')->where('usu_id', $id)->get()->getFirstRow('array');

        if ($cuenta === null || ! Passwords::verify($actual, (string) $cuenta['usu_pword'])) {
            returnAsJSON(['result' => 'error', 'errors' => ['actual' => 'La contraseña actual no es correcta']]);
        }

        if (! Passwords::storageSupportsModernHashes()) {
            returnAsJSON(['result' => 'error', 'errors' => ['password' => 'Ejecuta "php spark migrate" para habilitar el cambio de contraseña']]);
        }

        $db->table('gen_usuarios')
            ->where('usu_id', $id)
            ->update(['usu_pword' => Passwords::hash($nueva)]);

        returnAsJSON(['result' => 'ok']);
    }

}
