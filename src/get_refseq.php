<?php

require_once 'build_query.php';

function modelGetRefseq($db, $refseq) {
    $query = buildQuery(['refsequence_pk' => $refseq], [], ['seq_zotu'], true);
    $result = $db->select($query)[0]['seq_zotu'];
    return $result;
}

function viewGetRefseq($refseq, $result) {
    $view = <<<HTML
    <div class="modal is-active">
        <div class="modal-background"></div>
        <div class="modal-content">
            <div class="box">
            {$result}
            </div>
        </div>
        <button class="modal-close is-large" aria-label="close"></button>
    </div>
    HTML;
    return $view;
}

?>