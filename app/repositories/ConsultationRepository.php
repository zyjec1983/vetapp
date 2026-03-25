<?php
/**
 * Location: vetapp/app/repositories/ConsultationRepository.php
 */

class ConsultationRepository
{
    private $db;

    /**
     * Constructor
     * Recibe la conexión PDO desde el controller
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Obtener todas las consultas con información relacionada
     * 
     * Incluye:
     * - Mascota
     * - Cliente
     * - Veterinario (nombre completo)
     * - Diagnóstico corto
     */
    public function getAll()
    {
        try {
            $sql = "
                SELECT 
                    c.id_consultation,
                    c.consultation_date,
                    c.status,

                    -- Mascota
                    p.name AS pet_name,

                    -- Cliente
                    cl.name AS client_name,

                    -- Veterinario (nombre completo)
                    CONCAT(
                        u.name, ' ',
                        IFNULL(u.middlename, ''), ' ',
                        u.lastname1, ' ',
                        IFNULL(u.lastname2, '')
                    ) AS vet_name,

                    -- Diagnóstico corto (50 caracteres)
                    LEFT(c.diagnosis, 50) AS diagnosis_short

                FROM consultations c

                INNER JOIN pets p 
                    ON c.id_pet = p.id_pet

                INNER JOIN clients cl 
                    ON p.id_client = cl.id_client

                INNER JOIN users u 
                    ON c.id_user = u.id_user

                ORDER BY c.consultation_date DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            // Manejo de error profesional
            error_log("Error en ConsultationRepository::getAll - " . $e->getMessage());
            return [];
        }
    }

    // app/repositories/ConsultationRepository.php

    public function create(ConsultationModel $consultation)
    {
        $sql = "INSERT INTO consultations (
                id_pet, id_user, consultation_date, weight, temperature,
                diagnosis, treatment, next_visit, consultation_fee, status, observations
            ) VALUES (
                :id_pet, :id_user, NOW(), :weight, :temperature,
                :diagnosis, :treatment, :next_visit, :consultation_fee, :status, :observations
            )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_pet' => $consultation->getIdPet(),
            ':id_user' => $consultation->getIdUser(),
            ':weight' => $consultation->getWeight(),
            ':temperature' => $consultation->getTemperature(),
            ':diagnosis' => $consultation->getDiagnosis(),
            ':treatment' => $consultation->getTreatment(),
            ':next_visit' => $consultation->getNextVisit(),
            ':consultation_fee' => $consultation->getConsultationFee(),
            ':status' => $consultation->getStatus(),
            ':observations' => $consultation->getObservations()
        ]);
    }

    // app/repositories/ConsultationRepository.php

    public function findById($id)
    {
        $sql = "SELECT c.*,
                   p.name AS pet_name,
                   cl.name AS client_name,
                   CONCAT(u.name, ' ', u.lastname1) AS vet_name
            FROM consultations c
            INNER JOIN pets p ON c.id_pet = p.id_pet
            INNER JOIN clients cl ON p.id_client = cl.id_client
            INNER JOIN users u ON c.id_user = u.id_user
            WHERE c.id_consultation = :id AND c.active = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new ConsultationModel($row) : null;
    }

    public function update(ConsultationModel $consultation)
    {
        $sql = "UPDATE consultations SET
                id_pet = :id_pet,
                weight = :weight,
                temperature = :temperature,
                diagnosis = :diagnosis,
                treatment = :treatment,
                next_visit = :next_visit,
                consultation_fee = :consultation_fee,
                status = :status,
                observations = :observations
            WHERE id_consultation = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $consultation->getIdConsultation(),
            ':id_pet' => $consultation->getIdPet(),
            ':weight' => $consultation->getWeight(),
            ':temperature' => $consultation->getTemperature(),
            ':diagnosis' => $consultation->getDiagnosis(),
            ':treatment' => $consultation->getTreatment(),
            ':next_visit' => $consultation->getNextVisit(),
            ':consultation_fee' => $consultation->getConsultationFee(),
            ':status' => $consultation->getStatus(),
            ':observations' => $consultation->getObservations()
        ]);
    }

    public function deactivate($id)
    {
        $sql = "UPDATE consultations SET active = 0 WHERE id_consultation = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Opcional: método para obtener mascotas con datos de cliente (para el selector en create)
    public function getPetsWithClients()
    {
        $sql = "SELECT p.id_pet, p.name AS pet_name,
                   cl.id_client, cl.name AS client_name
            FROM pets p
            INNER JOIN clients cl ON p.id_client = cl.id_client
            WHERE p.active = 1
            ORDER BY cl.name, p.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}