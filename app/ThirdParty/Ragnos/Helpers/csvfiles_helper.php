<?php

/**
 * Converts an array to a CSV file and optionally downloads it
 * 
 * @param array  $results  Array of data to convert to CSV
 * @param string $fileName Name of the output file
 * @param bool   $download Whether to download the file (true) or save locally (false)
 * @param string $delimiter CSV delimiter character (default: comma)
 * @param string $enclosure CSV enclosure character (default: double quote)
 * @param string $escape    CSV escape character (default: backslash)
 * @return bool True on success, false on failure
 */
function arrayToCSVFile(array $results, $fileName = 'temp.csv', $download = TRUE, $delimiter = ',', $enclosure = '"', $escape = '\\')
{
    if (empty($results)) {
        return false;
    }

    try {
        if ($download) {
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header('Content-Description: File Transfer');
            header("Content-Type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header("Expires: 0");
            header("Pragma: public");

            $fh = fopen('php://output', 'w');
        } else {
            $directory = dirname($fileName);
            if (!empty($directory) && $directory != '.' && !is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $fh = fopen($fileName, 'w');
        }

        if ($fh === false) {
            return false;
        }

        // Add BOM for UTF-8 Excel compatibility
        fwrite($fh, "\xEF\xBB\xBF");

        $headerDisplayed = false;
        foreach ($results as $data) {
            if (!$headerDisplayed) {
                fputcsv($fh, array_keys($data), $delimiter, $enclosure, $escape);
                $headerDisplayed = true;
            }
            fputcsv($fh, $data, $delimiter, $enclosure, $escape);
        }
        fclose($fh);

        return true;
    } catch (Exception $e) {
        if (isset($fh) && is_resource($fh)) {
            fclose($fh);
        }
        return false;
    }
}
