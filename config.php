<?php
return [
    'db' => [
        'host' => 'localhost',
        'name' => 'portfolio_db',
        'user' => 'portfolio_user',
        'pass' => 'your_password_here'
    ],
    'email' => [
        'enabled' => true,
        'admin' => 'admin@yourdomain.ru',
        'from' => 'noreply@yourdomain.ru'
    ],
    'telegram' => [
        'enabled' => false,
        'token' => 'your_bot_token',
        'chat_id' => 'your_chat_id'
    ],
    'hcaptcha' => [
        'secret' => 'your_hcaptcha_secret',
    ],
];