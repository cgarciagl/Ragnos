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

/**
 * Helper interno para obtener el valor de cualquier fuente (JSON, Input Raw, GET, POST)
 */
function getInputValue($key, $default = null)
{
    $request = request();

    // 1. Verificamos si el Content-Type indica que es JSON
    $contentType = $request->getHeaderLine('Content-Type');

    // strpos verifica si 'application/json' está en el header (por si viene con charset=utf-8, etc.)
    if (strpos($contentType, 'application/json') !== false) {
        // Envolvemos en try-catch por si el JSON viene mal formado aunque el header diga que es JSON
        try {
            $jsonData = $request->getJSON(true);

            if (!empty($jsonData) && array_key_exists($key, $jsonData)) {
                return $jsonData[$key];
            }
        } catch (\Exception $e) {
            // Si el JSON falla, ignoramos silenciosamente y dejamos que getVar intente resolverlo
        }
    }

    // 2. Si no es JSON, intentar obtener usando getVar() 
    // Esto cubre GET, POST y datos RAW (form-urlencoded) para PUT/PATCH
    return $request->getVar($key) ?? $default;
}

function newValue($fieldname)
{
    return getInputValue($fieldname);
}

function oldValue($fieldname)
{
    return getInputValue('Ragnos_value_ant_' . $fieldname);
}

function fieldHasChanged($fieldname)
{
    return (newValue($fieldname) != oldValue($fieldname));
}

use CodeIgniter\Model; // Importar la clase Model

/**
 * Intenta recuperar una instancia de modelo de la propiedad pública 'modelo' de otro controlador.
 *
 * Esta función está diseñada para un patrón arquitectónico específico donde los controladores son
 * responsables de instanciar y configurar su modelo principal, y otras partes de la aplicación
 * necesitan acceder a ese modelo preconfigurado a través del controlador.
 *
 * @param string $controllername El nombre de la clase del controlador (sin espacio de nombres, por ejemplo, 'Users', 'Products').
 * Se espera que esté en el espacio de nombres 'App\Controllers\'.
 * @return Model La instancia de modelo configurada por el controlador especificado.
 * @throws \RuntimeException Si la clase del controlador no existe, no se puede instanciar,
 * o si su propiedad pública 'modelo' no es una instancia válida de CodeIgniter Model.
 */
function importModelFromController(string $controllername): Model
{
    $fullClassName = "App\\Controllers\\" . $controllername; // Construir el nombre completo de la clase.

    // 1. Verificar si la clase del controlador existe
    if (!class_exists($fullClassName)) {
        $errorMessage = "La clase del controlador '{$fullClassName}' no se encontró. No se puede importar el modelo.";
        log_message('error', $errorMessage); // Registrar el error para depuración.
        throw new \RuntimeException($errorMessage);
    }

    $controllerInstance = null;
    try {
        // 2. Intentar instanciar el controlador.
        // Esto es necesario para acceder a su propiedad 'modelo' configurada.
        $controllerInstance = new $fullClassName();
    } catch (\Throwable $e) {
        // Capturar cualquier excepción durante la instanciación del controlador (por ejemplo, errores de constructor).
        $errorMessage = "No se pudo instanciar el controlador '{$fullClassName}'. Error: " . $e->getMessage();
        log_message('error', $errorMessage);
        throw new \RuntimeException($errorMessage, $e->getCode(), $e); // Relanzar con la excepción anterior.
    }

    // 3. Verificar que la propiedad 'modelo' exista y sea accesible.
    // Verificamos la accesibilidad pública implícitamente al intentar acceder a ella.
    if (!property_exists($controllerInstance, 'modelo')) {
        $errorMessage = "El controlador '{$controllername}' no tiene una propiedad pública 'modelo' inicializada.";
        log_message('error', $errorMessage);
        throw new \RuntimeException($errorMessage);
    }

    // 4. Asegurarse de que la propiedad 'modelo' contiene una instancia válida de CodeIgniter Model.
    if (!($controllerInstance->modelo instanceof Model)) {
        $errorMessage = "La propiedad 'modelo' del controlador '{$controllername}' no es una instancia de CodeIgniter\\Model.";
        log_message('error', $errorMessage);
        throw new \RuntimeException($errorMessage);
    }

    // 5. Recuperar la instancia del modelo.
    $model = $controllerInstance->modelo;

    // 6. Limpieza: Desestablecer explícitamente la instancia del controlador.
    // Aunque el recolector de basura de PHP lo manejaría eventualmente,
    // esto puede ser una buena práctica si el controlador pudiera retener recursos significativos.
    unset($controllerInstance);

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
    $path = str_replace('\\', '/', $path);
    $path = str_replace('\\', '/', $path);
    return $path;
}

function controllerNameToURL(string $controllername): string
{
    $fullClassName = "App\\Controllers\\" . $controllername;
    return mapClassToURL($fullClassName);
}

use App\ThirdParty\Ragnos\Controllers\Ragnos;
function moneyFormat(float $amt): string
{
    // Use CodeIgniter's Config system for locale or pass it as a parameter
    $locale    = Ragnos::config()->locale ?? 'es_MX'; // Assuming Ragnos::config() returns a config object
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);
    $formatter->setTextAttribute(NumberFormatter::CURRENCY_SYMBOL, '$');
    return $formatter->format($amt);
}

function moneyToNumber(string $amt): float
{
    // Eliminar todos los caracteres no numéricos ni el punto decimal (si existe)
    $amt = preg_replace('/[^0-9\.-]/', '', $amt);
    // Asegurarse de que la cadena resultante es un número flotante válido
    return (float) $amt;
}