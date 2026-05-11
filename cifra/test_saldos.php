<?php
/**
 * Test de verificación de saldos
 * Ejecutar: php test_saldos.php
 */
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();
$mes = date('n');
$anio = date('Y');

echo "\n=== TEST DE SALDOS — Mes $mes/$anio ===\n\n";

// 1. Saldo Real: suma de cuentas
$stmt = $db->prepare("SELECT SUM(saldo_actual) as total FROM cuentas WHERE activo = 1 AND moneda = 'ARS'");
$stmt->execute();
$saldoReal = (float)($stmt->fetchColumn() ?? 0);
echo "1. SALDO REAL (suma cuentas): $" . number_format($saldoReal, 2, ',', '.') . "\n";

// 2. Total Gastos del mes
$stmt = $db->prepare(
    "SELECT COALESCE(SUM(rm.importe), 0) as total
     FROM registros_mensuales rm
     INNER JOIN conceptos c ON rm.concepto_id = c.id
     WHERE rm.mes = :mes AND rm.anio = :anio AND c.tipo = 'gasto' AND c.activo = 1"
);
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$totalGastos = (float)($stmt->fetchColumn() ?? 0);
echo "2. TOTAL GASTOS (todos): $" . number_format($totalGastos, 2, ',', '.') . "\n";

// 3. Gastos Pagados
$stmt = $db->prepare(
    "SELECT COALESCE(SUM(rm.importe), 0) as total
     FROM registros_mensuales rm
     INNER JOIN conceptos c ON rm.concepto_id = c.id
     WHERE rm.mes = :mes AND rm.anio = :anio AND c.tipo = 'gasto' AND c.activo = 1 AND rm.pagado = 1"
);
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$gastosPagados = (float)($stmt->fetchColumn() ?? 0);
echo "3. GASTOS PAGADOS: $" . number_format($gastosPagados, 2, ',', '.') . "\n";

// 4. Gastos Pendientes
$gastosPendientes = $totalGastos - $gastosPagados;
echo "4. GASTOS PENDIENTES: $" . number_format($gastosPendientes, 2, ',', '.') . "\n";

// 5. Disponible (Saldo Real - Gastos Pendientes)
$disponible = $saldoReal - $gastosPendientes;
echo "5. DISPONIBLE (Saldo - Pendientes): $" . number_format($disponible, 2, ',', '.') . "\n";

// 6. Ingresos Cobrados (para verificar también)
$stmt = $db->prepare(
    "SELECT COALESCE(SUM(rm.importe), 0) as total
     FROM registros_mensuales rm
     INNER JOIN conceptos c ON rm.concepto_id = c.id
     WHERE rm.mes = :mes AND rm.anio = :anio AND c.tipo = 'ingreso' AND c.activo = 1 AND rm.pagado = 1"
);
$stmt->execute(['mes' => $mes, 'anio' => $anio]);
$ingresosCobrados = (float)($stmt->fetchColumn() ?? 0);
echo "6. INGRESOS COBRADOS: $" . number_format($ingresosCobrados, 2, ',', '.') . "\n";

echo "\n=== VERIFICACIÓN DE MOVIMIENTOS ===\n\n";

// Verificar que los movimientos cuadren con los saldos
$stmt = $db->prepare(
    "SELECT c.id, c.nombre,
            c.saldo_actual,
            COALESCE(SUM(CASE WHEN m.tipo = 'ingreso' THEN m.importe ELSE 0 END), 0) as total_ingresos,
            COALESCE(SUM(CASE WHEN m.tipo = 'pago_gasto' THEN m.importe ELSE 0 END), 0) as total_gastos
     FROM cuentas c
     LEFT JOIN movimientos_cuenta m ON c.id IN (m.cuenta_origen_id, m.cuenta_destino_id)
     WHERE c.activo = 1
     GROUP BY c.id, c.nombre, c.saldo_actual
     ORDER BY c.id"
);
$stmt->execute();
$cuentas = $stmt->fetchAll();

foreach ($cuentas as $c) {
    echo "Cuenta: {$c['nombre']}\n";
    echo "  - Saldo: $" . number_format($c['saldo_actual'], 2, ',', '.') . "\n";
    echo "  - Ingresos registrados: $" . number_format($c['total_ingresos'], 2, ',', '.') . "\n";
    echo "  - Gastos registrados: $" . number_format($c['total_gastos'], 2, ',', '.') . "\n\n";
}

echo "=== FIN TEST ===\n\n";
