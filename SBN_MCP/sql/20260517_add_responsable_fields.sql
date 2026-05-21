-- ============================================================
-- Migración: Añadir campos de responsable (cédula y foto)
-- Fecha: 2026-05-17
-- ============================================================

ALTER TABLE bienes ADD COLUMN responsable_cedula VARCHAR(20) NULL AFTER responsable_id;
ALTER TABLE bienes ADD COLUMN responsable_foto_path VARCHAR(500) NULL AFTER responsable_cedula;

-- Crear índices para búsquedas
CREATE INDEX idx_bienes_responsable_cedula ON bienes(responsable_cedula);
CREATE INDEX idx_bienes_responsable_foto ON bienes(responsable_foto_path);
