-- ============================================================
-- BASE DE DATOS: hospital_bienes (Versión FINAL 2026-05-18)
-- Sistema de Gestión de Bienes Nacionales - MCP
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS hospital_bienes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE hospital_bienes;

-- ================= TABLAS PRINCIPALES ======================

-- Tabla de roles
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id_rol      INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    permisos    JSON NOT NULL,
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (nombre, descripcion, permisos) VALUES
('administrador',
 'Administrador del sistema — acceso total y gestión de usuarios',
 '["usuarios_crear","usuarios_editar","usuarios_eliminar","usuarios_ver","bienes_crear","bienes_editar","bienes_eliminar","bienes_ver","movimientos_crear","movimientos_ver","movimientos_aprobar","actas_generar","reportes_generar","configuracion","auditoria_ver"]'),
('gerencia_bn',
 'Gerente de Bienes Nacionales — asigna Nro. de Bien, registra, incorpora y genera actas',
 '["bienes_crear","bienes_editar","bienes_ver","movimientos_crear","movimientos_ver","movimientos_aprobar","actas_generar","reportes_generar","auditoria_ver"]'),
('controlador_inventario',
 'Controlador de Inventario — lleva el control global y puede corregir el inventario',
 '["bienes_editar","bienes_ver","movimientos_ver","reportes_generar","auditoria_ver"]'),
('registrador',
 'Registrador — solo carga información, no puede editar',
 '["bienes_crear","bienes_ver","movimientos_ver"]'),
('validador_inventario',
 'Validador de inventario — permiso para marcar/verificar inventario semestral',
 '["bienes_ver","verificaciones_marcar","verificaciones_ver"]');

-- Tabla de usuarios
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id_usuario       INT AUTO_INCREMENT PRIMARY KEY,
    id_rol           INT,
    cedula           VARCHAR(20)  NOT NULL UNIQUE,
    username         VARCHAR(50)  NOT NULL UNIQUE,
    primer_nombre    VARCHAR(100) NOT NULL,
    segundo_nombre   VARCHAR(100) NOT NULL DEFAULT '',
    primer_apellido  VARCHAR(100) NOT NULL,
    segundo_apellido VARCHAR(100) NOT NULL DEFAULT '',
    nombre_completo  VARCHAR(255) NOT NULL DEFAULT '',
    email            VARCHAR(255) NOT NULL UNIQUE,
    password_hash    VARCHAR(255) NOT NULL,
    telefono         VARCHAR(20),
    cargo            VARCHAR(100),
    ultimo_acceso    TIMESTAMP    NULL,
    intentos_fallidos INT         NOT NULL DEFAULT 0,
    bloqueado_hasta  TIMESTAMP    NULL,
    primer_login     TINYINT(1)   NOT NULL DEFAULT 1,
    activo           TINYINT(1)   NOT NULL DEFAULT 1,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_usuarios_username ON usuarios(username);
CREATE INDEX idx_usuarios_cedula   ON usuarios(cedula);
CREATE INDEX idx_usuarios_email    ON usuarios(email);
CREATE INDEX idx_usuarios_rol      ON usuarios(id_rol);
INSERT INTO usuarios (id_rol, cedula, username, primer_nombre, primer_apellido, nombre_completo, email, password_hash, cargo, primer_login, activo)
VALUES (1, 'V-00000001', 'admin', 'Administrador', 'BN', 'Administrador BN', 'admin@mcp.gob.ve', '$2y$12$/P/8pcNO32tcJpgNe22BMO/BvHxexoZazjnGABQuH9sc9KDV6qc52', 'Administrador del Sistema', 1, 1);

