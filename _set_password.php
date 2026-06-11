<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=jenincare', 'root', '');
$hash = password_hash('Jenin@2026!', PASSWORD_BCRYPT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = 'admin@jenincare.shop'");
$stmt->execute([$hash]);
echo "Updated password for admin@jenincare.shop\n";

// Verify
$stmt = $pdo->query("SELECT password FROM users WHERE email = 'admin@jenincare.shop'");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Hash stored: " . substr($row['password'], 0, 30) . "...\n";
echo "Verify: " . (password_verify('Jenin@2026!', $row['password']) ? 'PASS' : 'FAIL') . "\n";
