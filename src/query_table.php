<?php

require_once 'database.php';

function modelQueryTable($query, $page, $rowsPerPage) {
    $offset = ($page - 1) * $rowsPerPage;
    $paginatedQuery = $query . " LIMIT {$rowsPerPage} OFFSET {$offset}";

    $db = new Database();
    $pageResults = $db->select($paginatedQuery);
    $totalRows = $db->row_count($query);

    return [$pageResults, $totalRows];
}

function viewQueryTable($pageResults, $totalRows, $page, $rowsPerPage) {
    if (empty($pageResults)) {
        $view = "<p class='has-text-warning'>No results found.</p>";
        return $view;
    }

    // Print table
    $view =
        "<div style='overflow-x: auto; max-width: 100%; border: 1px solid #ddd;'>
        <table class='table is-striped is-hoverable is-fullwidth'>
        <thead><tr>";

    // Print table headers
    foreach (array_keys($pageResults[0]) as $header) {
        $view .= "<th style='position: sticky; top: 0; background-color: #f5f5f5;'>{$header}</th>";
    }
    $view .= "</tr></thead><tbody>";

    // Print table rows
    foreach ($pageResults as $row) {
        $view .= "<tr>";
        foreach ($row as $key => $cell) {
            if ($key === 'refsequence_pk') {
                $view .= '<td>
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
                $view .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
        }
        $view .= "</tr>";
    }

    $view .= 
        "</tbody></table>
        </div>";


    // Pagination controls
    $totalPages = ceil($totalRows / $rowsPerPage);
    $view .=
        "<div class='pagination is-centered' style='margin-top: 1em;'>
        <nav class='pagination' role='navigation' aria-label='pagination'>";

    // Previous Button
    if ($page > 1) {
        $view .= "<a class='pagination-previous' 
                hx-post='query_handler.php' 
                hx-target='#result' 
                hx-include='#continentDropdown, #subregionDropdown, #countryDropdown'
                hx-vals='{\"page\": " . ($page - 1) . ", \"action\": \"executeQuery\"}'>Previous</a>";
    } else {
        $view .= "<a class='pagination-previous' disabled>Previous</a>";
    }

    // Next Button
    if ($page < $totalPages) {
        $view .= "<a class='pagination-next' 
                hx-post='query_handler.php' 
                hx-target='#result' 
                hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                hx-vals='{\"page\": " . ($page + 1) . ", \"action\": \"executeQuery\"}'>Next</a>";
    } else {
        $view .= "<a class='pagination-next' disabled>Next</a>";
    }

    // Pagination List
    $view .= "<ul class='pagination-list'>";

    // First Page
    if ($page > 2) {
        $view .= "<li><a class='pagination-link' 
                    hx-post='query_handler.php' 
                    hx-target='#result' 
                    hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                    hx-vals='{\"page\": 1, \"action\": \"executeQuery\"}'>1</a></li>";
        if ($page > 3) {
            $view .= "<li><span class='pagination-ellipsis'>&hellip;</span></li>";
        }
    }

    // Current Page (and surrounding pages)
    for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++) {
        if ($i == $page) {
            $view .= "<li><a class='pagination-link is-current' 
                        aria-current='page'>$i</a></li>";
        } else {
            $view .= "<li><a class='pagination-link' 
                        hx-post='query_handler.php' 
                        hx-target='#result' 
                        hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                        hx-vals='{\"page\": $i, \"action\": \"executeQuery\"}'>$i</a></li>";
        }
    }

    // Last Page
    if ($page < $totalPages - 1) {
        if ($page < $totalPages - 2) {
            $view .= "<li><span class='pagination-ellipsis'>&hellip;</span></li>";
        }
        $view .= "<li><a class='pagination-link' 
                    hx-post='query_handler.php' 
                    hx-target='#result' 
                    hx-include='#continentDropdown, #subregionDropdown, #countryDropdown' 
                    hx-vals='{\"page\": $totalPages, \"action\": \"executeQuery\"}'>$totalPages</a></li>";
    }

    $view .=
        "</ul>
        </nav>
        </div>";

    return $view;
}

?>