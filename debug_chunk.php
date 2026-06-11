<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
echo "Source length: " . strlen($content) . "\n";
$json = json_encode($content, JSON_UNESCAPED_UNICODE);
echo "JSON length: " . strlen($json) . "\n";
echo "JSON first 100: " . substr($json, 0, 100) . "\n";
file_put_contents("C:\Users\Home\Downloads\chunk_0.txt", $json);
echo "Written: " . filesize("C:\Users\Home\Downloads\chunk_0.txt") . "\n";
