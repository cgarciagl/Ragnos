<?php

/*
 * Bootstrap de PHPUnit para Ragnos.
 *
 * El bootstrap de CodeIgniter supone que la carpeta pública se llama
 * "public". Aquí se define FCPATH con la ruta configurada en
 * Config\Paths::$publicDirectory ("public" por defecto) antes de cargarlo.
 *
 * Se lee el valor del archivo en lugar de instanciar Config\Paths porque el
 * bootstrap de CodeIgniter declara esa clase más adelante.
 */

$ragnosConfigDir = __DIR__ . '/../app/Config';
$ragnosPublicDir = $ragnosConfigDir . '/../../public';

if (preg_match('/\$publicDirectory\s*=\s*__DIR__\s*\.\s*\'([^\']+)\'/', (string) file_get_contents($ragnosConfigDir . '/Paths.php'), $match)) {
    $ragnosPublicDir = $ragnosConfigDir . $match[1];
}

defined('FCPATH') || define('FCPATH', rtrim((string) realpath($ragnosPublicDir), '\\/ ') . DIRECTORY_SEPARATOR);
unset($ragnosConfigDir, $ragnosPublicDir, $match);

require __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';
