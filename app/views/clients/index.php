<?php
// app/views/clients/index.php

$title = 'Clientes | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Clientes</h2>
                <a href="<?= BASE_URL ?>clients.php?action=create" class="btn btn-primary">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Cliente
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre completo</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Identificación</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark">#<?= $client->getIdClient() ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($client->getFullName()) ?></td>
                                        <td><?= htmlspecialchars($client->getPhone()) ?></td>
                                        <td><?= htmlspecialchars($client->getEmail()) ?></td>
                                        <td><?= htmlspecialchars($client->getIdentification()) ?></td>
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>clients.php?action=edit&id=<?= $client->getIdClient() ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-warning btn-deactivate"
                                                data-id="<?= $client->getIdClient() ?>"
                                                data-name="<?= htmlspecialchars($client->getFullName()) ?>">
                                                <i class="bi bi-person-x"></i> Desactivar
                                            </button>
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
    document.querySelectorAll('.btn-deactivate').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Desactivar cliente?',
                text: `El cliente "${name}" quedará inactivo y no aparecerá en la lista principal.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>clients.php?action=deactivate&id=' + id;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>