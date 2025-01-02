<?php

function buildQuery($base_query, $conditions = []) {
    // Sanitize the column name to prevent SQL injection
    $allowedColumns = ['country_geonames_continent', 'country_parent', 'country_geoname_pref_en'];

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
        $base_query .= '
WHERE ' . implode('
AND ', $whereClauses);
    }
    return $base_query;
}

?>