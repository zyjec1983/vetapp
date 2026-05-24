<?php
/**
 * Location: vetapp/app/repositories/CreditNoteRepository.php
 *
 * Repositorio para Notas de Crédito Electrónicas.
 * CRUD + restauración de stock + secuencial + estado SRI.
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/CreditNoteModel.php';
require_once __DIR__ . '/../models/CreditNoteDetailModel.php';

class CreditNoteRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(CreditNoteModel $cn)
    {
        $sql = "INSERT INTO credit_notes (
            id_sale, id_original_invoice, company_id, id_user,
            numero_nota_credito, clave_acceso, estado_sri, motivo,
            subtotal, discount, tax_total, total, mensaje_sri
        ) VALUES (
            :id_sale, :id_original_invoice, :company_id, :id_user,
            :numero_nota_credito, :clave_acceso, :estado_sri, :motivo,
            :subtotal, :discount, :tax_total, :total, :mensaje_sri
        )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_sale' => $cn->getIdSale(),
            ':id_original_invoice' => $cn->getIdOriginalInvoice(),
            ':company_id' => $cn->getCompanyId(),
            ':id_user' => $cn->getIdUser(),
            ':numero_nota_credito' => $cn->getNumeroNotaCredito(),
            ':clave_acceso' => $cn->getClaveAcceso(),
            ':estado_sri' => $cn->getEstadoSri(),
            ':motivo' => $cn->getMotivo(),
            ':subtotal' => $cn->getSubtotal(),
            ':discount' => $cn->getDiscount(),
            ':tax_total' => $cn->getTaxTotal(),
            ':total' => $cn->getTotal(),
            ':mensaje_sri' => $cn->getMensajeSri(),
        ]);
        return $this->db->lastInsertId();
    }

    public function createDetail(CreditNoteDetailModel $d)
    {
        $sql = "INSERT INTO credit_note_details (
            id_credit_note, id_medication, id_batch, quantity,
            unit_price, subtotal, tax_rate, tax_amount, total
        ) VALUES (
            :id_credit_note, :id_medication, :id_batch, :quantity,
            :unit_price, :subtotal, :tax_rate, :tax_amount, :total
        )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_credit_note' => $d->getIdCreditNote(),
            ':id_medication' => $d->getIdMedication(),
            ':id_batch' => $d->getIdBatch(),
            ':quantity' => $d->getQuantity(),
            ':unit_price' => $d->getUnitPrice(),
            ':subtotal' => $d->getSubtotal(),
            ':tax_rate' => $d->getTaxRate(),
            ':tax_amount' => $d->getTaxAmount(),
            ':total' => $d->getTotal(),
        ]);
    }

    public function findById($id)
    {
        $sql = "SELECT cn.*, s.sale_code, s.sale_date,
                       cs.commercial_name AS company_name, cs.ruc AS company_ruc,
                       c.name AS client_name, c.identification AS client_identification,
                       ei.numero_factura AS factura_original
                FROM credit_notes cn
                LEFT JOIN sales s ON cn.id_sale = s.id_sale
                LEFT JOIN clients c ON s.id_client = c.id_client
                LEFT JOIN company_settings cs ON cn.company_id = cs.id
                LEFT JOIN electronic_invoices ei ON cn.id_original_invoice = ei.id
                WHERE cn.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $cn = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cn) {
            $cn['details'] = $this->getDetails($id);
        }

        return $cn;
    }

    public function getDetails($creditNoteId)
    {
        $sql = "SELECT cnd.*, m.name AS medication_name, m.code AS medication_code
                FROM credit_note_details cnd
                LEFT JOIN medications m ON cnd.id_medication = m.id_medication
                WHERE cnd.id_credit_note = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $creditNoteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findBySaleId($saleId)
    {
        $sql = "SELECT cn.*, ei.numero_factura AS factura_original,
                       cs.commercial_name AS company_name
                FROM credit_notes cn
                LEFT JOIN electronic_invoices ei ON cn.id_original_invoice = ei.id
                LEFT JOIN company_settings cs ON cn.company_id = cs.id
                WHERE cn.id_sale = :id_sale
                ORDER BY cn.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($limit = 50)
    {
        $sql = "SELECT cn.*, s.sale_code,
                       cs.commercial_name AS company_name,
                       c.name AS client_name
                FROM credit_notes cn
                LEFT JOIN sales s ON cn.id_sale = s.id_sale
                LEFT JOIN clients c ON s.id_client = c.id_client
                LEFT JOIN company_settings cs ON cn.company_id = cs.id
                ORDER BY cn.created_at DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateSriStatus($id, $estado, $mensaje = null, $numeroAutorizacion = null, $xmlPath = null, $xmlFirmadoPath = null)
    {
        $sql = "UPDATE credit_notes SET
                estado_sri = :estado,
                mensaje_sri = :mensaje,
                updated_at = NOW()";

        $params = [
            ':id' => $id,
            ':estado' => $estado,
            ':mensaje' => $mensaje,
        ];

        if ($numeroAutorizacion) {
            $sql .= ", numero_autorizacion = :numero_autorizacion";
            $params[':numero_autorizacion'] = $numeroAutorizacion;
        }
        if ($xmlPath) {
            $sql .= ", xml_path = :xml_path";
            $params[':xml_path'] = $xmlPath;
        }
        if ($xmlFirmadoPath) {
            $sql .= ", xml_firmado_path = :xml_firmado_path";
            $params[':xml_firmado_path'] = $xmlFirmadoPath;
        }

        $sql .= " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function existsForSale($saleId)
    {
        $sql = "SELECT COUNT(*) FROM credit_notes WHERE id_sale = :id_sale";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function existsTotalCreditNoteForSale($saleId)
    {
        $sql = "SELECT COUNT(*) FROM credit_notes WHERE id_sale = :id_sale AND motivo = 'Devolución total' AND estado_sri = 'autorizada'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sale' => $saleId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getSiguienteSecuencialNC($companyId)
    {
        $sql = "SELECT COALESCE(MAX(CAST(SUBSTRING(numero_nota_credito, 9) AS UNSIGNED)), 0) + 1 as next_seq
                FROM credit_notes
                WHERE company_id = :company_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':company_id' => $companyId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['next_seq'] ?? 1);
    }

    /**
     * Restaurar stock al inventario por cada detalle de la NC.
     * Se llama DENTRO de una transacción.
     */
    public function restoreStock($creditNoteId)
    {
        $details = $this->getDetails($creditNoteId);
        if (empty($details)) {
            return;
        }

        foreach ($details as $d) {
            $this->db->prepare(
                "UPDATE medication_batches SET quantity_remaining = quantity_remaining + :qty WHERE id_batch = :id"
            )->execute([
                ':qty' => (int)$d['quantity'],
                ':id' => (int)$d['id_batch'],
            ]);

            $this->db->prepare(
                "INSERT INTO inventory_movements (id_medication, id_batch, movement_type, quantity, reason, reference_id, id_user, created_at)
                 VALUES (:med_id, :batch_id, 'in', :qty, :reason, :ref_id, :user_id, NOW())"
            )->execute([
                ':med_id' => (int)$d['id_medication'],
                ':batch_id' => (int)$d['id_batch'],
                ':qty' => (int)$d['quantity'],
                ':reason' => "NC #{$creditNoteId}",
                ':ref_id' => $creditNoteId,
                ':user_id' => $_SESSION['user']['id'] ?? 0,
            ]);
        }
    }

    /**
     * Obtener los lotes que se vendieron para un medicamento en una venta específica.
     * Útil para saber a qué lote devolver el stock.
     */
    public function getBatchesSoldInSale($saleId, $medicationId)
    {
        $sql = "SELECT sd.id_batch, sd.quantity, sd.unit_price, sd.subtotal, sd.tax_rate, sd.tax_amount, sd.total,
                       b.quantity_remaining AS current_stock
                FROM sale_details sd
                LEFT JOIN medication_batches b ON sd.id_batch = b.id_batch
                WHERE sd.id_sale = :id_sale AND sd.id_medication = :med_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_sale' => $saleId, ':med_id' => $medicationId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
