<?php
// Get the job ID from the query string
$jobId = $_GET['job'] ?? null;

// Validate Job ID
if (!$jobId) {
    http_response_code(400); // Bad Request
    echo 'Missing job ID.';
    exit;
}

// Locate the log file
$logFile = sys_get_temp_dir() . "/$jobId.log";

// Validate the log file
if (!file_exists($logFile)) {
    http_response_code(404); // Not Found
    echo 'Log file not found.';
    exit;
}

// Clean the output buffer to prevent interference
if (ob_get_level()) {
    ob_end_clean();
}

// Set headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="ping_' . basename($jobId) . '.log"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($logFile));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Disable output buffering to avoid memory issues
ob_clean();
flush();

// Stream the file to the browser
readfile($logFile);
exit;
?>
