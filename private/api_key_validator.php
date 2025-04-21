<?php
require_once 'photobooth_database.php';
// Validate an API key
function apiKeyValidator(string $apiKey): bool {
    $conn = PhotoboothDatabase::connection();
    $stmt = $conn->prepare("SELECT username, expires_at, is_active FROM users_api_keys WHERE api_key = ?");
    $stmt->bind_param("s", $apiKey);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        // Check if the key is active and not expired
        $isActive = $row['is_active'] == 1;
        $isExpired = strtotime($row['expires_at']) < time();
        if ($isActive && !$isExpired) {
            // Update last_used timestamp
            $updateStmt = $conn->prepare("UPDATE users_api_keys SET last_used = CURRENT_TIMESTAMP WHERE api_key = ?");
            $updateStmt->bind_param("s", $apiKey);
            $updateStmt->execute();
            $updateStmt->close();
            $stmt->close();
            return true;
        }
    }
    $stmt->close();
    return false;
}