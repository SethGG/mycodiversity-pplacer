<?php

require_once 'get_conditions.php';
require_once 'build_query.php';

function modelGetOptions($db, $column, $conditions) {
    $query = buildQuery($conditions, [], [$column], [$column]);
    $results = $db->select($query);

    return $results;
}

function viewGetOptions($results, $column, $conditions) {
    $selectedValue = $conditions[$column] ?? '';

    // Check if selected value exists in the results
    $foundSelected = false;
    foreach ($results as $row) {
        if ($row[$column] === $selectedValue) {
            $foundSelected = true;
            break;
        }
    }

    // Show static box only if selected value exists in results
    if (!empty($selectedValue) && $foundSelected) {
        $value = htmlspecialchars($selectedValue);
        return <<<HTML
        <button id="select_wrapper_{$column}"
            class="button is-small is-danger is-outlined is-fullwidth is-justify-content-flex-start px-3"
            hx-post="partials/get_options.php"
            hx-include=".where_filters"
            hx-swap="none"
            hx-swap-oob="true"
            name="where_{$column}"
            value=""
            title="Cancel Selection">
            {$value}
            <input type="hidden" name="where_{$column}" value="{$value}" class="where_filters">
        </button>
        HTML;
    }

    // Otherwise, show the dropdown
    $view = "<div id=\"select_wrapper_{$column}\" class=\"select is-small is-fullwidth\" hx-swap-oob=\"true\">
        <select
            class=\"where_filters px-3\"
            name=\"where_{$column}\"
            hx-post=\"partials/get_options.php\"
            hx-trigger=\"change\"
            hx-include=\".where_filters\"
            hx-swap=\"none\">
            <option selected=\"selected\" value=\"\">Select Option</option>";

    foreach ($results as $row) {
        $value = htmlspecialchars($row[$column]);
        $isSelected = ($value === $selectedValue) ? 'selected="selected"' : '';
        $view .= "<option {$isSelected} value=\"{$value}\">{$value}</option>";
    }

    $view .= "</select></div>";
    return $view;
}


?>