<?php
/**
 * Location: vetapp/app/helpers/sri_helpers.php
 *
 * Funciones auxiliares para facturación electrónica SRI Ecuador.
 * Incluye: generación de clave de acceso, número de factura,
 * dígito verificador módulo 11.
 */

/**
 * Genera el número de factura en formato SRI.
 * Formato: EEE-PPP-SSSSSSSSS (3-3-9 = 17 caracteres)
 *
 * @param string $establecimiento Código del establecimiento (001)
 * @param string $puntoEmision Código del punto de emisión (001)
 * @param int $secuencial Número secuencial de la factura
 * @return string Número de factura formateado
 */
function generarNumeroFacturaSRI($establecimiento, $puntoEmision, $secuencial) {
    return sprintf('%s-%s-%09d', $establecimiento, $puntoEmision, $secuencial);
}

/**
 * Obtiene el siguiente secuencial de factura desde la BD.
 *
 * @param PDO $db Conexión a base de datos
 * @param int $companyId ID de la empresa
 * @return int Siguiente secuencial
 */
function getSiguienteSecuencialSRI($db, $companyId) {
    $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(numero_factura, 9) AS UNSIGNED)), 0) + 1 as next_seq
            FROM electronic_invoices
            WHERE company_id = :company_id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':company_id' => $companyId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return (int)($row['next_seq'] ?? 1);
}

/**
 * Genera la clave de acceso del SRI (49 dígitos).
 *
 * Formato: DDMMAAAA + tipoComprobante + ruc + ambiente + serie +
 *          secuencial + codigoNumerico + tipoEmision + digitoVerificador
 *
 * @param string $fecha Fecha de emisión (YYYY-MM-DD)
 * @param string $ruc RUC del emisor
 * @param string $establecimiento Código establecimiento (001)
 * @param string $puntoEmision Código punto emisión (001)
 * @param int $secuencial Número secuencial
 * @param string $codigoNumerico Código numérico aleatorio (8 dígitos)
 * @param string $ambiente 'pruebas' o 'produccion'
 * @return string Clave de acceso de 49 dígitos
 */
function generarClaveAccesoSRI($fecha, $ruc, $establecimiento, $puntoEmision, $secuencial, $codigoNumerico = '', $ambiente = 'pruebas') {
    // Fecha en formato DDMMYYYY
    $fechaFormateada = date('dmY', strtotime($fecha));

    // Tipo de comprobante: 01 = Factura
    $tipoComprobante = '01';

    // Ambiente: 1 = pruebas, 2 = producción
    $ambienteCodigo = ($ambiente === 'pruebas') ? '1' : '2';

    // Serie del comprobante
    $serie = $establecimiento . $puntoEmision;

    // Secuencial de 9 dígitos
    $secuencialFormateado = sprintf('%09d', $secuencial);

    // Código numérico aleatorio de 8 dígitos
    if (empty($codigoNumerico)) {
        $codigoNumerico = sprintf('%08d', rand(10000000, 99999999));
    }

    // Tipo de emisión: 1 = normal
    $tipoEmision = '1';

    // Construir los primeros 48 dígitos
    $claveSinVerificador =
        $fechaFormateada .
        $tipoComprobante .
        $ruc .
        $ambienteCodigo .
        $serie .
        $secuencialFormateado .
        $codigoNumerico .
        $tipoEmision;

    // Calcular dígito verificador
    $digitoVerificador = calcularDigitoVerificadorSRI($claveSinVerificador);

    return $claveSinVerificador . $digitoVerificador;
}

/**
 * Calcula el dígito verificador usando el algoritmo Módulo 11.
 * Requerido por el SRI para validar la clave de acceso.
 *
 * @param string $clave Los primeros 48 dígitos de la clave
 * @return int Dígito verificador (0-9)
 */
function calcularDigitoVerificadorSRI($clave) {
    $suma = 0;
    $longitud = strlen($clave);

    // Factores cíclicos: 2,3,4,5,6,7
    $factores = [2, 3, 4, 5, 6, 7];

    for ($i = 0; $i < $longitud; $i++) {
        $posicion = $longitud - $i;
        $factor = $factores[$i % 6];
        $suma += intval($clave[$posicion - 1]) * $factor;
    }

    $residuo = $suma % 11;
    $resultado = 11 - $residuo;

    if ($resultado >= 10) {
        return 0;
    }

    return $resultado;
}

/**
 * Convierte forma de pago al código SRI.
 *
 * @param string $paymentMethod cash, card, transfer, credit
 * @return string Código SRI
 */
function getFormaPagoSRI($paymentMethod) {
    $mapa = [
        'cash' => '01',
        'card' => '16',
        'transfer' => '47',
        'credit' => '02',
    ];
    return $mapa[$paymentMethod] ?? '20';
}

/**
 * Convierte tipo de identificación al código SRI.
 *
 * @param string $tipo RUC, Cédula, Pasaporte, Consumidor Final
 * @return string Código SRI
 */
function getTipoIdentificacionSRI($tipo) {
    $mapa = [
        'RUC' => '04',
        'Cédula' => '05',
        'Cedula' => '05',
        'Pasaporte' => '06',
        'Consumidor Final' => '07',
    ];
    return $mapa[$tipo] ?? '07';
}
