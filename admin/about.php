<?php
session_start();
$config = require __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$pdo = new PDO(
    "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
    $config['db']['user'],
    $config['db']['pass']
);

$success = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio_ru = trim($_POST['bio_ru']);
    $bio_en = trim($_POST['bio_en']);
    $tech_stack = trim($_POST['tech_stack']);
    $timeline = trim($_POST['timeline']);
    
    // Check if record exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM about_page");
    $exists = $stmt->fetchColumn() > 0;
    
    if ($exists) {
        $stmt = $pdo->prepare("UPDATE about_page SET bio_ru=?, bio_en=?, tech_stack=?, timeline=?, updated_at=NOW() WHERE id=1");
        $stmt->execute([$bio_ru, $bio_en, $tech_stack, $timeline]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO about_page (id, bio_ru, bio_en, tech_stack, timeline, created_at) VALUES (1, ?, ?, ?, ?, NOW())");
        $stmt->execute([$bio_ru, $bio_en, $tech_stack, $timeline]);
    }
    
    $success = 'Страница успешно обновлена';
}

// Get current content
$stmt = $pdo->query("SELECT * FROM about_page WHERE id=1");
$about = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if empty
$about = $about ?: [
    'bio_ru' => 'Привет! Я IT-специалист с многолетним опытом в разработке программного обеспечения, дизайне и системном администрировании.',
    'bio_en' => 'Hello! I am an IT specialist with many years of experience in software development, design and system administration.',
    'tech_stack' => 'JavaScript, TypeScript, React, Next.js, Node.js, PHP, MySQL, PostgreSQL, Docker, Git, Tailwind CSS, Figma, Adobe XD, Linux, Nginx',
    'timeline' => '[{"year":"2020-н.в.","title":"Senior Fullstack Developer","company":"Tech Company"},{"year":"2017-2020","title":"Middle Developer","company":"Web Studio"},{"year":"2015-2017","title":"Junior Developer","company":"StartUp"}]'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование "Об авторе" - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Страница "Об авторе"</h1>
            <div class="flex gap-4">
                <a href="blog.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Блог</a>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Выйти</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-lg font-semibold mb-2">Биография (RU)</label>
                    <textarea name="bio_ru" rows="4" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($about['bio_ru']) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-lg font-semibold mb-2">Биография (EN)</label>
                    <textarea name="bio_en" rows="4" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($about['bio_en']) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-lg font-semibold mb-2">Технологии (через запятую)</label>
                    <textarea name="tech_stack" rows="2" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($about['tech_stack']) ?></textarea>
                </div>
                
                <div>
                    <label class="block text-lg font-semibold mb-2">Опыт работы (JSON)</label>
                    <textarea name="timeline" rows="6" class="w-full border rounded px-3 py-2 font-mono text-sm"><?= htmlspecialchars($about['timeline']) ?></textarea>
                    <p class="text-sm text-gray-600 mt-1">Формат: [{"year":"2020","title":"Developer","company":"Company"}]</p>
                </div>
                
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Сохранить изменения
                </button>
            </form>
        </div>
    </div>
</body>
</html>