-- Tabla de areas
DROP TABLE IF EXISTS areas;
CREATE TABLE areas (
    id_area        INT AUTO_INCREMENT PRIMARY KEY,
    nombre_area    VARCHAR(100) NOT NULL,
    descripcion    TEXT,
    edificio       VARCHAR(50),
    piso           TINYINT      COMMENT '-1=Sótano, 0=Planta Baja, 1..N=Pisos',
    responsable_id INT,
    area_padre_id  INT,
    activa         TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (area_padre_id)  REFERENCES areas(id_area)       ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_areas_edificio ON areas(edificio);
-- (Áreas de ejemplo, puedes agregar más según tu hospital)
INSERT INTO areas (nombre_area, descripcion, edificio, piso) VALUES
('Mantenimiento Técnico', 'Taller de mantenimiento técnico', 'Principal', -1),
('Lavandería', 'Servicio de lavandería', 'Principal', -1);

-- Tabla de estados
DROP TABLE IF EXISTS estados;
CREATE TABLE estados (
    id_estado   INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL,
    descripcion TEXT,
    color       VARCHAR(7)   COMMENT 'Color HEX para la UI',
    es_baja     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO estados (id_estado, nombre, descripcion, color, es_baja) VALUES
(1, 'Operativo', 'Bien en funcionamiento normal', '#28a745', 0),
(2, 'Inoperativo', 'Bien que no funciona pero es recuperable', '#ffc107', 0),
(3, 'En Resguardo', 'Bien resguardado temporalmente', '#17a2b8', 0),
(4, 'Chatarra', 'Bien sin posibilidad de recuperación', '#dc3545', 1),
(5, 'Desincorporado', 'Bien dado de baja oficialmente', '#6c757d', 1);

-- Tabla de tipos de bien
DROP TABLE IF EXISTS tipos_bien;
CREATE TABLE tipos_bien (
    id_tipo        INT AUTO_INCREMENT PRIMARY KEY,
    codigo         VARCHAR(10)  NOT NULL,
    nombre         VARCHAR(100) NOT NULL,
    descripcion    TEXT,
    vida_util_anos TINYINT      NOT NULL DEFAULT 10,
    categoria      VARCHAR(50),
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO tipos_bien (codigo, nombre, descripcion, vida_util_anos, categoria) VALUES
('01', 'Equipos de Oficina', 'Mobiliario y equipos de oficina en general', 10, 'Oficina');

-- Tabla de bienes (con campos de responsable y foto)
DROP TABLE IF EXISTS bienes;
CREATE TABLE bienes (
    id_bien              INT AUTO_INCREMENT PRIMARY KEY,
    codigo_sudebip       VARCHAR(50)  NOT NULL UNIQUE,
    codigo_interno       VARCHAR(50),
    codigo_ministerio    VARCHAR(50),
    nro_bien_ministerio  VARCHAR(30),
    es_sn                TINYINT(1)   NOT NULL DEFAULT 0,
    nombre               VARCHAR(255) NOT NULL,
    descripcion          TEXT,
    serial               VARCHAR(100),
    marca                VARCHAR(100),
    modelo               VARCHAR(100),
    color                VARCHAR(50),
    anio_fabricacion     SMALLINT,
    cantidad             SMALLINT     NOT NULL DEFAULT 1,
    condicion_inicial    ENUM('Nuevo','Bueno','Regular','Malo'),
    id_estado            INT          NOT NULL DEFAULT 1,
    id_tipo              INT,
    id_area              INT,
    responsable_id       INT,
    responsable_cedula   VARCHAR(20) NULL,
    responsable_foto_path VARCHAR(500) NULL,
    cin_edificio         VARCHAR(50),
    cin_piso             VARCHAR(10),
    cin_departamento     VARCHAR(100),
    cin_oficina          VARCHAR(100),
    cin_posicion         VARCHAR(20),
    numero_factura       VARCHAR(50),
    fecha_adquisicion    DATE,
    valor_inicial        DECIMAL(15,2),
    valor_residual       DECIMAL(15,2) NOT NULL DEFAULT 0,
    vida_util_anos       TINYINT       NOT NULL DEFAULT 10,
    observaciones        TEXT,
    imagen_path          VARCHAR(500),
    created_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estado)      REFERENCES estados(id_estado)    ON DELETE RESTRICT,
    FOREIGN KEY (id_tipo)        REFERENCES tipos_bien(id_tipo)   ON DELETE SET NULL,
    FOREIGN KEY (id_area)        REFERENCES areas(id_area)        ON DELETE SET NULL,
    FOREIGN KEY (responsable_id) REFERENCES usuarios(id_usuario)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_bienes_codigo_sudebip ON bienes(codigo_sudebip);
CREATE INDEX idx_bienes_codigo_interno ON bienes(codigo_interno);
CREATE INDEX idx_bienes_nro_ministerio ON bienes(nro_bien_ministerio);
CREATE INDEX idx_bienes_estado         ON bienes(id_estado);
CREATE INDEX idx_bienes_area           ON bienes(id_area);
CREATE INDEX idx_bienes_tipo           ON bienes(id_tipo);
CREATE INDEX idx_bienes_nombre         ON bienes(nombre);
CREATE INDEX idx_bienes_updated_at     ON bienes(updated_at);
CREATE INDEX idx_bienes_responsable_cedula ON bienes(responsable_cedula);
CREATE INDEX idx_bienes_responsable_foto ON bienes(responsable_foto_path);

-- Tabla de movimientos
DROP TABLE IF EXISTS movimientos;
CREATE TABLE movimientos (
    id_movimiento        INT AUTO_INCREMENT PRIMARY KEY,
    bien_id              INT NOT NULL,
    tipo_movimiento      ENUM('incorporacion','traslado','desincorporacion','asignacion') NOT NULL,
    area_origen_id       INT,
    area_destino_id      INT,
    usuario_solicita_id  INT,
    usuario_aprueba_id   INT,
    fecha_solicitud      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion     TIMESTAMP    NULL,
    fecha_ejecucion      TIMESTAMP    NULL,
    motivo               TEXT,
    observaciones        TEXT,
    documento_soporte    VARCHAR(500),
    estado               ENUM('pendiente','aprobado','rechazado','cancelado') NOT NULL DEFAULT 'pendiente',
    created_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bien_id)             REFERENCES bienes(id_bien)    ON DELETE CASCADE,
    FOREIGN KEY (area_origen_id)      REFERENCES areas(id_area)     ON DELETE SET NULL,
    FOREIGN KEY (area_destino_id)     REFERENCES areas(id_area)     ON DELETE SET NULL,
    FOREIGN KEY (usuario_solicita_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL,
    FOREIGN KEY (usuario_aprueba_id)  REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE INDEX idx_movimientos_bien   ON movimientos(bien_id);
CREATE INDEX idx_movimientos_tipo   ON movimientos(tipo_movimiento);
CREATE INDEX idx_movimientos_estado ON movimientos(estado);
CREATE INDEX idx_movimientos_fecha  ON movimientos(fecha_solicitud);

-- Tabla de verificaciones
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

-- Tabla de notificaciones
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

-- Tabla de backups
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

-- Tabla de recuperacion de contraseña
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

SET FOREIGN_KEY_CHECKS = 1;
