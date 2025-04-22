<?php

/**
 * Throws an exception with the specified message
 *
 * @param string $exception_message The error message to display
 * @param int $code Optional error code (default: 0)
 * @param \Throwable|null $previous Optional previous exception
 * @throws \Exception
 * @return never
 */
function raise(string $exception_message, int $code = 0, ?\Throwable $previous = null): never
{
    throw new \Exception($exception_message, $code, $previous);
}

function newValue($fieldname)
{
    return request()->getPost($fieldname);
}

function oldValue($fieldname)
{
    return request()->getPost('Ragnos_value_ant_' . $fieldname);
}


function fieldHasChanged($fieldname)
{
    return (newValue($fieldname) != oldValue($fieldname));
}

/**
 * Import model from a controller by controller name
 *
 * @param string $controllername The name of the controller class (without namespace)
 * @return object The model instance associated with the controller
 * @throws \Exception If controller class doesn't exist or has no model property
 */
function importModelFromController(string $controllername): object
{
    $fullClassName = "App\\Controllers\\" . $controllername;

    if (!class_exists($fullClassName)) {
        throw new \Exception("Controller class '{$fullClassName}' not found");
    }

    $controller = new $fullClassName();

    if (!isset($controller->modelo)) {
        throw new \Exception("Controller '{$controllername}' does not have a 'modelo' property");
    }

    $model = $controller->modelo;
    unset($controller);

    return $model;
}

/**
 * Convert a fully qualified class name to a URL path
 * 
 * @param string $class Fully qualified class name
 * @return string URL path representation
 */
function mapClassToURL(string $class): string
{
    // Remove the first 16 characters (likely "App\Controllers\") and convert to lowercase
    $path = strtolower(substr($class, 16));

    // Replace backslashes with forward slashes for URL compatibility
    return str_replace('\\', '/', $path);
}

use App\ThirdParty\Ragnos\Controllers\Ragnos;
function moneyFormat($amt)
{
    $locale    = Ragnos::config()->locale ?? 'es_MX';
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2); // Adjust decimal places as needed
    $formatter->setTextAttribute(NumberFormatter::CURRENCY_SYMBOL, '$'); // Set currency symbol
    return $formatter->format($amt);
}

function moneyToNumber($amt)
{
    // Eliminar todos los caracteres no numéricos ni el punto decimal (si existe)
    $amt = preg_replace('/[^0-9\.-]/', '', $amt);
    // Asegurarse de que la cadena resultante es un número flotante válido
    return (float) $amt;
}