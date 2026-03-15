<?php
/**
 * location: vetapp/app/views/dashboard/index.php 
 */
$title = "Dashboard | VetApp";
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<style>
    /* Estilos para el toque moderno */
    .dashboard-card {
        border: none;
        border-radius: 12px;
        transition: transform 0.2s;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
    }

    .icon-box {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .bg-light-blue {
        background-color: #f0f4f8;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 bg-light-blue">

            <div
                class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h2 class="h3 fw-bold text-dark"><i class="bi bi-speedometer2 me-2"></i>Panel de Administración</h2>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i>
                            Registro Rápido</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card shadow-sm border-start border-primary border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-primary text-white me-3"><i class="bi bi-people-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-0">Clientes</h6>
                                <h3 class="mb-0 fw-bold"><?= $data['totalClients'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card shadow-sm border-start border-success border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-success text-white me-3"><i class="bi bi-dog-fill fs-5"></i></div>
                            <div>
                                <h6 class="text-muted small mb-0">Mascotas</h6>
                                <h3 class="mb-0 fw-bold"><?= $data['totalPets'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card shadow-sm border-start border-info border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-info text-white me-3"><i class="bi bi-clipboard2-pulse fs-5"></i>
                            </div>
                            <div>
                                <h6 class="text-muted small mb-0">Consultas Hoy</h6>
                                <h3 class="mb-0 fw-bold"><?= $data['todayConsultations'] ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-4">
                    <div class="card dashboard-card shadow-sm border-start border-warning border-4">
                        <div class="card-body d-flex align-items-center">
                            <div class="icon-box bg-warning text-white me-3"><i class="bi bi-cash-stack fs-5"></i></div>
                            <div>
                                <h6 class="text-muted small mb-0">Ventas Hoy</h6>
                                <h3 class="mb-0 fw-bold">$<?= number_format($data['todaySales'], 2) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-lg-8">

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="m-0 fw-bold text-primary"><i
                                    class="bi bi-bell-fill text-danger me-2"></i>Recordatorios de Hoy</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th>Mascota</th>
                                            <th>Cliente</th>
                                            <th>Motivo</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($data['todayReminders'])): ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-3 text-muted">No hay recordatorios
                                                    pendientes para hoy.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($data['todayReminders'] as $rem): ?>
                                                <?php
                                                // Limpiar teléfono para Ecuador
                                                $tel = preg_replace('/[^0-9]/', '', $rem['client_phone']);
                                                if (str_starts_with($tel, '0')) {
                                                    $tel = '593' . substr($tel, 1);
                                                }

                                                $mensaje = "Hola " . $rem['client_name'] . ", te saludamos de VetApp. Recordatorio para " . $rem['pet_name'] . ": " . $rem['motivo'];
                                                $waUrl = "https://wa.me/" . $tel . "?text=" . urlencode($mensaje);
                                                ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= $rem['pet_name'] ?></strong>
                                                        <span
                                                            class="badge bg-light text-dark border small"><?= $rem['pet_species'] ?></span>
                                                    </td>
                                                    <td><?= $rem['client_name'] ?></td>
                                                    <td class="small text-muted"><?= $rem['motivo'] ?></td>
                                                    <td>
                                                        <a href="<?= $waUrl ?>" target="_blank"
                                                            class="btn btn-sm btn-success rounded-circle shadow-sm">
                                                            <i class="bi bi-whatsapp"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-graph-up me-2"></i>Rendimiento Anual
                            </h6>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="110"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4 bg-dark text-white">
                        <div
                            class="card-header bg-transparent border-bottom border-secondary py-3 text-warning fw-bold">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>Stock Crítico
                        </div>
                        <div class="card-body">
                            <?php if (empty($data['lowStockMedications'])): ?>
                                <p class="small text-muted mb-0">No hay alertas de stock</p>
                            <?php else: ?>
                                <?php foreach ($data['lowStockMedications'] as $med): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="small"><?= $med['name'] ?></span>
                                        <span class="badge bg-danger rounded-pill"><?= $med['stock'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    const salesData = <?= json_encode($data['monthlySales']) ?>;
    let totals = new Array(12).fill(0);
    salesData.forEach(sale => { totals[sale.month - 1] = sale.total; });

    const ctx = document.getElementById('salesChart');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
            datasets: [{
                label: 'Ventas ($)',
                data: totals,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>