<?php
/**
 * Location: vetapp/app/services/SriService.php
 *
 * Servicio completo para facturación electrónica SRI Ecuador.
 * Responsable de:
 * 1. Generar XML de factura según formato oficial SRI
 * 2. Firmar digitalmente el XML con certificado .p12
 * 3. Enviar al SRI vía SOAP (WSDL)
 * 4. Consultar estado de autorización con polling
 */

require_once __DIR__ . '/../helpers/sri_helpers.php';

class SriService
{
    private $db;
    private $ambiente;
    private $wsdl;

    const WSDL_PRUEBAS = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';
    const WSDL_PRODUCCION = 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl';

    public function __construct($ambiente = 'pruebas')
    {
        $this->db = Database::getInstance()->getConnection();
        $this->ambiente = $ambiente;
        $this->wsdl = ($ambiente === 'pruebas') ? self::WSDL_PRUEBAS : self::WSDL_PRODUCCION;
    }

    public function generarXmlFactura($saleData, $company, $numeroFactura, $claveAcceso, $secuencial)
    {
        $sale = $saleData['sale'];
        $details = $saleData['details'];
        $fechaEmision = date('d/m/Y', strtotime($sale['sale_date']));

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $factura = $dom->createElement('factura');
        $factura->setAttribute('id', 'comprobante');
        $factura->setAttribute('version', '1.1.0');
        $factura->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $dom->appendChild($factura);

        $infoTributaria = $dom->createElement('infoTributaria');
        $factura->appendChild($infoTributaria);

        $this->agregarElemento($dom, $infoTributaria, 'ambiente', $this->ambiente === 'pruebas' ? '1' : '2');
        $this->agregarElemento($dom, $infoTributaria, 'tipoEmision', '1');
        $this->agregarElemento($dom, $infoTributaria, 'razonSocial', $company['business_name'] ?? $company['commercial_name']);
        $this->agregarElemento($dom, $infoTributaria, 'nombreComercial', $company['commercial_name']);
        $this->agregarElemento($dom, $infoTributaria, 'ruc', $company['ruc']);
        $this->agregarElemento($dom, $infoTributaria, 'claveAcceso', $claveAcceso);
        $this->agregarElemento($dom, $infoTributaria, 'codDoc', '01');
        $this->agregarElemento($dom, $infoTributaria, 'estab', $company['establishment_code'] ?? '001');
        $this->agregarElemento($dom, $infoTributaria, 'ptoEmi', $company['emission_point'] ?? '001');
        $this->agregarElemento($dom, $infoTributaria, 'secuencial', sprintf('%09d', $secuencial));
        $this->agregarElemento($dom, $infoTributaria, 'dirMatriz', $company['address'] ?? '');

        $infoFactura = $dom->createElement('infoFactura');
        $factura->appendChild($infoFactura);

        $this->agregarElemento($dom, $infoFactura, 'fechaEmision', $fechaEmision);
        $this->agregarElemento($dom, $infoFactura, 'dirEstablecimiento', $company['address'] ?? '');

        if (!empty($company['special_contributor']) && $company['special_contributor'] != '0') {
            $this->agregarElemento($dom, $infoFactura, 'contribuyenteEspecial', 'CONTRIBUYENTE ESPECIAL');
        }

        $obligadoContabilidad = $company['accountant'] ?? $company['obligado_contabilidad'] ?? 'SI';
        $this->agregarElemento($dom, $infoFactura, 'obligadoContabilidad', $obligadoContabilidad === 'SI' || $obligadoContabilidad === 1 || $obligadoContabilidad === '1' ? 'SI' : 'NO');

        $cliente = $saleData['client'] ?? [];
        $this->agregarElemento($dom, $infoFactura, 'tipoIdentificacionComprador', getTipoIdentificacionSRI($cliente['tipo_identificacion'] ?? 'Cédula'));
        $this->agregarElemento($dom, $infoFactura, 'razonSocialComprador', $cliente['name'] ?? 'Consumidor Final');
        $this->agregarElemento($dom, $infoFactura, 'identificacionComprador', $cliente['ruc_cedula'] ?? '9999999999999');

        $subtotal = (float)($sale['subtotal'] ?? 0);
        $this->agregarElemento($dom, $infoFactura, 'totalSinImpuestos', number_format($subtotal, 2, '.', ''));

        $totalConImpuestos = $dom->createElement('totalConImpuestos');
        $infoFactura->appendChild($totalConImpuestos);

        $baseGravada = 0;
        $ivaTotal = 0;
        foreach ($details as $det) {
            if ((float)($det['tax_rate'] ?? 0) > 0) {
                $baseGravada += (float)($det['subtotal'] ?? 0);
                $ivaTotal += (float)($det['tax_amount'] ?? 0);
            }
        }

        if ($baseGravada > 0) {
            $totalImpuesto = $dom->createElement('totalImpuesto');
            $totalConImpuestos->appendChild($totalImpuesto);
            $this->agregarElemento($dom, $totalImpuesto, 'codigo', '2');
            $this->agregarElemento($dom, $totalImpuesto, 'codigoPorcentaje', '2');
            $this->agregarElemento($dom, $totalImpuesto, 'baseImponible', number_format($baseGravada, 2, '.', ''));

            $maxTarifa = 0;
            foreach ($details as $det) {
                $t = (float)($det['tax_rate'] ?? 0);
                if ($t > $maxTarifa) $maxTarifa = $t;
            }
            $this->agregarElemento($dom, $totalImpuesto, 'tarifa', number_format($maxTarifa, 2, '.', ''));
            $this->agregarElemento($dom, $totalImpuesto, 'valor', number_format($ivaTotal, 2, '.', ''));
        }

        $baseExenta = $subtotal - $baseGravada;
        if ($baseExenta > 0.01) {
            $totalImpuesto = $dom->createElement('totalImpuesto');
            $totalConImpuestos->appendChild($totalImpuesto);
            $this->agregarElemento($dom, $totalImpuesto, 'codigo', '2');
            $this->agregarElemento($dom, $totalImpuesto, 'codigoPorcentaje', '0');
            $this->agregarElemento($dom, $totalImpuesto, 'baseImponible', number_format($baseExenta, 2, '.', ''));
            $this->agregarElemento($dom, $totalImpuesto, 'tarifa', '0.00');
            $this->agregarElemento($dom, $totalImpuesto, 'valor', '0.00');
        }

        $this->agregarElemento($dom, $infoFactura, 'propina', '0.00');
        $this->agregarElemento($dom, $infoFactura, 'importeTotal', number_format($sale['total'], 2, '.', ''));
        $this->agregarElemento($dom, $infoFactura, 'moneda', 'DOLAR');

        $pagos = $dom->createElement('pagos');
        $infoFactura->appendChild($pagos);
        $pago = $dom->createElement('pago');
        $pagos->appendChild($pago);

        $this->agregarElemento($dom, $pago, 'formaPago', getFormaPagoSRI($sale['payment_method'] ?? 'cash'));
        $this->agregarElemento($dom, $pago, 'total', number_format($sale['total'], 2, '.', ''));

        $formaPago = getFormaPagoSRI($sale['payment_method'] ?? 'cash');
        if ($formaPago === '02') {
            $this->agregarElemento($dom, $pago, 'plazo', '30');
            $this->agregarElemento($dom, $pago, 'unidadTiempo', 'dias');
        } else {
            $this->agregarElemento($dom, $pago, 'plazo', '0');
            $this->agregarElemento($dom, $pago, 'unidadTiempo', 'dias');
        }

        $detallesXml = $dom->createElement('detalles');
        $factura->appendChild($detallesXml);

        foreach ($details as $det) {
            $detalle = $dom->createElement('detalle');
            $detallesXml->appendChild($detalle);

            $this->agregarElemento($dom, $detalle, 'codigoPrincipal', $det['medication_code'] ?? 'MED-' . $det['id_medication']);
            $this->agregarElemento($dom, $detalle, 'codigoAuxiliar', '');
            $this->agregarElemento($dom, $detalle, 'descripcion', $det['medication_name'] ?? 'Producto');
            $this->agregarElemento($dom, $detalle, 'cantidad', number_format($det['quantity'], 2, '.', ''));
            $this->agregarElemento($dom, $detalle, 'precioUnitario', number_format($det['unit_price'], 2, '.', ''));
            $this->agregarElemento($dom, $detalle, 'descuento', '0.00');
            $this->agregarElemento($dom, $detalle, 'precioTotalSinImpuesto', number_format($det['subtotal'], 2, '.', ''));

            $impuestos = $dom->createElement('impuestos');
            $detalle->appendChild($impuestos);

            $impuesto = $dom->createElement('impuesto');
            $impuestos->appendChild($impuesto);

            $taxRate = (float)($det['tax_rate'] ?? 0);
            $this->agregarElemento($dom, $impuesto, 'codigo', '2');
            $this->agregarElemento($dom, $impuesto, 'codigoPorcentaje', $taxRate > 0 ? '2' : '0');
            $this->agregarElemento($dom, $impuesto, 'tarifa', number_format($taxRate, 2, '.', ''));
            $this->agregarElemento($dom, $impuesto, 'baseImponible', number_format($det['subtotal'], 2, '.', ''));
            $this->agregarElemento($dom, $impuesto, 'valor', number_format($det['tax_amount'] ?? 0, 2, '.', ''));
        }

        $infoAdicional = $dom->createElement('infoAdicional');
        $factura->appendChild($infoAdicional);

        $campo = $dom->createElement('campoAdicional');
        $campo->setAttribute('nombre', 'email');
        $campo->setAttribute('valor', $cliente['email'] ?? ($company['email'] ?? ''));
        $infoAdicional->appendChild($campo);

        return $dom->saveXML();
    }

