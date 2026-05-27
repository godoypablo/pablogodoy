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
     * Obtiene fecha de vencimiento del PRIMER cierre cuya fecha_cierre >= fecha_compra
     *
     * NUEVA LÓGICA (simplificada):
     * - Busca en cierres_tarjeta el primer registro cuya fecha_cierre >= fecha_compra
     * - Devuelve su fecha_vencimiento real
     * - NO calcula, solo consulta
     *
     * @param int $tarjeta_id
     * @param string $fecha_compra (YYYY-MM-DD)
     * @return string fecha_vencimiento (YYYY-MM-DD)
     *
     * Ejemplo:
     * - Compra 20/05/2026
     * - cierres_tarjeta: 28/05 → vence 04/06
     * - Resultado: 04/06/2026 ✓
     */
    public static function calcularPrimerVencimiento($tarjeta_id, $fecha_compra) {
        $db = self::getDb();

        // Buscar el primer cierre >= fecha_compra
        $stmt = $db->prepare(
            "SELECT fecha_vencimiento FROM cierres_tarjeta
             WHERE tarjeta_id = ? AND fecha_cierre >= ?
             ORDER BY fecha_cierre ASC
             LIMIT 1"
        );
        $stmt->execute([$tarjeta_id, $fecha_compra]);
        $cierre = $stmt->fetch();

        if ($cierre && $cierre['fecha_vencimiento']) {
            return $cierre['fecha_vencimiento'];
        }

        // Fallback: si no hay cierres configurados, lanzar excepción
        throw new Exception(
            "No hay cierres configurados para la tarjeta $tarjeta_id después de $fecha_compra. " .
            "Configura cierres_tarjeta antes de crear movimientos."
        );
    }

    // ============================================================
    // FUNCIÓN CORE #2: Generar Cuotas
    // ============================================================

    /**
     * Genera N cuotas usando cierres_tarjeta como fuente de verdad
     *
     * NUEVA LÓGICA:
     * - Buscar el PRIMER cierre cuya fecha_cierre >= fecha_compra
     * - Para cada cuota, obtener el siguiente cierre secuencialmente
     * - Asociar cierre_id real a cada cuota
     * - Soporta movimientos históricos (compras pasadas)
     *
     * @param int $movimiento_id
     * @param int $tarjeta_id
     * @param string $fecha_compra (YYYY-MM-DD)
     * @param int $cuotas_totales
     * @param float $monto_total
     * @return bool
     */
    public static function generarCuotas($movimiento_id, $tarjeta_id, $fecha_compra, $cuotas_totales, $monto_total) {
        $db = self::getDb();

        try {
            // Calcular montos
            $monto_base = round($monto_total / $cuotas_totales, 2);
            $suma_base = round($monto_base * ($cuotas_totales - 1), 2);
            $monto_ultima = round($monto_total - $suma_base, 2);

            // Buscar cierres secuencialmente: primer cierre >= fecha_compra
            $stmt = $db->prepare(
                "SELECT id, fecha_vencimiento FROM cierres_tarjeta
                 WHERE tarjeta_id = ? AND fecha_cierre >= ?
                 ORDER BY fecha_cierre ASC"
            );
            $stmt->execute([$tarjeta_id, $fecha_compra]);
            $cierres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($cierres) < $cuotas_totales) {
                throw new Exception(
                    "No hay suficientes cierres configurados. Movimiento necesita $cuotas_totales cierres, pero solo hay " . count($cierres)
                );
            }

            // Generar cuotas asignando cada una a su cierre real
            for ($i = 1; $i <= $cuotas_totales; $i++) {
                $monto = ($i === $cuotas_totales) ? $monto_ultima : $monto_base;
                $cierre = $cierres[$i - 1];
                $cierre_id = $cierre['id'];
                $fecha_vencimiento = $cierre['fecha_vencimiento'];

                $stmt = $db->prepare(
                    "INSERT INTO cuotas_movimiento
                    (movimiento_id, cierre_id, numero_cuota, fecha_vencimiento, monto, pagada, fecha_pago)
                    VALUES (?, ?, ?, ?, ?, 0, NULL)"
                );
                $stmt->execute([$movimiento_id, $cierre_id, $i, $fecha_vencimiento, $monto]);
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
                  AND cm.fecha_vencimiento >= CURDATE()
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
                  AND cm.fecha_vencimiento >= CURDATE()
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

    // ============================================================
    // SINCRONIZAR: Tarjeta → Concepto de Gasto
    // ============================================================

    /**
     * Calcula el total de cuotas NO PAGADAS de una tarjeta para un mes/año específico,
     * aplica un ajuste, y hace upsert en registros_mensuales del concepto asociado.
     *
     * @param int $tarjeta_id
     * @param int $mes (1-12)
     * @param int $anio
     * @param float $ajuste (suma o resta al total calculado)
     * @return array { total_cuotas, ajuste, total_final, registro_id, concepto_id }
     */
    public static function sincronizarConConcepto($tarjeta_id, $mes, $anio, $ajuste = 0) {
        $db = self::getDb();

        // 1. Obtener concepto_id de la tarjeta
        $stmtTarjeta = $db->prepare("SELECT concepto_id FROM tarjetas_credito WHERE id = ?");
        $stmtTarjeta->execute([$tarjeta_id]);
        $tarjeta = $stmtTarjeta->fetch();

        if (!$tarjeta || !$tarjeta['concepto_id']) {
            throw new Exception("Tarjeta sin concepto asociado");
        }

        $concepto_id = (int)$tarjeta['concepto_id'];

        // 2. Calcular total de cuotas NO PAGADAS para ese mes/año
        $sql = "SELECT SUM(cm.monto) as total
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND YEAR(cm.fecha_vencimiento) = ?
                  AND MONTH(cm.fecha_vencimiento) = ?
                  AND cm.pagada = 0
                  AND mt.fecha_cancelacion IS NULL";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id, $anio, $mes]);
        $result = $stmt->fetch();

        $total_cuotas = $result['total'] ? (float)$result['total'] : 0;
        $total_final = $total_cuotas + $ajuste;

        // 3. Upsert en registros_mensuales
        $sqlUpsert = "INSERT INTO registros_mensuales
                      (concepto_id, mes, anio, importe, pagado, observaciones)
                      VALUES (?, ?, ?, ?, 1, 'Sincronizado desde tarjetas de crédito')
                      ON DUPLICATE KEY UPDATE
                      importe = VALUES(importe),
                      observaciones = 'Sincronizado desde tarjetas de crédito'";

        $stmtUpsert = $db->prepare($sqlUpsert);
        $stmtUpsert->execute([$concepto_id, $mes, $anio, $total_final]);

        // Obtener ID del registro (si es INSERT nuevo)
        $registro_id = $db->lastInsertId();

        // Si fue UPDATE, obtener el ID existente
        if (!$registro_id) {
            $stmtGet = $db->prepare(
                "SELECT id FROM registros_mensuales WHERE concepto_id = ? AND mes = ? AND anio = ?"
            );
            $stmtGet->execute([$concepto_id, $mes, $anio]);
            $reg = $stmtGet->fetch();
            $registro_id = $reg ? (int)$reg['id'] : null;
        }

        return [
            'total_cuotas' => $total_cuotas,
            'ajuste' => $ajuste,
            'total_final' => $total_final,
            'registro_id' => $registro_id,
            'concepto_id' => $concepto_id
        ];
    }
}
