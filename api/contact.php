<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Валидация
$errors = [];
if (!isset($input['fullName']) || strlen($input['fullName']) < 2 || strlen($input['fullName']) > 50) {
    $errors['fullName'] = 'Имя должно содержать от 2 до 50 символов';
}
if (!isset($input['contact']) || (!filter_var($input['contact'], FILTER_VALIDATE_EMAIL) && !preg_match('/^\+?[1-9]\d{1,14}$/', $input['contact']))) {
    $errors['contact'] = 'Некорректный email или телефон';
}
if (!isset($input['message']) || strlen($input['message']) < 10 || strlen($input['message']) > 1000) {
    $errors['message'] = 'Сообщение должно содержать от 10 до 1000 символов';
}

// Проверка hCaptcha
if (!isset($input['h-captcha-response']) || empty($input['h-captcha-response'])) {
    $errors['captcha'] = 'Пройдите проверку hCaptcha';
} else {
    $config = require __DIR__ . '/../config.php';
    if (!empty($config['hcaptcha']['secret'])) {
        $verifyUrl = 'https://hcaptcha.com/siteverify';
        $verifyData = http_build_query([
            'secret' => $config['hcaptcha']['secret'],
            'response' => $input['h-captcha-response'],
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]);
        
        $verifyResponse = @file_get_contents($verifyUrl . '?' . $verifyData);
        $verifyResult = json_decode($verifyResponse, true);
        
        if (!$verifyResult || !$verifyResult['success']) {
            $errors['captcha'] = 'Ошибка проверки hCaptcha';
        }
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}

// Подключение к БД
$config = require __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Сохранение заявки
    $stmt = $pdo->prepare("INSERT INTO applications (full_name, contact, message, status, created_at) VALUES (?, ?, ?, 'new', NOW())");
    $stmt->execute([$input['fullName'], $input['contact'], $input['message']]);
    $applicationId = $pdo->lastInsertId();
    
    // Получение настроек сайта
    $settingsStmt = $pdo->query("SELECT * FROM site_settings WHERE id=1");
    $settings = $settingsStmt->fetch(PDO::FETCH_ASSOC);
    
    // Отправка уведомлений
    $notificationSent = false;
    
    // Email уведомление
    if ($settings && $settings['enable_email_notify'] && !empty($settings['email'])) {
        $subject = "📩 Новая заявка #{$applicationId}";
        $body = "Поступила новая заявка:\n\n";
        $body .= "Имя: {$input['fullName']}\n";
        $body .= "Контакты: {$input['contact']}\n";
        $body .= "Сообщение:\n{$input['message']}\n\n";
        $body .= "ID заявки: {$applicationId}";
        
        $headers = "From: " . ($settings['smtp_from'] ?? 'noreply@yourdomain.ru') . "\r\n";
        $headers .= "Reply-To: {$input['contact']}\r\n";
        
        if (!empty($settings['smtp_host']) && !empty($settings['smtp_user'])) {
            // Отправка через SMTP (требуется PHPMailer или подобная библиотека)
            // Для простоты используем mail()
            @mail($settings['email'], $subject, $body, $headers);
        } else {
            @mail($settings['email'], $subject, $body, $headers);
        }
        
        $notificationSent = true;
    }
    
    // Telegram уведомление
    if ($settings && $settings['enable_telegram_notify'] && !empty($settings['telegram_bot_token']) && !empty($settings['telegram_chat_id'])) {
        $text = "📩 *Новая заявка #{$applicationId}*\n\n";
        $text .= "👤 *Имя:* {$input['fullName']}\n";
        $text .= "📞 *Контакты:* {$input['contact']}\n";
        $text .= "💬 *Сообщение:*\n{$input['message']}";
        
        $url = "https://api.telegram.org/bot{$settings['telegram_bot_token']}/sendMessage";
        $post = http_build_query([
            'chat_id' => $settings['telegram_chat_id'],
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
        
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $post,
                'timeout' => 10
            ]
        ];
        $context = stream_context_create($opts);
        @file_get_contents($url, false, $context);
        
        $notificationSent = true;
    }
    
    echo json_encode(['success' => true, 'notificationSent' => $notificationSent]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}