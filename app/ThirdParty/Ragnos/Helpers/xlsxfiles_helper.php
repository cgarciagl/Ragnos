<?php

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
        if (empty($results) || !class_exists('\ZipArchive')) {
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
            $zip = new \ZipArchive();
            if ($zip->open($tempZipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return false;
            }

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

            $zip->addFromString('[Content_Types].xml', $contentTypes);
            $zip->addFromString('_rels/.rels', $rels);
            $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
            $zip->addFromString('xl/workbook.xml', $workbook);
            $zip->addFromString('xl/styles.xml', $styles);
            $zip->addFile($finalSheetFile, 'xl/worksheets/sheet1.xml');
            $zip->close();

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
