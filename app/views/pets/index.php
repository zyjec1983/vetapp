<?php
// app/views/pets/index.php

$title = 'Mascotas | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Mascotas</h2>
                <a href="<?= BASE_URL ?>pets.php?action=create" class="btn btn-primary">
                    <i class="bi bi-heart-plus me-1"></i> Nueva Mascota
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Dueño</th>
                                    <th>Especie</th>
                                    <th>Raza</th>
                                    <th>Sexo</th>
                                    <th>Edad</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pets as $pet): ?>
                                <tr>
                                    <td><span class="badge bg-light text-dark">#<?= $pet->getIdPet() ?></span></td>
                                    <td><?= htmlspecialchars($pet->getName()) ?></td>
                                    <td><?= htmlspecialchars($pet->getClientName()) ?></td>
                                    <td><?= htmlspecialchars($pet->getSpecies()) ?></td>
                                    <td><?= htmlspecialchars($pet->getBreed()) ?></td>
                                    <td><?= $pet->getSex() == 'M' ? 'Macho' : ($pet->getSex() == 'F' ? 'Hembra' : 'Desconocido') ?></td>
                                    <td><?= $pet->getAge() ?></td>
                                    <td class="text-center">
                                        <a href="<?= BASE_URL ?>pets.php?action=show&id=<?= $pet->getIdPet() ?>" class="btn btn-sm btn-info" title="Ver ficha">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>pets.php?action=edit&id=<?= $pet->getIdPet() ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-warning btn-deactivate"
                                                data-id="<?= $pet->getIdPet() ?>"
                                                data-name="<?= htmlspecialchars($pet->getName()) ?>">
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
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            const name = this.dataset.name;
            Swal.fire({
                title: '¿Desactivar mascota?',
                text: `La mascota "${name}" quedará inactiva y no aparecerá en la lista principal.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= BASE_URL ?>pets.php?action=deactivate&id=' + id;
                }
            });
        });
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>