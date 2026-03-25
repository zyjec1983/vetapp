<?php
// app/repositories/PetRepository.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/PetModel.php';

class PetRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT p.*, 
                       CONCAT(c.name, ' ', c.lastname1) as client_name
                FROM pets p
                INNER JOIN clients c ON p.id_client = c.id_client
                WHERE p.active = 1
                ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pets = [];
        foreach ($rows as $row) {
            $pet = new PetModel($row);
            $pet->setClientName($row['client_name']);
            $pets[] = $pet;
        }
        return $pets;
    }

    public function findById($id)
    {
        $sql = "SELECT p.*, 
                       CONCAT(c.name, ' ', c.lastname1) as client_name
                FROM pets p
                INNER JOIN clients c ON p.id_client = c.id_client
                WHERE p.id_pet = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $pet = new PetModel($row);
            $pet->setClientName($row['client_name']);
            return $pet;
        }
        return null;
    }

    public function create(PetModel $pet)
    {
        $sql = "INSERT INTO pets (
                    id_client, name, species, breed, sex, date_of_birth,
                    current_weight, color, microchip, allergies, observations, picture, active
                ) VALUES (
                    :id_client, :name, :species, :breed, :sex, :date_of_birth,
                    :current_weight, :color, :microchip, :allergies, :observations, :picture, :active
                )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_client'      => $pet->getIdClient(),
            ':name'           => $pet->getName(),
            ':species'        => $pet->getSpecies(),
            ':breed'          => $pet->getBreed(),
            ':sex'            => $pet->getSex(),
            ':date_of_birth'  => $pet->getDateOfBirth(),
            ':current_weight' => $pet->getCurrentWeight(),
            ':color'          => $pet->getColor(),
            ':microchip'      => $pet->getMicrochip(),
            ':allergies'      => $pet->getAllergies(),
            ':observations'   => $pet->getObservations(),
            ':picture'        => $pet->getPicture(),
            ':active'         => 1
        ]);
    }

    public function update(PetModel $pet)
    {
        $sql = "UPDATE pets SET
                    id_client = :id_client,
                    name = :name,
                    species = :species,
                    breed = :breed,
                    sex = :sex,
                    date_of_birth = :date_of_birth,
                    current_weight = :current_weight,
                    color = :color,
                    microchip = :microchip,
                    allergies = :allergies,
                    observations = :observations,
                    picture = :picture
                WHERE id_pet = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'             => $pet->getIdPet(),
            ':id_client'      => $pet->getIdClient(),
            ':name'           => $pet->getName(),
            ':species'        => $pet->getSpecies(),
            ':breed'          => $pet->getBreed(),
            ':sex'            => $pet->getSex(),
            ':date_of_birth'  => $pet->getDateOfBirth(),
            ':current_weight' => $pet->getCurrentWeight(),
            ':color'          => $pet->getColor(),
            ':microchip'      => $pet->getMicrochip(),
            ':allergies'      => $pet->getAllergies(),
            ':observations'   => $pet->getObservations(),
            ':picture'        => $pet->getPicture()
        ]);
    }

    public function deactivate($id)
    {
        $sql = "UPDATE pets SET active = 0 WHERE id_pet = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Buscar mascotas por nombre o por cliente (para autocomplete en consultas)
    public function search($term)
    {
        $sql = "SELECT p.*, 
                       CONCAT(c.name, ' ', c.lastname1) as client_name
                FROM pets p
                INNER JOIN clients c ON p.id_client = c.id_client
                WHERE p.active = 1
                  AND (p.name LIKE :term OR c.name LIKE :term)
                ORDER BY p.name ASC
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term' => '%' . $term . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pets = [];
        foreach ($rows as $row) {
            $pet = new PetModel($row);
            $pet->setClientName($row['client_name']);
            $pets[] = $pet;
        }
        return $pets;
    }

    // Obtener mascotas de un cliente específico
    public function getByClient($clientId)
    {
        $sql = "SELECT p.*, 
                       CONCAT(c.name, ' ', c.lastname1) as client_name
                FROM pets p
                INNER JOIN clients c ON p.id_client = c.id_client
                WHERE p.id_client = :client_id AND p.active = 1
                ORDER BY p.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':client_id' => $clientId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pets = [];
        foreach ($rows as $row) {
            $pet = new PetModel($row);
            $pet->setClientName($row['client_name']);
            $pets[] = $pet;
        }
        return $pets;
    }
}