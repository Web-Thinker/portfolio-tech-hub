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
$error = '';

// Сохранение настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("
            UPDATE site_settings SET 
                email=?, phone=?, address=?, work_hours=?,
                vk_url=?, telegram_url=?, max_url=?,
                enable_email_notify=?, enable_telegram_notify=?,
                telegram_bot_token=?, telegram_chat_id=?,
                smtp_host=?, smtp_user=?, smtp_pass=?, smtp_from=?
            WHERE id=1
        ");
        
        $stmt->execute([
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['work_hours'],
            $_POST['vk_url'],
            $_POST['telegram_url'],
            $_POST['max_url'],
            isset($_POST['enable_email_notify']) ? 1 : 0,
            isset($_POST['enable_telegram_notify']) ? 1 : 0,
            $_POST['telegram_bot_token'],
            $_POST['telegram_chat_id'],
            $_POST['smtp_host'],
            $_POST['smtp_user'],
            $_POST['smtp_pass'],
            $_POST['smtp_from']
        ]);
        
        $success = 'Настройки успешно сохранены';
    } catch (PDOException $e) {
        $error = 'Ошибка при сохранении: ' . $e->getMessage();
    }
}

// Получение текущих настроек
$stmt = $pdo->query("SELECT * FROM site_settings WHERE id=1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Если настроек нет, создаём пустые
if (!$settings) {
    $settings = [
        'email' => '',
        'phone' => '',
        'address' => '',
        'work_hours' => '',
        'vk_url' => '',
        'telegram_url' => '',
        'max_url' => '',
        'enable_email_notify' => 0,
        'enable_telegram_notify' => 0,
        'telegram_bot_token' => '',
        'telegram_chat_id' => '',
        'smtp_host' => '',
        'smtp_user' => '',
        'smtp_pass' => '',
        'smtp_from' => ''
    ];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Настройки сайта - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-neutral-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Шапка -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-neutral-800">Настройки сайта</h1>
                    <p class="text-neutral-600 mt-1">Контакты, социальные сети и уведомления</p>
                </div>
                <div class="flex gap-3">
                    <a href="index.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                        ← На главную
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

        <form method="POST" class="space-y-6">
            <!-- Контактная информация -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4 text-neutral-800">Контактная информация</h2>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($settings['email']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Телефон</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($settings['phone']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Адрес</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($settings['address']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Режим работы</label>
                        <input type="text" name="work_hours" value="<?= htmlspecialchars($settings['work_hours']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Социальные сети -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4 text-neutral-800">Социальные сети</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">ВКонтакте</label>
                        <input type="url" name="vk_url" value="<?= htmlspecialchars($settings['vk_url']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://vk.com/username">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Telegram</label>
                        <input type="url" name="telegram_url" value="<?= htmlspecialchars($settings['telegram_url']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://t.me/username">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Max</label>
                        <input type="url" name="max_url" value="<?= htmlspecialchars($settings['max_url']) ?>" 
                               class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="https://max.ru/username">
                    </div>
                </div>
            </div>

            <!-- Уведомления -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4 text-neutral-800">Уведомления о новых заявках</h2>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-lg">
                        <div>
                            <h3 class="font-semibold text-neutral-800">Email уведомления</h3>
                            <p class="text-sm text-neutral-600">Отправлять уведомления на email при новой заявке</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enable_email_notify" value="1" <?= $settings['enable_email_notify'] ? 'checked' : '' ?> 
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-lg">
                        <div>
                            <h3 class="font-semibold text-neutral-800">Telegram уведомления</h3>
                            <p class="text-sm text-neutral-600">Отправлять уведомления в Telegram при новой заявке</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="enable_telegram_notify" value="1" <?= $settings['enable_telegram_notify'] ? 'checked' : '' ?> 
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>
                
                <!-- Настройки Telegram бота -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-3">Настройки Telegram бота</h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-blue-900 mb-1">Token бота</label>
                            <input type="text" name="telegram_bot_token" value="<?= htmlspecialchars($settings['telegram_bot_token']) ?>" 
                                   class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-blue-900 mb-1">Chat ID</label>
                            <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($settings['telegram_chat_id']) ?>" 
                                   class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="123456789">
                        </div>
                    </div>
                    
                    <p class="text-xs text-blue-700 mt-2">
                        💡 Как получить: создайте бота через @BotFather, получите токен. 
                        Chat ID можно узнать через @userinfobot
                    </p>
                </div>
                
                <!-- SMTP настройки -->
                <div class="mt-6 p-4 bg-green-50 rounded-lg">
                    <h3 class="font-semibold text-green-900 mb-3">SMTP настройки (для отправки email)</h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-green-900 mb-1">SMTP хост</label>
                            <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host']) ?>" 
                                   class="w-full px-3 py-2 border border-green-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="smtp.gmail.com">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-green-900 mb-1">SMTP пользователь</label>
                            <input type="text" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user']) ?>" 
                                   class="w-full px-3 py-2 border border-green-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-green-900 mb-1">SMTP пароль</label>
                            <input type="password" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass']) ?>" 
                                   class="w-full px-3 py-2 border border-green-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-green-900 mb-1">От кого (Email)</label>
                            <input type="email" name="smtp_from" value="<?= htmlspecialchars($settings['smtp_from']) ?>" 
                                   class="w-full px-3 py-2 border border-green-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Сохранить настройки
                </button>
            </div>
        </form>
    </div>
</body>
</html>