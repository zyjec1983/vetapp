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

    // =====================================================
    // BUSCAR USUARIO POR ID
    // =====================================================
    public function findById(int $id): ?array
    {
        // ******************* consulta para obtener usuario *******************
        $sql = "SELECT * FROM users WHERE id_user = :id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    // =====================================================
// ACTUALIZAR USUARIO COMPLETO
// =====================================================
    public function updateFull(
        int $id,
        string $name,
        ?string $middlename,
        string $lastname1,
        ?string $lastname2,
        string $email,
        ?string $phone,
        int $active,
        ?string $password = null
    ): bool {

        // ******************* SQL dinámico *******************
        $sql = "
        UPDATE users SET
            name = :name,
            middlename = :middlename,
            lastname1 = :lastname1,
            lastname2 = :lastname2,
            email = :email,
            phone = :phone,
            active = :active
    ";

        // ******************* incluir password si existe *******************
        if ($password !== null) {
            $sql .= ", password = :password";
        }

        $sql .= " WHERE id_user = :id";

        $stmt = $this->db->prepare($sql);

        $params = [
            'id' => $id,
            'name' => $name,
            'middlename' => $middlename,
            'lastname1' => $lastname1,
            'lastname2' => $lastname2,
            'email' => $email,
            'phone' => $phone,
            'active' => $active
        ];

        if ($password !== null) {
            $params['password'] = $password;
        }

        return $stmt->execute($params);
    }

    // =====================================================
// ACTUALIZAR ROLES DE USUARIO
// =====================================================
    public function updateRoles(int $id, array $roles): void
    {

        // ******************* eliminar roles actuales *******************
        $stmt = $this->db->prepare("DELETE FROM user_roles WHERE id_user = ?");
        $stmt->execute([$id]);

        // ******************* insertar nuevos roles *******************
        $stmt = $this->db->prepare("
        INSERT INTO user_roles (id_user, id_role)
        VALUES (?, ?)
    ");

        foreach ($roles as $roleId) {
            $stmt->execute([$id, $roleId]);
        }
    }

    // =====================================================
// OBTENER ROLES DE UN USUARIO
// =====================================================
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

    // =====================================================
    // DESACTIVAR USUARIO (SOFT DELETE)
    // =====================================================
    public function deactivate(int $id): bool
    {
        // ******************* actualizar campo active *******************
        $stmt = $this->db->prepare("
        UPDATE users 
        SET active = 0 
        WHERE id_user = ?
    ");

        return $stmt->execute([$id]);
    }

}
