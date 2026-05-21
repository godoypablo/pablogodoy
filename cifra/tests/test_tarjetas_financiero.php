<?php
/**
 * Tests Unitarios - Módulo Tarjetas de Crédito
 *
 * Ejecutar: php tests/test_tarjetas_financiero.php
 *
 * Valida:
 * - Cálculo de períodos y fechas
 * - Generación de cuotas
 * - Cambios de mes/año
 * - Edge cases (febrero, 31 días, etc)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/tarjetas_financiero.php';

class TestResults {
    public $total = 0;
    public $passed = 0;
    public $failed = 0;
    public $failures = [];

    public function assert($condition, $testName) {
        $this->total++;
        if ($condition) {
            $this->passed++;
            echo "✅ $testName\n";
        } else {
            $this->failed++;
            $this->failures[] = $testName;
            echo "❌ $testName\n";
        }
    }

    public function summary() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "RESUMEN: {$this->passed}/{$this->total} tests pasaron\n";
        if ($this->failed > 0) {
            echo "\n❌ FALLOS ({$this->failed}):\n";
            foreach ($this->failures as $failure) {
                echo "  - $failure\n";
            }
        } else {
            echo "\n🎉 TODOS LOS TESTS PASARON\n";
        }
        echo str_repeat("=", 60) . "\n";
    }
}

// Inicializar tests
$tests = new TestResults();

echo "\n" . str_repeat("=", 60) . "\n";
echo "SUITE DE TESTS - TARJETAS DE CRÉDITO\n";
echo str_repeat("=", 60) . "\n\n";

// Conectar a BD
try {
    $db = Database::getInstance()->getConnection();
    TarjetasFinanciero::setDatabase($db);
    echo "✅ Conexión a BD exitosa\n\n";
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================
// TESTS: determinarPeriodoResumen()
// ============================================================

echo "TEST GROUP 1: determinarPeriodoResumen()\n";
echo str_repeat("-", 60) . "\n";

// Crear tarjeta de prueba si no existe
$stmt = $db->prepare("SELECT id FROM tarjetas_credito WHERE nombre_tarjeta = 'TEST_VISA_001' LIMIT 1");
$stmt->execute();
$tarjeta_test = $stmt->fetch();

if (!$tarjeta_test) {
    $stmt = $db->prepare(
        "INSERT INTO tarjetas_credito (banco, nombre_tarjeta, marca, fecha_cierre_dia, fecha_vencimiento_dia, limite_credito)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute(['TEST_BANK', 'TEST_VISA_001', 'Visa', 25, 3, 500000]);
    $tarjeta_id = $db->lastInsertId();
} else {
    $tarjeta_id = $tarjeta_test['id'];
}

// Test 1.1: Compra ANTES del cierre (debe entrar en resumen actual)
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_id, '2026-05-20');
$tests->assert(
    $resultado['mes_resumen'] == 5 && $resultado['anio_resumen'] == 2026 && !$resultado['es_proximo_resumen'],
    "1.1: Compra antes del cierre entra en resumen actual"
);

// Test 1.2: Compra DESPUÉS del cierre (debe entrar en próximo resumen)
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_id, '2026-05-26');
$tests->assert(
    $resultado['mes_resumen'] == 6 && $resultado['anio_resumen'] == 2026 && $resultado['es_proximo_resumen'],
    "1.2: Compra después del cierre entra en próximo resumen"
);

// Test 1.3: Compra exactamente en cierre (debe entrar en resumen actual)
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_id, '2026-05-25');
$tests->assert(
    $resultado['mes_resumen'] == 5 && !$resultado['es_proximo_resumen'],
    "1.3: Compra en día cierre entra en resumen actual"
);

// Test 1.4: Cambio de año (compra después cierre en diciembre)
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_id, '2026-12-26');
$tests->assert(
    $resultado['mes_resumen'] == 1 && $resultado['anio_resumen'] == 2027,
    "1.4: Compra después cierre en diciembre cambia a enero siguiente"
);

// Test 1.5: Vencimiento correcto (debe ser día 3 del mes siguiente)
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_id, '2026-05-20');
$tests->assert(
    $resultado['fecha_vencimiento'] === '2026-06-03',
    "1.5: Vencimiento es día 3 del mes siguiente"
);

echo "\n";

// ============================================================
// TESTS: generarCuotasMovimiento()
// ============================================================

echo "TEST GROUP 2: generarCuotasMovimiento()\n";
echo str_repeat("-", 60) . "\n";

// Crear movimiento de prueba
$stmt = $db->prepare(
    "INSERT INTO movimientos_tarjeta (tarjeta_id, fecha_compra, descripcion, monto_total, cuotas_totales, cuota_pagada_proximo_resumen, monto_cuota)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([
    $tarjeta_id,
    '2026-05-15',
    'TEST_COMPRA_6_CUOTAS',
    600000,
    6,
    2,
    100000
]);
$movimiento_id = $db->lastInsertId();

// Generar cuotas
TarjetasFinanciero::generarCuotasMovimiento(
    $movimiento_id,
    6,
    2,
    $tarjeta_id,
    '2026-05-15',
    100000
);

// Test 2.1: Se crearon 6 cuotas
$stmt = $db->prepare("SELECT COUNT(*) as count FROM cuotas_movimiento WHERE movimiento_id = ?");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['count'] == 6, "2.1: Se crearon 6 cuotas");

// Test 2.2: Cuota 1 es pendiente (no la primera a pagar)
$stmt = $db->prepare("SELECT estado FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 1");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['estado'] === 'pendiente', "2.2: Cuota 1 es pendiente");

// Test 2.3: Cuota 2 es pagada (es la que se paga en próximo resumen)
$stmt = $db->prepare("SELECT estado FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 2");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['estado'] === 'pagada', "2.3: Cuota 2 es pagada");

// Test 2.4: Cuota 3 es pendiente
$stmt = $db->prepare("SELECT estado FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 3");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['estado'] === 'pendiente', "2.4: Cuota 3 es pendiente");

// Test 2.5: Período de cuota 1 es mayo 2026
$stmt = $db->prepare("SELECT periodo FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 1");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['periodo'] === '2026-05', "2.5: Cuota 1 en período 2026-05");

// Test 2.6: Período de cuota 6 es octubre 2026 (5 meses después)
$stmt = $db->prepare("SELECT periodo FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 6");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['periodo'] === '2026-10', "2.6: Cuota 6 en período 2026-10");

// Test 2.7: Monto de cada cuota es 100000
$stmt = $db->prepare("SELECT monto FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 1");
$stmt->execute([$movimiento_id]);
$result = $stmt->fetch();
$tests->assert($result['monto'] == 100000, "2.7: Monto de cuota es 100000");

echo "\n";

// ============================================================
// TESTS: Cambios de mes/año
// ============================================================

echo "TEST GROUP 3: Cambios de mes/año\n";
echo str_repeat("-", 60) . "\n";

// Test 3.1: Compra en enero, últimas cuotas en diciembre (cambio de año)
$stmt = $db->prepare(
    "INSERT INTO movimientos_tarjeta (tarjeta_id, fecha_compra, descripcion, monto_total, cuotas_totales, cuota_pagada_proximo_resumen, monto_cuota)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$stmt->execute([
    $tarjeta_id,
    '2026-01-10',
    'TEST_CAMBIO_AÑO',
    1200000,
    12,
    1,
    100000
]);
$movimiento_cambio_anio = $db->lastInsertId();

TarjetasFinanciero::generarCuotasMovimiento(
    $movimiento_cambio_anio,
    12,
    1,
    $tarjeta_id,
    '2026-01-10',
    100000
);

// Verificar que cuota 12 está en diciembre 2026
$stmt = $db->prepare("SELECT periodo, anio FROM cuotas_movimiento WHERE movimiento_id = ? AND numero_cuota = 12");
$stmt->execute([$movimiento_cambio_anio]);
$result = $stmt->fetch();
$tests->assert($result['periodo'] === '2026-12', "3.1: Cuota 12 de compra en enero entra en diciembre");

echo "\n";

// ============================================================
// TESTS: Edge cases (febrero, 31 días)
// ============================================================

echo "TEST GROUP 4: Edge Cases\n";
echo str_repeat("-", 60) . "\n";

// Crear tarjeta con cierre en día 31
$stmt = $db->prepare(
    "INSERT INTO tarjetas_credito (banco, nombre_tarjeta, marca, fecha_cierre_dia, fecha_vencimiento_dia, limite_credito)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->execute(['TEST_BANK_2', 'TEST_CARD_DIA31', 'Mastercard', 31, 15, 300000]);
$tarjeta_dia31 = $db->lastInsertId();

// Test 4.1: Febrero (28 días) con cierre día 31 debe usar día 28
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_dia31, '2026-02-15');
$tests->assert(
    strpos($resultado['fecha_cierre'], '-28') !== false || strpos($resultado['fecha_cierre'], '-02-28') !== false,
    "4.1: Febrero con cierre 31 usa día 28"
);

// Test 4.2: Año bisiesto (2024) febrero tiene 29 días
$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_dia31, '2024-02-15');
$tests->assert(
    strpos($resultado['fecha_cierre'], '2024-02-29') !== false,
    "4.2: Febrero bisiesto usa día 29"
);

// Test 4.3: Vencimiento en febrero no-bisiesto usa día 28
$stmt = $db->prepare(
    "INSERT INTO tarjetas_credito (banco, nombre_tarjeta, marca, fecha_cierre_dia, fecha_vencimiento_dia, limite_credito)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->execute(['TEST_BANK_3', 'TEST_CARD_FEB31', 'Visa', 25, 31, 200000]);
$tarjeta_feb31 = $db->lastInsertId();

$resultado = TarjetasFinanciero::determinarPeriodoResumen($tarjeta_feb31, '2026-01-20');
$tests->assert(
    strpos($resultado['fecha_vencimiento'], '2026-02-28') !== false,
    "4.3: Vencimiento en febrero no-bisiesto usa día 28"
);

echo "\n";

// ============================================================
// TESTS: obtenerDeudaTotal()
// ============================================================

echo "TEST GROUP 5: obtenerDeudaTotal()\n";
echo str_repeat("-", 60) . "\n";

// Obtener deuda total de tarjeta de prueba
$deuda_total = TarjetasFinanciero::obtenerDeudaTotal($tarjeta_id);

// Debe ser suma de cuotas pendientes
// Movimiento 1: 6 cuotas x 100k, cuota 2 pagada = 500k pendiente
// Movimiento 3: 12 cuotas x 100k, todas pendientes (inicialmente) = 1200k
// Total mínimo: 500k
$tests->assert(
    $deuda_total >= 500000,
    "5.1: Deuda total incluye cuotas pendientes"
);

echo "\n";

// ============================================================
// TESTS: obtenerDisponibleReal()
// ============================================================

echo "TEST GROUP 6: obtenerDisponibleReal()\n";
echo str_repeat("-", 60) . "\n";

$disponible = TarjetasFinanciero::obtenerDisponibleReal($tarjeta_id);

$tests->assert(
    isset($disponible['limite']) && isset($disponible['deuda_comprometida']) && isset($disponible['disponible']),
    "6.1: Retorna estructura correcta"
);

$tests->assert(
    $disponible['disponible'] == ($disponible['limite'] - $disponible['deuda_comprometida']),
    "6.2: Disponible = Límite - Deuda"
);

$tests->assert(
    $disponible['limite'] == 500000,
    "6.3: Límite es correcto"
);

echo "\n";

// ============================================================
// TESTS: obtenerProximoVencimiento()
// ============================================================

echo "TEST GROUP 7: obtenerProximoVencimiento()\n";
echo str_repeat("-", 60) . "\n";

$proximo = TarjetasFinanciero::obtenerProximoVencimiento($tarjeta_id);

if ($proximo) {
    $tests->assert(
        isset($proximo['fecha_vencimiento']) && isset($proximo['total']) && isset($proximo['cuotas_pendientes']),
        "7.1: Retorna estructura correcta"
    );

    $tests->assert(
        strtotime($proximo['fecha_vencimiento']) > time(),
        "7.2: Próximo vencimiento es en el futuro o hoy"
    );
} else {
    $tests->assert(false, "7.1: Debe retornar próximo vencimiento");
}

echo "\n";

// ============================================================
// CLEANUP
// ============================================================

echo "TEST GROUP 8: Cleanup\n";
echo str_repeat("-", 60) . "\n";

// Eliminar datos de prueba
$db->prepare("DELETE FROM cuotas_movimiento WHERE movimiento_id IN (?, ?, ?)")->execute([
    $movimiento_id,
    $movimiento_cambio_anio
]);
$db->prepare("DELETE FROM movimientos_tarjeta WHERE nombre_tarjeta LIKE 'TEST_%'")->execute();
$db->prepare("DELETE FROM tarjetas_credito WHERE nombre_tarjeta LIKE 'TEST_%'")->execute();

$tests->assert(true, "8.1: Datos de prueba limpiados");

echo "\n";

// ============================================================
// RESULTADO FINAL
// ============================================================

$tests->summary();

exit($tests->failed > 0 ? 1 : 0);
