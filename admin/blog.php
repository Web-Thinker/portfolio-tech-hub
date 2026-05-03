<?php
session_start();
$config = require __DIR__ . '/../config.php';

// Check auth
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
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create' || $_POST['action'] === 'update') {
            $title_ru = trim($_POST['title_ru']);
            $title_en = trim($_POST['title_en']);
            $excerpt_ru = trim($_POST['excerpt_ru']);
            $excerpt_en = trim($_POST['excerpt_en']);
            $content_ru = trim($_POST['content_ru']);
            $content_en = trim($_POST['content_en']);
            $category_id = intval($_POST['category_id']);
            $image_url = trim($_POST['image_url']);
            $published = isset($_POST['published']) ? 1 : 0;
            
            if ($_POST['action'] === 'create') {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title_ru)));
                $stmt = $pdo->prepare("INSERT INTO posts (slug, title_ru, title_en, excerpt_ru, excerpt_en, content_ru, content_en, category_id, image_url, published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$slug, $title_ru, $title_en, $excerpt_ru, $excerpt_en, $content_ru, $content_en, $category_id, $image_url, $published]);
                $success = 'Статья успешно создана';
            } else {
                $id = intval($_POST['id']);
                $stmt = $pdo->prepare("UPDATE posts SET title_ru=?, title_en=?, excerpt_ru=?, excerpt_en=?, content_ru=?, content_en=?, category_id=?, image_url=?, published=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$title_ru, $title_en, $excerpt_ru, $excerpt_en, $content_ru, $content_en, $category_id, $image_url, $published, $id]);
                $success = 'Статья успешно обновлена';
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = intval($_POST['id']);
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id=?");
            $stmt->execute([$id]);
            $success = 'Статья удалена';
        }
    }
}

// Get posts
$posts = $pdo->query("SELECT p.*, c.title_ru as category_title FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories = $pdo->query("SELECT * FROM categories WHERE type='blog' ORDER BY title_ru")->fetchAll(PDO::FETCH_ASSOC);

// Get post for editing
$editPost = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->execute([$id]);
    $editPost = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление блогом - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Управление блогом</h1>
            <div class="flex gap-4">
                <a href="about.php" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Страница "Об авторе"</a>
                <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Выйти</a>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-6"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4"><?= $editPost ? 'Редактировать статью' : 'Добавить статью' ?></h2>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="<?= $editPost ? 'update' : 'create' ?>">
                <?php if ($editPost): ?>
                    <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Заголовок (RU)</label>
                        <input type="text" name="title_ru" required value="<?= htmlspecialchars($editPost['title_ru'] ?? '') ?>" class="w-full border rounded px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Заголовок (EN)</label>
                        <input type="text" name="title_en" required value="<?= htmlspecialchars($editPost['title_en'] ?? '') ?>" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Краткое описание (RU)</label>
                        <textarea name="excerpt_ru" rows="2" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($editPost['excerpt_ru'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Краткое описание (EN)</label>
                        <textarea name="excerpt_en" rows="2" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($editPost['excerpt_en'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Содержание (RU)</label>
                        <textarea name="content_ru" rows="6" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($editPost['content_ru'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Содержание (EN)</label>
                        <textarea name="content_en" rows="6" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($editPost['content_en'] ?? '') ?></textarea>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Категория</label>
                        <select name="category_id" class="w-full border rounded px-3 py-2">
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($editPost['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['title_ru']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">URL изображения</label>
                        <input type="url" name="image_url" value="<?= htmlspecialchars($editPost['image_url'] ?? '') ?>" class="w-full border rounded px-3 py-2">
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="published" value="1" <?= ($editPost['published'] ?? 0) ? 'checked' : '' ?> class="mr-2">
                        Опубликовано
                    </label>
                </div>
                
                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                        <?= $editPost ? 'Обновить' : 'Создать' ?>
                    </button>
                    <?php if ($editPost): ?>
                        <a href="blog.php" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">Отмена</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Posts list -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Статьи</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Заголовок</th>
                            <th class="px-4 py-2 text-left">Категория</th>
                            <th class="px-4 py-2 text-left">Статус</th>
                            <th class="px-4 py-2 text-left">Дата</th>
                            <th class="px-4 py-2 text-left">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= $post['id'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($post['title_ru']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($post['category_title'] ?? 'Без категории') ?></td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-sm <?= $post['published'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= $post['published'] ? 'Опубликовано' : 'Черновик' ?>
                                </span>
                            </td>
                            <td class="px-4 py-2"><?= $post['created_at'] ?></td>
                            <td class="px-4 py-2">
                                <a href="?edit=<?= $post['id'] ?>" class="text-blue-600 hover:underline">Редактировать</a>
                                <form method="POST" class="inline" onsubmit="return confirm('Удалить статью?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:underline ml-2">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>