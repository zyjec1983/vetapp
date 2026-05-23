-- ============================================================
-- Migración 002: Placeholder Consumidor Final
-- ============================================================
-- Inserta un cliente fantasma para ventas sin cliente registrado.
-- El SRI usa RUC 9999999999999 para Consumidor Final.
-- ============================================================

USE vetapp1;

INSERT INTO clients (name, lastname1, identification, phone, email, address, created_at)
VALUES ('CONSUMIDOR', 'FINAL', '9999999999999', '', '', 'Consumidor Final - Ventas menores a $150', NOW())
ON DUPLICATE KEY UPDATE identification = VALUES(identification);

-- Nota: El ID del cliente CF se puede consultar con:
-- SELECT id_client FROM clients WHERE identification = '9999999999999';
