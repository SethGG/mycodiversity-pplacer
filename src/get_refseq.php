<?php

require_once 'build_query.php';

function modelGetRefseq($db, $refseq) {
    $query = buildQuery(['refsequence_pk' => $refseq], [], ['seq_zotu'], ['seq_zotu']);
    $result = $db->select($query)[0]['seq_zotu'];
    return $result;
}

function viewGetRefseq($refseq, $result) {
    // Split the DNA sequence into 10bp chunks
    $formattedResult = chunk_split($result, 10, ' '); // Adds a space after every 10bp

    $view = <<<HTML
    <div class="modal is-active"
        hx-on:htmx:load="htmx.addClass('html', 'is-clipped')">
        <div class="modal-background"></div>
        <div class="modal-content">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">{$refseq}</p>
                </header>
                <div class="card-content p-0">
                    <pre class="is-size-5" style="white-space: pre-wrap; word-wrap: break-word;">{$formattedResult}</pre>
                </div>
                <footer class="card-footer">
                    <a class="card-footer-item"
                        hx-on:click="htmx.remove('.modal'); htmx.removeClass('html', 'is-clipped')">
                        Close</a>
                </footer>
            </div>
        </div>
    </div>
    HTML;
    return $view;
}

?>