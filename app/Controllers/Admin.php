<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\BaseController;


class Admin extends BaseController
{
    public function index()
    {
        //cargar el helper utiles
        helper('App\ThirdParty\Ragnos\Helpers\ragnos_helper');
        $this->checklogin();
        $model                        = new \App\Models\Dashboard();
        $data['ventasultimos12meses'] = $model->ventasultimos12meses();
        $data['estadosDeCuenta']      = $model->estadosDeCuenta();
        $data['ventasporlinea']       = $model->ventasporlinea();
        return view('admin/dashboard', $data);
    }

    public function perfil()
    {
        $this->checklogin();
        return view('admin/perfil');
    }

    public function login()
    {
        $validation = \Config\Services::validation();
        helper('form');

        if (!$this->request->is('post')) {
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
                    $usuario  = strtoupper($request->getPost('usuario'));
                    $password = md5(strtoupper($request->getPost('pword')));
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
                        return true;
                    }
                },
            ]
        );

        if (!$validation->withRequest($this->request)->run()) {
            return view('admin/login', [
                'errors' => $validation->getErrors(),
            ]);
        } else {
            $sessionBeforeUri = session('bef_uri');
            if ($sessionBeforeUri) {
                return redirect()->to($sessionBeforeUri);
            } else {
                return redirect()->to('admin/index');
            }
        }
    }

    public function busqueda()
    {
        checkAjaxRequest($this->request);

        $valorabuscar = $this->request->getPost('valorabuscar');
        $ruta         = $this->request->getPost('ruta');
        $params       = $this->request->getPost('params');
        $pparams      = [
            'valorabuscar' => $valorabuscar,
            'ruta'         => $ruta,
            'params'       => $params,
        ];
        return view('admin/busqueda', $pparams);
    }

    private function search($table, $selectFields, $searchFields, $searchTerm, $limit = 10, $offset = 0)
    {
        checkAjaxRequest($this->request);

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
        $searchTerm = $this->request->getPost('searchTerm');
        $limit      = $this->request->getPost('iDisplayLength') ?: 10;
        $offset     = $this->request->getPost('iDisplayStart') ?: 0;

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
        checkAjaxRequest($this->request);

        //devolvemos el arreglo de la sesion como un objeto json
        return $this->response->setStatusCode(200)->setJSON(session()->get());
    }
}
