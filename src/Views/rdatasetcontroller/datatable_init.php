<?php
$sPaginationType = "numbers";
$bProcessing     = true;
$responsive      = true;
$bFilter         = true;

if (!isset($sortingDir)) {
    $sortingDir = 'asc';
}

$aaSorting   = ($sortingField >= 0) ? "[[$sortingField, '$sortingDir']]" : "[]";
$sAjaxSource = site_url($clase . '/getAjaxGridData');

$oLanguage = [
    "processing"   => lang('Ragnos.Ragnos_processing'),
    "lengthMenu"   => lang('Ragnos.Ragnos_show_n_records'),
    "zeroRecords"  => lang('Ragnos.Ragnos_no_records_found'),
    "info"         => lang('Ragnos.Ragnos_showing_from_to'),
    "infoEmpty"    => lang('Ragnos.Ragnos_info_empty'),
    "infoFiltered" => "",
    "search"       => lang('Ragnos.Ragnos_search'),
    "paginate"     => [
        "first"    => lang('Ragnos.Ragnos_first'),
        "previous" => lang('Ragnos.Ragnos_prior'),
        "next"     => lang('Ragnos.Ragnos_next'),
        "last"     => lang('Ragnos.Ragnos_last')
    ]
];

$olanguage = json_encode($oLanguage);

$fnServerData2 = "fnData2{$controllerUniqueID}";

$initialSearch = getInputValue('sSearch', '');
$sSearch      = json_encode(is_scalar($initialSearch) ? (string) $initialSearch : '');

echo <<<EOT
(() => {
    const tableElement = document.getElementById('{$controllerUniqueID}_table');
    const dataTable = new DataTable(tableElement, {
        pagingType:  'numbers',
        responsive:  true,
        processing:  true,
        serverSide:  true,
        autoWidth:   false,
        deferRender: true,
        select:      { style: 'single', selector: 'tbody tr' },
        order: $aaSorting,
        ajax: $fnServerData2,
        search: {
            return: true,
            search: $sSearch
        },
        language: $olanguage,
    });
    tableElement.ragnosDataTable = dataTable;
    aplicarDebounceABusqueda(dataTable, 400);
})();
EOT;
