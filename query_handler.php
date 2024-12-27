<?php
require_once 'Database.php';

class QueryHandler extends Database {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query']) && isset($_POST['action'])) {
            $query = $_POST['query'];
            $action = $_POST['action'];

            if (empty($query)) {
                echo "<p class='has-text-danger'>Error: Query cannot be empty!</p>";
                return;
            }

            try {
                if ($action === 'execute') {
                    $results = $this->select($query);
                    if (empty($results)) {
                        echo "<p class='has-text-warning'>No results found.</p>";
                        return;
                    }

                    echo "<table class='table is-striped is-hoverable is-fullwidth'>";
                    echo "<thead><tr>";

                    // Print table headers
                    foreach (array_keys($results[0]) as $header) {
                        echo "<th>{$header}</th>";
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
