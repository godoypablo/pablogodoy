<?php
/**
 * Lógica Financiera - Módulo Tarjetas v3 (SIMPLIFICADO)
 *
 * Modelo simple: tarjetas → movimientos → cuotas
 * Sin períodos, resúmenes, ni lógica contable compleja.
 *
 * Solo responde: ¿cuánto pago en cada vencimiento futuro?
 */

class TarjetasFinanciero {

    private static $db = null;

    public static function setDatabase($database) {
        self::$db = $database;
    }

    private static function getDb() {
        if (!self::$db) {
            require_once __DIR__ . '/../config/database.php';
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    // ============================================================
    // FUNCIÓN CORE #1: Calcular Primer Vencimiento
    // ============================================================

    /**
     * Dado una compra y configuración de tarjeta, calcula cuándo vence la primera cuota
     *
     * LÓGICA:
     * - Si día_compra <= fecha_cierre_dia → vence en cierre actual + 1 mes
     * - Si día_compra > fecha_cierre_dia → vence en cierre siguiente + 1 mes
     *
     * @param int $tarjeta_id
     * @param string $fecha_compra (YYYY-MM-DD)
     * @return string fecha_vencimiento (YYYY-MM-DD)
     *
     * Ejemplo:
     * - Tarjeta cierre=28, vencimiento=4
     * - Compra 12/05/2026 → 12 <= 28 → vence 04/06/2026 ✓
     * - Compra 30/05/2026 → 30 > 28 → vence 04/07/2026 ✓
     */
    public static function calcularPrimerVencimiento($tarjeta_id, $fecha_compra) {
        $db = self::getDb();

        // Parsear fecha de compra
        $fecha = new DateTime($fecha_compra);
        $dia_compra = (int)$fecha->format('d');
        $mes_compra = (int)$fecha->format('m');
        $anio_compra = (int)$fecha->format('Y');

        // Obtener configuración de tarjeta
        $stmt = $db->prepare("SELECT fecha_cierre_dia, fecha_vencimiento_dia FROM tarjetas_credito WHERE id = ?");
        $stmt->execute([$tarjeta_id]);
        $tarjeta = $stmt->fetch();

        if (!$tarjeta) {
            throw new Exception("Tarjeta no encontrada: $tarjeta_id");
        }

        $cierre_dia = (int)$tarjeta['fecha_cierre_dia'];

        // Lógica: ¿en qué cierre entra la compra?
        if ($dia_compra <= $cierre_dia) {
            // Entra en cierre actual → obtener fecha_vencimiento del mes siguiente
            $mes_venc = $mes_compra + 1;
            $anio_venc = $anio_compra;
        } else {
            // Entra en próximo cierre → obtener fecha_vencimiento del mes siguiente al próximo cierre
            $mes_venc = $mes_compra + 2;
            $anio_venc = $anio_compra;
        }

        // Ajustar año si mes > 12
        while ($mes_venc > 12) {
            $mes_venc -= 12;
            $anio_venc++;
        }

        // Intentar obtener fecha_vencimiento específica de la tabla cierres_tarjeta
        $stmt = $db->prepare(
            "SELECT fecha_vencimiento FROM cierres_tarjeta
             WHERE tarjeta_id = ? AND anio = ? AND mes = ?"
        );
        $stmt->execute([$tarjeta_id, $anio_venc, $mes_venc]);
        $cierre = $stmt->fetch();

        if ($cierre && $cierre['fecha_vencimiento']) {
            return $cierre['fecha_vencimiento'];
        }

        // Si no existe en cierres_tarjeta, generar automáticamente usando valores base
        $vencimiento_dia = (int)$tarjeta['fecha_vencimiento_dia'];
        $dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes_venc, $anio_venc);
        $dia_venc = min($vencimiento_dia, $dias_en_mes);

        $fecha_vencimiento = new DateTime("$anio_venc-" . str_pad($mes_venc, 2, '0', STR_PAD_LEFT) . "-$dia_venc");
        $fecha_vencimiento_str = $fecha_vencimiento->format('Y-m-d');

        // También generar el fecha_cierre automáticamente para guardar en BD
        $dias_en_mes_cierre = cal_days_in_month(CAL_GREGORIAN, $mes_venc, $anio_venc);
        $dia_cierre = min($cierre_dia, $dias_en_mes_cierre);
        $fecha_cierre_auto = new DateTime("$anio_venc-" . str_pad($mes_venc, 2, '0', STR_PAD_LEFT) . "-$dia_cierre");

        // Guardar en cierres_tarjeta para futuras consultas
        try {
            $stmtInsert = $db->prepare(
                "INSERT IGNORE INTO cierres_tarjeta (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento)
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmtInsert->execute([$tarjeta_id, $anio_venc, $mes_venc, $fecha_cierre_auto->format('Y-m-d'), $fecha_vencimiento_str]);
        } catch (Exception $e) {
            // Si falla el insert, no importa, ya devolvemos el resultado
        }

        return $fecha_vencimiento_str;
    }

    // ============================================================
    // FUNCIÓN CORE #2: Generar Cuotas
    // ============================================================

    /**
     * Genera N cuotas con fechas y montos correctos
     *
     * LÓGICA:
     * - Calcular monto_base = monto_total / cuotas
     * - Generar cuotas_totales-1 cuotas de monto_base
     * - Última cuota = monto_total - suma anterior (ajusta centavos)
     * - Fechas: primer_venc, primer_venc+1mes, primer_venc+2meses...
     *
     * @param int $movimiento_id
     * @param int $cuotas_totales
     * @param float $monto_total
     * @param string $primer_vencimiento (YYYY-MM-DD)
     * @return bool
     */
    public static function generarCuotas($movimiento_id, $cuotas_totales, $monto_total, $primer_vencimiento) {
        $db = self::getDb();

        try {
            // Calcular monto de cada cuota
            $monto_base = round($monto_total / $cuotas_totales, 2);
            $suma_base = round($monto_base * ($cuotas_totales - 1), 2);
            $monto_ultima = round($monto_total - $suma_base, 2);

            // Parsear primer vencimiento
            $fecha_venc = new DateTime($primer_vencimiento);

            // Generar cuotas
            for ($i = 1; $i <= $cuotas_totales; $i++) {
                $monto = ($i === $cuotas_totales) ? $monto_ultima : $monto_base;
                $fecha_venc_cuota = $fecha_venc->format('Y-m-d');

                $stmt = $db->prepare(
                    "INSERT INTO cuotas_movimiento
                    (movimiento_id, numero_cuota, fecha_vencimiento, monto, pagada, fecha_pago)
                    VALUES (?, ?, ?, ?, 0, NULL)"
                );
                $stmt->execute([$movimiento_id, $i, $fecha_venc_cuota, $monto]);

                // Avanzar a siguiente mes
                $fecha_venc->add(new DateInterval('P1M'));
            }

            return true;

        } catch (Exception $e) {
            throw new Exception("Error generando cuotas: " . $e->getMessage());
        }
    }

    // ============================================================
    // FUNCIÓN CORE #3: Deuda Pendiente
    // ============================================================

    /**
     * Suma de todas las cuotas no pagadas
     */
    public static function obtenerDeudaPendiente($tarjeta_id) {
        $db = self::getDb();

        $sql = "SELECT COALESCE(SUM(cm.monto), 0) as total
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.pagada = 0
                  AND mt.fecha_cancelacion IS NULL";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $result = $stmt->fetch();

        return (float)($result['total'] ?? 0);
    }

    // ============================================================
    // FUNCIÓN CORE #4: Límite Disponible Real
    // ============================================================

    /**
     * límite - (suma de monto_total de movimientos activos)
     *
     * IMPORTANTE: Descuenta el TOTAL de cada compra, no solo la próxima cuota
     */
    public static function obtenerLimiteDisponible($tarjeta_id) {
        $db = self::getDb();

        $sql = "SELECT
                    tc.limite_credito,
                    COALESCE(SUM(mt.monto_total), 0) as deuda_comprometida
                FROM tarjetas_credito tc
                LEFT JOIN movimientos_tarjeta mt ON mt.tarjeta_id = tc.id
                    AND mt.fecha_cancelacion IS NULL
                WHERE tc.id = ?
                GROUP BY tc.id";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $result = $stmt->fetch();

        if (!$result) {
            throw new Exception("Tarjeta no encontrada: $tarjeta_id");
        }

        $limite = (float)$result['limite_credito'];
        $deuda = (float)$result['deuda_comprometida'];
        $disponible = $limite - $deuda;

        return [
            'limite' => $limite,
            'deuda_comprometida' => $deuda,
            'disponible' => $disponible
        ];
    }

    // ============================================================
    // FUNCIÓN CORE #5: Próximo Vencimiento
    // ============================================================

    /**
     * Próximo vencimiento consolidado: fecha + total de ese día
     */
    public static function obtenerProximoVencimiento($tarjeta_id) {
        $db = self::getDb();

        $sql = "SELECT
                    MIN(cm.fecha_vencimiento) as fecha_vencimiento,
                    SUM(cm.monto) as total,
                    COUNT(*) as cuota_count
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.pagada = 0
                  AND mt.fecha_cancelacion IS NULL";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $result = $stmt->fetch();

        if (!$result || !$result['fecha_vencimiento']) {
            return null;
        }

        return [
            'fecha_vencimiento' => $result['fecha_vencimiento'],
            'total' => (float)$result['total'],
            'cuota_count' => (int)$result['cuota_count']
        ];
    }

    // ============================================================
    // FUNCIÓN CORE #6: Vencimientos Consolidados por Fecha
    // ============================================================

    /**
     * Agrupa cuotas por fecha de vencimiento
     * Para mostrar: "04/06: $60.666 (3 cuotas de diferentes compras)"
     *
     * Retorna:
     * [
     *   {
     *     fecha_vencimiento: "2026-06-04",
     *     total: 60666,
     *     cuotas: [
     *       { movimiento_id, descripcion, numero_cuota, cuotas_totales, monto }
     *     ]
     *   }
     * ]
     */
    public static function obtenerVencimientosConsolidados($tarjeta_id) {
        $db = self::getDb();

        $sql = "SELECT
                    cm.id as cuota_id,
                    cm.fecha_vencimiento,
                    mt.id as movimiento_id,
                    mt.descripcion,
                    cm.numero_cuota,
                    mt.cuotas_totales,
                    cm.monto
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.pagada = 0
                  AND mt.fecha_cancelacion IS NULL
                ORDER BY cm.fecha_vencimiento ASC, mt.id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $cuotas = $stmt->fetchAll();

        // Agrupar por fecha
        $consolidado = [];
        foreach ($cuotas as $cuota) {
            $fecha = $cuota['fecha_vencimiento'];

            if (!isset($consolidado[$fecha])) {
                $consolidado[$fecha] = [
                    'fecha_vencimiento' => $fecha,
                    'total' => 0,
                    'cuotas' => []
                ];
            }

            $consolidado[$fecha]['total'] += (float)$cuota['monto'];
            $consolidado[$fecha]['cuotas'][] = [
                'cuota_id' => (int)$cuota['cuota_id'],
                'movimiento_id' => (int)$cuota['movimiento_id'],
                'descripcion' => $cuota['descripcion'],
                'numero_cuota' => (int)$cuota['numero_cuota'],
                'cuotas_totales' => (int)$cuota['cuotas_totales'],
                'monto' => (float)$cuota['monto']
            ];
        }

        return array_values($consolidado);
    }

    // ============================================================
    // FUNCIÓN CORE #7: Cuotas Pendientes (lista simple)
    // ============================================================

    /**
     * Lista simple de próximas cuotas (para listados)
     */
    public static function obtenerCuotasPendientes($tarjeta_id, $limit = 12) {
        $db = self::getDb();

        $sql = "SELECT
                    cm.id,
                    cm.numero_cuota,
                    cm.fecha_vencimiento,
                    cm.monto,
                    mt.descripcion,
                    mt.cuotas_totales
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.pagada = 0
                  AND mt.fecha_cancelacion IS NULL
                ORDER BY cm.fecha_vencimiento ASC
                LIMIT ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id, $limit]);

        $cuotas = $stmt->fetchAll();
        foreach ($cuotas as &$c) {
            $c['numero_cuota'] = (int)$c['numero_cuota'];
            $c['cuotas_totales'] = (int)$c['cuotas_totales'];
            $c['monto'] = (float)$c['monto'];
        }

        return $cuotas;
    }

