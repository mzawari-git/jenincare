<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$secret = $_GET['key'] ?? '';
if ($secret !== 'jenincare-reset-2026') {
    http_response_code(403);
    die('Access denied');
}

$emails = ['admin@jenincare.shop', 'admin@jenincare.com'];
$results = [];

foreach ($emails as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->update(['password' => bcrypt('Jenin@2026!')]);
        $results[] = "Reset: $email";
    } else {
        $results[] = "Not found: $email";
    }
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'results' => $results,
    'login' => 'admin@jenincare.shop / Jenin@2026!',
    'warning' => 'DELETE THIS FILE AFTER USE: public/reset-admin-pw.php'
]);
