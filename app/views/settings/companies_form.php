<?php
/**
 * Location: vetapp/app/views/settings/companies_form.php
 *
 * Vista dedicada para crear/editar empresas (reemplaza el modal).
 * Variables esperadas: $company (array|null), $errors (array)
 */

if (!isset($company)) {
    $company = null;
}
if (!isset($errors)) {
    $errors = [];
}
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

$isEdit = $company !== null;
$title = ($isEdit ? 'Editar Empresa: ' . htmlspecialchars($company['commercial_name']) : 'Nueva Empresa') . ' | VetApp';

$old = $_SESSION['old_input'] ?? [];
if (isset($_SESSION['old_input'])) {
    unset($_SESSION['old_input']);
}

function old($key, $default = '') {
    global $company, $isEdit, $old;
    if (!empty($old) && isset($old[$key])) {
        return $old[$key];
    }
    if ($isEdit && isset($company[$key])) {
        return $company[$key];
    }
    return $default;
}

function hasError($key) {
    global $errors;
    return !empty($errors[$key]);
}

function errorFor($key) {
    global $errors;
    return $errors[$key] ?? '';
}

function inputClass($key) {
    return hasError($key) ? 'is-invalid' : '';
}

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../layouts/aside.php'; ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4 pb-5">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>settings.php">Empresas</a></li>
                    <li class="breadcrumb-item active"><?= $isEdit ? 'Editar' : 'Nueva' ?></li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2>
                    <?php if ($isEdit): ?>
                        <i class="bi bi-pencil-square me-2"></i>Editar Empresa
                    <?php else: ?>
                        <i class="bi bi-building me-2"></i>Nueva Empresa
                    <?php endif; ?>
                </h2>
                <a href="<?= BASE_URL ?>settings.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver a la lista
                </a>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i><?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle-fill me-1"></i><?= htmlspecialchars($_SESSION['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-header <?= (old('ambiente', $isEdit ? ($company['ambiente'] ?? 'pruebas') : 'pruebas') === 'produccion') ? 'bg-danger text-white' : 'bg-primary text-white' ?>" id="formHeader">
                    <h5 class="mb-0" id="formTitle">
                        <?php if ((old('ambiente', $isEdit ? ($company['ambiente'] ?? 'pruebas') : 'pruebas')) === 'produccion'): ?>
                            <i class="bi bi-exclamation-triangle me-2"></i>MODO PRODUCCIÓN
                        <?php else: ?>
                            <i class="bi bi-building me-2"></i><?= $isEdit ? 'Editar Empresa' : 'Nueva Empresa' ?>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form id="companyForm" method="POST" action="<?= BASE_URL ?>settings.php?action=save" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="id" id="companyId" value="<?= $isEdit ? (int)$company['id'] : '' ?>">
                        <input type="hidden" name="certificate_path_existing" id="certificatePathExisting" value="<?= htmlspecialchars(old('certificate_path_existing', $isEdit ? ($company['certificate_path'] ?? '') : '')) ?>">

                        <!-- Sección: Datos Fiscales -->
                        <h6 class="text-primary mb-3"><i class="bi bi-bank me-1"></i>Datos Fiscales</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">RUC <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= inputClass('ruc') ?>" name="ruc" id="ruc"
                                       value="<?= htmlspecialchars(old('ruc')) ?>" maxlength="13"
                                       placeholder="13 dígitos" required>
                                <?php if (hasError('ruc')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('ruc') ?></div>
                                <?php endif; ?>
                                <div class="form-text">RUC ecuatoriano: 13 dígitos con validación módulo 10.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Nombre Comercial <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= inputClass('commercial_name') ?>"
                                       name="commercial_name" id="commercial_name"
                                       value="<?= htmlspecialchars(old('commercial_name')) ?>" maxlength="150"
                                       placeholder="Nombre visible en factura" required>
                                <?php if (hasError('commercial_name')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('commercial_name') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= inputClass('business_name') ?>"
                                       name="business_name" id="business_name"
                                       value="<?= htmlspecialchars(old('business_name')) ?>" maxlength="150"
                                       placeholder="Nombre legal" required>
                                <?php if (hasError('business_name')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('business_name') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección Completa</label>
                                <input type="text" class="form-control" name="address" id="address"
                                       value="<?= htmlspecialchars(old('address')) ?>"
                                       placeholder="Calle, número, ciudad, provincia">
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input type="text" class="form-control <?= inputClass('phone') ?>"
                                       name="phone" id="phone"
                                       value="<?= htmlspecialchars(old('phone')) ?>" maxlength="20"
                                       placeholder="(02) 234-5678">
                                <?php if (hasError('phone')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('phone') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control <?= inputClass('email') ?>"
                                       name="email" id="email"
                                       value="<?= htmlspecialchars(old('email')) ?>" maxlength="100"
                                       placeholder="empresa@ejemplo.com">
                                <?php if (hasError('email')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('email') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label">Contribuyente Especial</label>
                                <select class="form-select" name="special_contributor" id="special_contributor">
                                    <option value="0" <?= old('special_contributor', '0') == '0' ? 'selected' : '' ?>>No</option>
                                    <option value="1" <?= old('special_contributor') == '1' ? 'selected' : '' ?>>Sí</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- Sección: Configuración SRI -->
                        <h6 class="text-primary mb-3"><i class="bi bi-gear me-1"></i>Configuración SRI</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label fw-bold">Ambiente</label>
                                <select class="form-select <?= inputClass('ambiente') ?>" name="ambiente" id="ambiente" onchange="updateFormTheme()">
                                    <option value="pruebas" <?= old('ambiente', $isEdit ? ($company['ambiente'] ?? 'pruebas') : 'pruebas') === 'pruebas' ? 'selected' : '' ?>>🧪 Pruebas (CEL)</option>
                                    <option value="produccion" <?= old('ambiente') === 'produccion' ? 'selected' : '' ?>>🌐 Producción</option>
                                </select>
                                <?php if (hasError('ambiente')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('ambiente') ?></div>
                                <?php endif; ?>
                                <div class="form-text">Pruebas: ambiente de desarrollo SRI</div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label">Cód. Establecimiento</label>
                                <input type="text" class="form-control <?= inputClass('establishment_code') ?>"
                                       name="establishment_code" id="establishment_code"
                                       value="<?= htmlspecialchars(old('establishment_code', '001')) ?>" maxlength="3"
                                       placeholder="001">
                                <?php if (hasError('establishment_code')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('establishment_code') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label">Cód. Punto Emisión</label>
                                <input type="text" class="form-control <?= inputClass('emission_point') ?>"
                                       name="emission_point" id="emission_point"
                                       value="<?= htmlspecialchars(old('emission_point', '001')) ?>" maxlength="3"
                                       placeholder="001">
                                <?php if (hasError('emission_point')): ?>
                                    <div class="invalid-feedback d-block"><?= errorFor('emission_point') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label">Obligado Contabilidad</label>
                                <select class="form-select" name="accountant" id="accountant">
                                    <option value="1" <?= old('accountant', '1') == '1' ? 'selected' : '' ?>>Sí</option>
                                    <option value="0" <?= old('accountant') == '0' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4">
                                <label class="form-label fw-bold">Estado</label>
                                <select class="form-select" name="activo" id="activo">
                                    <option value="1" <?= old('activo', '1') == '1' ? 'selected' : '' ?>>Activa</option>
                                    <option value="0" <?= old('activo') == '0' ? 'selected' : '' ?>>Inactiva</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        <!-- Sección: Certificado Digital -->
                        <h6 class="text-primary mb-3"><i class="bi bi-shield-lock me-1"></i>Certificado Digital (.p12)</h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <?php
                                $certPath = old('certificate_path_existing', $isEdit ? ($company['certificate_path'] ?? '') : '');
                                $hasCert = !empty($certPath) && file_exists($certPath);
                                ?>
                                <div id="certUploadSection" <?= $hasCert ? 'style="display:none;"' : '' ?>>
                                    <div class="alert alert-info py-2 mb-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <strong>Para producción</strong> se requiere certificado digital .p12 o .pfx emitido por el SRI.
                                        <strong>Para pruebas</strong> no se necesita.
                                    </div>
                                    <label class="form-label fw-bold">Subir Certificado</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" name="certificate" id="certificateFile" accept=".p12,.pfx">
                                        <button type="button" class="btn btn-outline-primary" onclick="uploadCert()">
                                            <i class="bi bi-upload me-1"></i>Subir
                                        </button>
                                    </div>
                                    <small class="text-muted">Máximo 2MB. Solo .p12 o .pfx</small>
                                </div>
                                <div id="certInstalledSection" <?= $hasCert ? '' : 'style="display:none;"' ?>>
                                    <div class="alert alert-success py-2 mb-2">
                                        <i class="bi bi-check-circle-fill me-1"></i>
                                        <strong>Certificado instalado:</strong> <span id="certFilename"><?= $hasCert ? htmlspecialchars(basename($certPath)) : '' ?></span>
                                    </div>
                                    <?php if ($isEdit): ?>
                                        <a href="<?= BASE_URL ?>settings.php?action=deleteCert&id=<?= (int)$company['id'] ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('¿Eliminar este certificado?')">
                                            <i class="bi bi-trash me-1"></i>Eliminar Certificado
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="testSriConnection(<?= (int)$company['id'] ?>)">
                                            <i class="bi bi-wifi me-1"></i>Probar conexión SRI
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>settings.php" class="btn btn-secondary">
                                <i class="bi bi-x-lg me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Actualizar Empresa' : 'Guardar Empresa' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function updateFormTheme() {
    const ambiente = document.getElementById('ambiente').value;
    const header = document.getElementById('formHeader');
    const title = document.getElementById('formTitle');

    if (ambiente === 'produccion') {
        header.className = 'card-header bg-danger text-white';
        title.innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>MODO PRODUCCIÓN';
    } else {
        header.className = 'card-header bg-primary text-white';
        title.innerHTML = '<i class="bi bi-building me-2"></i><?= $isEdit ? 'Editar Empresa' : 'Nueva Empresa' ?>';
    }
}

function showCertSection(mode) {
    const upload = document.getElementById('certUploadSection');
    const installed = document.getElementById('certInstalledSection');
    if (mode === 'installed') {
        upload.style.display = 'none';
        installed.style.display = '';
    } else {
        upload.style.display = '';
        installed.style.display = 'none';
    }
}

function uploadCert() {
    const companyId = document.getElementById('companyId').value;
    if (!companyId) {
        Swal.fire('Error', 'Primero guarde la empresa antes de subir un certificado.', 'warning');
        return;
    }

    const fileInput = document.getElementById('certificateFile');
    if (fileInput.files.length === 0) {
        Swal.fire('Error', 'Seleccione un archivo .p12 o .pfx', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?= generateCSRFToken() ?>');
    formData.append('company_id', companyId);
    formData.append('certificate', fileInput.files[0]);

    Swal.fire({
        title: 'Subiendo certificado...',
        html: 'Por favor espere mientras se procesa el archivo.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('<?= BASE_URL ?>settings.php?action=uploadCert', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(() => {
        location.reload();
    })
    .catch(err => {
        Swal.fire('Error', err.message, 'error');
    });
}

function testSriConnection(companyId) {
    Swal.fire({
        title: 'Probando conexión SRI...',
        html: 'Conectando con el servicio web del SRI',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('<?= BASE_URL ?>settings.php?action=testConnection', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `company_id=${companyId}&csrf_token=<?= generateCSRFToken() ?>`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Conexión Exitosa',
                html: `<strong>${data.message}</strong><br><small>WSDL: <code>${data.wsdl}</code></small>`,
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: data.error,
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(err => {
        Swal.fire('Error', 'No se pudo conectar: ' + err.message, 'error');
    });
}

document.getElementById('ruc').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 13);
});

document.getElementById('establishment_code').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 3);
});

document.getElementById('emission_point').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9]/g, '').substring(0, 3);
});

document.getElementById('phone').addEventListener('input', function() {
    this.value = this.value.replace(/[^0-9\s\-\(\)\+]/g, '').substring(0, 20);
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
