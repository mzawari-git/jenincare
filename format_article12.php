<?php
$raw = file_get_contents('C:\Users\Home\Downloads\article12_raw.txt');

$raw = preg_replace('/<div[^>]*>/', '', $raw);
$raw = str_replace('</div>', '', $raw);
// The duplicate title appears right after the first image, before "يشهد مجال"
// It looks like: "التقرير الشامل...الجينييشهد مجال" (no space between title and first sentence)
$raw = preg_replace('/التقرير الشامل والموسع للابتكارات المخبرية وتطبيقاتها في العناية الشخصية: من آليات استتباب الحاجز الجلدي إلى الطب التجديدي وعلم التخلق الجيني(?=يشهد)/u', '', $raw);

$images = [];
$raw = preg_replace_callback('/<img[^>]+src="([^"]+)"[^>]*>/', function($m) use (&$images) {
    $id = '##IMG_' . count($images) . '##';
    $images[$id] = '<div style="text-align:center;margin:25px 0;"><img src="' . $m[1] . '" alt="" class="rounded-xl max-w-full mx-auto block" loading="lazy"></div>';
    return $id;
}, $raw);

$raw = trim($raw);

function formatBody($text, $images) {
    $text = trim($text);
    if (empty($text)) return '';

    $parts = preg_split('/(##IMG_\d+##)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    $result = '';
    $paraBuf = [];

    foreach ($parts as $part) {
        $part = trim($part);
        if (empty($part)) continue;

        if (isset($images[$part])) {
            if (!empty($paraBuf)) {
                $result .= '    <p>' . implode(' ', $paraBuf) . '</p>' . "\n\n";
                $paraBuf = [];
            }
            $result .= '    ' . $images[$part] . "\n\n";
            continue;
        }

        $part = preg_replace('/(\.)(?=[\x{0600}-\x{06FF}])/u', '. ', $part);

        $chunks = preg_split('/(?<=[.!?])\s+(?=[\x{0600}-\x{06FF}\p{L}])/u', $part);

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if (empty($chunk)) continue;

            $paraBuf[] = $chunk;
            $combined = implode(' ', $paraBuf);
            if (mb_strlen($combined) > 350) {
                $result .= '    <p>' . implode(' ', $paraBuf) . '</p>' . "\n\n";
                $paraBuf = [];
            }
        }
    }

    if (!empty($paraBuf)) {
        $result .= '    <p>' . implode(' ', $paraBuf) . '</p>' . "\n\n";
    }

    return rtrim($result);
}

$sectionKeys = ['القسم الأول', 'القسم الثاني', 'القسم الثالث', 'القسم الرابع', 'القسم الخامس'];
$sectionIcons = ['fas fa-flask', 'fas fa-dna', 'fas fa-microscope', 'fas fa-clinic-medical', 'fas fa-robot'];

$intro = '';
$pos = mb_strpos($raw, 'القسم الأول');
if ($pos !== false) {
    $intro = mb_substr($raw, 0, $pos);
    $remaining = mb_substr($raw, $pos);
} else {
    $remaining = $raw;
}

$output = '';

if (!empty(trim($intro))) {
    $body = formatBody($intro, $images);
    $output .= '<div class="blog-section">' . "\n";
    $output .= '    <h2><i class="fas fa-book-open"></i> مقدمة: الثورة العلمية في عالم العناية الشخصية</h2>' . "\n";
    $output .= $body . "\n";
    $output .= '</div>' . "\n\n";
}

foreach ($sectionKeys as $idx => $key) {
    $start = mb_strpos($remaining, $key);
    if ($start === false) continue;
    $nextKey = isset($sectionKeys[$idx + 1]) ? $sectionKeys[$idx + 1] : '';
    if (!empty($nextKey)) {
        $end = mb_strpos($remaining, $nextKey, $start + mb_strlen($key));
    } else {
        $end = false;
    }
    if ($end !== false) {
        $sectionContent = mb_substr($remaining, $start, $end - $start);
    } else {
        $sectionContent = mb_substr($remaining, $start);
    }
    $title = $key;
    $bodyText = $sectionContent;
    $escapedKey = preg_quote($key, '/');
    if (preg_match('/^(' . $escapedKey . '[:：]\s*[^\n]*?)(?:\n|$)/u', $sectionContent, $m)) {
        $title = trim($m[1]);
        $bodyText = mb_substr($sectionContent, mb_strlen($m[0]));
    }
    $icon = $sectionIcons[$idx];
    $bodyHtml = formatBody($bodyText, $images);
    $output .= '<div class="blog-section">' . "\n";
    $output .= '    <h2><i class="' . $icon . '"></i> ' . htmlspecialchars($title) . '</h2>' . "\n";
    $output .= $bodyHtml . "\n";
    $output .= '</div>' . "\n\n";
}

file_put_contents('C:\Users\Home\Downloads\article12_formatted.txt', $output);
echo "Done! " . strlen($output) . " chars\n";
echo "=== SAMPLE ===\n";
echo mb_substr($output, 0, 1500) . "\n";
echo "=== END ===\n";
echo "Paragraphs: " . substr_count($output, '<p>') . "\n";
echo "Sections: " . substr_count($output, 'blog-section') . "\n";
echo "Images: " . substr_count($output, '<img') . "\n";
