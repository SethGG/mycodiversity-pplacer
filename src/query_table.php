<?php

require_once 'build_query.php';

function modelQueryStats($db, $conditions) {
    $statColumns = [
        'refsequence_pk' => 'Total Sequences', 
        'biosample_id' => 'Total BioSamples',
        'sra_sample' => 'Total SRA Samples'
    ];
    $statResults = [];
    foreach ($statColumns as $column => $name) {
        $query = buildQuery($conditions,
            [],
            [$column],
            [$column]
        );
        $totalRows = $db->row_count($query);
        $statResults[$name] = $totalRows;
    }
    return $statResults;
}

function viewQueryStats($statResults) {
    $view = <<<HTML
    <div class="box has-text-centered">
        <h3 class="title is-5">Query Statistics</h3>
        <div>
HTML;

    foreach ($statResults as $label => $value) {
        $view .= <<<HTML
        <div class="media mb-4">
            <div class="media-content">
                <p class="has-text-weight-medium">{$label}</p>
                <p class="is-size-4 has-text-primary">{$value}</p>
            </div>
        </div>
HTML;
    }

    $view .= <<<HTML
        </div>
    </div>
HTML;

    return $view;
}

function modelQueryTable($db, $conditions, $page, $rowsPerPage) {
    $query = buildQuery($conditions,
        ['refsequence_pk'],
        ['refsequence_pk', 'sh_unite_id', 'phylum_name', 'species_name'],
        ['biosample_id', 'sra_sample'],
        ['biosample_id', 'sra_sample'],
        ['refsequence_pk', 'sh_unite_id', 'phylum_name', 'species_name']
    );

    $offset = ($page - 1) * $rowsPerPage;
    $paginatedQuery = $query . " LIMIT {$rowsPerPage} OFFSET {$offset}";

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
        "<div class='card' style='overflow-x: auto; max-width: 100%; border: 1px solid #ddd;'>
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
                        name="refseq"
                        value="' . $cell . '"
                        hx-post="partials/get_refseq.php"
                        hx-target="body"
                        hx-swap="beforeend"
                        >
                        ' . htmlspecialchars($cell) . '
                    </button>
                </td>';
            } elseif (str_starts_with($key, 'count_')) {
                $view .= '<td class="is-justify-content-center">
                    <button class="button is-small is-rounded is-link is-outlined" style="width:5rem;">
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
                hx-post='partials/query_table.php' 
                hx-target='#result' 
                hx-include='.where_filters'
                hx-vals='{\"page\": " . ($page - 1) . "}'>Previous</a>";
    } else {
        $view .= "<a class='pagination-previous' disabled>Previous</a>";
    }

    // Next Button
    if ($page < $totalPages) {
        $view .= "<a class='pagination-previous' 
                hx-post='partials/query_table.php' 
                hx-target='#result' 
                hx-include='.where_filters'
                hx-vals='{\"page\": " . ($page + 1) . "}'>Next</a>";
    } else {
        $view .= "<a class='pagination-next' disabled>Next</a>";
    }

    // Pagination List
    $view .= "<ul class='pagination-list'>";

    // First Page
    if ($page > 2) {
        $view .= "<li><a class='pagination-previous' 
                hx-post='partials/query_table.php' 
                hx-target='#result' 
                hx-include='.where_filters'
                hx-vals='{\"page\": 1}'>1</a></li>";
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
            $view .= "<li><a class='pagination-previous' 
                hx-post='partials/query_table.php' 
                hx-target='#result' 
                hx-include='.where_filters'
                hx-vals='{\"page\": $i}'>$i</a></li>";
        }
    }

    // Last Page
    if ($page < $totalPages - 1) {
        if ($page < $totalPages - 2) {
            $view .= "<li><span class='pagination-ellipsis'>&hellip;</span></li>";
        }
        $view .= "<li><a class='pagination-previous' 
                hx-post='partials/query_table.php' 
                hx-target='#result' 
                hx-include='.where_filters'
                hx-vals='{\"page\": $totalPages}'>$totalPages</a></li>";
    }

    $view .=
        "</ul>
        </nav>
        </div>";

    return $view;
}

?>