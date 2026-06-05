<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=jenincare', 'root', '');
$pdo->prepare("INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, abilities, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())")->execute([
    'App\Models\User', 1, 'test-script2', hash('sha256', 'test-token-2'), '["*"]'
]);
$tokenId = $pdo->lastInsertId();
$fullToken = "$tokenId|test-token-2";

$scanId = '01kt8xphrq4w8ys3hbvhvsybxd'; // latest approved

$ch = curl_init("http://localhost/jenincare/public/api/v1/scans/$scanId/report");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Accept: application/json", "Authorization: Bearer $fullToken"],
    CURLOPT_RETURNTRANSFER => true,
]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP $code\n";
if ($code === 200) {
    $data = json_decode($body, true);
    echo "scan.id: " . ($data['scan']['id'] ?? 'MISSING') . "\n";
    echo "defects count: " . count($data['defects'] ?? []) . "\n";
    echo "general_tips count: " . count($data['general_tips'] ?? []) . "\n";
    echo "heatmap_points count: " . count($data['heatmap_points'] ?? []) . "\n";
    if (!empty($data['defects'])) {
        $d = $data['defects'][0];
        echo "defect[0] id: {$d['id']} name: {$d['name_en']}\n";
        echo "defect[0] recommended_products: " . count($d['recommended_products'] ?? []) . "\n";
        if (!empty($d['recommended_products'])) {
            $p = $d['recommended_products'][0];
            echo "  product: {$p['name_en']} price: {$p['price']}\n";
        }
    }
} else {
    echo "ERROR: " . substr($body, 0, 1000);
}
