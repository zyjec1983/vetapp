<?php
/**
 * location: vetapp/app/views/dashboard/index.php 
 * Dasboard for admin
 */

$title = "Dashboard | VetApp";

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">

    <div class="row">

        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

            <h2 class="mt-4 mb-4">
                <i class="bi bi-speedometer2"></i> Panel de Administración
            </h2>

            <div class="row">

                <!-- **************** CLIENTS **************** -->
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-primary">
                        <div class="card-body">
                            <h6>Clientes</h6>
                            <h2><?= $data['totalClients'] ?></h2>
                        </div>
                    </div>
                </div>

                <!-- **************** MASCOTS **************** -->
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-success">
                        <div class="card-body">
                            <h6>Mascotas</h6>
                            <h2><?= $data['totalPets'] ?></h2>
                        </div>
                    </div>
                </div>

                <!-- **************** TODAY'S CONSULTATIONS **************** -->
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-info">
                        <div class="card-body">
                            <h6>Consultas Hoy</h6>
                            <h2><?= $data['todayConsultations'] ?></h2>
                        </div>
                    </div>
                </div>

                <!-- **************** TODAY'S SALES **************** -->
                <div class="col-md-3 mb-3">
                    <div class="card text-bg-warning">
                        <div class="card-body">
                            <h6>Ventas Hoy</h6>
                            <h2>$<?= number_format($data['todaySales'], 2) ?></h2>
                        </div>
                    </div>
                </div>
            </div> <!-- 🔴 CIERRA LA FILA DE TARJETAS -->

            <div class="row">
                <!-- **************** RECENT CONSULTATIONS && RECENT SALES **************** -->
                <div class="row mt-4">
                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="bi bi-clipboard2-pulse"></i> Consultas recientes
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($data['recentConsultations'])): ?>
                                    <div class="p-3 text-muted">No hay consultas registradas</div>
                                <?php else: ?>
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Mascota</th>
                                                <th>Cliente</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['recentConsultations'] as $c): ?>
                                                <tr>
                                                    <td><?= $c['consultation_date'] ?></td>
                                                    <td><?= $c['pet_name'] ?></td>
                                                    <td><?= $c['client_name'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="bi bi-receipt"></i> Ventas recientes
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($data['recentSales'])): ?>
                                    <div class="p-3 text-muted">No hay ventas registradas</div>
                                <?php else: ?>
                                    <table class="table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($data['recentSales'] as $sale): ?>
                                                <tr>
                                                    <td><?= $sale['sale_date'] ?></td>
                                                    <td>$<?= number_format($sale['total'], 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- **************** LOW STOCK OF MEDICINE **************** -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card border-warning">

                        <div class="card-header bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i>
                            Medicamentos con poco stock
                        </div>

                        <div class="card-body p-0">
                            <?php if (empty($data['lowStockMedications'])): ?>

                                <div class="p-3 text-muted">
                                    No hay medicamentos con stock bajo
                                </div>

                            <?php else: ?>

                                <table class="table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Medicamento</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php foreach ($data['lowStockMedications'] as $med): ?>

                                            <tr>
                                                <td><?= $med['name'] ?></td>
                                                <td>
                                                    <span class="badge bg-danger">
                                                        <?= $med['stock'] ?>
                                                    </span>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>

                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>


            <!-- **************** GRAPH **************** -->
            <div class="row mt-4 mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-graph-up"></i>
                            Ventas del año
                        </div>

                        <div class="card-body">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- **************** MAIN ENDS **************** -->
        </main>

    </div>
</div>

<script>
    const salesData = <?= json_encode($data['monthlySales']) ?>;

    const months = [
        "Ene", "Feb", "Mar", "Abr", "May", "Jun",
        "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"
    ];

    let totals = new Array(12).fill(0);

    salesData.forEach(sale => {
        totals[sale.month - 1] = sale.total;
    });

    const ctx = document.getElementById('salesChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Ventas ($)',
                data: totals
            }]
        },
        options: {
            responsive: true
        }
    });

</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>