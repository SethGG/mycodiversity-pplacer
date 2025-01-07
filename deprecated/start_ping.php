<?php
// Start a new ping session and return a unique job ID

// Generate a unique job ID
$jobId = uniqid('ping_', true);
$logFile = sys_get_temp_dir() . "/$jobId.log";
$pidFile = sys_get_temp_dir() . "/$jobId.pid";

// Start the ping command in the background and save the PID
$command = "ping -c 10 127.0.0.1 > $logFile 2>&1 & echo $!";
exec($command, $output, $returnVar);

if ($returnVar === 0 && isset($output[0])) {
    // Save PID to file
    file_put_contents($pidFile, trim($output[0]));
    
    echo <<<HTML
    <div class="modal is-active"
        hx-on:htmx:load="htmx.addClass('html', 'is-clipped')">
        <div class="modal-background"></div>
        <div class="modal-content">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">Phylogenetic Placement ({$jobId})</p>
                </header>
                <div class="card-content p-0">
                    <pre style="white-space: pre-wrap; word-wrap: break-word; height: 30rem;"
                        hx-ext="sse"
                        sse-connect="ping.php?job={$jobId}"
                        sse-swap="message"
                        sse-close="close"
                        hx-on:htmx:sse-close="htmx.removeClass('#downloadButton', 'isDisabled')">
                    </pre>
                </div>
                <footer class="card-footer">
                    <a id="downloadButton" class="card-footer-item isDisabled"
                        href="download_ping.php?job={$jobId}">Download Results</a>
                    <a class="card-footer-item"
                        hx-on:click="htmx.remove('.modal'); htmx.removeClass('html', 'is-clipped')">
                        Close</a>
                </footer>
            </div>
        </div>
    </div>
    HTML;
} else {
    echo 'Failed to start ping command.';
}
?>
