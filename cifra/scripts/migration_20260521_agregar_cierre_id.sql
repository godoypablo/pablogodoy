-- Migration: Agregar cierre_id a cuotas_movimiento
-- Fecha: 2026-05-21
-- Descripción: Refactorización del sistema de cuotas para usar cierres_tarjeta como motor

-- 1. Agregar columna cierre_id
ALTER TABLE cuotas_movimiento
ADD COLUMN cierre_id INT NULL AFTER movimiento_id,
ADD FOREIGN KEY (cierre_id) REFERENCES cierres_tarjeta(id) ON DELETE SET NULL;

-- 2. Crear índice para búsquedas por cierre
CREATE INDEX idx_cierre_id ON cuotas_movimiento(cierre_id);

-- 3. Crear índice compuesto para queries de próximos pagos
CREATE INDEX idx_pagada_vencimiento ON cuotas_movimiento(pagada, fecha_vencimiento);
