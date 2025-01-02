<?php

require_once '../../src/get_conditions.php';
require_once '../../src/build_query.php';
require_once '../../src/base_query.php';
require_once '../../src/query_preview.php';

$conditions = getConditions();
$query = buildQuery($base_query, $conditions);
$view = viewQueryPreview($query);

echo $view;

?>