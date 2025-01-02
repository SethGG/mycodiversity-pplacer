<?php

require_once '../../src/get_options.php';
require_once '../../src/get_conditions.php';

$conditions = getConditions();

$allowedColumns = ['country_geonames_continent', 'country_parent', 'country_geoname_pref_en'];

$view = '';
foreach($allowedColumns as $column) {
    $results = modelGetOptions($column, $conditions);
    $view .= viewGetOptions($results, $column, $conditions);
}

echo $view;

header("HX-Trigger:reloadQueryPreview");
?>