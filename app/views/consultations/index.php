<?php
/**
 * Location: vetapp/app/views/consultations/index.php
 * View for listing all consultations
 */

// echo "🔥 VISTA EJECUTADA";
//die();


$title = 'Consultas | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

?>


<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">

            <!-- ******************* HEADER ****************** -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Consultas</h2>
                <a href="<?= BASE_URL ?>consultations.php?action=create" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-1"></i> Nueva Consulta
                </a>
            </div>

            <!-- ******************* TABLA ****************** -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">

                        <table class="table table-hover table-striped align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Mascota</th>
                                    <th>Cliente</th>
                                    <th>Veterinario</th>
                                    <th>Diagnóstico</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($consultations as $c): ?>
                                    <tr>
                                        <!-- ID -->
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                #<?= $c['id_consultation'] ?>
                                            </span>
                                        </td>

                                        <!-- Fecha -->
                                        <td>
                                            <?= date('Y-m-d H:i', strtotime($c['consultation_date'])) ?>
                                        </td>

                                        <!-- Mascota -->
                                        <td><?= htmlspecialchars($c['pet_name']) ?></td>

                                        <!-- Cliente -->
                                        <td><?= htmlspecialchars($c['client_name']) ?></td>

                                        <!-- Veterinario -->
                                        <td><?= htmlspecialchars($c['vet_name']) ?></td>

                                        <!-- Diagnóstico corto -->
                                        <td>
                                            <?= htmlspecialchars($c['diagnosis_short']) ?>...
                                        </td>

                                        <!-- Estado -->
                                        <td class="text-center">
                                            <?php if ($c['status'] === 'completed'): ?>
                                                <span class="badge bg-success">Completado</span>
                                            <?php elseif ($c['status'] === 'pending'): ?>
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Cancelado</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Acciones -->
                                        <td class="text-center">
                                            <a href="<?= BASE_URL ?>consultations.php?action=show&id=<?= $c['id_consultation'] ?>"
                                                class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="<?= BASE_URL ?>consultations.php?action=edit&id=<?= $c['id_consultation'] ?>"
                                                class="btn btn-sm btn-primary" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>