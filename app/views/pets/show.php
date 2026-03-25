<?php
// app/views/pets/show.php

$title = 'Ficha de ' . htmlspecialchars($pet->getName()) . ' | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>pets.php">Mascotas</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pet->getName()) ?></li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-file-medical me-2"></i>Ficha Médica
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-4 text-center">
                            <?php if ($pet->getPicture()): ?>
                                <img src="<?= BASE_URL ?>storage/uploads/<?= $pet->getPicture() ?>"
                                    class="img-fluid rounded mb-3" style="max-height: 200px;">
                            <?php else: ?>
                                <div class="bg-light rounded p-5 mb-3">
                                    <i class="bi bi-camera" style="font-size: 3rem;"></i>
                                    <p class="text-muted">Sin foto</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-8">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Nombre</th>
                                    <td><?= htmlspecialchars($pet->getName()) ?></td>
                                </tr>

                                <tr>
                                    <th>Dueño</th>
                                    <td><?= htmlspecialchars($pet->getClientName()) ?></td>
                                </tr>

                                <tr>
                                    <th>Especie</th>
                                    <td><?= htmlspecialchars($pet->getSpecies()) ?></td>
                                </tr>

                                <tr>
                                    <th>Raza</th>
                                    <td><?= htmlspecialchars($pet->getBreed() ?: 'No especificada') ?></td>
                                </tr>

                                <tr>
                                    <th>Sexo</th>
                                    <td><?= $pet->getSex() == 'M' ? 'Macho' : ($pet->getSex() == 'F' ? 'Hembra' : 'Desconocido') ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Fecha de nacimiento</th>
                                    <td><?= $pet->getDateOfBirth() ?: 'No registrada' ?></td>
                                </tr>
                                <tr>
                                    <th>Edad</th>
                                    <td><?= $pet->getAge() ?></td>
                                </tr>

                                <tr>
                                    <th>Peso</th>
                                    <td><?= $pet->getCurrentWeight() ? $pet->getCurrentWeight() . ' kg' : 'No registrado' ?>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th>Color</th>
                                    <td><?= htmlspecialchars($pet->getColor() ?: 'No registrado') ?></td>
                                </tr>
                                <tr>
                                    <th>Microchip</th>
                                    <td><?= htmlspecialchars($pet->getMicrochip() ?: 'No registrado') ?></td>
                                </tr>
                                <tr>
                                    <th>Alergias</th>
                                    <td><?= nl2br(htmlspecialchars($pet->getAllergies() ?: 'Ninguna')) ?></td>
                                </tr>
                                <tr>
                                    <th>Observaciones</th>
                                    <td><?= nl2br(htmlspecialchars($pet->getObservations() ?: 'Ninguna')) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>pets.php" class="btn btn-secondary">Volver</a>
                    <a href="<?= BASE_URL ?>pets.php?action=edit&id=<?= $pet->getIdPet() ?>"
                        class="btn btn-primary">Editar</a>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>