-- ============================================================
-- BASE DE DATOS: hospital_bienes
-- Sistema de Gestión de Bienes Nacionales
-- Maternidad Concepción Palacios (MCP)
-- Versión: 1.1.0 | 2026
-- Motor: MySQL 8.0+ / MariaDB 10.4+
-- ============================================================
-- Credenciales por defecto: usuario=admin / clave=Admin_bn
-- El sistema pedirá cambiar la clave en el primer login.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS hospital_bienes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE hospital_bienes;

-- ============================================================
-- TABLA: roles
-- ============================================================
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id_rol      INT          AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL UNIQUE,
    descripcion TEXT,
    permisos    JSON         NOT NULL,
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
 '["bienes_crear","bienes_ver","movimientos_ver"]');

-- ============================================================
-- TABLA: usuarios
-- ============================================================
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
    id_usuario       INT          AUTO_INCREMENT PRIMARY KEY,
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

-- Clave: Admin_bn
INSERT INTO usuarios (id_rol, cedula, username, primer_nombre, primer_apellido,
    nombre_completo, email, password_hash, cargo, primer_login, activo)
VALUES (1, 'V-00000001', 'admin', 'Administrador', 'BN',
    'Administrador BN', 'admin@mcp.gob.ve',
    '$2y$12$/P/8pcNO32tcJpgNe22BMO/BvHxexoZazjnGABQuH9sc9KDV6qc52',
    'Administrador del Sistema', 1, 1);

