<?php
/**
 * Jenin Care - Maintenance Script
 * 
 * Usage:
 *   Deploy: https://www.jenincare.shop/public/maintain.php?action=deploy&key=jenincare2026
 *   Reset Password: https://www.jenincare.shop/public/maintain.php?action=reset&key=jenincare2026
 *   Clear Cache: https://www.jenincare.shop/public/maintain.php?action=cache&key=jenincare2026
 * 
 * DELETE THIS FILE AFTER USE!
 */

$secret = $_GET['key'] ?? '';
$action = $_GET['action'] ?? '';

if ($secret !== 'jenincare2026') {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid key']));
}

header('Content-Type: application/json');

// Find project root (go up from public/)
$projectRoot = dirname(__DIR__);

// Bootstrap Laravel
require $projectRoot . '/vendor/autoload.php';
$app = require_once $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = ['action' => $action, 'success' => false, 'output' => []];

switch ($action) {
    case 'deploy':
        chdir($projectRoot);
        $commands = [
            'git fetch origin 2>&1',
            'git reset --hard origin/master 2>&1',
            'composer install --no-interaction --prefer-dist --no-dev 2>&1',
            'php artisan migrate --force 2>&1',
        ];
        foreach ($commands as $cmd) {
            $output = [];
            exec($cmd, $output, $code);
            $result['output'][] = ['cmd' => $cmd, 'output' => implode("\n", $output), 'code' => $code];
        }
        // Clear caches after deploy
        artisan('config:clear');
        artisan('route:clear');
        artisan('view:clear');
        artisan('cache:clear');
        $result['success'] = true;
        $result['message'] = 'Deployment completed';
        break;

    case 'reset':
        $user = \App\Models\User::where('email', 'admin@jenincare.shop')->first();
        if ($user) {
            $user->update(['password' => bcrypt('Jenin@2026!')]);
            $result['success'] = true;
            $result['message'] = 'Password reset for admin@jenincare.shop';
            $result['login'] = ['email' => 'admin@jenincare.shop', 'password' => 'Jenin@2026!'];
        } else {
            $result['error'] = 'User not found';
        }
        break;

    case 'cache':
        artisan('config:clear');
        artisan('route:clear');
        artisan('view:clear');
        artisan('cache:clear');
        $result['success'] = true;
        $result['message'] = 'All caches cleared';
        break;

    case 'info':
        $result['success'] = true;
        $result['info'] = [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'project_root' => $projectRoot,
            'admin_exists' => \App\Models\User::where('email', 'admin@jenincare.shop')->exists(),
        ];
        break;

    default:
        $result['error'] = 'Unknown action. Use: deploy, reset, cache, or info';
}

echo json_encode($result, JSON_PRETTY_PRINT);

function artisan($command) {
    \Illuminate\Support\Facades\Artisan::call($command);
}
