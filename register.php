<?php
session_start();
require_once 'private/register_user.php';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $result = registerUser(
        $_POST['username'],
        $_POST['password'],
        $_POST['re_enter_password']
    );
    if ($result['status']) {
        $_SESSION['username'] = $_POST['username'];
        header("Location: index.php");
        exit();
    } else {
        $errorMessage = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-4">Register</h2>
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $errorMessage; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>                         
                            <div class="mb-4">
                                <label for="re_enter_password" class="form-label">Re-enter Password</label>
                                <input type="password" class="form-control" id="re_enter_password" name="re_enter_password" required>
                            </div>                           
                            <div class="d-grid mb-3">
                                <button type="submit" name="submit" class="btn btn-primary">Register</button>
                            </div>
                            <div class="text-center">
                                <p class="text-muted">Already have an account? 
                                    <a href="login.php" class="text-decoration-none">Click here</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>