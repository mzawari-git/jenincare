<?php
header('Content-Type: text/plain');
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
echo "ORIG_PATH_INFO: " . ($_SERVER['ORIG_PATH_INFO'] ?? 'N/A') . "\n";
echo "PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'N/A') . "\n";
echo "ORIG_SCRIPT_NAME: " . ($_SERVER['ORIG_SCRIPT_NAME'] ?? 'N/A') . "\n";
echo "REDIRECT_URL: " . ($_SERVER['REDIRECT_URL'] ?? 'N/A') . "\n";
echo "REDIRECT_URI: " . ($_SERVER['REDIRECT_URI'] ?? 'N/A') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
