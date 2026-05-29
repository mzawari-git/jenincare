<?php
$base = __DIR__ . '/..';
require $base . '/vendor/autoload.php';
$app = require_once $base . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "<pre>";
echo "Running migration fix...\n";

try {
    Schema::table('identity_events', function (Blueprint $table) {
        $table->text('url')->nullable()->change();
        $table->text('referer')->nullable()->change();
    });
    echo "SUCCESS: url and referer columns changed to TEXT.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
