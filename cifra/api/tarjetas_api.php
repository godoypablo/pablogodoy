<?php
/**
 * API REST para Tarjetas de Crédito
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
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

function _calcularPeriodos($cierre_dia, $vencimiento_dia) {
    $hoy = new DateTime('now');
    $dia_hoy = (int)$hoy->format('j');

    if ($dia_hoy > $cierre_dia) {
        $cerrado_mes = (int)$hoy->format('n');
        $cerrado_anio = (int)$hoy->format('Y');
        $acumulando_mes = $cerrado_mes + 1;
        $acumulando_anio = $cerrado_anio;
        if ($acumulando_mes > 12) {
            $acumulando_mes = 1;
            $acumulando_anio++;
        }
    } else {
        $acumulando_mes = (int)$hoy->format('n');
        $acumulando_anio = (int)$hoy->format('Y');
        $cerrado_mes = $acumulando_mes - 1;
        $cerrado_anio = $acumulando_anio;
        if ($cerrado_mes < 1) {
            $cerrado_mes = 12;
            $cerrado_anio--;
        }
    }

    if ($vencimiento_dia > $cierre_dia) {
        $fecha_vencimiento = new DateTime("$cerrado_anio-" . str_pad($cerrado_mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($vencimiento_dia, 2, '0', STR_PAD_LEFT));
    } else {
        $fecha_vence_mes = $cerrado_mes + 1;
        $fecha_vence_anio = $cerrado_anio;
        if ($fecha_vence_mes > 12) {
            $fecha_vence_mes = 1;
            $fecha_vence_anio++;
        }
        $fecha_vencimiento = new DateTime("$fecha_vence_anio-" . str_pad($fecha_vence_mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($vencimiento_dia, 2, '0', STR_PAD_LEFT));
    }

    $fecha_fin_cerrado = new DateTime("$cerrado_anio-" . str_pad($cerrado_mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($cierre_dia, 2, '0', STR_PAD_LEFT));
    $fecha_inicio_cerrado = (clone $fecha_fin_cerrado)->modify('-1 month')->modify('+1 day');

    $fecha_fin_acumulando = new DateTime("$acumulando_anio-" . str_pad($acumulando_mes, 2, '0', STR_PAD_LEFT) . '-' . str_pad($cierre_dia, 2, '0', STR_PAD_LEFT));
    $fecha_inicio_acumulando = (clone $fecha_fin_acumulando)->modify('-1 month')->modify('+1 day');

    return [
        'cerrado' => [
            'mes' => $cerrado_mes,
            'anio' => $cerrado_anio,
            'fecha_inicio' => $fecha_inicio_cerrado->format('Y-m-d'),
            'fecha_fin' => $fecha_fin_cerrado->format('Y-m-d')
        ],
        'acumulando' => [
            'mes' => $acumulando_mes,
            'anio' => $acumulando_anio,
            'fecha_inicio' => $fecha_inicio_acumulando->format('Y-m-d'),
            'fecha_fin' => $fecha_fin_acumulando->format('Y-m-d')
        ],
        'fecha_vencimiento' => $fecha_vencimiento->format('Y-m-d')
    ];
}

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
                            t.id, t.nombre, t.banco, t.limite, t.color, t.activo,
                            COALESCE(r.cierre_dia, t.cierre_dia) AS cierre_dia,
                            COALESCE(r.vencimiento_dia, t.vencimiento_dia) AS vencimiento_dia,
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
            elseif ($action === 'todos_periodos') {
                $sql = "SELECT t.id, t.nombre, t.limite, t.color,
                               t.cierre_dia AS cierre_dia_def, t.vencimiento_dia AS vencimiento_dia_def
                        FROM tarjetas t WHERE t.activo = 1 ORDER BY t.id ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $tarjetas = $stmt->fetchAll();

                foreach ($tarjetas as &$t) {
                    $t['limite'] = (float)$t['limite'];
                    $cdia_def = (int)$t['cierre_dia_def'];
                    $vdia_def = (int)$t['vencimiento_dia_def'];

                    $sqlP = "SELECT r.id, r.mes, r.anio,
                                    COALESCE(r.cierre_dia, :cdia) AS cierre_dia,
                                    COALESCE(r.vencimiento_dia, :vdia) AS vencimiento_dia,
                                    COALESCE(r.total_consumido, 0) AS total_consumido,
                                    COALESCE(r.pagado, 0) AS pagado,
                                    (SELECT COUNT(*) FROM consumos_tarjeta WHERE resumen_id = r.id) AS total_items
                             FROM resumenes_tarjeta r
                             WHERE r.tarjeta_id = :tid
                             ORDER BY r.anio DESC, r.mes DESC";
                    $stmtP = $db->prepare($sqlP);
                    $stmtP->execute(['tid' => (int)$t['id'], 'cdia' => $cdia_def, 'vdia' => $vdia_def]);
                    $periodos = $stmtP->fetchAll();

                    foreach ($periodos as &$p) {
                        $p['total_consumido'] = (float)$p['total_consumido'];
                        $p['pagado']          = (int)$p['pagado'];
                        $p['tiene_consumos']  = (int)$p['total_items'] > 0;
                        $cdia = (int)$p['cierre_dia'];
                        $vdia = (int)$p['vencimiento_dia'];
                        $mes  = (int)$p['mes'];
                        $anio = (int)$p['anio'];
                        $p['fecha_cierre'] = sprintf('%04d-%02d-%02d', $anio, $mes, $cdia);
                        if ($vdia > $cdia) {
                            $p['fecha_vencimiento'] = sprintf('%04d-%02d-%02d', $anio, $mes, $vdia);
                        } else {
                            $vm = $mes + 1; $va = $anio;
                            if ($vm > 12) { $vm = 1; $va++; }
                            $p['fecha_vencimiento'] = sprintf('%04d-%02d-%02d', $va, $vm, $vdia);
                        }
                        unset($p['total_items']);
                    }
                    unset($p);
                    $t['periodos'] = $periodos;
                    unset($t['cierre_dia_def'], $t['vencimiento_dia_def']);
                }
                unset($t);

                sendResponse(true, $tarjetas);
            }
            elseif ($action === 'periodos_activos') {
                $sql = "SELECT t.id, t.nombre, t.banco, t.limite, t.cierre_dia, t.vencimiento_dia, t.color
                        FROM tarjetas t
                        WHERE t.activo = 1
                        ORDER BY t.id ASC";

                $stmt = $db->prepare($sql);
                $stmt->execute();
                $tarjetas = $stmt->fetchAll();

                foreach ($tarjetas as &$t) {
                    $t['limite'] = (float)$t['limite'];
                    $cierre_dia = (int)$t['cierre_dia'];
                    $vencimiento_dia = (int)$t['vencimiento_dia'];

                    $periodos = _calcularPeriodos($cierre_dia, $vencimiento_dia);
                    $mes_cerrado = $periodos['cerrado']['mes'];
                    $anio_cerrado = $periodos['cerrado']['anio'];
                    $mes_acum = $periodos['acumulando']['mes'];
                    $anio_acum = $periodos['acumulando']['anio'];
                    $fecha_vencimiento = $periodos['fecha_vencimiento'];

                    $sqlCerrado = "SELECT COALESCE(total_consumido, 0) AS total, COALESCE(pagado, 0) AS pagado, id AS resumen_id
                                   FROM resumenes_tarjeta
                                   WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                    $stmtCerrado = $db->prepare($sqlCerrado);
                    $stmtCerrado->execute(['tid' => (int)$t['id'], 'mes' => $mes_cerrado, 'anio' => $anio_cerrado]);
                    $cerrado = $stmtCerrado->fetch();

                    $sqlAcum = "SELECT COALESCE(total_consumido, 0) AS total, id AS resumen_id
                                FROM resumenes_tarjeta
                                WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                    $stmtAcum = $db->prepare($sqlAcum);
                    $stmtAcum->execute(['tid' => (int)$t['id'], 'mes' => $mes_acum, 'anio' => $anio_acum]);
                    $acum = $stmtAcum->fetch();

                    $t['periodo_cerrado'] = [
                        'mes' => $mes_cerrado,
                        'anio' => $anio_cerrado,
                        'total' => (float)($cerrado ? $cerrado['total'] : 0),
                        'pagado' => (int)($cerrado ? $cerrado['pagado'] : 0),
                        'resumen_id' => $cerrado ? (int)$cerrado['resumen_id'] : null,
                        'fecha_vencimiento' => $fecha_vencimiento
                    ];

                    $t['periodo_acumulando'] = [
                        'mes' => $mes_acum,
                        'anio' => $anio_acum,
                        'total' => (float)($acum ? $acum['total'] : 0),
                        'resumen_id' => $acum ? (int)$acum['resumen_id'] : null
                    ];
                }
                unset($t);

                sendResponse(true, $tarjetas);
            }
            elseif ($action === 'consumos_periodos') {
                $tarjeta_id = isset($_GET['tarjeta_id']) ? (int)$_GET['tarjeta_id'] : 0;
                if (!$tarjeta_id) sendResponse(false, null, 'tarjeta_id es requerido', 400);

                $sqlTarj = "SELECT cierre_dia, vencimiento_dia FROM tarjetas WHERE id = :tid";
                $stmtTarj = $db->prepare($sqlTarj);
                $stmtTarj->execute(['tid' => $tarjeta_id]);
                $tarjeta = $stmtTarj->fetch();

                if (!$tarjeta) sendResponse(false, null, 'Tarjeta no encontrada', 404);

                $cierre_dia = (int)$tarjeta['cierre_dia'];
                $vencimiento_dia = (int)$tarjeta['vencimiento_dia'];

                // Obtener el último período pagado
                $sqlUltimoPagado = "SELECT mes, anio FROM resumenes_tarjeta
                                    WHERE tarjeta_id = :tid AND pagado = 1
                                    ORDER BY anio DESC, mes DESC LIMIT 1";
                $stmtUltimoPagado = $db->prepare($sqlUltimoPagado);
                $stmtUltimoPagado->execute(['tid' => $tarjeta_id]);
                $ultimoPagado = $stmtUltimoPagado->fetch();

                // Calcular próximo período (después del último pagado)
                if ($ultimoPagado) {
                    $mes_prox = (int)$ultimoPagado['mes'] + 1;
                    $anio_prox = (int)$ultimoPagado['anio'];
                    if ($mes_prox > 12) {
                        $mes_prox = 1;
                        $anio_prox++;
                    }
                } else {
                    // Si no hay pagado, próximo es el mes actual
                    $mes_prox = date('n');
                    $anio_prox = date('Y');
                }

                // Obtener cierre_dia del próximo período
                $sqlCierreProx = "SELECT cierre_dia FROM resumenes_tarjeta
                                  WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                $stmtCierreProx = $db->prepare($sqlCierreProx);
                $stmtCierreProx->execute(['tid' => $tarjeta_id, 'mes' => $mes_prox, 'anio' => $anio_prox]);
                $resumenProx = $stmtCierreProx->fetch();
                $cierre_prox = $resumenProx ? (int)$resumenProx['cierre_dia'] : $cierre_dia;

                // Calcular fecha de cierre del próximo período
                $fechaCierreProx = new DateTime("$anio_prox-" . str_pad($mes_prox, 2, '0', STR_PAD_LEFT) . "-" . str_pad($cierre_prox, 2, '0', STR_PAD_LEFT));

                // Obtener consumos con fecha anterior a la fecha de cierre del próximo período
                $sqlConsumos = "SELECT ct.*, cat.nombre AS categoria_nombre, cat.color AS categoria_color
                               FROM consumos_tarjeta ct
                               LEFT JOIN categorias cat ON cat.id = ct.categoria_id
                               WHERE ct.tarjeta_id = :tid AND ct.fecha < :fecha_cierre
                               ORDER BY ct.fecha DESC, ct.id DESC";
                $stmtConsumos = $db->prepare($sqlConsumos);
                $stmtConsumos->execute(['tid' => $tarjeta_id, 'fecha_cierre' => $fechaCierreProx->format('Y-m-d')]);
                $consumos = $stmtConsumos->fetchAll();

                // Convertir importes a float
                foreach ($consumos as &$c) {
                    $c['importe'] = (float)$c['importe'];
                }
                unset($c);

                // Calcular total de consumos
                $totalAcumulado = array_sum(array_column($consumos, 'importe'));

                sendResponse(true, [
                    'consumos' => $consumos,
                    'total_acumulado' => $totalAcumulado,
                    'proximo_vencimiento' => [
                        'mes' => $mes_prox,
                        'anio' => $anio_prox,
                        'cierre_dia' => $cierre_prox,
                        'fecha_cierre' => $fechaCierreProx->format('Y-m-d')
                    ]
                ]);
            }
            elseif ($action === 'consumos_por_tarjeta') {
                // GET ?action=consumos_por_tarjeta&mes=X&anio=Y → consumos de todas las tarjetas del mes
                $mes  = isset($_GET['mes'])  ? (int)$_GET['mes']  : date('n');
                $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');

                $sql = "SELECT t.id, t.nombre, t.color FROM tarjetas t WHERE t.activo = 1 ORDER BY t.id ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $tarjetas = $stmt->fetchAll();

                foreach ($tarjetas as &$t) {
                    $sqlCons = "SELECT ct.id, ct.fecha, ct.descripcion, ct.importe, ct.categoria_id, ct.cuotas_total, ct.cuota_numero,
                                       cat.nombre AS categoria_nombre, cat.color AS categoria_color
                                FROM consumos_tarjeta ct
                                LEFT JOIN categorias cat ON cat.id = ct.categoria_id
                                WHERE ct.tarjeta_id = :tid AND ct.mes = :mes AND ct.anio = :anio
                                ORDER BY ct.fecha DESC, ct.id DESC";
                    $stmtCons = $db->prepare($sqlCons);
                    $stmtCons->execute(['tid' => (int)$t['id'], 'mes' => $mes, 'anio' => $anio]);
                    $consumos = $stmtCons->fetchAll();

                    foreach ($consumos as &$c) {
                        $c['importe'] = (float)$c['importe'];
                    }
                    unset($c);

                    $t['consumos'] = $consumos;
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
                // POST: INSERT consumo(s) + upsert resumen(es)
                if (empty($input['tarjeta_id']) || empty($input['descripcion']) ||
                    !isset($input['importe']) || $input['importe'] <= 0 || empty($input['fecha'])) {
                    sendResponse(false, null, 'Campos requeridos: tarjeta_id, descripcion, importe > 0, fecha', 400);
                }

                $tarjeta_id = (int)$input['tarjeta_id'];
                $importe_total = (float)$input['importe'];
                $fecha = trim($input['fecha']);
                $desc = trim($input['descripcion']);
                $categoria_id = isset($input['categoria_id']) && $input['categoria_id'] ? (int)$input['categoria_id'] : null;
                $cuotas_total = isset($input['cuotas_total']) ? (int)$input['cuotas_total'] : 1;
                $cuota_numero = isset($input['cuota_numero']) ? (int)$input['cuota_numero'] : 1;

                // Validar rango de cuotas
                $cuotas_total = max(1, min($cuotas_total, 18));
                $cuota_numero = max(1, min($cuota_numero, $cuotas_total));

                // Validar fecha
                $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
                if (!$fechaObj || $fechaObj->format('Y-m-d') !== $fecha) {
                    sendResponse(false, null, 'Fecha inválida (formato Y-m-d)', 400);
                }

                // Obtener cierre_dia del resumen del mes actual (o usar valor por defecto de la tarjeta)
                $dia_calc = (int)$fechaObj->format('j');
                $mes_calc = (int)$fechaObj->format('n');
                $anio_calc = (int)$fechaObj->format('Y');

                $sqlCierre = "SELECT cierre_dia FROM resumenes_tarjeta WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                $stmtCierre = $db->prepare($sqlCierre);
                $stmtCierre->execute(['tid' => $tarjeta_id, 'mes' => $mes_calc, 'anio' => $anio_calc]);
                $resumenCierre = $stmtCierre->fetch();

                // Si no existe resumen configurado, usar valor de la tarjeta
                $cierre_dia = $resumenCierre ? (int)$resumenCierre['cierre_dia'] : null;
                if (!$cierre_dia) {
                    $stmtTarj = $db->prepare("SELECT cierre_dia FROM tarjetas WHERE id = :id");
                    $stmtTarj->execute(['id' => $tarjeta_id]);
                    $tarjeta = $stmtTarj->fetch();
                    if (!$tarjeta) {
                        sendResponse(false, null, 'Tarjeta no encontrada', 404);
                    }
                    $cierre_dia = (int)$tarjeta['cierre_dia'];
                }

                // Calcular importe por cuota
                $es_cuotas = $cuotas_total > 1;
                $importe_cuota = $es_cuotas ? round($importe_total / $cuotas_total, 2) : $importe_total;

                // Calcular fecha base: la cuota 1 ocurrió (cuota_numero - 1) meses antes
                $fechaBase = clone $fechaObj;
                if ($cuota_numero > 1) {
                    $fechaBase->modify('-' . ($cuota_numero - 1) . ' months');
                }

                // Transacción
                $db->beginTransaction();
                try {
                    $consumo_padre_id = null;
                    $consumos_ids = [];
                    $resumenes_procesados = [];

                    // Pre-crear resumenes para todos los meses necesarios
                    for ($i = 1; $i <= $cuotas_total; $i++) {
                        $fechaTemp = clone $fechaBase;
                        $fechaTemp->modify('+' . ($i - 1) . ' months');
                        $diaTemp = (int)$fechaTemp->format('j');
                        $mesTemp = (int)$fechaTemp->format('n');
                        $anioTemp = (int)$fechaTemp->format('Y');

                        // Ajustar mes si es posterior al cierre
                        if ($diaTemp > $cierre_dia) {
                            $fechaTemp->modify('+1 month');
                            $mesTemp = (int)$fechaTemp->format('n');
                            $anioTemp = (int)$fechaTemp->format('Y');
                        }

                        // Crear resumen si no existe (asegurar que tenga cierre_dia correcto)
                        $sqlCheckRes = "SELECT id FROM resumenes_tarjeta WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                        $stmtCheckRes = $db->prepare($sqlCheckRes);
                        $stmtCheckRes->execute(['tid' => $tarjeta_id, 'mes' => $mesTemp, 'anio' => $anioTemp]);
                        if (!$stmtCheckRes->fetch()) {
                            $sqlCreateRes = "INSERT INTO resumenes_tarjeta (tarjeta_id, mes, anio, cierre_dia, vencimiento_dia, total_consumido)
                                            VALUES (:tid, :mes, :anio, :cdia, 15, 0)";
                            $stmtCreateRes = $db->prepare($sqlCreateRes);
                            $stmtCreateRes->execute(['tid' => $tarjeta_id, 'mes' => $mesTemp, 'anio' => $anioTemp, 'cdia' => $cierre_dia]);
                        }
                    }

                    // Generar todas las cuotas
                    for ($i = 1; $i <= $cuotas_total; $i++) {
                        // Calcular fecha de esta cuota (desde la base)
                        $fechaCuota = clone $fechaBase;
                        $fechaCuota->modify('+' . ($i - 1) . ' months');

                        // Calcular mes/año
                        $dia = (int)$fechaCuota->format('j');
                        $mes = (int)$fechaCuota->format('n');
                        $anio = (int)$fechaCuota->format('Y');

                        // Ajustar mes si es posterior al cierre (usando cierre_dia de la tarjeta)
                        if ($dia > $cierre_dia) {
                            $fechaCuota->modify('+1 month');
                            $mes = (int)$fechaCuota->format('n');
                            $anio = (int)$fechaCuota->format('Y');
                        }

                        // Obtener resumen (garantizado que existe por pre-creación)
                        $sqlResumen = "SELECT id FROM resumenes_tarjeta WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                        $stmtRes = $db->prepare($sqlResumen);
                        $stmtRes->execute(['tid' => $tarjeta_id, 'mes' => $mes, 'anio' => $anio]);
                        $resumenData = $stmtRes->fetch();
                        $resumen_id = $resumenData['id'];

                        // Recordar resumenes para no actualizar duplicados
                        $resumenes_procesados[$resumen_id] = true;

                        // INSERT consumo con cuotas_total, cuota_numero y consumo_padre_id
                        $sqlCons = "INSERT INTO consumos_tarjeta
                                    (tarjeta_id, resumen_id, descripcion, importe, fecha, mes, anio, categoria_id, cuotas_total, cuota_numero, consumo_padre_id)
                                    VALUES (:tid, :rid, :desc, :imp, :fecha, :mes, :anio, :cat, :cuotas_total, :cuota_num, :padre_id)";
                        $stmtCons = $db->prepare($sqlCons);
                        $stmtCons->execute([
                            'tid' => $tarjeta_id,
                            'rid' => $resumen_id,
                            'desc' => $desc,
                            'imp' => $importe_cuota,
                            'fecha' => $fechaCuota->format('Y-m-d'),
                            'mes' => $mes,
                            'anio' => $anio,
                            'cat' => $categoria_id,
                            'cuotas_total' => $es_cuotas ? $cuotas_total : null,
                            'cuota_num' => $es_cuotas ? $i : null,
                            'padre_id' => $i === 1 ? null : $consumo_padre_id,
                        ]);

                        $consumo_id = $db->lastInsertId();
                        if ($i === 1) {
                            $consumo_padre_id = $consumo_id;
                        }
                        $consumos_ids[] = $consumo_id;
                    }

                    // Actualizar total_consumido en todos los resumenes afectados (una sola vez cada uno)
                    foreach (array_keys($resumenes_procesados) as $rid) {
                        $sqlUpdate = "UPDATE resumenes_tarjeta SET total_consumido = total_consumido + :imp WHERE id = :rid";
                        $stmtUpd = $db->prepare($sqlUpdate);
                        $stmtUpd->execute(['imp' => $importe_cuota, 'rid' => $rid]);
                    }

                    $db->commit();

                    sendResponse(true, [
                        'consumo_ids' => $consumos_ids,
                        'cuotas_generadas' => count($consumos_ids),
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
            elseif ($action === 'configurar_mes') {
                // POST: guardar configuración de tarjetas para un mes específico
                // Si se proporciona resumen_id sin tarjetas, solo actualizar el estado pagado
                if (!empty($input['resumen_id']) && isset($input['pagado']) && empty($input['tarjetas'])) {
                    $resumen_id = (int)$input['resumen_id'];
                    $pagado = (int)$input['pagado'];
                    $sqlUpdate = "UPDATE resumenes_tarjeta SET pagado = :pag, fecha_pago = " . ($pagado ? "CURDATE()" : "NULL") . " WHERE id = :rid";
                    $stmtUpdate = $db->prepare($sqlUpdate);
                    $stmtUpdate->execute(['pag' => $pagado, 'rid' => $resumen_id]);
                    sendResponse(true, null, 'Estado actualizado');
                    break;
                }

                if (empty($input['mes']) || empty($input['anio']) || empty($input['tarjetas'])) {
                    sendResponse(false, null, 'Campos requeridos: mes, anio, tarjetas[]', 400);
                }

                $mes = (int)$input['mes'];
                $anio = (int)$input['anio'];
                $tarjetas = $input['tarjetas']; // array de {tarjeta_id, limite, cierre_dia, vencimiento_dia}

                if ($mes < 1 || $mes > 12) {
                    sendResponse(false, null, 'mes debe estar entre 1 y 12', 400);
                }

                $db->beginTransaction();
                try {
                    foreach ($tarjetas as $t) {
                        if (empty($t['tarjeta_id'])) continue;

                        $tid = (int)$t['tarjeta_id'];
                        $limite = isset($t['limite']) ? (float)$t['limite'] : 0;
                        $cierre_dia = isset($t['cierre_dia']) ? (int)$t['cierre_dia'] : 1;
                        $vencimiento_dia = isset($t['vencimiento_dia']) ? (int)$t['vencimiento_dia'] : 10;

                        // Validar rangos
                        if ($cierre_dia < 1 || $cierre_dia > 31) $cierre_dia = 1;
                        if ($vencimiento_dia < 1 || $vencimiento_dia > 31) $vencimiento_dia = 10;

                        // Guardar límite en tabla tarjetas (genérico)
                        if ($limite > 0) {
                            $sqlLimite = "UPDATE tarjetas SET limite = :lim WHERE id = :tid";
                            $stmtLimite = $db->prepare($sqlLimite);
                            $stmtLimite->execute(['lim' => $limite, 'tid' => $tid]);
                        }

                        // INSERT or UPDATE resumen (cierre_dia y vencimiento_dia por período)
                        $updateClause = "cierre_dia = :cdia, vencimiento_dia = :vdia";
                        if (isset($input['pagado'])) {
                            $updateClause .= ", pagado = :pag, fecha_pago = " . (intval($input['pagado']) ? "CURDATE()" : "NULL");
                        }
                        $updateClause .= ", id = LAST_INSERT_ID(id)";

                        $sqlUpsert = "INSERT INTO resumenes_tarjeta (tarjeta_id, mes, anio, cierre_dia, vencimiento_dia, total_consumido)
                                      VALUES (:tid, :mes, :anio, :cdia, :vdia, 0)
                                      ON DUPLICATE KEY UPDATE
                                          $updateClause";
                        $stmtUpsert = $db->prepare($sqlUpsert);
                        $params = [
                            'tid' => $tid,
                            'mes' => $mes,
                            'anio' => $anio,
                            'cdia' => $cierre_dia,
                            'vdia' => $vencimiento_dia,
                        ];
                        if (isset($input['pagado'])) {
                            $params['pag'] = intval($input['pagado']);
                        }
                        $stmtUpsert->execute($params);
                    }

                    $db->commit();
                    sendResponse(true, null, 'Configuración guardada');

                } catch (Exception $e) {
                    $db->rollBack();
                    throw $e;
                }
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

        case 'PATCH':
            $input = json_decode(file_get_contents('php://input'), true);

            if (empty($input['consumo_id'])) {
                sendResponse(false, null, 'consumo_id es requerido', 400);
            }

            $consumo_id = (int)$input['consumo_id'];
            $nueva_fecha   = isset($input['fecha']) && $input['fecha'] ? trim($input['fecha']) : null;
            $nueva_cuota   = isset($input['cuota_numero']) ? (int)$input['cuota_numero'] : null;
            $nueva_desc    = isset($input['descripcion']) ? trim($input['descripcion']) : null;
            $nuevo_importe = isset($input['importe']) ? (float)$input['importe'] : null;

            if (!$nueva_fecha && $nueva_cuota === null && $nueva_desc === null && $nuevo_importe === null) {
                sendResponse(false, null, 'Debe especificar al menos un campo para editar', 400);
            }

            $db->beginTransaction();
            try {
                $sqlCons = "SELECT id, tarjeta_id, resumen_id, importe, mes, anio, cuotas_total, cuota_numero FROM consumos_tarjeta WHERE id = :cid";
                $stmtCons = $db->prepare($sqlCons);
                $stmtCons->execute(['cid' => $consumo_id]);
                $consumo = $stmtCons->fetch();

                if (!$consumo) {
                    $db->rollBack();
                    sendResponse(false, null, 'Consumo no encontrado', 404);
                }

                $resumen_id_viejo = (int)$consumo['resumen_id'];
                $sqlVerif = "SELECT pagado FROM resumenes_tarjeta WHERE id = :rid";
                $stmtVerif = $db->prepare($sqlVerif);
                $stmtVerif->execute(['rid' => $resumen_id_viejo]);
                $resumen = $stmtVerif->fetch();

                if ($resumen && $resumen['pagado']) {
                    $db->rollBack();
                    sendResponse(false, null, 'No se puede editar un consumo de un resumen pagado', 422);
                }

                $tarjeta_id = (int)$consumo['tarjeta_id'];
                $importe = (float)$consumo['importe'];
                $mes_nuevo = (int)$consumo['mes'];
                $anio_nuevo = (int)$consumo['anio'];
                $resumen_id_nuevo = $resumen_id_viejo;

                if ($nueva_fecha) {
                    $fechaObj = DateTime::createFromFormat('Y-m-d', $nueva_fecha);
                    if (!$fechaObj || $fechaObj->format('Y-m-d') !== $nueva_fecha) {
                        $db->rollBack();
                        sendResponse(false, null, 'Fecha inválida (formato Y-m-d)', 400);
                    }

                    $dia = (int)$fechaObj->format('j');
                    $mes_fecha = (int)$fechaObj->format('n');
                    $anio_fecha = (int)$fechaObj->format('Y');

                    $sqlCierre = "SELECT cierre_dia FROM resumenes_tarjeta WHERE tarjeta_id = :tid AND mes = :mes AND anio = :anio";
                    $stmtCierre = $db->prepare($sqlCierre);
                    $stmtCierre->execute(['tid' => $tarjeta_id, 'mes' => $mes_fecha, 'anio' => $anio_fecha]);
                    $resumenCierre = $stmtCierre->fetch();

                    $cierre_dia = $resumenCierre ? (int)$resumenCierre['cierre_dia'] : null;
                    if (!$cierre_dia) {
                        $stmtTarj = $db->prepare("SELECT cierre_dia FROM tarjetas WHERE id = :id");
                        $stmtTarj->execute(['id' => $tarjeta_id]);
                        $tarjeta = $stmtTarj->fetch();
                        if (!$tarjeta) {
                            $db->rollBack();
                            sendResponse(false, null, 'Tarjeta no encontrada', 404);
                        }
                        $cierre_dia = (int)$tarjeta['cierre_dia'];
                    }

                    if ($dia > $cierre_dia) {
                        $fechaObj->modify('+1 month');
                        $mes_fecha = (int)$fechaObj->format('n');
                        $anio_fecha = (int)$fechaObj->format('Y');
                    }

                    $mes_nuevo = $mes_fecha;
                    $anio_nuevo = $anio_fecha;

                    if ($mes_nuevo !== (int)$consumo['mes'] || $anio_nuevo !== (int)$consumo['anio']) {
                        $sqlResumenNuevo = "INSERT INTO resumenes_tarjeta (tarjeta_id, mes, anio, total_consumido)
                                           VALUES (:tid, :mes, :anio, 0)
                                           ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
                        $stmtResNuevo = $db->prepare($sqlResumenNuevo);
                        $stmtResNuevo->execute(['tid' => $tarjeta_id, 'mes' => $mes_nuevo, 'anio' => $anio_nuevo]);
                        $resumen_id_nuevo = $db->lastInsertId();

                        $sqlDescontar = "UPDATE resumenes_tarjeta SET total_consumido = total_consumido - :imp WHERE id = :rid";
                        $stmtDesc = $db->prepare($sqlDescontar);
                        $stmtDesc->execute(['imp' => $importe, 'rid' => $resumen_id_viejo]);

                        $sqlSumar = "UPDATE resumenes_tarjeta SET total_consumido = total_consumido + :imp WHERE id = :rid";
                        $stmtSum = $db->prepare($sqlSumar);
                        $stmtSum->execute(['imp' => $importe, 'rid' => $resumen_id_nuevo]);
                    }
                }

                $fields = [];
                $params = ['cid' => $consumo_id];

                if ($nueva_fecha) {
                    $fields[] = 'fecha = :fecha';
                    $params['fecha'] = $nueva_fecha;
                    $fields[] = 'mes = :mes';
                    $params['mes'] = $mes_nuevo;
                    $fields[] = 'anio = :anio';
                    $params['anio'] = $anio_nuevo;
                    $fields[] = 'resumen_id = :resumen_id';
                    $params['resumen_id'] = $resumen_id_nuevo;
                }

                if ($nueva_cuota !== null) {
                    $cuotas_total = (int)$consumo['cuotas_total'];
                    if ($cuotas_total > 1) {
                        $nueva_cuota = max(1, min($nueva_cuota, $cuotas_total));
                        $fields[] = 'cuota_numero = :cuota_numero';
                        $params['cuota_numero'] = $nueva_cuota;
                    }
                }

                if ($nueva_desc !== null && $nueva_desc !== '') {
                    $fields[] = 'descripcion = :desc';
                    $params['desc'] = $nueva_desc;
                }

                if ($nuevo_importe !== null && $nuevo_importe > 0) {
                    $importe_actual = (float)$consumo['importe'];
                    $diff = $nuevo_importe - $importe_actual;
                    $fields[] = 'importe = :imp';
                    $params['imp'] = $nuevo_importe;
                    // Actualizar total_consumido del resumen
                    $db->prepare("UPDATE resumenes_tarjeta SET total_consumido = total_consumido + :diff WHERE id = :rid")
                        ->execute(['diff' => $diff, 'rid' => $resumen_id_viejo]);
                }

                if (!empty($fields)) {
                    $db->prepare(
                        "UPDATE consumos_tarjeta SET " . implode(', ', $fields) . " WHERE id = :cid"
                    )->execute($params);
                }

                $db->commit();
                sendResponse(true, null, 'Consumo actualizado');

            } catch (Exception $e) {
                $db->rollBack();
                throw $e;
            }
            break;

        case 'DELETE':
            $input = json_decode(file_get_contents('php://input'), true);

            // Eliminar período (resumen sin consumos)
            if (!empty($input['action']) && $input['action'] === 'periodo') {
                $resumen_id = (int)($input['resumen_id'] ?? 0);
                if (!$resumen_id) sendResponse(false, null, 'resumen_id es requerido', 400);

                $stmtCheck = $db->prepare("SELECT COUNT(*) AS cnt FROM consumos_tarjeta WHERE resumen_id = :rid");
                $stmtCheck->execute(['rid' => $resumen_id]);
                if ((int)$stmtCheck->fetch()['cnt'] > 0) {
                    sendResponse(false, null, 'No se puede eliminar un período con consumos', 422);
                }

                $db->prepare("DELETE FROM resumenes_tarjeta WHERE id = :rid")->execute(['rid' => $resumen_id]);
                sendResponse(true, null, 'Período eliminado');
            }

            // Eliminar consumo (y opcionalmente todas sus cuotas relacionadas)
            if (empty($input['consumo_id'])) {
                sendResponse(false, null, 'consumo_id es requerido', 400);
            }

            $consumo_id = (int)$input['consumo_id'];
            $eliminar_todas = isset($input['eliminar_todas']) && $input['eliminar_todas'];

            $db->beginTransaction();
            try {
                // 1. SELECT consumo
                $sqlCons = "SELECT resumen_id, importe, cuotas_total, cuota_numero, consumo_padre_id FROM consumos_tarjeta WHERE id = :cid";
                $stmtCons = $db->prepare($sqlCons);
                $stmtCons->execute(['cid' => $consumo_id]);
                $consumo = $stmtCons->fetch();

                if (!$consumo) {
                    $db->rollBack();
                    sendResponse(false, null, 'Consumo no encontrado', 404);
                }

                // 2. Si es un consumo con cuotas, buscar hermanos
                $consumos_a_eliminar = [$consumo_id];
                if ($consumo['cuotas_total'] && $consumo['cuotas_total'] > 1) {
                    $padre_id = $consumo['consumo_padre_id'] ?? $consumo_id;

                    // Obtener todos los consumos del grupo
                    $sqlHermanos = "SELECT id FROM consumos_tarjeta WHERE consumo_padre_id = :padre OR id = :padre ORDER BY cuota_numero ASC";
                    $stmtHermanos = $db->prepare($sqlHermanos);
                    $stmtHermanos->execute(['padre' => $padre_id]);
                    $hermanos = $stmtHermanos->fetchAll();

                    if (count($hermanos) > 1 && !$eliminar_todas) {
                        // Retornar 409 Conflict con la lista de cuotas
                        $db->rollBack();
                        $cuota_info = [];
                        foreach ($hermanos as $h) {
                            $cuota_info[] = $h;
                        }
                        sendResponse(false, $cuota_info, 'Este consumo tiene ' . count($hermanos) . ' cuotas. Confirmar eliminación de todas.', 409);
                    }

                    if ($eliminar_todas) {
                        $consumos_a_eliminar = array_map(fn($h) => $h['id'], $hermanos);
                    }
                }

                // 3. Verificar que ninguno de los resumenes esté pagado
                $resumenes_affected = [];
                foreach ($consumos_a_eliminar as $cid) {
                    $sqlConsVerif = "SELECT resumen_id, importe FROM consumos_tarjeta WHERE id = :cid";
                    $stmtConsVerif = $db->prepare($sqlConsVerif);
                    $stmtConsVerif->execute(['cid' => $cid]);
                    $consVerif = $stmtConsVerif->fetch();

                    if ($consVerif && $consVerif['resumen_id']) {
                        $sqlResFinal = "SELECT pagado FROM resumenes_tarjeta WHERE id = :rid";
                        $stmtResFinal = $db->prepare($sqlResFinal);
                        $stmtResFinal->execute(['rid' => $consVerif['resumen_id']]);
                        $resFinal = $stmtResFinal->fetch();

                        if ($resFinal && $resFinal['pagado']) {
                            $db->rollBack();
                            sendResponse(false, null, 'No se puede eliminar consumos de resúmenes ya pagados', 422);
                        }

                        $resumenes_affected[$consVerif['resumen_id']] = (float)$consVerif['importe'];
                    }
                }

                // 4. UPDATE resumenes descontando importes
                foreach ($resumenes_affected as $rid => $imp) {
                    $sqlActu = "UPDATE resumenes_tarjeta SET total_consumido = total_consumido - :imp WHERE id = :rid";
                    $stmtActu = $db->prepare($sqlActu);
                    $stmtActu->execute(['imp' => $imp, 'rid' => $rid]);
                }

                // 5. DELETE consumos
                foreach ($consumos_a_eliminar as $cid) {
                    $sqlDel = "DELETE FROM consumos_tarjeta WHERE id = :cid";
                    $stmtDel = $db->prepare($sqlDel);
                    $stmtDel->execute(['cid' => $cid]);
                }

                $db->commit();
                $msg = count($consumos_a_eliminar) > 1
                    ? count($consumos_a_eliminar) . ' cuotas eliminadas'
                    : 'Consumo eliminado';
                sendResponse(true, null, $msg);

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
