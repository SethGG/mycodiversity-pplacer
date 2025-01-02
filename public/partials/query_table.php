<?php

require_once '../../src/get_conditions.php';
require_once '../../src/query_table.php';
require_once '../../src/build_query.php';
require_once '../../src/base_query.php';

$conditions = getConditions();
$query = buildQuery($base_query, $conditions);
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$rowsPerPage = 20;
list($pageResults, $totalRows) = modelQueryTable($query, $page, $rowsPerPage);
$view = viewQueryTable($pageResults, $totalRows, $page, $rowsPerPage);

echo $view;

?>