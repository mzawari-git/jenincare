<?php
/**
 * Auto-Deploy Webhook for شركة جنين للتجميل
 * 
 * Place this file on your production server at: public/deploy.php
 * Then add a GitHub webhook pointing to:
 *   https://www.jenincare.shop/deploy.php
 * 
 * GitHub Webhook Settings:
 *   - URL: https://www.jenincare.shop/deploy.php
 *   - Content type: application/json
 *   - Secret: (set DEPLOY_SECRET in the script below)
 *   - Events: Just the push event
 */

$secret = getenv('DEPLOY_SECRET') ?: 'jenincare-deploy-2026';

$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$payload = file_get_contents('php://input');

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    die('Invalid signature');
}

$data = json_decode($payload, true);
$branch = str_replace('refs/heads/', '', $data['ref'] ?? '');

if ($branch !== 'master' && $branch !== 'main') {
    die("Ignored branch: {$branch}");
}

$output = [];
$exitCode = 0;

chdir(dirname(__DIR__));

exec('git fetch origin 2>&1', $output, $exitCode);
exec('git reset --hard origin/master 2>&1', $output, $exitCode);
exec('composer install --no-interaction --prefer-dist --no-dev 2>&1', $output, $exitCode);
exec('php artisan migrate --force 2>&1', $output, $exitCode);
exec('php artisan config:clear 2>&1', $output, $exitCode);
exec('php artisan route:clear 2>&1', $output, $exitCode);
exec('php artisan view:clear 2>&1', $output, $exitCode);
exec('php artisan cache:clear 2>&1', $output, $exitCode);

file_put_contents(
    dirname(__DIR__) . '/storage/logs/deploy.log',
    date('[Y-m-d H:i:s]') . " Deploy from {$branch}\n" . implode("\n", $output) . "\n---\n",
    FILE_APPEND
);

echo "Deployed successfully from {$branch}\n";
echo implode("\n", array_slice($output, -5));
