<?php
/**
 * Location: vetapp/app/views/users/create.php
 * View for creating new users
 */

$title = 'Nuevo Usuario | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="users.php">Usuarios</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nuevo Usuario</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-person-plus-fill me-2"></i>Registrar Nuevo Usuario
                    </h5>
                </div>
                <div class="card-body p-4">

                    <form id="userForm" action="<?= BASE_URL ?>users.php?action=store" method="POST" autocomplete="off">

                        <!-- ********** GENERA TOKEN ********** -->
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">


                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Información Personal</h6>
                        <div class="row g-3 mb-4">

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Cédula o Pasaporte *</label>
                                <input type="text" name="identification" class="form-control" required maxlength="20"
                                    pattern="[0-9]+" title="Solo se permiten números (0-9)"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Primer Nombre *</label>
                                <input type="text" name="name" class="form-control" required
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                    oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Segundo Nombre</label>
                                <input type="text" name="middlename" class="form-control"
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                    oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Apellido Paterno *</label>
                                <input type="text" name="lastname1" class="form-control" required
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                    oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Apellido Materno</label>
                                <input type="text" name="lastname2" class="form-control"
                                    pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                    oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')">
                            </div>
                        </div>

                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Contacto y Credenciales</h6>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Correo Electrónico *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="usuario@vetapp.com" required>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" class="form-control" placeholder="+593 987654321">
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Contraseña *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" name="password" id="password" class="form-control"
                                        placeholder="Min 5 - Max 8 caracteres" required minlength="5" maxlength="8">
                                </div>
                                <div class="form-text small text-muted">Debe tener entre 5 y 8 caracteres.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Repetir Contraseña *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" name="confirm_password" id="confirm_password"
                                        class="form-control" placeholder="Repita la contraseña" required>
                                </div>
                                <div id="passwordError" class="text-danger small mt-1" style="display: none;">
                                    <i class="bi bi-exclamation-circle"></i> Las contraseñas no coinciden.
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Asignación de Roles</h6>
                        <div class="p-3 border rounded bg-light mb-4">
                            <p class="small text-muted mb-3">Selecciona uno o más roles para este usuario:</p>
                            <div class="row">
                                <div class="col-12 col-sm-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="1"
                                            id="role_admin">
                                        <label class="form-check-label fw-bold text-dark"
                                            for="role_admin">Administrador</label>
                                    </div>
                                    <small class="text-muted d-block ms-4">Control total del sistema</small>
                                </div>

                                <div class="col-12 col-sm-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="2"
                                            id="role_vet">
                                        <label class="form-check-label fw-bold text-dark"
                                            for="role_vet">Veterinario</label>
                                    </div>
                                    <small class="text-muted d-block ms-4">Consultas y registros médicos</small>
                                </div>

                                <div class="col-12 col-sm-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="3"
                                            id="role_pharm">
                                        <label class="form-check-label fw-bold text-dark"
                                            for="role_pharm">Farmacia</label>
                                    </div>
                                    <small class="text-muted d-block ms-4">Gestión de stock y ventas</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="users.php" class="btn btn-outline-secondary px-4">Cancelar</a>
                            <button type="submit" id="submitBtn" class="btn btn-primary px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Guardar Usuario
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const errorDiv = document.getElementById('passwordError');
        const submitBtn = document.getElementById('submitBtn');

        function validatePassword() {
            // Solo validamos si el usuario ya empezó a escribir en el segundo campo
            if (confirmPassword.value.length > 0) {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.classList.add('is-invalid');
                    confirmPassword.classList.remove('is-valid');
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = true;
                } else {
                    confirmPassword.classList.remove('is-invalid');
                    confirmPassword.classList.add('is-valid');
                    errorDiv.style.display = 'none';
                    submitBtn.disabled = false;
                }
            } else {
                confirmPassword.classList.remove('is-invalid', 'is-valid');
                errorDiv.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        password.addEventListener('input', validatePassword);
        confirmPassword.addEventListener('input', validatePassword);
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>