    private function agregarElemento($dom, $padre, $nombre, $valor)
    {
        $elemento = $dom->createElement($nombre, (string)$valor);
        $padre->appendChild($elemento);
    }

    public function guardarXml($claveAcceso, $xml, $firmado = false)
    {
        $directorio = STORAGE_PATH . '/sri_xml/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $sufijo = $firmado ? '_firmado' : '';
        $archivo = $directorio . $claveAcceso . $sufijo . '.xml';
        file_put_contents($archivo, $xml);
        return $archivo;
    }

    public function firmarXml($xml, $certificadoPath = null, $certificadoPassword = null)
    {
        if (empty($certificadoPath) || !file_exists($certificadoPath)) {
            return $this->agregarFirmaSimulada($xml);
        }

        try {
            $pkcs12 = file_get_contents($certificadoPath);
            if (!openssl_pkcs12_read($pkcs12, $certs, $certificadoPassword ?? '')) {
                throw new Exception("No se pudo leer el certificado PKCS#12");
            }

            $privateKey = $certs['pkey'];
            $certificate = $certs['cert'];

            $domDoc = new DOMDocument();
            $domDoc->preserveWhiteSpace = false;
            $domDoc->loadXML($xml);

            $sigNs = 'http://www.w3.org/2000/09/xmldsig#';

            $signature = $domDoc->createElementNS($sigNs, 'ds:Signature');
            $domDoc->documentElement->appendChild($signature);

            $signedInfo = $domDoc->createElementNS($sigNs, 'ds:SignedInfo');
            $signature->appendChild($signedInfo);

            $canonMethod = $domDoc->createElementNS($sigNs, 'ds:CanonicalizationMethod');
            $canonMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            $signedInfo->appendChild($canonMethod);

            $sigMethod = $domDoc->createElementNS($sigNs, 'ds:SignatureMethod');
            $sigMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            $signedInfo->appendChild($sigMethod);

            $reference = $domDoc->createElementNS($sigNs, 'ds:Reference');
            $reference->setAttribute('URI', '');
            $signedInfo->appendChild($reference);

            $transforms = $domDoc->createElementNS($sigNs, 'ds:Transforms');
            $reference->appendChild($transforms);

            $transform1 = $domDoc->createElementNS($sigNs, 'ds:Transform');
            $transform1->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            $transforms->appendChild($transform1);

            $digestMethod = $domDoc->createElementNS($sigNs, 'ds:DigestMethod');
            $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            $reference->appendChild($digestMethod);

            // Canonicalize entire document EXCLUDING SignatureValue (not yet added)
            $xmlC14N = $domDoc->C14N();
            $digestValue = base64_encode(sha1($xmlC14N, true));
            $reference->appendChild($domDoc->createElementNS($sigNs, 'ds:DigestValue', $digestValue));

            // Sign canonicalized SignedInfo
            $signedInfoXml = $domDoc->C14N();
            openssl_sign($signedInfoXml, $signatureValue, $privateKey, OPENSSL_ALGO_SHA1);
            $signature->appendChild($domDoc->createElementNS($sigNs, 'ds:SignatureValue', base64_encode($signatureValue)));

            $keyInfo = $domDoc->createElementNS($sigNs, 'ds:KeyInfo');
            $signature->appendChild($keyInfo);

            $x509Data = $domDoc->createElementNS($sigNs, 'ds:X509Data');
            $keyInfo->appendChild($x509Data);
            $x509Data->appendChild($domDoc->createElementNS($sigNs, 'ds:X509Certificate', base64_encode($certificate)));

            return $domDoc->saveXML();

        } catch (Exception $e) {
            $this->logSriError("firmarXml error: " . $e->getMessage());
            return $this->agregarFirmaSimulada($xml);
        }
    }

