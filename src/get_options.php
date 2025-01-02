<?php

require_once 'get_conditions.php';
require_once 'build_query.php';
require_once 'database.php';

function modelGetOptions($column, $conditions) {
    // Sanitize the column name to prevent SQL injection
    $allowedColumns = ['country_geonames_continent', 'country_parent', 'country_geoname_pref_en'];
    if (!in_array($column, $allowedColumns)) {
        throw new Exception('Invalid column name');
    }

    $base_query = "SELECT DISTINCT {$column} FROM \"Sample\"";
    $query = buildQuery($base_query, $conditions);

    $db = new Database();
    $results = $db->select($query);

    return $results;
}

function viewGetOptions($results, $column, $conditions) {
    $selectedValue = $conditions[$column] ?? '';

    $view = "<select id=\"select_{$column}\"
        class=\"where_filters\"
        name=\"where_{$column}\"
        hx-post=\"partials/get_options.php\"
        hx-trigger=\"change\"
        hx-include=\".where_filters\"
        hx-swap=\"none\"
        hx-swap-oob=\"true\"
        >";

    // Track if we found the selected value in the results
    $foundSelected = false;
    
    $options = '';
    foreach ($results as $row) {
        $value = htmlspecialchars($row[$column]);
        $isSelected = ($value === $selectedValue) ? 'selected="selected"' : '';

        if ($isSelected) {
            $foundSelected = true; // Mark as found
        }

        $options .= "<option {$isSelected} value=\"{$value}\">{$value}</option>";
    }

    // Default "Select Option"
    $defaultSelected = !$foundSelected ? 'selected="selected"' : '';
    $defaultMessage = !$foundSelected ? 'Select Option' : 'Cancel Selection';
    $view .= "<option {$defaultSelected} value=\"\">{$defaultMessage}</option>" . $options;

    $view .= "</select>";
    return $view;
}

?>