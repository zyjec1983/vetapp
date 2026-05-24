<?php
/**
 * Location: vetapp/app/models/CreditNoteDetailModel.php
 *
 * Modelo de dominio para detalle de Nota de Crédito.
 * Representa un producto devuelto en la NC.
 */

class CreditNoteDetailModel
{
    private $id;
    private $id_credit_note;
    private $id_medication;
    private $id_batch;
    private $quantity;
    private $unit_price;
    private $subtotal;
    private $tax_rate;
    private $tax_amount;
    private $total;

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

    // Getters
    public function getId() { return $this->id; }
    public function getIdCreditNote() { return $this->id_credit_note; }
    public function getIdMedication() { return $this->id_medication; }
    public function getIdBatch() { return $this->id_batch; }
    public function getQuantity() { return $this->quantity; }
    public function getUnitPrice() { return $this->unit_price; }
    public function getSubtotal() { return $this->subtotal; }
    public function getTaxRate() { return $this->tax_rate; }
    public function getTaxAmount() { return $this->tax_amount; }
    public function getTotal() { return $this->total; }

    // Setters
    public function setId($v) { $this->id = $v; }
    public function setIdCreditNote($v) { $this->id_credit_note = $v; }
    public function setIdMedication($v) { $this->id_medication = $v; }
    public function setIdBatch($v) { $this->id_batch = $v; }
    public function setQuantity($v) { $this->quantity = $v; }
    public function setUnitPrice($v) { $this->unit_price = $v; }
    public function setSubtotal($v) { $this->subtotal = $v; }
    public function setTaxRate($v) { $this->tax_rate = $v; }
    public function setTaxAmount($v) { $this->tax_amount = $v; }
    public function setTotal($v) { $this->total = $v; }
}
