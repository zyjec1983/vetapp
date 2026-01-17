<?php
/**
 * Location: vetapp/app/repositories/UserRepository.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository {

    private PDO $db;

    public function __construct(PDO $db){
        $this->db = $db;
    }

    public function findByEmail(string $email): ?User {

        $sql = "
            SELECT 
                id_user,
                email,
                password,
                name,
                middlename,
                lastname1,
                lastname2,
                role,
                phone,
                active
            FROM users
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', strtolower($email), PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $user = new User();

        // 🔒 Hydration controlada
        $user->setId((int)$row['id_user']);
        $user->setEmail($row['email']);
        $user->setHashedPassword($row['password']);
        $user->setName($row['name']);
        $user->setMiddlename($row['middlename']);
        $user->setLastname1($row['lastname1']);
        $user->setLastname2($row['lastname2']);
        $user->setRole($row['role']);
        $user->setPhone($row['phone']);
        $user->setActive((bool)$row['active']);

        return $user;
    }
}
