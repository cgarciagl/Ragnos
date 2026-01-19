<?php

namespace App\Controllers\Tienda;

use App\ThirdParty\Ragnos\Controllers\RDatasetController;

class Empleados extends RDatasetController
{
    function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        $this->setTitle('Empleados');
        $this->setTableName('employees');
        $this->setIdField('employeeNumber');
        $this->setAutoIncrement(false);

        $this->initializeFields();
        $this->initializeSearch();
        $this->initializeDropdown();
    }

    private function initializeFields()
    {
        $this->addField(
            'nombreCompleto',
            [
                'label' => 'Nombre completo',
                'rules' => 'readonly',
                'query' => "concat(lastName, ', ', firstName)",
                'type'  => 'hidden'
            ]
        );

        $this->addField('employeeNumber', ['label' => 'Número de empleado', 'rules' => 'required|is_unique']);
        $this->addField('lastName', ['label' => 'Apellido', 'rules' => 'required']);
        $this->addField('firstName', ['label' => 'Nombre', 'rules' => 'required']);
        $this->addField('extension', ['label' => 'Extension', 'rules' => '']);
        $this->addField('email', ['label' => 'Email', 'rules' => '', 'type' => 'email']);
        $this->addField('officeCode', ['label' => 'Oficina', 'rules' => 'required', 'placeholder' => 'Selecciona una oficina']);
        $this->addField('jobTitle', ['label' => 'Puesto', 'rules' => 'required']);
        $this->setTableFields(['nombreCompleto', 'employeeNumber', 'officeCode', 'reportsTo']);
    }

    private function initializeSearch()
    {
        $this->addSearch('officeCode', 'Tienda\Oficinas', '', 'pruebaBusquedaOffice');
    }

    private function initializeDropdown()
    {
        $empleados = queryToAssocArray(
            'SELECT employeeNumber, CONCAT(lastName, ", ", firstName) as name FROM employees ORDER BY lastName, firstName',
            'employeeNumber',
            'name'
        );
        $empleados = ['' => 'Nadie'] + $empleados;
        $this->addField(
            'reportsTo',
            [
                'label'   => 'Reporta a',
                'type'    => 'dropdown',
                'options' => $empleados
            ]
        );
    }
}
