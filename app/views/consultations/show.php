<?php
/**
 * Location: app/views/consultations/show.php
 */

$title = 'Detalle de Consulta | VetApp';

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';

?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>consultations.php">Consultas</a></li>
                    <li class="breadcrumb-item active">Detalle</li>
                </ol>
            </nav>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5>Consulta #<?= $consultation->getIdConsultation() ?></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mascota:</strong> <?= htmlspecialchars($consultation->getPetName()) ?></p>
                            <p><strong>Dueño:</strong> <?= htmlspecialchars($consultation->getClientName()) ?></p>
                            <p><strong>Veterinario:</strong> <?= htmlspecialchars($consultation->getVetName()) ?></p>
                            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($consultation->getConsultationDate())) ?></p>
                            <p><strong>Peso:</strong> <?= $consultation->getWeight() ? $consultation->getWeight() . ' kg' : 'N/A' ?></p>
                            <p><strong>Temperatura:</strong> <?= $consultation->getTemperature() ? $consultation->getTemperature() . ' °C' : 'N/A' ?></p>
                            <p><strong>Honorarios:</strong> $<?= number_format($consultation->getConsultationFee(), 2) ?></p>
                            <p><strong>Estado:</strong>
                                <span class="badge bg-<?= $consultation->getStatus() == 'completed' ? 'success' : ($consultation->getStatus() == 'pending' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($consultation->getStatus()) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Diagnóstico:</strong></p>
                            <div class="border p-2 bg-light"><?= nl2br(htmlspecialchars($consultation->getDiagnosis())) ?></div>
                            <p class="mt-2"><strong>Tratamiento:</strong></p>
                            <div class="border p-2 bg-light"><?= nl2br(htmlspecialchars($consultation->getTreatment() ?: 'Ninguno')) ?></div>
                            <p class="mt-2"><strong>Próxima visita:</strong> <?= $consultation->getNextVisit() ? date('d/m/Y', strtotime($consultation->getNextVisit())) : 'No programada' ?></p>
                            <p><strong>Observaciones:</strong> <?= nl2br(htmlspecialchars($consultation->getObservations() ?: 'Ninguna')) ?></p>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="<?= BASE_URL ?>consultations.php" class="btn btn-secondary">Volver</a>
                    <a href="<?= BASE_URL ?>consultations.php?action=edit&id=<?= $consultation->getIdConsultation() ?>" class="btn btn-primary">Editar</a>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>