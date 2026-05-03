<?php
session_start();
$config = require __DIR__ . '/../config.php';

// Simple auth
if (isset($_POST['login']) && isset($_POST['password'])) {
    if ($_POST['login'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

if (!isset($_SESSION['admin'])) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <form method="POST" class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6">Вход в админку</h1>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <input type="text" name="login" placeholder="Логин" required class="w-full p-3 border rounded mb-4">
            <input type="password" name="password" placeholder="Пароль" required class="w-full p-3 border rounded mb-4">
            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded hover:bg-blue-700">Войти</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass']
    );
    
    if ($_POST['action'] === 'update_service') {
        $stmt = $pdo->prepare("UPDATE services SET title_ru=?, description_ru=?, published=? WHERE id=?");
        $stmt->execute([$_POST['title'], $_POST['description'], $_POST['published'], $_POST['id']]);
        $success = 'Услуга обновлена';
    } elseif ($_POST['action'] === 'update_application') {
        $stmt = $pdo->prepare("UPDATE applications SET status=? WHERE id=?");
        $stmt->execute([$_POST['status'], $_POST['id']]);
        $success = 'Статус обновлен';
    }
}

// Fetch data
$pdo = new PDO(
    "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
    $config['db']['user'],
    $config['db']['pass']
);

$services = $pdo->query("SELECT * FROM services ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$applications = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Админ-панель</h1>
            <a href="logout.php" class="text-red-600 hover:underline">Выйти</a>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-6"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <!-- Applications -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Заявки</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Имя</th>
                            <th class="px-4 py-2 text-left">Контакты</th>
                            <th class="px-4 py-2 text-left">Сообщение</th>
                            <th class="px-4 py-2 text-left">Статус</th>
                            <th class="px-4 py-2 text-left">Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= $app['id'] ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($app['full_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($app['contact']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($app['message']) ?></td>
                            <td class="px-4 py-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="update_application">
                                    <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1">
                                        <option value="new" <?= $app['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                        <option value="processing" <?= $app['status'] === 'processing' ? 'selected' : '' ?>>В работе</option>
                                        <option value="done" <?= $app['status'] === 'done' ? 'selected' : '' ?>>Выполнена</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-2"><?= $app['created_at'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Services -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Услуги</h2>
            <?php foreach ($services as $service): ?>
            <div class="border-b py-4">
                <form method="POST">
                    <input type="hidden" name="action" value="update_service">
                    <input type="hidden" name="id" value="<?= $service['id'] ?>">
                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <input type="text" name="title" value="<?= htmlspecialchars($service['title_ru']) ?>" class="border rounded px-3 py-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="published" value="1" <?= $service['published'] ? 'checked' : '' ?> class="mr-2">
                            Опубликовано
                        </label>
                    </div>
                    <textarea name="description" rows="3" class="w-full border rounded px-3 py-2 mb-2"><?= htmlspecialchars($service['description_ru']) ?></textarea>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Сохранить</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>