<?php
$content = file_get_contents('C:\xampp\htdocs\jenincare\resources\views\frontend\layouts\organic-spa\footer.blade.php');

$search = "'??? ?? ???????'";
echo 'Search value: ' . $search . PHP_EOL;
echo 'Search hex: ' . bin2hex($search) . PHP_EOL;

$found = strpos($content, $search);
echo 'Found at: ' . ($found === false ? 'NOT FOUND' : $found) . PHP_EOL;

if ($found !== false) {
    echo 'Context: ' . bin2hex(substr($content, $found, 50)) . PHP_EOL;
}

// Also check for the shorter ???
$search2 = "'???'";
echo PHP_EOL . 'Search2: ' . $search2 . PHP_EOL;
echo 'Search2 hex: ' . bin2hex($search2) . PHP_EOL;
$found2 = strpos($content, $search2);
echo 'Found2 at: ' . ($found2 === false ? 'NOT FOUND' : $found2) . PHP_EOL;

// Check what's at position ~6000 (around the JS area)
echo PHP_EOL . 'Looking at JS area...' . PHP_EOL;
$jsArea = substr($content, 5800, 300);
$lines = explode("\n", $jsArea);
foreach ($lines as $i => $line) {
    if (strpos($line, '?') !== false) {
        $pos = 5800 + strpos(substr($content, 5800), $line);
        $simplified = preg_replace('/[^\x20-\x7E\x80-\xFF]/', '.', $line);
        echo "  pos~$pos: $simplified" . PHP_EOL;
    }
}
