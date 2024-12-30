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
    
    echo '
    <h1>Ping command output</h1>
    <pre 
        hx-ext="sse"
        sse-connect="ping.php?job=' . htmlspecialchars($jobId) . '"
        sse-swap="message"
        sse-close="close"
    ></pre>';
} else {
    echo 'Failed to start ping command.';
}
?>
