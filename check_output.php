<?php
$output = file_get_contents('C:\Users\Home\Downloads\article12_formatted.txt');
echo "Total: " . strlen($output) . " chars\n\n";

// Extract and show summary for each section
$sections = explode('<div class="blog-section">', $output);
foreach ($sections as $i => $s) {
    if (empty(trim($s))) continue;
    // Get heading
    preg_match('/<h2>(.*?)<\/h2>/u', $s, $m);
    $heading = $m[1] ?? 'NO HEADING';
    $paraCount = substr_count($s, '<p>');
    $imgCount = substr_count($s, '<img');
    $charCount = strlen(strip_tags($s));
    echo "Section " . ($i+1) . ": $heading\n";
    echo "  Paragraphs: $paraCount, Images: $imgCount, Chars: $charCount\n";
}
