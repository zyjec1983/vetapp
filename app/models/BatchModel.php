<?php
/**
 * Location: vetapp/app/models/BatchModel.php
 * Representa un lote de medicamento (entrada de inventario)
 */

class BatchModel
{
    private $id_batch;
    private $id_medication;
    private $batch_number;
    private $expiration_date;
    private $purchase_price;
    private $quantity_received;
    private $quantity_remaining;
    private $received_date;

    public function __construct($data = [])
    {
        $this->hydrate($data);
    }

    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }

    // Getters y Setters
    public function getIdBatch() { return $this->id_batch; }
    public function setIdBatch($id) { $this->id_batch = $id; }

    public function getIdMedication() { return $this->id_medication; }
    public function setIdMedication($id) { $this->id_medication = $id; }

    public function getBatchNumber() { return $this->batch_number; }
    public function setBatchNumber($num) { $this->batch_number = $num; }

    public function getExpirationDate() { return $this->expiration_date; }
    public function setExpirationDate($date) { $this->expiration_date = $date; }

    public function getPurchasePrice() { return $this->purchase_price; }
    public function setPurchasePrice($price) { $this->purchase_price = $price; }

    public function getQuantityReceived() { return $this->quantity_received; }
    public function setQuantityReceived($qty) { $this->quantity_received = $qty; }

    public function getQuantityRemaining() { return $this->quantity_remaining; }
    public function setQuantityRemaining($qty) { $this->quantity_remaining = $qty; }

    public function getReceivedDate() { return $this->received_date; }
    public function setReceivedDate($date) { $this->received_date = $date; }
}