<?php
header('Content-Type: application/json');
require_once 'config.php';

$response = ['success' => false, 'message' => ''];

// Obtener acción
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'actualizar_estado') {
    // Actualizar estado de cuota (PAGADA/IMPAGA)
    $nro_cuota = isset($_POST['nro_cuota']) ? intval($_POST['nro_cuota']) : 0;
    $nuevo_estado = isset($_POST['estado']) ? $_POST['estado'] : '';

    if ($nro_cuota > 0 && in_array($nuevo_estado, ['PAGADA', 'IMPAGA'])) {
        $fecha_pago = ($nuevo_estado === 'PAGADA') ? date('Y-m-d') : NULL;

        $sql = "UPDATE cuotas_hipoteca
                SET estado = ?, fecha_pago = ?
                WHERE nro_cuota = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ssi", $nuevo_estado, $fecha_pago, $nro_cuota);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Cuota {$nro_cuota} actualizada a {$nuevo_estado}";
        } else {
            $response['message'] = "Error al actualizar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = "Datos inválidos";
    }
}

elseif ($action === 'actualizar_valor_uva') {
    // Actualizar valor de UVA (afecta todas las cuotas)
    $valor_uva = isset($_POST['valor_uva']) ? floatval($_POST['valor_uva']) : 0;

    if ($valor_uva > 0) {
        $sql = "UPDATE cuotas_hipoteca SET valor_uva = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("d", $valor_uva);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Valor de UVA actualizado a $" . number_format($valor_uva, 2);
            $response['valor_uva'] = $valor_uva;
        } else {
            $response['message'] = "Error al actualizar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = "Valor de UVA inválido";
    }
}

elseif ($action === 'obtener_datos') {
    // Obtener todos los datos de la tabla
    $sql = "SELECT nro_cuota, estado, fecha_vencimiento, capital, interes, total_uva,
            valor_uva, total_pesos, fecha_pago, observaciones
            FROM cuotas_hipoteca
            ORDER BY nro_cuota";

    $result = $conexion->query($sql);

    if ($result) {
        $cuotas = [];
        while ($row = $result->fetch_assoc()) {
            $cuotas[] = $row;
        }

        $response['success'] = true;
        $response['cuotas'] = $cuotas;

        // Calcular estadísticas
        $sql_stats = "SELECT
                      COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as pagadas,
                      COUNT(CASE WHEN estado = 'IMPAGA' THEN 1 END) as impagas,
                      SUM(CASE WHEN estado = 'PAGADA' THEN total_pesos ELSE 0 END) as pagado_pesos,
                      SUM(CASE WHEN estado = 'IMPAGA' THEN total_pesos ELSE 0 END) as pendiente_pesos,
                      MAX(valor_uva) as valor_uva_actual
                      FROM cuotas_hipoteca
                      WHERE valor_uva IS NOT NULL";

        $stats = $conexion->query($sql_stats)->fetch_assoc();
        $response['stats'] = $stats;
    } else {
        $response['message'] = "Error al obtener datos: " . $conexion->error;
    }
}

echo json_encode($response);
$conexion->close();
