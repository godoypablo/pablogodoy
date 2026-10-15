<?php
/**
 * "Cuota Crédito" (planilla, categoría Bancos) se completa sola desde
 * Cuotas hipotecarias: importe = "Cuota Bco ($)" (total_uva × valor_uva) y
 * fecha de vencimiento de la cuota que vence en el mes mostrado.
 *
 * - Sin cuota ese mes o sin valor UVA cargado → no toca nada.
 * - Registro ya pagado → no se modifica (la plata ya salió con ese importe).
 */

const CONCEPTO_CUOTA_CREDITO_ID = 35;

function sincronizarCuotaCredito(PDO $db, int $mes, int $anio): void {
    $stmt = $db->prepare("SELECT id, cuenta_id_default FROM conceptos WHERE id = ? AND activo = 1");
    $stmt->execute([CONCEPTO_CUOTA_CREDITO_ID]);
    $concepto = $stmt->fetch();
    if (!$concepto) return;

    $desde = sprintf('%04d-%02d-01', $anio, $mes);
    $hasta = date('Y-m-d', strtotime("$desde +1 month"));

    $stmt = $db->prepare(
        "SELECT nro_cuota, fecha_vencimiento, ROUND(total_uva * valor_uva, 2) AS importe
         FROM cuotas_hipoteca
         WHERE fecha_vencimiento >= ? AND fecha_vencimiento < ?
         ORDER BY nro_cuota LIMIT 1"
    );
    $stmt->execute([$desde, $hasta]);
    $cuota = $stmt->fetch();
    if (!$cuota || (float)$cuota['importe'] <= 0) return;

    $importe = (float)$cuota['importe'];
    $vence   = $cuota['fecha_vencimiento'];

    $stmt = $db->prepare(
        "SELECT id, importe, fecha_vencimiento, pagado FROM registros_mensuales
         WHERE concepto_id = ? AND mes = ? AND anio = ? ORDER BY id LIMIT 1"
    );
    $stmt->execute([CONCEPTO_CUOTA_CREDITO_ID, $mes, $anio]);
    $reg = $stmt->fetch();

    if (!$reg) {
        $db->prepare(
            "INSERT INTO registros_mensuales (concepto_id, mes, anio, importe, fecha_vencimiento, cuenta_id, pagado, observaciones)
             VALUES (?, ?, ?, ?, ?, ?, 0, ?)"
        )->execute([CONCEPTO_CUOTA_CREDITO_ID, $mes, $anio, $importe, $vence,
                    $concepto['cuenta_id_default'] ?: null, "Cuota hipotecaria Nº {$cuota['nro_cuota']}"]);
        return;
    }

    if ((int)$reg['pagado'] === 1) return;

    if (abs((float)$reg['importe'] - $importe) > 0.005 || $reg['fecha_vencimiento'] !== $vence) {
        $db->prepare("UPDATE registros_mensuales SET importe = ?, fecha_vencimiento = ? WHERE id = ?")
           ->execute([$importe, $vence, $reg['id']]);
    }
}