-- ============================================================
-- TABLA: areas
-- ============================================================
DROP TABLE IF EXISTS areas;
CREATE TABLE areas (
    id_area        INT          AUTO_INCREMENT PRIMARY KEY,
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

INSERT INTO areas (nombre_area, descripcion, edificio, piso) VALUES
('Mantenimiento Técnico',     'Taller de mantenimiento técnico',              'Principal', -1),
('Lavandería',                'Servicio de lavandería',                       'Principal', -1),
('Sindicato',                 'Sede del sindicato',                           'Principal', -1),
('Electromedicina',           'Departamento de electromedicina',              'Principal', -1),
('Servicios Generales',       'Servicios generales del hospital',             'Principal', -1),
('Telecomunicaciones',        'Centro de telecomunicaciones',                 'Principal', -1),
('Morgue',                    'Servicio de morgue',                           'Principal', -1),
('Casa de Abrigo',            'Casa de abrigo institucional',                 'Principal', -1),
('Mantenimiento de Limpieza', 'Servicio de limpieza y mantenimiento',         'Principal', -1),
('Centro de Capacitación',    'Centro de capacitación del personal',          'Principal', -1),
('Depósito BN 1',             'Depósito de Bienes Nacionales N°1',            'Principal', -1),
('Depósito BN 2',             'Depósito de Bienes Nacionales N°2',            'Principal', -1),
('Depósito BN 3',             'Depósito de Bienes Nacionales N°3',            'Principal', -1),
('Depósito BN 4',             'Depósito de Bienes Nacionales N°4',            'Principal', -1),
('Sala de Parto',             'Sala de partos',                               'Principal',  1),
('Quirófano',                 'Salas de operaciones',                         'Principal',  1),
('Banco de Sangre',           'Banco de sangre',                              'Principal',  1),
('Anestesia',                 'Departamento de anestesia',                    'Principal',  1),
('Central de Suministros',    'Central de suministros médicos',               'Principal',  1),
('Terapia Adulto',            'Unidad de terapia adulto',                     'Principal',  1),
('Nutrición',                 'Departamento de nutrición',                    'Principal',  1),
('Gerencia de Bienes Nacionales','Departamento de control de bienes',         'Principal',  1),
('Administración',            'Área administrativa central',                  'Principal',  1),
('Materno-Fetal',             'Unidad materno-fetal',                         'Principal',  2),
('Consultas de Eco',          'Consultas de ecosonografía',                   'Principal',  2),
('Hospitalización P2',        'Hospitalización piso 2',                       'Principal',  2),
('Hospitalización P3',        'Hospitalización piso 3',                       'Principal',  3),
('Hospitalización P4',        'Hospitalización piso 4',                       'Principal',  4),
('Hospitalización P5',        'Hospitalización piso 5',                       'Principal',  5),
('Hospitalización P6',        'Hospitalización piso 6',                       'Principal',  6),
('Hospitalización P7',        'Hospitalización piso 7',                       'Principal',  7),
('Hospitalización P8',        'Hospitalización piso 8',                       'Principal',  8),
('Residencias Médicas',       'Residencias médicas',                          'Principal',  9),
('Milicianos',                'Sede de milicianos',                           'Anexo',     -1),
('Mamá Canguro',              'Programa mamá canguro',                        'Anexo',      0),
('Odontología',               'Servicio de odontología',                      'Anexo',      0),
('Central de Citas',          'Central de citas médicas',                     'Anexo',      0),
('Prenatal',                  'Consultas prenatales (mezzanina)',              'Anexo',      0),
('Adolescentes',              'Servicio de adolescentes',                     'Anexo',      1),
('Alto Riesgo Neurológico',   'Unidad de alto riesgo neurológico',            'Anexo',      1),
('Ortopedia',                 'Servicio de ortopedia',                        'Anexo',      1),
('Ginecología',               'Servicio de ginecología',                      'Anexo',      2),
('Ecosonografía',             'Servicio de ecosonografía',                    'Anexo',      2),
('Planificación Familiar',    'Servicio de planificación familiar',           'Anexo',      2),
('Endocrinología',            'Servicio de endocrinología',                   'Anexo',      2),
('Fertilidad',                'Servicio de fertilidad',                       'Anexo',      3),
('Psiquiatría',               'Servicio de psiquiatría',                      'Anexo',      3),
('Medicina Interna',          'Servicio de medicina interna',                 'Anexo',      3),
('Infectología',              'Servicio de infectología',                     'Anexo',      3),
('Recursos Humanos',          'Departamento de recursos humanos',             'Anexo',      4),
('Postgrado',                 'Departamento de postgrado',                    'Anexo',      4);

-- ============================================================
-- TABLA: estados
-- ============================================================
DROP TABLE IF EXISTS estados;
CREATE TABLE estados (
    id_estado   INT          AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(50)  NOT NULL,
    descripcion TEXT,
    color       VARCHAR(7)   COMMENT 'Color HEX para la UI',
    es_baja     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO estados (id_estado, nombre, descripcion, color, es_baja) VALUES
(1, 'Operativo',      'Bien en funcionamiento normal',              '#28a745', 0),
(2, 'Inoperativo',    'Bien que no funciona pero es recuperable',   '#ffc107', 0),
(3, 'En Resguardo',   'Bien resguardado temporalmente',             '#17a2b8', 0),
(4, 'Chatarra',       'Bien sin posibilidad de recuperación',       '#dc3545', 1),
(5, 'Desincorporado', 'Bien dado de baja oficialmente',             '#6c757d', 1);

-- ============================================================
-- TABLA: tipos_bien
-- ============================================================
DROP TABLE IF EXISTS tipos_bien;
CREATE TABLE tipos_bien (
    id_tipo        INT          AUTO_INCREMENT PRIMARY KEY,
    codigo         VARCHAR(10)  NOT NULL,
    nombre         VARCHAR(100) NOT NULL,
    descripcion    TEXT,
    vida_util_anos TINYINT      NOT NULL DEFAULT 10,
    categoria      VARCHAR(50),
    activo         TINYINT(1)   NOT NULL DEFAULT 1,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tipos_bien (codigo, nombre, descripcion, vida_util_anos, categoria) VALUES
('01',   'Equipos de Oficina',                          'Mobiliario y equipos de oficina en general',              10, 'Oficina'),
('02',   'Del Alojamiento',                             'Bienes del alojamiento (unidad)',                         10, 'Alojamiento'),
('03',   'Material de Construcción y Taller',           'Material de construcción, equipos de taller, etc.',       15, 'Construcción'),
('03-1', 'Reguladores de Voltaje',                      'Todos los reguladores de voltaje',                         5, 'Construcción'),
('04',   'Vehículos',                                   'Vehículos de la institución',                             15, 'Vehículos'),
('05',   'Telecomunicaciones',                          'Teléfonos, radios y equipos de comunicación',              8, 'Telecomunicaciones'),
('06',   'Bienes Hospitalarios',                        'Bienes hospitalarios en general',                         10, 'Hospitalario'),
('06-1', 'Equipos Quirúrgicos y Hospitalarios',         'Equipos quirúrgicos y hospitalarios específicos',         10, 'Hospitalario'),
('06-2', 'Equipo Odontológico',                         'Equipo odontológico únicamente',                          10, 'Hospitalario'),
('07',   'Equipos Científicos, Enseñanza y Religiosos', 'Equipos científicos, de enseñanza y religiosos',          10, 'Científico'),
('07-2', 'Equipos de Enseñanza',                        'Pizarras, pupitres y equipos de enseñanza',               10, 'Científico'),
('07-3', 'Capilla',                                     'Todo lo que abarca la Capilla',                           20, 'Religioso'),
('08',   'Cultural y Artístico',                        'Cuadros y bienes culturales o artísticos',                20, 'Cultural'),
('13',   'Equipos de Procesamiento de Datos',           'Todo el equipamiento tecnológico e informático',           5, 'Tecnología');

-- ============================================================
-- TABLA: bienes
-- ============================================================
DROP TABLE IF EXISTS bienes;
CREATE TABLE bienes (
    id_bien              INT          AUTO_INCREMENT PRIMARY KEY,
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

-- ============================================================
-- TABLA: movimientos
-- ============================================================
DROP TABLE IF EXISTS movimientos;
CREATE TABLE movimientos (
    id_movimiento        INT          AUTO_INCREMENT PRIMARY KEY,
    bien_id              INT          NOT NULL,
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

-- ============================================================
-- TABLA: mantenimientos
-- ============================================================
DROP TABLE IF EXISTS mantenimientos;
CREATE TABLE mantenimientos (
    id_mantenimiento         INT          AUTO_INCREMENT PRIMARY KEY,
    bien_id                  INT,
    tipo_servicio            ENUM('preventivo','correctivo','predictivo') NOT NULL,
    fecha_programada         DATE,
    fecha_ejecutada          DATE,
    proveedor                VARCHAR(255),
    tecnico                  VARCHAR(255),
    costo                    DECIMAL(15,2),
    diagnostico              TEXT,
    trabajo_realizado        TEXT,
    proxima_fecha_programada DATE,
    garantia_meses           TINYINT,
    observaciones            TEXT,
    documento_reporte        VARCHAR(500),
    realizado_por_id         INT,
    created_at               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bien_id)          REFERENCES bienes(id_bien)      ON DELETE CASCADE,
    FOREIGN KEY (realizado_por_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_mantenimientos_bien             ON mantenimientos(bien_id);
CREATE INDEX idx_mantenimientos_fecha_ejecutada  ON mantenimientos(fecha_ejecutada);
CREATE INDEX idx_mantenimientos_fecha_programada ON mantenimientos(fecha_programada);
CREATE INDEX idx_mantenimientos_tipo             ON mantenimientos(tipo_servicio);

-- ============================================================
-- TABLA: auditoria
-- ============================================================
DROP TABLE IF EXISTS auditoria;
CREATE TABLE auditoria (
    id_auditoria     INT          AUTO_INCREMENT PRIMARY KEY,
    tabla_afectada   VARCHAR(50)  NOT NULL,
    registro_id      INT,
    accion           VARCHAR(20)  NOT NULL,
    usuario_id       INT,
    ip_address       VARCHAR(45),
    user_agent       TEXT,
    datos_anteriores JSON,
    datos_nuevos     JSON,
    fecha_operacion  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_auditoria_tabla   ON auditoria(tabla_afectada);
CREATE INDEX idx_auditoria_usuario ON auditoria(usuario_id);
CREATE INDEX idx_auditoria_fecha   ON auditoria(fecha_operacion);

-- ============================================================
-- TABLA: notificaciones
-- ============================================================
DROP TABLE IF EXISTS notificaciones;
CREATE TABLE notificaciones (
    id_notificacion INT          AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT          NOT NULL,
    tipo            ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
    titulo          VARCHAR(255) NOT NULL,
    mensaje         TEXT         NOT NULL,
    link            VARCHAR(500),
    leida           TINYINT(1)   NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at         DATETIME     NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_notificaciones_usuario       ON notificaciones(usuario_id);
CREATE INDEX idx_notificaciones_leida         ON notificaciones(leida);
CREATE INDEX idx_notificaciones_created       ON notificaciones(created_at);
CREATE INDEX idx_notificaciones_usuario_leida ON notificaciones(usuario_id, leida);

-- ============================================================
-- TABLA: backups_log
-- ============================================================
DROP TABLE IF EXISTS backups_log;
CREATE TABLE backups_log (
    id_backup     INT          AUTO_INCREMENT PRIMARY KEY,
    filename      VARCHAR(255) NOT NULL,
    size          INT          NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status        ENUM('success','error') NOT NULL DEFAULT 'success',
    error_message TEXT         NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_backups_log_created ON backups_log(created_at);
CREATE INDEX idx_backups_log_status  ON backups_log(status);

-- ============================================================
-- TABLA: password_resets
-- ============================================================
DROP TABLE IF EXISTS password_resets;
CREATE TABLE password_resets (
    id_reset      INT          AUTO_INCREMENT PRIMARY KEY,
    usuario_id    INT          NOT NULL,
    codigo        VARCHAR(6)   NOT NULL,
    email         VARCHAR(100) NOT NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at    DATETIME     NOT NULL,
    used_at       DATETIME     NULL,
    ip_address    VARCHAR(45)  NOT NULL,
    user_agent    TEXT,
    intentos_dia  INT          NOT NULL DEFAULT 1,
    fecha_intento DATE         NOT NULL DEFAULT (CURRENT_DATE),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_password_resets_codigo        ON password_resets(codigo);
CREATE INDEX idx_password_resets_expires       ON password_resets(expires_at);
CREATE INDEX idx_password_resets_usuario_fecha ON password_resets(usuario_id, fecha_intento);

-- ============================================================
-- TABLA: configuracion
-- ============================================================
DROP TABLE IF EXISTS configuracion;
CREATE TABLE configuracion (
    id_configuracion INT          AUTO_INCREMENT PRIMARY KEY,
    clave            VARCHAR(50)  NOT NULL UNIQUE,
    valor            TEXT,
    descripcion      TEXT,
    tipo_dato        VARCHAR(20)  NOT NULL DEFAULT 'string',
    editable         TINYINT(1)   NOT NULL DEFAULT 1,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO configuracion (clave, valor, descripcion, tipo_dato) VALUES
('app_nombre',           'Sistema de Gestión de Bienes Nacionales', 'Nombre de la aplicación',           'string'),
('app_version',          '1.1.0',                                   'Versión del sistema',               'string'),
('institucion_nombre',   'Maternidad Concepción Palacios',          'Nombre de la institución',          'string'),
('institucion_rif',      'G-20003090-0',                            'RIF de la institución',             'string'),
('frecuencia_inventario','Semestral',                               'Frecuencia de inventario acordada', 'string'),
('sesion_tiempo_minutos','30',                                      'Tiempo de sesión en minutos',       'integer'),
('intentos_login_max',   '5',                                       'Máximo de intentos de login',       'integer');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- VERIFICACIÓN
-- ============================================================
SELECT 'Instalación completada exitosamente' AS resultado;
SELECT CONCAT('Roles: ',      COUNT(*)) AS info FROM roles;
SELECT CONCAT('Usuarios: ',   COUNT(*)) AS info FROM usuarios;
SELECT CONCAT('Áreas: ',      COUNT(*)) AS info FROM areas;
SELECT CONCAT('Estados: ',    COUNT(*)) AS info FROM estados;
SELECT CONCAT('Tipos bien: ', COUNT(*)) AS info FROM tipos_bien;
SELECT '' AS '', 'Usuario: admin | Clave: Admin_bn' AS credenciales_por_defecto;
