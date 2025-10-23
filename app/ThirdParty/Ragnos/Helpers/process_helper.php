<?php

function sseHeaders()
{
    // Disable output buffering
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set headers for Server-Sent Events
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-open');
    header('X-Accel-Buffering: no'); // Disable nginx buffering
}

function sseSend($eventName, $data)
{
    // Ensure data is a string
    $data = is_array($data) ? json_encode($data) : (string) $data;

    // Escape newline characters within the data
    $data = str_replace(["\n", "\r"], ['\n', '\r'], $data);

    if (ob_get_level() == 0) {
        ob_start();
    }

    echo "event: $eventName\n";
    echo "data: $data\n\n";

    // Flush output to send data immediately
    ob_flush();
    flush();
}

function processStart($title = 'Processing...')
{
    // Set SSE headers
    sseHeaders();

    // Disable time limits and memory restrictions
    set_time_limit(0);
    ini_set('memory_limit', '-1');

    // Send initial progress event
    sseSend('progress_title', $title);
    sseSend('progress', 0);
}

function setProgress($percentage)
{
    // Ensure percentage is a whole number
    $percentage = ceil($percentage);

    // Send progress update event
    sseSend('progress', $percentage);
}

function setProgressText($text)
{
    // Send progress text event
    sseSend('progress_text', $text);
}

function endProcess($additionalData = null)
{
    // Calculate and send process time
    $time = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 5);

    // Send completion events
    sseSend('progress', 100);
    sseSend('process_complete', [
        'time'           => $time,
        'additionalData' => $additionalData
    ]);

    // Terminate the script
    exit;
}

function setProgressOf($currentStep, $total)
{
    // Calculate percentage and set progress
    setProgress(($currentStep / $total) * 100);
}