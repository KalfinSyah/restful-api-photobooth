<?php
// for read the .env
require_once __DIR__ . '/../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
require_once 'custom_throw.php';
class PhotoboothDatabase {
    private static ?\mysqli $conn = null;
    public static function connection(): \mysqli {
        if (self::$conn === null) {
            self::$conn = new \mysqli($_ENV['MYSQLHOST'], $_ENV['MYSQLUSER'], $_ENV['MYSQLPASSWORD']);
            CustomThrow::exceptionWithCondition(
                self::$conn->connect_error,
                "Connection failed: " . self::$conn->connect_error
            );
            // create database if not exist
            self::$conn->query("CREATE DATABASE IF NOT EXISTS " . $_ENV['MYSQLDATABASE']);
            self::$conn->select_db($_ENV['MYSQLDATABASE']);
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
                    username VARCHAR(50) NOT NULL,
                    api_key VARCHAR(64) NOT NULL UNIQUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_used TIMESTAMP NULL,
                    expires_at TIMESTAMP NULL,
                    is_active TINYINT(1) DEFAULT 1,
                    FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE
                )",
                "CREATE TABLE IF NOT EXISTS photos (
                    photo_id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) NOT NULL,
                    image_data LONGBLOB NOT NULL,
                    mime_type VARCHAR(50) NOT NULL,
                    filename VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (username) REFERENCES users(username) ON UPDATE CASCADE,
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