<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=jenincare', 'root', '');
$stmt = $pdo->query("SELECT id, name, email, role FROM users WHERE email = 'admin@jenincare.shop'");
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    echo "User EXISTS:\n";
    print_r($user);
} else {
    echo "User NOT FOUND\n";
    
    // Check what users exist
    $stmt = $pdo->query("SELECT id, name, email, role FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Current users:\n";
    print_r($users);
}
