<?php
$content = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
$chunks = mb_str_split($content, 5000);
$lines = [];
foreach ($chunks as $i => $chunk) {
    $escaped = json_encode($chunk, JSON_UNESCAPED_UNICODE);
    $lines[] = "localStorage.setItem('c$i', $escaped);";
}
$lines[] = '';
$lines[] = "const ta = document.querySelector('[name=\"content_ar\"]');";
$lines[] = "let content = '';";
$lines[] = "for (let i = 0; i < " . count($chunks) . "; i++) {";
$lines[] = "    content += localStorage.getItem('c' + i);";
$lines[] = "}";
$lines[] = "ta.value = content;";
$lines[] = "ta.dispatchEvent(new Event('input', {bubbles: true}));";
file_put_contents('C:\Users\Home\Downloads\article12_full_script.js', implode("\n", $lines));
echo "Written: " . count($lines) . " lines, " . (int)(filesize('C:\Users\Home\Downloads\article12_full_script.js')) . " bytes\n";
