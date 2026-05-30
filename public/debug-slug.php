<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

$posts = DB::table('blog_posts')->orderByDesc('id')->limit(5)->get();
echo "<h3>آخر 5 مقالات في قاعدة البيانات</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse:collapse;direction:rtl;font-family:Tajawal,sans-serif;'>";
echo "<tr><th>ID</th><th>العنوان</th><th>Slug</th><th>is_published</th><th>deleted_at</th><th>image</th></tr>";
foreach ($posts as $p) {
    $del = $p->deleted_at ? '🟡 محذوف' : '🟢 موجود';
    $pub = $p->is_published ? '✅ منشور' : '❌ مخفي';
    echo "<tr>
        <td>{$p->id}</td>
        <td>" . htmlspecialchars($p->title_ar ?? '') . "</td>
        <td><code>{$p->slug}</code></td>
        <td>{$pub}</td>
        <td>{$del}</td>
        <td>{$p->image}</td>
    </tr>";
}
echo "</table>";
echo "<p>الرابط الذي تحاول فتحه: <code>/blog/ghaz-azal-alshaar-ballyzr-alma-soprano-altkny-alahdth-oalakthr-amana</code></p>";
