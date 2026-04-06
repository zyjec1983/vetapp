<?php
/**
 * Location: vetapp/app/repositories/MedicationRepository.php
 */

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/MedicationModel.php';

class MedicationRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todos los medicamentos con su stock calculado y nombre del principio activo
     */
    public function getAll()
    {
        $sql = "SELECT 
                    m.*,
                    a.name AS active_name,
                    COALESCE(SUM(b.quantity_remaining), 0) AS stock_total
                FROM medications m
                LEFT JOIN active_ingredients a ON m.id_active = a.id_active
                LEFT JOIN medication_batches b ON m.id_medication = b.id_medication
                WHERE m.active = 1
                GROUP BY m.id_medication
                ORDER BY m.name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $medications = [];
        foreach ($rows as $row) {
            $med = new MedicationModel($row);
            $med->setActiveName($row['active_name']);
            $med->setStockTotal($row['stock_total']);
            $medications[] = $med;
        }
        return $medications;
    }

    /**
     * Obtiene un medicamento por su ID con stock calculado
     */
    public function findById($id)
    {
        $sql = "SELECT m.id_medication, m.code, m.name, m.id_active, m.category, m.description,
                   m.minimum_stock, m.sale_price, m.location, m.active, m.created_at, m.taxable,
                   a.name AS active_name,
                   COALESCE(SUM(b.quantity_remaining), 0) AS stock_total
            FROM medications m
            LEFT JOIN active_ingredients a ON m.id_active = a.id_active
            LEFT JOIN medication_batches b ON m.id_medication = b.id_medication
            WHERE m.id_medication = :id
            GROUP BY m.id_medication, a.name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $med = new MedicationModel($row);
            $med->setActiveName($row['active_name']);
            $med->setStockTotal($row['stock_total']);
            return $med;
        }
        return null;
    }

    /**
     * Crear nuevo medicamento
     */
    public function create(MedicationModel $med)
    {
        $sql = "INSERT INTO medications (
                code, name, id_active, category, description,
                minimum_stock, sale_price, location, active, taxable
            ) VALUES (
                :code, :name, :id_active, :category, :description,
                :minimum_stock, :sale_price, :location, :active, :taxable
            )";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':code' => $med->getCode(),
            ':name' => $med->getName(),
            ':id_active' => $med->getIdActive(),
            ':category' => $med->getCategory(),
            ':description' => $med->getDescription(),
            ':minimum_stock' => $med->getMinimumStock(),
            ':sale_price' => $med->getSalePrice(),
            ':location' => $med->getLocation(),
            ':active' => $med->getActive() ? 1 : 0,
            ':taxable' => $med->getTaxable() ? 1 : 0
        ]);
    }

    /**
     * Actualizar medicamento
     */
    public function update(MedicationModel $med)
{
    $sql = "UPDATE medications SET
                code = :code,
                name = :name,
                id_active = :id_active,
                category = :category,
                description = :description,
                minimum_stock = :minimum_stock,
                sale_price = :sale_price,
                location = :location,
                active = :active,
                taxable = :taxable
            WHERE id_medication = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
        ':id'            => $med->getIdMedication(),
        ':code'          => $med->getCode(),
        ':name'          => $med->getName(),
        ':id_active'     => $med->getIdActive(),
        ':category'      => $med->getCategory(),
        ':description'   => $med->getDescription(),
        ':minimum_stock' => $med->getMinimumStock(),
        ':sale_price'    => $med->getSalePrice(),
        ':location'      => $med->getLocation(),
        ':active'        => $med->getActive() ? 1 : 0,
        ':taxable'       => $med->getTaxable() ? 1 : 0
    ]);
}

    /**
     * Desactivar medicamento (soft delete)
     */
    public function deactivate($id)
    {
        $sql = "UPDATE medications SET active = 0 WHERE id_medication = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function reactivate($id)
{
    $sql = "UPDATE medications SET active = 1 WHERE id_medication = :id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':id' => $id]);
}

/**
 * Reactiva productos
 * @return MedicationModel[]
 */
public function getAllInactive()
{
    $sql = "SELECT m.*, a.name AS active_name,
                   COALESCE(SUM(b.quantity_remaining), 0) AS stock_total
            FROM medications m
            LEFT JOIN active_ingredients a ON m.id_active = a.id_active
            LEFT JOIN medication_batches b ON m.id_medication = b.id_medication
            WHERE m.active = 0
            GROUP BY m.id_medication
            ORDER BY m.name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $medications = [];
    foreach ($rows as $row) {
        $med = new MedicationModel($row);
        $med->setActiveName($row['active_name']);
        $med->setStockTotal($row['stock_total']);
        $medications[] = $med;
    }
    return $medications;
}

    /**
     * Obtener medicamentos con stock bajo (stock_total <= minimum_stock)
     * Utilizado en dashboard
     */
    public function getLowStock()
    {
        $sql = "SELECT 
                    m.id_medication,
                    m.name,
                    m.minimum_stock,
                    COALESCE(SUM(b.quantity_remaining), 0) AS stock
                FROM medications m
                LEFT JOIN medication_batches b ON m.id_medication = b.id_medication
                WHERE m.active = 1
                GROUP BY m.id_medication
                HAVING stock <= m.minimum_stock
                ORDER BY stock ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // app/repositories/MedicationRepository.php  

    public function search($term)
    {
        $searchTerm = '%' . $term . '%';
        $sql = "SELECT m.id_medication, m.code, m.name, m.sale_price, m.taxable,
                   COALESCE(SUM(b.quantity_remaining), 0) AS stock
            FROM medications m
            LEFT JOIN medication_batches b ON m.id_medication = b.id_medication
            WHERE m.active = 1
              AND (m.code LIKE ? OR m.name LIKE ?)
            GROUP BY m.id_medication
            ORDER BY m.name ASC
            LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCode($code)
    {
        $sql = "SELECT * FROM medications WHERE code = :code";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}