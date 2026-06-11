<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$json = json_encode(['content' => $content], JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/public/article12_content.json', $json);
echo 'Written ' . strlen($json) . " bytes\n";
