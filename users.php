<?php
require_once __DIR__ . '/private/photobooth_repository.php';
require_once __DIR__ . '/private/api_key_validator.php';
require_once __DIR__ . '/private/custom_throw.php';
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
            $jsonResponse = \json_encode(PhotoboothRepository::getUsers($_GET['username']));
            echo $jsonResponse;
        } elseif (empty($_GET) || isset($_GET['apikey'])) {
            $jsonResponse = \json_encode(PhotoboothRepository::getUsers());
            echo $jsonResponse;
        } else {
            CustomThrow::exception('Invalid parameter');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['re_enter_password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $reEnterPassword = $_POST['re_enter_password'];
        if (isset($username) && isset($password)) {
            $jsonResponse = \json_encode(PhotoboothRepository::addUser($username, $password, $reEnterPassword));
            echo $jsonResponse;
        } else {
            CustomThrow::exception('Invalid parameter');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $reEnterPassword = $_POST['re_enter_password'];
        if (isset($username) && isset($password)) {
            $jsonResponse = \json_encode(PhotoboothRepository::getUsers($username, $password));
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

