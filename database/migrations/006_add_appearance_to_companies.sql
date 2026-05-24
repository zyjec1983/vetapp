-- Migration: Add app_title and logo_path to company_settings
ALTER TABLE company_settings
  ADD COLUMN app_title VARCHAR(100) DEFAULT NULL AFTER whatsapp_phone,
  ADD COLUMN logo_path VARCHAR(255) DEFAULT NULL AFTER app_title;
