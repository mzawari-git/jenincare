<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunks = [];
$chunkSize = 25000;
for ($i = 0; $i < strlen($content); $i += $chunkSize) {
    $chunks[] = substr($content, $i, $chunkSize);
}

foreach ($chunks as $idx => $chunk) {
    $js = json_encode($chunk, JSON_UNESCAPED_UNICODE);
    file_put_contents("C:\Users\Home\Downloads\a12_chunk_$idx.js", "localStorage.setItem('fmt_$idx', $js);");
    echo "Chunk $idx: " . strlen($chunk) . " chars\n";
}
echo "Total chunks: " . count($chunks) . "\n";

// Also create the concatenation script
$concat = "const ta = document.querySelector('[name=\\\"content_ar\\\"]');\n";
$concat .= "let c = '';\n";
for ($i = 0; $i < count($chunks); $i++) {
    $concat .= "c += localStorage.getItem('fmt_$i');\n";
}
$concat .= "ta.value = c;\n";
$concat .= "ta.dispatchEvent(new Event('input', {bubbles: true}));\n";
$concat .= "'Content set: ' + c.length;\n";
file_put_contents("C:\Users\Home\Downloads\a12_concat.js", $concat);
echo "Created concat script\n";
