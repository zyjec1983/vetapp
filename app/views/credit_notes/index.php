<?php
/**
 * Location: vetapp/app/views/credit_notes/index.php
 *
 * Listado de Notas de Crédito.
 */

$title = 'Notas de Crédito | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2><i class="bi bi-arrow-return-left me-2"></i>Notas de Crédito</h2>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <?php if (empty($creditNotes)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                            No hay notas de crédito registradas.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>NC #</th>
                                        <th>Venta</th>
                                        <th>Cliente</th>
                                        <th>Factura Original</th>
                                        <th>Motivo</th>
                                        <th>Total</th>
                                        <th>Estado SRI</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($creditNotes as $nc): ?>
                                        <tr>
                                            <td><code><?= htmlspecialchars($nc['numero_nota_credito'] ?? '-') ?></code></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $nc['id_sale'] ?>">
                                                    <?= htmlspecialchars($nc['sale_code'] ?? '') ?>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($nc['client_name'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($nc['factura_original'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($nc['motivo']) ?></td>
                                            <td class="text-danger fw-bold">-$<?= number_format($nc['total'], 2) ?></td>
                                            <td>
                                                <?php if ($nc['estado_sri'] === 'autorizada'): ?>
                                                    <span class="badge bg-success">Autorizada</span>
                                                <?php elseif ($nc['estado_sri'] === 'rechazada'): ?>
                                                    <span class="badge bg-danger">Rechazada</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><?= ucfirst($nc['estado_sri']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($nc['created_at'])) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>credit_notes.php?action=show&id=<?= $nc['id'] ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
