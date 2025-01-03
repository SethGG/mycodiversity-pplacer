<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDDB4 Query Builder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.2/css/bulma.min.css">
    <script src="https://unpkg.com/htmx.org@2.0.4"></script>
    <script src="https://unpkg.com/htmx-ext-sse@2.2.2/sse.js"></script>
</head>
<body>
    <section class="section">
        <div class="container">
            <h1 class="title">MDDB4 Query Builder</h1>
            <!-- Sample Filters -->
            <div class="box">
                <h5 class="title is-5">Sample Filters</h5>
                <div class="columns">
                <?php
                    require_once '../src/get_options.php';
                    $geoFilters = [
                        "study_bioproject_id" => "BioProject",
                        "biosample_id" => "BioSample",
                        "study_sra_id" => "SRA Study",
                        "sra_sample" => "SRA Sample"
                    ];
                    foreach ($geoFilters as $column => $label) {
                        echo "<div class='column'>";
                        echo "<label class='label'>{$label}</label>";
                        $results = modelGetOptions($column, []);
                        $view = viewGetOptions($results, $column, []);
                        echo $view;
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <!-- Geographical Filters -->
            <div class="box">
                <h5 class="title is-5">Geographical Filters</h5>
                <div class="columns">
                <?php
                    require_once '../src/get_options.php';
                    $geoFilters = [
                        "country_geonames_continent" => "Continent",
                        "country_parent" => "Subregion",
                        "country_geoname_pref_en" => "Country"
                    ];
                    foreach ($geoFilters as $column => $label) {
                        echo "<div class='column'>";
                        echo "<label class='label'>{$label}</label>";
                        $results = modelGetOptions($column, []);
                        $view = viewGetOptions($results, $column, []);
                        echo $view;
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <!-- Environmental Filters -->
            <div class="box">
                <h5 class="title is-5">Environmental Filters</h5>
                <div class="columns">
                <?php
                    require_once '../src/get_options.php';
                    $geoFilters = [
                        "env_feature" => "Environment Feature",
                        "envo_biome_term" => "Biome Term",
                        "env_material" => "Material",
                    ];
                    foreach ($geoFilters as $column => $label) {
                        echo "<div class='column'>";
                        echo "<label class='label'>{$label}</label>";
                        $results = modelGetOptions($column, []);
                        $view = viewGetOptions($results, $column, []);
                        echo $view;
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <!-- Taxonomy Filters -->
            <div class="box">
                <h5 class="title is-5">Taxonomy Filters</h5>
                <div class="columns">
                <?php
                    require_once '../src/get_options.php';
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
                        echo "<label class='label'>{$label}</label>";
                        $results = modelGetOptions($column, []);
                        $view = viewGetOptions($results, $column, []);
                        echo $view;
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
            <div class="box">
                <label class="label">SQL Query Preview</label>
                <?php
                    require_once '../src/build_query.php';
                    require_once '../src/query_preview.php';
                    $query = buildQuery();
                    $view = viewQueryPreview($query);
                    echo $view;
                ?>
                <div class="buttons mt-3">
                    <button class="button is-primary"
                        hx-post="partials/query_table.php"
                        hx-target="#result"
                        hx-swap="innerHTML"
                        hx-include=".where_filters">
                        Execute Query
                    </button>
                    <button class="button is-info"
                        hx-post="query_handler.php"
                        hx-target="#result"
                        hx-swap="innerHTML"
                        hx-include="#continentDropdown, #subregionDropdown, #countryDropdown"
                        name="action" 
                        value="rowCount">Row Count
                    </button>
                    <button class="button is-warning"
                        hx-get="start_ping.php"
                        hx-target="#result"
                        hx-swap="innerHTML">Ping test
                    </button>
                </div>
            </div>
            <div id="result" class="box">
                <p class="has-text-grey">Results will be displayed here...</p>
            </div>
        </div>
    </section>
    <script>
        htmx.logAll()
    </script>
</body>
</html>
