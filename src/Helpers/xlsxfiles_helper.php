<?php

if (!function_exists('buildZipPackage')) {
    /**
     * Empaqueta archivos y contenidos en un archivo ZIP/XLSX válido.
     * Utiliza \ZipArchive si la extensión está instalada y habilitada; si no está disponible,
     * utiliza un empaquetador ZIP nativo en PHP puro (usando gzdeflate de zlib o modo sin comprimir),
     * garantizando funcionamiento al 100% sin depender de extensiones compiladas adicionales.
     *
     * @param string $outputPath Ruta del archivo ZIP/XLSX de salida
     * @param array  $entries    Array asociativo ['nombre/en/zip' => ['type' => 'string'|'file', 'content' => ...]]
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    function buildZipPackage(string $outputPath, array $entries): bool
    {
        if (empty($entries)) {
            return false;
        }

        // 1. Intentar con \ZipArchive nativo si está disponible
        if (class_exists('\ZipArchive')) {
            try {
                $zip = new \ZipArchive();
                if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                    foreach ($entries as $zipName => $entry) {
                        if ($entry['type'] === 'file') {
                            $zip->addFile($entry['content'], $zipName);
                        } else {
                            $zip->addFromString($zipName, (string) $entry['content']);
                        }
                    }
                    if ($zip->close()) {
                        return true;
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'buildZipPackage (ZipArchive): ' . $e->getMessage());
            }
        }

        // 2. Fallback ZIP en PHP puro (cero dependencias externas, usando pack y gzdeflate)
        $zipHandle = @fopen($outputPath, 'wb');
        if (!$zipHandle) {
            log_message('error', 'buildZipPackage: No se pudo abrir el archivo para escritura: ' . $outputPath);
            return false;
        }

        $time = time();
        $dtime = getdate($time);
        $dosTime = (($dtime['hours'] << 11) | ($dtime['minutes'] << 5) | ($dtime['seconds'] >> 1));
        $dosDate = ((($dtime['year'] - 1980) << 9) | ($dtime['mon'] << 5) | $dtime['mday']);

        $centralDirectory = '';
        $offset = 0;
        $count = 0;

        foreach ($entries as $name => $entry) {
            if ($entry['type'] === 'file') {
                $data = @file_get_contents($entry['content']);
                if ($data === false) {
                    $data = '';
                }
            } else {
                $data = (string) $entry['content'];
            }

            $uncompressedSize = strlen($data);
            $crc = crc32($data);

            $method = 0;
            $compressedData = $data;
            if (function_exists('gzdeflate')) {
                $deflated = @gzdeflate($data);
                if ($deflated !== false && strlen($deflated) < $uncompressedSize) {
                    $method = 8;
                    $compressedData = $deflated;
                }
            }
            $compressedSize = strlen($compressedData);
            $nameLength = strlen($name);

            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50, // Firma encabezado local
                20,         // Versión mínima requerida (2.0)
                0x0800,     // Bit 11 activado para nombres UTF-8
                $method,    // Método de compresión (0 = store, 8 = deflate)
                $dosTime,
                $dosDate,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $nameLength,
                0           // Longitud campo extra
            ) . $name;

            fwrite($zipHandle, $localHeader);
            fwrite($zipHandle, $compressedData);

            $centralDirectory .= pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50, // Firma encabezado directorio central
                20,         // Versión creadora
                20,         // Versión mínima requerida
                0x0800,     // Nombres UTF-8
                $method,
                $dosTime,
                $dosDate,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $nameLength,
                0,          // Campo extra
                0,          // Comentario del archivo
                0,          // Disco inicial
                0,          // Atributos internos
                0,          // Atributos externos
                $offset
            ) . $name;

            $offset += strlen($localHeader) + $compressedSize;
            $count++;
        }

        $centralDirLength = strlen($centralDirectory);
        $endOfCentralDir = pack(
            'VvvvvVVv',
            0x06054b50, // Firma fin de directorio central
            0,          // Número de este disco
            0,          // Disco donde inicia el directorio central
            $count,     // Registros en este disco
            $count,     // Total de registros
            $centralDirLength,
            $offset,    // Offset relativo del directorio central
            0           // Longitud comentario ZIP
        );

        fwrite($zipHandle, $centralDirectory);
        fwrite($zipHandle, $endOfCentralDir);
        fclose($zipHandle);

        return true;
    }
}

if (!function_exists('arrayToXLSXFile')) {
    /**
     * Convierte un array de datos en un archivo Excel (.xlsx) nativo sin dependencias externas
     * y opcionalmente lo descarga o lo guarda en el sistema de archivos local.
     *
     * @param array  $results   Array asociativo o matriz de datos a convertir a Excel
     * @param string $fileName  Nombre de la ruta o archivo de salida (por defecto: 'temp.xlsx')
     * @param bool   $download  Si es true descarga el archivo en el navegador, si es false lo guarda localmente
     * @param string $sheetName Nombre de la hoja de cálculo (por defecto: 'Sheet1')
     * @param array  $options   Opciones adicionales de personalización:
     *                          - 'header_bg'      (string) Color HEX del fondo del encabezado (ej. 'FF1F4E79')
     *                          - 'header_color'   (string) Color HEX del texto del encabezado (ej. 'FFFFFFFF')
     *                          - 'header_height'  (float)  Altura de la fila de encabezado (por defecto: 24)
     *                          - 'row_height'     (float)  Altura de las filas de datos (por defecto: 19)
     *                          - 'auto_width'     (bool)   Ajustar automáticamente el ancho de columnas (por defecto: true)
     *                          - 'auto_filter'    (bool)   Agregar filtros automáticos de Excel en encabezados (por defecto: true)
     *                          - 'freeze_header'  (bool)   Inmovilizar la primera fila de encabezado (por defecto: true)
     *                          - 'font_name'      (string) Nombre de fuente (por defecto: 'Calibri')
     *                          - 'font_size'      (int)    Tamaño de fuente (por defecto: 11)
     *                          - 'custom_widths'  (array)  Anchos personalizados por índice o columna (ej. ['A' => 25, 1 => 30])
     * @return bool True si la operación fue exitosa, false en caso de error
     */
    function arrayToXLSXFile(
        array $results,
        string $fileName = 'temp.xlsx',
        bool $download = true,
        string $sheetName = 'Sheet1',
        array $options = []
    ): bool {
        if (empty($results)) {
            return false;
        }

        // Asegurar extensión .xlsx
        if (!preg_match('/\.xlsx$/i', $fileName)) {
            $fileName .= '.xlsx';
        }

        $tempSheetFile = null;
        $finalSheetFile = null;
        $tempZipFile = null;

        try {
            // Normalizar primer registro para extraer encabezados
            $firstRow = reset($results);
            if (is_object($firstRow)) {
                $firstRow = (array) $firstRow;
            }

            if (!is_array($firstRow)) {
                return false;
            }

            $headers = array_keys($firstRow);
            $colCount = count($headers);
            if ($colCount === 0) {
                return false;
            }

            // Opciones y estilos por defecto
            $headerBg     = isset($options['header_bg']) ? strtoupper(ltrim($options['header_bg'], '#')) : '1F4E79';
            $headerColor  = isset($options['header_color']) ? strtoupper(ltrim($options['header_color'], '#')) : 'FFFFFF';
            $headerHeight = (float) ($options['header_height'] ?? 24);
            $rowHeight    = (float) ($options['row_height'] ?? 19);
            $autoWidth    = (bool) ($options['auto_width'] ?? true);
            $autoFilter   = (bool) ($options['auto_filter'] ?? true);
            $freezeHeader = (bool) ($options['freeze_header'] ?? true);
            $fontName     = htmlspecialchars($options['font_name'] ?? 'Calibri', ENT_QUOTES | ENT_XML1, 'UTF-8');
            $fontSize     = (int) ($options['font_size'] ?? 11);
            $customWidths = $options['custom_widths'] ?? [];

            if (strlen($headerBg) === 6) {
                $headerBg = 'FF' . $headerBg;
            }
            if (strlen($headerColor) === 6) {
                $headerColor = 'FF' . $headerColor;
            }

            // Función interna para calcular nombre de columna Excel (0 -> A, 25 -> Z, 26 -> AA)
            $toColName = function (int $col): string {
                $name = '';
                $col++;
                while ($col > 0) {
                    $mod = ($col - 1) % 26;
                    $name = chr(65 + $mod) . $name;
                    $col = intdiv($col - $mod, 26);
                }
                return $name;
            };

            // Sanitizar nombre de hoja (máximo 31 caracteres y sin caracteres prohibidos)
            $cleanSheetName = preg_replace('/[\\\\\/\?\*\[\]\:]/', '', $sheetName);
            $cleanSheetName = mb_substr(trim($cleanSheetName) !== '' ? $cleanSheetName : 'Sheet1', 0, 31, 'UTF-8');
            $cleanSheetNameXml = htmlspecialchars($cleanSheetName, ENT_QUOTES | ENT_XML1, 'UTF-8');

            // Inicializar anchos de columnas
            $colWidths = [];
            foreach ($headers as $idx => $header) {
                $colLetter = $toColName($idx);
                if (isset($customWidths[$colLetter])) {
                    $colWidths[$idx] = (float) $customWidths[$colLetter];
                } elseif (isset($customWidths[$idx])) {
                    $colWidths[$idx] = (float) $customWidths[$idx];
                } else {
                    $colWidths[$idx] = max(10, mb_strwidth((string) $header, 'UTF-8') + 4);
                }
            }

            // Archivo temporal para streamear el cuerpo del sheet
            $tempSheetFile = tempnam(sys_get_temp_dir(), 'xlsx_sh_');
            $sh = fopen($tempSheetFile, 'w');
            if ($sh === false) {
                return false;
            }

            // Escribir fila de encabezados
            $headerRowXml = "    <row r=\"1\" ht=\"{$headerHeight}\" customHeight=\"1\">\n";
            foreach ($headers as $cIdx => $header) {
                $cellRef = $toColName($cIdx) . '1';
                $cleanStr = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', (string) $header);
                $escaped = htmlspecialchars($cleanStr, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $headerRowXml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\" s=\"1\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>\n";
            }
            $headerRowXml .= "    </row>\n";
            fwrite($sh, $headerRowXml);

            // Escribir filas de datos
            $rowNum = 1;
            foreach ($results as $row) {
                $rowNum++;
                $rowData = is_object($row) ? (array) $row : (array) $row;
                $rowXml = "    <row r=\"{$rowNum}\" ht=\"{$rowHeight}\" customHeight=\"1\">\n";
                $cIdx = 0;

                foreach ($rowData as $val) {
                    $cellRef = $toColName($cIdx) . $rowNum;
                    $valStr = (string) $val;

                    if ($autoWidth && !isset($customWidths[$toColName($cIdx)]) && !isset($customWidths[$cIdx])) {
                        $len = mb_strwidth($valStr, 'UTF-8');
                        if (isset($colWidths[$cIdx]) && ($len + 4) > $colWidths[$cIdx]) {
                            $colWidths[$cIdx] = min(60, $len + 4);
                        }
                    }

                    if ($val === null || $val === '') {
                        $rowXml .= "      <c r=\"{$cellRef}\" s=\"2\"/>\n";
                    } elseif (is_bool($val)) {
                        $bVal = $val ? '1' : '0';
                        $rowXml .= "      <c r=\"{$cellRef}\" t=\"b\" s=\"2\"><v>{$bVal}</v></c>\n";
                    } elseif (is_numeric($val) && (!is_string($val) || !preg_match('/^0[0-9]+/', $val))) {
                        $numVal = is_float($val) || (is_string($val) && strpos($val, '.') !== false) ? (float) $val : (int) $val;
                        $rowXml .= "      <c r=\"{$cellRef}\" s=\"2\"><v>{$numVal}</v></c>\n";
                    } else {
                        $cleanStr = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $valStr);
                        $escaped = htmlspecialchars($cleanStr, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $rowXml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\" s=\"2\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>\n";
                    }

                    $cIdx++;
                }

                $rowXml .= "    </row>\n";
                fwrite($sh, $rowXml);
            }
            fclose($sh);

            // Construir archivo final de hoja con vistas, columnas y filtros
            $lastColName = $toColName($colCount - 1);
            $range = "A1:{$lastColName}{$rowNum}";

            $finalSheetFile = tempnam(sys_get_temp_dir(), 'xlsx_fsh_');
            $outFh = fopen($finalSheetFile, 'w');
            if ($outFh === false) {
                return false;
            }

            $sheetHeader = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
            $sheetHeader .= "<worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">\n";

            if ($freezeHeader) {
                $sheetHeader .= "  <sheetViews>\n";
                $sheetHeader .= "    <sheetView tabSelected=\"1\" workbookViewId=\"0\">\n";
                $sheetHeader .= "      <pane ySplit=\"1\" topLeftCell=\"A2\" activePane=\"bottomLeft\" state=\"frozen\"/>\n";
                $sheetHeader .= "    </sheetView>\n";
                $sheetHeader .= "  </sheetViews>\n";
            }

            $sheetHeader .= "  <sheetFormatPr defaultRowHeight=\"{$rowHeight}\" customHeight=\"1\"/>\n";
            $sheetHeader .= "  <cols>\n";
            foreach ($colWidths as $idx => $width) {
                $col1 = $idx + 1;
                $sheetHeader .= "    <col min=\"{$col1}\" max=\"{$col1}\" width=\"{$width}\" customWidth=\"1\"/>\n";
            }
            $sheetHeader .= "  </cols>\n";
            $sheetHeader .= "  <sheetData>\n";
            fwrite($outFh, $sheetHeader);

            // Volcar filas procesadas
            $inFh = fopen($tempSheetFile, 'r');
            if ($inFh !== false) {
                while (!feof($inFh)) {
                    fwrite($outFh, fread($inFh, 65536));
                }
                fclose($inFh);
            }

            $sheetFooter = "  </sheetData>\n";
            if ($autoFilter) {
                $sheetFooter .= "  <autoFilter ref=\"{$range}\"/>\n";
            }
            $sheetFooter .= "</worksheet>";
            fwrite($outFh, $sheetFooter);
            fclose($outFh);

            // Generar paquete ZIP (.xlsx)
            $tempZipFile = tempnam(sys_get_temp_dir(), 'xlsx_pkg_');

            // 1. [Content_Types].xml
            $contentTypes = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<Types xmlns=\"http://schemas.openxmlformats.org/package/2006/content-types\">\n"
                . "  <Default Extension=\"rels\" ContentType=\"application/vnd.openxmlformats-package.relationships+xml\"/>\n"
                . "  <Default Extension=\"xml\" ContentType=\"application/xml\"/>\n"
                . "  <Override PartName=\"/xl/workbook.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml\"/>\n"
                . "  <Override PartName=\"/xl/worksheets/sheet1.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>\n"
                . "  <Override PartName=\"/xl/styles.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml\"/>\n"
                . "</Types>";

            // 2. _rels/.rels
            $rels = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">\n"
                . "  <Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument\" Target=\"xl/workbook.xml\"/>\n"
                . "</Relationships>";

            // 3. xl/_rels/workbook.xml.rels
            $wbRels = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">\n"
                . "  <Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet1.xml\"/>\n"
                . "  <Relationship Id=\"rId2\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>\n"
                . "</Relationships>";

            // 4. xl/workbook.xml
            $workbook = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<workbook xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">\n"
                . "  <workbookPr date1904=\"false\"/>\n"
                . "  <sheets>\n"
                . "    <sheet name=\"{$cleanSheetNameXml}\" sheetId=\"1\" r:id=\"rId1\"/>\n"
                . "  </sheets>\n"
                . "</workbook>";

            // 5. xl/styles.xml
            $styles = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<styleSheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\">\n"
                . "  <fonts count=\"2\">\n"
                . "    <font><sz val=\"{$fontSize}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "    <font><b/><sz val=\"{$fontSize}\"/><color rgb=\"{$headerColor}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "  </fonts>\n"
                . "  <fills count=\"3\">\n"
                . "    <fill><patternFill patternType=\"none\"/></fill>\n"
                . "    <fill><patternFill patternType=\"gray125\"/></fill>\n"
                . "    <fill><patternFill patternType=\"solid\"><fgColor rgb=\"{$headerBg}\"/></patternFill></fill>\n"
                . "  </fills>\n"
                . "  <borders count=\"2\">\n"
                . "    <border><left/><right/><top/><bottom/><diagonal/></border>\n"
                . "    <border>\n"
                . "      <left style=\"thin\"><color rgb=\"FFD9D9D9\"/></left>\n"
                . "      <right style=\"thin\"><color rgb=\"FFD9D9D9\"/></right>\n"
                . "      <top style=\"thin\"><color rgb=\"FFD9D9D9\"/></top>\n"
                . "      <bottom style=\"thin\"><color rgb=\"FFD9D9D9\"/></bottom>\n"
                . "    </border>\n"
                . "  </borders>\n"
                . "  <cellStyleXfs count=\"1\">\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\"/>\n"
                . "  </cellStyleXfs>\n"
                . "  <cellXfs count=\"3\">\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\" xfId=\"0\"/>\n"
                . "    <xf numFmtId=\"0\" fontId=\"1\" fillId=\"2\" borderId=\"1\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\">\n"
                . "      <alignment horizontal=\"center\" vertical=\"center\" wrapText=\"1\"/>\n"
                . "    </xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"1\" xfId=\"0\" applyBorder=\"1\" applyAlignment=\"1\">\n"
                . "      <alignment vertical=\"center\"/>\n"
                . "    </xf>\n"
                . "  </cellXfs>\n"
                . "</styleSheet>";

            $entries = [
                '[Content_Types].xml'        => ['type' => 'string', 'content' => $contentTypes],
                '_rels/.rels'                => ['type' => 'string', 'content' => $rels],
                'xl/_rels/workbook.xml.rels' => ['type' => 'string', 'content' => $wbRels],
                'xl/workbook.xml'            => ['type' => 'string', 'content' => $workbook],
                'xl/styles.xml'              => ['type' => 'string', 'content' => $styles],
                'xl/worksheets/sheet1.xml'   => ['type' => 'file', 'content' => $finalSheetFile],
            ];

            if (!buildZipPackage($tempZipFile, $entries)) {
                log_message('error', 'arrayToXLSXFile: No se pudo empaquetar el archivo XLSX.');
                return false;
            }

            // Salida: Descarga HTTP o Guardar localmente
            if ($download) {
                if (ob_get_level()) {
                    ob_end_clean();
                }

                $downloadName = basename($fileName);
                header('Content-Description: File Transfer');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tempZipFile));

                readfile($tempZipFile);
                @unlink($tempZipFile);
                exit;
            } else {
                $directory = dirname($fileName);
                if (!empty($directory) && $directory !== '.' && !is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                if (!copy($tempZipFile, $fileName)) {
                    return false;
                }

                return true;
            }
        } catch (\Throwable $e) {
            log_message('error', 'arrayToXLSXFile: ' . $e->getMessage());
            return false;
        } finally {
            if ($tempSheetFile && file_exists($tempSheetFile)) {
                @unlink($tempSheetFile);
            }
            if ($finalSheetFile && file_exists($finalSheetFile)) {
                @unlink($finalSheetFile);
            }
            if ($tempZipFile && file_exists($tempZipFile)) {
                @unlink($tempZipFile);
            }
        }
    }
}

