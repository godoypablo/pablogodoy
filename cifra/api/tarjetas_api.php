<?php
/**
 * API REST para Tarjetas de Crédito
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../config/database.php';
require_once '../config/auth_check.php';
require_auth_or_401();

function sendResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance()->getConnection();

    switch ($method) {

        case 'GET':
            $action = isset($_GET['action']) ? $_GET['action'] : 'lista';

            if ($action === 'lista') {
                // GET ?mes&anio → todas las tarjetas activas con total_consumido y disponible del período
                $mes  = isset($_GET['mes'])  ? (int)$_GET['mes']  : date('n');
                $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

                $sql = "SELECT
                            t.id, t.nombre, t.banco, t.limite, t.cierre_dia,
                            t.vencimiento_dia, t.color, t.activo,
                            COALESCE(r.total_consumido, 0) AS total_consumido,
                            COALESCE(r.pagado, 0) AS pagado,
                            r.id AS resumen_id,
                            t.limite - COALESCE(r.total_consumido, 0) AS disponible
                        FROM tarjetas t
                        LEFT JOIN resumenes_tarjeta r
                               ON r.tarjeta_id = t.id AND r.mes = :mes AND r.anio = :anio
                        WHERE t.activo = 1
                        ORDER BY t.id ASC";

                $stmt = $db->prepare($sql);
                $stmt->execute(['mes' => $mes, 'anio' => $anio]);
                $tarjetas = $stmt->fetchAll();

                foreach ($tarjetas as &$t) {
                    $t['total_consumido'] = (float)$t['total_consumido'];
                    $t['disponible'] = (float)$t['disponible'];
                    $t['limite'] = (float)$t['limite'];
                    $t['pagado'] = (int)$t['pagado'];
                }
                unset($t);

                sendResponse(true, $tarjetas);
            }
            elseif ($action === 'resumenes') {
                // GET ?action=resumenes&tarjeta_id=X → historial de resúmenes
                $tarjeta_id = isset($_GET['tarjeta_id']) ? (int)$_GET['tarjeta_id'] : 0;
                if (!$tarjeta_id) sendResponse(false, null, 'tarjeta_id es requerido', 400);

                $sql = "SELECT r.*, c.nombre AS cuenta_nombre
                        FROM resumenes_tarjeta r
                        LEFT JOIN cuentas c ON c.id = r.cuenta_pago_id
                        WHERE r.tarjeta_id = :tid
                        ORDER BY r.anio DESC, r.mes DESC
                        LIMIT 24";

                $stmt = $db->prepare($sql);
                $stmt->execute(['tid' => $tarjeta_id]);
                $resumenes = $stmt->fetchAll();

                foreach ($resumenes as &$r) {
                    $r['total_consumido'] = (float)$r['total_consumido'];
                    $r['pagado'] = (int)$r['pagado'];
                }
                unset($r);

                sendResponse(true, $resumenes);
            }
            elseif ($action === 'consumos') {
                // GET ?action=consumos&tarjeta_id=X&mes=Y&anio=Z → consumos del período
                $tarjeta_id = isset($_GET['tarjeta_id']) ? (int)$_GET['tarjeta_id'] : 0;
                $mes        = isset($_GET['mes'])  ? (int)$_GET['mes']  : date('n');
                $anio       = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

                if (!$tarjeta_id) sendResponse(false, null, 'tarjeta_id es requerido', 400);

                $sql = "SELECT ct.*, cat.nombre AS categoria_nombre, cat.color AS categoria_color
                        FROM consumos_tarjeta ct
                        LEFT JOIN categorias cat ON cat.id = ct.categoria_id
                        WHERE ct.tarjeta_id = :tid AND ct.mes = :mes AND ct.anio = :anio
                        ORDER BY ct.fecha DESC, ct.id DESC";

                $stmt = $db->prepare($sql);
                $stmt->execute(['tid' => $tarjeta_id, 'mes' => $mes, 'anio' => $anio]);
                $consumos = $stmt->fetchAll();

                foreach ($consumos as &$c) {
                    $c['importe'] = (float)$c['importe'];
                }
                unset($c);

                sendResponse(true, $consumos);
            }
            else {
                sendResponse(false, null, 'action no reconocido', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = isset($input['action']) ? $input['action'] : null;

            if ($action === 'consumo') {
                // POST: INSERT consumo + upsert resumen
                if (empty($input['tarjeta_id']) || empty($input['descripcion']) ||
                    !isset($input['importe']) || $input['importe'] <= 0 || empty($input['fecha'])) {
                    sendResponse(false, null, 'Campos requeridos: tarjeta_id, descripcion, importe > 0, fecha', 400);
                }

                $tarjeta_id = (int)$input['tarjeta_id'];
                $importe = (float)$input['importe'];
                $fecha = trim($input['fecha']);
                $desc = trim($input['descripcion']);
                $categoria_id = isset($input['categoria_id']) && $input['categoria_id'] ? (int)$input['categoria_id'] : null;

                // Validar fecha
                $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                    sendResponse(false, null, 'Fecha inválida (formato Y-m-d)', 400);
                }

                // Obtener tarjeta para el cierre_dia
                $stmtTarj = $db->prepare("SELECT cierre_dia FROM tarjetas WHERE id = :id");
                $stmtTarj->execute(['id' => $tarjeta_id]);
                $tarjeta = $stmtTarj->fetch();
                if (!$tarjeta) {
                    sendResponse(false, null, 'Tarjeta no encontrada', 404);
                }

                // Calcular mes/anio del resumen según cierre_dia
                $dia = (int)$fechaObj->format('j');
                $mes = (int)$fechaObj->format('n');
                $anio = (int)$fechaObj->format('Y');

                // Si el día es posterior al cierre, el consumo va al resumen del mes siguiente
                if ($dia > $tarjeta['cierre_dia']) {
                    $fechaObj->modify('+1 month');
                    $mes = (int)$fechaObj->format('n');
                    $anio = (int)$fechaObj->format('Y');
                }

                // Transacción
                $db->beginTransaction();
                try {
                    // 1. Upsert resumen (INSERT ... ON DUPLICATE KEY UPDATE)
                    $sqlResumen = "INSERT INTO resumenes_tarjeta (tarjeta_id, mes, anio, total_consumido)
                                   VALUES (:tid, :mes, :anio, 0)
                                   ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
                    $stmtRes = $db->prepare($sqlResumen);
                    $stmtRes->execute(['tid' => $tarjeta_id, 'mes' => $mes, 'anio' => $anio]);
                    $resumen_id = $db->lastInsertId();

                    // 2. UPDATE total_consumido en el resumen
                    $sqlUpdate = "UPDATE resumenes_tarjeta SET total_consumido = total_consumido + :imp WHERE id = :rid";
                    $stmtUpd = $db->prepare($sqlUpdate);
                    $stmtUpd->execute(['imp' => $importe, 'rid' => $resumen_id]);

                    // 3. INSERT consumo
                    $sqlCons = "INSERT INTO consumos_tarjeta (tarjeta_id, resumen_id, descripcion, importe, fecha, mes, anio, categoria_id)
                                VALUES (:tid, :rid, :desc, :imp, :fecha, :mes, :anio, :cat)";
                    $stmtCons = $db->prepare($sqlCons);
                    $stmtCons->execute([
                        'tid' => $tarjeta_id,
                        'rid' => $resumen_id,
                        'desc' => $desc,
                        'imp' => $importe,
                        'fecha' => $fecha,
                        'mes' => $mes,
                        'anio' => $anio,
                        'cat' => $categoria_id,
                    ]);
                    $consumo_id = $db->lastInsertId();

                    $db->commit();

                    sendResponse(true, [
                        'consumo_id' => $consumo_id,
                        'resumen_id' => $resumen_id,
                        'mes_resumen' => $mes,
                        'anio_resumen' => $anio,
                    ], 'Consumo agregado');

                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }
            elseif ($action === 'pago') {
                // POST: transacción de pago
                if (empty($input['resumen_id']) || empty($input['cuenta_id']) || !isset($input['importe']) || $input['importe'] <= 0) {
                    sendResponse(false, null, 'Campos requeridos: resumen_id, cuenta_id, importe > 0', 400);
                }

                $resumen_id = (int)$input['resumen_id'];
                $cuenta_id = (int)$input['cuenta_id'];
                $importe = (float)$input['importe'];

                $db->beginTransaction();
                try {
                    // 1. Verificar resumen no pagado (con lock)
                    $sqlVerif = "SELECT pagado, tarjeta_id FROM resumenes_tarjeta WHERE id = :rid FOR UPDATE";
                    $stmtVerif = $db->prepare($sqlVerif);
                    $stmtVerif->execute(['rid' => $resumen_id]);
                    $resumen = $stmtVerif->fetch();

                    if (!$resumen) {
                        $db->rollBack();
                        sendResponse(false, null, 'Resumen no encontrado', 404);
                    }
                    if ($resumen['pagado']) {
                        $db->rollBack();
                        sendResponse(false, null, 'Este resumen ya está pagado', 422);
                    }

                    // 2. Verificar saldo de la cuenta
                    $sqlSaldo = "SELECT saldo_actual FROM cuentas WHERE id = :cid FOR UPDATE";
                    $stmtSaldo = $db->prepare($sqlSaldo);
                    $stmtSaldo->execute(['cid' => $cuenta_id]);
                    $cuenta = $stmtSaldo->fetch();

                    if (!$cuenta) {
                        $db->rollBack();
                        sendResponse(false, null, 'Cuenta no encontrada', 404);
                    }

                    $saldo = (float)$cuenta['saldo_actual'];
                    if ($saldo < $importe) {
                        $db->rollBack();
                        $msg = "Saldo insuficiente. Disponible: " . number_format($saldo, 2, ',', '.') .
                               " | Requerido: " . number_format($importe, 2, ',', '.');
                        sendResponse(false, null, $msg, 422);
                    }

                    // 3. INSERT movimiento en movimientos_cuenta
                    $sqlMov = "INSERT INTO movimientos_cuenta (tipo, cuenta_origen_id, importe, fecha, descripcion)
                               VALUES ('pago_tarjeta', :cid, :imp, CURDATE(), 'Pago de tarjeta de crédito')";
                    $stmtMov = $db->prepare($sqlMov);
                    $stmtMov->execute(['cid' => $cuenta_id, 'imp' => $importe]);
                    $movimiento_id = $db->lastInsertId();

                    // 4. UPDATE saldo en cuentas
                    $sqlActuCuenta = "UPDATE cuentas SET saldo_actual = saldo_actual - :imp, fecha_saldo = CURDATE() WHERE id = :cid";
                    $stmtActuCuenta = $db->prepare($sqlActuCuenta);
                    $stmtActuCuenta->execute(['imp' => $importe, 'cid' => $cuenta_id]);

                    // 5. UPDATE resumen marcado como pagado
                    $sqlActuResum = "UPDATE resumenes_tarjeta SET pagado = 1, fecha_pago = CURDATE(), cuenta_pago_id = :cid, movimiento_id = :mid WHERE id = :rid";
                    $stmtActuResum = $db->prepare($sqlActuResum);
                    $stmtActuResum->execute(['cid' => $cuenta_id, 'mid' => $movimiento_id, 'rid' => $resumen_id]);

                    $db->commit();
                    sendResponse(true, ['movimiento_id' => $movimiento_id], 'Resumen pagado exitosamente');

                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
            }
            elseif ($action === 'nueva_tarjeta') {
                // POST: crear nueva tarjeta
                if (empty($input['nombre'])) {
                    sendResponse(false, null, 'nombre es requerido', 400);
                }

                $nombre = trim($input['nombre']);
                $banco = isset($input['banco']) ? trim($input['banco']) : '';
                $limite = isset($input['limite']) ? (float)$input['limite'] : 0;
                $cierre_dia = isset($input['cierre_dia']) ? (int)$input['cierre_dia'] : 1;
                $vencimiento_dia = isset($input['vencimiento_dia']) ? (int)$input['vencimiento_dia'] : 10;
                $color = isset($input['color']) ? $input['color'] : '#6366f1';

                // Validar rangos de días
                if ($cierre_dia < 1 || $cierre_dia > 31) $cierre_dia = 1;
                if ($vencimiento_dia < 1 || $vencimiento_dia > 31) $vencimiento_dia = 10;

                $sql = "INSERT INTO tarjetas (nombre, banco, limite, cierre_dia, vencimiento_dia, color)
                        VALUES (:nom, :ban, :lim, :cdia, :vdia, :col)";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    'nom' => $nombre,
                    'ban' => $banco,
                    'lim' => $limite,
                    'cdia' => $cierre_dia,
                    'vdia' => $vencimiento_dia,
                    'col' => $color,
                ]);

                sendResponse(true, ['id' => $db->lastInsertId()], 'Tarjeta creada');
            }
            else {
                sendResponse(false, null, 'action no reconocido', 400);
            }
            break;

        case 'PUT':
            // Actualizar tarjeta (nombre, banco, limite, cierre_dia, vencimiento_dia, color)
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['id'])) {
                sendResponse(false, null, 'id es requerido', 400);
            }

            $id = (int)$input['id'];
            $fields = [];
            $params = ['id' => $id];

            if (!empty($input['nombre'])) {
                $fields[] = 'nombre = :nombre';
                $params['nombre'] = trim($input['nombre']);
            }
            if (!empty($input['banco'])) {
                $fields[] = 'banco = :banco';
                $params['banco'] = trim($input['banco']);
            }
            if (array_key_exists('limite', $input)) {
                $fields[] = 'limite = :limite';
                $params['limite'] = (float)$input['limite'];
            }
            if (array_key_exists('cierre_dia', $input)) {
                $cierre = (int)$input['cierre_dia'];
                if ($cierre >= 1 && $cierre <= 31) {
                    $fields[] = 'cierre_dia = :cierre_dia';
                    $params['cierre_dia'] = $cierre;
                }
            }
            if (array_key_exists('vencimiento_dia', $input)) {
                $vence = (int)$input['vencimiento_dia'];
                if ($vence >= 1 && $vence <= 31) {
                    $fields[] = 'vencimiento_dia = :vencimiento_dia';
                    $params['vencimiento_dia'] = $vence;
                }
            }
            if (!empty($input['color'])) {
                $fields[] = 'color = :color';
                $params['color'] = $input['color'];
            }

            if (empty($fields)) {
                sendResponse(false, null, 'Sin campos para actualizar', 400);
            }

            $db->prepare(
                "UPDATE tarjetas SET " . implode(', ', $fields) . " WHERE id = :id"
            )->execute($params);

            sendResponse(true, null, 'Tarjeta actualizada');
            break;

        case 'DELETE':
            // Eliminar consumo
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['consumo_id'])) {
                sendResponse(false, null, 'consumo_id es requerido', 400);
            }

            $consumo_id = (int)$input['consumo_id'];

            $db->beginTransaction();
            try {
                // 1. SELECT consumo (verificar existe + obtener resumen_id, importe)
                $sqlCons = "SELECT resumen_id, importe FROM consumos_tarjeta WHERE id = :cid";
                $stmtCons = $db->prepare($sqlCons);
                $stmtCons->execute(['cid' => $consumo_id]);
                $consumo = $stmtCons->fetch();

                if (!$consumo) {
                    $db->rollBack();
                    sendResponse(false, null, 'Consumo no encontrado', 404);
                }

                $resumen_id = $consumo['resumen_id'];
                $importe = (float)$consumo['importe'];

                // 2. Verificar que el resumen no esté pagado
                if ($resumen_id) {
                    $sqlVerif = "SELECT pagado FROM resumenes_tarjeta WHERE id = :rid";
                    $stmtVerif = $db->prepare($sqlVerif);
                    $stmtVerif->execute(['rid' => $resumen_id]);
                    $resumen = $stmtVerif->fetch();

                    if ($resumen && $resumen['pagado']) {
                        $db->rollBack();
                        sendResponse(false, null, 'No se puede eliminar un consumo de un resumen ya pagado', 422);
                    }
                }

                // 3. UPDATE resumen descontando el importe
                if ($resumen_id) {
                    $sqlActu = "UPDATE resumenes_tarjeta SET total_consumido = total_consumido - :imp WHERE id = :rid";
                    $stmtActu = $db->prepare($sqlActu);
                    $stmtActu->execute(['imp' => $importe, 'rid' => $resumen_id]);
                }

                // 4. DELETE consumo
                $sqlDel = "DELETE FROM consumos_tarjeta WHERE id = :cid";
                $stmtDel = $db->prepare($sqlDel);
                $stmtDel->execute(['cid' => $consumo_id]);

                $db->commit();
                sendResponse(true, null, 'Consumo eliminado');

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;

        default:
            sendResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    sendResponse(false, null, 'Error: ' . $e->getMessage(), 500);
}
