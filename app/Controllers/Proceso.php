<?php

namespace App\Controllers;

use Ragnos\Controllers\RProcessController;

class Proceso extends RProcessController
{
    public $requireConfirmation = true;
    public $confirmationMessage = '¿Estás completamente seguro de iniciar el recálculo de precios? Esta acción no se puede deshacer.';

    public function __construct()
    {
        parent::__construct();
        // Los procesos modifican datos: solo usuarios con sesión iniciada.
        $this->checkLogin();
    }

    public function start()
    {
        processStart('Recalculando precios');
        $numberOfItems = 100;
        for ($i = 1; $i <= $numberOfItems; $i++) {
            setProgressOf($i, $numberOfItems);
            setProgressText('Procesando producto ' . $i . ' de ' . $numberOfItems);
            usleep(150000);
        }

        endProcess([
            'message'        => 'Proceso completado exitosamente',
            'itemsProcessed' => $numberOfItems
        ]);
    }
}