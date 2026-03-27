<?php
/**
 * Location: vetapp/app/repositories/BatchRepository.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/BatchModel.php';

class BatchRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los lotes de un medicamento
     */
    public function getByMedication($medicationId)
    {
        $sql = "SELECT * FROM medication_batches
                WHERE id_medication = :id
                ORDER BY expiration_date ASC, received_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $medicationId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $batches = [];
        foreach ($rows as $row) {
            $batches[] = new BatchModel($row);
        }
        return $batches;
    }

    /**
     * Crear un nuevo lote
     */
    public function create(BatchModel $batch)
    {
        $sql = "INSERT INTO medication_batches (
                    id_medication, batch_number, expiration_date,
                    purchase_price, quantity_received, quantity_remaining
                ) VALUES (
                    :id_medication, :batch_number, :expiration_date,
                    :purchase_price, :quantity_received, :quantity_remaining
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_medication'    => $batch->getIdMedication(),
            ':batch_number'     => $batch->getBatchNumber(),
            ':expiration_date'  => $batch->getExpirationDate(),
            ':purchase_price'   => $batch->getPurchasePrice(),
            ':quantity_received'=> $batch->getQuantityReceived(),
            ':quantity_remaining'=> $batch->getQuantityRemaining()
        ]);
    }

    /**
     * Actualizar la cantidad restante de un lote (usado para descontar stock)
     */
    public function updateRemaining($batchId, $newRemaining)
    {
        $sql = "UPDATE medication_batches SET quantity_remaining = :remaining
                WHERE id_batch = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $batchId, ':remaining' => $newRemaining]);
    }
}