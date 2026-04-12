<?php
// app/repositories/ServiceRepository.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/ServiceModel.php';

class ServiceRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($onlyActive = false)
    {
        $sql = "SELECT * FROM services";
        if ($onlyActive) {
            $sql .= " WHERE active = 1";
        }
        $sql .= " ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $services = [];
        foreach ($rows as $row) {
            $services[] = new ServiceModel($row);
        }
        return $services;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM services WHERE id_service = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new ServiceModel($row) : null;
    }

    public function create(ServiceModel $service)
    {
        $sql = "INSERT INTO services (name, description, price, taxable, active)
                VALUES (:name, :description, :price, :taxable, :active)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name'        => $service->getName(),
            ':description' => $service->getDescription(),
            ':price'       => $service->getPrice(),
            ':taxable'     => $service->getTaxable() ? 1 : 0,
            ':active'      => $service->getActive() ? 1 : 0
        ]);
    }

    public function update(ServiceModel $service)
    {
        $sql = "UPDATE services SET
                    name = :name,
                    description = :description,
                    price = :price,
                    taxable = :taxable,
                    active = :active
                WHERE id_service = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => $service->getIdService(),
            ':name'        => $service->getName(),
            ':description' => $service->getDescription(),
            ':price'       => $service->getPrice(),
            ':taxable'     => $service->getTaxable() ? 1 : 0,
            ':active'      => $service->getActive() ? 1 : 0
        ]);
    }

    public function deactivate($id)
    {
        $sql = "UPDATE services SET active = 0 WHERE id_service = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id)
    {
        $sql = "UPDATE services SET active = 1 WHERE id_service = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}