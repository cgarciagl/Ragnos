<?php

namespace App\Controllers\Musica;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Artistas extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checklogin();
        $this->setTitle('🎤 Artistas');
        $this->setTableName('mus_cantantes');
        $this->setIdField('CNT_ID');
        $this->addField('CNT_NOMBRE', ['label' => '🎤 Artista', 'rules' => 'required|is_unique']);

        $this->addField(
            'cuantas_canciones',
            [
                'label' => 'Número de canciones 🎶',
                'rules' => 'readonly',
                'query' => "SELECT count(*) from mus_canciones where cnt_id = can_cantante",
            ]
        );
    }
}
