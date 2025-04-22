<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Gruposdeusuarios extends RDatasetController
{

    public function __construct()
    {
        parent::__construct();

        $this->soloparagrupo('Administrador');

        $this->setTitle('Grupos de Usuarios');

        $this->setTableName('gen_gruposdeusuarios');
        $this->setIdField('gru_id');

        $this->addField('gru_nombre', ['label' => 'Grupo', 'rules' => 'required|is_unique']);
    }

    public function _beforeDelete()
    {
        $id = oldValue('gru_id');
        if ($id == 1) {
            raise('No se puede borrar el grupo de administradores');
        } else {
            $this->checkAssociatedUsers($id);
        }
    }

    private function checkAssociatedUsers($groupId)
    {
        $db        = db_connect();
        $userCount = $db->table('gen_usuarios')->where('usu_grupo', $groupId)->countAllResults();
        if ($userCount > 0) {
            raise('No se puede borrar porque tiene usuarios asociados');
        }
    }

    public function _beforeUpdate(&$a)
    {
        if (oldValue('gru_id') == 1) {
            raise('No se puede modificar el grupo de administradores');
        }
    }
}
