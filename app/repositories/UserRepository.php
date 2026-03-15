<?php
/**
 * Location: Vetapp/app/repositories/UserRepository.php
 */

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
        // 1 Buscar usuario
        $sqlUser = "
            SELECT 
                id_user,
                email,
                password,
                name,
                lastname1,
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

        // 2 Buscar roles del usuario
        $sqlRoles = "
            SELECT r.name
            FROM roles r
            INNER JOIN user_roles ur ON ur.id_role = r.id_role
            WHERE ur.id_user = :id_user
        ";

        $stmtRoles = $this->db->prepare($sqlRoles);
        $stmtRoles->execute(['id_user' => $user['id_user']]);

        $roles = $stmtRoles->fetchAll(PDO::FETCH_COLUMN);

        // 3 Armar estructura final
        return [
            'id_user' => (int) $user['id_user'],
            'email' => $user['email'],
            'password' => $user['password'],
            'name' => $user['name'],
            'lastname1' => $user['lastname1'],
            'active' => (bool) $user['active'],
            'roles' => $roles
        ];
    }

    // ***** BUsca todos los usuarios *****
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ******************* crear nuevo usuario *******************
    // ******************* insertar usuario *******************
    public function create($data)
    {

        $sql = "INSERT INTO users 
    (email,password,name,middlename,lastname1,lastname2,identification,phone)
    VALUES
    (:email,:password,:name,:middlename,:lastname1,:lastname2,:identification,:phone)";

        $stmt = $this->db->prepare($sql);

        $stmt->execute($data);

        // ******************* devolver id del usuario *******************
        return $this->db->lastInsertId();

    }

    // ******************* asignar roles a usuario *******************
    public function assignRoles($userId, $roles)
    {

        $sql = "INSERT INTO user_roles (id_user,id_role) VALUES (:user,:role)";

        $stmt = $this->db->prepare($sql);

        foreach ($roles as $role) {

            $stmt->execute([
                'user' => $userId,
                'role' => $role
            ]);

        }

    }


}
