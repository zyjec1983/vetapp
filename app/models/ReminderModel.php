<?php
/**
 * Location: vetapp/app/models/ReminderModel.php
 * Representa un recordatorio (vacuna, consulta, pago)
 */

class ReminderModel
{
    private $id;
    private $reminder_type;
    private $id_pet;
    private $id_client;
    private $reminder_date;
    private $message;
    private $sent;
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

    // Getters y setters (puedes generarlos con tu IDE o a mano)
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getReminderType() { return $this->reminder_type; }
    public function setReminderType($type) { $this->reminder_type = $type; }

    public function getIdPet() { return $this->id_pet; }
    public function setIdPet($id) { $this->id_pet = $id; }

    public function getIdClient() { return $this->id_client; }
    public function setIdClient($id) { $this->id_client = $id; }

    public function getReminderDate() { return $this->reminder_date; }
    public function setReminderDate($date) { $this->reminder_date = $date; }

    public function getMessage() { return $this->message; }
    public function setMessage($msg) { $this->message = $msg; }

    public function getSent() { return $this->sent; }
    public function setSent($sent) { $this->sent = (bool)$sent; }

    public function getCreatedAt() { return $this->created_at; }
    public function setCreatedAt($date) { $this->created_at = $date; }
}