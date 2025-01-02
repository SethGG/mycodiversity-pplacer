<?php

function getConditions() {
    $conditions = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'where_') === 0 && !empty($value)) {
            // Remove 'where_' prefix to get the actual column name
            $columnName = substr($key, 6); 
            $conditions[$columnName] = $value;
        }
    }
    return $conditions;
}

?>