<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunks = str_split($content, 20000);
foreach ($chunks as $idx => $chunk) {
    $js = json_encode($chunk, JSON_UNESCAPED_UNICODE);
    file_put_contents("C:\Users\Home\Downloads\chunk_$idx.txt", $js);
    echo "Chunk $idx: " . strlen($chunk) . " chars saved\n";
}
echo "Total: " . count($chunks) . " chunks\n";
