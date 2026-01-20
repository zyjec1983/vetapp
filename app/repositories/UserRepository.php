<?php

require_once __DIR__ . '/../config/Database.php';

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // =====================================================
    // BUSCAR USUARIO POR EMAIL + ROLES
    // =====================================================
    public function findByEmailWithRoles(string $email): ?array
    {
        // 1️⃣ Buscar usuario
        $sqlUser = "
            SELECT 
                id_user,
                email,
                password,
                name,
                active
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sqlUser);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        // 2️⃣ Buscar roles del usuario
        $sqlRoles = "
            SELECT r.name
            FROM roles r
            INNER JOIN user_roles ur ON ur.id_role = r.id_role
            WHERE ur.id_user = :id_user
        ";

        $stmtRoles = $this->db->prepare($sqlRoles);
        $stmtRoles->execute(['id_user' => $user['id_user']]);

        $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

        // 3️⃣ Armar estructura final
        return [
            'id_user' => (int)$user['id_user'],
            'email'   => $user['email'],
            'password'=> $user['password'],
            'name'    => $user['name'],
            'active'  => (bool)$user['active'],
            'roles'   => $roles
        ];
    }
}
