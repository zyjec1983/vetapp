<?php
/**
 * Location: vetapp/app/views/users/edit.php
 * View for editing users
 */

$title = 'Editar Usuario | VetApp';

// Recuperar datos del formulario si hay error
$formData = $_SESSION['form_data'] ?? $user ?? [];
unset($_SESSION['form_data']);

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
                    <li class="breadcrumb-item active" aria-current="page">Editar Usuario</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Editar Usuario
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    <form action="/vetapp/public/users.php?action=update&id=<?= $user['id_user'] ?>" method="POST" autocomplete="off">

                        <!-- ***************** INGRESO DE DATOS ***************** -->
                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Información Personal</h6>
                        <div class="row g-3 mb-4">

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Cédula o Pasaporte *</label>
                                <input type="text" 
                                       name="identification" 
                                       class="form-control" 
                                       required 
                                       maxlength="20"
                                       pattern="[0-9]+" 
                                       title="Solo se permiten números (0-9)"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                       value="<?= htmlspecialchars($formData['identification'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Primer Nombre *</label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control" 
                                       required
                                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                       title="No se permiten números ni caracteres especiales"
                                       oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')"
                                       value="<?= htmlspecialchars($formData['name'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Segundo Nombre</label>
                                <input type="text" 
                                       name="middlename" 
                                       class="form-control"
                                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                       title="No se permiten números ni caracteres especiales"
                                       oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')"
                                       value="<?= htmlspecialchars($formData['middlename'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Apellido Paterno *</label>
                                <input type="text" 
                                       name="lastname1" 
                                       class="form-control" 
                                       required
                                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                       title="No se permiten números ni caracteres especiales"
                                       oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')"
                                       value="<?= htmlspecialchars($formData['lastname1'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold small">Apellido Materno</label>
                                <input type="text" 
                                       name="lastname2" 
                                       class="form-control"
                                       pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+"
                                       title="No se permiten números ni caracteres especiales"
                                       oninput="this.value = this.value.replace(/[^A-Za-zñÑáéíóúÁÉÍÓÚ\s]/g, '')"
                                       value="<?= htmlspecialchars($formData['lastname2'] ?? '') ?>">
                            </div>
                        </div>

                        <!-- ***************** INGRESO DE DATOS CONTACTOS ***************** -->
                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Contacto y Credenciales</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Correo Electrónico *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" 
                                           name="email" 
                                           class="form-control" 
                                           placeholder="usuario@vetapp.com"
                                           required
                                           value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" 
                                           name="phone" 
                                           class="form-control"
                                           placeholder="+593 987654321"
                                           value="<?= htmlspecialchars($formData['phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold small">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password" 
                                           name="password" 
                                           class="form-control" 
                                           placeholder="Dejar vacío para no cambiar">
                                </div>
                                <small class="text-muted">Mínimo 6 caracteres. Dejar vacío para mantener actual.</small>
                            </div>
                        </div>

                        <!-- ***************** ASIGNACIÓN DE ROLES ***************** -->
                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Asignación de Roles</h6>
                        <div class="p-3 border rounded bg-light mb-4">
                            <p class="small text-muted mb-3">Selecciona uno o más roles para este usuario:</p>
                            <div class="row">
                                <?php foreach ($roles as $role): ?>
                                <div class="col-12 col-sm-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="roles[]" 
                                               value="<?= $role['id_role'] ?>"
                                               id="role_<?= $role['id_role'] ?>"
                                               <?= (isset($formData['role_ids']) && in_array($role['id_role'], $formData['role_ids'])) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-dark"
                                               for="role_<?= $role['id_role'] ?>">
                                            <?= ucfirst($role['name']) ?>
                                        </label>
                                    </div>
                                    <small class="text-muted d-block ms-4"><?= $role['description'] ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ***************** ESTADO DEL USUARIO ***************** -->
                        <h6 class="text-primary text-uppercase small fw-bold mb-3">Estado del Usuario</h6>
                        <div class="p-3 border rounded bg-light mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="active" 
                                       id="active" 
                                       value="1"
                                       <?= (!isset($formData['active']) || $formData['active']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="active">
                                    Usuario Activo
                                </label>
                                <small class="text-muted d-block">Desmarca esta casilla para desactivar el acceso del usuario</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            <a href="users.php" class="btn btn-outline-secondary px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning px-5 shadow-sm">
                                <i class="bi bi-save me-2"></i>Actualizar Usuario
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>