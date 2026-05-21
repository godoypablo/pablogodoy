<?php
/**
 * Lógica Financiera - Módulo Tarjetas de Crédito
 * Funciones reutilizables para cálculos y proyecciones
 *
 * IMPORTANTE: Todas las funciones reciben tarjeta_id y NO dependen del contexto global
 * Diseñadas para ser agnósticas de usuario logueado
 */

class TarjetasFinanciero {

    private static $db = null;

    /**
     * Inyectar instancia de base de datos
     */
    public static function setDatabase($database) {
        self::$db = $database;
    }

    /**
     * Obtener conexión a BD (singleton dentro de la clase)
     */
    private static function getDb() {
        if (!self::$db) {
            require_once __DIR__ . '/../config/database.php';
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    // ============================================================
    // FUNCIÓN CORE #1: Determinar Período y Fechas de Resumen
    // ============================================================

    /**
     * determinarPeriodoResumen()
     *
     * Dado una fecha de compra y datos de tarjeta, determina:
     * - En qué período del resumen entra la compra
     * - Fecha de cierre real
     * - Fecha de vencimiento real
     *
     * @param int $tarjeta_id
     * @param DateTime|string $fecha_compra (YYYY-MM-DD)
     *
     * @return array {
     *     'periodo' => '2026-05',
     *     'fecha_cierre' => '2026-05-25',
     *     'fecha_vencimiento' => '2026-06-03',
     *     'es_proximo_resumen' => false
     * }
     */
    public static function determinarPeriodoResumen($tarjeta_id, $fecha_compra) {
        $db = self::getDb();

        // Obtener tarjeta
        $stmt = $db->prepare("SELECT fecha_cierre_dia, fecha_vencimiento_dia FROM tarjetas_credito WHERE id = ?");
        $stmt->execute([$tarjeta_id]);
        $tarjeta = $stmt->fetch();

        if (!$tarjeta) {
            throw new Exception("Tarjeta no encontrada: $tarjeta_id");
        }

        $fecha_cierre_dia = (int)$tarjeta['fecha_cierre_dia'];
        $fecha_vencimiento_dia = (int)$tarjeta['fecha_vencimiento_dia'];

        // Parsear fecha de compra
        $fecha = new DateTime($fecha_compra);
        $dia_compra = (int)$fecha->format('d');
        $mes_compra = (int)$fecha->format('m');
        $anio_compra = (int)$fecha->format('Y');

        // LÓGICA: Si día_compra <= fecha_cierre_dia → entra en resumen ACTUAL
        //         Si día_compra > fecha_cierre_dia → entra en resumen PRÓXIMO

        if ($dia_compra <= $fecha_cierre_dia) {
            // Entra en resumen actual
            $mes_resumen = $mes_compra;
            $anio_resumen = $anio_compra;
            $es_proximo = false;
        } else {
            // Entra en resumen próximo
            $mes_resumen = $mes_compra + 1;
            $anio_resumen = $anio_compra;

            if ($mes_resumen > 12) {
                $mes_resumen = 1;
                $anio_resumen++;
            }
            $es_proximo = true;
        }

        // Calcular fecha de cierre real (ajustar si el día no existe en ese mes)
        $dias_en_mes_cierre = cal_days_in_month(CAL_GREGORIAN, $mes_resumen, $anio_resumen);
        $dia_cierre_real = min($fecha_cierre_dia, $dias_en_mes_cierre);
        $fecha_cierre = new DateTime("$anio_resumen-$mes_resumen-$dia_cierre_real");

        // Calcular fecha de vencimiento real (mes siguiente)
        $mes_venc = $mes_resumen + 1;
        $anio_venc = $anio_resumen;
        if ($mes_venc > 12) {
            $mes_venc = 1;
            $anio_venc++;
        }

        // Si vencimiento_dia no existe en ese mes (ej: 31 de febrero), usar último día del mes
        $dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes_venc, $anio_venc);
        $dia_venc = min($fecha_vencimiento_dia, $dias_en_mes);

        $fecha_vencimiento = new DateTime("$anio_venc-$mes_venc-$dia_venc");

        $periodo = substr($fecha_cierre->format('Y-m-d'), 0, 7); // YYYY-MM

        return [
            'periodo' => $periodo,
            'fecha_cierre' => $fecha_cierre->format('Y-m-d'),
            'fecha_vencimiento' => $fecha_vencimiento->format('Y-m-d'),
            'mes_resumen' => $mes_resumen,
            'anio_resumen' => $anio_resumen,
            'es_proximo_resumen' => $es_proximo
        ];
    }

    // ============================================================
    // FUNCIÓN CORE #2: Generar Cuotas de un Movimiento
    // ============================================================

    /**
     * generarCuotasMovimiento()
     *
     * Crea N registros de cuotas persistidas en BD
     * Marca la cuota del próximo resumen como "pagada"
     * El resto como "pendiente"
     *
     * @param int $movimiento_id
     * @param int $cuotas_totales
     * @param int $cuota_pagada_proximo_resumen (¿cuál se paga primero?)
     * @param int $tarjeta_id
     * @param string $fecha_compra (YYYY-MM-DD)
     * @param float $monto_cuota
     *
     * @return bool
     */
    public static function generarCuotasMovimiento(
        $movimiento_id,
        $cuotas_totales,
        $cuota_pagada_proximo_resumen,
        $tarjeta_id,
        $fecha_compra,
        $monto_cuota
    ) {
        $db = self::getDb();

        // Determinar período del movimiento
        $periodo_info = self::determinarPeriodoResumen($tarjeta_id, $fecha_compra);
        $mes_inicial = (int)substr($periodo_info['periodo'], 5, 2);
        $anio_inicial = (int)substr($periodo_info['periodo'], 0, 4);

        try {
            // Generar cuotas (la transacción es manejada por el llamador)
            for ($numero_cuota = 1; $numero_cuota <= $cuotas_totales; $numero_cuota++) {

                // Calcular en qué mes cae esta cuota
                $meses_offset = $numero_cuota - 1;
                $mes_cuota = $mes_inicial + $meses_offset;
                $anio_cuota = $anio_inicial;

                // Ajustar por cambio de año
                while ($mes_cuota > 12) {
                    $mes_cuota -= 12;
                    $anio_cuota++;
                }

                // Determinar fechas de cierre y vencimiento para ESTA cuota
                $tarjeta_stmt = $db->prepare(
                    "SELECT fecha_cierre_dia, fecha_vencimiento_dia FROM tarjetas_credito WHERE id = ?"
                );
                $tarjeta_stmt->execute([$tarjeta_id]);
                $tarjeta_data = $tarjeta_stmt->fetch(PDO::FETCH_ASSOC);

                if (!$tarjeta_data) {
                    throw new Exception("Tarjeta ID $tarjeta_id no encontrada");
                }

                $fecha_cierre_dia = (int)$tarjeta_data['fecha_cierre_dia'];
                $fecha_vencimiento_dia = (int)$tarjeta_data['fecha_vencimiento_dia'];

                // Fecha de cierre de esta cuota
                $dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes_cuota, $anio_cuota);
                $dia_cierre = min($fecha_cierre_dia, $dias_en_mes);
                $fecha_cierre = "$anio_cuota-" . str_pad($mes_cuota, 2, '0', STR_PAD_LEFT) . "-$dia_cierre";

                // Fecha de vencimiento (mes siguiente)
                $mes_venc = $mes_cuota + 1;
                $anio_venc = $anio_cuota;
                if ($mes_venc > 12) {
                    $mes_venc = 1;
                    $anio_venc++;
                }
                $dias_en_mes_venc = cal_days_in_month(CAL_GREGORIAN, $mes_venc, $anio_venc);
                $dia_venc = min($fecha_vencimiento_dia, $dias_en_mes_venc);
                $fecha_vencimiento = "$anio_venc-" . str_pad($mes_venc, 2, '0', STR_PAD_LEFT) . "-$dia_venc";

                // Determinar estado
                $estado = ($numero_cuota == $cuota_pagada_proximo_resumen) ? 'pagada' : 'pendiente';
                $fecha_pago = ($numero_cuota == $cuota_pagada_proximo_resumen) ? date('Y-m-d') : null;

                $periodo = substr($fecha_cierre, 0, 7); // YYYY-MM

                // Insertar cuota
                $sql = "INSERT INTO cuotas_movimiento
                        (movimiento_id, numero_cuota, periodo, fecha_cierre, fecha_vencimiento, monto, estado, fecha_pago)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt_cuota = $db->prepare($sql);
                $stmt_cuota->execute([
                    $movimiento_id,
                    $numero_cuota,
                    $periodo,
                    $fecha_cierre,
                    $fecha_vencimiento,
                    $monto_cuota,
                    $estado,
                    $fecha_pago
                ]);
            }

            return true;

        } catch (Exception $e) {
            throw $e;
        }
    }

