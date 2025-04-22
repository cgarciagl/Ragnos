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
    "sProcessing"   => lang('Ragnos.Ragnos_processing'),
    "sLengthMenu"   => lang('Ragnos.Ragnos_show_n_records'),
    "sZeroRecords"  => lang('Ragnos.Ragnos_no_records_found'),
    "sInfo"         => lang('Ragnos.Ragnos_showing_from_to'),
    "sInfoEmpty"    => lang('Ragnos.Ragnos_info_empty'),
    "sInfoFiltered" => "",
    "sInfoPostFix"  => "",
    "sSearch"       => lang('Ragnos.Ragnos_search'),
    "sUrl"          => "",
    "oPaginate"     => [
        "sFirst"    => lang('Ragnos.Ragnos_first'),
        "sPrevious" => lang('Ragnos.Ragnos_prior'),
        "sNext"     => lang('Ragnos.Ragnos_next'),
        "sLast"     => lang('Ragnos.Ragnos_last')
    ]
];

$olanguage = json_encode($oLanguage);

$fnServerData2 = "fnData2{$controllerUniqueID}";

echo "var tabla = $('#{$controllerUniqueID}_table');";

//si en el post viene la variable RagnosPreSearch, es porque se hizo una busqueda
//y se debe cargar la tabla con los datos filtrados
if (request()->getPost('RagnosPreSearch')) {
    $RagnosPreSearch = request()->getPost('RagnosPreSearch');
    $RagnosPreSearch = json_encode($RagnosPreSearch);
} else {
    $RagnosPreSearch = 'null';
}


echo <<<EOT
tabla.DataTable({
    pagingType: 'numbers',
    responsive: true,
    processing: true,
    serverSide: true,
    order: $aaSorting,
    ajax: $fnServerData2,
    search: {
        return: true,
        search: $RagnosPreSearch
    },
    oLanguage: $olanguage,
});
EOT;