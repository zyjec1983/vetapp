<?php
/**
 * Location: vetapp/public/index.php
 */

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
    <div class="login-card card shadow-lg border-0">
        <div class="card-body p-4">

            <h4 class="text-center text-white mb-4">VetApp Login</h4>

            <form method="POST" action="../app/controllers/auth/AuthController.php">

                <div class="mb-3">
                    <label class="form-label text-white">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Login
                </button>

            </form>

        </div>
    </div>
</div>

</body>


</html>