    // ============================================================
    // OPERACIONES: Marcar Pagada
    // ============================================================

    /**
     * Marca una cuota como pagada
     */
    public static function marcarCuotaPagada($cuota_id) {
        $db = self::getDb();

        $sql = "UPDATE cuotas_movimiento
                SET pagada = 1, fecha_pago = NOW()
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([$cuota_id]);
    }

    // ============================================================
    // OPERACIONES: Anular Movimiento
    // ============================================================

    /**
     * Anula un movimiento (marca fecha_cancelacion, no borra)
     * Las cuotas quedan en la BD para auditoría
     */
    public static function anularMovimiento($movimiento_id) {
        $db = self::getDb();

        $sql = "UPDATE movimientos_tarjeta
                SET fecha_cancelacion = NOW()
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([$movimiento_id]);
    }

    // ============================================================
    // HELPER: Obtener Tarjeta
    // ============================================================

    public static function obtenerTarjeta($tarjeta_id) {
        $db = self::getDb();

        $stmt = $db->prepare("SELECT * FROM tarjetas_credito WHERE id = ?");
        $stmt->execute([$tarjeta_id]);
        return $stmt->fetch();
    }

    // ============================================================
    // HELPER: Obtener Movimiento
    // ============================================================

    public static function obtenerMovimiento($movimiento_id) {
        $db = self::getDb();

        $sql = "SELECT mt.*, tc.nombre_tarjeta, tc.banco, tc.marca
                FROM movimientos_tarjeta mt
                INNER JOIN tarjetas_credito tc ON mt.tarjeta_id = tc.id
                WHERE mt.id = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$movimiento_id]);
        return $stmt->fetch();
    }

    // ============================================================
    // HELPER: Obtener Cuotas de un Movimiento
    // ============================================================

    public static function obtenerCuotasMovimiento($movimiento_id) {
        $db = self::getDb();

        $sql = "SELECT * FROM cuotas_movimiento
                WHERE movimiento_id = ?
                ORDER BY numero_cuota ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$movimiento_id]);
        return $stmt->fetchAll();
    }
}