if (!function_exists('arrayToExcelFile')) {
    /**
     * Alias de arrayToXLSXFile para mayor comodidad al generar archivos Excel (.xlsx).
     *
     * @see arrayToXLSXFile()
     */
    function arrayToExcelFile(
        array $results,
        string $fileName = 'temp.xlsx',
        bool $download = true,
        string $sheetName = 'Sheet1',
        array $options = []
    ): bool {
        return arrayToXLSXFile($results, $fileName, $download, $sheetName, $options);
    }
}

if (!function_exists('htmlToXLSXFile')) {
    /**
     * Convierte contenido HTML (tablas simples o reportes complejos con títulos, filtros,
     * grupos de control break, subtotales y resumen general) en un archivo Excel (.xlsx) nativo
     * sin dependencias externas, manteniendo fielmente el diseño y la jerarquía del reporte.
     *
     * @param string $html      Contenido HTML a procesar (ej. innerHTML del reporte o tabla)
     * @param string $fileName  Nombre del archivo o ruta de salida (por defecto: 'reporte.xlsx')
     * @param bool   $download  Si es true descarga el archivo en el navegador, si es false lo guarda localmente
     * @param string $sheetName Nombre de la hoja de cálculo (máximo 31 caracteres)
     * @param array  $options   Opciones adicionales de personalización:
     *                          - 'header_bg'     (string) Color HEX del fondo de cabeceras de tabla (def: '1F4E79')
     *                          - 'header_color'  (string) Color HEX del texto de cabeceras (def: 'FFFFFF')
     *                          - 'title_color'   (string) Color HEX para títulos y totales principales (def: '1F4E79')
     *                          - 'group_bg'      (string) Color HEX de fondo de encabezados de grupo (def: 'E9EEF4')
     *                          - 'subtotal_bg'   (string) Color HEX de fondo para filas de subtotal (def: 'F2F4F7')
     *                          - 'grand_bg'      (string) Color HEX de fondo para resumen general (def: 'D9E1F2')
     *                          - 'font_name'     (string) Nombre de la fuente (def: 'Calibri')
     *                          - 'font_size'     (int)    Tamaño de fuente base (def: 11)
     *                          - 'auto_width'    (bool)   Ajustar automáticamente el ancho de columnas (def: true)
     * @return bool True si la operación fue exitosa, false en caso de error
     */
    function htmlToXLSXFile(
        string $html,
        string $fileName = 'reporte.xlsx',
        bool $download = true,
        string $sheetName = 'Reporte',
        array $options = []
    ): bool {
        if (empty(trim($html))) {
            return false;
        }

        // Asegurar extensión .xlsx
        if (!preg_match('/\.xlsx$/i', $fileName)) {
            $fileName .= '.xlsx';
        }

        $tempSheetFile = null;
        $finalSheetFile = null;
        $tempZipFile = null;

        try {
            $dom = new \DOMDocument();
            $prevLibxml = libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="UTF-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            libxml_use_internal_errors($prevLibxml);

            $xpath = new \DOMXPath($dom);

            // Estilos y opciones configurables
            $headerBg    = strtoupper(ltrim($options['header_bg'] ?? '1F4E79', '#'));
            $headerColor = strtoupper(ltrim($options['header_color'] ?? 'FFFFFF', '#'));
            $titleColor  = strtoupper(ltrim($options['title_color'] ?? '1F4E79', '#'));
            $groupBg     = strtoupper(ltrim($options['group_bg'] ?? 'E9EEF4', '#'));
            $subtotalBg  = strtoupper(ltrim($options['subtotal_bg'] ?? 'F2F4F7', '#'));
            $grandBg     = strtoupper(ltrim($options['grand_bg'] ?? 'D9E1F2', '#'));
            $fontName    = htmlspecialchars($options['font_name'] ?? 'Calibri', ENT_QUOTES | ENT_XML1, 'UTF-8');
            $fontSize    = (int) ($options['font_size'] ?? 11);
            $autoWidth   = (bool) ($options['auto_width'] ?? true);

            if (strlen($headerBg) === 6)    $headerBg    = 'FF' . $headerBg;
            if (strlen($headerColor) === 6) $headerColor = 'FF' . $headerColor;
            if (strlen($titleColor) === 6)  $titleColor  = 'FF' . $titleColor;
            if (strlen($groupBg) === 6)     $groupBg     = 'FF' . $groupBg;
            if (strlen($subtotalBg) === 6)  $subtotalBg  = 'FF' . $subtotalBg;
            if (strlen($grandBg) === 6)     $grandBg     = 'FF' . $grandBg;

            $toColName = function (int $col): string {
                $name = '';
                $col++;
                while ($col > 0) {
                    $mod = ($col - 1) % 26;
                    $name = chr(65 + $mod) . $name;
                    $col = intdiv($col - $mod, 26);
                }
                return $name;
            };

            // Sanitizar nombre de hoja
            $cleanSheetName = preg_replace('/[\\\\\/\?\*\[\]\:]/', '', $sheetName);
            $cleanSheetName = mb_substr(trim($cleanSheetName) !== '' ? $cleanSheetName : 'Reporte', 0, 31, 'UTF-8');
            $cleanSheetNameXml = htmlspecialchars($cleanSheetName, ENT_QUOTES | ENT_XML1, 'UTF-8');

            // Extraer metadatos del reporte (Título, filtros, fecha)
            $reportTitle = null;
            $titleNode = $xpath->query('//h1 | //h2')->item(0);
            if ($titleNode) {
                $reportTitle = trim(preg_replace('/\s+/', ' ', $titleNode->textContent));
            }

            $filterDesc = null;
            $filterNode = $xpath->query('//*[contains(@class, "alert")]//span[contains(@class, "fs-5")]')->item(0);
            if ($filterNode) {
                $filterDesc = 'Filtros Activos: ' . trim(preg_replace('/\s+/', ' ', $filterNode->textContent));
            }

            $dateText = null;
            $dateNodes = $xpath->query('//div[contains(@class, "text-end") and contains(@class, "text-muted")]//div');
            if ($dateNodes->length > 0) {
                $parts = [];
                foreach ($dateNodes as $dn) {
                    $t = trim($dn->textContent);
                    if (!empty($t)) $parts[] = $t;
                }
                if (!empty($parts)) {
                    $dateText = 'Generado: ' . implode(' ', $parts);
                }
            }

            // Colección de filas para la hoja de cálculo
            $structuredRows = [];

            if (!empty($reportTitle)) {
                $structuredRows[] = [
                    'type'   => 'title',
                    'height' => 28,
                    'cells'  => [
                        ['text' => $reportTitle, 'style' => 7, 'align' => 'left', 'is_num' => false],
                    ],
                ];
            }

            if (!empty($filterDesc) || !empty($dateText)) {
                $subParts = array_filter([$filterDesc, $dateText]);
                $structuredRows[] = [
                    'type'   => 'subtitle',
                    'height' => 18,
                    'cells'  => [
                        ['text' => implode('  |  ', $subParts), 'style' => 8, 'align' => 'left', 'is_num' => false],
                    ],
                ];
            }

            if (!empty($reportTitle) || !empty($filterDesc)) {
                $structuredRows[] = [
                    'type'   => 'empty',
                    'height' => 12,
                    'cells'  => [],
                ];
            }

            $tables = $xpath->query('//table');
            if ($tables->length === 0) {
                log_message('error', 'htmlToXLSXFile: No se encontraron tablas <table> en el HTML.');
                return false;
            }

            $emittedGroups = [];
            $emittedH4     = [];

            foreach ($tables as $tableIdx => $table) {
                // Verificar si hay encabezado de grupo antes de esta tabla
                $precedingGroupVal = $xpath->query('preceding::div[contains(@class, "fs-4")][1]', $table)->item(0);
                $precedingGroupLbl = $xpath->query('preceding::div[contains(@class, "text-muted") and contains(@class, "text-uppercase")][1]', $table)->item(0);

                if ($precedingGroupVal) {
                    $gVal = trim(preg_replace('/\s+/', ' ', $precedingGroupVal->textContent));
                    $gLbl = $precedingGroupLbl ? trim(preg_replace('/\s+/', ' ', $precedingGroupLbl->textContent)) : '';
                    $groupText = $gLbl !== '' ? "{$gLbl}: {$gVal}" : $gVal;

                    if (!empty($groupText) && !in_array($groupText, $emittedGroups, true)) {
                        $emittedGroups[] = $groupText;
                        if ($tableIdx > 0) {
                            $structuredRows[] = ['type' => 'empty', 'height' => 12, 'cells' => []];
                        }
                        $structuredRows[] = [
                            'type'   => 'group',
                            'height' => 22,
                            'cells'  => [
                                ['text' => $groupText, 'style' => 9, 'align' => 'left', 'is_num' => false],
                            ],
                        ];
                    }
                }

                // Verificar si hay subtítulo h4 de sección (ej. Resumen General)
                $precedingH4 = $xpath->query('preceding::h4[1]', $table)->item(0);
                if ($precedingH4) {
                    $h4Text = trim(preg_replace('/\s+/', ' ', $precedingH4->textContent));
                    if (!empty($h4Text) && !in_array($h4Text, $emittedH4, true) && stripos($h4Text, 'General') !== false) {
                        $emittedH4[] = $h4Text;
                        $structuredRows[] = ['type' => 'empty', 'height' => 14, 'cells' => []];
                        $structuredRows[] = [
                            'type'   => 'section',
                            'height' => 22,
                            'cells'  => [
                                ['text' => $h4Text, 'style' => 10, 'align' => 'left', 'is_num' => false],
                            ],
                        ];
                    }
                }

                // Detectar si la tabla completa es de resumen general
                $isGrandTotalTable = $xpath->query('.//tr[contains(@class, "border-primary")]', $table)->length > 0;

                // Procesar filas de la tabla
                $tableRows = $xpath->query('.//tr', $table);
                foreach ($tableRows as $tr) {
                    $parentName = $tr->parentNode ? strtolower($tr->parentNode->nodeName) : '';
                    $hasThOnly  = $xpath->query('.//td', $tr)->length === 0 && $xpath->query('.//th', $tr)->length > 0;
                    $isHeader   = ($parentName === 'thead') || $hasThOnly;
                    $isFooter   = ($parentName === 'tfoot');

                    $rowTextUpper = mb_strtoupper($tr->textContent, 'UTF-8');
                    $isGrand      = $isGrandTotalTable || str_contains($rowTextUpper, 'RESUMEN GENERAL') || str_contains($rowTextUpper, 'TOTAL GENERAL');
                    $isSubtotal   = $isFooter && !$isGrand;

                    $cells = $xpath->query('.//th | .//td', $tr);
                    $rowCells = [];

                    foreach ($cells as $cell) {
                        $rawText = trim(preg_replace('/\s+/', ' ', $cell->textContent));
                        $rawText = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $rawText);
                        $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $rawText);

                        // Detectar alineación de celda
                        $class = $cell->getAttribute('class');
                        $align = 'left';
                        if (str_contains($class, 'text-end')) {
                            $align = 'right';
                        } elseif (str_contains($class, 'text-center')) {
                            $align = 'center';
                        } elseif (preg_match('/^[\$€£]?\s*[\d,]+(\.\d+)?%?$/', $cleanText)) {
                            $align = 'right';
                        }

                        // Asignar índice de estilo en styles.xml
                        if ($isHeader) {
                            $style = ($align === 'right') ? 2 : (($align === 'center') ? 3 : 1);
                        } elseif ($isGrand) {
                            $style = ($align === 'right') ? 14 : 13;
                        } elseif ($isSubtotal) {
                            $style = ($align === 'right') ? 12 : 11;
                        } else {
                            $style = ($align === 'right') ? 5 : (($align === 'center') ? 6 : 4);
                        }

                        // Detección de valor numérico
                        $isNum  = false;
                        $numVal = 0;
                        if (is_numeric($cleanText) && !preg_match('/^0[0-9]+/', $cleanText)) {
                            $isNum  = true;
                            $numVal = str_contains($cleanText, '.') ? (float) $cleanText : (int) $cleanText;
                        }

                        $rowCells[] = [
                            'text'    => $cleanText,
                            'align'   => $align,
                            'style'   => $style,
                            'is_num'  => $isNum,
                            'num_val' => $numVal,
                        ];
                    }

                    $height = $isHeader ? 24 : ($isGrand ? 22 : ($isSubtotal ? 20 : 19));
                    $structuredRows[] = [
                        'type'   => $isHeader ? 'header' : ($isGrand ? 'grand' : ($isSubtotal ? 'subtotal' : 'data')),
                        'height' => $height,
                        'cells'  => $rowCells,
                    ];
                }
            }

            // Calcular anchos automáticos de columnas
            $colWidths = [];
            $maxColCount = 1;
            foreach ($structuredRows as $r) {
                $colCount = count($r['cells']);
                if ($colCount > $maxColCount) {
                    $maxColCount = $colCount;
                }
                foreach ($r['cells'] as $cIdx => $cell) {
                    if (in_array($r['type'], ['title', 'subtitle', 'group', 'section'], true)) {
                        continue;
                    }
                    $textLen = mb_strwidth($cell['text'], 'UTF-8');
                    $colWidths[$cIdx] = max($colWidths[$cIdx] ?? 12, min(50, $textLen + 4));
                }
            }

            for ($i = 0; $i < $maxColCount; $i++) {
                if (!isset($colWidths[$i])) {
                    $colWidths[$i] = 15;
                }
            }

            // Archivo temporal para volcar filas XML
            $tempSheetFile = tempnam(sys_get_temp_dir(), 'xlsx_hsh_');
            $sh = fopen($tempSheetFile, 'w');
            if ($sh === false) {
                return false;
            }

            $rowNum = 0;
            foreach ($structuredRows as $sRow) {
                $rowNum++;
                $rHeight = $sRow['height'];
                $rowXml  = "    <row r=\"{$rowNum}\" ht=\"{$rHeight}\" customHeight=\"1\">\n";

                foreach ($sRow['cells'] as $cIdx => $cell) {
                    $cellRef  = $toColName($cIdx) . $rowNum;
                    $styleIdx = $cell['style'];
                    $text     = $cell['text'];

                    if ($cell['is_num']) {
                        $rowXml .= "      <c r=\"{$cellRef}\" s=\"{$styleIdx}\"><v>{$cell['num_val']}</v></c>\n";
                    } elseif ($text === '') {
                        $rowXml .= "      <c r=\"{$cellRef}\" s=\"{$styleIdx}\"/>\n";
                    } else {
                        $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                        $rowXml .= "      <c r=\"{$cellRef}\" t=\"inlineStr\" s=\"{$styleIdx}\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>\n";
                    }
                }

                $rowXml .= "    </row>\n";
                fwrite($sh, $rowXml);
            }
            fclose($sh);

            // Ensamblar XML final de worksheet
            $finalSheetFile = tempnam(sys_get_temp_dir(), 'xlsx_hfsh_');
            $outFh = fopen($finalSheetFile, 'w');
            if ($outFh === false) {
                return false;
            }

            $sheetHeader = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
            $sheetHeader .= "<worksheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">\n";
            $sheetHeader .= "  <sheetFormatPr defaultRowHeight=\"19\" customHeight=\"1\"/>\n";
            $sheetHeader .= "  <cols>\n";
            foreach ($colWidths as $idx => $width) {
                $col1 = $idx + 1;
                $sheetHeader .= "    <col min=\"{$col1}\" max=\"{$col1}\" width=\"{$width}\" customWidth=\"1\"/>\n";
            }
            $sheetHeader .= "  </cols>\n";
            $sheetHeader .= "  <sheetData>\n";
            fwrite($outFh, $sheetHeader);

            $inFh = fopen($tempSheetFile, 'r');
            if ($inFh !== false) {
                while (!feof($inFh)) {
                    fwrite($outFh, fread($inFh, 65536));
                }
                fclose($inFh);
            }

            $sheetFooter = "  </sheetData>\n";
            $sheetFooter .= "</worksheet>";
            fwrite($outFh, $sheetFooter);
            fclose($outFh);

            // Generar paquete ZIP (.xlsx)
            $tempZipFile = tempnam(sys_get_temp_dir(), 'xlsx_hpkg_');

            $contentTypes = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<Types xmlns=\"http://schemas.openxmlformats.org/package/2006/content-types\">\n"
                . "  <Default Extension=\"rels\" ContentType=\"application/vnd.openxmlformats-package.relationships+xml\"/>\n"
                . "  <Default Extension=\"xml\" ContentType=\"application/xml\"/>\n"
                . "  <Override PartName=\"/xl/workbook.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml\"/>\n"
                . "  <Override PartName=\"/xl/worksheets/sheet1.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>\n"
                . "  <Override PartName=\"/xl/styles.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml\"/>\n"
                . "</Types>";

            $rels = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">\n"
                . "  <Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument\" Target=\"xl/workbook.xml\"/>\n"
                . "</Relationships>";

            $wbRels = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<Relationships xmlns=\"http://schemas.openxmlformats.org/package/2006/relationships\">\n"
                . "  <Relationship Id=\"rId1\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet1.xml\"/>\n"
                . "  <Relationship Id=\"rId2\" Type=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles\" Target=\"styles.xml\"/>\n"
                . "</Relationships>";

            $workbook = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<workbook xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" xmlns:r=\"http://schemas.openxmlformats.org/officeDocument/2006/relationships\">\n"
                . "  <workbookPr date1904=\"false\"/>\n"
                . "  <sheets>\n"
                . "    <sheet name=\"{$cleanSheetNameXml}\" sheetId=\"1\" r:id=\"rId1\"/>\n"
                . "  </sheets>\n"
                . "</workbook>";

            $styles = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n"
                . "<styleSheet xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\">\n"
                . "  <fonts count=\"6\">\n"
                . "    <font><sz val=\"{$fontSize}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "    <font><b/><sz val=\"{$fontSize}\"/><color rgb=\"{$headerColor}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "    <font><b/><sz val=\"{$fontSize}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "    <font><b/><sz val=\"14\"/><color rgb=\"{$titleColor}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "    <font><i/><sz val=\"9\"/><color rgb=\"FF555555\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "    <font><b/><sz val=\"11\"/><color rgb=\"{$titleColor}\"/><name val=\"{$fontName}\"/><family val=\"2\"/></font>\n"
                . "  </fonts>\n"
                . "  <fills count=\"6\">\n"
                . "    <fill><patternFill patternType=\"none\"/></fill>\n"
                . "    <fill><patternFill patternType=\"gray125\"/></fill>\n"
                . "    <fill><patternFill patternType=\"solid\"><fgColor rgb=\"{$headerBg}\"/></patternFill></fill>\n"
                . "    <fill><patternFill patternType=\"solid\"><fgColor rgb=\"{$subtotalBg}\"/></patternFill></fill>\n"
                . "    <fill><patternFill patternType=\"solid\"><fgColor rgb=\"{$groupBg}\"/></patternFill></fill>\n"
                . "    <fill><patternFill patternType=\"solid\"><fgColor rgb=\"{$grandBg}\"/></patternFill></fill>\n"
                . "  </fills>\n"
                . "  <borders count=\"4\">\n"
                . "    <border><left/><right/><top/><bottom/><diagonal/></border>\n"
                . "    <border>\n"
                . "      <left style=\"thin\"><color rgb=\"FFD9D9D9\"/></left>\n"
                . "      <right style=\"thin\"><color rgb=\"FFD9D9D9\"/></right>\n"
                . "      <top style=\"thin\"><color rgb=\"FFD9D9D9\"/></top>\n"
                . "      <bottom style=\"thin\"><color rgb=\"FFD9D9D9\"/></bottom>\n"
                . "    </border>\n"
                . "    <border>\n"
                . "      <top style=\"thin\"><color rgb=\"FFB0B0B0\"/></top>\n"
                . "      <bottom style=\"thin\"><color rgb=\"FFB0B0B0\"/></bottom>\n"
                . "    </border>\n"
                . "    <border>\n"
                . "      <top style=\"thin\"><color rgb=\"{$titleColor}\"/></top>\n"
                . "      <bottom style=\"double\"><color rgb=\"{$titleColor}\"/></bottom>\n"
                . "    </border>\n"
                . "  </borders>\n"
                . "  <cellStyleXfs count=\"1\">\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\"/>\n"
                . "  </cellStyleXfs>\n"
                . "  <cellXfs count=\"15\">\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"0\" xfId=\"0\"/>\n"
                . "    <xf numFmtId=\"0\" fontId=\"1\" fillId=\"2\" borderId=\"1\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"1\" fillId=\"2\" borderId=\"1\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"right\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"1\" fillId=\"2\" borderId=\"1\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"center\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"1\" xfId=\"0\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"1\" xfId=\"0\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"right\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"0\" fillId=\"0\" borderId=\"1\" xfId=\"0\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"center\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"3\" fillId=\"0\" borderId=\"0\" xfId=\"0\" applyFont=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"4\" fillId=\"0\" borderId=\"0\" xfId=\"0\" applyFont=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"5\" fillId=\"4\" borderId=\"1\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"5\" fillId=\"0\" borderId=\"0\" xfId=\"0\" applyFont=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"2\" fillId=\"3\" borderId=\"2\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"2\" fillId=\"3\" borderId=\"2\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"right\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"2\" fillId=\"5\" borderId=\"3\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"left\" vertical=\"center\"/></xf>\n"
                . "    <xf numFmtId=\"0\" fontId=\"2\" fillId=\"5\" borderId=\"3\" xfId=\"0\" applyFont=\"1\" applyFill=\"1\" applyBorder=\"1\" applyAlignment=\"1\"><alignment horizontal=\"right\" vertical=\"center\"/></xf>\n"
                . "  </cellXfs>\n"
                . "</styleSheet>";

            $entries = [
                '[Content_Types].xml'        => ['type' => 'string', 'content' => $contentTypes],
                '_rels/.rels'                => ['type' => 'string', 'content' => $rels],
                'xl/_rels/workbook.xml.rels' => ['type' => 'string', 'content' => $wbRels],
                'xl/workbook.xml'            => ['type' => 'string', 'content' => $workbook],
                'xl/styles.xml'              => ['type' => 'string', 'content' => $styles],
                'xl/worksheets/sheet1.xml'   => ['type' => 'file', 'content' => $finalSheetFile],
            ];

            if (!buildZipPackage($tempZipFile, $entries)) {
                log_message('error', 'htmlToXLSXFile: No se pudo empaquetar el archivo XLSX.');
                return false;
            }

            if ($download) {
                if (ob_get_level()) {
                    ob_end_clean();
                }

                $downloadName = basename($fileName);
                header('Content-Description: File Transfer');
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($tempZipFile));

                readfile($tempZipFile);
                @unlink($tempZipFile);
                exit;
            } else {
                $directory = dirname($fileName);
                if (!empty($directory) && $directory !== '.' && !is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }

                if (!copy($tempZipFile, $fileName)) {
                    return false;
                }

                return true;
            }
        } catch (\Throwable $e) {
            log_message('error', 'htmlToXLSXFile: ' . $e->getMessage());
            return false;
        } finally {
            if ($tempSheetFile && file_exists($tempSheetFile)) {
                @unlink($tempSheetFile);
            }
            if ($finalSheetFile && file_exists($finalSheetFile)) {
                @unlink($finalSheetFile);
            }
            if ($tempZipFile && file_exists($tempZipFile)) {
                @unlink($tempZipFile);
            }
        }
    }
}

if (!function_exists('htmlToExcelFile')) {
    /**
     * Alias de htmlToXLSXFile para mayor comodidad al generar archivos Excel (.xlsx) desde HTML.
     *
     * @see htmlToXLSXFile()
     */
    function htmlToExcelFile(
        string $html,
        string $fileName = 'reporte.xlsx',
        bool $download = true,
        string $sheetName = 'Reporte',
        array $options = []
    ): bool {
        return htmlToXLSXFile($html, $fileName, $download, $sheetName, $options);
    }
}

