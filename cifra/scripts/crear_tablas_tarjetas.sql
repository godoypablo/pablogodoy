-- ============================================================
-- MÓDULO TARJETAS DE CRÉDITO (v2 - 2026-05-20)
-- Reemplaza las tablas viejas: tarjetas, consumos_tarjeta
-- ============================================================

USE gastos_personales;

-- Tabla de tarjetas de crédito
CREATE TABLE IF NOT EXISTS tarjetas_credito (
    id                      INT AUTO_INCREMENT PRIMARY KEY,
    banco                   VARCHAR(100) NOT NULL,
    nombre_tarjeta          VARCHAR(100) NOT NULL,
    marca                   VARCHAR(50) COMMENT 'Visa, Mastercard, Amex, etc',
    ultimos_4               VARCHAR(4),
    limite_credito          DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    fecha_cierre_dia        TINYINT NOT NULL DEFAULT 25 COMMENT 'Día del mes en que cierra el resumen (1-31)',
    fecha_vencimiento_dia   TINYINT NOT NULL DEFAULT 3 COMMENT 'Día del mes en que vence el pago (1-31)',
    titular                 VARCHAR(100),
    activo                  TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo),
    INDEX idx_banco (banco)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de movimientos (compras) en tarjeta
CREATE TABLE IF NOT EXISTS movimientos_tarjeta (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    tarjeta_id                      INT NOT NULL,
    fecha_compra                    DATE NOT NULL,
    descripcion                     VARCHAR(200),
    comercio                        VARCHAR(100),
    categoria                       VARCHAR(100),
    monto_total                     DECIMAL(14,2) NOT NULL,
    moneda                          CHAR(3) DEFAULT 'ARS',
    cuotas_totales                  TINYINT NOT NULL DEFAULT 1 COMMENT '1=pago único, 2+=cuotas',
    cuota_pagada_proximo_resumen    TINYINT NOT NULL DEFAULT 1 COMMENT 'Cuota que se paga en el próximo resumen',
    monto_cuota                     DECIMAL(14,2) NOT NULL COMMENT 'Monto de cada cuota (monto_total / cuotas_totales)',
    observaciones                   TEXT,
    estado                          ENUM('activo','cancelado','anulado') DEFAULT 'activo',
    fecha_creacion                  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion              TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tarjeta_id) REFERENCES tarjetas_credito(id) ON DELETE CASCADE,
    INDEX idx_tarjeta (tarjeta_id),
    INDEX idx_fecha (fecha_compra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cuotas individuales de movimientos
CREATE TABLE IF NOT EXISTS cuotas_movimiento (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    movimiento_id       INT NOT NULL,
    numero_cuota        TINYINT NOT NULL COMMENT '1, 2, 3...',
    periodo             CHAR(7) NOT NULL COMMENT 'YYYY-MM del resumen',
    fecha_cierre        DATE NOT NULL,
    fecha_vencimiento   DATE NOT NULL,
    monto               DECIMAL(14,2) NOT NULL,
    estado              ENUM('pendiente','pagada','vencida') DEFAULT 'pendiente',
    fecha_pago          DATE DEFAULT NULL,
    fecha_creacion      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movimiento_id) REFERENCES movimientos_tarjeta(id) ON DELETE CASCADE,
    INDEX idx_movimiento (movimiento_id),
    INDEX idx_periodo (periodo),
    INDEX idx_estado (estado),
    UNIQUE KEY uk_movimiento_cuota (movimiento_id, numero_cuota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de resúmenes de períodos cerrados
CREATE TABLE IF NOT EXISTS resumenes_tarjeta (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    tarjeta_id          INT NOT NULL,
    periodo             CHAR(7) NOT NULL COMMENT 'YYYY-MM',
    fecha_cierre        DATE NOT NULL,
    fecha_vencimiento   DATE NOT NULL,
    total_consumido     DECIMAL(14,2) NOT NULL DEFAULT 0.00,
    cuotas_totales      INT NOT NULL DEFAULT 0,
    cuotas_pagadas      INT NOT NULL DEFAULT 0,
    pagado              TINYINT(1) NOT NULL DEFAULT 0,
    fecha_pago          DATE DEFAULT NULL,
    cuenta_pago_id      INT DEFAULT NULL COMMENT 'Cuenta desde la que se pagó',
    estado_resumen      ENUM('abierto','cerrado','vencido','pagado','parcial') DEFAULT 'abierto',
    fecha_creacion      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tarjeta_id) REFERENCES tarjetas_credito(id) ON DELETE CASCADE,
    UNIQUE KEY uk_tarjeta_periodo (tarjeta_id, periodo),
    INDEX idx_periodo (periodo),
    INDEX idx_pagado (pagado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
