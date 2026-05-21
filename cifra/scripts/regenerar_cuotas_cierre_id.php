<?php
/**
 * Script de migración: Regenerar cuotas con cierre_id
 *
 * Propósito:
 * - Eliminar cuotas existentes
 * - Regenerarlas con cierre_id asignado correctamente
 * - Usar la nueva lógica de generarCuotas()
 *
 * Uso:
 * php scripts/regenerar_cuotas_cierre_id.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/tarjetas_financiero_v3.php';

$db = Database::getInstance()->getConnection();
TarjetasFinanciero::setDatabase($db);

try {
    echo "=== Iniciando regeneración de cuotas ===\n\n";

    // 1. Obtener todos los movimientos activos
    $stmt = $db->prepare(
        "SELECT id, tarjeta_id, fecha_compra, cuotas_totales, monto_total
         FROM movimientos_tarjeta
         WHERE fecha_cancelacion IS NULL"
    );
    $stmt->execute();
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Encontrados " . count($movimientos) . " movimientos activos\n";

    $db->beginTransaction();

    $regenerados = 0;
    $errores = 0;

    foreach ($movimientos as $mov) {
        echo "\n[" . $mov['id'] . "] " . $mov['fecha_compra'] . " - " .
             $mov['cuotas_totales'] . " cuotas @ " . $mov['monto_total'];

        try {
            // 2. Eliminar cuotas existentes de este movimiento
            $delStmt = $db->prepare("DELETE FROM cuotas_movimiento WHERE movimiento_id = ?");
            $delStmt->execute([$mov['id']]);
            $deletedCount = $delStmt->rowCount();

            // 3. Regenerar con la nueva lógica
            TarjetasFinanciero::generarCuotas(
                $mov['id'],
                $mov['tarjeta_id'],
                $mov['fecha_compra'],
                $mov['cuotas_totales'],
                $mov['monto_total']
            );

            echo " ✓ [eliminadas $deletedCount, regeneradas " . $mov['cuotas_totales'] . "]\n";
            $regenerados++;

        } catch (Exception $e) {
            echo " ✗ ERROR: " . $e->getMessage() . "\n";
            $errores++;
        }
    }

    $db->commit();

    echo "\n=== Resumen ===\n";
    echo "Regenerados: $regenerados\n";
    echo "Errores: $errores\n";
    echo "Total: " . ($regenerados + $errores) . "\n";

    if ($errores === 0) {
        echo "\n✓ Migración completada exitosamente\n";
    } else {
        echo "\n⚠ Migración completada con algunos errores. Verifica los movimientos fallidos.\n";
    }

} catch (Exception $e) {
    $db->rollBack();
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
