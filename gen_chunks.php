<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunks = mb_str_split($content, 5000);
echo "Total chunks: " . count($chunks) . "\n\n";
foreach ($chunks as $i => $chunk) {
    $escaped = json_encode($chunk, JSON_UNESCAPED_UNICODE);
    if ($escaped === false || $escaped === '""' || $escaped === 'null') {
        echo "// chunk $i is " . var_export($escaped, true) . ", length=" . mb_strlen($chunk) . "\n";
    } else {
        echo "localStorage.setItem('c$i', $escaped);\n";
    }
}
