<?php
/**
 * Location: vetapp/app/repositories/SaleRepository.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/SaleModel.php';
require_once __DIR__ . '/../models/SaleDetailModel.php';

class SaleRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Crear una nueva venta (cabecera + detalles) y descontar stock FIFO
     * @param SaleModel $sale
     * @param array $details Array de SaleDetailModel
     * @return int|false ID de la venta creada o false en caso de error
     */
    public function createSale(SaleModel $sale, array $details)
    {
        try {
            // Iniciar transacción
            $this->db->beginTransaction();

            // 1. Insertar cabecera
            $sql = "INSERT INTO sales (
                        sale_code, id_client, id_user, sale_date, subtotal, discount, tax_total, total,
                        payment_method, status, observations
                    ) VALUES (
                        :sale_code, :id_client, :id_user, NOW(), :subtotal, :discount, :tax_total, :total,
                        :payment_method, :status, :observations
                    )";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':sale_code' => $sale->getSaleCode(),
                ':id_client' => $sale->getIdClient(),
                ':id_user' => $sale->getIdUser(),
                ':subtotal' => $sale->getSubtotal(),
                ':discount' => $sale->getDiscount(),
                ':tax_total' => $sale->getTaxTotal(),
                ':total' => $sale->getTotal(),
                ':payment_method' => $sale->getPaymentMethod(),
                ':status' => $sale->getStatus(),
                ':observations' => $sale->getObservations()
            ]);
            $saleId = $this->db->lastInsertId();
            if (!$saleId) {
                throw new Exception("Error al insertar cabecera de venta");
            }

            // 2. Procesar cada detalle, descontar stock y guardar detalles
            foreach ($details as $detail) {
                $medicationId = $detail->getIdMedication();
                $quantityToSell = $detail->getQuantity();

                // Obtener lotes disponibles (FIFO por fecha de expiración)
                $batches = $this->getAvailableBatches($medicationId, $quantityToSell);
                if (empty($batches)) {
                    throw new Exception("Stock insuficiente para el producto ID {$medicationId}");
                }

                $remainingToSell = $quantityToSell;
                foreach ($batches as $batch) {
                    if ($remainingToSell <= 0)
                        break;

                    $batchId = $batch['id_batch'];
                    $available = $batch['quantity_remaining'];
                    $taken = min($available, $remainingToSell);

                    // Crear detalle por lote (puede ser que un detalle se divida en varios registros)
                    $detailLine = new SaleDetailModel([
                        'id_sale' => $saleId,
                        'id_medication' => $medicationId,
                        'id_batch' => $batchId,
                        'quantity' => $taken,
                        'unit_price' => $detail->getUnitPrice(),
                        'subtotal' => $taken * $detail->getUnitPrice(),
                        'tax_rate' => $detail->getTaxRate(),
                        'tax_amount' => ($taken * $detail->getUnitPrice()) * ($detail->getTaxRate() / 100),
                        'total' => ($taken * $detail->getUnitPrice()) * (1 + $detail->getTaxRate() / 100)
                    ]);
                    $this->insertSaleDetail($detailLine);

                    // Actualizar lote
                    $newRemaining = $available - $taken;
                    $this->updateBatchRemaining($batchId, $newRemaining);

                    // Registrar movimiento de inventario
                    $this->registerInventoryMovement($medicationId, $batchId, $taken, 'out', $saleId);

                    $remainingToSell -= $taken;
                }
            }

            // 3. Confirmar transacción
            $this->db->commit();
            return $saleId;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en SaleRepository::createSale: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener lotes disponibles para un medicamento, ordenados por fecha de expiración (FIFO)
     * @param int $medicationId
     * @param int $required
     * @return array
     */
    private function getAvailableBatches($medicationId, $required)
    {
        $sql = "SELECT id_batch, quantity_remaining
            FROM medication_batches
            WHERE id_medication = :med_id AND quantity_remaining > 0
            ORDER BY expiration_date ASC, received_date ASC
            LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':med_id' => $medicationId]);
        $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Batches encontrados para medicamento $medicationId: " . print_r($batches, true));

        $total = array_sum(array_column($batches, 'quantity_remaining'));
        if ($total < $required) {
            error_log("Stock total $total insuficiente para $required");
            return [];
        }
        return $batches;
    }


    private function insertSaleDetail(SaleDetailModel $detail)
    {
        $sql = "INSERT INTO sale_details (
                    id_sale, id_medication, id_batch, quantity, unit_price,
                    subtotal, tax_rate, tax_amount, total
                ) VALUES (
                    :id_sale, :id_medication, :id_batch, :quantity, :unit_price,
                    :subtotal, :tax_rate, :tax_amount, :total
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_sale' => $detail->getIdSale(),
            ':id_medication' => $detail->getIdMedication(),
            ':id_batch' => $detail->getIdBatch(),
            ':quantity' => $detail->getQuantity(),
            ':unit_price' => $detail->getUnitPrice(),
            ':subtotal' => $detail->getSubtotal(),
            ':tax_rate' => $detail->getTaxRate(),
            ':tax_amount' => $detail->getTaxAmount(),
            ':total' => $detail->getTotal()
        ]);
    }

    private function updateBatchRemaining($batchId, $newRemaining)
    {
        $sql = "UPDATE medication_batches SET quantity_remaining = :remaining WHERE id_batch = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $batchId, ':remaining' => $newRemaining]);
    }

    private function registerInventoryMovement($medicationId, $batchId, $quantity, $type, $referenceId)
    {
        $sql = "INSERT INTO inventory_movements (
                    id_medication, id_batch, movement_type, quantity, reason, reference_id, id_user
                ) VALUES (
                    :med_id, :batch_id, :type, :quantity, :reason, :ref_id, :user_id
                )";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':med_id' => $medicationId,
            ':batch_id' => $batchId,
            ':type' => $type,
            ':quantity' => $quantity,
            ':reason' => "Venta #{$referenceId}",
            ':ref_id' => $referenceId,
            ':user_id' => $_SESSION['user']['id'] ?? 0
        ]);
    }

    // ========== Métodos adicionales (listado, detalle, etc.) ==========
    public function getAll()
    {
        $sql = "SELECT s.*, c.name as client_name, u.name as user_name
                FROM sales s
                LEFT JOIN clients c ON s.id_client = c.id_client
                LEFT JOIN users u ON s.id_user = u.id_user
                ORDER BY s.sale_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        // Obtener cabecera
        $sql = "SELECT s.*, c.name AS client_name, c.phone AS client_phone
        FROM sales s
        LEFT JOIN clients c ON s.id_client = c.id_client
        WHERE s.id_sale = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $sale = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$sale) {
            return null;
        }

        // Obtener detalles
        $sqlDetails = "SELECT sd.*, m.name AS medication_name
                   FROM sale_details sd
                   JOIN medications m ON sd.id_medication = m.id_medication
                   WHERE sd.id_sale = :id";
        $stmtDetails = $this->db->prepare($sqlDetails);
        $stmtDetails->execute([':id' => $id]);
        $details = $stmtDetails->fetchAll(PDO::FETCH_ASSOC);

        return ['sale' => $sale, 'details' => $details];
    }

    private function getDetails($saleId)
    {
        $sql = "SELECT sd.*, m.name as medication_name
                FROM sale_details sd
                JOIN medications m ON sd.id_medication = m.id_medication
                WHERE sd.id_sale = :id
                ORDER BY sd.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $saleId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancel($id)
    {
        $sql = "UPDATE sales SET status = 'cancelled' WHERE id_sale = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}