<?php
/**
 * Location: vetapp/public/settings.php
 *
 * Router para gestión de empresas/RUCs.
 */

session_start();
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/repositories/CompanySettingRepository.php';
require_once __DIR__ . '/../app/models/CompanySettingModel.php';
require_once __DIR__ . '/../app/helpers/csrf.php';
require_once __DIR__ . '/../app/helpers/sanitize.php';
require_once __DIR__ . '/../app/helpers/validation.php';

$repo = new CompanySettingRepository();
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'index':
        $companies = $repo->getAll();
        require_once __DIR__ . '/../app/views/settings/companies.php';
        break;

    case 'create':
        $company = null;
        $errors = [];
        require_once __DIR__ . '/../app/views/settings/companies_form.php';
        break;

    case 'edit':
        $id = (int)($_GET['id'] ?? 0);
        $company = $repo->findById($id);
        if (!$company) {
            $_SESSION['error'] = 'Empresa no encontrada.';
            header('Location: ' . BASE_URL . 'settings.php');
            exit;
        }
        $errors = [];
        require_once __DIR__ . '/../app/views/settings/companies_form.php';
        break;

    case 'save':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die('Error CSRF');
            }

            $errors = [];
            $id = $_POST['id'] ?? null;
            $rucRaw = trim($_POST['ruc'] ?? '');

            $rucValidation = validateRUC($rucRaw);
            if (!$rucValidation['valid']) {
                $errors['ruc'] = $rucValidation['message'];
            }

            $nameValidation = validateRequired($_POST['commercial_name'] ?? '', 'Nombre Comercial');
            if (!$nameValidation['valid']) {
                $errors['commercial_name'] = $nameValidation['message'];
            } else {
                $lenValidation = validateMaxLength($_POST['commercial_name'], 150, 'Nombre Comercial');
                if (!$lenValidation['valid']) {
                    $errors['commercial_name'] = $lenValidation['message'];
                }
            }

            $bizValidation = validateRequired($_POST['business_name'] ?? '', 'Razón Social');
            if (!$bizValidation['valid']) {
                $errors['business_name'] = $bizValidation['message'];
            } else {
                $lenValidation = validateMaxLength($_POST['business_name'], 150, 'Razón Social');
                if (!$lenValidation['valid']) {
                    $errors['business_name'] = $lenValidation['message'];
                }
            }

            $emailValidation = validateEmail($_POST['email'] ?? '');
            if (!$emailValidation['valid']) {
                $errors['email'] = $emailValidation['message'];
            }

            $phoneValidation = validatePhone($_POST['phone'] ?? '');
            if (!$phoneValidation['valid']) {
                $errors['phone'] = $phoneValidation['message'];
            }

            $estValidation = validateNumericCode($_POST['establishment_code'] ?? '', 3, 'Cód. Establecimiento');
            if (!$estValidation['valid']) {
                $errors['establishment_code'] = $estValidation['message'];
            }

            $emitValidation = validateNumericCode($_POST['emission_point'] ?? '', 3, 'Cód. Punto Emisión');
            if (!$emitValidation['valid']) {
                $errors['emission_point'] = $emitValidation['message'];
            }

            $ambValidation = validateAmbiente($_POST['ambiente'] ?? '');
            if (!$ambValidation['valid']) {
                $errors['ambiente'] = $ambValidation['message'];
            }

            if (!empty($errors)) {
                $_SESSION['error'] = 'Por favor corrija los errores en el formulario.';
                $_SESSION['errors'] = $errors;
                $_SESSION['old_input'] = $_POST;
                $redirect = $id ? 'settings.php?action=edit&id=' . $id : 'settings.php?action=create';
                header('Location: ' . BASE_URL . $redirect);
                exit;
            }

            $existing = $repo->findByRuc(sanitizeInput($rucRaw));
            if ($existing && (!$id || $existing['id'] != $id)) {
                $_SESSION['error'] = 'Ya existe una empresa con ese RUC.';
                $_SESSION['old_input'] = $_POST;
                $redirect = $id ? 'settings.php?action=edit&id=' . $id : 'settings.php?action=create';
                header('Location: ' . BASE_URL . $redirect);
                exit;
            }

            $company = new CompanySettingModel([
                'ruc' => sanitizeInput($rucRaw),
                'commercial_name' => sanitizeInput($_POST['commercial_name'] ?? ''),
                'business_name' => sanitizeInput($_POST['business_name'] ?? ''),
                'address' => sanitizeInput($_POST['address'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'accountant' => (int)($_POST['accountant'] ?? 1),
                'special_contributor' => (int)($_POST['special_contributor'] ?? 0),
                'establishment_code' => sanitizeInput($_POST['establishment_code'] ?? '001'),
                'emission_point' => sanitizeInput($_POST['emission_point'] ?? '001'),
                'certificate_path' => $_POST['certificate_path_existing'] ?? '',
                'ambiente' => sanitizeInput($_POST['ambiente'] ?? 'pruebas'),
                'activo' => (int)($_POST['activo'] ?? 1),
            ]);

            if ($id) {
                $company->setId($id);
                if ($repo->update($company)) {
                    $_SESSION['success'] = 'Empresa actualizada correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al actualizar la empresa.';
                    $_SESSION['old_input'] = $_POST;
                }
            } else {
                if ($repo->create($company)) {
                    $_SESSION['success'] = 'Empresa creada correctamente.';
                } else {
                    $_SESSION['error'] = 'Error al crear la empresa.';
                    $_SESSION['old_input'] = $_POST;
                }
            }
        }
        header('Location: ' . BASE_URL . 'settings.php');
        exit;

    case 'uploadCert':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificate'])) {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die('Error CSRF');
            }

            $companyId = (int)($_POST['company_id'] ?? 0);
            if (!$companyId) {
                $_SESSION['error'] = 'Empresa no válida.';
                header('Location: ' . BASE_URL . 'settings.php');
                exit;
            }

            $result = $repo->uploadCertificate($companyId, $_FILES['certificate']);

            if ($result['success']) {
                $existing = $repo->findById($companyId);
                if (!empty($existing['certificate_path']) && file_exists($existing['certificate_path'])) {
                    unlink($existing['certificate_path']);
                }
                $repo->updateCertificatePath($companyId, $result['path']);
                $_SESSION['success'] = 'Certificado subido correctamente.';
            } else {
                $_SESSION['error'] = $result['error'];
            }
        }
        header('Location: ' . BASE_URL . 'settings.php');
        exit;

    case 'deleteCert':
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $repo->deleteCertificate($id);
            $_SESSION['success'] = 'Certificado eliminado.';
        }
        header('Location: ' . BASE_URL . 'settings.php');
        exit;

    case 'testConnection':
        header('Content-Type: application/json');
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Error CSRF']);
            exit;
        }
        $companyId = (int)($_POST['company_id'] ?? 0);
        if (!$companyId) {
            echo json_encode(['success' => false, 'error' => 'Empresa no válida']);
            exit;
        }
        $company = $repo->findById($companyId);
        if (!$company) {
            echo json_encode(['success' => false, 'error' => 'Empresa no encontrada']);
            exit;
        }
        echo json_encode($repo->testSriConnection($company['ambiente']));
        exit;

    case 'deactivate':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $repo->deactivate($id);
            $_SESSION['success'] = 'Empresa desactivada.';
        }
        header('Location: ' . BASE_URL . 'settings.php');
        exit;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $repo->softDelete($id);
            $_SESSION['success'] = 'Empresa eliminada correctamente.';
        }
        header('Location: ' . BASE_URL . 'settings.php');
        exit;

    case 'restore':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $repo->restore($id);
            $_SESSION['success'] = 'Empresa restaurada correctamente.';
        }
        header('Location: ' . BASE_URL . 'settings.php');
        exit;

    case 'deleted':
        $companies = $repo->getDeleted();
        require_once __DIR__ . '/../app/views/settings/deleted_companies.php';
        break;

    case 'whatsapp':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die('Error CSRF');
            }

            $whatsapps = $_POST['whatsapp'] ?? [];
            $count = 0;
            foreach ($whatsapps as $id => $phone) {
                $phone = preg_replace('/\D/', '', trim($phone));
                if ($repo->updateWhatsappPhone((int)$id, $phone)) {
                    $count++;
                }
            }

            $_SESSION['success'] = "$count número(s) de WhatsApp actualizado(s) correctamente.";
            header('Location: ' . BASE_URL . 'settings.php?action=whatsapp');
            exit;
        }

        $companies = $repo->getActivas();
        require_once __DIR__ . '/../app/views/settings/whatsapp.php';
        break;

    case 'personalization':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                http_response_code(403);
                die('Error CSRF');
            }

            // Update titles
            $titles = $_POST['title'] ?? [];
            foreach ($titles as $id => $title) {
                $title = trim(sanitizeInput($title));
                $repo->updateAppearance((int)$id, $title);
            }

            // Upload logos
            if (!empty($_FILES['logo'])) {
                foreach ($_FILES['logo']['name'] as $id => $name) {
                    if (empty($name)) continue;
                    $file = [
                        'name' => $_FILES['logo']['name'][$id],
                        'type' => $_FILES['logo']['type'][$id],
                        'tmp_name' => $_FILES['logo']['tmp_name'][$id],
                        'error' => $_FILES['logo']['error'][$id],
                        'size' => $_FILES['logo']['size'][$id],
                    ];
                    $repo->uploadLogo((int)$id, $file);
                }
            }

            // Handle logo removals
            $removals = $_POST['remove_logo'] ?? [];
            foreach ($removals as $id => $val) {
                if ($val) {
                    $company = $repo->findById((int)$id);
                    if ($company && !empty($company['logo_path']) && file_exists($company['logo_path'])) {
                        unlink($company['logo_path']);
                    }
                    $repo->updateLogoPath((int)$id, null);
                }
            }

            $_SESSION['success'] = 'Personalización actualizada correctamente.';
            header('Location: ' . BASE_URL . 'settings.php?action=personalization');
            exit;
        }

        $companies = $repo->getActivas();
        require_once __DIR__ . '/../app/views/settings/personalization.php';
        break;

    default:
        header('Location: ' . BASE_URL . 'settings.php');
        exit;
}
