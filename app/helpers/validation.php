<?php
/**
 * Location: vetapp/app/helpers/validation.php
 *
 * Validaciones para datos de entrada, incluyendo RUC ecuatoriano.
 */

function validateRUC($ruc) {
    $ruc = preg_replace('/[^0-9]/', '', $ruc);

    if (strlen($ruc) !== 13) {
        return ['valid' => false, 'message' => 'El RUC debe tener exactamente 13 dígitos.'];
    }

    if (!preg_match('/^[0-9]+$/', $ruc)) {
        return ['valid' => false, 'message' => 'El RUC solo debe contener números.'];
    }

    $province = (int)substr($ruc, 0, 2);
    if ($province < 1 || $province > 24) {
        return ['valid' => false, 'message' => 'Los dos primeros dígitos del RUC deben corresponder a una provincia válida (01-24).'];
    }

    $thirdDigit = (int)$ruc[2];
    if ($thirdDigit < 1 || $thirdDigit > 6) {
        return ['valid' => false, 'message' => 'El tercer dígito del RUC no es válido.'];
    }

    $coefficient = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    $sum = 0;
    for ($i = 0; $i < 9; $i++) {
        $value = (int)$ruc[$i] * $coefficient[$i];
        $sum += ($value >= 10) ? ($value - 9) : $value;
    }

    $checkDigit = (10 - ($sum % 10)) % 10;
    if ((int)$ruc[9] !== $checkDigit) {
        return ['valid' => false, 'message' => 'El RUC no pasa la validación de dígito verificador (módulo 10).'];
    }

    $establishment = substr($ruc, 10, 3);
    if ((int)$establishment < 1) {
        return ['valid' => false, 'message' => 'El código de establecimiento del RUC no es válido.'];
    }

    return ['valid' => true, 'message' => 'RUC válido.'];
}

function validateRequired($value, $fieldName) {
    $value = trim($value);
    if (empty($value)) {
        return ['valid' => false, 'message' => "El campo {$fieldName} es obligatorio."];
    }
    return ['valid' => true, 'message' => ''];
}

function validateMaxLength($value, $max, $fieldName) {
    if (strlen($value) > $max) {
        return ['valid' => false, 'message' => "El campo {$fieldName} no debe exceder los {$max} caracteres."];
    }
    return ['valid' => true, 'message' => ''];
}

function validateEmail($email) {
    if (empty($email)) {
        return ['valid' => true, 'message' => ''];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'message' => 'El email no tiene un formato válido.'];
    }
    return ['valid' => true, 'message' => ''];
}

function validatePhone($phone) {
    if (empty($phone)) {
        return ['valid' => true, 'message' => ''];
    }
    if (!preg_match('/^[0-9\s\-\(\)\+]+$/', $phone)) {
        return ['valid' => false, 'message' => 'El teléfono solo puede contener números, espacios, guiones, paréntesis y +.'];
    }
    return ['valid' => true, 'message' => ''];
}

function validateNumericCode($value, $length, $fieldName) {
    $value = trim($value);
    if (strlen($value) !== $length || !ctype_digit($value)) {
        return ['valid' => false, 'message' => "El campo {$fieldName} debe tener exactamente {$length} dígitos numéricos."];
    }
    return ['valid' => true, 'message' => ''];
}

function validateAmbiente($ambiente) {
    if (!in_array($ambiente, ['pruebas', 'produccion'], true)) {
        return ['valid' => false, 'message' => 'El ambiente debe ser "pruebas" o "produccion".'];
    }
    return ['valid' => true, 'message' => ''];
}
