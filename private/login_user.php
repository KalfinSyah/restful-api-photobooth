<?php
require_once 'photobooth_database.php';
function loginUser(string $username, string $password): array {
    try {
        $conn = PhotoboothDatabase::connection();
        // Clean inputs
        $username = trim(stripslashes($username));
        // Check for empty values
        if (empty($username)) {
            return ['status' => false, 'error' => 'username_empty', 'message' => 'Username cannot be empty.'];
        }
        if (empty($password)) {
            return ['status' => false, 'error' => 'password_empty', 'message' => 'Password cannot be empty.'];
        }
        // Prepare and execute statement
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        if (!$stmt) {
            return ['status' => false, 'error' => 'database_error', 'message' => $conn->error];
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            return ['status' => false, 'error' => 'user_not_found', 'message' => 'User not found.'];
        }
        $user = $result->fetch_assoc();
        if (!password_verify($password, $user['password_hash'])) {
            return ['status' => false, 'error' => 'invalid_password', 'message' => 'Wrong username or password.'];
        }
        $stmt->close();
        return ['status' => true, 'error' => null, 'message' => 'Login successful.'];
    } catch (Exception $e) {
        // Handle any exceptions from database connection
        return ['status' => false, 'error' => 'system_error', 'message' => $e->getMessage()];
    }
}