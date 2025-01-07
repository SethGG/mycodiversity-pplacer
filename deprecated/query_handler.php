<?php
require_once 'Database.php';

class QueryHandler extends Database {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            $conditions = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'where_') === 0 && !empty($value)) {
                    // Remove 'where_' prefix to get the actual column name
                    $columnName = substr($key, 6); 
                    $conditions[$columnName] = $value;
                }
            }

            switch ($action) {
                case 'getOptions':
                    $column = $_POST['column'] ?? '';
                    $this->renderOptions($column, $conditions);
                    break;

                case 'updateQuery':
                    $this->updateQuery($conditions);
                    break;

                case 'executeQuery':
                    $this->executeQuery($conditions);
                    break;

                case 'rowCount':
                    $this->rowCount($conditions);
                    break;

                default:
                    echo "Invalid action!";
            }
        }
    }

    private function getZOTU($refseq) {
        $sql = "SELECT seq_zotu FROM \"RefSequence\" WHERE refsequence_pk = {$refseq}";

        $result = $this->select($sql);

    }

    public function renderOptions($column, $conditions = []) {
        // Sanitize the column name to prevent SQL injection
        $allowedColumns = ['country_geonames_continent', 'country_parent', 'country_geoname_pref_en'];
        if (!in_array($column, $allowedColumns)) {
            throw new Exception('Invalid column name');
        }

        $sql = "SELECT DISTINCT {$column} FROM \"Sample\"";

        // Process WHERE conditions if provided
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $whereColumn => $whereValue) {
                if (!in_array($whereColumn, $allowedColumns)) {
                    throw new Exception("Invalid WHERE column: {$whereColumn}");
                }
                $whereValue = htmlspecialchars($whereValue); // Sanitize value
                $whereClauses[] = "{$whereColumn} = '{$whereValue}'";
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        // Execute query
        $results = $this->select($sql);

        // Render options
        echo '<option value="">Select Option</option>';
        foreach ($results as $row) {
            echo '<option value="' . htmlspecialchars($row[$column]) . '">' 
                . htmlspecialchars($row[$column]) . '</option>';
        }
    }

    private function buildQuery($conditions = []) {
        // Sanitize the column name to prevent SQL injection
        $allowedColumns = ['country_geonames_continent', 'country_parent', 'country_geoname_pref_en'];

        $query = 'SELECT S.sra_sample, S.country_geoname_pref_en, C.refsequence_pk, R.seq_zotu
FROM "Sample" S
JOIN "Contain" C ON S.sample_pk = C.sample_pk
JOIN "RefSequence" R ON C.refsequence_pk = R.refsequence_pk';

        // Process WHERE conditions if provided
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $whereColumn => $whereValue) {
                if (!in_array($whereColumn, $allowedColumns)) {
                    throw new Exception("Invalid WHERE column: {$whereColumn}");
                }
                $whereValue = htmlspecialchars($whereValue); // Sanitize value
                $whereClauses[] = "{$whereColumn} = '{$whereValue}'";
            }
            $query .= '
WHERE ' . implode('
AND ', $whereClauses);
        }
        return $query;
    }

    public function updateQuery($conditions = []) {
        $query = $this->buildQuery($conditions);

        echo '<pre>' . $query . '</pre>';
    }

    private function executeQuery($conditions = []) {
        $query = $this->buildQuery($conditions);

        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $rowsPerPage = 30;
        $offset = ($page - 1) * $rowsPerPage;

        if (empty($query)) {
            echo "<p class='has-text-danger'>Error: Query cannot be empty!</p>";
            return;
        }

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
            foreach ($row as $key => $cell) {
                if ($key === 'refsequence_pk') {
                    echo '<td>
                        <button class="button is-small is-rounded is-link is-outlined"
                            hx-post="query_handler.php"
                            hx-target="#modal-content"
                            hx-vals=\'{
                                "action": "getZOTU",
                                "refseq": "' . htmlspecialchars($cell) . '"
                            }\'
                            hx-trigger="click"
                            hx-swap="innerHTML">
                            ' . htmlspecialchars($cell) . '
                        </button>
                    </td>';
                } else {
                    echo '<td>' . htmlspecialchars($cell) . '</td>';
                }
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
                    hx-include='#continentDropdown, #subregionDropdown, #countryDropdown'
                    hx-vals='{\"page\": " . ($page - 1) . ", \"action\": \"executeQuery\"}'>Previous</a>";
        } else {
            echo "<a class='pagination-previous' disabled>Previous</a>";
        }

        // Next Button
        if ($page < $totalPages) {
            echo "<a class='pagination-next' 
                    hx-post='query_handler.php' 
                    hx-target='#result' 
                    hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                    hx-vals='{\"page\": " . ($page + 1) . ", \"action\": \"executeQuery\"}'>Next</a>";
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
                        hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                        hx-vals='{\"page\": 1, \"action\": \"executeQuery\"}'>1</a></li>";
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
                            hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                            hx-vals='{\"page\": $i, \"action\": \"executeQuery\"}'>$i</a></li>";
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
                        hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                        hx-vals='{\"page\": $totalPages, \"action\": \"executeQuery\"}'>$totalPages</a></li>";
        }

        echo "</ul>";
        echo "</nav>";
        echo "</div>";
    }

    private function rowCount($conditions = []) {
        $query = $this->buildQuery($conditions);

        if (empty($query)) {
            echo "<p class='has-text-danger'>Error: Query cannot be empty!</p>";
            return;
        }

        $rowCount = $this->row_count($query);
        echo "<p class='has-text-info'>Row Count: <strong>{$rowCount}</strong></p>";
    }
}

$handler = new QueryHandler();
$handler->handleRequest();
