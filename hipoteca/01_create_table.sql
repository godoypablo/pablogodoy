-- Tabla de Cuotas Hipotecarias - Banco Entre Ríos
-- Base de datos: gastos_personales
-- Compatible con MariaDB 10.3+

CREATE TABLE IF NOT EXISTS cuotas_hipoteca (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nro_cuota INT NOT NULL UNIQUE,
    estado ENUM('PAGADA', 'IMPAGA') NOT NULL DEFAULT 'IMPAGA',
    fecha_vencimiento DATE NOT NULL,
    capital DECIMAL(10, 2) NOT NULL COMMENT 'Capital en UVAs',
    interes DECIMAL(10, 2) NOT NULL COMMENT 'Interés en UVAs',
    total_uva DECIMAL(10, 2) NOT NULL COMMENT 'Total en UVAs (Capital + Interés)',
    valor_uva DECIMAL(10, 2) NULL COMMENT 'Valor de 1 UVA en pesos argentinos (actualizar diariamente)',
    total_pesos DECIMAL(12, 2) GENERATED ALWAYS AS (total_uva * COALESCE(valor_uva, 0)) STORED COMMENT 'Total en pesos = Total UVA * Valor UVA (calculado automáticamente)',
    fecha_pago DATE NULL,
    observaciones VARCHAR(500),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento),
    INDEX idx_nro_cuota (nro_cuota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuotas hipotecarias en UVAs - Banco Entre Ríos';
