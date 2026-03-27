<?php
/**
 * Location: vetapp/app/config/Database.php
 * Clase singleton para la conexión PDO.
 * Lee los parámetros de las constantes definidas en config.php.
 */

class Database
{
    private static $instance = null;
    private $connection;

    // Valores por defecto estan en config.php
    private $host;
    private $port;
    private $db;
    private $user;
    private $pass;
    private $charset;

    private function __construct()
    {
        // Usar constantes si están definidas
        if (defined('DB_HOST')) $this->host = DB_HOST;
        if (defined('DB_PORT')) $this->port = DB_PORT;
        if (defined('DB_NAME')) $this->db = DB_NAME;
        if (defined('DB_USER')) $this->user = DB_USER;
        if (defined('DB_PASS')) $this->pass = DB_PASS;
        if (defined('DB_CHARSET')) $this->charset = DB_CHARSET;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Si falla, mostrará este mensaje
            die('Error de conexión a la base de datos. Por favor, contacta al administrador.');
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}