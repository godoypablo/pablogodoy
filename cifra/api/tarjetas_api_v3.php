<?php
/**
 * API REST - Módulo Tarjetas v3 (SIMPLIFICADO)
 *
 * Endpoints:
 * GET    /api/tarjetas_api.php                    → Listar tarjetas con deuda/disponible
 * POST   /api/tarjetas_api.php                    → Crear tarjeta
 * PUT    /api/tarjetas_api.php?id=...             → Editar tarjeta
 * DELETE /api/tarjetas_api.php?id=...             → Desactivar tarjeta
 *
 * POST   /api/tarjetas_api.php?action=movimiento  → Crear movimiento (genera cuotas auto)
 * GET    /api/tarjetas_api.php?action=movimientos&tarjeta_id=...  → Listar movimientos
 *
 * GET    /api/tarjetas_api.php?action=vencimientos_consolidados&tarjeta_id=...
 * GET    /api/tarjetas_api.php?action=proximo_vencimiento&tarjeta_id=...
 * GET    /api/tarjetas_api.php?action=disponible&tarjeta_id=...
 *
 * PATCH  /api/tarjetas_api.php?action=marcar_pagada&cuota_id=...
 * DELETE /api/tarjetas_api.php?action=movimiento&id=...
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/database.php';
require_once '../config/auth_check.php';
require_once '../lib/tarjetas_financiero_v3.php';

require_auth_or_401();

function sendResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    TarjetasFinanciero::setDatabase($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;

    // ============================================================
    // CRUD TARJETAS
    // ============================================================

    if (!$action) {
        switch ($method) {

            // GET: Listar tarjetas con deuda y límite disponible
            case 'GET':
                $sql = "SELECT * FROM tarjetas_credito WHERE activa = 1 ORDER BY banco ASC, nombre_tarjeta ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $tarjetas = $stmt->fetchAll();

                foreach ($tarjetas as &$t) {
                    $t['limite_credito'] = (float)$t['limite_credito'];
                    if (isset($t['activa'])) $t['activa'] = (bool)$t['activa'];

                    // Calcular deuda y límite disponible
                    try {
                        $disponible_info = TarjetasFinanciero::obtenerLimiteDisponible($t['id']);
                        $t['deuda_comprometida'] = $disponible_info['deuda_comprometida'];
                        $t['disponible'] = $disponible_info['disponible'];
                    } catch (Exception $e) {
                        $t['deuda_comprometida'] = 0;
                        $t['disponible'] = $t['limite_credito'];
                    }
                }
                unset($t);

                sendResponse(true, $tarjetas);

            // POST: Crear tarjeta
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);

                $banco = trim($data['banco'] ?? '');
                $nombre = trim($data['nombre_tarjeta'] ?? '');
                $marca = trim($data['marca'] ?? '');
                $ultimos = trim($data['ultimos_4'] ?? '');
                $limite = (float)($data['limite_credito'] ?? 0);
                $cierre = (int)($data['fecha_cierre_dia'] ?? 25);
                $vencimiento = (int)($data['fecha_vencimiento_dia'] ?? 3);
                $titular = trim($data['titular'] ?? '');

                if (!$banco || !$nombre || !$marca || !$cierre || !$vencimiento) {
                    sendResponse(false, null, 'Campos obligatorios faltantes', 400);
                }

                $sql = "INSERT INTO tarjetas_credito
                        (banco, nombre_tarjeta, marca, ultimos_4, limite_credito,
                         fecha_cierre_dia, fecha_vencimiento_dia, titular, activo)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";

                $stmt = $db->prepare($sql);
                $stmt->execute([$banco, $nombre, $marca, $ultimos, $limite, $cierre, $vencimiento, $titular]);

                $id = $db->lastInsertId();

                sendResponse(true, ['id' => $id], 'Tarjeta creada');

            // PUT: Editar tarjeta
            case 'PUT':
                if (!isset($_GET['id'])) {
                    sendResponse(false, null, 'ID requerido', 400);
                }

                $data = json_decode(file_get_contents('php://input'), true);
                $id = (int)$_GET['id'];

                $updates = [];
                $params = [];

                // Campos editables
                $campos = [
                    'banco' => 'string',
                    'nombre_tarjeta' => 'string',
                    'marca' => 'string',
                    'ultimos_4' => 'string',
                    'titular' => 'string',
                    'limite_credito' => 'float',
                    'fecha_cierre_dia' => 'int',
                    'fecha_vencimiento_dia' => 'int',
                    'activa' => 'bool'
                ];

                foreach ($campos as $campo => $tipo) {
                    if (isset($data[$campo])) {
                        $updates[] = "$campo = ?";
                        if ($tipo === 'float') {
                            $params[] = (float)$data[$campo];
                        } elseif ($tipo === 'int') {
                            $params[] = (int)$data[$campo];
                        } elseif ($tipo === 'bool') {
                            $params[] = (int)(bool)$data[$campo];
                        } else {
                            $params[] = trim($data[$campo]);
                        }
                    }
                }

                if (empty($updates)) {
                    sendResponse(false, null, 'Sin cambios', 400);
                }

                $params[] = $id;
                $sql = "UPDATE tarjetas_credito SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute($params);

                sendResponse(true, null, 'Tarjeta actualizada');

            // DELETE: Desactivar tarjeta
            case 'DELETE':
                if (!isset($_GET['id'])) {
                    sendResponse(false, null, 'ID requerido', 400);
                }

                $id = (int)$_GET['id'];
                $sql = "UPDATE tarjetas_credito SET activa = 0 WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id]);

                sendResponse(true, null, 'Tarjeta desactivada');

            default:
                sendResponse(false, null, 'Método no permitido', 405);
        }
    }

    // ============================================================
    // CREAR MOVIMIENTO (auto-genera cuotas)
    // ============================================================

    if ($action === 'movimiento' && $method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $tarjeta_id = (int)($data['tarjeta_id'] ?? 0);
        $fecha_compra = trim($data['fecha_compra'] ?? '');
        $descripcion = trim($data['descripcion'] ?? '');
        $comercio = trim($data['comercio'] ?? '');
        $categoria = trim($data['categoria'] ?? '');
        $monto_total = (float)($data['monto_total'] ?? 0);
        $cuotas_totales = (int)($data['cuotas_totales'] ?? 1);

        if (!$tarjeta_id || !$fecha_compra || $monto_total <= 0 || $cuotas_totales < 1) {
            sendResponse(false, null, 'Datos inválidos', 400);
        }

        try {
            $db->beginTransaction();

            // 0. Asegurar que haya cierres configurados
            $stmtTarjeta = $db->prepare(
                "SELECT fecha_cierre_dia, fecha_vencimiento_dia FROM tarjetas_credito WHERE id = ?"
            );
            $stmtTarjeta->execute([$tarjeta_id]);
            $tarjeta = $stmtTarjeta->fetch();

            if (!$tarjeta) {
                throw new Exception("Tarjeta no encontrada");
            }

            // Generar cierres para los próximos 12 meses desde la fecha de compra
            $cierre_dia = (int)$tarjeta['fecha_cierre_dia'];
            $vencimiento_dia = (int)$tarjeta['fecha_vencimiento_dia'];

            $fecha_inicio = new DateTime($fecha_compra);
            $anio = (int)$fecha_inicio->format('Y');
            $mes = (int)$fecha_inicio->format('m');

            for ($i = 0; $i < 12; $i++) {
                $m = $mes + $i;
                $a = $anio;

                while ($m > 12) {
                    $m -= 12;
                    $a++;
                }

                $dias_mes = cal_days_in_month(CAL_GREGORIAN, $m, $a);
                $dia_c = min($cierre_dia, $dias_mes);
                $fecha_cierre = "$a-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia_c, 2, '0', STR_PAD_LEFT);

                // Vencimiento es del MISMO mes que el cierre, no el siguiente
                $dias_mes_venc = cal_days_in_month(CAL_GREGORIAN, $m, $a);
                $dia_v = min($vencimiento_dia, $dias_mes_venc);
                $fecha_venc = "$a-" . str_pad($m, 2, '0', STR_PAD_LEFT) . "-" . str_pad($dia_v, 2, '0', STR_PAD_LEFT);

                // Usar ON DUPLICATE KEY para actualizar si existe
                $stmtInsert = $db->prepare(
                    "INSERT INTO cierres_tarjeta (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                     fecha_cierre = VALUES(fecha_cierre),
                     fecha_vencimiento = VALUES(fecha_vencimiento)"
                );
                $stmtInsert->execute([$tarjeta_id, $a, $m, $fecha_cierre, $fecha_venc]);
            }

            // 1. Insertar movimiento
            $sql = "INSERT INTO movimientos_tarjeta
                    (tarjeta_id, fecha_compra, descripcion, comercio, categoria,
                     monto_total, cuotas_totales, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'activo')";

            $stmt = $db->prepare($sql);
            $stmt->execute([$tarjeta_id, $fecha_compra, $descripcion, $comercio, $categoria, $monto_total, $cuotas_totales]);

            $movimiento_id = $db->lastInsertId();

            // 2. Generar cuotas automáticamente (usa cierres_tarjeta)
            TarjetasFinanciero::generarCuotas($movimiento_id, $tarjeta_id, $fecha_compra, $cuotas_totales, $monto_total);

            $db->commit();

            sendResponse(true, [
                'movimiento_id' => $movimiento_id,
                'cuotas_generadas' => $cuotas_totales
            ], 'Movimiento creado');

        } catch (Exception $e) {
            $db->rollBack();
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================
    // LISTAR MOVIMIENTOS
    // ============================================================

    if ($action === 'movimientos' && $method === 'GET') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        $sql = "SELECT * FROM movimientos_tarjeta
                WHERE tarjeta_id = ? AND fecha_cancelacion IS NULL
                ORDER BY fecha_compra DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $movimientos = $stmt->fetchAll();

        foreach ($movimientos as &$m) {
            $m['monto_total'] = (float)$m['monto_total'];
            $m['cuotas_totales'] = (int)$m['cuotas_totales'];
        }

        sendResponse(true, $movimientos);
    }

    // ============================================================
    // VENCIMIENTOS CONSOLIDADOS POR FECHA
    // ============================================================

    if ($action === 'vencimientos_consolidados' && $method === 'GET') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        try {
            $vencimientos = TarjetasFinanciero::obtenerVencimientosConsolidados($tarjeta_id);
            sendResponse(true, $vencimientos);
        } catch (Exception $e) {
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================
    // PRÓXIMO VENCIMIENTO
    // ============================================================

    if ($action === 'proximo_vencimiento' && $method === 'GET') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        try {
            $proximo = TarjetasFinanciero::obtenerProximoVencimiento($tarjeta_id);
            sendResponse(true, $proximo);
        } catch (Exception $e) {
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================
    // DISPONIBLE (límite - deuda comprometida)
    // ============================================================

    if ($action === 'disponible' && $method === 'GET') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        try {
            $disponible = TarjetasFinanciero::obtenerLimiteDisponible($tarjeta_id);
            sendResponse(true, $disponible);
        } catch (Exception $e) {
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================
    // MARCAR CUOTA COMO PAGADA
    // ============================================================

    if ($action === 'marcar_pagada' && $method === 'PATCH') {
        if (!isset($_GET['cuota_id'])) {
            sendResponse(false, null, 'cuota_id requerido', 400);
        }

        $cuota_id = (int)$_GET['cuota_id'];

        try {
            $result = TarjetasFinanciero::marcarCuotaPagada($cuota_id);
            if ($result) {
                sendResponse(true, null, 'Cuota marcada como pagada');
            } else {
                sendResponse(false, null, 'No se pudo marcar la cuota', 500);
            }
        } catch (Exception $e) {
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================
    // EDITAR MOVIMIENTO
    // ============================================================

    if ($action === 'movimiento' && $method === 'PATCH') {
        if (!isset($_GET['id'])) {
            sendResponse(false, null, 'ID requerido', 400);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $movimiento_id = (int)$_GET['id'];

        $updates = [];
        $params = [];

        if (isset($data['descripcion'])) {
            $updates[] = 'descripcion = ?';
            $params[] = trim($data['descripcion']);
        }
        if (isset($data['comercio'])) {
            $updates[] = 'comercio = ?';
            $params[] = trim($data['comercio']);
        }
        if (isset($data['categoria'])) {
            $updates[] = 'categoria = ?';
            $params[] = trim($data['categoria']);
        }

        if (empty($updates)) {
            sendResponse(false, null, 'Sin cambios', 400);
        }

        $params[] = $movimiento_id;
        $sql = "UPDATE movimientos_tarjeta SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute($params);

        if ($result) {
            sendResponse(true, null, 'Movimiento actualizado');
        } else {
            sendResponse(false, null, 'No se pudo actualizar', 500);
        }
    }

    // ============================================================
    // ANULAR MOVIMIENTO
    // ============================================================

    if ($action === 'movimiento' && $method === 'DELETE') {
        if (!isset($_GET['id'])) {
            sendResponse(false, null, 'ID requerido', 400);
        }

        $movimiento_id = (int)$_GET['id'];

        try {
            $result = TarjetasFinanciero::anularMovimiento($movimiento_id);
            if ($result) {
                sendResponse(true, null, 'Movimiento anulado');
            } else {
                sendResponse(false, null, 'No se pudo anular el movimiento', 500);
            }
        } catch (Exception $e) {
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // ============================================================
    // OBTENER CIERRES Y VENCIMIENTOS POR MES
    // ============================================================

    if ($action === 'cierres' && $method === 'GET') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        $sql = "SELECT id, tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento
                FROM cierres_tarjeta
                WHERE tarjeta_id = ?
                ORDER BY anio ASC, mes ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id]);
        $cierres = $stmt->fetchAll();

        sendResponse(true, $cierres);
    }

    // ============================================================
    // ACTUALIZAR CIERRE/VENCIMIENTO DE UN MES
    // ============================================================

    if ($action === 'cierres' && $method === 'PUT') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $tarjeta_id = (int)$_GET['tarjeta_id'];

        if (!isset($data['anio']) || !isset($data['mes']) || !isset($data['fecha_cierre']) || !isset($data['fecha_vencimiento'])) {
            sendResponse(false, null, 'Campos requeridos: anio, mes, fecha_cierre, fecha_vencimiento', 400);
        }

        $anio = (int)$data['anio'];
        $mes = (int)$data['mes'];
        $fecha_cierre = $data['fecha_cierre'];
        $fecha_vencimiento = $data['fecha_vencimiento'];

        $sql = "INSERT INTO cierres_tarjeta (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                fecha_cierre = VALUES(fecha_cierre),
                fecha_vencimiento = VALUES(fecha_vencimiento),
                updated_at = CURRENT_TIMESTAMP";

        $stmt = $db->prepare($sql);
        $stmt->execute([$tarjeta_id, $anio, $mes, $fecha_cierre, $fecha_vencimiento]);

        sendResponse(true, null, 'Cierre actualizado');
    }

    // ============================================================
    // GENERAR CIERRES AUTOMÁTICOS
    // ============================================================

    if ($action === 'generar_cierres' && $method === 'POST') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        try {
            // Obtener datos base de la tarjeta
            $stmt = $db->prepare(
                "SELECT fecha_cierre_dia, fecha_vencimiento_dia FROM tarjetas_credito WHERE id = ?"
            );
            $stmt->execute([$tarjeta_id]);
            $tarjeta = $stmt->fetch();

            if (!$tarjeta) {
                sendResponse(false, null, 'Tarjeta no encontrada', 404);
            }

            $cierre_dia = (int)$tarjeta['fecha_cierre_dia'];
            $vencimiento_dia = (int)$tarjeta['fecha_vencimiento_dia'];

            // Generar cierres para los próximos 12 meses
            $fecha_hoy = new DateTime();
            $anio_actual = (int)$fecha_hoy->format('Y');
            $mes_actual = (int)$fecha_hoy->format('m');

            $generados = 0;
            for ($i = 0; $i < 12; $i++) {
                $mes = $mes_actual + $i;
                $anio = $anio_actual;

                while ($mes > 12) {
                    $mes -= 12;
                    $anio++;
                }

                // Calcular fecha de cierre
                $dias_en_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
                $dia_cierre = min($cierre_dia, $dias_en_mes);
                $fecha_cierre = new DateTime("$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-$dia_cierre");

                // Calcular fecha de vencimiento (MISMO mes que el cierre)
                $dias_en_mes_venc = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);
                $dia_venc = min($vencimiento_dia, $dias_en_mes_venc);
                $fecha_vencimiento = new DateTime("$anio-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "-$dia_venc");

                // Insertar o actualizar
                $stmt = $db->prepare(
                    "INSERT INTO cierres_tarjeta (tarjeta_id, anio, mes, fecha_cierre, fecha_vencimiento)
                     VALUES (?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE
                     fecha_cierre = VALUES(fecha_cierre),
                     fecha_vencimiento = VALUES(fecha_vencimiento)"
                );
                $stmt->execute([
                    $tarjeta_id,
                    $anio,
                    $mes,
                    $fecha_cierre->format('Y-m-d'),
                    $fecha_vencimiento->format('Y-m-d')
                ]);

                $generados++;
            }

            sendResponse(true, ['generados' => $generados], "Se generaron $generados cierres automáticamente");

        } catch (Exception $e) {
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
        }
    }

    // Si llega aquí, acción no reconocida
    sendResponse(false, null, 'Acción no reconocida', 404);

} catch (Exception $e) {
    sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
