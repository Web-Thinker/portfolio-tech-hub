<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass']
    );
    
    $stmt = $pdo->query("SELECT s.*, c.title_ru as category_title FROM services s LEFT JOIN categories c ON s.category_id = c.id WHERE s.published = 1 ORDER BY s.created_at DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($services);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}