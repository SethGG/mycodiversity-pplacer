<?php
// Set headers for Server-Sent Events (SSE)
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Disable buffering
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', 'off');

// Prevent Apache/Nginx buffering
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', '1');
    apache_setenv('dont-vary', '1');
}

// Start ping command
$command = '/home/dzee/Documents/Studie/Bachelorklas/mycodiversity-pplacer/dummy.sh';
$handle = popen($command, 'r');

if ($handle) {
    while (!feof($handle)) {
        $buffer = fgets($handle);
        if ($buffer !== false) {
            // Send each line as an SSE event
            echo "data: <div>" . trim($buffer) . " </div>\n\n";
            ob_flush();
            flush();
            usleep(100000); // Slight delay for real-time effect
        }
    }
    pclose($handle);
} else {
    echo "data: Failed to execute command.\n\n";
    ob_flush();
    flush();
}

// Send the closing event
echo "event: close\n";
echo "data: Connection closed\n\n";
ob_flush();
flush();
exit;
?>
