<?php
/**
 * Location: vetapp/app/models/MedicationModel.php
 * Representa un producto farmacéutico (catálogo)
 */

class MedicationModel
{
    private $id_medication;
    private $code;
    private $name;
    private $id_active;
    private $category;
    private $description;
    private $minimum_stock;
    private $sale_price;
    private $location;
    private $active;
    private $created_at;

    // Datos adicionales (no en BD)
    private $active_name;       // nombre del principio activo
    private $stock_total;       // stock calculado
    private $taxable; 

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
    public function getIdMedication() { return $this->id_medication; }
    public function setIdMedication($id) { $this->id_medication = $id; }

    public function getCode() { return $this->code; }
    public function setCode($code) { $this->code = $code; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getIdActive() { return $this->id_active; }
    public function setIdActive($id) { $this->id_active = $id; }

    public function getCategory() { return $this->category; }
    public function setCategory($category) { $this->category = $category; }

    public function getDescription() { return $this->description; }
    public function setDescription($desc) { $this->description = $desc; }

    public function getMinimumStock() { return $this->minimum_stock; }
    public function setMinimumStock($stock) { $this->minimum_stock = $stock; }

    public function getSalePrice() { return $this->sale_price; }
    public function setSalePrice($price) { $this->sale_price = $price; }

    public function getLocation() { return $this->location; }
    public function setLocation($loc) { $this->location = $loc; }

    public function getActive() { return $this->active; }
    public function setActive($active) { $this->active = (bool) $active; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }

    // Datos adicionales
    public function getActiveName() { return $this->active_name; }
    public function setActiveName($name) { $this->active_name = $name; }

    public function getStockTotal() { return $this->stock_total; }
    public function setStockTotal($stock) { $this->stock_total = (int) $stock; }

    public function getTaxable() { return $this->taxable; }
    public function setTaxable($taxable) { $this->taxable = (bool) $taxable; }
}