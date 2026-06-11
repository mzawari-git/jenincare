<?php
$raw = file_get_contents('C:\Users\Home\Downloads\article12_raw.txt');
$raw = preg_replace('/<div[^>]*>/', '', $raw);
$raw = str_replace('</div>', '', $raw);
$raw = trim($raw);

// Print hex bytes around position 230 to see what's there
$pos = 230;
echo "Bytes 230-250:\n";
for ($i = $pos; $i < $pos+60 && $i < strlen($raw); $i++) {
    $byte = ord($raw[$i]);
    echo sprintf('%02x ', $byte);
}
echo "\n\n";

// Check if الجينييشهد exists
$needle = "\xD8\xA7\xD9\x84\xD8\xAC\xD9\x8A\xD9\x86\xD9\x8A\xD9\x8A\xD8\xB4\xD9\x87\xD8\xAF";
echo "Contains الجينييشهد: " . (strpos($raw, $needle) !== false ? "YES" : "NO") . "\n";

// Also look for just 'التقرير الشامل'
$needle2 = "\xD8\xA7\xD9\x84\xD8\xAA\xD9\x82\xD8\xB1\xD9\x8A\xD8\xB1";
echo "Contains التقرير at start: " . (strpos($raw, $needle2) === 0 ? "YES" : "NO") . "\n";
