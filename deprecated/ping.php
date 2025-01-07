<?php
// Set headers for Server-Sent Events (SSE)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Disable buffering
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 'off');

// Get the job ID from the query string
$jobId = $_GET['job'] ?? null;

// Function to ensure the close event is always sent
function sendCloseEvent($message = 'Connection closed') {
    echo "event: close\n";
    echo "data: $message\n\n";
    ob_flush();
    flush();
    exit;
}

// Validate Job ID
if (!$jobId) {
    echo "data: Missing job ID.\n\n";
    ob_flush();
    flush();
    sendCloseEvent('Missing job ID');
}

// Locate the log file and PID file
$logFile = sys_get_temp_dir() . "/$jobId.log";
$pidFile = sys_get_temp_dir() . "/$jobId.pid";

// Validate the log file
if (!file_exists($logFile)) {
    echo "data: Log file not found.\n\n";
    ob_flush();
    flush();
    sendCloseEvent('Log file not found');
}

// Check if the process is still running
function isProcessRunning($pid) {
    return posix_getpgid($pid) !== false;
}

// Read the PID from the PID file
$pid = file_exists($pidFile) ? (int)file_get_contents($pidFile) : null;

// Track the last modification time of the file
$lastModTime = 0;

// Start the streaming loop
while (true) {
    clearstatcache(); // Clear file status cache
    
    if (!file_exists($logFile)) {
        sendCloseEvent('Log file deleted');
    }

    $currentModTime = filemtime($logFile);

    // Check if the file has been updated
    if ($currentModTime > $lastModTime) {
        $lastModTime = $currentModTime;

        // Read the entire file contents and split into lines
        $content = file($logFile, FILE_IGNORE_NEW_LINES);

        foreach ($content as $line) {
            echo "data: " . htmlspecialchars($line) . "\n";
        }
        echo "\n"; // SSE expects a blank line to signal the end of an event
        ob_flush();
        flush();
    }

    // Check if the process is still running
    if (!$pid || !isProcessRunning($pid)) {
        break;
    }

    // Small delay before checking again
    usleep(500000); // 500ms
}

// Clean up
if (file_exists($logFile)) {
    unlink($logFile);
}
if (file_exists($pidFile)) {
    unlink($pidFile);
}

// Send the closing event
sendCloseEvent();
?>
