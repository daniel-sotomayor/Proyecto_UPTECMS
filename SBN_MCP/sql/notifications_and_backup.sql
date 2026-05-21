-- ============================================================
-- Tabla de Notificaciones en Tiempo Real
-- ============================================================

CREATE TABLE IF NOT EXISTS notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    link VARCHAR(500) DEFAULT NULL,
    leida BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME DEFAULT NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_leida (leida),
    INDEX idx_created (created_at),
    INDEX idx_usuario_leida (usuario_id, leida),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla de Log de Backups
-- ============================================================

CREATE TABLE IF NOT EXISTS backups_log (
    id_backup INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    size INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'error') DEFAULT 'success',
    error_message TEXT DEFAULT NULL,
    INDEX idx_created (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
