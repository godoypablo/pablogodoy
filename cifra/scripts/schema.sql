-- Base de datos para Sistema de Gastos Personales
-- Creado: Diciembre 2025

CREATE DATABASE IF NOT EXISTS gastos_personales CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gastos_personales;

-- Tabla de conceptos (ingresos y gastos)
CREATE TABLE IF NOT EXISTS conceptos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('ingreso', 'gasto') NOT NULL,
    orden INT NOT NULL DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de registros mensuales
CREATE TABLE IF NOT EXISTS registros_mensuales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concepto_id INT NOT NULL,
    mes INT NOT NULL COMMENT 'Mes (1-12)',
    anio INT NOT NULL COMMENT 'Año (ej: 2025)',
    importe DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (concepto_id) REFERENCES conceptos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_concepto_mes_anio (concepto_id, mes, anio),
    INDEX idx_mes_anio (mes, anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar conceptos predefinidos (INGRESOS)
INSERT INTO conceptos (nombre, tipo, orden) VALUES
('Sueldo HSC', 'ingreso', 1),
('Sueldo TCER', 'ingreso', 2),
('IPEM', 'ingreso', 3),
('Ahorro', 'ingreso', 4);

-- Insertar conceptos predefinidos (GASTOS)
INSERT INTO conceptos (nombre, tipo, orden) VALUES
('Cuota Alimentaria', 'gasto', 1),
('Rowing', 'gasto', 2),
('Mastercard', 'gasto', 3),
('Alquiler Departamento', 'gasto', 4),
('Supermercado', 'gasto', 5),
('YouTube', 'gasto', 6),
('Spotify', 'gasto', 7),
('Elena', 'gasto', 8),
('ATER Etios', 'gasto', 9),
('ATER Tornado', 'gasto', 10),
('Enersa', 'gasto', 11),
('Rivadavia Etios', 'gasto', 12),
('Rivadavia Tornado', 'gasto', 13),
('Redengas', 'gasto', 14),
('Personal Flow (Int.&C)', 'gasto', 15),
('Monotributo Afip', 'gasto', 16),
('Roy Udrizar', 'gasto', 17),
('Coprocier', 'gasto', 18),
('Nafta', 'gasto', 19),
('Aire Acondicionado', 'gasto', 20),
('Remedios', 'gasto', 21),
('Cochera', 'gasto', 22),
('Gimnasio', 'gasto', 23),
('Gastos', 'gasto', 24);

-- Vista para obtener resumen mensual
CREATE OR REPLACE VIEW vista_resumen_mensual AS
SELECT
    rm.mes,
    rm.anio,
    SUM(CASE WHEN c.tipo = 'ingreso' THEN rm.importe ELSE 0 END) as total_ingresos,
    SUM(CASE WHEN c.tipo = 'gasto' THEN rm.importe ELSE 0 END) as total_gastos,
    SUM(CASE WHEN c.tipo = 'ingreso' THEN rm.importe ELSE 0 END) -
    SUM(CASE WHEN c.tipo = 'gasto' THEN rm.importe ELSE 0 END) as saldo
FROM registros_mensuales rm
INNER JOIN conceptos c ON rm.concepto_id = c.id
GROUP BY rm.mes, rm.anio;

-- ============================================================
-- MÓDULO TARJETAS DE CRÉDITO
-- ============================================================

CREATE TABLE IF NOT EXISTS tarjetas (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL,
    banco            VARCHAR(100) NOT NULL DEFAULT '',
    limite           DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    cierre_dia       TINYINT NOT NULL DEFAULT 1
                         COMMENT 'Día del mes en que cierra el resumen (1-31)',
    vencimiento_dia  TINYINT NOT NULL DEFAULT 10
                         COMMENT 'Día del mes en que vence el pago (1-31)',
    color            CHAR(7) NOT NULL DEFAULT '#6366f1'
                         COMMENT 'Hex color para la UI',
    activo           TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS resumenes_tarjeta (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    tarjeta_id       INT NOT NULL,
    mes              TINYINT NOT NULL  COMMENT '1-12',
    anio             SMALLINT NOT NULL,
    total_consumido  DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    pagado           TINYINT(1) NOT NULL DEFAULT 0,
    fecha_pago       DATE DEFAULT NULL,
    cuenta_pago_id   INT DEFAULT NULL
                         COMMENT 'Cuenta bancaria desde la que se pagó',
    movimiento_id    INT DEFAULT NULL
                         COMMENT 'FK a movimientos_cuenta (pago_tarjeta)',
    fecha_creacion   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_tarjeta_mes_anio (tarjeta_id, mes, anio),
    FOREIGN KEY (tarjeta_id)     REFERENCES tarjetas(id)          ON DELETE CASCADE,
    FOREIGN KEY (cuenta_pago_id) REFERENCES cuentas(id)           ON DELETE SET NULL,
    FOREIGN KEY (movimiento_id)  REFERENCES movimientos_cuenta(id) ON DELETE SET NULL,
    INDEX idx_mes_anio (mes, anio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consumos_tarjeta (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    tarjeta_id   INT NOT NULL,
    resumen_id   INT DEFAULT NULL
                     COMMENT 'FK al resumen del período; NULL si el resumen aún no existe',
    descripcion  VARCHAR(200) NOT NULL DEFAULT '',
    importe      DECIMAL(14,2) NOT NULL,
    fecha        DATE NOT NULL,
    mes          TINYINT NOT NULL  COMMENT '1-12 — mes del resumen al que pertenece',
    anio         SMALLINT NOT NULL COMMENT 'Año del resumen',
    categoria_id INT DEFAULT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tarjeta_id)   REFERENCES tarjetas(id)          ON DELETE CASCADE,
    FOREIGN KEY (resumen_id)   REFERENCES resumenes_tarjeta(id) ON DELETE SET NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)        ON DELETE SET NULL,
    INDEX idx_tarjeta_mes_anio (tarjeta_id, mes, anio),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cuotas en consumos de tarjeta (agregado 2026-05-13)
ALTER TABLE consumos_tarjeta
    ADD COLUMN cuotas_total TINYINT DEFAULT NULL COMMENT 'Cantidad total de cuotas (NULL = pago único)',
    ADD COLUMN cuota_numero TINYINT DEFAULT NULL COMMENT 'Número de esta cuota (1, 2, 3...)',
    ADD COLUMN consumo_padre_id INT DEFAULT NULL COMMENT 'FK al consumo de la cuota 1 — agrupa todas las cuotas';
