<?php
    require_once '../src/get_options.php';
    require_once '../src/database.php';
    require_once '../src/build_query.php';
    require_once '../src/query_preview.php';
    $db = new Database();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDDB4 Query Builder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.2/css/bulma.min.css">
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <script src="https://unpkg.com/htmx-ext-sse@2.2.2/sse.js"></script>
    <style>
        .isDisabled {
        color: currentColor;
        cursor: not-allowed;
        opacity: 0.5;
        text-decoration: none;
        }
    </style>
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">MDDB4 Query Builder</h1>
            <!-- Sample Filters -->
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Sample Filters</p>
                </header>
                <div class="card-content p-4">
                    <div class="columns">
                    <?php
                        $geoFilters = [
                            "study_bioproject_id" => "BioProject",
                            "biosample_id" => "BioSample",
                            "study_sra_id" => "SRA Study",
                            "sra_sample" => "SRA Sample"
                        ];                   
                        foreach ($geoFilters as $column => $label) {
                            echo "<div class='column'>";
                            echo "<label class='label has-text-weight-medium'>{$label}</label>";
                            $results = modelGetOptions($db, $column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view;
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="columns">
            <!-- Geographical Filters -->
            <div class="column is-half">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Geographical Filters</p>
                </header>
                <div class="card-content p-4">
                    <div class="columns">
                    <?php
                        $geoFilters = [
                            "country_geonames_continent" => "Continent",
                            "country_parent" => "Subregion",
                            "country_geoname_pref_en" => "Country"
                        ];
                        foreach ($geoFilters as $column => $label) {
                            echo "<div class='column'>";
                            echo "<label class='label has-text-weight-medium'>{$label}</label>";
                            $results = modelGetOptions($db, $column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view;
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            </div>
            <!-- Environmental Filters -->
            <div class="column is-half">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Environmental Filters</p>
                </header>
                <div class="card-content p-4">
                    <div class="columns">
                    <?php
                        $geoFilters = [
                            "env_feature" => "Environment Feature",
                            "envo_biome_term" => "Biome Term",
                            "env_material" => "Material",
                        ];
                        foreach ($geoFilters as $column => $label) {
                            echo "<div class='column'>";
                            echo "<label class='label has-text-weight-medium'>{$label}</label>";
                            $results = modelGetOptions($db, $column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view;
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            </div>
            </div>
            <!-- Taxonomy Filters -->
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Taxonomy Filters<p>
                </header>
                <div class="card-content p-4">
                    <div class="columns">
                    <?php
                        $geoFilters = [
                            "phylum_name" => "Phylum",
                            "class_name" => "Class",
                            "order_name" => "Order",
                            "family_name" => "Family",
                            "genus_name" => "Genus",
                            "species_name" => "Species"
                        ];
                        foreach ($geoFilters as $column => $label) {
                            echo "<div class='column'>";
                            echo "<label class='label has-text-weight-medium'>{$label}</label>";
                            $results = modelGetOptions($db, $column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view;
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">SQL Query Preview</p>
                </header>
                <div class="card-content p-0">
                    <?php
                        $query = buildQuery($conditions,
                            ['refsequence_pk'],
                            ['refsequence_pk', 'sh_unite_id', 'phylum_name', 'species_name'],
                            ['biosample_id', 'sra_sample'],
                            ['biosample_id', 'sra_sample'],
                            ['refsequence_pk', 'sh_unite_id', 'phylum_name', 'species_name']
                        );
                        $view = viewQueryPreview($query);
                        echo $view;
                    ?>
                </div>
                <footer class="card-footer">
                    <a class="card-footer-item" hx-post="partials/query_table.php"
                            hx-target="#result"
                            hx-include=".where_filters">
                        Execute Query
                    </a>
                </footer>
            </div>
            <div id="result">
                <div class="box">
                    <p class="has-text-grey">Results will be displayed here...</p>
                </div>
            </div>
        </div>
    </section>
    <script>
        htmx.logAll();
    </script>
</body>
</html>
