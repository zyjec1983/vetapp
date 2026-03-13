<?php

require_once __DIR__ . '/../config/Database.php';

class DashboardRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ======================================
    // TOTAL CLIENTES
    // ======================================
    public function countClients(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM clients");
        return (int) $stmt->fetchColumn();
    }

    // ======================================
    // TOTAL MASCOTAS
    // ======================================
    public function countPets(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM pets");
        return (int) $stmt->fetchColumn();
    }

    // ======================================
    // CONSULTAS DE HOY
    // ======================================
    public function todayConsultations(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*) 
            FROM consultations
            WHERE DATE(consultation_date) = CURDATE()
        ");

        return (int) $stmt->fetchColumn();
    }

    // ======================================
    // VENTAS DE HOY
    // ======================================
    public function todaySales(): float
    {
        $stmt = $this->db->query("
            SELECT COALESCE(SUM(total),0)
            FROM sales
            WHERE DATE(sale_date) = CURDATE()
        ");

        return (float) $stmt->fetchColumn();
    }

    // ======================================
    // CONSULTAS RECIENTES
    // ======================================
    public function recentConsultations(): array
    {
        $stmt = $this->db->query("
            SELECT 
                c.id_consultation,
                c.consultation_date,
                p.name AS pet_name,
                cl.name AS client_name
            FROM consultations c
            INNER JOIN pets p ON p.id_pet = c.id_pet
            INNER JOIN clients cl ON cl.id_client = p.id_client
            ORDER BY c.consultation_date DESC
            LIMIT 5
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ======================================
    // VENTAS RECIENTES
    // ======================================
    public function recentSales(): array
    {
        $stmt = $this->db->query("
            SELECT 
                id_sale,
                sale_date,
                total
            FROM sales
            ORDER BY sale_date DESC
            LIMIT 5
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ======================================
    // MEDICAMENTOS CON POCO STOCK
    // ======================================
    public function lowStockMedications(): array
    {
        $stmt = $this->db->query("
            SELECT 
                name,
                stock
            FROM medications
            WHERE stock <= 5
            ORDER BY stock ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ======================================
    // VENTAS POR MES (GRAFICA)
    // ======================================
    public function monthlySales(): array
    {
        $stmt = $this->db->query("
        SELECT 
            MONTH(sale_date) as month,
            SUM(total) as total
        FROM sales
        WHERE YEAR(sale_date) = YEAR(CURDATE())
        GROUP BY MONTH(sale_date)
    ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}