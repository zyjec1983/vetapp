<?php
// app/models/ServiceModel.php

class ServiceModel
{
    private $id_service;
    private $name;
    private $description;
    private $price;
    private $taxable;
    private $active;
    private $created_at;

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
    public function getIdService() { return $this->id_service; }
    public function setIdService($id) { $this->id_service = $id; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getDescription() { return $this->description; }
    public function setDescription($desc) { $this->description = $desc; }

    public function getPrice() { return $this->price; }
    public function setPrice($price) { $this->price = $price; }

    public function getTaxable() { return $this->taxable; }
    public function setTaxable($taxable) { $this->taxable = (bool) $taxable; }

    public function getActive() { return $this->active; }
    public function setActive($active) { $this->active = (bool) $active; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }
}