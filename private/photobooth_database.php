<?php
require_once __DIR__ . '/custom_throw.php';
class PhotoboothDatabase {
    private static ?\mysqli $conn = null;
    public static function connection(): \mysqli {
        if (self::$conn === null) {
            self::$conn = new \mysqli("localhost", "root", "");
            CustomThrow::exceptionWithCondition(
                self::$conn->connect_error,
                "Connection failed: " . self::$conn->connect_error
            );
            // create database if not exist
            self::$conn->query("CREATE DATABASE IF NOT EXISTS web_photobooth");
            self::$conn->select_db("web_photobooth");
            // create all tables if not exist
            $queries = [
                "CREATE TABLE IF NOT EXISTS users (
                    user_id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )",
                "CREATE TABLE IF NOT EXISTS users_api_keys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    -- user_id INT NOT NULL,
                    username VARCHAR(50) NOT NULL,
                    api_key VARCHAR(64) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_used TIMESTAMP NULL,
                    expires_at TIMESTAMP NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    -- FOREIGN KEY (user_id) REFERENCES users(user_id),
                    FOREIGN KEY (username) REFERENCES users(username)
                )",
                "CREATE TABLE IF NOT EXISTS photos (
                    photo_id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) NOT NULL,
                    image_data LONGBLOB NOT NULL,
                    mime_type VARCHAR(50) NOT NULL,
                    filename VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (username) REFERENCES users(username),
                    INDEX idx_username (username)
                )"
            ];
            foreach ($queries as $query) {
                CustomThrow::exceptionWithCondition(
                    !self::$conn->query($query),
                    "Error creating table: " . self::$conn->error
                );
            }
        }
        return self::$conn;
    }
}