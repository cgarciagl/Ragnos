<?php

function dbgConsola($data, string $label = 'dbgConsola')
{
    // Solo ejecutar en entorno de desarrollo para evitar depuración en producción
    if (ENVIRONMENT === 'development') {
        // Asegurarse de que el contenido sea seguro para JSON
        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // Si json_encode falla, recurrir a var_export
        if (json_last_error() !== JSON_ERROR_NONE) {
            $json_data = json_encode(var_export($data, true)); // Envuelve var_export en json_encode
        }

        // Sanitizar la etiqueta para evitar inyecciones básicas
        $sanitized_label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

        // Generar el script JavaScript.
        // Se usa console.log para datos generales, console.warn o error si es un aviso/error.
        // Aquí usamos console.log para una depuración general.
        echo '<script type="text/javascript">' . PHP_EOL;
        echo '  console.log("' . $sanitized_label . ':", ' . $json_data . ');' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }
}

function dbgDie($data, int $statusCode = 200): never
{
    // Obtener el servicio de respuesta de CodeIgniter
    $response = service('response');

    // Configurar la cabecera Content-Type como JSON
    $response->setHeader('Content-Type', 'application/json; charset=utf-8');

    // Configurar el código de estado HTTP
    $response->setStatusCode($statusCode);

    // Opciones para json_encode
    $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

    // Si estamos en entorno de desarrollo, hacer la salida JSON más legible
    if (ENVIRONMENT === 'development') {
        $options |= JSON_PRETTY_PRINT;
    }

    try {
        $jsonOutput = json_encode($data, $options);

        // Si la codificación JSON falla, registrar el error y usar un mensaje de error genérico
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', 'dbgDie: Error al codificar datos a JSON. Error: ' . json_last_error_msg());
            $errorData  = [
                'error'   => 'Fallo al serializar datos de depuración.',
                'message' => ENVIRONMENT === 'development' ? json_last_error_msg() : 'Error interno.',
            ];
            $jsonOutput = json_encode($errorData, $options);
        }
    } catch (\Throwable $e) {
        // Capturar cualquier otra excepción durante la codificación
        log_message('critical', 'dbgDie: Excepción inesperada durante la codificación JSON: ' . $e->getMessage());
        $errorData  = [
            'error'   => 'Excepción durante la serialización de datos.',
            'message' => ENVIRONMENT === 'development' ? $e->getMessage() : 'Error interno.',
        ];
        $jsonOutput = json_encode($errorData, $options);
    }

    // Enviar la respuesta y terminar la ejecución
    $response->setBody($jsonOutput)->send();

    // Terminar la ejecución de forma limpia
    exit; // Uso preferido sobre die() para consistencia con CI4
}

