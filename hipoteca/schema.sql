-- Tabla de Crédito Hipotecario Banco Entre Ríos
-- Compatible con MySQL 5.7+ / MariaDB
CREATE TABLE IF NOT EXISTS cuotas_hipoteca (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nro_cuota INT NOT NULL UNIQUE,
    estado ENUM('PAGADA', 'IMPAGA') NOT NULL DEFAULT 'IMPAGA',
    fecha_vencimiento DATE NOT NULL,
    capital DECIMAL(10, 2) NOT NULL,
    interes DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    fecha_pago DATE NULL,
    observaciones VARCHAR(500),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índices para búsqueda rápida
CREATE INDEX idx_estado ON cuotas_hipoteca(estado);
CREATE INDEX idx_fecha_vencimiento ON cuotas_hipoteca(fecha_vencimiento);
CREATE INDEX idx_nro_cuota ON cuotas_hipoteca(nro_cuota);
