<?php
/**
 * RUN THIS FILE ON YOUR PRODUCTION SERVER
 * Place it in public_html/ and visit: https://www.jenincare.shop/fix-cat-images.php
 * Then DELETE it after running.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$imageMap = [
    '💎 قسم العطور ومنتجات العناية بالتعرق Perfumes & Deodorant Solutions' => 'images/categories/cat_22.webp',
    '💎 قسم البيرسينج Piercing Station' => 'images/categories/cat_14.webp',
    '✨ قسم العناية بالجسم والسبا Body Care & Spa Services' => 'images/categories/cat_21.webp',
    '🩸 قسم خدمات الحجامة والعلاج البديل Cupping & Wellness Solutions' => 'images/categories/cat_11.webp',
    '🎁 قسم العروض والتخفيضات الحصرية Exclusive Offers & Bundles' => 'images/categories/cat_20.webp',
    '💉 قسم الميزوثيرابي والمستلزمات الطبية التجميلية Mesotherapy & Supplies' => 'images/categories/cat_7.webp',
    'قسم العناية بالشعر Hair Care Solutions' => 'images/categories/cat_8.webp',
    '👀 قسم الرموش ورفع الحواجب Lashes & Brow Lamination' => 'images/categories/cat_25.webp',
    '👑 قسم تجهيز الصالونات النسائية الشامل Ladies Salon Solutions' => 'images/categories/cat_17.webp',
    '💄 قسم المكياج والتجميل Makeup & Artistry' => 'images/categories/cat_13.webp',
    '🎨 قسم الصبغات وحلول تغيير اللون Professional Hair Color Solutions' => 'images/categories/cat_19.webp',
    '🌸 قسم منتجات مسك  Musk' => 'images/categories/cat_23.webp',
    '🧖‍♀️ قسم العناية بالبشرة الشاملةAdvanced Skincare Solutions' => 'images/categories/cat_15.webp',
    'قسم العناية بالأظافر Nails & Pedicure Corner' => 'images/categories/cat_9.webp',
    '🔬 قسم أجهزة العناية بالبشرة والليزر Skin & Laser Technology' => 'images/categories/cat_12.webp',
    '⚡ قسم إزالة الشعر الشامل Advanced Hair Removal Solutions' => 'images/categories/cat_10.webp',
    '👣 قسم الباديكير الفاخر Luxury Pedicure Stations' => 'images/categories/cat_16.webp',
    '🧔 قسم العناية باللحية Professional Beard Grooming' => 'images/categories/cat_24.webp',
    '💈 قسم تجهيز الصالونات الرجالية Barber Shop Solutions' => 'images/categories/cat_18.webp',
];

$updated = 0;
foreach ($imageMap as $arName => $path) {
    $count = DB::table('categories')->where('name_ar', $arName)->update(['image' => $path]);
    if ($count) {
        echo "✅ Updated: {$arName}<br>";
        $updated++;
    } else {
        echo "❌ Not found: {$arName}<br>";
    }
}

echo "<br><strong>Total updated: {$updated}</strong><br>";
echo "<strong>NOW DELETE THIS FILE!</strong>";
