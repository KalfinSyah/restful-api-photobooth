<?php    
require_once 'photobooth_database.php';
require_once 'custom_throw.php';
require_once 'get_image_mime_type.php';
class PhotoboothRepository extends PhotoboothDatabase {
    public static function addUser(string $username, string $password, string $re_enter_password): array {
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
    public static function updateUsername(string $oldUsername, string $newUsername): array {
        try {
            $conn = PhotoboothDatabase::connection();
            // Clean inputs
            $oldUsername = trim(stripslashes($oldUsername));
            $newUsername = trim(stripslashes($newUsername));
            // Check for empty values
            if (empty($newUsername)) {
                return ['status' => false, 'error' => 'username_empty', 'message' => 'Username cannot be empty.'];
            }
            if ($oldUsername === $newUsername) {
                return ['status' => false, 'error' => 'no_change', 'message' => 'New username is the same as the current one.'];
            }            
            // This is the corrected line:
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ?");
            if (!$stmt) {
                return ['status' => false, 'error' => 'database_error', 'message' => $conn->error];
            }
            $stmt->bind_param("ss", $newUsername, $oldUsername);
            if (!$stmt->execute()) {
                // Handle duplicate username error
                if ($conn->errno === 1062) {
                    return ['status' => false, 'error' => 'username_exists', 'message' => 'Username already exists.'];
                }
                return ['status' => false, 'error' => 'database_error', 'message' => $stmt->error];
            }
            $stmt->close();
            return ['status' => true, 'error' => null, 'message' => 'Successfully change username.'];
        } catch (Exception $e) {
            // Handle any exceptions from database connection
            return ['status' => false, 'error' => 'system_error', 'message' => $e->getMessage()];
        }
    }
    public static function addPhoto(string $username, $file) {
        try {
            $conn = parent::connection();
            // Process image upload
            $imageData = file_get_contents($file['tmp_name']);
            $mimeType = getImageMimeType($file['tmp_name']);
            $filename = basename($file['name']);
            $stmt = $conn->prepare("INSERT INTO photos (username, image_data, mime_type, filename) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $imageData, $mimeType, $filename);
            $stmt->execute();
            $stmt->close();
            return ['status' => true, 'error' => null, 'message' => 'Image Uploaded Successfully.'];
        } catch (Exception $e) {
            return ['status' => false, 'error' => 'system_error', 'message' => $e->getMessage()];
        }
    }
    public static function deletePhoto(int $id) {
        try {
            $conn = parent::connection();
            $stmt = $conn->prepare("DELETE FROM photos WHERE photo_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            // Check if any row was actually deleted
            $affectedRows = $conn->affected_rows;
            $stmt->close();
            if ($affectedRows === 0) {
                return ['status' => false, 'error' => 'not_found', 'message' => 'No photo found with that ID'];
            }
            return ['status' => true, 'error' => null, 'message' => 'Image Deleted Successfully.'];
        } catch (Exception $e) {
            // Generic error message for security
            return ['status' => false, 
                    'error' => 'system_error', 
                    'message' => $e->getMessage()];
        }
    }
    public static function getUsers(?string $username = null, ?string $password = null): array {
        if ($username) {
            $conn = parent::connection();
            if ($password) {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
                if (!$stmt) {
                    return ['status' => false, 'error' => 'database_error', 'message' => $conn->error];
                }
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    return ['status' => false, 'error' => 'username_not_found', 'message' => 'Username not found'];
                }
                $user = $result->fetch_assoc();
                if (!password_verify($password, $user['password_hash'])) {
                    return ['status' => false, 'error' => 'invalid_credential', 'message' => 'Wrong password.'];
                }
                $stmt->close();
                return ['status' => true, 'error' => null, 'message' => 'Success login.'];
            } else {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
                if (!$stmt) {
                    throw new RuntimeException("Error preparing statement: " . $conn->error);
                }   
                $stmt->bind_param("s", $username); 
                if (!$stmt->execute()) {
                    throw new RuntimeException("Error executing query: " . $stmt->error);
                }
                $result = $stmt->get_result();
                if (!$result) {
                    throw new RuntimeException("Error getting result: " . $stmt->error);
                }
            }         
        } else {
            $result = parent::connection()->query("SELECT * FROM users");
            CustomThrow::exceptionWithCondition(
                !$result,
                "Error executing query: " . parent::connection()->error
            );
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        if ($username) {
            return $data[0];
        }
        return $data;
    }
    public static function getApiKeys(?string $username = null): array {
        try {
            $conn = parent::connection();
            if ($username) {
                // Get API keys for specific user
                $stmt = $conn->prepare("SELECT * FROM users_api_keys WHERE username = ?");
                if (!$stmt) {
                    throw new RuntimeException("Error preparing statement: " . $conn->error);
                }   
                $stmt->bind_param("s", $username);          
                if (!$stmt->execute()) {
                    throw new RuntimeException("Error executing query: " . $stmt->error);
                }
                $result = $stmt->get_result();
            } else {
                // Get all API keys
                $result = $conn->query("SELECT * FROM users_api_keys");
                if (!$result) {
                    throw new RuntimeException("Error executing query: " . $conn->error);
                }
            }
            $apiKeys = [];
            while ($row = $result->fetch_assoc()) {
                $apiKeys[] = $row;
            }      
            return $apiKeys;            
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }
    public static function getPhotos(?string $username = null): array {
        try {
            $conn = PhotoboothDatabase::connection();
            // Query to get photos with Base64-encoded image data
            if ($username) {
                // Prepare statement to avoid SQL injection
                $stmt = $conn->prepare("SELECT photo_id, username, mime_type, filename, created_at, 
                                       TO_BASE64(image_data) as image_data_base64 
                                       FROM photos WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                CustomThrow::exceptionWithCondition(
                    !$result,
                    "Error retrieving photos: " . $conn->error
                );
            } else {
                // Get all photos if no username specified
                $query = "SELECT photo_id, username, mime_type, filename, created_at, 
                         TO_BASE64(image_data) as image_data_base64 FROM photos";
                $result = $conn->query($query);
                CustomThrow::exceptionWithCondition(
                    !$result,
                    "Error retrieving photos: " . $conn->error
                );
            }
            $photos = [];
            while ($row = $result->fetch_assoc()) {
                // Add data URL prefix to make the base64 data directly usable
                $row['image_data'] = 'data:' . $row['mime_type'] . ';base64,' . $row['image_data_base64'];
                unset($row['image_data_base64']); // Remove the temporary field
                $photos[] = $row;
            }
            if (isset($stmt)) {
                $stmt->close();
            } else {
                $result->free();
            }
            return $photos;
        } catch (Exception $e) {
            throw new Exception("Database error: " . $e->getMessage());
        }
    }
}