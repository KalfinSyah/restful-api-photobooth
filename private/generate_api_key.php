<?php
require_once __DIR__ . '/photobooth_database.php';
function generateApiKey(bool $btn, string $username): array {
    // Check if form is submitted
    if (!$btn) {
        return ['status' => false, 'error' => 'form_not_submitted', 'key' => null];
    }
    // Generate a secure random string
    $apiKey = bin2hex(random_bytes(32)); // 64 characters
    $conn = PhotoboothDatabase::connection();
    // Set expiration date
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 year'));
    // Insert the new API key
    $stmt = $conn->prepare("INSERT INTO users_api_keys (username, api_key, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $apiKey, $expiresAt);
    if ($stmt->execute()) {
        $stmt->close();
        return ['status' => true, 'error' => null, 'key' => $apiKey];
    } else {
        $stmt->close();
        return ['status' => false, 'error' => 'failed_generate_key', 'key' => null];
    }
}