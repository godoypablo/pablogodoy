<?php
/**
 * API REST - Módulo Tarjetas de Crédito
 *
 * Endpoints:
 * GET    /api/tarjetas_api.php                    → Listar tarjetas + deuda
 * POST   /api/tarjetas_api.php                    → Alta tarjeta
 * PUT    /api/tarjetas_api.php?id=...             → Editar tarjeta
 * DELETE /api/tarjetas_api.php?id=...             → Desactivar tarjeta
 *
 * GET    /api/tarjetas_api.php?action=movimientos&tarjeta_id=...  → Listar movimientos
 * POST   /api/tarjetas_api.php?action=movimiento                  → Crear movimiento
 *
 * GET    /api/tarjetas_api.php?action=proyeccion&tarjeta_id=... → Proyección mensual
 * GET    /api/tarjetas_api.php?action=proximo_vencimiento&tarjeta_id=...
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

// Activar error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/database.php';
require_once '../config/auth_check.php';
require_once '../lib/tarjetas_financiero.php';

require_auth_or_401();

// Función para enviar respuesta JSON
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

    if (!class_exists('TarjetasFinanciero')) {
        throw new Exception('Clase TarjetasFinanciero no encontrada. Verificar que tarjetas_financiero.php esté cargado correctamente.');
    }

    TarjetasFinanciero::setDatabase($db);

    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? null;

    // ============================================================
    // CRUD TARJETAS
    // ============================================================

    if (!$action) {
        switch ($method) {

            // GET: Listar tarjetas con deuda comprometida
            case 'GET':
                $sql = "SELECT
                            tc.*,
                            (SELECT COALESCE(SUM(cm.monto), 0)
                             FROM cuotas_movimiento cm
                             INNER JOIN movimientos_tarjeta mt ON cm.movimiento_id = mt.id
                             WHERE mt.tarjeta_id = tc.id AND cm.estado = 'pendiente'
                            ) as deuda_comprometida
                        FROM tarjetas_credito tc
                        WHERE tc.activa = 1
                        ORDER BY tc.banco ASC, tc.nombre_tarjeta ASC";

                $stmt = $db->prepare($sql);
                $stmt->execute();
                $tarjetas = $stmt->fetchAll();

                foreach ($tarjetas as &$t) {
                    $t['deuda_comprometida'] = (float)$t['deuda_comprometida'];
                    $t['limite_credito'] = (float)$t['limite_credito'];
                    $t['disponible'] = (float)$t['limite_credito'] - (float)$t['deuda_comprometida'];
                    $t['activa'] = (bool)$t['activa'];
                }
                unset($t);

                sendResponse(true, $tarjetas);

            // POST: Crear tarjeta
            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($input['banco']) || !isset($input['nombre_tarjeta']) || !isset($input['marca'])) {
                    sendResponse(false, null, 'Campos obligatorios faltantes', 400);
                }

                if (!isset($input['fecha_cierre_dia']) || !isset($input['fecha_vencimiento_dia'])) {
                    sendResponse(false, null, 'Fechas de cierre y vencimiento obligatorias', 400);
                }

                if ($input['fecha_cierre_dia'] < 1 || $input['fecha_cierre_dia'] > 31) {
                    sendResponse(false, null, 'Día de cierre debe ser 1-31', 400);
                }

                if ($input['fecha_vencimiento_dia'] < 1 || $input['fecha_vencimiento_dia'] > 31) {
                    sendResponse(false, null, 'Día de vencimiento debe ser 1-31', 400);
                }

                $sql = "INSERT INTO tarjetas_credito
                        (banco, nombre_tarjeta, marca, ultimos_4, fecha_cierre_dia, fecha_vencimiento_dia, limite_credito, titular, activa)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    $input['banco'],
                    $input['nombre_tarjeta'],
                    $input['marca'],
                    $input['ultimos_4'] ?? null,
                    $input['fecha_cierre_dia'],
                    $input['fecha_vencimiento_dia'],
                    $input['limite_credito'] ?? 0,
                    $input['titular'] ?? null
                ]);

                $id = $db->lastInsertId();
                sendResponse(true, ['id' => $id], 'Tarjeta creada', 201);

            // PUT: Editar tarjeta
            case 'PUT':
                if (!isset($_GET['id'])) {
                    sendResponse(false, null, 'ID requerido', 400);
                }

                $id = (int)$_GET['id'];
                $input = json_decode(file_get_contents('php://input'), true);

                $campos = [];
                $valores = [];

                if (isset($input['banco'])) { $campos[] = 'banco = ?'; $valores[] = $input['banco']; }
                if (isset($input['nombre_tarjeta'])) { $campos[] = 'nombre_tarjeta = ?'; $valores[] = $input['nombre_tarjeta']; }
                if (isset($input['limite_credito'])) { $campos[] = 'limite_credito = ?'; $valores[] = $input['limite_credito']; }
                if (isset($input['titular'])) { $campos[] = 'titular = ?'; $valores[] = $input['titular']; }

                if (empty($campos)) {
                    sendResponse(false, null, 'Nada que actualizar', 400);
                }

                $valores[] = $id;
                $sql = "UPDATE tarjetas_credito SET " . implode(', ', $campos) . " WHERE id = ?";

                $stmt = $db->prepare($sql);
                $stmt->execute($valores);

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
    // MOVIMIENTOS (action=movimientos)
    // ============================================================

    if ($action === 'movimientos') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];

        switch ($method) {
            case 'GET':
                $sql = "SELECT
                            mt.*,
                            tc.nombre_tarjeta,
                            (SELECT COUNT(*) FROM cuotas_movimiento cm WHERE cm.movimiento_id = mt.id) as cuotas_count,
                            (SELECT COUNT(*) FROM cuotas_movimiento cm WHERE cm.movimiento_id = mt.id AND cm.estado = 'pagada') as cuotas_pagadas
                        FROM movimientos_tarjeta mt
                        INNER JOIN tarjetas_credito tc ON mt.tarjeta_id = tc.id
                        WHERE mt.tarjeta_id = ? AND mt.estado = 'activo'
                        ORDER BY mt.fecha_compra DESC";

                $stmt = $db->prepare($sql);
                $stmt->execute([$tarjeta_id]);
                $movimientos = $stmt->fetchAll();

                foreach ($movimientos as &$m) {
                    $m['monto_total'] = (float)$m['monto_total'];
                    $m['monto_cuota'] = (float)$m['monto_cuota'];
                    $m['cuotas_totales'] = (int)$m['cuotas_totales'];
                    $m['cuota_pagada_proximo_resumen'] = (int)$m['cuota_pagada_proximo_resumen'];
                }
                unset($m);

                sendResponse(true, $movimientos);

            case 'POST':
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($input['fecha_compra']) || !isset($input['monto_total']) || !isset($input['cuotas_totales'])) {
                    sendResponse(false, null, 'Campos obligatorios faltantes', 400);
                }

                if ($input['monto_total'] <= 0) {
                    sendResponse(false, null, 'Monto debe ser mayor a 0', 400);
                }

                if ($input['cuotas_totales'] < 1 || $input['cuotas_totales'] > 99) {
                    sendResponse(false, null, 'Cuotas debe ser 1-99', 400);
                }

                $cuota_pagada_proximo = (int)($input['cuota_pagada_proximo_resumen'] ?? 1);
                if ($cuota_pagada_proximo < 1 || $cuota_pagada_proximo > $input['cuotas_totales']) {
                    sendResponse(false, null, 'Cuota a pagar debe estar entre 1 y ' . $input['cuotas_totales'], 400);
                }

                $monto_cuota = round($input['monto_total'] / $input['cuotas_totales'], 2);

                try {
                    $db->beginTransaction();

                    // Insertar movimiento
                    $sql = "INSERT INTO movimientos_tarjeta
                            (tarjeta_id, fecha_compra, descripcion, comercio, categoria, monto_total, moneda, cuotas_totales, cuota_pagada_proximo_resumen, monto_cuota, observaciones)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        $tarjeta_id,
                        $input['fecha_compra'],
                        $input['descripcion'] ?? null,
                        $input['comercio'] ?? null,
                        $input['categoria'] ?? null,
                        $input['monto_total'],
                        $input['moneda'] ?? 'ARS',
                        $input['cuotas_totales'],
                        $cuota_pagada_proximo,
                        $monto_cuota,
                        $input['observaciones'] ?? null
                    ]);

                    $movimiento_id = $db->lastInsertId();

                    // Generar cuotas
                    try {
                        TarjetasFinanciero::generarCuotasMovimiento(
                            $movimiento_id,
                            $input['cuotas_totales'],
                            $cuota_pagada_proximo,
                            $tarjeta_id,
                            $input['fecha_compra'],
                            $monto_cuota
                        );
                    } catch (Exception $e) {
                        throw new Exception('Error al generar cuotas: ' . $e->getMessage());
                    }

                    $db->commit();

                    sendResponse(true, ['id' => $movimiento_id], 'Movimiento creado', 201);

                } catch (Exception $e) {
                    $db->rollBack();
                    error_log('Error en POST movimientos: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
                    sendResponse(false, null, 'Error: ' . $e->getMessage(), 400);
                }

            case 'PATCH':
                // Editar movimiento (consumo)
                $input = json_decode(file_get_contents('php://input'), true);

                if (!isset($_GET['movimiento_id'])) {
                    sendResponse(false, null, 'movimiento_id requerido', 400);
                }

                $movimiento_id = (int)$_GET['movimiento_id'];

                // Obtener movimiento actual
                $stmt = $db->prepare("SELECT * FROM movimientos_tarjeta WHERE id = ? AND tarjeta_id = ?");
                $stmt->execute([$movimiento_id, $tarjeta_id]);
                $movimiento = $stmt->fetch();

                if (!$movimiento) {
                    sendResponse(false, null, 'Movimiento no encontrado', 404);
                }

                $fields = [];
                $params = [];

                // Validar y agregar campos a actualizar
                if (isset($input['descripcion'])) {
                    $fields[] = 'descripcion = :desc';
                    $params['desc'] = trim($input['descripcion']);
                }

                if (isset($input['fecha_compra'])) {
                    $fields[] = 'fecha_compra = :fecha';
                    $params['fecha'] = $input['fecha_compra'];
                }

                if (isset($input['importe'])) {
                    $nuevo_importe = (float)$input['importe'];
                    if ($nuevo_importe <= 0) {
                        sendResponse(false, null, 'Importe debe ser mayor a 0', 400);
                    }

                    $diff = $nuevo_importe - (float)$movimiento['monto_total'];
                    $fields[] = 'monto_total = :imp';
                    $params['imp'] = $nuevo_importe;

                    // Recalcular monto_cuota
                    $cuotas = (int)$movimiento['cuotas_totales'];
                    $monto_cuota = round($nuevo_importe / $cuotas, 2);
                    $fields[] = 'monto_cuota = :cuota_monto';
                    $params['cuota_monto'] = $monto_cuota;

                    // Actualizar total_consumido en resumenes_tarjeta (si existe)
                    $params['diff'] = $diff;
                }

                if (isset($input['cuota_pagada_proximo_resumen'])) {
                    $cuota_num = (int)$input['cuota_pagada_proximo_resumen'];
                    if ($cuota_num < 1 || $cuota_num > (int)$movimiento['cuotas_totales']) {
                        sendResponse(false, null, 'Cuota inválida', 400);
                    }
                    $fields[] = 'cuota_pagada_proximo_resumen = :cuota';
                    $params['cuota'] = $cuota_num;
                }

                if (count($fields) === 0) {
                    sendResponse(false, null, 'Nada que actualizar', 400);
                }

                try {
                    $db->beginTransaction();

                    // Actualizar movimiento
                    $params['id'] = $movimiento_id;
                    $sql = "UPDATE movimientos_tarjeta SET " . implode(', ', $fields) . " WHERE id = :id";
                    $db->prepare($sql)->execute($params);

                    // Si cambió importe, actualizar total_consumido en resumenes
                    if (isset($input['importe']) && $diff != 0) {
                        // Obtener el resumen del período
                        $stmt = $db->prepare(
                            "SELECT id FROM resumenes_tarjeta
                             WHERE tarjeta_id = ? AND
                             YEAR(fecha_cierre) = YEAR(?) AND
                             MONTH(fecha_cierre) = MONTH(?)"
                        );
                        $stmt->execute([$tarjeta_id, $movimiento['fecha_compra'], $movimiento['fecha_compra']]);
                        $resumen = $stmt->fetch();

                        if ($resumen) {
                            $db->prepare(
                                "UPDATE resumenes_tarjeta SET total_consumido = total_consumido + ? WHERE id = ?"
                            )->execute([$diff, $resumen['id']]);
                        }
                    }

                    $db->commit();
                    sendResponse(true, null, 'Movimiento actualizado', 200);

                } catch (Exception $e) {
                    $db->rollBack();
                    sendResponse(false, null, 'Error: ' . $e->getMessage(), 400);
                }

            case 'DELETE':
                // Eliminar movimiento (consumo)
                if (!isset($_GET['movimiento_id'])) {
                    sendResponse(false, null, 'movimiento_id requerido', 400);
                }

                $movimiento_id = (int)$_GET['movimiento_id'];

                // Obtener movimiento actual
                $stmt = $db->prepare("SELECT * FROM movimientos_tarjeta WHERE id = ? AND tarjeta_id = ?");
                $stmt->execute([$movimiento_id, $tarjeta_id]);
                $movimiento = $stmt->fetch();

                if (!$movimiento) {
                    sendResponse(false, null, 'Movimiento no encontrado', 404);
                }

                try {
                    $db->beginTransaction();

                    // Eliminar todas las cuotas del movimiento
                    $db->prepare("DELETE FROM cuotas_movimiento WHERE movimiento_id = ?")
                        ->execute([$movimiento_id]);

                    // Eliminar movimiento
                    $db->prepare("DELETE FROM movimientos_tarjeta WHERE id = ?")
                        ->execute([$movimiento_id]);

                    $db->commit();
                    sendResponse(true, null, 'Movimiento eliminado', 200);

                } catch (Exception $e) {
                    $db->rollBack();
                    sendResponse(false, null, 'Error: ' . $e->getMessage(), 400);
                }

            default:
                sendResponse(false, null, 'Método no permitido', 405);
        }
    }

    // ============================================================
    // PAGO DE PERÍODO (action=pago)
    // ============================================================

    if ($action === 'pago') {
        if ($method !== 'POST') {
            sendResponse(false, null, 'Solo POST permitido', 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['resumen_id']) || !isset($input['cuenta_id']) || !isset($input['importe'])) {
            sendResponse(false, null, 'Campos obligatorios: resumen_id, cuenta_id, importe', 400);
        }

        $resumen_id = (int)$input['resumen_id'];
        $cuenta_id = (int)$input['cuenta_id'];
        $importe = (float)$input['importe'];

        if ($importe <= 0) {
            sendResponse(false, null, 'Importe debe ser mayor a 0', 400);
        }

        try {
            // Obtener resumen
            $stmt = $db->prepare("SELECT * FROM resumenes_tarjeta WHERE id = ?");
            $stmt->execute([$resumen_id]);
            $resumen = $stmt->fetch();

            if (!$resumen) {
                sendResponse(false, null, 'Resumen no encontrado', 404);
            }

            if ($resumen['pagado']) {
                sendResponse(false, null, 'Resumen ya está pagado', 400);
            }

            // Obtener cuenta para validar saldo
            $stmt = $db->prepare("SELECT * FROM cuentas WHERE id = ? AND activo = 1");
            $stmt->execute([$cuenta_id]);
            $cuenta = $stmt->fetch();

            if (!$cuenta) {
                sendResponse(false, null, 'Cuenta no encontrada', 404);
            }

            // Validar saldo (no permitir descubierto)
            $nuevo_saldo = (float)$cuenta['saldo_actual'] - $importe;
            if ($nuevo_saldo < 0) {
                sendResponse(false, null, 'Saldo insuficiente', 422);
            }

            $db->beginTransaction();

            // Marcar resumen como pagado
            $stmt = $db->prepare(
                "UPDATE resumenes_tarjeta SET pagado = 1, fecha_pago = NOW() WHERE id = ?"
            );
            $stmt->execute([$resumen_id]);

            // Registrar movimiento de pago en la cuenta
            $stmt = $db->prepare(
                "INSERT INTO movimientos_cuenta (tipo, cuenta_origen_id, importe, fecha, descripcion)
                 VALUES ('pago_gasto', ?, ?, NOW(), ?)"
            );
            $stmt->execute([
                $cuenta_id,
                $importe,
                "Pago tarjeta período " . $resumen['periodo']
            ]);

            // Actualizar saldo de cuenta
            $stmt = $db->prepare("UPDATE cuentas SET saldo_actual = ? WHERE id = ?");
            $stmt->execute([$nuevo_saldo, $cuenta_id]);

            $db->commit();

            sendResponse(true, null, 'Pago registrado exitosamente', 200);

        } catch (Exception $e) {
            $db->rollBack();
            sendResponse(false, null, 'Error: ' . $e->getMessage(), 400);
        }
    }

    // ============================================================
    // PROYECCIÓN MENSUAL (action=proyeccion)
    // ============================================================

    if ($action === 'proyeccion') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        if ($method !== 'GET') {
            sendResponse(false, null, 'Solo GET permitido', 405);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];
        $meses = (int)($_GET['meses'] ?? 12);

        $proyeccion = TarjetasFinanciero::obtenerProyeccionMensual($tarjeta_id, $meses);

        sendResponse(true, $proyeccion);
    }

    // ============================================================
    // PRÓXIMO VENCIMIENTO (action=proximo_vencimiento)
    // ============================================================

    if ($action === 'proximo_vencimiento') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        if ($method !== 'GET') {
            sendResponse(false, null, 'Solo GET permitido', 405);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];
        $resultado = TarjetasFinanciero::obtenerProximoVencimiento($tarjeta_id);

        sendResponse(true, $resultado);
    }

    // ============================================================
    // DEUDA TOTAL (action=deuda_total)
    // ============================================================

    if ($action === 'deuda_total') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        if ($method !== 'GET') {
            sendResponse(false, null, 'Solo GET permitido', 405);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];
        $deuda = TarjetasFinanciero::obtenerDeudaTotal($tarjeta_id);

        sendResponse(true, ['deuda_total' => $deuda]);
    }

    // ============================================================
    // DISPONIBLE (action=disponible)
    // ============================================================

    if ($action === 'disponible') {
        if (!isset($_GET['tarjeta_id'])) {
            sendResponse(false, null, 'tarjeta_id requerido', 400);
        }

        if ($method !== 'GET') {
            sendResponse(false, null, 'Solo GET permitido', 405);
        }

        $tarjeta_id = (int)$_GET['tarjeta_id'];
        $disponible = TarjetasFinanciero::obtenerDisponibleReal($tarjeta_id);

        sendResponse(true, $disponible);
    }

    sendResponse(false, null, 'Acción no reconocida', 400);

} catch (Exception $e) {
    sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