    // ============================================================
    // FUNCIÓN CORE #3: Próximo Vencimiento
    // ============================================================

    /**
     * obtenerProximoVencimiento()
     *
     * Retorna la próxima fecha de vencimiento pendiente de pago
     *
     * @param int $tarjeta_id
     *
     * @return array {
     *     'fecha_vencimiento' => '2026-06-03',
     *     'total' => 125000.00,
     *     'cuotas_pendientes' => 3,
     *     'dias_para_vencer' => 5
     * } | null
     */
    public static function obtenerProximoVencimiento($tarjeta_id) {
        $db = self::getDb();

        $sql = "SELECT
                    MIN(cm.fecha_vencimiento) as fecha_vencimiento,
                    SUM(cm.monto) as total,
                    COUNT(*) as cuotas_pendientes
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.estado = 'pendiente'
                  AND cm.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 120 DAY)
                GROUP BY DATE(cm.fecha_vencimiento)
                ORDER BY cm.fecha_vencimiento ASC
                LIMIT 1";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $result = $stmt->fetch();

        if (!$result) {
            return null;
        }

        $fecha_venc = new DateTime($result['fecha_vencimiento']);
        $hoy = new DateTime();
        $dias = $hoy->diff($fecha_venc)->days;

        return [
            'fecha_vencimiento' => $result['fecha_vencimiento'],
            'total' => (float)$result['total'],
            'cuotas_pendientes' => (int)$result['cuotas_pendientes'],
            'dias_para_vencer' => $dias
        ];
    }

    // ============================================================
    // FUNCIÓN CORE #4: Proyección Mensual de Deuda
    // ============================================================

    /**
     * obtenerProyeccionMensual()
     *
     * Retorna cuotas agrupadas por mes/año (período)
     * Útil para saber "¿cuánto pago cada mes?"
     *
     * @param int $tarjeta_id
     * @param int $meses_futuros (ej: 12 para próximos 12 meses)
     *
     * @return array [
     *     [
     *         'periodo' => '2026-05',
     *         'fecha_cierre' => '2026-05-25',
     *         'fecha_vencimiento' => '2026-06-03',
     *         'total_pendiente' => 250000.00,
     *         'cuotas_pendientes' => 5,
     *         'estado_resumen' => 'abierto|cerrado|vencido'
     *     ],
     *     ...
     * ]
     */
    public static function obtenerProyeccionMensual($tarjeta_id, $meses_futuros = 12) {
        $db = self::getDb();

        $hoy = new DateTime();
        $hoy_str = $hoy->format('Y-m-d');

        $sql = "SELECT
                    cm.periodo,
                    MIN(cm.fecha_cierre) as fecha_cierre,
                    MIN(cm.fecha_vencimiento) as fecha_vencimiento,
                    SUM(CASE WHEN cm.estado = 'pendiente' THEN cm.monto ELSE 0 END) as total_pendiente,
                    COUNT(CASE WHEN cm.estado = 'pendiente' THEN 1 END) as cuotas_pendientes,
                    COUNT(*) as cuotas_totales,
                    COUNT(CASE WHEN cm.estado = 'pagada' THEN 1 END) as cuotas_pagadas
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.periodo >= DATE_FORMAT(?, '%Y-%m')
                  AND cm.periodo < DATE_FORMAT(DATE_ADD(?, INTERVAL ? MONTH), '%Y-%m')
                GROUP BY cm.periodo
                ORDER BY cm.periodo ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id, $hoy_str, $hoy_str, $meses_futuros]);
        $resultados = $stmt->fetchAll();

        $proyeccion = [];
        foreach ($resultados as $row) {
            $fecha_venc = new DateTime($row['fecha_vencimiento']);
            $hoy_comp = new DateTime();

            if ($fecha_venc < $hoy_comp) {
                $estado = 'vencido';
            } else if ($fecha_venc->format('Y-m-d') == $hoy_comp->format('Y-m-d')) {
                $estado = 'hoy';
            } else if ($fecha_venc->diff($hoy_comp)->days <= 3) {
                $estado = 'proximamente';
            } else if ((int)$row['cuotas_pagadas'] == 0) {
                $estado = 'abierto';
            } else if ((int)$row['cuotas_pagadas'] == $row['cuotas_totales']) {
                $estado = 'cerrado';
            } else {
                $estado = 'parcial';
            }

            $proyeccion[] = [
                'periodo' => $row['periodo'],
                'fecha_cierre' => $row['fecha_cierre'],
                'fecha_vencimiento' => $row['fecha_vencimiento'],
                'total_pendiente' => (float)$row['total_pendiente'],
                'cuotas_pendientes' => (int)$row['cuotas_pendientes'],
                'cuotas_totales' => (int)$row['cuotas_totales'],
                'cuotas_pagadas' => (int)$row['cuotas_pagadas'],
                'estado_resumen' => $estado
            ];
        }

        return $proyeccion;
    }

    // ============================================================
    // FUNCIÓN CORE #5: Deuda Total
    // ============================================================

    /**
     * obtenerDeudaTotal()
     *
     * Suma todas las cuotas pendientes (presentes y futuras)
     *
     * @param int $tarjeta_id
     *
     * @return float
     */
    public static function obtenerDeudaTotal($tarjeta_id) {
        $db = self::getDb();

        $sql = "SELECT COALESCE(SUM(cm.monto), 0) as total
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.estado = 'pendiente'";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $result = $stmt->fetch();

        return (float)($result['total'] ?? 0);
    }

    // ============================================================
    // FUNCIÓN CORE #6: Disponible Real
    // ============================================================

    /**
     * obtenerDisponibleReal()
     *
     * Límite - Deuda Total (en cuenta)
     *
     * @param int $tarjeta_id
     *
     * @return array {
     *     'limite' => 500000.00,
     *     'deuda_comprometida' => 125000.00,
     *     'disponible' => 375000.00
     * }
     */
    public static function obtenerDisponibleReal($tarjeta_id) {
        $db = self::getDb();

        // Obtener límite
        $stmt = $db->prepare("SELECT limite_credito FROM tarjetas_credito WHERE id = ?");
        $stmt->execute([$tarjeta_id]);
        $tarjeta = $stmt->fetch();

        if (!$tarjeta) {
            throw new Exception("Tarjeta no encontrada: $tarjeta_id");
        }

        $limite = (float)$tarjeta['limite_credito'];
        $deuda = self::obtenerDeudaTotal($tarjeta_id);
        $disponible = $limite - $deuda;

        return [
            'limite' => $limite,
            'deuda_comprometida' => $deuda,
            'disponible' => $disponible
        ];
    }

    // ============================================================
    // FUNCIONES AUXILIARES
    // ============================================================

    /**
     * Obtener datos de tarjeta
     */
    public static function obtenerTarjeta($tarjeta_id) {
        $db = self::getDb();

        $stmt = $db->prepare(
            "SELECT * FROM tarjetas_credito WHERE id = ? AND activa = 1"
        );
        $stmt->execute([$tarjeta_id]);
        return $stmt->fetch();
    }

    /**
     * Obtener movimiento con datos de tarjeta
     */
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

    /**
     * Obtener cuotas de un movimiento
     */
    public static function obtenerCuotasMovimiento($movimiento_id) {
        $db = self::getDb();

        $sql = "SELECT * FROM cuotas_movimiento
                WHERE movimiento_id = ?
                ORDER BY numero_cuota ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$movimiento_id]);
        return $stmt->fetchAll();
    }

    /**
     * Marcar cuota como pagada
     */
    public static function marcarCuotaPagada($cuota_id) {
        $db = self::getDb();

        $sql = "UPDATE cuotas_movimiento
                SET estado = 'pagada', fecha_pago = CURDATE()
                WHERE id = ?";

        $stmt = $db->prepare($sql);
        return $stmt->execute([$cuota_id]);
    }

    /**
     * Obtener resumen de tarjeta por período
     */
    public static function obtenerResumenPeriodo($tarjeta_id, $periodo) {
        $db = self::getDb();

        $sql = "SELECT
                    SUM(cm.monto) as total,
                    COUNT(*) as cuotas_totales,
                    COUNT(CASE WHEN cm.estado = 'pagada' THEN 1 END) as cuotas_pagadas,
                    MIN(cm.fecha_vencimiento) as fecha_vencimiento
                FROM cuotas_movimiento cm
                INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                WHERE mt.tarjeta_id = ?
                  AND cm.periodo = ?";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id, $periodo]);
        return $stmt->fetch();
    }

}
