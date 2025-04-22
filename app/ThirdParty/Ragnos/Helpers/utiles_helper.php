<?php

function dbgConsola($x)
{
    // Imprimimos el código JavaScript como una cadena
    echo "<script type=\"text/javascript\">";

    // Usamos var_export para obtener una representación legible de la variable
    $x_str = var_export($x, true);

    // Usamos console.warn con un argumento opcional para el nombre del grupo
    echo "console.warn('dbgConsola', $x_str);";

    // Cerramos la etiqueta de script
    echo "</script>";
}

function dbgDie($x)
{
    echo '<pre>' . json_encode($x) . '</pre>';
    die();
}

function currency($number, $symbol = true)
{
    if ($symbol) {
        return ($symbol ? '$' : '') . number_format($number, 2);
    } else {
        return number_format($number, 2);
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
    // Set proper headers
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

    // Set HTTP status code
    http_response_code($statusCode);

    // Handle different data types properly
    if (is_array($data) || is_object($data)) {
        // Use JSON_PRETTY_PRINT for readability in development and JSON_UNESCAPED_UNICODE for proper character handling
        $options = JSON_UNESCAPED_UNICODE;
        if (ENVIRONMENT === 'development') {
            $options |= JSON_PRETTY_PRINT;
        }

        try {
            echo json_encode($data, $options);
        } catch (Exception $e) {
            // Handle encoding errors
            http_response_code(500);
            echo json_encode(['error' => 'Failed to encode response', 'message' => $e->getMessage()], $options);
        }
    } else {
        // Ensure string output is valid JSON if it's not already
        if (!is_string($data) || !isJson($data)) {
            echo json_encode(['data' => $data]);
        } else {
            echo $data;
        }
    }
    exit();
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

function arrayToSelect($nombre, $opciones, $campollave, $campovalor = null, $seleccionado = null, $extra = [])
{
    $campovalor = $campovalor ?? $campollave;
    $options    = arrayToDropdown($opciones, $campollave, $campovalor);
    if (!isset($extra['class'])) {
        $extra['class'] = 'form-control';
    }
    $seleccionado = $seleccionado ?? '';
    return form_dropdown($nombre, $options, $seleccionado, $extra);
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
        return redirect()->to($url, 'auto', $statusCode)->send();
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

function checkAjaxRequest($request)
{
    if (!$request->isAJAX()) {
        $response    = service('response');
        $urlcompleta = site_url('/');

        // Return JSON for potential API consumers
        if ($request->getHeaderLine('Accept') === 'application/json') {
            returnAsJSON(['error' => 'AJAX request required'], 400);
        }

        // More professional HTML response with better styling and clear message
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

        exit; // More standardized than die()
    }

    return true; // Return true to allow method chaining
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
function queryToAssocArray($sql, $index_key, $column_key)
{
    try {
        $db    = db_connect();
        $query = $db->query($sql);

        if (!$query) {
            return [];
        }

        $records = $query->getResultArray();

        // Return empty array if no results
        if (empty($records)) {
            return [];
        }

        // Verify keys exist in the result set
        if (!isset($records[0][$index_key]) || !isset($records[0][$column_key])) {
            log_message('error', "queryToAssocArray: Keys '$index_key' or '$column_key' not found in query results");
            return [];
        }

        return array_column($records, $column_key, $index_key);
    } catch (\Exception $e) {
        log_message('error', "Database query error: " . $e->getMessage());
        throw $e;
    }
}