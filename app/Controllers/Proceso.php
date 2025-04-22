<?php

namespace App\Controllers;

use App\ThirdParty\Ragnos\Controllers\RProcessController;

class Proceso extends RProcessController
{
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