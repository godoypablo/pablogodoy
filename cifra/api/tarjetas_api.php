<?php
/**
 * API Tarjetas de crédito (simplificada)
 *
 * Los resúmenes NO se cargan acá: se cargan en la planilla, en los conceptos
 * de gasto vinculados a cada tarjeta (pesos y dólares, categoría Bancos).
 * Esta API solo lee esos registros para mostrar la evolución mes a mes.
 *
 * GET  ?mes&anio            → tarjetas + 12 meses hasta mes/anio (inclusive)
 * POST                      → crear tarjeta
 * PUT  ?id=X                → editar tarjeta
 */
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../config/auth_check.php';
require_auth_or_401();

function sendResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

const TARJETAS_MESES_HISTORIA = 12;

try {
    $db     = Database::getInstance()->getConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $mes  = isset($_GET['mes'])  ? (int)$_GET['mes']  : (int)date('n');
            $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
            if ($mes < 1 || $mes > 12 || $anio < 2000) sendResponse(false, null, 'Mes/año inválidos', 400);

            $tarjetas = $db->query(
                "SELECT t.id, t.banco, t.nombre_tarjeta, t.ultimos_4, t.activa,
                        t.concepto_id, c1.nombre AS concepto_nombre,
                        t.concepto_usd_id, c2.nombre AS concepto_usd_nombre
                 FROM tarjetas_credito t
                 LEFT JOIN conceptos c1 ON c1.id = t.concepto_id
                 LEFT JOIN conceptos c2 ON c2.id = t.concepto_usd_id
                 ORDER BY t.activa DESC, t.nombre_tarjeta ASC"
            )->fetchAll();

            // Ventana de meses: del más viejo al seleccionado
            $hasta  = $anio * 12 + ($mes - 1);
            $desde  = $hasta - (TARJETAS_MESES_HISTORIA - 1);
            $meses  = [];
            for ($i = $desde; $i <= $hasta; $i++) {
                $meses[] = ['mes' => $i % 12 + 1, 'anio' => intdiv($i, 12)];
            }

            // Registros de los conceptos vinculados en esa ventana
            $conceptoIds = [];
            foreach ($tarjetas as $t) {
                if ($t['concepto_id'])     $conceptoIds[] = (int)$t['concepto_id'];
                if ($t['concepto_usd_id']) $conceptoIds[] = (int)$t['concepto_usd_id'];
            }

            $registros = [];
            if ($conceptoIds) {
                $in   = implode(',', array_fill(0, count($conceptoIds), '?'));
                $stmt = $db->prepare(
                    "SELECT concepto_id, mes, anio,
                            SUM(importe) AS importe,
                            MIN(fecha_vencimiento) AS fecha_vencimiento,
                            MIN(pagado) AS pagado
                     FROM registros_mensuales
                     WHERE concepto_id IN ($in)
                       AND (anio * 12 + mes - 1) BETWEEN ? AND ?
                     GROUP BY concepto_id, anio, mes"
                );
                $stmt->execute(array_merge($conceptoIds, [$desde, $hasta]));
                foreach ($stmt->fetchAll() as $r) {
                    $clave = sprintf('%04d-%02d', $r['anio'], $r['mes']);
                    $registros[(int)$r['concepto_id']][$clave] = [
                        'importe'           => (float)$r['importe'],
                        'fecha_vencimiento' => $r['fecha_vencimiento'],
                        'pagado'            => (int)$r['pagado'],
                    ];
                }
            }

            // Armar la serie por tarjeta: { 'YYYY-MM': { ars: {...}|null, usd: {...}|null } }
            foreach ($tarjetas as &$t) {
                $t['id']     = (int)$t['id'];
                $t['activa'] = (int)$t['activa'];
                $serie = [];
                foreach ($meses as $m) {
                    $clave = sprintf('%04d-%02d', $m['anio'], $m['mes']);
                    $serie[$clave] = [
                        'ars' => $t['concepto_id']     ? ($registros[(int)$t['concepto_id']][$clave] ?? null)     : null,
                        'usd' => $t['concepto_usd_id'] ? ($registros[(int)$t['concepto_usd_id']][$clave] ?? null) : null,
                    ];
                }
                $t['serie'] = $serie;
            }
            unset($t);

            sendResponse(true, ['tarjetas' => $tarjetas, 'meses' => $meses]);
            break;

        case 'POST':
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true) ?: [];

            $nombre = trim($data['nombre_tarjeta'] ?? '');
            if ($nombre === '') sendResponse(false, null, 'El nombre de la tarjeta es obligatorio', 400);

            $campos = [
                'banco'           => trim($data['banco'] ?? ''), // NOT NULL en la tabla
                'nombre_tarjeta'  => $nombre,
                'ultimos_4'       => preg_replace('/\D/', '', $data['ultimos_4'] ?? '') ?: null,
                'concepto_id'     => !empty($data['concepto_id'])     ? (int)$data['concepto_id']     : null,
                'concepto_usd_id' => !empty($data['concepto_usd_id']) ? (int)$data['concepto_usd_id'] : null,
                'activa'          => isset($data['activa']) ? (int)(bool)$data['activa'] : 1,
            ];
            if ($campos['ultimos_4'] !== null && strlen($campos['ultimos_4']) !== 4) {
                sendResponse(false, null, 'Los últimos dígitos deben ser 4 números', 400);
            }

            // Validar que los conceptos sean gastos de carga única en la moneda correcta
            foreach (['concepto_id' => 'ARS', 'concepto_usd_id' => 'USD'] as $campo => $moneda) {
                if (!$campos[$campo]) continue;
                $stmt = $db->prepare("SELECT tipo, moneda, permite_multiples FROM conceptos WHERE id = ?");
                $stmt->execute([$campos[$campo]]);
                $c = $stmt->fetch();
                if (!$c || $c['tipo'] !== 'gasto' || $c['permite_multiples'] || ($c['moneda'] ?? 'ARS') !== $moneda) {
                    sendResponse(false, null, "El concepto en " . ($moneda === 'USD' ? 'dólares' : 'pesos') .
                        " debe ser un gasto de una sola carga por mes, en $moneda", 422);
                }
            }

            if ($method === 'POST') {
                $cols = implode(', ', array_keys($campos));
                $ph   = implode(', ', array_fill(0, count($campos), '?'));
                $db->prepare("INSERT INTO tarjetas_credito ($cols) VALUES ($ph)")->execute(array_values($campos));
                sendResponse(true, ['id' => (int)$db->lastInsertId()], 'Tarjeta creada');
            }

            $id = (int)($_GET['id'] ?? 0);
            if (!$id) sendResponse(false, null, 'ID requerido', 400);
            $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($campos)));
            $db->prepare("UPDATE tarjetas_credito SET $sets WHERE id = ?")->execute([...array_values($campos), $id]);
            sendResponse(true, ['id' => $id], 'Tarjeta actualizada');
            break;

        default:
            sendResponse(false, null, 'Método no permitido', 405);
    }
} catch (Exception $e) {
    sendResponse(false, null, cifra_error_publico($e), 500);
}
