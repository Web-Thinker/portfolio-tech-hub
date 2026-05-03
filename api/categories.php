<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/../config.php';

$type = $_GET['type'] ?? null;

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass']
    );
    
    if ($type) {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE type = ? ORDER BY title_ru");
        $stmt->execute([$type]);
    } else {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY title_ru");
    }
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}