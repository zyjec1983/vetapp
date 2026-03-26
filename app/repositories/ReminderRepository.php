<?php
/**
 * Location: vetapp/app/repositories/ReminderRepository.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/ReminderModel.php';

class ReminderRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(ReminderModel $reminder)
    {
        $sql = "INSERT INTO reminders 
                (reminder_type, id_pet, id_client, reminder_date, message, sent)
                VALUES 
                (:type, :id_pet, :id_client, :reminder_date, :message, :sent)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':type'          => $reminder->getReminderType(),
            ':id_pet'        => $reminder->getIdPet(),
            ':id_client'     => $reminder->getIdClient(),
            ':reminder_date' => $reminder->getReminderDate(),
            ':message'       => $reminder->getMessage(),
            ':sent'          => $reminder->getSent() ? 1 : 0
        ]);
    }

    public function getByDate($date)
    {
        $sql = "SELECT r.*,
                       p.name AS pet_name,
                       p.species AS pet_species,
                       c.name AS client_name,
                       c.phone AS client_phone
                FROM reminders r
                INNER JOIN pets p ON r.id_pet = p.id_pet
                INNER JOIN clients c ON r.id_client = c.id_client
                WHERE r.reminder_date = :date
                ORDER BY r.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date' => $date]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $reminders = [];
        foreach ($rows as $row) {
            $reminder = new ReminderModel($row);
            // Añadir campos extras para la vista
            $reminder->pet_name     = $row['pet_name'];
            $reminder->pet_species  = $row['pet_species'];
            $reminder->client_name  = $row['client_name'];
            $reminder->client_phone = $row['client_phone'];
            $reminders[] = $reminder;
        }
        return $reminders;
    }
}