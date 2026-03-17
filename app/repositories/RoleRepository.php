<?php

class RoleRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM roles");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRolesByUserId(int $id): array
    {
        $stmt = $this->db->prepare("
        SELECT id_role
        FROM user_roles
        WHERE id_user = ?
    ");

        $stmt->execute([$id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}