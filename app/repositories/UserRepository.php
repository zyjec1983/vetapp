<?php

class UserRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // =====================================================
    // FIND USER BY EMAIL (WITH ROLES)
    // =====================================================
    public function findByEmail(string $email): ?array
    {
        // 1️⃣ Obtener usuario
        $sql = "
            SELECT 
                u.id_user,
                u.email,
                u.password,
                u.name,
                u.middlename,
                u.lastname1,
                u.lastname2,
                u.phone,
                u.active
            FROM users u
            WHERE u.email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return null;
        }

        // 2️⃣ Obtener roles del usuario
        $rolesSql = "
            SELECT r.name
            FROM roles r
            INNER JOIN user_roles ur ON ur.id_role = r.id_role
            WHERE ur.id_user = :id_user
        ";

        $rolesStmt = $this->db->prepare($rolesSql);
        $rolesStmt->bindValue(':id_user', $user['id_user']);
        $rolesStmt->execute();

        $roles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN);

        // 3️⃣ Agregar roles al array de usuario
        $user['roles'] = $roles;

        return $user;
    }
}
