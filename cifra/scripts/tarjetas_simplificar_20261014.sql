-- ============================================================
-- Tarjetas simplificadas (2026-10-14)
-- Los resúmenes se cargan en la planilla (categoría Bancos) y el
-- módulo Tarjetas solo los lee. Cada tarjeta se vincula a dos
-- conceptos de gasto: uno en pesos y otro en dólares.
-- Ejecutar UNA sola vez en phpMyAdmin (base gastos_personales).
-- No borra datos: las tablas viejas (movimientos, cuotas, cierres)
-- quedan sin uso.
-- ============================================================

-- 1) Campo para el concepto en dólares
ALTER TABLE tarjetas_credito
    ADD COLUMN concepto_usd_id INT(11) NULL DEFAULT NULL AFTER concepto_id,
    ADD CONSTRAINT fk_tarjeta_concepto_usd
        FOREIGN KEY (concepto_usd_id) REFERENCES conceptos (id) ON DELETE SET NULL;

-- 2) Conceptos en dólares, en la misma categoría que los de pesos (Bancos)
INSERT INTO conceptos (nombre, tipo, categoria_id, permite_multiples, moneda, activo)
SELECT 'Tarjeta Visa USD', 'gasto', categoria_id, 0, 'USD', 1 FROM conceptos WHERE id = 29;

INSERT INTO conceptos (nombre, tipo, categoria_id, permite_multiples, moneda, activo)
SELECT 'Tarjeta Mastercard USD', 'gasto', categoria_id, 0, 'USD', 1 FROM conceptos WHERE id = 7;

-- 3) Vincular cada tarjeta con sus conceptos
UPDATE tarjetas_credito
SET concepto_id     = 29,
    concepto_usd_id = (SELECT id FROM conceptos WHERE nombre = 'Tarjeta Visa USD' AND moneda = 'USD' ORDER BY id DESC LIMIT 1)
WHERE id = 1;  -- Visa Santander

UPDATE tarjetas_credito
SET concepto_id     = 7,
    concepto_usd_id = (SELECT id FROM conceptos WHERE nombre = 'Tarjeta Mastercard USD' AND moneda = 'USD' ORDER BY id DESC LIMIT 1)
WHERE id = 2;  -- Mastercard Bersa

-- Verificación
SELECT t.id, t.nombre_tarjeta, c1.nombre AS concepto_pesos, c2.nombre AS concepto_dolares
FROM tarjetas_credito t
LEFT JOIN conceptos c1 ON c1.id = t.concepto_id
LEFT JOIN conceptos c2 ON c2.id = t.concepto_usd_id;
