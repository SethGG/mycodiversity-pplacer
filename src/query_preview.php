<?php

function viewQueryPreview($query) {
    $view = <<<HTML
    <div id="queryPreview" hx-swap-oob="true">
        <pre>{$query}</pre>
    </div>
    HTML;
    return $view;
}

?>