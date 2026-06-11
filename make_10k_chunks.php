<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunkSize = 10000;
$chunks = [];
for ($i = 0; $i < strlen($content); $i += $chunkSize) {
    $chunks[] = substr($content, $i, $chunkSize);
}
foreach ($chunks as $idx => $chunk) {
    $js = json_encode($chunk, JSON_UNESCAPED_UNICODE);
    file_put_contents("C:\Users\Home\Downloads\chunk_$idx.txt", $js);
    echo "Chunk $idx: " . strlen($chunk) . " chars\n";
}
echo "Total: " . count($chunks) . " chunks\n";
