-- Создание базы данных
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Категории
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title_ru VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    type ENUM('service', 'blog') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Услуги
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title_ru VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    description_ru TEXT,
    description_en TEXT,
    category_id INT,
    image_url VARCHAR(500),
    published BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Посты блога
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title_ru VARCHAR(255) NOT NULL,
    title_en VARCHAR(255) NOT NULL,
    content_ru LONGTEXT,
    content_en LONGTEXT,
    excerpt_ru TEXT,
    excerpt_en TEXT,
    category_id INT,
    image_url VARCHAR(500),
    published BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Заявки (ИСПРАВЛЕНО: добавлена колонка updated_at)
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    contact VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'processing', 'done') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Страница "Обо мне"
CREATE TABLE IF NOT EXISTS about_page (
    id INT PRIMARY KEY DEFAULT 1,
    bio_ru TEXT,
    bio_en TEXT,
    tech_stack TEXT,
    timeline JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_id CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- НАСТРОЙКИ САЙТА (НОВАЯ ТАБЛИЦА)
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY DEFAULT 1,
    email VARCHAR(255),
    phone VARCHAR(50),
    address VARCHAR(255),
    work_hours VARCHAR(100),
    vk_url VARCHAR(255),
    telegram_url VARCHAR(255),
    max_url VARCHAR(255),
    enable_email_notify BOOLEAN DEFAULT FALSE,
    enable_telegram_notify BOOLEAN DEFAULT FALSE,
    telegram_bot_token VARCHAR(255),
    telegram_chat_id VARCHAR(100),
    smtp_host VARCHAR(255),
    smtp_user VARCHAR(255),
    smtp_pass VARCHAR(255),
    smtp_from VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_settings_id CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Начальные данные для категорий
INSERT INTO categories (slug, title_ru, title_en, type) VALUES
('dev', 'Разработка', 'Development', 'service'),
('design', 'Дизайн', 'Design', 'service'),
('devops', 'DevOps', 'DevOps', 'service'),
('tech', 'Технологии', 'Technology', 'blog')
ON DUPLICATE KEY UPDATE slug=slug;

-- Начальные данные для услуг
INSERT INTO services (slug, title_ru, title_en, description_ru, description_en, category_id, published, image_url) VALUES
('web-dev', 'Веб-разработка', 'Web Development', 'Создание современных сайтов и веб-приложений', 'Building modern websites and web apps', 1, 1, 'https://placehold.co/600x400/2563EB/FFFFFF?text=Web+Dev'),
('video-editing', 'Видеомонтаж', 'Video Editing', 'Профессиональный монтаж видео', 'Professional video editing', 2, 1, 'https://placehold.co/600x400/F97316/FFFFFF?text=Video'),
('devops-setup', 'Настройка серверов', 'Server Setup', 'DevOps и системное администрирование', 'DevOps and system administration', 3, 1, 'https://placehold.co/600x400/10B981/FFFFFF?text=DevOps')
ON DUPLICATE KEY UPDATE slug=slug;

-- Начальные данные для страницы "Обо мне"
INSERT INTO about_page (id, bio_ru, bio_en, tech_stack, timeline) VALUES (1, 
'Привет! Я IT-специалист с многолетним опытом в разработке программного обеспечения, дизайне и системном администрировании.',
'Hello! I am an IT specialist with many years of experience in software development, design and system administration.',
'JavaScript, TypeScript, React, Next.js, Node.js, PHP, MySQL, PostgreSQL, Docker, Git, Tailwind CSS, Figma, Adobe XD, Linux, Nginx',
'[{"year":"2020-н.в.","title":"Senior Fullstack Developer","company":"Tech Company"},{"year":"2017-2020","title":"Middle Developer","company":"Web Studio"},{"year":"2015-2017","title":"Junior Developer","company":"StartUp"}]'
) ON DUPLICATE KEY UPDATE id=id;

-- Начальные данные для настроек сайта
INSERT INTO site_settings (id, email, phone, address, work_hours, vk_url, telegram_url, max_url, enable_email_notify, enable_telegram_notify) VALUES (1,
'contact@techportfolio.ru',
'+7 (999) 123-45-67',
'Москва, Россия',
'Пн-Пт: 9:00 - 18:00',
'https://vk.com/techportfolio',
'https://t.me/techportfolio',
'https://max.ru/techportfolio',
FALSE,
FALSE
) ON DUPLICATE KEY UPDATE id=id;