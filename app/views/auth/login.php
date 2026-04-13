<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

<div class="container mt-5">
    <h2>Login VetApp</h2>

    <form method="POST" action="/vetapp/public/login">
        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>Contraseña</label>
            <input type="password" name="password" class="form-control">
        </div>

        <button class="btn btn-primary">Ingresar</button>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>