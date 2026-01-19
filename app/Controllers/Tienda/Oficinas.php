<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Oficinas extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        $this->setTitle('Oficinas');
        $this->setTableName('offices');
        $this->setIdField('officeCode');

        $this->addField(
            'nombreCiudad',
            [
                'label' => 'Nombre ciudad',
                'rules' => 'readonly',
                'query' => "concat(city, ', ', country)",
                'type'  => 'hidden'
            ]
        );

        $this->addField('city', ['label' => 'Ciudad', 'rules' => 'required|is_unique']);
        $this->addField('officeCode', ['label' => 'Código', 'rules' => 'required|is_unique']);
        $this->addField('phone', ['label' => 'Teléfono', 'rules' => 'required']);
        $this->addField('addressline1', ['label' => 'Dirección 1', 'rules' => 'required']);
        $this->addField('addressline2', ['label' => 'Dirección 2', 'rules' => '']);
        $this->addField('state', ['label' => 'Estado', 'rules' => '']);
        $this->addField('country', ['label' => 'País', 'rules' => 'required']);
        $this->addField('postalcode', ['label' => 'Código postal', 'rules' => 'required']);
        $this->addField('territory', ['label' => 'Territorio', 'rules' => 'required']);

        $this->setTableFields(['nombreCiudad', 'state', 'territory']);
    }
}
