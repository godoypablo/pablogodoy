-- ============================================================
-- MIGRACIÓN TARJETAS V3 - SIMPLIFICACIÓN DE MODELO
-- Fecha: 2026-05-20
-- ============================================================

USE gastos_personales;

-- 1. Eliminar tabla de resúmenes (no la necesitamos)
DROP TABLE IF EXISTS resumenes_tarjeta;

-- 2. Ver estructura actual de cuotas_movimiento
-- (Para diagnóstico - ver qué columnas realmente existen)
DESCRIBE cuotas_movimiento;

-- 3. Ver estructura actual de movimientos_tarjeta
DESCRIBE movimientos_tarjeta;
