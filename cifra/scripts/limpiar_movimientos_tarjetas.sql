-- Limpiar datos de prueba de tarjetas de crédito (OPCIÓN 2)
-- Mantiene: tarjetas_credito (y su configuración)
-- Elimina: movimientos_tarjeta, cuotas_movimiento, cierres_tarjeta

SET FOREIGN_KEY_CHECKS=0;

DELETE FROM cuotas_movimiento
WHERE movimiento_id IN (SELECT id FROM movimientos_tarjeta);

DELETE FROM movimientos_tarjeta;

DELETE FROM cierres_tarjeta;

SET FOREIGN_KEY_CHECKS=1;

-- Verificar que las tablas estén limpias
SELECT 'movimientos_tarjeta' as tabla, COUNT(*) as registros FROM movimientos_tarjeta
UNION ALL
SELECT 'cuotas_movimiento', COUNT(*) FROM cuotas_movimiento
UNION ALL
SELECT 'cierres_tarjeta', COUNT(*) FROM cierres_tarjeta;
