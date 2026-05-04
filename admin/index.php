<?php
session_start();
$config = require __DIR__ . '/../config.php';

// Простая авторизация
if (isset($_POST['login']) && isset($_POST['password'])) {
    if ($_POST['login'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}

if (!isset($_SESSION['admin'])) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Вход в админку</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 flex items-center justify-center min-h-screen">
        <form method="POST" class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Вход в админку</h1>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Логин</label>
                <input type="text" name="login" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="admin">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Пароль</label>
                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500" placeholder="admin123">
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition">
                Войти
            </button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

$pdo = new PDO(
    "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
    $config['db']['user'],
    $config['db']['pass']
);

$success = '';
$error = '';

// Обработка смены статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $id = intval($_POST['id']);
        $status = $_POST['status'];
        // ИСПРАВЛЕНО: обновляем только status, updated_at обновится автоматически
        $stmt = $pdo->prepare("UPDATE applications SET status=? WHERE id=?");
        $stmt->execute([$status, $id]);
        $success = 'Статус обновлён';
    }
}

// Получение заявок
$applications = [
    'new' => [],
    'processing' => [],
    'done' => []
];

$stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
while ($app = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $applications[$app['status']][] = $app;
}

$statusColors = [
    'new' => 'bg-red-100 border-red-500 text-red-800',
    'processing' => 'bg-orange-100 border-orange-500 text-orange-800',
    'done' => 'bg-green-100 border-green-500 text-green-800'
];

$statusLabels = [
    'new' => 'Новая',
    'processing' => 'В работе',
    'done' => 'Выполнена'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - TechPortfolio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Шапка -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-800">Админ-панель</h1>
                    <p class="text-neutral-600 mt-1">Управление контентом и заявками</p>
                </div>
                <div class="flex gap-3">
                    <a href="blog.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-2-1H5a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-1m-2-1H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2z"/>
                        </svg>
                        Блог
                    </a>
                    <a href="about.php" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Обо мне
                    </a>
                    <a href="settings.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Настройки
                    </a>
                    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">
                        Выйти
                    </a>
                </div>
            </div>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Канбан-доска заявок -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-neutral-800 mb-6">Заявки (Планировщик задач)</h2>
            
            <div class="grid md:grid-cols-3 gap-6">
                <?php foreach ($applications as $status => $apps): ?>
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-4 border-b-4 <?= $statusColors[$status] ?>">
                        <h3 class="font-bold text-lg"><?= $statusLabels[$status] ?></h3>
                        <span class="text-sm opacity-75"><?= count($apps) ?> заявок</span>
                    </div>
                    <div class="p-4 space-y-3">
                        <?php if (empty($apps)): ?>
                            <p class="text-neutral-400 text-center py-4">Нет заявок</p>
                        <?php else: ?>
                            <?php foreach ($apps as $app): ?>
                            <div class="border-l-4 <?= $statusColors[$status] ?> bg-neutral-50 p-4 rounded-r-lg">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="text-xs text-neutral-500">#<?= $app['id'] ?></span>
                                    <span class="text-xs text-neutral-500"><?= date('d.m.Y', strtotime($app['created_at'])) ?></span>
                                </div>
                                <h4 class="font-semibold text-neutral-800 mb-1"><?= htmlspecialchars($app['full_name']) ?></h4>
                                <p class="text-sm text-neutral-600 mb-2"><?= htmlspecialchars($app['contact']) ?></p>
                                <p class="text-sm text-neutral-700 mb-3"><?= htmlspecialchars(mb_substr($app['message'], 0, 100)) ?><?= mb_strlen($app['message']) > 100 ? '...' : '' ?></p>
                                
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="id" value="<?= $app['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="flex-1 text-sm border border-neutral-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="new" <?= $app['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                        <option value="processing" <?= $app['status'] === 'processing' ? 'selected' : '' ?>>В работе</option>
                                        <option value="done" <?= $app['status'] === 'done' ? 'selected' : '' ?>>Выполнена</option>
                                    </select>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Статистика -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs uppercase font-bold">Всего</p>
                    <p class="text-2xl font-bold text-neutral-800"><?= array_sum(array_map('count', $applications)) ?></p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs uppercase font-bold">Новые</p>
                    <p class="text-2xl font-bold text-red-600"><?= count($applications['new']) ?></p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs uppercase font-bold">В работе</p>
                    <p class="text-2xl font-bold text-orange-600"><?= count($applications['processing']) ?></p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 flex items-center justify-between">
                <div>
                    <p class="text-neutral-600 text-xs uppercase font-bold">Готово</p>
                    <p class="text-2xl font-bold text-green-600"><?= count($applications['done']) ?></p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</body>
</html>