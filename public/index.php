<?php
/**
 * Location: vetapp/public/index.php
 * Role: Login view + front controller for login
 */

session_start();

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/auth/AuthController.php';

// ==========================
// SI YA ESTÁ LOGUEADO
// ==========================
if (isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

// ==========================
// SI VIENE POST → LOGIN
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AuthController())->login();
    exit;
}

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>

<!-- *************** INICIO DEL FORMULARIO DE LOGIN ****************** -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>VetApp | Login</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <div class="login-wrapper">
        <div class="login-card card shadow-lg border-0">
            <div class="card-body p-4">

                <h4 class="text-center text-white mb-4">VetApp Login</h4>

                <!-- ***** Maneja errores ***** -->
                <?php if ($error): ?>
                    <div class="alert alert-danger text-center">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- action apunta al MISMO index -->
                <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">

                    <div class="mb-3">
                        <label class="form-label text-white">Email</label>
                        <input type="email" name="email" class="form-control" required>
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