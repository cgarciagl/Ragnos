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

function sessionValueOrDefault($variable, $defaultValue = '')
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

/**
 * Genera un elemento HTML <select> a partir de un array de opciones.
 *
 * @param string $name         El nombre del campo <select>.
 * @param array  $options      El array de opciones.
 * @param string $valueField   El nombre de la clave para el valor de la opción.
 * @param string $textField    El nombre de la clave para el texto visible de la opción. Por defecto, es igual a $valueField.
 * @param mixed  $selectedValue El valor o array de valores que deben estar preseleccionados.
 * @param array  $extra        Un array asociativo de atributos HTML adicionales para el <select>.
 *
 * @return string El HTML del elemento <select> generado.
 */
function arrayToSelect(string $name, array $options, string $valueField, string $textField = null, $selectedValue = null, array $extra = []): string
{
    // Normaliza los valores y sanitiza los atributos
    $textField     = $textField ?? $valueField;
    $selectedValue = (array) $selectedValue;

    $attributes = '';
    foreach ($extra as $key => $val) {
        $sanitizedKey  = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
        $sanitizedVal  = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
        $attributes   .= " {$sanitizedKey}=\"{$sanitizedVal}\"";
    }

    if (!isset($extra['class'])) {
        $attributes .= ' class="form-control"';
    }

    // Lógica para selecciones múltiples
    $isMultiple = isset($extra['multiple']) && $extra['multiple'];
    if ($isMultiple) {
        $attributes .= ' multiple';
        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }
    }

    // Sanitiza el nombre del campo para evitar inyecciones
    $sanitizedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    // Inicia la construcción del string HTML
    $html = "<select name=\"{$sanitizedName}\"{$attributes}>";

    // Genera las opciones
    foreach ($options as $option) {
        // Asegúrate de que el valor y el texto existen
        $value = $option[$valueField] ?? '';
        $text  = $option[$textField] ?? '';

        // Sanitiza los valores de la opción
        $sanitizedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        $sanitizedText  = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $isSelected = in_array($value, $selectedValue, true) ? ' selected' : '';

        $html .= "<option value=\"{$sanitizedValue}\"{$isSelected}>{$sanitizedText}</option>";
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

    // Use CodeIgniter's built-in redirect for 3xx status codes
    if ($statusCode >= 300 && $statusCode < 400) {
        return redirect()->to($url, null, $statusCode)->send();
    }

    // For non-redirect status codes, create a custom response
    $response = service('response');
    $response->setStatusCode($statusCode)
        ->setHeader('Location', $url)
        ->setContentType('text/html')
        ->setBody(sprintf(
            '<html><head><meta http-equiv="refresh" content="0;url=%s">
                <script>window.location.href="%s";</script></head>
                <body>Redirecting to <a href="%s">%s</a>...</body></html>',
            $url,
            $url,
            $url,
            htmlspecialchars($url)
        ))
        ->send();

    exit;
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
 * Helper para detectar si la petición espera JSON
 */
function isApiCall(\CodeIgniter\HTTP\IncomingRequest $request)
{
    // Verifica si el cliente envió header "Accept: application/json"
    // o si es una petición AJAX pura que prefiere JSON
    return $request->negotiate('media', ['text/html', 'application/json']) === 'application/json';
}

/**
 * Execute a SQL query and return the results as an array.
 *
 * Connects to the configured database, executes the provided SQL statement with
 * optional bound parameters, and returns the resulting rows as an array.
 * Errors and exceptions are logged; on failure the function returns an empty
 * array and any caught exception is re-thrown after logging.
 *
 * @param string $sql    The SQL query to execute. Use driver-compatible placeholders for parameters.
 * @param array  $params Optional parameters to bind into the query.
 *
 * @return array An array of result rows (associative arrays). Returns an empty array if the query fails or returns no rows.
 *
 * @throws \Exception Re-throws exceptions encountered during connection or execution after logging the error.
 */
function executeQuery(string $sql, array $params = []): array
{
    try {
        $db    = \Config\Database::connect();
        $query = $db->query($sql, $params);

        if (!$query) {
            log_message('error', 'Database query failed for SQL: ' . $sql);
            return [];
        }

        return $query->getResultArray();
    } catch (\Exception $e) {
        log_message('error', "Database query error: " . $e->getMessage() . " SQL: " . $sql);
        throw $e;
    }
}


/**
 * Convenience wrapper that executes a SQL query without parameters and
 * returns an associative array mapping a given index column to a given value column.
 *
 * This simply delegates to queryToAssocArrayParams() with an empty parameters array.
 *
 * @param string $sql        The SQL query to execute. Should return rows as associative arrays.
 * @param string $index_key  The column name to use as the keys of the resulting array.
 * @param string $column_key The column name to use as the values of the resulting array.
 *
 * @return array An associative array keyed by $index_key with values from $column_key.
 *               Returns an empty array if the query returns no rows.
 */

function queryToAssocArray(string $sql, string $index_key, string $column_key): array
{
    return queryToAssocArrayParams($sql, [], $index_key, $column_key);
}


/**
 * Execute a SQL query with parameters and return an associative array mapping
 * a specified index column to a specified value column.
 *
 * Behavior:
 * - Calls executeQuery($sql, $params) to fetch records (expected as an array of associative arrays).
 * - If no records are returned, returns an empty array.
 * - Validates that the first row contains both $index_key and $column_key using array_key_exists
 *   (so NULL values in columns are allowed). If either key is missing, logs an error via log_message()
 *   and returns an empty array.
 * - Uses array_column() to build and return the final mapping: [ index_key => column_key ].
 *
 * @param string $sql        The SQL query to execute.
 * @param array  $params     Parameters to bind to the SQL query.
 * @param string $index_key  The column name to use as the keys of the resulting array.
 * @param string $column_key The column name to use as the values of the resulting array.
 *
 * @return array An associative array keyed by $index_key with values from $column_key.
 *               Returns an empty array if no rows are returned or if the specified keys are not present.
 *
 * @see executeQuery()
 * @see log_message()
 */
function queryToAssocArrayParams(string $sql, array $params, string $index_key, string $column_key): array
{
    $records = executeQuery($sql, $params);

    if (empty($records)) {
        return [];
    }

    // Se usa array_key_exists en lugar de isset para permitir valores NULL en las columnas
    if (!array_key_exists($index_key, $records[0]) || !array_key_exists($column_key, $records[0])) {
        log_message('error', "queryToAssocArrayParams: Keys '{$index_key}' or '{$column_key}' not found in query results for SQL: {$sql}");
        return [];
    }

    return array_column($records, $column_key, $index_key);
}


/**
 * Ejecuta una consulta SQL y almacena en caché los resultados para optimizar el rendimiento.
 *
 * @param string      $sql       La consulta SQL a ejecutar.
 * @param array       $params    Parámetros para la consulta SQL (opcional).
 * @param string|null $cacheKey  Clave de caché personalizada (opcional). Si no se proporciona, se genera una automáticamente.
 * @param int         $ttl       Tiempo de vida en segundos para la caché (por defecto: 86400 segundos = 1 día).
 *
 * @return array Los resultados de la consulta, ya sea desde la caché o ejecutando la consulta.
 */
function getCachedData(string $sql, array $params = [], ?string $cacheKey = null, int $ttl = 86400): array
{
    $cache = \Config\Services::cache();

    // Generar una clave única basada en la consulta y los parámetros si no se proporciona una.
    // Se añade un prefijo para evitar colisiones con otras claves de caché.
    if (empty($cacheKey)) {
        $cacheKey = 'sql_' . md5($sql . json_encode($params));
    }

    // Intentar recuperar los datos del caché
    $cachedData = $cache->get($cacheKey);

    // Verificar explícitamente si no es null (el caché puede devolver false o null dependiendo del driver)
    if ($cachedData !== null) {
        return $cachedData;
    }

    // Si no hay datos en caché, ejecutar la consulta usando la función helper existente
    $result = executeQuery($sql, $params);

    // Guardar el resultado en caché
    $cache->save($cacheKey, $result, $ttl);

    return $result;
}
