<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RQueryController;

class Searchusuarios extends RQueryController
{
    public function __construct()
    {
        parent::__construct();
        $this->checkUserInGroup('Administrador');
        $this->setTitle('🔎 Usuarios');
        $this->setQuery("SELECT usu_id, usu_nombre as 'Nombre', usu_login as 'Login', usu_activo as Activo, usu_grupo FROM gen_usuarios");
        $this->setIdField('usu_id');
        $this->addField('Activo', [
            'label'   => 'Activo',
            'type'    => 'dropdown',
            'options' => ['S' => 'SI', 'N' => 'NO'],
        ]);
        $this->addField('usu_grupo', ['label' => 'Grupo']);
        $this->addSearch('usu_grupo', 'Gruposdeusuarios');
        $this->setTableFields(['Nombre', 'Login', 'Activo', 'usu_grupo']);
    }

}