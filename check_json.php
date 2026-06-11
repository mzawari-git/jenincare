<?php
$json = file_get_contents(__DIR__ . '/public/article12_content.json');
$data = json_decode($json, true);
echo 'Content length: ' . strlen($data['content']) . "\n";
echo 'First 200 chars: ' . substr($data['content'], 0, 200) . "\n";
echo 'Last 200 chars: ' . substr($data['content'], -200) . "\n";
