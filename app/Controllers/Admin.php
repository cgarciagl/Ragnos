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
        return view('admin/perfil');
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

    public function cambiar_password()
    {
        checkAjaxRequest();
        $this->checkLogin();

        $auth = service('Admin_aut');
        $id   = $auth->id();

        if ($id == 1) {
            returnAsJSON(['result' => 'error', 'errors' => ['password' => 'El superusuario no puede cambiar su contraseña por este medio']]);
        }

        $password = getInputValue('password');

        if (empty($password)) {
            returnAsJSON(['result' => 'error', 'errors' => ['password' => 'La contraseña no puede estar vacía']]);
        }

        $db = db_connect();
        $db->table('gen_usuarios')
            ->where('usu_id', $id)
            ->update(['usu_pword' => Passwords::hash((string) $password)]);

        returnAsJSON(['result' => 'ok']);
    }

}
