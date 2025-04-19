<?php
require_once __DIR__ . '/photobooth_database.php';
function registerUser(string $username, string $password, string $re_enter_password): array {
    try {
        $conn = PhotoboothDatabase::connection();
        // Clean inputs
        $username = trim(stripslashes($username));
        // Validate password match
        if ($password !== $re_enter_password) {
            return ['status' => false, 'error' => 'password_mismatch', 'message' => 'Passwords do not match.'];
        }
        // Check for empty values
        if (empty($username)) {
            return ['status' => false, 'error' => 'username_empty', 'message' => 'Username cannot be empty.'];
        }
        if (empty($password)) {
            return ['status' => false, 'error' => 'password_empty', 'message' => 'Password cannot be empty.'];
        }
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        // Prepare and execute statement
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
        if (!$stmt) {
            return ['status' => false, 'error' => 'database_error', 'message' => $conn->error];
        }
        $stmt->bind_param("ss", $username, $password_hash);
        if (!$stmt->execute()) {
            // Handle duplicate username error
            if ($conn->errno === 1062) {
                return ['status' => false, 'error' => 'username_exists', 'message' => 'Username already exists.'];
            }
            return ['status' => false, 'error' => 'database_error', 'message' => $stmt->error];
        }
        $stmt->close();
        return ['status' => true, 'error' => null, 'message' => 'User registered successfully.'];
    } catch (Exception $e) {
        // Handle any exceptions from database connection
        return ['status' => false, 'error' => 'system_error', 'message' => $e->getMessage()];
    }
}