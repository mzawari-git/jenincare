<?php
/**
 * ترحيل صور المقالات من مجلد storage إلى public/uploads/blog
 * شغّل هذا الملف مرة واحدة فقط
 */
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

$oldDir = __DIR__ . '/../storage/app/public/blog';
$newDir = __DIR__ . '/uploads/blog';

if (!is_dir($oldDir)) {
    die("المجلد القديم غير موجود: $oldDir\nربما الصور في مكان آخر أو لا توجد صور قديمة.\n");
}

if (!is_dir($newDir)) {
    mkdir($newDir, 0755, true);
}

$rows = DB::table('blog_posts')->whereNotNull('image')->where('image', 'not like', 'uploads/%')->get();
$count = 0;

foreach ($rows as $row) {
    $oldFile = $oldDir . '/' . basename($row->image);
    $newFile = $newDir . '/' . basename($row->image);

    if (file_exists($oldFile) && !file_exists($newFile)) {
        copy($oldFile, $newFile);
        echo "نسخ: {$row->image} → uploads/blog/" . basename($row->image) . "\n";
    }

    DB::table('blog_posts')->where('id', $row->id)->update([
        'image' => 'uploads/blog/' . basename($row->image)
    ]);
    $count++;
}

echo "\nتم تحديث $count صورة بنجاح.\n";
