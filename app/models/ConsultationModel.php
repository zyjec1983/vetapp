<?php
// app/models/ConsultationModel.php

class ConsultationModel
{
    private $id_consultation;
    private $id_pet;
    private $id_user;
    private $consultation_date;
    private $weight;
    private $temperature;
    private $diagnosis;
    private $treatment;
    private $next_visit;
    private $consultation_fee;
    private $status;
    private $observations;
    private $active;
    private $created_at;

    // Datos adicionales (no en BD)
    private $pet_name;
    private $client_name;
    private $vet_name;

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
    public function getIdConsultation() { return $this->id_consultation; }
    public function setIdConsultation($id) { $this->id_consultation = $id; }

    public function getIdPet() { return $this->id_pet; }
    public function setIdPet($id) { $this->id_pet = $id; }

    public function getIdUser() { return $this->id_user; }
    public function setIdUser($id) { $this->id_user = $id; }

    public function getConsultationDate() { return $this->consultation_date; }
    public function setConsultationDate($date) { $this->consultation_date = $date; }

    public function getWeight() { return $this->weight; }
    public function setWeight($weight) { $this->weight = $weight; }

    public function getTemperature() { return $this->temperature; }
    public function setTemperature($temp) { $this->temperature = $temp; }

    public function getDiagnosis() { return $this->diagnosis; }
    public function setDiagnosis($diag) { $this->diagnosis = $diag; }

    public function getTreatment() { return $this->treatment; }
    public function setTreatment($treat) { $this->treatment = $treat; }

    public function getNextVisit() { return $this->next_visit; }
    public function setNextVisit($date) { $this->next_visit = $date; }

    public function getConsultationFee() { return $this->consultation_fee; }
    public function setConsultationFee($fee) { $this->consultation_fee = $fee; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function getObservations() { return $this->observations; }
    public function setObservations($obs) { $this->observations = $obs; }

    public function getActive() { return $this->active; }
    public function setActive($active) { $this->active = (bool) $active; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }

    public function getPetName() { return $this->pet_name; }
    public function setPetName($name) { $this->pet_name = $name; }

    public function getClientName() { return $this->client_name; }
    public function setClientName($name) { $this->client_name = $name; }

    public function getVetName() { return $this->vet_name; }
    public function setVetName($name) { $this->vet_name = $name; }
}