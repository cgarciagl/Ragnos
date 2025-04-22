<?php

namespace App\Controllers\Musica;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Canciones extends RDatasetController
{
    function __construct()
    {
        parent::__construct();

        $this->checklogin();

        $this->setTitle('🎶 Canciones');
        $this->setTableName('mus_canciones');
        $this->setIdField('CAN_ID');
        $this->addField('CAN_TITULO', ['label' => '🎵 Canción', 'rules' => 'required']);

        $this->addField('CAN_CANTANTE', ['label' => '🎤 Artista']);
        $this->addSearch('CAN_CANTANTE', 'Musica\Artistas');

        $this->addField('CAN_CATEGORIA', ['label' => '📂 Categoría']);
        $this->addSearch('CAN_CATEGORIA', 'Musica\Categorias');
    }
}
