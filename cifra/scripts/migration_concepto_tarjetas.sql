-- Migration: Agregar concepto_id a tarjetas_credito
-- Descripción: Vincula cada tarjeta de crédito con un concepto de gasto
-- para sincronizar automáticamente los pagos con los gastos registrados

-- Agregar columna concepto_id con FK a conceptos
ALTER TABLE tarjetas_credito
ADD COLUMN concepto_id INT NULL DEFAULT NULL,
ADD CONSTRAINT fk_tarjeta_concepto
    FOREIGN KEY (concepto_id) REFERENCES conceptos(id) ON DELETE SET NULL;

-- Verificación: mostrar estructura de la tabla
DESCRIBE tarjetas_credito;
