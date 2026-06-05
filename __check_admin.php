<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = DB::table('users')->where('is_admin', true)->get();
if ($users->isEmpty()) {
    echo "NO ADMIN USERS FOUND\n";
} else {
    foreach ($users as $u) {
        $passOk = password_verify('admin123!@#', $u->password);
        echo "Admin: {$u->email} | pass_ok: " . ($passOk ? 'YES' : 'NO') . "\n";
        echo "  name: {$u->name}\n";
        echo "  pass_hash_start: " . substr($u->password, 0, 20) . "...\n";
    }
}
