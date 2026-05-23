<?php
/**
 * Location: vetapp/app/views/sales/sri_select_company.php
 *
 * Vista: Selección de empresa/RUC antes de enviar factura al SRI.
 * El usuario elige con qué establecimiento va a facturar esta venta.
 */

$title = 'Enviar al SRI | VetApp';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>sales.php">Ventas</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>">Venta</a></li>
                    <li class="breadcrumb-item active">Enviar al SRI</li>
                </ol>
            </nav>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <?php
                    $clientIdent = $saleData['sale']['client_identification'] ?? '';
                    $saleTotal = (float)($saleData['sale']['total'] ?? 0);
                    $isCF = ($clientIdent === '9999999999999');
                    ?>

                    <?php if ($isCF && $saleTotal >= 150): ?>
                        <div class="card shadow border-danger">
                            <div class="card-header bg-danger text-white">
                                <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>No se puede facturar electrónicamente</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <strong>El monto ($<?= number_format($saleTotal, 2) ?>) supera el límite de $150.00</strong><br>
                                    <small>Según la normativa del SRI (Resolución NAC-DGERCGC22-00000017-2022), las ventas a Consumidor Final solo pueden emitirse electrónicamente cuando el total no excede $150.00.</small>
                                </div>
                                <p class="text-muted mb-3">Para facturar esta venta electrónicamente, debe registrar un cliente con identificación real (RUC o Cédula).</p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="<?= BASE_URL ?>clients.php?action=create" class="btn btn-primary">
                                        <i class="bi bi-person-plus me-1"></i> Crear Cliente
                                    </a>
                                    <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Volver a la Venta
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-send me-2"></i>Seleccione el Establecimiento para Facturar</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Esta venta será facturada electrónicamente. Seleccione con qué RUC/establecimiento
                                desea emitir la factura.
                            </p>

                            <?php if (empty($companies)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    No hay empresas configuradas.
                                    <a href="<?= BASE_URL ?>settings.php">Agregue una empresa aquí</a>.
                                </div>
                            <?php else: ?>
                                <div class="list-group">
                                    <?php foreach ($companies as $c): ?>
                                        <label class="list-group-item list-group-item-action d-flex align-items-start mb-2 <?= $c['ambiente'] === 'pruebas' ? 'list-group-item-warning' : '' ?>">
                                            <input class="form-check-input me-3 mt-1" type="radio" name="company_id" value="<?= $c['id'] ?>" required>
                                            <div>
                                                 <strong><?= htmlspecialchars($c['commercial_name']) ?></strong><br>
                                                 <small class="text-muted">
                                                     RUC: <code><?= htmlspecialchars($c['ruc']) ?></code> |
                                                     <?= htmlspecialchars($c['address'] ?? 'Sin dirección') ?> |
                                                     Tel: <?= htmlspecialchars($c['phone'] ?? '-') ?>
                                                </small><br>
                                                <span class="badge <?= $c['ambiente'] === 'pruebas' ? 'bg-warning' : 'bg-success' ?>">
                                                    <?= ucfirst($c['ambiente']) ?>
                                                </span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <a href="<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Volver
                                    </a>
                                    <button class="btn btn-primary btn-lg" id="btnSendSri" onclick="enviarAlSri()">
                                        <i class="bi bi-send me-1"></i> Enviar al SRI
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estado del proceso -->
                    <div id="sriStatus" class="card shadow mt-3" style="display:none;">
                        <div class="card-body text-center">
                            <div class="spinner-border text-primary mb-3" role="status" id="sriSpinner">
                                <span class="visually-hidden">Procesando...</span>
                            </div>
                            <h5 id="sriMessage">Procesando factura electrónica...</h5>
                            <p class="text-muted" id="sriDetail"></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function enviarAlSri() {
    const companyId = document.querySelector('input[name="company_id"]:checked');
    if (!companyId) {
        Swal.fire('Seleccione Empresa', 'Debe seleccionar un establecimiento/RUC para facturar.', 'warning');
        return;
    }

    const btn = document.getElementById('btnSendSri');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enviando...';

    const statusDiv = document.getElementById('sriStatus');
    statusDiv.style.display = 'block';
    document.getElementById('sriSpinner').style.display = '';
    document.getElementById('sriMessage').textContent = 'Generando factura electrónica...';
    document.getElementById('sriDetail').textContent = 'Creando XML, firmando y enviando al SRI...';

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 90000);

    fetch(`<?= BASE_URL ?>sri.php?action=send`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `sale_id=<?= $saleId ?>&company_id=${companyId.value}&csrf_token=<?= generateCSRFToken() ?>`,
        signal: controller.signal
    })
    .then(res => res.json())
    .then(data => {
        clearTimeout(timeoutId);
        document.getElementById('sriSpinner').style.display = 'none';

        if (data.success) {
            document.getElementById('sriMessage').innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> ¡Factura Autorizada!</span>';
            document.getElementById('sriDetail').textContent = data.mensaje;

            Swal.fire({
                icon: 'success',
                title: 'Factura Autorizada',
                text: data.mensaje,
                confirmButtonText: 'Ver Factura'
            }).then(() => {
                window.location.href = '<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>';
            });
        } else if (data.pending) {
            document.getElementById('sriMessage').innerHTML = '<span class="text-warning"><i class="bi bi-clock-fill"></i> Pendiente de Autorización</span>';
            document.getElementById('sriDetail').textContent = data.error;

            Swal.fire({
                icon: 'warning',
                title: 'Factura Pendiente',
                text: data.error,
                confirmButtonText: 'Reintentar',
                showCancelButton: true,
                cancelButtonText: 'Ver Venta'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send me-1"></i> Enviar al SRI';
                    document.getElementById('sriStatus').style.display = 'none';
                } else {
                    window.location.href = '<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>';
                }
            });
        } else {
            document.getElementById('sriMessage').innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error</span>';
            document.getElementById('sriDetail').textContent = data.error || 'Error desconocido';

            Swal.fire({
                title: 'Error al enviar al SRI',
                html: data.error + (data.observaciones && data.observaciones.length > 0 ? '<br><small>' + data.observaciones.map(o => o.mensaje).join('<br>') + '</small>' : ''),
                icon: 'error',
                confirmButtonText: 'Reintentar',
                showCancelButton: true,
                cancelButtonText: 'Ver Venta'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-send me-1"></i> Reintentar';
                    document.getElementById('sriStatus').style.display = 'none';
                } else {
                    window.location.href = '<?= BASE_URL ?>sales.php?action=show&id=<?= $saleId ?>';
                }
            });
        }
    })
    .catch(err => {
        clearTimeout(timeoutId);
        document.getElementById('sriSpinner').style.display = 'none';

        let msg = err.name === 'AbortError' ? 'Tiempo de espera agotado (90s). El SRI no respondió.' : err.message;
        document.getElementById('sriMessage').innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Error de Conexión</span>';
        document.getElementById('sriDetail').textContent = msg;

        Swal.fire('Error de Conexión', msg, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-1"></i> Reintentar';
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
