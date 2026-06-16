<?php
$files = [
    "organic-spa" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/organic-spa/footer.blade.php",
    "luxury-boutique" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/luxury-boutique/footer.blade.php",
    "editorial" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/editorial/footer.blade.php",
    "cyber-lab" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/cyber-lab/footer.blade.php",
];

$reps = [];

// Copyright: . ???? ?????? ??????. -> . جميع الحقوق محفوظة.
$copyrightOld = '. ???? ?????? ??????.';
$copyrightNew = '. جميع الحقوق محفوظة.';

// Organic-spa: newsletter heading
$reps['organic-spa'] = [
    ['????? ??? ????? {{ $site_settings[\'site_name\'] ?? \'???? ???? ???????\' }}', 'انضمي إلى نادي {{ $site_settings[\'site_name\'] ?? \'شركة جنين للتجميل\' }}'],
    // Actually the site_name uses camelCase $siteSettings
];

// Per-file JS error replacements (inside <span> tags)
$reps['organic-spa'][] = ['<span class="text-red-400">??? ?? ???????</span>', '<span class="text-red-400">حدث خطأ من فضلك حاولي مرة أخرى</span>'];
$reps['organic-spa'][] = [$copyrightOld, $copyrightNew];

$reps['luxury-boutique'] = [
    ['<span class="text-red-400">???</span>', '<span class="text-red-400">خطأ</span>'],
    [$copyrightOld, $copyrightNew],
];

$reps['editorial'] = [
    ['<span class="text-red-400">???</span>', '<span class="text-red-400">خطأ</span>'],
    [$copyrightOld, $copyrightNew],
];

$reps['cyber-lab'] = [
    ['<span class="text-red-400">??? ??? ?? ???????</span>', '<span class="text-red-400">حدث خطأ من فضلك حاولي مرة أخرى</span>'],
    [$copyrightOld, $copyrightNew],
];

$changed = 0;
foreach ($reps as $theme => $pairs) {
    $path = $files[$theme];
    echo "Processing: $theme ($path)\n";
    $content = file_get_contents($path);
    $original = $content;

    // Sort longest first
    usort($pairs, fn($a, $b) => strlen($b[0]) <=> strlen($a[0]));

    foreach ($pairs as [$old, $new]) {
        $count = 0;
        $content = str_replace($old, $new, $content, $count);
        if ($count > 0) {
            echo "  OK: '$old' -> '$new' ($count)\n";
        }
    }

    // Count remaining ? outside Blade comments
    $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    // Remove PHP ?? operators and JS ?. optional chaining
    $stripped = preg_replace('/\?\?/', '  ', $stripped); // null coalescing
    $stripped = preg_replace('/\?\./', ' .', $stripped); // optional chaining
    
    $remaining = 0;
    $inString = false;
    $i = 0;
    $len = strlen($stripped);
    $q3plus = 0;
    while ($i < $len) {
        if ($stripped[$i] === '<' && substr($stripped, $i, 4) === '<!--') {
            $end = strpos($stripped, '-->', $i);
            if ($end !== false) { $i = $end + 3; continue; }
        }
        if ($stripped[$i] === '?') {
            $start = $i;
            while ($i < $len && $stripped[$i] === '?') $i++;
            $qLen = $i - $start;
            if ($qLen >= 3) {
                $q3plus += $qLen;
            }
            continue;
        }
        $i++;
    }
    
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  Saved. Remaining non-code ? (3+): $q3plus\n";
        $changed++;
    } else {
        echo "  No changes\n";
    }
}

echo "\nDone! $changed of " . count($reps) . " modified.\n";
