<?php
// app/models/PetModel.php

class PetModel
{
    private $id_pet;
    private $id_client;
    private $name;
    private $species;
    private $breed;
    private $sex;
    private $date_of_birth;
    private $current_weight;
    private $color;
    private $microchip;
    private $allergies;
    private $observations;
    private $picture;
    private $active;
    private $created_at;

    // Datos adicionales (no en BD, para mostrar)
    private $client_name;

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
    public function getIdPet() { return $this->id_pet; }
    public function setIdPet($id) { $this->id_pet = $id; }

    public function getIdClient() { return $this->id_client; }
    public function setIdClient($id) { $this->id_client = $id; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getSpecies() { return $this->species; }
    public function setSpecies($species) { $this->species = $species; }

    public function getBreed() { return $this->breed; }
    public function setBreed($breed) { $this->breed = $breed; }

    public function getSex() { return $this->sex; }
    public function setSex($sex) { $this->sex = $sex; }

    public function getDateOfBirth() { return $this->date_of_birth; }
    public function setDateOfBirth($date) { $this->date_of_birth = $date; }

    public function getCurrentWeight() { return $this->current_weight; }
    public function setCurrentWeight($weight) { $this->current_weight = $weight; }

    public function getColor() { return $this->color; }
    public function setColor($color) { $this->color = $color; }

    public function getMicrochip() { return $this->microchip; }
    public function setMicrochip($microchip) { $this->microchip = $microchip; }

    public function getAllergies() { return $this->allergies; }
    public function setAllergies($allergies) { $this->allergies = $allergies; }

    public function getObservations() { return $this->observations; }
    public function setObservations($observations) { $this->observations = $observations; }

    public function getPicture() { return $this->picture; }
    public function setPicture($picture) { $this->picture = $picture; }

    public function getActive() { return $this->active; }
    public function setActive($active) { $this->active = (bool) $active; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }

    public function getClientName() { return $this->client_name; }
    public function setClientName($name) { $this->client_name = $name; }

    // Calcular edad aproximada
    public function getAge()
    {
        if (!$this->date_of_birth) return 'No registrada';
        $dob = new DateTime($this->date_of_birth);
        $now = new DateTime();
        $diff = $now->diff($dob);
        return $diff->y . ' años';
    }
}