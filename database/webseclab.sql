-- WebSecLab database
-- Import lewat phpMyAdmin (tab Import) atau: mysql -u root < webseclab.sql

CREATE DATABASE IF NOT EXISTS webseclab CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE webseclab;

DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS guestbook;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    user_id  INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar   VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE guestbook (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    comment    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel tambahan untuk level Impossible pada modul Brute Force (pembatasan percobaan login).
CREATE TABLE login_attempts (
    username     VARCHAR(50) PRIMARY KEY,
    attempts     INT NOT NULL DEFAULT 0,
    last_attempt DATETIME NOT NULL
) ENGINE=InnoDB;

-- Password disimpan sebagai MD5 (sengaja, agar hasil SQLi bisa dianalisis).
INSERT INTO users (username, password, avatar) VALUES
('admin',    '5f4dcc3b5aa765d61d8327deb882cf99', 'default.png'),  -- password
('gordonb',  'e99a18c428cb38d5f260853678922e03', 'default.png'),  -- abc123
('1337',     '8d3533d75ae2c3966d7e0d4fcc69216b', 'default.png'),  -- charley
('pablo',    '0d107d09f5bbe40cade3de5c71e9e9b7', 'default.png'),  -- letmein
('smithy',   '5f4dcc3b5aa765d61d8327deb882cf99', 'default.png');  -- password

INSERT INTO guestbook (name, comment) VALUES
('Admin', 'Selamat datang di buku tamu WebSecLab!');
