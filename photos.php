<?php
require_once 'private/photobooth_repository.php';
require_once 'private/api_key_validator.php';
require_once 'private/custom_throw.php';
header('Content-Type: application/json');
try {
    // API Key validation
    $headers = getallheaders();
    // Get API key from either header or GET parameter
    $apiKey = $_GET['apikey'] ?? $headers['apikey'] ?? null;
    if (!$apiKey) {
        CustomThrow::exception('API key is required');
    }
    // Validate the API key using the validator class
    if (!apiKeyValidator($apiKey)) {
        CustomThrow::exception('Invalid API key');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (isset($_GET['username'])) {
            $jsonResponse = \json_encode(PhotoboothRepository::getPhotos($_GET['username']));
            echo $jsonResponse;
        } elseif (empty($_GET) || isset($_GET['apikey'])) {
            $jsonResponse = \json_encode(PhotoboothRepository::getPhotos());
            echo $jsonResponse;
        } else {
            CustomThrow::exception('Invalid parameter');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $file = $_FILES['photo'];
        $jsonResponse = \json_encode(PhotoboothRepository::addPhoto($username, $file));
        echo $jsonResponse;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        if (isset($_GET['photo_id'])) {
            $jsonResponse = \json_encode(PhotoboothRepository::deletePhoto($_GET['photo_id']));
            echo $jsonResponse;
        } else {
            CustomThrow::exception('Invalid parameter');
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
    exit;
}