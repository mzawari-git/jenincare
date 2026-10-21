<?php
$files = [
    "organic-spa" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/organic-spa/footer.blade.php",
    "luxury-boutique" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/luxury-boutique/footer.blade.php",
    "editorial" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/editorial/footer.blade.php",
    "cyber-lab" => "C:/xampp/htdocs/jenincare/resources/views/frontend/layouts/cyber-lab/footer.blade.php",
];

foreach ($files as $name => $path) {
    echo "=== $name ===\n";
    $content = file_get_contents($path);
    
    // Remove Blade comments
    $stripped = preg_replace('/\{\{--.*?--\}\}/s', '', $content);
    
    preg_match_all('/\?{3,}/', $stripped, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $m) {
        $pos = $m[1];
        $q = $m[0];
        $len = strlen($q);
        
        // Determine FULL ? block (extend to include adjacent ?)
        $fullLen = $len;
        $fullPos = $pos;
        
        // Show context
        $ctxStart = max(0, $pos - 30);
        $ctxEnd = min(strlen($stripped), $pos + $len + 30);
        $ctx = substr($stripped, $ctxStart, $ctxEnd - $ctxStart);
        $ctx = str_replace(["\r", "\n"], ["", "\\n"], $ctx);
        
        echo "  '$q' (len=$len) ctx: ...$ctx...\n";
    }
    echo "\n";
}
