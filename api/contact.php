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

// Validation
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

// Verify hCaptcha
if (!isset($input['h-captcha-response']) || empty($input['h-captcha-response'])) {
    $errors['captcha'] = 'Пройдите проверку hCaptcha';
} else {
    $verifyUrl = 'https://hcaptcha.com/siteverify';
    $verifyData = http_build_query([
        'secret' => 'YOUR_HCAPTCHA_SECRET', // Замените на ваш секретный ключ
        'response' => $input['h-captcha-response'],
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    $verifyResponse = @file_get_contents($verifyUrl . '?' . $verifyData);
    $verifyResult = json_decode($verifyResponse, true);
    
    if (!$verifyResult || !$verifyResult['success']) {
        $errors['captcha'] = 'Ошибка проверки hCaptcha';
    }
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['errors' => $errors]);
    exit;
}

// Load config
$config = require __DIR__ . '/../config.php';

// Rate limiting
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$rateFile = sys_get_temp_dir() . '/rate_' . md5($ip);
$now = time();

if (file_exists($rateFile)) {
    $data = json_decode(file_get_contents($rateFile), true);
    if ($now < $data['reset'] && $data['count'] >= 5) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests']);
        exit;
    }
    $data['count']++;
} else {
    $data = ['count' => 1, 'reset' => $now + 600];
}
file_put_contents($rateFile, json_encode($data));

// Save to database
try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->prepare("INSERT INTO applications (full_name, contact, message, status, created_at) VALUES (?, ?, ?, 'new', NOW())");
    $stmt->execute([$input['fullName'], $input['contact'], $input['message']]);
    
    // Send email
    if ($config['email']['enabled']) {
        $to = $config['email']['admin'];
        $subject = "Новая заявка от {$input['fullName']}";
        $body = "Имя: {$input['fullName']}\nКонтакты: {$input['contact']}\n\nСообщение:\n{$input['message']}";
        $headers = "From: {$config['email']['from']}\r\nReply-To: {$input['contact']}\r\n";
        
        mail($to, $subject, $body, $headers);
    }
    
    // Send Telegram
    if ($config['telegram']['enabled']) {
        $text = "📩 Заявка:\n👤 {$input['fullName']}\n📞 {$input['contact']}\n💬 {$input['message']}";
        $url = "https://api.telegram.org/bot{$config['telegram']['token']}/sendMessage";
        $post = http_build_query(['chat_id' => $config['telegram']['chat_id'], 'text' => $text]);
        
        $opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $post]];
        $context = stream_context_create($opts);
        @file_get_contents($url, false, $context);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}