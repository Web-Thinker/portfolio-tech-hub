<?php
header('Content-Type: application/json');
$config = require __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass']
    );
    
    $stmt = $pdo->query("SELECT * FROM about_page WHERE id=1");
    $about = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$about) {
        echo json_encode([
            'bio_ru' => 'Информация о себе будет добавлена позже.',
            'bio_en' => 'Information about myself will be added later.',
            'tech_stack' => 'PHP, JavaScript, MySQL',
            'timeline' => '[]'
        ]);
    } else {
        echo json_encode($about);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}