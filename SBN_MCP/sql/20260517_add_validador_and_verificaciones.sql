-- Migración: Añadir rol `validador_inventario` y tabla `verificaciones`
-- Fecha: 2026-05-17

-- 1) Insertar rol si no existe
INSERT INTO roles (nombre, descripcion, activo)
SELECT 'validador_inventario',
       'Validador de inventario — permiso para marcar/verificar inventario semestral',
       1
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE nombre = 'validador_inventario');

-- 2) Crear tabla de verificaciones (registro de verificaciones físicas/semestrales)
CREATE TABLE IF NOT EXISTS verificaciones (
  id_verificacion INT AUTO_INCREMENT PRIMARY KEY,
  bien_id INT NOT NULL,
  usuario_id INT NOT NULL,
  fecha_verificacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  tipo VARCHAR(50) NOT NULL DEFAULT 'semestral',
  observaciones TEXT,
  INDEX (bien_id),
  INDEX (usuario_id),
  CONSTRAINT fk_verif_bien FOREIGN KEY (bien_id) REFERENCES bienes(id_bien) ON DELETE CASCADE,
  CONSTRAINT fk_verif_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
