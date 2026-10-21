<?php
declare(strict_types=1);

$files = [
    'C:\xampp\htdocs\jenincare\resources\views\frontend\layouts\organic-spa\footer.blade.php',
    'C:\xampp\htdocs\jenincare\resources\views\frontend\layouts\luxury-boutique\footer.blade.php',
    'C:\xampp\htdocs\jenincare\resources\views\frontend\layouts\editorial\footer.blade.php',
    'C:\xampp\htdocs\jenincare\resources\views\frontend\layouts\cyber-lab\footer.blade.php',
];

$savedFiles = [];

// ============================================================
// Helper: replace exact string (binary safe) with unique marker
// then replace markers with real Arabic text at the end.
// This prevents substring interference.
// ============================================================

function fixFile(string $path): bool {
    global $savedFiles;
    echo "Processing: $path\n";
    $content = file_get_contents($path);
    if ($content === false) { echo "  FAILED\n"; return false; }
    $original = $content;

    $theme = 'unknown';
    if (str_contains($path, 'organic-spa')) $theme = 'organic-spa';
    elseif (str_contains($path, 'luxury-boutique')) $theme = 'luxury-boutique';
    elseif (str_contains($path, 'editorial')) $theme = 'editorial';
    elseif (str_contains($path, 'cyber-lab')) $theme = 'cyber-lab';

    // Strategy: process the file left-to-right, identifying each ? region
    // by its surrounding HTML context, and replacing it.
    // The key is to use preg_replace with specific regex patterns
    // that include enough context.

    $count = 0;

    // ═══════════════════ NEWSLETTER HEADING ═══════════════════
    // Full heading with all ? replaced
    $patterns = [
        // Theme: organic-spa (no span)
        [
            '#(style="color:var\(--brand-500\);">)\?\?\?\?\?\ \?\?\?\ \?\?\?\?\ \{\{\ \$siteSettings\[\'site_name\'\]\ \?\?\ \'[?]+\ [?]+\ [?]+\'\ \}\}</h2>#',
            '$1انضمي إلى نادي {{ $siteSettings[\'site_name\'] ?? \'شركة جنين للتجميل\' }}</h2>'
        ],
        // Themes: luxury, editorial, cyber-lab (with span)
        [
            '#(class="text-2xl md:text-3xl font-black mb-3 text-white">)\?\?\?\?\?\ \?\?\?\ \?\?\?\?\ <span class="text-brand-500">\{\{\ \$siteSettings\[\'site_name\'\]\ \?\?\ \'[?]+\ [?]+\ [?]+\'\ \}\}</span></h2>#',
            '$1انضمي إلى نادي <span class="text-brand-500">{{ $siteSettings[\'site_name\'] ?? \'شركة جنين للتجميل\' }}</span></h2>'
        ],
        // Editorial: different heading (????? instead of ????? ??? ????)
        [
            '#(class="text-2xl md:text-3xl font-black mb-3 text-white">)\?\?\?\?\?\ \?\?\?\ <span class="text-brand-500">\{\{\ \$siteSettings\[\'site_name\'\]\ \?\?\ \'[?]+\ [?]+\ [?]+\'\ \}\}</span></h2>#',
            '$1انضمي إلى <span class="text-brand-500">{{ $siteSettings[\'site_name\'] ?? \'شركة جنين للتجميل\' }}</span></h2>'
        ],
    ];

    foreach ($patterns as [$regex, $replacement]) {
        $new = preg_replace($regex, $replacement, $content, 1, $matched);
        if ($matched > 0) {
            $content = $new;
            $count++;
            echo "  ✓ Newsletter heading\n";
            break;
        }
    }

    // ═══════════════════ NEWSLETTER SUBTEXT ═══════════════════
    // Just the <p> content
    $patterns = [
        // Full subtext
        [
            '#(class="text-ink-dim mb-8 max-w-lg mx-auto text-sm">)\?\?\?\?\?\ \?\?\?\ \?\?\?\ 10%\ \?\?\?\ \?\?\?\?\ \?\?\?\?\?\?\ \?\?\?\?\?\ \?\?\?\ \?\?\ \?\?\?\?\ \?\?\ \?\?\?\?\?\?\ \?\?\?\?\?\?\?\ \?\?\?\?\?\?\?\?\?\ \?\?\?\?\?\?\?\.</p>#',
            '$1احصلي على خصم 10% على أول طلبية عند الاشتراك في النشرة البريدية الحصرية.</p>'
        ],
        // Shorter subtext variant
        [
            '#(class="text-ink-dim mb-8 max-w-lg mx-auto text-sm">)\?\?\?\?\?\ \?\?\?\ \?\?\?\ 10%\ \?\?\?\ \?\?\?\?\ \?\?\?\?\?\?\ \?\?\?\?\?\ \?\?\?\ \?\?\ \?\?\?\?\ \?\?\ \?\?\?\?\?\?\ \?\?\?\?\?\.</p>#',
            '$1احصلي على خصم 10% على أول طلبية عند الاشتراك في النشرة البريدية الحصرية.</p>'
        ],
    ];

    foreach ($patterns as [$regex, $replacement]) {
        $new = preg_replace($regex, $replacement, $content, 1, $matched);
        if ($matched > 0) {
            $content = $new;
            $count++;
            echo "  ✓ Newsletter subtext\n";
            break;
        }
    }

    // ═══════════════════ INPUT PLACEHOLDER ═══════════════════
    $new = preg_replace(
        '#placeholder="[?]{5}\ [?]{10}"#',
        'placeholder="أدخلي بريدك الإلكتروني"',
        $content, 1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Placeholder\n"; }

    // ═══════════════════ BUTTON TEXT ═══════════════════
    $new = preg_replace(
        '#(background:var\(--gradient-primary\);">)[?]{6}</button>#',
        '$1اشتراك</button>',
        $content, 1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Button\n"; }

    // ═══════════════════ SITE NAME DEFAULTS ═══════════════════
    // All occurrences of ?? '???? ???? ???????'
    $new = preg_replace(
        "#\?\?\ '[?]{4}\ [?]{4}\ [?]{6}'#",
        "?? 'شركة جنين للتجميل'",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Site name defaults ($matched)\n"; }

    // ═══════════════════ BADGE 1 ═══════════════════
    // Title: ضمان 100%
    $new = preg_replace(
        '#(mb-1">)[?]{4}\ 100%</h3>#',
        '$1ضمان 100%</h3>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge1 title\n"; }

    // Cyber-lab badge 1: ?????? ????? 100% -> منتجات أصلية 100%
    $new = preg_replace(
        '#(mb-1">)[?]{6}\ [?]{5}\ 100%</h3>#',
        '$1منتجات أصلية 100%</h3>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge1a title (cyber-lab)\n"; }

    // Badge 1 desc: منتجات أصلية مضمونة
    $new = preg_replace(
        '#(text-xs">)[?]{5}\ [?]{5}\ [?]{8}</p>#',
        '$1منتجات أصلية مضمونة</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge1 desc\n"; }

    // Cyber-lab badge 1 desc: ???????? ????? ????????
    $new = preg_replace(
        '#(text-xs">)[?]{4}\ [?]{8}\ [?]{5}\ [?]{8}</p>#',
        '$1ضمان الجودة والثقة</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge1a desc (cyber-lab)\n"; }

    // ═══════════════════ BADGE 2 ═══════════════════
    $new = preg_replace(
        '#(mb-1">)[?]{3}\ [?]{3}\ [?]{6}</h3>#',
        '$1شحن سريع وآمن</h3>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge2 title\n"; }

    $new = preg_replace(
        '#(text-xs">)[?]{5}\ [?]{5}\ [?]{7}</p>#',
        '$1توصيل لكل فلسطين</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge2 desc\n"; }

    // ═══════════════════ BADGE 3 (lock → دفع آمن) ═══════════════════
    // Use ph-lock context
    $new = preg_replace(
        '#(ph-lock[^<]*</i></div>\s*<h3 class="[^"]*">)[?]{3}\ [?]{3}</h3>#',
        '$1دفع آمن</h3>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge3 (lock)\n"; }

    // Some themes use ph-shield-check for badge 3
    // But ph-shield-check is ALSO used for badge 1! Need to distinguish:
    // badge 1 has "???? 100%" after the icon, badge 3 has "??? ???"
    // For themes where ph-shield-check is used for BOTH badge 1 AND badge 3:
    // We need to match shield-check where the NEXT h3 contains ??? ??? (6 ?s total)
    $new = preg_replace(
        '#(ph-shield-check[^<]*</i></div>\s*<h3 class="[^"]*">)[?]{6}</h3>#',
        '$1دفع آمن</h3>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge3 (shield)\n"; }

    // Badge 3 desc
    $new = preg_replace(
        '#(text-xs">)[?]{5}\ [?]{3}\ [?]{8}</p>#',
        '$1مشفر بالكامل لحماية بياناتك</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge3 desc\n"; }

    // Badge 3 desc (cyber-lab variant with ????)
    $new = preg_replace(
        '#(text-xs">)[?]{5}\ [?]{3}\ [?]{8}\ [?]{4}</p>#',
        '$1مشفر بالكامل لحماية بياناتك</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge3 desc variant\n"; }

    // ═══════════════════ BADGE 4 (headset → دعم فني) ═══════════════════
    $new = preg_replace(
        '#(ph-headset[^<]*</i></div>\s*<h3 class="[^"]*">)[?]{3}\ [?]{3}</h3>#',
        '$1دعم فني</h3>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge4 (headset)\n"; }

    // Badge 4 working hours
    // Pattern: 9 ? - 10 ? ??????
    $new = preg_replace(
        '#(text-xs">)9\ [?]\ -\ 10\ [?]\ [?]{6}</p>#',
        '$19 ص - 10 م طوال الأسبوع</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge4 hours\n"; }

    // Cyber-lab: 9 ? - 10 ? (no ??????)
    $new = preg_replace(
        '#(text-xs">)9\ [?]\ -\ 10\ [?]</p>#',
        '$19 ص - 10 م</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Badge4 hours (short)\n"; }

    // ═══════════════════ SOCIAL MEDIA ═══════════════════
    $social = [
        '??????' => 'فيسبوك',
        '????????' => 'انستغرام',
        '??????' => 'واتساب',
        '??? ???' => 'تيك توك',
        '?????' => 'تويتر',
    ];
    foreach ($social as $old => $newText) {
        $escaped = preg_quote($old, '#');
        $new = preg_replace(
            "#(aria-label=\")$escaped\"#",
            '$1' . $newText . '"',
            $content, -1, $matched
        );
        if ($matched) { $content = $new; $count++; echo "  ✓ Aria-label: $newText\n"; }
    }

    // ═══════════════════ SHOP HEADING ═══════════════════
    $new = preg_replace(
        '#(mb-5 text-sm">)[?]{6}</h5>#',
        '$1تسوقي</h5>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Shop heading\n"; }

    // ═══════════════════ ALL PRODUCTS LINK ═══════════════════
    $new = preg_replace(
        '#(route\(\'shop\'\)[^>]*>)[?]{4}\ [?]{8}</a>#',
        '$1جميع المنتجات</a>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ All products link\n"; }

    // ═══════════════════ SUPPORT HEADING ═══════════════════
    $new = preg_replace(
        '#(mb-5 text-sm">)[?]{4}\ [?]{7}</h5>#',
        '$1روابط مهمة</h5>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Support heading\n"; }

    // ═══════════════════ CONTACT HEADING ═══════════════════
    $new = preg_replace(
        '#(mb-5 text-sm">)[?]{5}\ [?]{4}</h5>#',
        '$1اتصل بنا</h5>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Contact heading\n"; }

    // ═══════════════════ POLICY LINKS ═══════════════════
    $links = [
        '????? ????????' => 'سياسة الشحن',
        '????? ???????' => 'سياسة الإرجاع',
        '??????? ???????' => 'الأسئلة الشائعة',
        '?????? ????????' => 'شروط الاستخدام',
        '????? ????????' => 'سياسة الخصوصية',
        '????? ????' => 'اتصل بنا',
    ];

    // For policy links, use route name as context
    $routeLinks = [
        'shipping-policy' => '????? ????????',
        'return-policy' => '????? ???????',
        'faq' => '??????? ???????',
        'terms' => '?????? ????????',
        'privacy' => '????? ????????',
        'contact' => '????? ????',
    ];

    foreach ($routeLinks as $route => $qText) {
        $escaped = preg_quote($qText, '#');
        $arText = $links[$qText] ?? $qText;
        $new = preg_replace(
            "#(route\('$route'\)[^>]*>)$escaped</a>#",
            '$1' . $arText . '</a>',
            $content, -1, $matched
        );
        if ($matched) { $content = $new; $count++; echo "  ✓ Link: $arText\n"; }
    }

    // ═══════════════════ COUPON LINK ═══════════════════
    $new = preg_replace(
        '#(ph-gift[^>]*></i>)[?]{3}\ [?]{4}</a>#',
        '$1رمز الكوبون</a>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Coupon link\n"; }

    // ═══════════════════ ADDRESS DEFAULT ═══════════════════
    $new = preg_replace(
        "#\?\?\ '[?]{7}\ [?]{3}\ [?]{4}' }}#",
        "?? 'فلسطين - رام الله' }}",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Address default\n"; }

    // ═══════════════════ WORKING HOURS ═══════════════════
    $new = preg_replace(
        '#<span>[?]{6}\ 9:00\ [?]\ -\ 10:00\ [?]</span>#',
        '<span>أوقات العمل 9:00 ص - 10:00 م</span>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Working hours\n"; }

    // ═══════════════════ SITE DESCRIPTION ═══════════════════
    $descPatterns = [
        // organic-spa: ????? ??????? ??????? ??????? ????????.
        [
            "#\?\?\ '[?]{5}\ [?]{7}\ [?]{7}\ [?]{7}\ [?]{8}\.' }}#",
            "?? 'منتجات تجميل أصلية للعناية بالبشرة والشعر في فلسطين.' }}"
        ],
        // luxury/editorial: longer
        [
            "#\?\?\ '[?]{5}\ [?]{7}\ [?]{7}\ [?]{7}\ [?]{7}\ [?]{5}\ [?]{11}\ [?]{5}\ [?]{9}\ [؟]{2}\ [?]{6}\.' }}#",
            "?? 'منتجات تجميل أصلية للعناية بالبشرة والشعر مع خدمة توصيل سريعة وآمنة في فلسطين.' }}"
        ],
        // cyber-lab: even longer with ???? ?????? ???? ?????.
        [
            "#\?\?\ '[?]{5}\ [?]{7}\ [?]{7}\ [?]{7}\ [?]{7}\ [?]{5}\ [?]{11}\ [?]{5}\ [?]{9}\ [؟]{2}\ [?]{6}\.\ [؟]{4}\ [؟]{6}\ [؟]{4}\ [؟]{5}\.' }}#",
            "?? 'منتجات تجميل أصلية للعناية بالبشرة والشعر مع خدمة توصيل سريعة وآمنة في فلسطين. شحن لكافة المدن الفلسطينية.' }}"
        ],
    ];

    foreach ($descPatterns as [$regex, $replacement]) {
        $new = preg_replace($regex, $replacement, $content, 1, $matched);
        if ($matched > 0) {
            $content = $new;
            $count++;
            echo "  ✓ Site description\n";
            break;
        }
    }

    // ═══════════════════ COPYRIGHT ═══════════════════
    $new = preg_replace(
        '#(&copy;\ \{\{\ date\(\'Y\'\)\ \}\}\ \{\{\ \$siteSettings\[\'site_name\'\]\ \?\?\ \')[?]{4}\ [?]{4}\ [?]{6}(\'\ \}\}\.)[?]{4}\ [?]{6}\ [?]{6}\.</p>#',
        '$1شركة جنين للتجميل$2 جميع الحقوق محفوظة.</p>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Copyright\n"; }

    // ═══════════════════ PAYMENT BADGES ═══════════════════
    // COD: ????? ??? ???????? with rounded-full context
    $new = preg_replace(
        '#(rounded-full[^>]*>)[?]{5}\ [?]{3}\ [?]{8}</span>#',
        '$1الدفع عند الاستلام</span>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ COD badge\n"; }

    // Jawwal Pay: ???? ???
    $new = preg_replace(
        '#(rounded-full[^>]*>)[?]{4}\ [?]{3}</span>#',
        '$1جوال باي</span>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Jawwal badge\n"; }

    // Bank transfer: ????? ????
    $new = preg_replace(
        '#(rounded-full text-ink-dim">)[?]{5}\ [?]{4}</span>#',
        '$1تحويل بنكي</span>',
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ Bank badge\n"; }

    // ═══════════════════ JAVASCRIPT ═══════════════════
    // Loading text
    $new = preg_replace(
        "#(innerHTML\s*=\s*')[?]{4}\.\.\.'#",
        "$1جاري...'",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ JS loading\n"; }

    // Error fallback '???'
    $new = preg_replace(
        "#(d\.message\|\|')[?]{3}'#",
        "$1خطأ'",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ JS error fallback\n"; }

    // '??? ???' -> 'حدث خطأ'
    $new = preg_replace(
        "#'[?]{3}\ [?]{3}'#",
        "'حدث خطأ'",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ JS error msg\n"; }

    // '??? ??? ?? ???????' -> longer error
    $new = preg_replace(
        "#'[?]{3}\ [?]{2}\ [?]{7}'#",
        "'حدث خطأ من فضلك حاولي مرة أخرى'",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ JS error long\n"; }

    // Button restore
    $new = preg_replace(
        "#(btn\.innerHTML\s*=\s*')[?]{6}'#",
        "$1اشتراك'",
        $content, -1, $matched
    );
    if ($matched) { $content = $new; $count++; echo "  ✓ JS button restore\n"; }

    // ═══════════════════ EDITORIAL-SPECIFIC SOCIAL LINKS ═══════════════════
    if ($theme === 'editorial') {
        $editorialSocial = [
            '??????' => 'فيسبوك',
            '????????' => 'انستغرام',
            '?????' => 'تويتر',
            '??? ???' => 'تيك توك',
        ];
        foreach ($editorialSocial as $old => $newText) {
            $escaped = preg_quote($old, '#');
            $new = preg_replace(
                "#(transition-colors\">)$escaped</a>#",
                '$1' . $newText . '</a>',
                $content, -1, $matched
            );
            if ($matched) { $content = $new; $count++; echo "  ✓ Editorial social: $newText\n"; }
        }
    }

    // ═══════════════════ FALLBACK: Any remaining ? in Arabic context ═══════════════════
    // Count remaining ? outside Blade comments
    $remaining = 0;
    $inComment = false;
    for ($i = 0; $i < strlen($content); $i++) {
        if (substr($content, $i, 4) === '{{--') { $inComment = true; $i += 3; continue; }
        if (substr($content, $i, 4) === '--}}') { $inComment = false; $i += 3; continue; }
        if (!$inComment && $content[$i] === '?') {
            if ($i + 1 < strlen($content) && $content[$i+1] === '?') continue;
            $remaining++;
        }
    }

    if ($remaining > 0) {
        echo "  ⚠ WARNING: $remaining ? chars remaining:\n";
        $shown = 0;
        $inComment = false;
        for ($i = 0; $i < strlen($content); $i++) {
            if (substr($content, $i, 4) === '{{--') { $inComment = true; $i += 3; continue; }
            if (substr($content, $i, 4) === '--}}') { $inComment = false; $i += 3; continue; }
            if (!$inComment && $content[$i] === '?' && ($i === 0 || $content[$i-1] !== '?')) {
                if ($shown < 15) {
                    $ctx = substr($content, max(0, $i-25), 60);
                    $ctx = str_replace(["\r", "\n"], ['', '\\n'], $ctx);
                    echo "    pos $i: ...$ctx...\n";
                    $shown++;
                }
            }
        }
    } else {
        echo "  ✓ No remaining ? outside comments!\n";
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "  ✓ Saved\n";
        return true;
    }
    echo "  ⚠ No changes\n";
    return false;
}

$changed = 0;
foreach ($files as $f) {
    if (fixFile($f)) $changed++;
}
echo "\nDone! $changed of " . count($files) . " files modified.\n";
