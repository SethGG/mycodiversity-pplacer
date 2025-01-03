<?php

require_once '../../src/get_options.php';
require_once '../../src/get_conditions.php';
require_once '../../src/build_query.php';
require_once '../../src/query_preview.php';
require_once '../../src/database.php';

$conditions = getConditions();

$filterColumns = [
    'country_geonames_continent',
    'country_parent',
    'country_geoname_pref_en',
    'study_bioproject_id',
    'biosample_id',
    'study_sra_id',
    'sra_sample',
    'env_feature',
    'envo_biome_term',
    'env_material',
    'phylum_name',
    'class_name',
    'order_name',
    'family_name',
    'genus_name',
    'species_name'
];

$db = new Database();

$view = '';
foreach($filterColumns as $column) {
    $results = modelGetOptions($db, $column, $conditions);
    $view .= viewGetOptions($results, $column, $conditions);
}

$query = buildQuery($conditions);
$view .= viewQueryPreview($query);

echo $view;
?>