-- ============================================================
-- Migración 004: Notas de Crédito Electrónicas
-- ============================================================
-- Agrega tablas para soportar Notas de Crédito SRI (tipo 04)
-- con devolución de productos al inventario.
-- ============================================================

USE vetapp1;

CREATE TABLE IF NOT EXISTS credit_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sale INT NOT NULL COMMENT 'Venta original',
    id_original_invoice INT NOT NULL COMMENT 'Factura electrónica original',
    company_id INT NOT NULL COMMENT 'Empresa que emite la NC',
    id_user INT NOT NULL COMMENT 'Usuario que creó la NC',
    numero_nota_credito VARCHAR(17) NOT NULL COMMENT '001-001-000000001',
    clave_acceso VARCHAR(49) COMMENT 'Clave de acceso SRI (49 dígitos)',
    numero_autorizacion VARCHAR(50) COMMENT 'Número de autorización del SRI',
    estado_sri VARCHAR(20) DEFAULT 'pendiente' COMMENT 'pendiente, enviada, autorizada, rechazada',
    motivo VARCHAR(100) NOT NULL COMMENT 'Devolución total, parcial, cambio de producto',
    subtotal DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    tax_total DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    mensaje_sri TEXT COMMENT 'Mensaje de respuesta del SRI',
    xml_path VARCHAR(255) COMMENT 'Ruta al XML generado',
    xml_firmado_path VARCHAR(255) COMMENT 'Ruta al XML firmado',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_sale) REFERENCES sales(id_sale),
    FOREIGN KEY (id_original_invoice) REFERENCES electronic_invoices(id),
    FOREIGN KEY (company_id) REFERENCES company_settings(id),
    FOREIGN KEY (id_user) REFERENCES users(id_user),
    INDEX idx_credit_notes_sale (id_sale),
    INDEX idx_credit_notes_estado (estado_sri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS credit_note_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_credit_note INT NOT NULL COMMENT 'NC padre',
    id_medication INT NOT NULL,
    id_batch INT NOT NULL COMMENT 'Lote original del que se devuelve',
    quantity INT NOT NULL COMMENT 'Cantidad devuelta',
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (id_credit_note) REFERENCES credit_notes(id),
    FOREIGN KEY (id_medication) REFERENCES medications(id_medication),
    FOREIGN KEY (id_batch) REFERENCES medication_batches(id_batch)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
