-- Script para corregir la tabla cuotas_hipoteca
-- Compatible con versiones más antiguas de MariaDB
-- Ejecutar en phpMyAdmin (copiar TODO de una sola vez)

-- PASO 1: Eliminar total_pesos (si existe)
ALTER TABLE cuotas_hipoteca DROP COLUMN `total_pesos`;

-- PASO 2: Eliminar valor_uva vacío (si existe)
ALTER TABLE cuotas_hipoteca DROP COLUMN `valor_uva`;

-- PASO 3: Renombrar total → total_uva
ALTER TABLE cuotas_hipoteca
CHANGE COLUMN `total` `total_uva` DECIMAL(10, 2) NOT NULL COMMENT 'Total en UVAs (Capital + Interés)';

-- PASO 4: Agregar valor_uva (precio de 1 UVA en pesos)
ALTER TABLE cuotas_hipoteca
ADD COLUMN `valor_uva` DECIMAL(10, 2) NULL COMMENT 'Valor de 1 UVA en pesos argentinos' AFTER `total_uva`;

-- PASO 5: Agregar total_pesos (generada, se calcula automáticamente)
ALTER TABLE cuotas_hipoteca
ADD COLUMN `total_pesos` DECIMAL(12, 2) GENERATED ALWAYS AS (total_uva * COALESCE(valor_uva, 0)) STORED COMMENT 'Total en pesos = Total UVA * Valor UVA' AFTER `valor_uva`;
