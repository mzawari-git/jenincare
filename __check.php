<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jenincare', 'root', '');
$pdo->prepare("INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, abilities, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([
    'App\Models\User', 1, 'test-script3', hash('sha256', 'test-token-3'), '["*"]'
]);
$tokenId = $pdo->lastInsertId();
$fullToken = "$tokenId|test-token-3";

// Test latest approved scan
$scanId = '01kt8xphrq4w8ys3hbvhvsybxd';
$ch = curl_init("http://localhost/jenincare/public/api/v1/scans/$scanId/report");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Accept: application/json", "Authorization: Bearer $fullToken"],
    CURLOPT_RETURNTRANSFER => true,
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($body, true);

echo "HTTP $code\n";
echo "defects count: " . count($data['defects'] ?? []) . "\n";
echo "general_tips count: " . count($data['general_tips'] ?? []) . "\n";
echo "\n--- FULL RESPONSE ---\n";
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
