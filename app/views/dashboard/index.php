<!-- 
Ubicacion: app/views/dashboard/index.php 
-->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>VetApp | Dashboard</title>
    <!-- Usando Bootswatch Pulse -->
    <link rel="stylesheet" href="<?= BASE_URL ?>css/bootstrap.min.css">
    <!-- Iconos de Bootstrap (Indispensables para el look profesional) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net">
</head>

<body class="bg-light">

    <?php require_once __DIR__ . '/../layouts/navbar.php'; ?>

    <div class="container py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">
                <i class="bi bi-speedometer2"></i> Panel de Control
            </h2>
            <h3><strong><span class="badge bg-secondary p-2">Bienvenido: <?= htmlspecialchars($_SESSION['user']['name']) ?></span></strong></h3>
        </div>

        <!-- 1. WIDGETS DE ESTADO (Métricas rápidas) -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Citas Hoy</h6>
                        <h2 class="text-primary fw-bold">12</h2> <!-- Cambiar por variable de DB -->
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-danger shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Stock Bajo</h6>
                        <h2 class="text-danger fw-bold">5</h2> <!-- Cambiar por variable de DB -->
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Ventas Semanales</h6>
                        <h2 class="text-success fw-bold">$450.25</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info shadow-sm text-center">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Pacientes Activos</h6>
                        <h2 class="text-info fw-bold">142</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. ACCESOS DIRECTOS SEGÚN ROL -->
        <div class="row g-4">
            <!-- Bloque Dinámico: Cambia el 'Entrar' por acciones directas -->
            <?php if (hasRole('admin')): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="bi bi-shield-lock"></i> Admin</h5>
                            <ul class="list-unstyled mt-3">
                                <li><a href="<?= BASE_URL ?>users" class="text-decoration-none">→ Gestionar Personal</a></li>
                                <li><a href="<?= BASE_URL ?>reports" class="text-decoration-none">→ Reportes Contables</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (hasRole('veterinarian')): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-4 border-success">
                        <div class="card-body text-center">
                            <h5 class="card-title text-success"><i class="bi bi-heart-pulse"></i> Clínica</h5>
                            <div class="d-grid gap-2 mt-3">
                                <a href="<?= BASE_URL ?>consultations/create" class="btn btn-success btn-md">Nueva Consulta</a>
                                <a href="<?= BASE_URL ?>pets" class="btn btn-outline-success btn-md">Ver Mascotas</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (hasRole('pharmacy')): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 border-top border-4 border-warning">
                        <div class="card-body text-center">
                            <h5 class="card-title text-warning"><i class="bi bi-cart4"></i> Ventas</h5>
                            <div class="d-grid gap-2 mt-3">
                                <a href="<?= BASE_URL ?>sales/create" class="btn btn-warning btn-md text-white">Facturar Ahora</a>
                                <a href="<?= BASE_URL ?>medications" class="btn btn-outline-warning btn-md">Stock Farmacia</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 3. SECCIÓN DE ACTIVIDAD RECIENTE (TABLA) -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-dark">Próximos Recordatorios de Vacunas</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Mascota</th>
                                    <th>Dueño</th>
                                    <th>Vacuna</th>
                                    <th>Fecha</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Esto vendrá de tu tabla 'reminders' o 'vaccines' -->
                                <tr>
                                    <td><strong>Firulais</strong></td>
                                    <td>Juan Pérez</td>
                                    <td><span class="badge bg-light text-dark">Antirrábica</span></td>
                                    <td>15 Oct 2026</td>
                                    <td><button class="btn btn-sm btn-outline-primary"><i class="bi bi-whatsapp"></i> Avisar</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts de Bootstrap (Necesarios para dropdowns y modales) -->
    <script src="<?= BASE_URL ?>js/bootstrap.bundle.min.js"></script>
</body>
</html>