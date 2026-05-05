<?php 
/**
 * Location: vetapp/app/views/auth/login.php
 * Página de login - Diseño responsivo y profesional
 */

$title = 'Login | VetApp';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<!-- Estilos adicionales para el login -->
<style>
    /* Página de login - fondo gradiente */
    .login-page {
        min-height: 100vh;
        width: 100%;
        padding: 2rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-sizing: border-box;
    }
    
    /* Contenedor del formulario */
    .login-form-container {
        width: 100%;
        max-width: 400px;
        margin: 0 auto;
    }
    
    /* Tarjeta de login */
    .login-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    /* Logo */
    .brand-logo {
        font-size: 3rem;
    }
</style>

<div class="login-page">
    <div class="login-form-container">
        <div class="card login-card">
                    <div class="card-header login-header text-center py-4">
                        <div class="brand-logo">
                            <i class="bi bi-heart-pulse"></i>
                        </div>
                        <h1 class="text-dark mb-0">VetApp</h1>
                        <p class="text-muted small mb-0">Sistema de Gestión Veterinaria</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="<?= BASE_URL ?>login" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" 
                                           id="email"
                                           name="email" 
                                           class="form-control form-control-lg" 
                                           placeholder="correo@ejemplo.com"
                                           required
                                           autocomplete="email">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" 
                                           id="password"
                                           name="password" 
                                           class="form-control form-control-lg" 
                                           placeholder="Ingresa tu contrasela"
                                           required
                                           autocomplete="current-password">
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Iniciar Sesión
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer text-center py-3 bg-transparent">
                        <small class="text-muted">
                            &copy; <?= date('Y'); ?> VetApp - Todos los derechos reservados
                        </small>
                    </div>
                </div>
        </div>
</div>

<script>
// Validación de formulario Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>