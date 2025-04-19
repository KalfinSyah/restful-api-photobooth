<?php 
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once __DIR__ . '/private/generate_api_key.php';
require_once __DIR__ . '/private/photobooth_repository.php';
$generatedApiKey = generateApiKey(isset($_POST['submit']), $_SESSION['username']);
$apiKeys = PhotoboothRepository::getApiKeys($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create API Key</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center mt-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">API Key Management</h2>
                        
                        <?php if (isset($generatedApiKey['key']) && $generatedApiKey['error'] === null): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>New API Key:</strong> <?php echo htmlspecialchars($generatedApiKey['key']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php elseif ($generatedApiKey['error'] !== null && $generatedApiKey['error'] !== 'form_not_submitted'): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($generatedApiKey['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="mb-4">
                            <div class="d-grid">
                                <button type="submit" name="submit" class="btn btn-primary btn-lg">
                                    Generate New API Key
                                </button>
                            </div>
                        </form>
                        <?php if (!empty($apiKeys)): ?>
                            <div class="mt-4">
                                <h3 class="h5 mb-3">Your Active API Keys:</h3>
                                <div class="list-group">
                                    <?php foreach ($apiKeys as $keyRecord): ?>
                                        <div class="list-group-item font-monospace">
                                            <?php echo htmlspecialchars($keyRecord['api_key']); ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-center mt-5 mb-5">
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>