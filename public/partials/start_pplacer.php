<?php

require_once '../../src/get_conditions.php';
require_once '../../src/start_pplacer.php';
require_once '../../src/database.php';

$conditions = getConditions();
$db = new Database();

// Generate a unique job ID
$jobId = uniqid('pplacer_', true);
$tempDir = sys_get_temp_dir() . "/$jobId";
$logFile = "$tempDir/$jobId.log";
$pidFile = "$tempDir/$jobId.pid";
$queryFile = "$tempDir/query.txt";
$statsFile = "$tempDir/stats.txt";
$fastaFile = "$tempDir/input.fasta";

// Create job directory
if (!mkdir($tempDir, 0777, true)) {
    die('Failed to create temporary directory.');
}

list($results, $query) = modelStartPplacer($db, $conditions);
$totalRows = count($results);

// Write Query
file_put_contents($queryFile, $query);

// Write Stats
$statsContent = "Total Sequences: $totalRows\n";
file_put_contents($statsFile, $statsContent);

// Write FASTA File
toFastaStartPplacer($results, $fastaFile);

// Run the Phylogenetic Placement Script
$command = "cd ../../phylo_placement; bash start_pipeline.sh $fastaFile > $logFile 2>&1 & echo $!";
exec($command, $output, $returnVar);

if ($returnVar === 0 && isset($output[0])) {
    // Save PID
    file_put_contents($pidFile, trim($output[0]));

    echo <<<HTML
    <div class="modal is-active"
        hx-on:htmx:load="htmx.addClass('html', 'is-clipped')">
        <div class="modal-background"></div>
        <div class="modal-content" style="width: 47rem;">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Phylogenetic Placement ({$jobId})</p>
                </header>
                <div class="card-content p-0">
                    <pre style="white-space: pre-wrap; word-wrap: break-word; height: 30rem;"
                        hx-ext="sse"
                        sse-connect="placement_log.php?job={$jobId}"
                        sse-swap="message"
                        sse-close="close"
                        hx-on:htmx:sse-close="htmx.removeClass('#downloadButton', 'isDisabled')">
                    </pre>
                </div>
                <footer class="card-footer">
                    <a id="downloadButton" class="card-footer-item isDisabled"
                        href="download_pplacer.php?job={$jobId}">Download Results</a>
                    <a class="card-footer-item"
                        hx-on:click="htmx.remove('.modal'); htmx.removeClass('html', 'is-clipped')">
                        Close</a>
                </footer>
            </div>
        </div>
    </div>
    HTML;
} else {
    echo 'Failed to start Phylogenetic Placement command.';
}

?>