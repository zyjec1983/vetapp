-- ============================================================
-- Migración 001: Integración Facturación Electrónica SRI
-- ============================================================
-- Ejecutar en phpMyAdmin o consola MySQL para agregar
-- soporte de facturación electrónica a VetApp
-- ============================================================

USE vetapp1;

-- 1. Tabla: configuraciones de empresa (múltiples RUCs)
CREATE TABLE IF NOT EXISTS company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ruc VARCHAR(13) NOT NULL UNIQUE COMMENT 'RUC del establecimiento',
    nombre_comercial VARCHAR(150) NOT NULL COMMENT 'Nombre que aparece en la factura',
    nombre_legal VARCHAR(150) NOT NULL COMMENT 'Razón social legal',
    direccion VARCHAR(250) COMMENT 'Dirección del establecimiento',
    telefono VARCHAR(20) COMMENT 'Teléfono de contacto',
    email VARCHAR(100) COMMENT 'Correo electrónico',
    obligado_contabilidad ENUM('SI','NO') DEFAULT 'SI' COMMENT 'Obligado a llevar contabilidad',
    contribuyente_especial VARCHAR(50) COMMENT 'Nombre si es contribuyente especial, vacío si no',
    establecimiento_codigo VARCHAR(3) DEFAULT '001' COMMENT 'Código del establecimiento (3 dígitos)',
    punto_emision VARCHAR(3) DEFAULT '001' COMMENT 'Código del punto de emisión (3 dígitos)',
    certificado_path VARCHAR(255) COMMENT 'Ruta al certificado .p12',
    ambiente ENUM('pruebas','produccion') DEFAULT 'pruebas' COMMENT 'Ambiente de facturación',
    activo TINYINT(1) DEFAULT 1 COMMENT '1 = Activa, 0 = Inactiva',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla: facturas electrónicas (datos SRI por venta)
CREATE TABLE IF NOT EXISTS electronic_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sale INT NOT NULL COMMENT 'Referencia a la venta',
    company_id INT NOT NULL COMMENT 'Empresa/RUC usado para facturar',
    numero_factura VARCHAR(17) NOT NULL UNIQUE COMMENT 'Formato: 001-001-000000001',
    clave_acceso VARCHAR(49) COMMENT 'Clave de acceso SRI (49 dígitos)',
    numero_autorizacion VARCHAR(50) COMMENT 'Número de autorización del SRI',
    estado_sri VARCHAR(20) DEFAULT 'pendiente' COMMENT 'pendiente, enviada, autorizada, rechazada',
    mensaje_sri TEXT COMMENT 'Mensaje de respuesta del SRI',
    fecha_envio DATETIME COMMENT 'Cuándo se envió al SRI',
    fecha_autorizacion DATETIME COMMENT 'Cuándo el SRI autorizó',
    xml_path VARCHAR(255) COMMENT 'Ruta al archivo XML generado',
    xml_firmado_path VARCHAR(255) COMMENT 'Ruta al XML firmado',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sale) REFERENCES sales(id_sale) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES company_settings(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Agregar campos a clients para facturación electrónica
ALTER TABLE clients ADD COLUMN IF NOT EXISTS ruc_cedula VARCHAR(13) COMMENT 'RUC o Cédula del cliente';
ALTER TABLE clients ADD COLUMN IF NOT EXISTS tipo_identificacion VARCHAR(10) DEFAULT 'Cédula' COMMENT 'RUC, Cédula, Pasaporte, Consumidor Final';
ALTER TABLE clients ADD COLUMN IF NOT EXISTS direccion TEXT COMMENT 'Dirección del cliente';
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email VARCHAR(100) COMMENT 'Email del cliente para enviar factura';

-- 4. Agregar campo company_id a sales (si no existe)
ALTER TABLE sales ADD COLUMN IF NOT EXISTS company_id INT COMMENT 'Empresa que facturó esta venta';
ALTER TABLE sales ADD FOREIGN KEY IF NOT EXISTS (company_id) REFERENCES company_settings(id);

-- 5. Agregar tax_total a sales (si no existe)
ALTER TABLE sales ADD COLUMN IF NOT EXISTS tax_total DECIMAL(10,2) DEFAULT 0 COMMENT 'Total de IVA';

-- 6. Agregar campo batch_id a sale_details (si no existe)
ALTER TABLE sale_details ADD COLUMN IF NOT EXISTS id_batch INT COMMENT 'Referencia al lote vendido';

-- ============================================================
-- DATOS DE EJEMPLO: 2 empresas de la veterinaria
-- ============================================================
INSERT INTO company_settings (ruc, nombre_comercial, nombre_legal, direccion, telefono, email, obligado_contabilidad, ambiente) VALUES
('1234567890001', 'VetApp Farmacia Principal', 'Veterinaria Farmacia Principal C.A.', 'Av. Principal 123 y Calle Secundaria, Quito - Ecuador', '(02) 234-5678', 'principal@vetapp.com', 'SI', 'pruebas'),
('9876543210001', 'VetApp Sucursal Norte', 'Veterinaria Sucursal Norte C.A.', 'Calle Norte 456 y Avenida Central, Quito - Ecuador', '(02) 876-5432', 'norte@vetapp.com', 'SI', 'pruebas')
ON DUPLICATE KEY UPDATE ruc = VALUES(ruc);

-- 7. Actualizar clientes existentes para tener tipo_identificacion por defecto
UPDATE clients SET tipo_identificacion = 'Cédula' WHERE tipo_identificacion IS NULL OR tipo_identificacion = '';
