<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cuotas Hipotecarias</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
        }

        header h1 {
            margin-bottom: 10px;
            font-size: 28px;
        }

        .controls {
            display: flex;
            gap: 15px;
            align-items: center;
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            flex-wrap: wrap;
        }

        .control-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        input[type="number"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            width: 120px;
        }

        button {
            padding: 8px 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            font-size: 14px;
        }

        button:hover {
            background: #5568d3;
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            padding: 20px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .stat {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .stat-value.pagadas { color: #27ae60; }
        .stat-value.impagas { color: #e74c3c; }
        .stat-value.pesos { color: #3498db; }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
            position: sticky;
            top: 0;
        }

        th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
            font-size: 13px;
            text-transform: uppercase;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .nro-cuota {
            font-weight: 600;
            width: 60px;
            text-align: center;
        }

        .checkbox-cell {
            text-align: center;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .numero {
            text-align: right;
            font-family: 'Courier New', monospace;
            width: 90px;
        }

        .estado-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .estado-badge.pagada {
            background: #d4edda;
            color: #155724;
        }

        .estado-badge.impaga {
            background: #f8d7da;
            color: #721c24;
        }

        .valor-uva-input {
            width: 100px;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .loader {
            display: none;
            text-align: center;
            padding: 20px;
            color: #667eea;
        }

        .loader.show {
            display: block;
        }

        .loading-spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .mensaje {
            padding: 15px 30px;
            margin: 10px 30px;
            border-radius: 6px;
            display: none;
        }

        .mensaje.show {
            display: block;
        }

        .mensaje.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .mensaje.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .fecha-pago {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>💰 Gestión de Cuotas Hipotecarias</h1>
            <p>Banco Entre Ríos | Control de estado y conversión a pesos</p>
        </header>

        <div class="controls">
            <div class="control-group">
                <label for="valor_uva">Valor UVA (ARS):</label>
                <input type="number" id="valor_uva" placeholder="Ej: 1025.50" step="0.01" min="0">
                <button onclick="actualizarValorUVA()">Actualizar UVA</button>
                <small style="color: #666; margin-left: 10px;">
                    Ver valor en: <a href="https://ikiwi.net.ar/calculadoras/uva-a-pesos/" target="_blank" style="color: #667eea;">ikiwi.net.ar</a>
                </small>
            </div>
        </div>

        <div class="stats" id="stats">
            <div class="stat">
                <div class="stat-label">Cuotas Pagadas</div>
                <div class="stat-value pagadas" id="stat_pagadas">-</div>
            </div>
            <div class="stat">
                <div class="stat-label">Cuotas Impagas</div>
                <div class="stat-value impagas" id="stat_impagas">-</div>
            </div>
            <div class="stat">
                <div class="stat-label">Total Pagado</div>
                <div class="stat-value pesos" id="stat_pagado">$-</div>
            </div>
            <div class="stat">
                <div class="stat-label">Total Pendiente</div>
                <div class="stat-value pesos" id="stat_pendiente">$-</div>
            </div>
            <div class="stat">
                <div class="stat-label">Valor UVA Actual</div>
                <div class="stat-value pesos" id="stat_uva">$-</div>
            </div>
        </div>

        <div class="mensaje" id="mensaje"></div>

        <div class="loader" id="loader">
            <div class="loading-spinner"></div>
            <p>Cargando datos...</p>
        </div>

        <div class="table-wrapper" id="tableContainer" style="display: none;">
            <table>
                <thead>
                    <tr>
                        <th class="nro-cuota">Nro</th>
                        <th>Estado</th>
                        <th>Fecha Vto</th>
                        <th class="numero">Capital (UVA)</th>
                        <th class="numero">Interés (UVA)</th>
                        <th class="numero">Total (UVA)</th>
                        <th class="numero">Total ($)</th>
                        <th>Fecha Pago</th>
                    </tr>
                </thead>
                <tbody id="cuotasTable">
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Cargar datos al inicio
        window.addEventListener('DOMContentLoaded', cargarDatos);

        function cargarDatos() {
            mostrarLoader(true);

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=obtener_datos'
            })
            .then(response => response.json())
            .then(data => {
                mostrarLoader(false);

                if (data.success) {
                    renderizarTabla(data.cuotas);
                    actualizarEstadisticas(data.stats);
                    document.getElementById('tableContainer').style.display = 'block';
                } else {
                    mostrarMensaje('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                mostrarLoader(false);
                mostrarMensaje('Error al conectar: ' + error, 'error');
            });
        }

        function renderizarTabla(cuotas) {
            const tbody = document.getElementById('cuotasTable');
            tbody.innerHTML = '';

            cuotas.forEach(cuota => {
                const row = document.createElement('tr');

                const estilo = cuota.estado === 'PAGADA' ? 'pagada' : 'impaga';
                const checked = cuota.estado === 'PAGADA' ? 'checked' : '';
                const fechaPago = cuota.fecha_pago ? cuota.fecha_pago : '-';

                row.innerHTML = `
                    <td class="nro-cuota">${cuota.nro_cuota}</td>
                    <td class="checkbox-cell">
                        <label style="display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" class="checkbox" ${checked}
                                onchange="cambiarEstado(${cuota.nro_cuota}, this.checked)">
                            <span class="estado-badge ${estilo}">
                                ${cuota.estado === 'PAGADA' ? '✓ Pagada' : 'Impaga'}
                            </span>
                        </label>
                    </td>
                    <td>${formatoFecha(cuota.fecha_vencimiento)}</td>
                    <td class="numero">${parseFloat(cuota.capital).toFixed(2)}</td>
                    <td class="numero">${parseFloat(cuota.interes).toFixed(2)}</td>
                    <td class="numero">${parseFloat(cuota.total_uva).toFixed(2)}</td>
                    <td class="numero" style="color: #3498db; font-weight: bold;">
                        ${cuota.total_pesos ? '$' + parseFloat(cuota.total_pesos).toLocaleString('es-AR', {minimumFractionDigits: 2}) : '$0.00'}
                    </td>
                    <td class="fecha-pago">${fechaPago}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function cambiarEstado(nroCuota, estaPagada) {
            const nuevoEstado = estaPagada ? 'PAGADA' : 'IMPAGA';

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=actualizar_estado&nro_cuota=${nroCuota}&estado=${nuevoEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    cargarDatos(); // Recargar para ver cambios
                } else {
                    mostrarMensaje('Error: ' + data.message, 'error');
                }
            })
            .catch(error => mostrarMensaje('Error: ' + error, 'error'));
        }

        function actualizarValorUVA() {
            const valor = document.getElementById('valor_uva').value;

            if (!valor || valor <= 0) {
                mostrarMensaje('Por favor ingresa un valor válido para UVA', 'error');
                return;
            }

            fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=actualizar_valor_uva&valor_uva=${valor}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    cargarDatos(); // Recargar para ver nuevos totales en pesos
                } else {
                    mostrarMensaje('Error: ' + data.message, 'error');
                }
            })
            .catch(error => mostrarMensaje('Error: ' + error, 'error'));
        }

        function actualizarEstadisticas(stats) {
            document.getElementById('stat_pagadas').textContent = stats.pagadas || 0;
            document.getElementById('stat_impagas').textContent = stats.impagas || 0;
            document.getElementById('stat_pagado').textContent = '$' + (stats.pagado_pesos ? parseFloat(stats.pagado_pesos).toLocaleString('es-AR', {minimumFractionDigits: 2}) : '0.00');
            document.getElementById('stat_pendiente').textContent = '$' + (stats.pendiente_pesos ? parseFloat(stats.pendiente_pesos).toLocaleString('es-AR', {minimumFractionDigits: 2}) : '0.00');
            document.getElementById('stat_uva').textContent = '$' + (stats.valor_uva_actual ? parseFloat(stats.valor_uva_actual).toFixed(2) : '-');

            if (stats.valor_uva_actual) {
                document.getElementById('valor_uva').value = stats.valor_uva_actual;
            }
        }

        function formatoFecha(fecha) {
            const [año, mes, día] = fecha.split('-');
            return `${día}/${mes}/${año}`;
        }

        function mostrarMensaje(texto, tipo) {
            const msg = document.getElementById('mensaje');
            msg.textContent = texto;
            msg.className = 'mensaje show ' + tipo;
            setTimeout(() => msg.classList.remove('show'), 5000);
        }

        function mostrarLoader(show) {
            document.getElementById('loader').classList.toggle('show', show);
        }

        // Recargar datos cada 30 segundos para ver cambios en tiempo real
        setInterval(cargarDatos, 30000);
    </script>
</body>
</html>
