<?php
/**
 * Location: vetapp/app/views/users/index.php
 * View for listing all users (admin only)
 */

$title = 'Usuarios | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">

            <!-- ******************* BOTON DE CREACIÓN DE USUARIO ****************** -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="">Gestión de Usuarios</h2>
                <a href="/vetapp/public/users.php?action=create" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
                </a>
            </div>

            <!-- ******************* TABLA DE USUARIOS DEL SISTEMA ****************** -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Identificación</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th class="text-center">Activo</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- ******************* LLENADO DE TABLA DE USUARIO ****************** -->
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark">#<?= $user['id_user'] ?></span></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['lastname1']) ?></td>
                                        <td><?= htmlspecialchars($user['identification']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td class="text-center">
                                            <?php if ($user['active']): ?>
                                                <span class="badge rounded-pill bg-success">Sí</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger">No</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- ******************* BOTON DE ACCIÓN EDITAR/ELIMINAR ****************** -->
                                        <!-- <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="<?= BASE_URL ?>users.php?action=edit&id=<?= $user['id_user'] ?>"
                                                    class="btn btn-outline-info btn-sm" title="Editar Usuario">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    title="Eliminar Usuario"
                                                    onclick="confirmDelete(<?= $user['id_user'] ?>)">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>  -->


                                        <td>
                                            <!-- ******************* BOTON EDITAR USUARIO ******************* -->
                                            <a href="<?= BASE_URL ?>users.php?action=edit&id=<?= $user['id_user'] ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- ******************* BOTON DESACTIVAR USUARIO ******************* -->
                                            <a href="<?= BASE_URL ?>users.php?action=deactivate&id=<?= $user['id_user'] ?>"
                                                class="btn btn-sm btn-danger btn-delete" data-name="<?= $user['name'] ?>">
                                                <i class="bi bi-person-x"></i>
                                            </a>
                                        </td>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();

            const url = this.href;
            const name = this.dataset.name || 'este usuario';

            Swal.fire({
                title: '¿Estás seguro?',
                text: `Se desactivará ${name}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>