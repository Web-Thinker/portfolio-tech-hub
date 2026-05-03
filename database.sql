-- Create database
CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Categories table
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

-- Services table
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

-- Posts table
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

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    contact VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'processing', 'done') DEFAULT 'new',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data
INSERT INTO categories (slug, title_ru, title_en, type) VALUES
('dev', 'Разработка', 'Development', 'service'),
('design', 'Дизайн', 'Design', 'service'),
('devops', 'DevOps', 'DevOps', 'service'),
('tech', 'Технологии', 'Technology', 'blog');

INSERT INTO services (slug, title_ru, title_en, description_ru, description_en, category_id, published, image_url) VALUES
('web-dev', 'Веб-разработка', 'Web Development', 'Создание современных сайтов и веб-приложений', 'Building modern websites and web apps', 1, 1, 'https://placehold.co/600x400/2563EB/FFFFFF?text=Web+Dev'),
('video-editing', 'Видеомонтаж', 'Video Editing', 'Профессиональный монтаж видео', 'Professional video editing', 2, 1, 'https://placehold.co/600x400/F97316/FFFFFF?text=Video'),
('devops-setup', 'Настройка серверов', 'Server Setup', 'DevOps и системное администрирование', 'DevOps and system administration', 3, 1, 'https://placehold.co/600x400/10B981/FFFFFF?text=DevOps');

INSERT INTO posts (slug, title_ru, title_en, excerpt_ru, excerpt_en, content_ru, content_en, category_id, published, image_url) VALUES
('first-post', 'Первая статья', 'First Post', 'Введение в блог', 'Blog introduction', 'Содержание статьи...', 'Article content...', 4, 1, 'https://placehold.co/600x400/6366F1/FFFFFF?text=Blog');