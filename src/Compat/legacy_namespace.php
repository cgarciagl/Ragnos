<?php

/**
 * Compatibilidad con el namespace anterior de Ragnos.
 *
 * Hasta la versión 1.x el núcleo vivía en app/ThirdParty/Ragnos con el
 * namespace App\ThirdParty\Ragnos. Ahora vive en src/ con el namespace Ragnos.
 * Este cargador crea alias para que las aplicaciones existentes sigan
 * funcionando mientras migran sus "use".
 *
 * @deprecated Actualiza tus imports a Ragnos\...; este alias se eliminará
 *             en una versión mayor futura.
 */

spl_autoload_register(static function (string $class): void {
    $legacy = 'App\\ThirdParty\\Ragnos\\';

    if (strncmp($class, $legacy, strlen($legacy)) !== 0) {
        return;
    }

    $current = 'Ragnos\\' . substr($class, strlen($legacy));

    if (class_exists($current) || interface_exists($current) || trait_exists($current)) {
        class_alias($current, $class);
    }
});
