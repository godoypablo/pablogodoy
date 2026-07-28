-- ================================================
-- ACTUALIZAR VALOR DE LA UVA
-- ================================================
--
-- ¿QUÉ HACE ESTE SCRIPT?
-- El campo "valor_uva" es el PRECIO ACTUAL de 1 UVA en pesos
-- La cuota "total_pesos" se calcula automáticamente: total_uva * valor_uva
--
-- EJEMPLO:
--   Si 1 UVA = $1025.50 (valor actual)
--   Y una cuota tiene 320.26 UVAs
--   Entonces: 320.26 * 1025.50 = $328,266.63 pesos
--
-- ================================================

-- INSTRUCCIONES:
-- 1. Obtén el valor actual de 1 UVA en:
--    https://ikiwi.net.ar/calculadoras/uva-a-pesos/
-- 2. Reemplaza XXX.XX por ese valor
-- 3. Ejecuta este script en phpMyAdmin

-- IMPORTANTE: El valor de la UVA cambia DIARIAMENTE
-- Actualiza este valor regularmente para que los cálculos sean precisos

-- ACTUALIZAR: Cambia 1025.50 por el valor actual de 1 UVA
UPDATE cuotas_hipoteca SET valor_uva = 1025.50;

-- VERIFICAR: Ver que el cálculo está correcto
-- Ejecuta esta consulta para verificar:
-- SELECT nro_cuota, estado, total_uva, valor_uva, total_pesos FROM cuotas_hipoteca LIMIT 5;
