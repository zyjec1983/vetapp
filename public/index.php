<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VetApp | Login</title>

    <!-- Bootstrap (Bootswatch Litera) -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Custom styles -->
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="login-wrapper">
    <div class="card login-card shadow-lg border-0">
        <div class="card-body p-4">
            <h4 class="text-center mb-4 fw-bold">VetApp Login</h4>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Invalid username or password
                </div>
            <?php endif; ?>

            <form method="POST" action="../app/controllers/auth/AuthController.php">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    Login
                </button>

            </form>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
