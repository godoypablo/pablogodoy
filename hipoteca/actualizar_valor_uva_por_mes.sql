-- Script para actualizar valores de UVA por mes
-- Ejecutar en phpMyAdmin del servidor

-- ============================================
-- ACTUALIZAR POR MES SEGÚN SE CONOZCA EL VALOR
-- ============================================

-- MARZO 2026 (cuota 1)
-- UPDATE cuotas_hipoteca SET valor_uva = 1000.00 WHERE nro_cuota = 1;

-- ABRIL 2026 (cuota 2)
-- UPDATE cuotas_hipoteca SET valor_uva = 1005.00 WHERE nro_cuota = 2;

-- MAYO 2026 (cuota 3)
-- UPDATE cuotas_hipoteca SET valor_uva = 1010.00 WHERE nro_cuota = 3;

-- JUNIO 2026 (cuota 4)
-- UPDATE cuotas_hipoteca SET valor_uva = 1015.00 WHERE nro_cuota = 4;

-- JULIO 2026 (cuota 5 - VALOR ACTUAL)
UPDATE cuotas_hipoteca SET valor_uva = 2020.51 WHERE nro_cuota = 5;

-- AGOSTO 2026 EN ADELANTE: PONER 0 (hasta conocer valor real)
UPDATE cuotas_hipoteca SET valor_uva = 0 WHERE nro_cuota >= 6;

-- ============================================
-- EXPLICACIÓN:
-- ============================================
--
-- Valor 0: Significa "no calculado aún"
-- El total_pesos será $0.00 hasta que actualices el valor real
--
-- Cuando conozca el valor de UVA para cada mes:
-- 1. Actualiza el valor en phpMyAdmin
-- 2. O haz clic en el valor en hipoteca.php para editarlo
--
-- Ejemplos de comandos para los próximos meses:
--
-- Agosto 2026 (cuota 6 - cuando sepas el valor):
-- UPDATE cuotas_hipoteca SET valor_uva = 2050.00 WHERE nro_cuota = 6;
--
-- Septiembre 2026 (cuota 7):
-- UPDATE cuotas_hipoteca SET valor_uva = 2080.00 WHERE nro_cuota = 7;
--
-- Etc...
--
-- O simplemente haz clic en cada celda en hipoteca.php para editarla
-- ============================================

-- Verificar que quedó bien:
-- SELECT nro_cuota, fecha_vencimiento, total_uva, valor_uva, total_pesos FROM cuotas_hipoteca ORDER BY nro_cuota;
