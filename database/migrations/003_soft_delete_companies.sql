-- ============================================================
-- Migración 003: Soft Delete para Empresas
-- ============================================================
-- Agrega columna deleted_at a company_settings para permitir
-- eliminación lógica sin perder datos en la base de datos.
-- ============================================================

USE vetapp1;

ALTER TABLE company_settings
  ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER activo,
  ADD INDEX idx_deleted_at (deleted_at);
