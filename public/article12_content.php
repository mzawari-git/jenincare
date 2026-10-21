<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
$json = file_get_contents(__DIR__ . '/article12_content.json');
if ($json) {
    echo $json;
} else {
    echo json_encode(['error' => 'content file not found']);
}
