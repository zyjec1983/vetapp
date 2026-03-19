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
}