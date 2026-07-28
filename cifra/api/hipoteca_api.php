<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../config/auth_check.php';
require_auth_or_401();

// Intentar cargar database.php si existe
if (file_exists('../config/database.php')) {
    require_once '../config/database.php';
    $db = Database::getInstance()->getConnection();
} else {
    // Fallback: conectar directamente
    try {
        $db = new PDO('mysql:host=localhost;dbname=gastos_personales;charset=utf8mb4', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error de conexión a BD']);
        exit;
    }
}

$response = ['success' => false, 'message' => ''];
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    if ($action === 'actualizar_estado') {
        $nro_cuota = isset($_POST['nro_cuota']) ? intval($_POST['nro_cuota']) : 0;
        $nuevo_estado = isset($_POST['estado']) ? $_POST['estado'] : '';

        if ($nro_cuota > 0 && in_array($nuevo_estado, ['PAGADA', 'IMPAGA'])) {
            $fecha_pago = ($nuevo_estado === 'PAGADA') ? date('Y-m-d') : NULL;

            $sql = "UPDATE cuotas_hipoteca SET estado = ?, fecha_pago = ? WHERE nro_cuota = ?";
            $stmt = $db->prepare($sql);

            if ($stmt->execute([$nuevo_estado, $fecha_pago, $nro_cuota])) {
                $response['success'] = true;
                $response['message'] = "Cuota {$nro_cuota} actualizada a {$nuevo_estado}";
            } else {
                $response['message'] = "Error al actualizar";
            }
        } else {
            $response['message'] = "Datos inválidos";
        }
    }

    elseif ($action === 'actualizar_valor_uva') {
        $valor_uva = isset($_POST['valor_uva']) ? floatval($_POST['valor_uva']) : 0;

        if ($valor_uva > 0) {
            $sql = "UPDATE cuotas_hipoteca SET valor_uva = ?";
            $stmt = $db->prepare($sql);

            if ($stmt->execute([$valor_uva])) {
                $response['success'] = true;
                $response['message'] = "Valor de UVA actualizado a $" . number_format($valor_uva, 2);
                $response['valor_uva'] = $valor_uva;
            } else {
                $response['message'] = "Error al actualizar";
            }
        } else {
            $response['message'] = "Valor de UVA inválido";
        }
    }

    elseif ($action === 'actualizar_valor_uva_cuota') {
        $nro_cuota = isset($_POST['nro_cuota']) ? intval($_POST['nro_cuota']) : 0;
        $valor_uva = isset($_POST['valor_uva']) ? floatval($_POST['valor_uva']) : 0;

        if ($nro_cuota > 0 && $valor_uva > 0) {
            $sql = "UPDATE cuotas_hipoteca SET valor_uva = ? WHERE nro_cuota = ?";
            $stmt = $db->prepare($sql);

            if ($stmt->execute([$valor_uva, $nro_cuota])) {
                $response['success'] = true;
                $response['message'] = "Valor UVA de cuota {$nro_cuota} actualizado a $" . number_format($valor_uva, 2);
            } else {
                $response['message'] = "Error al actualizar";
            }
        } else {
            $response['message'] = "Datos inválidos";
        }
    }

    elseif ($action === 'obtener_datos') {
        $sql = "SELECT nro_cuota, estado, fecha_vencimiento, capital, interes, total_uva,
                valor_uva, total_pesos, fecha_pago, observaciones
                FROM cuotas_hipoteca
                ORDER BY nro_cuota";

        $stmt = $db->query($sql);
        $cuotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['cuotas'] = $cuotas;

        // Estadísticas
        $sql_stats = "SELECT
                      COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as pagadas,
                      COUNT(CASE WHEN estado = 'IMPAGA' THEN 1 END) as impagas,
                      SUM(CASE WHEN estado = 'PAGADA' THEN total_pesos ELSE 0 END) as pagado_pesos,
                      SUM(CASE WHEN estado = 'IMPAGA' THEN total_pesos ELSE 0 END) as pendiente_pesos,
                      MAX(valor_uva) as valor_uva_actual
                      FROM cuotas_hipoteca
                      WHERE valor_uva IS NOT NULL";

        $stmt_stats = $db->query($sql_stats);
        $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
        $response['stats'] = $stats;
    }
} catch (Exception $e) {
    http_response_code(500);
    $response['message'] = "Error: " . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
