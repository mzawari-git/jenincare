<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BlogPost;
use Illuminate\Support\Facades\DB;

// Image map: post_id => [main_image, secondary_image_name]
$imageMap = [
    7 => [
        'main' => 'candela-main.png',
        'sec'  => 'candela-sec.png',
        'sec_alt' => 'جهاز Candela GentleMax Pro Plus ليزر الإزالة الشعر',
    ],
    8 => [
        'main' => 'bodysculpt-main.jpg',
        'sec'  => 'bodysculpt-sec.jpg',
        'sec_alt' => 'جهاز نحت الجسم 6 في 1 لتجميد الدهون وتحفيز العضلات',
    ],
    9 => [
        'main' => 'skinanalyzer-main.jpg',
        'sec'  => 'skinanalyzer-sec.jpg',
        'sec_alt' => 'جهاز تحليل البشرة الذكي Meicet MC88',
    ],
    10 => [
        'main' => 'wellnesspod-main.jpg',
        'sec'  => 'wellnesspod-sec.jpg',
        'sec_alt' => 'كبسولة السبا والعناية المتكاملة Wellness Pod',
    ],
    11 => [
        'main' => 'legalguide-main.jpeg',
        'sec'  => 'legalguide-sec.jpeg',
        'sec_alt' => 'الدليل التشريعي لترخيص مراكز التجميل والليزر',
    ],
];

// Also set images for older posts (IDs 2-6) using Unsplash
$oldPostsImageMap = [
    2 => 'https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?w=800', // fractional CO2
    3 => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=800', // Q-switched laser
    4 => 'https://images.unsplash.com/photo-1559305616-3f99cd43e353?w=800',  // hydro facial
    5 => 'https://images.unsplash.com/photo-1596755389378-c31d21fd1273?w=800', // skin treatment
    6 => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800', // documents/guide
];

echo "=== Updating blog post images ===\n\n";

// Download external images for old posts
$blogDir = __DIR__ . '/public/uploads/blog';
echo "Downloading images for older posts...\n";
foreach ($oldPostsImageMap as $postId => $url) {
    $filename = 'post-' . $postId . '-main.jpg';
    $dest = $blogDir . '/' . $filename;
    if (!file_exists($dest)) {
        $ch = curl_init($url);
        $fp = fopen($dest, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if ($httpCode === 200) {
            echo "  Downloaded: $filename\n";
        } else {
            echo "  FAILED ($httpCode): $filename from $url\n";
            unlink($dest);
        }
    } else {
        echo "  Already exists: $filename\n";
    }
}

echo "\n";

// Update images for all posts
DB::beginTransaction();
try {
    // Update new posts with local images
    foreach ($imageMap as $postId => $images) {
        $post = BlogPost::find($postId);
        if (!$post) {
            echo "  [SKIP] Post ID $postId not found\n";
            continue;
        }

        // Set main image
        $post->image = $images['main'];

        // Inject secondary image into content after first <h2>
        $imgUrl = '/uploads/blog/' . $images['sec'];
        $secImgHtml = "\n\n<div style=\"text-align:center;margin:25px 0;\">\n"
            . '    <img src="' . $imgUrl . '"'
            . ' alt="' . $images['sec_alt'] . '"'
            . ' style="max-width:100%;border-radius:16px;box-shadow:0 4px 20px rgba(0,0,0,.1);">'
            . "\n</div>\n\n";

        // Insert the secondary image after the first </h2>
        $content = $post->content_ar;
        $pos = strpos($content, '</h2>');
        if ($pos !== false) {
            $content = substr_replace($content, '</h2>' . $secImgHtml, $pos, 5);
        }

        $post->content_ar = $content;
        $post->save();

        echo "  [UPDATED] Post ID $postId: {$post->title_ar}\n";
        echo "            Main image: {$images['main']}\n";
        echo "            Sec image: {$images['sec']}\n\n";
    }

    // Update old posts with downloaded images
    foreach ($oldPostsImageMap as $postId => $url) {
        $post = BlogPost::find($postId);
        if (!$post) {
            echo "  [SKIP] Post ID $postId not found\n";
            continue;
        }
        $filename = 'post-' . $postId . '-main.jpg';
        $post->image = $filename;
        $post->save();
        echo "  [UPDATED] Post ID $postId: {$post->title_ar} -> $filename\n";
    }

    DB::commit();
    echo "\n=== All images updated successfully! ===\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
}
