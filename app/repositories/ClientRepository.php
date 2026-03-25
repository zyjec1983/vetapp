<?php
// app/repositories/ClientRepository.php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/ClientModel.php';

class ClientRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT * FROM clients WHERE active =1 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $clients = [];
        foreach ($rows as $row) {
            $clients[] = new ClientModel($row);
        }
        return $clients;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM clients WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new ClientModel($row) : null;
    }

    public function create(ClientModel $client)
    {
        $sql = "INSERT INTO clients (name, middlename, lastname1, lastname2, phone, email, address, identification, observations)
                VALUES (:name, :middlename, :lastname1, :lastname2, :phone, :email, :address, :identification, :observations)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name' => $client->getName(),
            ':middlename' => $client->getMiddlename(),
            ':lastname1' => $client->getLastname1(),
            ':lastname2' => $client->getLastname2(),
            ':phone' => $client->getPhone(),
            ':email' => $client->getEmail(),
            ':address' => $client->getAddress(),
            ':identification' => $client->getIdentification(),
            ':observations' => $client->getObservations()
        ]);
    }

    public function update(ClientModel $client)
    {
        $sql = "UPDATE clients SET
                    name = :name,
                    middlename = :middlename,
                    lastname1 = :lastname1,
                    lastname2 = :lastname2,
                    phone = :phone,
                    email = :email,
                    address = :address,
                    identification = :identification,
                    observations = :observations
                WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $client->getIdClient(),
            ':name' => $client->getName(),
            ':middlename' => $client->getMiddlename(),
            ':lastname1' => $client->getLastname1(),
            ':lastname2' => $client->getLastname2(),
            ':phone' => $client->getPhone(),
            ':email' => $client->getEmail(),
            ':address' => $client->getAddress(),
            ':identification' => $client->getIdentification(),
            ':observations' => $client->getObservations()
        ]);
    }

    public function deactivate($id)
    {
        $sql = "UPDATE clients SET active = 0 WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id)
    {
        $sql = "UPDATE clients SET active = 1 WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function delete($id)
    {
        $sql = "DELETE FROM clients WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Búsqueda para autocomplete (usado en consultas)
    public function search($term)
    {
        $sql = "SELECT * FROM clients
                WHERE name LIKE :term
                   OR lastname1 LIKE :term
                   OR identification LIKE :term
                ORDER BY name ASC
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term' => '%' . $term . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $clients = [];
        foreach ($rows as $row) {
            $clients[] = new ClientModel($row);
        }
        return $clients;
    }
}
?>

<?php
/**
 * Location: vetapp/app/repositories/ClientRepository.php
 */
/*
require_once __DIR__ . '/../models/ClientModel.php';

class ClientRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $sql = "SELECT * FROM clients ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $clients = [];
        foreach ($rows as $row) {
            $clients[] = new ClientModel($row);
        }
        return $clients;
    }

    public function findById($id)
    {
        $sql = "SELECT * FROM clients WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new ClientModel($row) : null;
    }

    public function create(ClientModel $client)
    {
        $sql = "INSERT INTO clients (name, middlename, lastname1, lastname2, phone, email, address, identification, observations)
                VALUES (:name, :middlename, :lastname1, :lastname2, :phone, :email, :address, :identification, :observations)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name'          => $client->getName(),
            ':middlename'    => $client->getMiddlename(),
            ':lastname1'     => $client->getLastname1(),
            ':lastname2'     => $client->getLastname2(),
            ':phone'         => $client->getPhone(),
            ':email'         => $client->getEmail(),
            ':address'       => $client->getAddress(),
            ':identification'=> $client->getIdentification(),
            ':observations'  => $client->getObservations()
        ]);
    }

    public function update(ClientModel $client)
    {
        $sql = "UPDATE clients SET 
                    name = :name,
                    middlename = :middlename,
                    lastname1 = :lastname1,
                    lastname2 = :lastname2,
                    phone = :phone,
                    email = :email,
                    address = :address,
                    identification = :identification,
                    observations = :observations
                WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'            => $client->getIdClient(),
            ':name'          => $client->getName(),
            ':middlename'    => $client->getMiddlename(),
            ':lastname1'     => $client->getLastname1(),
            ':lastname2'     => $client->getLastname2(),
            ':phone'         => $client->getPhone(),
            ':email'         => $client->getEmail(),
            ':address'       => $client->getAddress(),
            ':identification'=> $client->getIdentification(),
            ':observations'  => $client->getObservations()
        ]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM clients WHERE id_client = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function search($term)
    {
        $sql = "SELECT * FROM clients 
                WHERE name LIKE :term 
                   OR lastname1 LIKE :term 
                   OR identification LIKE :term
                ORDER BY name ASC
                LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term' => '%' . $term . '%']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $clients = [];
        foreach ($rows as $row) {
            $clients[] = new ClientModel($row);
        }
        return $clients;
    }
}

*/
?>