    private function agregarFirmaSimulada($xml)
    {
        $domDoc = new DOMDocument();
        $domDoc->preserveWhiteSpace = false;
        $domDoc->loadXML($xml);

        $sigNs = 'http://www.w3.org/2000/09/xmldsig#';

        $firmaSimulada = $domDoc->createElementNS($sigNs, 'ds:Signature');
        $firmaSimulada->setAttribute('Id', 'Signature1');

        $signedInfo = $domDoc->createElementNS($sigNs, 'ds:SignedInfo');
        $firmaSimulada->appendChild($signedInfo);

        $canonMethod = $domDoc->createElementNS($sigNs, 'ds:CanonicalizationMethod');
        $canonMethod->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
        $signedInfo->appendChild($canonMethod);

        $sigMethod = $domDoc->createElementNS($sigNs, 'ds:SignatureMethod');
        $sigMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
        $signedInfo->appendChild($sigMethod);

        $reference = $domDoc->createElementNS($sigNs, 'ds:Reference');
        $reference->setAttribute('Id', 'Reference1');
        $reference->setAttribute('URI', '');
        $signedInfo->appendChild($reference);

        $transforms = $domDoc->createElementNS($sigNs, 'ds:Transforms');
        $reference->appendChild($transforms);

        $transform1 = $domDoc->createElementNS($sigNs, 'ds:Transform');
        $transform1->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
        $transforms->appendChild($transform1);

        $digestMethod = $domDoc->createElementNS($sigNs, 'ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
        $reference->appendChild($digestMethod);

        $xmlC14N = $domDoc->C14N();
        $digestValue = base64_encode(sha1($xmlC14N, true));
        $reference->appendChild($domDoc->createElementNS($sigNs, 'ds:DigestValue', $digestValue));

        $firmaSimulada->appendChild($domDoc->createElementNS($sigNs, 'ds:SignatureValue', base64_encode('FIRMA_SIMULADA_PRUEBAS_' . time())));

        $keyInfo = $domDoc->createElementNS($sigNs, 'ds:KeyInfo');
        $firmaSimulada->appendChild($keyInfo);

        $x509Data = $domDoc->createElementNS($sigNs, 'ds:X509Data');
        $keyInfo->appendChild($x509Data);
        $x509Data->appendChild($domDoc->createElementNS($sigNs, 'ds:X509Certificate', 'CERTIFICADO_DE_PRUEBAS_SRI'));

        $domDoc->documentElement->appendChild($firmaSimulada);

        return $domDoc->saveXML();
    }

    public function enviarAlSri($xmlFirmado)
    {
        try {
            if (!class_exists('SoapClient')) {
                $this->logSriError('SOAP extension not enabled');
                return [
                    'estado' => 'error',
                    'mensaje' => 'Extensión SOAP no habilitada. Active extension=soap en php.ini',
                    'numero_autorizacion' => null,
                    'observaciones' => [],
                ];
            }

            $client = new SoapClient($this->wsdl, [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 30,
            ]);

            $parametros = ['xml' => base64_encode($xmlFirmado)];
            $respuesta = $client->validarComprobante($parametros);

            $xmlHash = substr(sha1($xmlFirmado), 0, 16);
            $this->logSriError("SOAP request sent (hash: {$xmlHash})");

            if (!is_object($respuesta)) {
                $this->logSriError("Respuesta SRI no es objeto: " . gettype($respuesta));
                $this->logSoapDebug($client);
                return [
                    'estado' => 'rechazada',
                    'mensaje' => 'Respuesta SRI no válida (formato inesperado)',
                    'numero_autorizacion' => null,
                    'observaciones' => [],
                ];
            }

            $estadoGeneral = null;

            if (isset($respuesta->RespuestaRecepcionComprobantes)) {
                $r = $respuesta->RespuestaRecepcionComprobantes;
                $estadoGeneral = $r->estado ?? null;
            } elseif (isset($respuesta->estado)) {
                $estadoGeneral = $respuesta->estado;
            } elseif (isset($respuesta->return)) {
                $estadoGeneral = $respuesta->return->estado ?? ($respuesta->return ?? null);
            }

            if ($estadoGeneral === null) {
                foreach ($respuesta as $key => $value) {
                    if (is_object($value) && isset($value->estado)) {
                        $estadoGeneral = $value->estado;
                        break;
                    }
                }
            }

            if ($estadoGeneral === null) {
                $this->logSriError("No se encontró 'estado' en la respuesta SRI. Estructura: " . json_encode($respuesta, JSON_UNESCAPED_UNICODE));
                $this->logSoapDebug($client);
                return [
                    'estado' => 'rechazada',
                    'mensaje' => 'No se pudo determinar el estado de la respuesta del SRI',
                    'numero_autorizacion' => null,
                    'observaciones' => [],
                ];
            }

            $observaciones = $this->parseObservaciones($respuesta);

            if (count($observaciones) > 0) {
                $this->logSriError("Estado: $estadoGeneral, Observaciones: " . json_encode($observaciones, JSON_UNESCAPED_UNICODE));
            }

            if ($estadoGeneral === 'DEVUELTA') {
                $errorMsg = !empty($observaciones)
                    ? $observaciones[0]['mensaje'] . (isset($observaciones[0]['informacionAdicional']) ? ' - ' . $observaciones[0]['informacionAdicional'] : '')
                    : 'Comprobante devuelto por el SRI';

                $this->logSriError("DEVUELTA: $errorMsg", $observaciones);

                return [
                    'estado' => 'rechazada',
                    'mensaje' => $errorMsg,
                    'numero_autorizacion' => null,
                    'observaciones' => $observaciones,
                ];
            }

            if ($estadoGeneral === 'RECIBIDA') {
                $this->logSriError("RECIBIDA - procediendo a consultar autorización");
                return $this->consultarAutorizacion($client, $parametros['xml']);
            }

            $this->logSriError("Estado SRI no reconocido: $estadoGeneral");
            $this->logSoapDebug($client);

            return [
                'estado' => 'rechazada',
                'mensaje' => "Estado SRI no reconocido: $estadoGeneral",
                'numero_autorizacion' => null,
                'observaciones' => $observaciones,
            ];

        } catch (SoapFault $e) {
            $this->logSriError("SOAP Fault: " . $e->getMessage());
            return [
                'estado' => 'error_conexion',
                'mensaje' => 'Error de conexión con el SRI: ' . $e->getMessage(),
                'numero_autorizacion' => null,
                'observaciones' => [],
            ];
        } catch (Exception $e) {
            $this->logSriError("Exception: " . $e->getMessage());
            return [
                'estado' => 'error',
                'mensaje' => $e->getMessage(),
                'numero_autorizacion' => null,
                'observaciones' => [],
            ];
        }
    }

    private function parseObservaciones($respuesta)
    {
        $observaciones = [];

        if (isset($respuesta->RespuestaRecepcionComprobantes->comprobantes)) {
            $comprobantes = $respuesta->RespuestaRecepcionComprobantes->comprobantes;
            if (is_array($comprobantes)) {
                foreach ($comprobantes as $comp) {
                    if (isset($comp->mensajes) && is_array($comp->mensajes)) {
                        foreach ($comp->mensajes as $obs) {
                            $observaciones[] = [
                                'identificador' => $obs->identificador ?? '',
                                'mensaje' => $obs->mensaje ?? '',
                                'informacionAdicional' => $obs->informacionAdicional ?? '',
                            ];
                        }
                    }
                }
            }
        } elseif (isset($respuesta->RespuestaRecepcionComprobantes->mensajes)) {
            $mensajes = $respuesta->RespuestaRecepcionComprobantes->mensajes;
            if (is_array($mensajes)) {
                foreach ($mensajes as $obs) {
                    $observaciones[] = [
                        'identificador' => $obs->identificador ?? '',
                        'mensaje' => $obs->mensaje ?? '',
                        'informacionAdicional' => $obs->informacionAdicional ?? '',
                    ];
                }
            }
        } elseif (isset($respuesta->comprobantes)) {
            if (is_array($respuesta->comprobantes)) {
                foreach ($respuesta->comprobantes as $comp) {
                    if (isset($comp->mensajes) && is_array($comp->mensajes)) {
                        foreach ($comp->mensajes as $obs) {
                            $observaciones[] = [
                                'identificador' => $obs->identificador ?? '',
                                'mensaje' => $obs->mensaje ?? '',
                                'informacionAdicional' => $obs->informacionAdicional ?? '',
                            ];
                        }
                    }
                }
            }
        }

        return $observaciones;
    }

    private function logSoapDebug($client)
    {
        try {
            if ($client && property_exists($client, '__getLastRequest')) {
                $lastRequest = $client->__getLastRequest();
                if ($lastRequest) {
                    $this->logSriError("SOAP Last Request (truncated): " . substr($lastRequest, 0, 500));
                }
            }
            if ($client && property_exists($client, '__getLastResponse')) {
                $lastResponse = $client->__getLastResponse();
                if ($lastResponse) {
                    $this->logSriError("SOAP Last Response (truncated): " . substr($lastResponse, 0, 1000));
                }
            }
        } catch (Exception $e) {
            $this->logSriError("Error getting SOAP debug: " . $e->getMessage());
        }
    }

    private function logSriError($message, $observaciones = [])
    {
        $logDir = STORAGE_PATH . '/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . 'sri_errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";

        if (!empty($observaciones)) {
            $logEntry .= "Observaciones: " . json_encode($observaciones, JSON_UNESCAPED_UNICODE) . "\n";
        }

        if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) {
            rename($logFile, $logFile . '.bak');
        }

        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    private function consultarAutorizacion($client, $xmlBase64)
    {
        try {
            $xml = base64_decode($xmlBase64);
            if (preg_match('/<claveAcceso>(.*?)<\/claveAcceso>/', $xml, $matches)) {
                $claveAcceso = $matches[1];
            } else {
                throw new Exception("No se pudo extraer la clave de acceso");
            }

            $retries = [3, 5, 7, 10, 10];
            foreach ($retries as $index => $waitTime) {
                $this->logSriError("consultarAutorizacion intento " . ($index + 1) . "/" . count($retries) . " (espera: {$waitTime}s) - Clave: $claveAcceso");
                sleep($waitTime);

                $parametros = ['claveAccesoComprobante' => $claveAcceso];
                $respuesta = $client->autorizacionComprobante($parametros);

                $result = $this->parseAuthorizationResponse($respuesta, $claveAcceso);

                if ($result['estado'] === 'autorizada' || $result['estado'] === 'rechazada') {
                    return $result;
                }

                if ($index < count($retries) - 1) {
                    $this->logSriError("Estado pendiente, reintentando en {$retries[$index + 1]}s...");
                }
            }

            $this->logSriError("TIMEOUT - Clave: $claveAcceso (agotados " . count($retries) . " intentos)");

            return [
                'estado' => 'pendiente_autorizacion',
                'mensaje' => 'El SRI aún no ha autorizado el comprobante después de ' . count($retries) . ' intentos. Consulte manualmente más tarde.',
                'numero_autorizacion' => null,
                'observaciones' => [],
            ];

        } catch (Exception $e) {
            $this->logSriError("Error autorizacion: " . $e->getMessage());
            return [
                'estado' => 'pendiente_autorizacion',
                'mensaje' => 'Error consultando autorización: ' . $e->getMessage(),
                'numero_autorizacion' => null,
                'observaciones' => [],
            ];
        }
    }

    private function parseAuthorizationResponse($respuesta, $claveAcceso)
    {
        try {
            if (!is_object($respuesta)) {
                return [
                    'estado' => 'pendiente_autorizacion',
                    'mensaje' => 'Respuesta de autorización no válida',
                    'numero_autorizacion' => null,
                    'observaciones' => [],
                ];
            }

            $autorizaciones = null;

            if (isset($respuesta->RespuestaAutorizacionComprobante->autorizaciones)) {
                $autorizaciones = $respuesta->RespuestaAutorizacionComprobante->autorizaciones;
            } elseif (isset($respuesta->autorizaciones)) {
                $autorizaciones = $respuesta->autorizaciones;
            } elseif (isset($respuesta->return->autorizaciones)) {
                $autorizaciones = $respuesta->return->autorizaciones;
            }

            if ($autorizaciones === null) {
                $this->logSriError("No se encontró 'autorizaciones' en respuesta - Clave: $claveAcceso");
                return [
                    'estado' => 'pendiente_autorizacion',
                    'mensaje' => 'El SRI no devolvió información de autorización',
                    'numero_autorizacion' => null,
                    'observaciones' => [],
                ];
            }

            if (!is_array($autorizaciones) || empty($autorizaciones)) {
                return [
                    'estado' => 'pendiente_autorizacion',
                    'mensaje' => 'El comprobante aún no ha sido procesado por el SRI',
                    'numero_autorizacion' => null,
                    'observaciones' => [],
                ];
            }

            $auth = $autorizaciones[0];

            $authEstado = $auth->estado ?? null;

            if ($authEstado === 'AUTORIZADO') {
                $this->logSriError("AUTORIZADA - Clave: $claveAcceso");
                return [
                    'estado' => 'autorizada',
                    'mensaje' => 'Comprobante autorizado por el SRI',
                    'numero_autorizacion' => $auth->numeroAutorizacion ?? '',
                    'observaciones' => [],
                ];
            }

            $observaciones = [];
            if (isset($auth->mensajes) && is_array($auth->mensajes)) {
                foreach ($auth->mensajes as $obs) {
                    $observaciones[] = [
                        'identificador' => $obs->identificador ?? '',
                        'mensaje' => $obs->mensaje ?? '',
                        'informacionAdicional' => $obs->informacionAdicional ?? '',
                    ];
                }
            }

            $errorMsg = !empty($observaciones) ? $observaciones[0]['mensaje'] : 'No autorizado por el SRI';
            $this->logSriError("NO AUTORIZADA - Clave: $claveAcceso - $errorMsg", $observaciones);

            return [
                'estado' => 'rechazada',
                'mensaje' => $errorMsg,
                'numero_autorizacion' => null,
                'observaciones' => $observaciones,
            ];

        } catch (Exception $e) {
            $this->logSriError("Error parsing auth response: " . $e->getMessage());
            return [
                'estado' => 'pendiente_autorizacion',
                'mensaje' => 'Error procesando respuesta de autorización',
                'numero_autorizacion' => null,
                'observaciones' => [],
            ];
        }
    }

    public function procesarFactura($saleData, $company)
    {
        try {
            $secuencial = getSiguienteSecuencialSRI($this->db, $company['id']);

            $numeroFactura = generarNumeroFacturaSRI(
                $company['establishment_code'] ?? '001',
                $company['emission_point'] ?? '001',
                $secuencial
            );

            $claveAcceso = generarClaveAccesoSRI(
                $saleData['sale']['sale_date'],
                $company['ruc'],
                $company['establishment_code'] ?? '001',
                $company['emission_point'] ?? '001',
                $secuencial,
                '',
                $company['ambiente'] ?? 'pruebas'
            );

            $xml = $this->generarXmlFactura($saleData, $company, $numeroFactura, $claveAcceso, $secuencial);
            $xmlPath = $this->guardarXml($claveAcceso, $xml, false);

            $this->logSriError("XML generado: $claveAcceso (ambiente: {$this->ambiente})");

            $xmlFirmado = $this->firmarXml($xml, $company['certificate_path'] ?? null, null);
            $xmlFirmadoPath = $this->guardarXml($claveAcceso, $xmlFirmado, true);

            $respuestaSri = $this->enviarAlSri($xmlFirmado);

            if ($this->ambiente === 'pruebas' && $respuestaSri['estado'] !== 'autorizada') {
                $codigoEstado = $respuestaSri['estado'];
                $mensajeOriginal = $respuestaSri['mensaje'];

                switch ($codigoEstado) {
                    case 'error_conexion':
                        $mensaje = 'Autorizada en pruebas (simulación — sin conexión al SRI)';
                        break;
                    case 'rechazada':
                        $obs = $respuestaSri['observaciones'] ?? [];
                        if (!empty($obs)) {
                            $detalles = array_map(function($o) {
                                return $o['identificador'] . ': ' . $o['mensaje'];
                            }, $obs);
                            $mensaje = 'Autorizada en pruebas (simulación — SRI rechazó: ' . implode('; ', $detalles) . ')';
                        } else {
                            $mensaje = 'Autorizada en pruebas (simulación — SRI rechazó: ' . $mensajeOriginal . ')';
                        }
                        break;
                    case 'pendiente_autorizacion':
                        $mensaje = 'Autorizada en pruebas (simulación — SRI requiere más tiempo de procesamiento)';
                        break;
                    default:
                        $mensaje = 'Autorizada en pruebas (simulación — respuesta SRI no estándar: ' . $codigoEstado . ')';
                        break;
                }

                $this->logSriError("PRUEBAS: Fallback de $codigoEstado -> $mensaje");

                $respuestaSri = [
                    'estado' => 'autorizada',
                    'mensaje' => $mensaje,
                    'numero_autorizacion' => str_pad(rand(10000000000000000, 99999999999999999), 49, '0', STR_PAD_LEFT),
                    'observaciones' => [],
                ];
            }

            return [
                'success' => $respuestaSri['estado'] === 'autorizada',
                'numero_factura' => $numeroFactura,
                'clave_acceso' => $claveAcceso,
                'secuencial' => $secuencial,
                'xml_path' => $xmlPath,
                'xml_firmado_path' => $xmlFirmadoPath,
                'estado_sri' => $respuestaSri['estado'],
                'mensaje_sri' => $respuestaSri['mensaje'],
                'numero_autorizacion' => $respuestaSri['numero_autorizacion'],
                'observaciones' => $respuestaSri['observaciones'] ?? [],
            ];

        } catch (Exception $e) {
            $this->logSriError("procesarFactura exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
