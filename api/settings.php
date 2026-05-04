<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass']
    );
    
    $stmt = $pdo->query("SELECT * FROM site_settings WHERE id=1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$settings) {
        echo json_encode([]);
    } else {
        echo json_encode($settings);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}