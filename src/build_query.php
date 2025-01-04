<?php

function buildQuery($conditions = [],
    $orderBy = ['refsequence_pk'],
    $select = [],
    $distinct = [],
    $count = [],
    $groupBy = []
    ) {
    $tableMapping = [
        'country_geonames_continent' => 'SP',
        'country_parent' => 'SP',
        'country_geoname_pref_en' => 'SP',
        'study_bioproject_id' => 'ST',
        'biosample_id' => 'SP',
        'study_sra_id' => 'ST',
        'sra_sample' => 'SP',
        "env_feature" => "SP",
        "envo_biome_term" => "SP",
        "env_material" => "SP",
        "phylum_name" => "RT",
        "class_name" => "RT",
        "order_name" => "RT",
        "family_name" => "RT",
        "genus_name" => "RT",
        "species_name" => "RT",
        "sh_unite_id" => "RT",
        "refsequence_pk" => "RS",
        "seq_zotu" => "RS"
    ];

    // Build SELECT clause
    $mappedSelect = [];

    // Add regular SELECT columns first
    foreach ($select as $column) {
        $tableAlias = $tableMapping[$column] ?? null;
        $prefixedColumn = $tableAlias ? "{$tableAlias}." . htmlspecialchars($column) : htmlspecialchars($column);

        // Check if column is in DISTINCT array
        if (in_array($column, $distinct)) {
            $mappedSelect[] = "DISTINCT {$prefixedColumn}";
        } else {
            $mappedSelect[] = $prefixedColumn;
        }
    }

    // Add COUNT columns after SELECT columns
    if (!empty($count)) {
        foreach ($count as $column) {
            $tableAlias = $tableMapping[$column] ?? null;
            $prefixedColumn = $tableAlias ? "{$tableAlias}." . htmlspecialchars($column) : htmlspecialchars($column);

            // Check if column is in DISTINCT array
            if (in_array($column, $distinct)) {
                $mappedSelect[] = "COUNT(DISTINCT {$prefixedColumn}) AS count_{$column}";
            } else {
                $mappedSelect[] = "COUNT({$prefixedColumn}) AS count_{$column}";
            }
        }
    }

    $selectClause = implode(', ', array_unique($mappedSelect));

    // Base query
    $base_query = <<<SQL
    SELECT {$selectClause}
    FROM "Sample" SP
    JOIN "Contain" CN ON SP.sample_pk = CN.sample_pk
    JOIN "RefSequence" RS ON CN.refsequence_pk = RS.refsequence_pk
    JOIN "Include" IC ON SP.sample_pk = IC.sample_pk
    JOIN "Study" ST ON IC.study_pk = ST.study_pk
    JOIN "AssignTaxa" AX ON RS.refsequence_pk = AX.refsequence_pk
    JOIN "RefTaxonomicDB" RT ON AX.refsequence_taxonomic_pk = RT.refsequence_taxonomic_pk
    SQL;

    // Process WHERE conditions if provided
    if (!empty($conditions)) {
        $whereClauses = [];
        foreach ($conditions as $whereColumn => $whereValue) {
            $tableAlias = $tableMapping[$whereColumn] ?? null;
            if ($tableAlias) {
                $whereValue = htmlspecialchars($whereValue); // Sanitize value
                $whereClauses[] = "{$tableAlias}.{$whereColumn} = '{$whereValue}'";
            } else {
                $whereClauses[] = "{$whereColumn} = '{$whereValue}'";
            }
        }
        $base_query .= '
WHERE ' . implode('
AND ', $whereClauses);
    }

    // Process GROUP BY clause if provided
    if (!empty($groupBy)) {
        $groupClauses = [];
        foreach ($groupBy as $groupColumn) {
            $tableAlias = $tableMapping[$groupColumn] ?? null;
            if ($tableAlias) {
                $groupClauses[] = "{$tableAlias}.{$groupColumn}";
            } else {
                $groupClauses[] = $groupColumn;
            }
        }
        $base_query .= '
GROUP BY ' . implode(', ', $groupClauses);
    }

    // Process ORDER BY clause if provided
    if (!empty($orderBy)) {
        $orderClauses = [];
        if (is_array($orderBy)) {
            foreach ($orderBy as $orderColumn) {
                $tableAlias = $tableMapping[$orderColumn] ?? null;
                if ($tableAlias) {
                    $orderClauses[] = "{$tableAlias}.{$orderColumn}";
                } else {
                    $orderClauses[] = $orderColumn;
                }
            }
        } else {
            $tableAlias = $tableMapping[$orderBy] ?? null;
            if ($tableAlias) {
                $orderClauses[] = "{$tableAlias}.{$orderBy}";
            } else {
                $orderClauses[] = $orderBy;
            }
        }
        $base_query .= '
ORDER BY ' . implode(', ', $orderClauses);
    }

    return $base_query;
}
?>
