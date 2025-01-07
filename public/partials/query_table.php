<?php

require_once '../../src/get_conditions.php';
require_once '../../src/query_table.php';
require_once '../../src/build_query.php';
require_once '../../src/database.php';


$db = new Database();
$conditions = getConditions();
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$rowsPerPage = 15;
list($pageResults, $totalRows) = modelQueryTable($db, $conditions, $page, $rowsPerPage);
$view1 = viewQueryTable($pageResults, $totalRows, $page, $rowsPerPage);

$statResults = modelQueryStats($db, $conditions);
$view2 = viewQueryStats($statResults);

$view = <<<HTML
    <div id="result" class="columns">
        <div class="column">
            {$view2}
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Phylogenetic Placement</p>
                </header>
                <div class="card-content p-4">
                    <div class="content">
                    <p>Select a Phylogenetic reference tree to place the sequences on:</p>
                    </div>
                    <div class="select is-small">
                        <select>
                            <option>l0.2_s3_4_1500_o1.0_a0_constr_localpair</option>
                        </select>
                    </div>
                </div>
                <footer class="card-footer">
                    <a class="card-footer-item" hx-post="partials/start_pplacer.php"
                        hx-target="body"
                        hx-swap="beforeend"
                        hx-include=".where_filters">Execute Placement</a>
                </footer>
            </div>
        </div>
        <div class="column">{$view1}</div>
    </div>
HTML;


echo $view;

?>