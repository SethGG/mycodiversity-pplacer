<?php

function buildQuery($conditions = [], $select = ['*'], $distinct = false, $count = false) {
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
        "species_name" => "RT"
    ];

    // Build SELECT clause
    if ($count) {
        $selectClause = 'COUNT(*)';
    } else {
        $mappedSelect = [];
        foreach ($select as $column) {
            if ($column === '*') {
                $mappedSelect[] = '*';
            } else {
                $tableAlias = $tableMapping[$column] ?? null;
                if ($tableAlias) {
                    $mappedSelect[] = "{$tableAlias}." . htmlspecialchars($column);
                } else {
                    // If no mapping is found, include column without alias
                    $mappedSelect[] = htmlspecialchars($column);
                }
            }
        }
        $selectClause = ($distinct ? 'DISTINCT ' : '') . implode(', ', $mappedSelect);
    }

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
                // If no mapping exists, use column as is
                $whereClauses[] = "{$whereColumn} = '{$whereValue}'";
            }
        }
        $base_query .= '
WHERE ' . implode('
AND ', $whereClauses);
    }

    return $base_query;
}


?>