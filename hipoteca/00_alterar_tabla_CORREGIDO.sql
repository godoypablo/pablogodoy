-- Script para corregir la tabla cuotas_hipoteca (versión corregida)
-- Ejecutar en phpMyAdmin en este EXACTO orden

-- PASO 1: Eliminar la columna generada total_pesos (si existe)
-- Esto es necesario porque depende de la columna "total" que vamos a renombrar
ALTER TABLE cuotas_hipoteca
DROP COLUMN IF EXISTS `total_pesos`;

-- PASO 2: Eliminar el campo valor_uva vacío (si existe)
ALTER TABLE cuotas_hipoteca
DROP COLUMN IF EXISTS `valor_uva`;

-- PASO 3: Renombrar "total" a "total_uva"
ALTER TABLE cuotas_hipoteca
CHANGE COLUMN `total` `total_uva` DECIMAL(10, 2) NOT NULL COMMENT 'Total en UVAs (Capital + Interés)';

-- PASO 4: Agregar nueva columna valor_uva (precio de 1 UVA en pesos)
ALTER TABLE cuotas_hipoteca
ADD COLUMN `valor_uva` DECIMAL(10, 2) NULL COMMENT 'Valor de 1 UVA en pesos argentinos' AFTER `total_uva`;

-- PASO 5: Agregar columna total_pesos (generada, calculada automáticamente)
ALTER TABLE cuotas_hipoteca
ADD COLUMN `total_pesos` DECIMAL(12, 2) GENERATED ALWAYS AS (total_uva * COALESCE(valor_uva, 0)) STORED COMMENT 'Total en pesos = Total UVA * Valor UVA' AFTER `valor_uva`;

-- ============================================
-- Verificar que quedó correcta:
-- DESCRIBE cuotas_hipoteca;
-- ============================================
