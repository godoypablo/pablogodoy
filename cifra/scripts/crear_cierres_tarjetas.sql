-- Tabla para almacenar cierres y vencimientos específicos por mes/año
CREATE TABLE IF NOT EXISTS cierres_tarjeta (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tarjeta_id INT NOT NULL,
    anio INT NOT NULL,
    mes INT NOT NULL,
    fecha_cierre DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cierre (tarjeta_id, anio, mes),
    FOREIGN KEY (tarjeta_id) REFERENCES tarjetas_credito(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTAR CIERRES Y VENCIMIENTOS ESPECÍFICOS POR MES
-- ============================================================
-- Reemplaza estos valores con tus fechas reales
-- Formato: (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento)
--
-- Referencia de tarjetas:
-- 1 = Visa Santander
-- 2 = Nuevo BERSA
-- 3 = (otras tarjetas)
--
-- Ejemplo para mayo-diciembre 2026:
-- INSERT INTO cierres_tarjeta (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento) VALUES
--     -- Visa Santander (tarjeta_id=1)
--     (1, 2026, 5, '2026-05-11', '2026-05-19'),
--     (1, 2026, 6, '2026-06-11', '2026-06-19'),
--     (1, 2026, 7, '2026-07-11', '2026-07-19'),
--     -- Nuevo BERSA (tarjeta_id=2)
--     (2, 2026, 5, '2026-05-28', '2026-06-04'),
--     (2, 2026, 6, '2026-06-28', '2026-07-04'),
--     (2, 2026, 7, '2026-07-28', '2026-08-04');

-- DESCOMENTAR Y LLENAR CON TUS DATOS REALES
-- INSERT INTO cierres_tarjeta (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento) VALUES
