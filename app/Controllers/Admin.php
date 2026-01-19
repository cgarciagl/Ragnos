<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\BaseController;


class Admin extends BaseController
{

    function __construct()
    {
        helper(['form', 'App\ThirdParty\Ragnos\Helpers\ragnos_helper']);
    }

    public function index()
    {
        //cargar el helper utiles
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $this->checkLogin();
        $model                                            = new \App\Models\Dashboard();
        $data['ventasultimos12meses']                     = $model->ventasultimos12meses();
        $data['estadosDeCuenta']                          = $model->estadosDeCuenta();
        $data['ventasporlinea']                           = $model->ventasporlinea();
        $data['empleadosConMasVentasEnElUltimoTrimestre'] = $model->empleadosConMasVentasEnElUltimoTrimestre();
        $data['productosConMenorRotacion']                = $model->productosConMenorRotacion();
        $data['margenDeGananciaPorLinea']                 = $model->margenDeGananciaPorLinea();
        $data['datosinfobox']                             = $model->datosAtomicosDashboard();
        $data['mapa_ventas']                              = $model->ventasPorPais();
        return view('admin/dashboard', $data);
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
                    $usuario  = strtoupper(getInputValue('usuario'));
                    $password = md5(strtoupper(getInputValue('pword')));
                    $db       = db_connect();
                    $query    = $db->table('gen_usuarios')
                        ->select('usu_id, usu_nombre, gru_nombre')
                        ->join('gen_gruposdeusuarios', 'usu_grupo=gru_id', 'inner')
                        ->where(['usu_login' => $usuario, 'usu_pword' => $password, 'usu_activo' => 'S'])
                        ->get();
                    if ($query->getNumRows() == 0) {
                        $error = 'Usuario ó contraseña incorrectos!';
                        return false;
                    } else {
                        $r = $query->getFirstRow('array');
                        $session->set('usu_id', $r['usu_id']);
                        $session->set('usu_nombre', $r['usu_nombre']);
                        $session->set('gru_nombre', $r['gru_nombre']);
                        $token = bin2hex(random_bytes(32)); // Genera un token seguro de 64 caracteres
                        $session->set('usu_token', $token);
                        $db->table('gen_usuarios')->update(['usu_token' => $token], ['usu_id' => $r['usu_id']]);
                        return true;
                    }
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
                    'status'  => 'success',
                    'message' => 'Login successful',
                    'token'   => sessionValue('usu_token'),
                    'user_id' => sessionValue('usu_id')
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

    private function search($table, $selectFields, $searchFields, $searchTerm, $limit = 10, $offset = 0)
    {
        checkAjaxRequest();

        $userList = [];
        $query    = $this->db->table($table)
            ->select($selectFields);

        foreach ($searchFields as $field) {
            $query->orLike($field, $searchTerm);
        }

        $query->limit($limit, $offset);
        $result = $query->get();

        if ($result->getNumRows() > 0) {
            $rows                  = $result->getResult('array');
            $userList['resultado'] = 'SI';
            $userList['datos']     = $rows;
        } else {
            $userList['resultado'] = 'NO';
            $userList['mensaje']   = "No se encontraron registros";
            $userList['datos']     = [];
        }

        return $this->response->setStatusCode(200)->setJSON($userList);
    }

    public function testusuarios()
    {
        $searchTerm = getInputValue('searchTerm');
        $limit      = (int) getInputValue('iDisplayLength') ?: 10;
        $offset     = (int) getInputValue('iDisplayStart') ?: 0;

        return $this->search(
            'gen_usuarios',
            'usu_id, usu_login as Login, usu_nombre as Nombre',
            ['usu_nombre', 'usu_login'],
            $searchTerm,
            $limit,
            $offset
        );
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('admin/login');
    }


    function sess()
    {
        checkAjaxRequest();

        //devolvemos el arreglo de la sesion como un objeto json
        return $this->response->setStatusCode(200)->setJSON(session()->get());
    }
}
