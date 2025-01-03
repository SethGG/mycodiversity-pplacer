<?php

require_once '../../src/get_conditions.php';
require_once '../../src/query_table.php';
require_once '../../src/build_query.php';

$conditions = getConditions();
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$rowsPerPage = 3;
list($pageResults, $totalRows) = modelQueryTable($conditions, $page, $rowsPerPage);
$view = viewQueryTable($pageResults, $totalRows, $page, $rowsPerPage);

echo $view;

?>