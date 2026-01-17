<?php
/**
 * Location: vetapp/app/models/User.php
 * Responsibility:
 * - Representa la entidad User (DOMINIO)
 * - Contiene reglas de negocio del usuario
 * - NO accede a base de datos
 */

class User
{
    // ************* PROPIEDADES *************
    private int $id_user;
    private string $email;
    private string $password; // password HASHEADO
    private string $name;
    private ?string $middlename; // opcional en la base de datos tienen "NULL"
    private string $lastname1;
    private ?string $lastname2; // opcional en la base de datos tienen "NULL"
    private string $role;
    private ?string $phone;  // opcional en la base de datos tienen "NULL"
    private bool $active;

    // ************* CONSTRUCTOR *************
    public function __construct()
    {
        // Valores por defecto
        $this->id_user = 0;
        $this->email = '';
        $this->password = '';
        $this->name = '';
        $this->middlename = null;
        $this->lastname1 = '';
        $this->lastname2 = null;
        $this->role = 'veterinarian';
        $this->phone = null;
        $this->active = true;
    }

    // =====================================================
    // GETTERS (solo lectura)
    // =====================================================

    public function getId(): int
    {
        return $this->id_user;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMiddlename(): ?string
    {
        return $this->middlename;
    }

    public function getLastname1(): string
    {
        return $this->lastname1;
    }

    public function getLastname2(): ?string
    {
        return $this->lastname2;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    // =====================================================
    // SETTERS DE DOMINIO (validan reglas del negocio)
    // =====================================================

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Formato de email inválido');
        }

        $this->email = strtolower(trim($email));
    }

    /**
     * Recibe contraseña EN TEXTO PLANO
     * - Se usa al CREAR o CAMBIAR contraseña
     */
    public function setPassword(string $plainPassword): void
    {
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function setMiddlename(?string $middlename): void
    {
        $this->middlename = $middlename ? trim($middlename) : null;
    }

    public function setLastname1(string $lastname1): void
    {
        $this->lastname1 = trim($lastname1);
    }

    public function setLastname2(?string $lastname2): void
    {
        $this->lastname2 = $lastname2 ? trim($lastname2) : null;
    }

    /**
     * Control estricto de roles permitidos
     */
    public function setRole(string $role): void
    {
        $allowedRoles = ['admin', 'veterinarian', 'pharmacy'];

        if (!in_array($role, $allowedRoles, true)) {
            throw new InvalidArgumentException('Invalid role');
        }

        $this->role = $role;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone ? trim($phone) : null;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    // =====================================================
    // BUSINESS LOGIC (reglas del dominio)
    // =====================================================

    /**
     * Verifica contraseña
     * - Se usa en AuthController
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

    /**
     * Nombre completo formateado
     */
    public function getFullName(): string
    {
        return trim(
            $this->name . ' ' .
            ($this->middlename ?? '') . ' ' .
            $this->lastname1 . ' ' .
            ($this->lastname2 ?? '')
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // =====================================================
    // SETTERS INTERNOS / HIDRATACIÓN
    // ⚠️ SOLO deben ser usados por el Repository
    // =====================================================

    /**
     * Setea ID desde la base de datos
     */
    public function setId(int $id): void
    {
        $this->id_user = $id;
    }

    /**
     * Setea password YA HASHEADO (desde BD)
     * ⚠️ NO usar con texto plano
     */
    public function setHashedPassword(string $hash): void
    {
        $this->password = $hash;
    }
}
