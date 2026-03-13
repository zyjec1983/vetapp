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

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">

            <h2 class="mb-4">Gestión de Usuarios</h2>

            <div class="card shadow-sm">
                <div class="card-body">

                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Activo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id_user'] ?></td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['lastname1']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?= $user['active'] ? 'Sí' : 'No' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div>
            </div>

        </main>

    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>