<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Usuarios extends RDatasetController
{

    public function __construct()
    {
        parent::__construct();

        $this->checkUserInGroup('Administrador');

        $this->setTitle('👨🏻‍💻 Usuarios');

        $this->setTableName('gen_usuarios');
        $this->setIdField('usu_id');

        $this->addField('usu_nombre', ['label' => 'Nombre', 'rules' => 'required']);
        $this->addField('usu_login', extraOptions: ['label' => 'Login', 'rules' => 'required|is_unique']);
        $this->addField(
            'usu_pword',
            [
                'label'   => 'Password',
                'rules'   => 'required',
                'type'    => 'password',
                'default' => 'ok',
            ]
        );

        $this->addField(
            'usu_activo',
            [
                'label'   => 'Activo',
                'rules'   => 'required',
                'type'    => 'dropdown',
                'default' => 'N',
                'options' => ['S' => 'SI', 'N' => 'NO'],
            ]
        );

        $this->addField('usu_grupo', ['label' => 'Grupo', 'rules' => 'required']);
        $this->addSearch('usu_grupo', 'Gruposdeusuarios');

        $this->setTableFields(['usu_nombre', 'usu_login', 'usu_grupo', 'usu_activo']);
    }

    public function _beforeDelete()
    {
        $auth = service('Admin_aut');
        $id   = oldValue('usu_id');
        if ($id == 1) {
            raise('No puede borrar al superusuario...');
        } elseif ($id == $auth->id()) {
            raise('No puede borrar su propia cuenta...');
        }
    }

    public function _beforeUpdate(&$userData)
    {
        if (fieldHasChanged('usu_pword')) {
            $userData['usu_pword'] = md5(strtoupper($userData['usu_pword']));
        }
    }

    public function _beforeInsert(&$userData)
    {
        $userData['usu_pword'] = md5(strtoupper($userData['usu_pword']));
    }

}
