<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunks = str_split($content, 5000);
echo count($chunks) . " chunks needed\n";
echo "Total: " . strlen($content) . " chars\n";
echo "First: " . substr($content, 0, 100) . "\n";
echo "Last: " . substr($content, -100) . "\n";
