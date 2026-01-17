<?php
/**
 * Location: vetapp/app/config/Database.php
 */

class Database{
    private static $instance = null;
    private $connection;

    private $host = 'localhost';
    private $db = 'vetapp';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    private function __construct(){
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch (PDOException $e) {
            die('Database connection error');
        }        
    }

    public static function getInstance(){
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(){
        return $this->connection;
    }
}

?>