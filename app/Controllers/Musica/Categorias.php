<?php

namespace App\Controllers\Musica;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Categorias extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checklogin();
        $this->setTitle('📂 Categorias');
        $this->setTableName('mus_categorias');
        $this->setIdField('CAT_ID');
        $this->addField('CAT_NOMBRE', ['label' => '📂 Categoría', 'rules' => 'required|is_unique']);

        $this->addField(
            'cuantas_canciones',
            [
                'label' => 'Número de canciones 🎶',
                'rules' => 'readonly',
                'query' => "SELECT count(*) from mus_canciones where cat_id = can_categoria",
            ]
        );

        $this->setUseTimestamps(TRUE);
    }
}
