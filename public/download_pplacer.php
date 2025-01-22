<?php
// Set headers for file download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="pplacer_files.zip"');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Get the job ID from the query string
$jobId = $_GET['job'] ?? null;

// Validate Job ID
if (!$jobId) {
    die('Missing job ID');
}

// Define the directory for the job files
$jobDir = sys_get_temp_dir() . "/$jobId";

// Check if the job directory exists
if (!is_dir($jobDir)) {
    die('Job directory not found');
}

// Create a temporary zip file
$zip = new ZipArchive();
$tempZipFile = tempnam(sys_get_temp_dir(), 'pplacer_zip_');
$zip->open($tempZipFile, ZipArchive::CREATE);

// Function to recursively add files to the zip
function addFilesToZip($dir, $zip, $parentDir = '') {
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;

        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        $relativePath = $parentDir ? $parentDir . DIRECTORY_SEPARATOR . $file : $file;

        if (is_dir($filePath)) {
            // Add directory recursively
            addFilesToZip($filePath, $zip, $relativePath);
        } else {
            // Add file to zip
            $zip->addFile($filePath, $relativePath);
        }
    }
}

// Add all files from the job directory to the zip
addFilesToZip($jobDir, $zip);

// Close the zip file
$zip->close();

// Output the zip file to the browser for download
readfile($tempZipFile);

// Cleanup temporary zip file
unlink($tempZipFile);
?>
