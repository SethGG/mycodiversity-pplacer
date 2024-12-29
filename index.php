<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MDDB4 Query Builder</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
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
                            <select id="continentDropdown"
                                name="where_country_geonames_continent" 
                                hx-post="query_handler.php"
                                hx-target="#subregionDropdown"
                                hx-vals='{
                                    "action": "getOptions",
                                    "column": "country_parent"
                                }'
                                hx-trigger="change">
                                 <?php
                                    require_once 'query_handler.php';
                                    $handler = new QueryHandler();
                                    $handler->renderOptions('country_geonames_continent');
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="column">
                        <label class="label">Subregion</label>
                        <div class="select is-fullwidth">
                            <select id="subregionDropdown"
                                name="where_country_parent" 
                                hx-post="query_handler.php"
                                hx-target="#countryDropdown"
                                hx-vals='{
                                    "action": "getOptions",
                                    "column": "country_geoname_pref_en"
                                }'
                                hx-include="#continentDropdown"
                                hx-trigger="change, htmx:afterSettle">
                                <?php
                                    require_once 'query_handler.php';
                                    $handler = new QueryHandler();
                                    $handler->renderOptions('country_parent');
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="column">
                        <label class="label">Country</label>
                        <div class="select is-fullwidth">
                            <select id="countryDropdown"
                                name="where_country_geoname_pref_en" 
                                hx-post="query_handler.php"
                                hx-target="#queryPreview"
                                hx-vals='{
                                    "action": "updateQuery"
                                }'
                                hx-include="#continentDropdown, #subregionDropdown"
                                hx-trigger="change, htmx:afterSettle">
                                <?php
                                    require_once 'query_handler.php';
                                    $handler = new QueryHandler();
                                    $handler->renderOptions('country_geoname_pref_en');
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <label class="label">SQL Query Preview</label>
                <div id="queryPreview">
                    <?php
                        require_once 'query_handler.php';
                        $handler = new QueryHandler();
                        $handler->updateQuery();
                    ?>
                </div>
                <div class="buttons mt-3">
                    <button class="button is-primary"
                        hx-post="query_handler.php"
                        hx-target="#result"
                        hx-swap="innerHTML"
                        hx-include="#continentDropdown, #subregionDropdown, #countryDropdown"
                        name="action" 
                        value="executeQuery">Execute Query
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
