<?php
require_once 'Database.php';

class QueryHandler extends Database {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query']) && isset($_POST['action'])) {
            $query = $_POST['query'];
            $action = $_POST['action'];
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $rowsPerPage = 30;
            $offset = ($page - 1) * $rowsPerPage;

            if (empty($query)) {
                echo "<p class='has-text-danger'>Error: Query cannot be empty!</p>";
                return;
            }

            try {
                if ($action === 'execute') {
                    $paginatedQuery = $query . " LIMIT {$rowsPerPage} OFFSET {$offset}";
                    $results = $this->select($paginatedQuery);

                    if (empty($results)) {
                        echo "<p class='has-text-warning'>No results found.</p>";
                        return;
                    }
                    
                    // Print table
                    echo "<div style='overflow-x: auto; max-width: 100%; border: 1px solid #ddd;'>";
                    echo "<table class='table is-striped is-hoverable is-fullwidth'>";
                    echo "<thead><tr>";

                    // Print table headers
                    foreach (array_keys($results[0]) as $header) {
                        echo "<th style='position: sticky; top: 0; background-color: #f5f5f5;'>{$header}</th>";
                    }
                    echo "</tr></thead><tbody>";

                    // Print table rows
                    foreach ($results as $row) {
                        echo "<tr>";
                        foreach ($row as $cell) {
                            echo "<td>{$cell}</td>";
                        }
                        echo "</tr>";
                    }

                    echo "</tbody></table>";
                    echo "</div>";

                    // Fetch total rows for pagination controls
                    $totalRows = $this->row_count($query);
                    $totalPages = ceil($totalRows / $rowsPerPage);

                    // Pagination controls
                    echo "<div class='pagination is-centered' style='margin-top: 1em;'>";
                    echo "<nav class='pagination' role='navigation' aria-label='pagination'>";

                    // Previous Button
                    if ($page > 1) {
                        echo "<a class='pagination-previous' 
                                hx-post='query_handler.php' 
                                hx-target='#result' 
                                hx-include='#query' 
                                hx-vals='{\"page\": " . ($page - 1) . ", \"action\": \"execute\"}'>Previous</a>";
                    } else {
                        echo "<a class='pagination-previous' disabled>Previous</a>";
                    }

                    // Next Button
                    if ($page < $totalPages) {
                        echo "<a class='pagination-next' 
                                hx-post='query_handler.php' 
                                hx-target='#result' 
                                hx-include='#query' 
                                hx-vals='{\"page\": " . ($page + 1) . ", \"action\": \"execute\"}'>Next</a>";
                    } else {
                        echo "<a class='pagination-next' disabled>Next</a>";
                    }

                    // Pagination List
                    echo "<ul class='pagination-list'>";

                    // First Page
                    if ($page > 2) {
                        echo "<li><a class='pagination-link' 
                                    hx-post='query_handler.php' 
                                    hx-target='#result' 
                                    hx-include='#query' 
                                    hx-vals='{\"page\": 1, \"action\": \"execute\"}'>1</a></li>";
                        if ($page > 3) {
                            echo "<li><span class='pagination-ellipsis'>&hellip;</span></li>";
                        }
                    }

                    // Current Page (and surrounding pages)
                    for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++) {
                        if ($i == $page) {
                            echo "<li><a class='pagination-link is-current' 
                                        aria-current='page'>$i</a></li>";
                        } else {
                            echo "<li><a class='pagination-link' 
                                        hx-post='query_handler.php' 
                                        hx-target='#result' 
                                        hx-include='#query' 
                                        hx-vals='{\"page\": $i, \"action\": \"execute\"}'>$i</a></li>";
                        }
                    }

                    // Last Page
                    if ($page < $totalPages - 1) {
                        if ($page < $totalPages - 2) {
                            echo "<li><span class='pagination-ellipsis'>&hellip;</span></li>";
                        }
                        echo "<li><a class='pagination-link' 
                                    hx-post='query_handler.php' 
                                    hx-target='#result' 
                                    hx-include='#query' 
                                    hx-vals='{\"page\": $totalPages, \"action\": \"execute\"}'>$totalPages</a></li>";
                    }

                    echo "</ul>";
                    echo "</nav>";
                    echo "</div>";

                } elseif ($action === 'rowcount') {
                    $rowCount = $this->row_count($query);
                    echo "<p class='has-text-info'>Row Count: <strong>{$rowCount}</strong></p>";
                } else {
                    echo "<p class='has-text-danger'>Invalid action!</p>";
                }
            } catch (Exception $e) {
                echo "<p class='has-text-danger'>Error: {$e->getMessage()}</p>";
            }
        } else {
            echo "<p class='has-text-danger'>Invalid request!</p>";
        }
    }
}

$handler = new QueryHandler();
$handler->handleRequest();
