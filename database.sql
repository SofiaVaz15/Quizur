-- ============================================================
-- Quizur - Sistema de Quizzes Interativos
-- Desenvolvido para o 1° Química Manhã
-- Script de Criação e Dados Iniciais para MySQL / MariaDB (PDO)
-- ============================================================

CREATE DATABASE IF NOT EXISTS `quizur` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quizur`;

-- 1. Tabela de Usuários (Senhas com bcrypt)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    `avatar` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabela de Categorias
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'fa-flask',
    `color` VARCHAR(50) DEFAULT 'purple',
    `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabela de Quizzes
CREATE TABLE IF NOT EXISTS `quizzes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL,
    `description` TEXT NULL,
    `difficulty` VARCHAR(20) DEFAULT 'Médio',
    `points_per_question` INT DEFAULT 10,
    `image_url` VARCHAR(500) NULL,
    `is_published` TINYINT(1) DEFAULT 1,
    `views_count` INT DEFAULT 0,
    `plays_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabela de Perguntas
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `explanation` TEXT NULL,
    `image_url` VARCHAR(500) NULL,
    `order_num` INT DEFAULT 1,
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabela de Respostas / Alternativas
CREATE TABLE IF NOT EXISTS `answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_id` INT NOT NULL,
    `answer_text` TEXT NOT NULL,
    `is_correct` TINYINT(1) DEFAULT 0,
    `order_num` INT DEFAULT 1,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabela de Tentativas do Quiz (Pontuação e Resultados)
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` INT NOT NULL,
    `user_id` INT NULL,
    `guest_name` VARCHAR(100) NULL,
    `total_questions` INT NOT NULL,
    `correct_answers` INT NOT NULL,
    `wrong_answers` INT NOT NULL,
    `score` INT NOT NULL,
    `max_score` INT NOT NULL,
    `percentage` DECIMAL(5,2) NOT NULL,
    `time_spent_seconds` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabela de Detalhes da Tentativa (Respostas Marcadas)
CREATE TABLE IF NOT EXISTS `quiz_attempt_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `selected_answer_id` INT NULL,
    `is_correct` TINYINT(1) NOT NULL,
    FOREIGN KEY (`attempt_id`) REFERENCES `quiz_attempts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabela de Auditoria e Logs (Exclusivo para Visualização do Administrador)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `user_name` VARCHAR(100) NULL,
    `action` VARCHAR(50) NOT NULL,
    `target_type` VARCHAR(50) NULL,
    `target_id` INT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserção de Usuários Padrão (Senhas com bcrypt: admin123, quimica123, aluno123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Administrador Quizur', 'admin@quizur.com', '$2y$10$w0u36oNlQeWf543FwN0oqe2E/Y8qOa2wPqFkZ4Nvy4rT2qXq8hI9C', 'admin'),
(2, 'Aluno Químico 1° Manhã', 'quimica@quizur.com', '$2y$10$m6d8f8kK3l4N8h0f/A1q0uNlQeWf543FwN0oqe2E/Y8qOa2wPqFkZ', 'user'),
(3, 'Sofia Vaz', 'sofia@quizur.com', '$2y$10$a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a', 'user')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Inserção de Categorias
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `color`, `description`) VALUES
(1, 'Química & Ciências', 'quimica-ciencias', 'fa-flask', 'purple', 'Tabela periódica, reações, fórmulas e o fascinante mundo químico!'),
(2, 'Conhecimentos Gerais', 'conhecimentos-gerais', 'fa-brain', 'blue', 'Desafie sua mente com fatos mundiais e saberes gerais.'),
(3, 'Cultura Pop & Cinema', 'cultura-pop', 'fa-film', 'pink', 'Séries marcantes, filmes épicos, música e entretenimento.'),
(4, 'Geografia & Geopolítica', 'geografia', 'fa-earth-americas', 'blue', 'Países, capitais, relevo e relações internacionais.'),
(5, 'Tecnologia & Games', 'tecnologia-games', 'fa-gamepad', 'purple', 'O universo dos jogos, inteligência artificial e informática.')
ON DUPLICATE KEY UPDATE `id`=`id`;
