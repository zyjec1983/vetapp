<?php
/**
 * Location: vetapp/app/repositories/ElectronicInvoiceRepository.php
 *
 * Gestión del estado de facturas electrónicas ante el SRI.
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/ElectronicInvoiceModel.php';

class ElectronicInvoiceRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear registro de factura electrónica
     */
    public function create(ElectronicInvoiceModel $invoice)
    {
        $sql = "INSERT INTO electronic_invoices (
            id_sale, company_id, numero_factura, clave_acceso,
            estado_sri, mensaje_sri
        ) VALUES (
            :id_sale, :company_id, :numero_factura, :clave_acceso,
            :estado_sri, :mensaje_sri
        )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_sale' => $invoice->getIdSale(),
            ':company_id' => $invoice->getCompanyId(),
            ':numero_factura' => $invoice->getNumeroFactura(),
            ':clave_acceso' => $invoice->getClaveAcceso(),
            ':estado_sri' => $invoice->getEstadoSri(),
            ':mensaje_sri' => $invoice->getMensajeSri(),
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Obtener factura electrónica por ID de venta
     */
    public function findBySaleId($saleId)
    {
        $sql = "SELECT ei.*, cs.ruc as company_ruc, cs.commercial_name as company_name
                FROM electronic_invoices ei
                LEFT JOIN company_settings cs ON ei.company_id = cs.id
                WHERE ei.id_sale = :id_sale
                ORDER BY ei.creado_en DESC
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las facturas electrónicas
     */
    public function getAll($limit = 50)
    {
        $sql = "SELECT ei.*, s.sale_code, c.name as client_name,
                       cs.commercial_name as company_name, cs.ruc as company_ruc
                FROM electronic_invoices ei
                LEFT JOIN sales s ON ei.id_sale = s.id_sale
                LEFT JOIN clients c ON s.id_client = c.id_client
                LEFT JOIN company_settings cs ON ei.company_id = cs.id
                ORDER BY ei.creado_en DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado SRI de una factura
     */
    public function updateEstado($id, $estado, $mensaje, $numeroAutorizacion = null, $xmlPath = null, $xmlFirmadoPath = null)
    {
        $sql = "UPDATE electronic_invoices SET
                estado_sri = :estado,
                mensaje_sri = :mensaje,
                fecha_envio = NOW(),";

        if ($numeroAutorizacion) {
            $sql .= " numero_autorizacion = :numero_autorizacion,
                      fecha_autorizacion = NOW(),";
        }

        if ($xmlPath) {
            $sql .= " xml_path = :xml_path,";
        }

        if ($xmlFirmadoPath) {
            $sql .= " xml_firmado_path = :xml_firmado_path,";
        }

        $sql .= " actualizado_en = NOW() WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $params = [
            ':id' => $id,
            ':estado' => $estado,
            ':mensaje' => $mensaje,
        ];

        if ($numeroAutorizacion) {
            $params[':numero_autorizacion'] = $numeroAutorizacion;
        }

        if ($xmlPath) {
            $params[':xml_path'] = $xmlPath;
        }

        if ($xmlFirmadoPath) {
            $params[':xml_firmado_path'] = $xmlFirmadoPath;
        }

        return $stmt->execute($params);
    }

    /**
     * Verificar si una venta ya tiene factura electrónica
     */
    public function existsForSale($saleId)
    {
        $sql = "SELECT COUNT(*) FROM electronic_invoices WHERE id_sale = :id_sale";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
