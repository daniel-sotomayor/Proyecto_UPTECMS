<?php
/**
 * SQL para tabla de recuperacion de contraseña
 * password_resets - Almacena codigos de verificacion
 */

-- Tabla para recuperacion de contraseña
CREATE TABLE IF NOT EXISTS password_resets (
    id_reset INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used_at DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    intentos_dia INT DEFAULT 1,
    fecha_intento DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
    INDEX idx_codigo (codigo),
    INDEX idx_expires (expires_at),
    INDEX idx_usuario_fecha (usuario_id, fecha_intento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vista para contar intentos de recuperacion por usuario y dia
CREATE OR REPLACE VIEW v_intentos_recuperacion AS
SELECT 
    usuario_id,
    fecha_intento,
    COUNT(*) as total_intentos
FROM password_resets
WHERE fecha_intento = CURRENT_DATE
GROUP BY usuario_id, fecha_intento;
