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
            <div class="box">
                <div class="columns">
                    <div class="column">
                        <label class="label">Continent</label>
                        <div class="select is-fullwidth">
                            <?php
                            require_once '../src/get_options.php';
                            $column = "country_geonames_continent";
                            $results = modelGetOptions($column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view
                            ?>
                        </div>
                    </div>
                    <div class="column">
                        <label class="label">Subregion</label>
                        <div class="select is-fullwidth">
                        <?php
                            require_once '../src/get_options.php';
                            $column = "country_parent";
                            $results = modelGetOptions($column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view
                            ?>
                        </div>
                    </div>
                    <div class="column">
                        <label class="label">Country</label>
                        <div class="select is-fullwidth">
                        <?php
                            require_once '../src/get_options.php';
                            $column = "country_geoname_pref_en";
                            $results = modelGetOptions($column, []);
                            $view = viewGetOptions($results, $column, []);
                            echo $view
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <label class="label">SQL Query Preview</label>
                <div id="queryPreview"
                    hx-post="partials/query_preview.php"
                    hx-trigger="reloadQueryPreview from:body"
                    hx-include=".where_filters">
                    <?php
                        require_once '../src/build_query.php';
                        require_once '../src/base_query.php';
                        require_once '../src/query_preview.php';
                        $query = buildQuery($base_query, $conditions);
                        $view = viewQueryPreview($query);
                        echo $view;
                    ?>
                </div>
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
