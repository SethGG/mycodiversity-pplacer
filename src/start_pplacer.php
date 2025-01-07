<?php

require_once 'build_query.php';

function modelStartPplacer($db, $conditions) {
    $query = buildQuery($conditions,
    ['refsequence_pk'],
    ['refsequence_pk', 'seq_zotu'],
    ['refsequence_pk']
    );

    $results = $db->select($query);
    return [$results, $query];
}

function toFastaStartPplacer($results, $filepath) {
    // Open the file for writing
    $file = fopen($filepath, 'w');
    if (!$file) {
        throw new Exception("Unable to open file for writing: $filepath");
    }

    // Write each sequence to the FASTA file
    foreach ($results as $row) {
        if (isset($row['refsequence_pk'], $row['seq_zotu'])) {
            fwrite($file, ">{$row['refsequence_pk']}\n");
            fwrite($file, "{$row['seq_zotu']}\n");
        }
    }

    // Close the file
    fclose($file);
}

?>