if (!function_exists('currency')) {
    /**
     * Formatea un número como una cadena de moneda.
     * Utiliza la configuración regional y de moneda del framework (si está disponible)
     * o valores predeterminados sensatos.
     *
     * @param float|int $number El número a formatear.
     * @param bool $includeSymbol Si se debe incluir el símbolo de la moneda (por defecto: true).
     * @param string|null $locale La configuración regional a usar (por ejemplo, 'en_US', 'es_MX').
     * Si es null, intentará usar la configuración global de CodeIgniter.
     * @param string|null $currencyCode El código de moneda de 3 letras (por ejemplo, 'USD', 'MXN').
     * Si es null, intentará inferir de la configuración regional o usará el predeterminado.
     * @return string El número formateado como moneda.
     */
    function currency(float|int $number, bool $includeSymbol = true, ?string $locale = null, ?string $currencyCode = null): string
    {
        // Usar la configuración local de la aplicación o un valor predeterminado si no se especifica.
        // CodeIgniter 4 a menudo tiene una configuración regional en Config/App.php.
        // También se puede obtener desde Ragnos::config()->locale si es aplicable a su framework.
        $actualLocale = $locale ?? (config('App')->defaultLocale ?? 'es_MX');

        // Para CodeIgniter 4, el servicio de internacionalización (Intl) es la forma recomendada.
        // Asegurarse de que la extensión Intl esté habilitada en PHP.
        if (extension_loaded('intl')) {
            try {
                $formatter = new NumberFormatter($actualLocale, NumberFormatter::CURRENCY);

                // Si se especifica un código de moneda, intentar usarlo.
                // NumberFormatter usará el símbolo predeterminado para la configuración regional si no se especifica.
                if ($currencyCode !== null) {
                    // Esto puede no cambiar el símbolo visible, pero asegura la moneda base.
                    // A menudo, el símbolo se deriva de la configuración regional.
                    // Si necesita un control estricto del símbolo, el enfoque de `moneyFormat` en ragnos_helper es una buena alternativa.
                }

                // Ajustar decimales, si es necesario (el formato de moneda suele tener 2)
                $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

                $formatted = $formatter->format($number);

                // Si se solicita no incluir el símbolo, intentar quitarlo.
                // Esto puede ser complejo debido a la ubicación variable del símbolo.
                if (!$includeSymbol) {
                    // Esto es un intento básico; para un control riguroso, es mejor
                    // configurar NumberFormatter sin el símbolo si se puede.
                    // Para EUR 1.234,56 €, quitar € no es sencillo.
                    // Una alternativa es usar NumberFormatter::DECIMAL y añadir el símbolo manualmente si se requiere.
                    return str_replace($formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL), '', $formatted);
                }

                return $formatted;

            } catch (\IntlException $e) {
                // Registrar el error si Intl falla (por ejemplo, locale no válido)
                log_message('error', 'Error en Intl al formatear moneda: ' . $e->getMessage());
                // Caer al método de respaldo si Intl falla
                return ($includeSymbol ? '$' : '') . number_format($number, 2, '.', ','); // Fallback seguro
            }
        } else {
            // Si la extensión Intl no está habilitada, usar number_format como fallback.
            log_message('warning', 'Extensión PHP Intl no habilitada. Usando number_format como fallback para currency().');
            return ($includeSymbol ? '$' : '') . number_format($number, 2, '.', ','); // Formato de EE. UU. / México
        }
    }
}

function selfUrl()
{
    $uri = current_url(true);
    $url = (string) $uri;
    return $url;
}

function refresh()
{
    redirectAndDie(selfUrl());
}

function ifSet(&$val, $default = null)
{
    return isset($val) && !empty($val) ? $val : $default;
}

function startsWith($cadena, $parcial)
{
    return $parcial === "" || strrpos($cadena, $parcial, -strlen($cadena)) !== false;
}

function endsWith($cadena, $parcial)
{
    return $parcial === "" || strpos($cadena, $parcial, strlen($cadena) - strlen($parcial)) !== false;
}

function removeNewLines($text)
{
    return str_replace(["\n", "\r"], ' ', $text);
}

function valueFromSessionOrDefault($variable, $defaultValue = '')
{
    return session($variable) ? session($variable) : $defaultValue;
}


function returnAsJSON($data, $statusCode = 200)
{
    $response = service('response');
    $response->setStatusCode($statusCode);
    $options = JSON_UNESCAPED_UNICODE;

    if (ENVIRONMENT === 'development') {
        $options |= JSON_PRETTY_PRINT;
    }

    if (is_array($data) || is_object($data)) {
        $response->setJSON($data, $options)->send();
    } else {
        // Ensure string output is valid JSON if it's not already
        if (!is_string($data) || !isJson($data)) {
            $response->setJSON(['data' => $data], $options)->send();
        } else {
            // If it's already a JSON string, set rawBody and content type
            $response->setBody($data)
                ->setHeader('Content-Type', 'application/json; charset=utf-8')
                ->send();
        }
    }
    exit(); // Use exit() after sending the response
}

