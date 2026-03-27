<?php
/**
 * Location: vetapp/app/repositories/ActiveIngredientRepository.php
 */

class ActiveIngredientRepository
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT id_active, name FROM active_ingredients ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}