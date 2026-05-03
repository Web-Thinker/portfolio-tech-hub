<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass']
    );
    
    $stmt = $pdo->query("SELECT p.*, c.title_ru as category_title FROM posts p LEFT JOIN categories c ON p.category_id = c.id WHERE p.published = 1 ORDER BY p.created_at DESC LIMIT 10");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($posts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}