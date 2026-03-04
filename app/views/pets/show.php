<?php
/**
 * Location: vetapp/app/views/pets/show.php
 * View for displaying a single pet's details
 */
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Columna Izquierda: Información de la Mascota y Dueño -->
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <img src="<?= $pet['picture'] ?? 'img/default-pet.png' ?>" class="card-img-top" alt="Mascota">
                <div class="card-body">
                    <h3 class="card-title text-primary"><?= $pet['name'] ?></h3>
                    <p class="badge bg-info"><?= $pet['species'] ?> - <?= $pet['breed'] ?></p>
                    <hr>
                    <h6><i class="bi bi-person"></i> Dueño: <span class="text-muted"><?= $client['name'] ?></span></h6>
                    <p><i class="bi bi-telephone"></i> <?= $client['phone'] ?></p>
                    <a href="client_controller.php?id=<?= $client['id_client'] ?>" class="btn btn-outline-primary btn-sm w-100">Ver Dueño</a>
                </div>
            </div>
            
            <div class="card border-warning mb-4">
                <div class="card-header">Alertas Médicas</div>
                <div class="card-body">
                    <p class="card-text text-danger"><strong>Alergias:</strong> <?= $pet['allergies'] ?? 'Ninguna' ?></p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Timeline de Consultas y Vacunas -->
        <div class="col-md-8">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#history">Historial Clínico</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#vaccines">Vacunas</a></li>
            </ul>

            <div class="tab-content mt-3">
                <div id="history" class="tab-pane fade show active">
                    <div class="d-flex justify-content-between mb-3">
                        <h4>Últimas Consultas</h4>
                        <button class="btn btn-success btn-sm">+ Nueva Consulta</button>
                    </div>
                    <?php foreach($consultations as $c): ?>
                    <div class="list-group mb-2">
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 text-primary">Diagnóstico: <?= substr($c['diagnosis'], 0, 50) ?>...</h5>
                                <small class="text-muted"><?= $c['consultation_date'] ?></small>
                            </div>
                            <p class="mb-1"><?= $c['treatment'] ?></p>
                            <small>Atendido por: Dr. <?= $c['user_name'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>