-- Migration: Add whatsapp_phone column to company_settings
ALTER TABLE company_settings ADD COLUMN whatsapp_phone VARCHAR(20) DEFAULT NULL AFTER phone;