// Helper function to check if a string is valid JSON
function isJson($string)
{
    if (!is_string($string))
        return false;
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

function arrayToDropdown($array, $valueField, $textField = null)
{
    $textField = $textField ?? $valueField;
    if (is_array($array) && !empty($array)) {
        return array_column($array, $textField, $valueField);
    }
    return [];
}

function arrayToSelect($name, $options, $valueField, $textField = null, $selectedValue = null, $extra = [])
{
    $textField       = $textField ?? $valueField;
    $dropdownOptions = arrayToDropdown($options, $valueField, $textField);

    $attributes = '';
    foreach ($extra as $key => $val) {
        $attributes .= " {$key}=\"{$val}\"";
    }
    if (!isset($extra['class'])) {
        $attributes .= ' class="form-control"';
    }

    $html = "<select name=\"{$name}\"{$attributes}>";
    foreach ($dropdownOptions as $value => $text) {
        $selected = ($value == $selectedValue) ? ' selected' : '';
        $html .= "<option value=\"{$value}\"{$selected}>" . htmlspecialchars($text) . "</option>";
    }
    $html .= "</select>";
    return $html;
}

function sessionObject()
{
    return json_encode(session()->get());
}

function redirectAndDie($url, $statusCode = 302)
{
    $url = strpos($url, 'http') === 0 ? $url : site_url($url);
    service('response')->redirect($url, 'auto', $statusCode)->send();
    exit(); // Important to exit after send()
}

function checkAjaxRequest(\CodeIgniter\HTTP\IncomingRequest $request)
{
    if (!$request->isAJAX()) {
        $response    = service('response');
        $urlcompleta = site_url('/');

        if ($request->getHeaderLine('Accept') === 'application/json') {
            returnAsJSON(['error' => 'AJAX request required'], 400); // Leverage existing helper
        }

        $html = <<<HTML
<html>
<head>
    <title>400 Bad Request</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        h1 { color: #d9534f; }
        .message { margin: 20px 0; }
        .redirect { color: #777; font-size: 14px; }
    </style>
    <script>
        setTimeout(function() { window.location.href = '{$urlcompleta}'; }, 3000);
    </script>
</head>
<body>
    <h1>400 Bad Request</h1>
    <div class="message">This endpoint requires an AJAX request.</div>
    <p class="redirect">Redirecting to homepage in 3 seconds...</p>
</body>
</html>
HTML;

        $response->setStatusCode(400)
            ->setHeader('Content-Type', 'text/html')
            ->setBody($html)
            ->send();
        exit;
    }
    return true;
}

/**
 * Convert a SQL query to an associative array
 * 
 * @param string $sql       SQL query to execute
 * @param string $index_key Column to use as array index
 * @param string $column_key Column to use as array value
 * @return array Associative array of results
 * @throws Exception If database error occurs
 */
function queryToAssocArray(string $sql, string $index_key, string $column_key): array
{
    try {
        $db    = \Config\Database::connect(); // Or use db_connect() wrapper
        $query = $db->query($sql);

        if (!$query) {
            // Log a more specific error or return empty array if query failed
            log_message('error', 'Database query failed for SQL: ' . $sql);
            return [];
        }

        $records = $query->getResultArray();

        if (empty($records)) {
            return [];
        }

        if (!isset($records[0][$index_key]) || !isset($records[0][$column_key])) {
            log_message('error', "queryToAssocArray: Keys '$index_key' or '$column_key' not found in query results for SQL: " . $sql);
            return [];
        }

        return array_column($records, $column_key, $index_key);
    } catch (\Exception $e) {
        log_message('error', "Database query error in queryToAssocArray: " . $e->getMessage() . " SQL: " . $sql);
        throw $e; // Re-throw the exception for the calling code to handle
    }
}

/**
 * Retrieves data from cache if available; otherwise, executes the provided SQL query,
 * caches the result, and returns the data.
 *
 * @param string $cacheKey The unique key used to identify the cached data.
 * @param string $sql The SQL query to execute if the data is not found in the cache.
 * @param int $ttl The time-to-live for the cached data in seconds. Defaults to 86400 (24 hours).
 * @return mixed The cached data or the result of the SQL query.
 */
function getCachedData($cacheKey, $sql, $ttl = 86400)
{
    $cache      = \Config\Services::cache();
    $cachedData = $cache->get($cacheKey);

    if ($cachedData) {
        return $cachedData;
    }

    $db     = db_connect();
    $query  = $db->query($sql);
    $result = $query->getResultArray();

    $cache->save($cacheKey, $result, $ttl);
    return $result;
}