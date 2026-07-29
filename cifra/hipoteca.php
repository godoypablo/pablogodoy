<?php
require_once 'config/auth_check.php';
require_auth_or_redirect();
define('APP_VERSION', '20260721-1');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cifra — Cuotas Hipotecarias</title>
    <script>
        const t = localStorage.getItem('cifra-theme');
        if (t) document.documentElement.setAttribute('data-bs-theme', t);
    </script>

    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#1e1b4b">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>

        .table-wrapper {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        table {
            margin-bottom: 0;
        }

        thead {
            background: var(--bs-secondary-bg);
        }

        th {
            font-size: 0.85rem;
            font-weight: 600;
            padding: 1rem 0.75rem;
            color: var(--bs-body-color);
        }

        td {
            padding: 0.75rem;
            font-size: 0.95rem;
            vertical-align: middle;
        }

        tr:hover {
            background: var(--bs-secondary-bg);
        }

        .badge-pagada {
            background-color: #10b981;
            color: white;
        }

        .badge-impaga {
            background-color: #ef4444;
            color: white;
        }

        .loader {
            display: none;
        }

        .loader.show {
            display: flex;
        }

        .mensaje {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .numero {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .nro-cuota {
            text-align: center;
            font-weight: 600;
            width: 50px;
        }

        input[type="checkbox"] {
            width: 1.25rem;
            height: 1.25rem;
            cursor: pointer;
        }

        .valor-uva-cell {
            cursor: pointer;
            position: relative;
        }

        .valor-uva-display {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background 0.2s;
            display: block;
        }

        .valor-uva-display:hover {
            background: var(--bs-secondary-bg);
        }

        .valor-uva-input {
            width: 100%;
            padding: 0.35rem 0.5rem;
            border: 1px solid #0d6efd;
            border-radius: 0.25rem;
            font-size: 0.95rem;
            font-family: 'Courier New', monospace;
        }

        .valor-uva-input:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .cuota-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .cuota-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            gap: 0.5rem;
        }

        .cuota-card-nro {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--bs-body-color);
        }

        .cuota-card-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            padding: 0.35rem 0;
            border-bottom: 1px solid var(--bs-border-color);
        }

        .cuota-card-row:last-child {
            border-bottom: none;
        }

        .cuota-card-label {
            color: var(--bs-secondary);
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 500;
        }

        .cuota-card-value {
            font-weight: 500;
            font-family: 'Courier New', monospace;
            text-align: right;
        }

        .cuota-card-valor-uva {
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            transition: background 0.2s;
        }

        .cuota-card-valor-uva:hover {
            background: var(--bs-secondary-bg);
        }

        .cuota-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid var(--bs-border-color);
        }

        .cuota-card-check {
            margin: 0;
        }

        .cuota-card-total-pesos {
            font-weight: 600;
            color: var(--bs-primary);
            font-size: 1.1rem;
            text-align: right;
        }

        .progress-cuotas {
            background: var(--bs-body-bg);
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .progress-bar-custom {
            display: flex;
            height: 1.5rem;
            border-radius: 0.5rem;
            overflow: hidden;
            background-color: #e5e7eb;
        }

        .progress-segment {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .progress-segment.pagadas-segment {
            background-color: #10b981;
        }

        .progress-segment.impagas-segment {
            background-color: #ef4444;
        }

        .progress-stats-row {
            display: flex;
            gap: 2rem;
            margin-top: 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .progress-stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--bs-body-color);
        }

        .stat-dot {
            display: inline-block;
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
        }

        .stat-dot.pagadas {
            background-color: #10b981;
        }

        .stat-dot.impagas {
            background-color: #ef4444;
        }

        @media (max-width: 767.98px) {
            th {
                font-size: 0.75rem;
                padding: 0.5rem 0.35rem;
            }

            td {
                padding: 0.5rem 0.35rem;
                font-size: 0.85rem;
            }

            .nro-cuota {
                width: 35px;
            }

            .table-wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .progress-stats-row {
                font-size: 0.8rem;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header bg-primary text-white shadow-sm sticky-top">
        <div class="container py-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="text-white text-decoration-none" title="Volver a Cifra">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <h1 class="h5 mb-0">
                    <i class="bi bi-building me-2"></i>
                    Cuotas Hipotecarias
                </h1>
            </div>
            <div class="small text-white-50">Banco Entre Ríos</div>
        </div>
    </header>

    <main class="container py-4">
        <!-- Barra de Progreso de Cuotas -->
        <div class="progress-cuotas mb-4">
            <div class="progress-bar-custom">
                <div id="progressPagadas" class="progress-segment pagadas-segment" style="width: 0%;">
                </div>
                <div id="progressImpagas" class="progress-segment impagas-segment" style="width: 0%;">
                </div>
            </div>
            <div class="progress-stats-row">
                <span class="progress-stat-item">
                    <span class="stat-dot pagadas"></span>
                    <span id="textPagadas">0 Pagadas</span>
                </span>
                <span class="progress-stat-item">
                    <span class="stat-dot impagas"></span>
                    <span id="textImpagas">0 Impagas</span>
                </span>
            </div>
        </div>

        <!-- Tabla de Cuotas (Desktop) -->
        <div class="card shadow-sm d-none d-md-block">
            <div class="card-header bg-secondary">
                <h6 class="mb-0">Detalle de Cuotas</h6>
            </div>
            <div class="loader show" style="justify-content: center; align-items: center; min-height: 200px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div class="table-wrapper" id="tableContainer" style="display: none;">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th class="nro-cuota">Nro</th>
                            <th>Estado</th>
                            <th>Fecha Vto</th>
                            <th class="numero d-none d-md-table-header-cell">Capital (UVA)</th>
                            <th class="numero d-none d-md-table-header-cell">Interés (UVA)</th>
                            <th class="numero">Total (UVA)</th>
                            <th class="numero">Valor UVA ($)</th>
                            <th class="numero">Total ($)</th>
                            <th class="d-none d-md-table-header-cell">Fecha Pago</th>
                        </tr>
                    </thead>
                    <tbody id="cuotasTable">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Cards de Cuotas (Mobile) -->
        <div class="d-md-none">
            <div id="loaderCards" class="loader show" style="justify-content: center; align-items: center; min-height: 200px;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
            <div id="cuotasCardsContainer" style="display: none;">
                <div id="cuotasCards"></div>
            </div>
        </div>
    </main>

    <!-- Mensajes de notificación -->
    <div id="mensaje" class="alert mensaje d-none" role="alert"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', cargarDatos);

        function cargarDatos() {
            mostrarLoader(true);

            fetch('api/hipoteca_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=obtener_datos'
            })
            .then(response => {
                if (response.status === 401) {
                    window.location.href = '/cifra/login.php';
                    return null;
                }
                return response.json();
            })
            .then(data => {
                if (!data) return;
                mostrarLoader(false);

                if (data.success) {
                    renderizarCuotas(data.cuotas);
                    actualizarEstadisticas(data.stats);
                    document.getElementById('tableContainer').style.display = 'block';
                    document.getElementById('cuotasCardsContainer').style.display = 'block';
                    document.getElementById('loaderCards').style.display = 'none';
                } else {
                    mostrarMensaje('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                mostrarLoader(false);
                mostrarMensaje('Error al conectar: ' + error, 'error');
            });
        }

        function renderizarCuotas(cuotas) {
            renderizarTabla(cuotas);
            renderizarCards(cuotas);
        }

        function renderizarTabla(cuotas) {
            const tbody = document.getElementById('cuotasTable');
            tbody.innerHTML = '';

            cuotas.forEach(cuota => {
                const row = document.createElement('tr');
                const estilo = cuota.estado === 'PAGADA' ? 'badge-pagada' : 'badge-impaga';
                const checked = cuota.estado === 'PAGADA' ? 'checked' : '';
                const fechaPago = cuota.fecha_pago ? cuota.fecha_pago : '-';

                row.innerHTML = `
                    <td class="nro-cuota">${cuota.nro_cuota}</td>
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" ${checked}
                                onchange="cambiarEstado(${cuota.nro_cuota}, this.checked)">
                            <span class="badge ${estilo}">
                                ${cuota.estado}
                            </span>
                        </div>
                    </td>
                    <td>${formatoFecha(cuota.fecha_vencimiento)}</td>
                    <td class="numero d-none d-md-table-cell">${parseFloat(cuota.capital).toFixed(2)}</td>
                    <td class="numero d-none d-md-table-cell">${parseFloat(cuota.interes).toFixed(2)}</td>
                    <td class="numero fw-600">${parseFloat(cuota.total_uva).toFixed(2)}</td>
                    <td class="valor-uva-cell numero">
                        <span class="valor-uva-display" onclick="editarValorUVA(${cuota.nro_cuota}, this)">
                            ${cuota.valor_uva ? '$' + parseFloat(cuota.valor_uva).toFixed(2) : '-'}
                        </span>
                    </td>
                    <td class="numero fw-600 text-primary">
                        ${cuota.total_pesos ? '$' + parseFloat(cuota.total_pesos).toLocaleString('es-AR', {minimumFractionDigits: 2}) : '$0.00'}
                    </td>
                    <td class="small text-muted d-none d-md-table-cell">${fechaPago}</td>
                `;
                tbody.appendChild(row);
            });
        }

        function renderizarCards(cuotas) {
            const container = document.getElementById('cuotasCards');
            container.innerHTML = '';

            cuotas.forEach(cuota => {
                const card = document.createElement('div');
                const estilo = cuota.estado === 'PAGADA' ? 'badge-pagada' : 'badge-impaga';
                const checked = cuota.estado === 'PAGADA' ? 'checked' : '';
                const fechaPago = cuota.fecha_pago ? cuota.fecha_pago : '-';

                card.className = 'cuota-card';
                card.innerHTML = `
                    <div class="cuota-card-header">
                        <div class="cuota-card-nro">Cuota ${cuota.nro_cuota}</div>
                        <span class="badge ${estilo}">${cuota.estado}</span>
                    </div>

                    <div class="cuota-card-row">
                        <span class="cuota-card-label">Fecha Vto</span>
                        <span class="cuota-card-value">${formatoFecha(cuota.fecha_vencimiento)}</span>
                    </div>

                    <div class="cuota-card-row">
                        <span class="cuota-card-label">Total (UVA)</span>
                        <span class="cuota-card-value">${parseFloat(cuota.total_uva).toFixed(2)}</span>
                    </div>

                    <div class="cuota-card-row">
                        <span class="cuota-card-label">Valor UVA</span>
                        <span class="cuota-card-valor-uva" onclick="editarValorUVA(${cuota.nro_cuota}, this)">
                            ${cuota.valor_uva ? '$' + parseFloat(cuota.valor_uva).toFixed(2) : '-'}
                        </span>
                    </div>

                    <div class="cuota-card-footer">
                        <div class="form-check cuota-card-check">
                            <input type="checkbox" class="form-check-input" ${checked}
                                onchange="cambiarEstado(${cuota.nro_cuota}, this.checked)">
                            <label class="form-check-label" style="font-size: 0.85rem;">Pagar</label>
                        </div>
                        <div class="cuota-card-total-pesos">
                            ${cuota.total_pesos ? '$' + parseFloat(cuota.total_pesos).toLocaleString('es-AR', {minimumFractionDigits: 2}) : '$0.00'}
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function cambiarEstado(nroCuota, estaPagada) {
            const nuevoEstado = estaPagada ? 'PAGADA' : 'IMPAGA';

            fetch('api/hipoteca_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=actualizar_estado&nro_cuota=${nroCuota}&estado=${nuevoEstado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarMensaje('Cuota ' + nroCuota + ' actualizada', 'success');
                    cargarDatos();
                } else {
                    mostrarMensaje('Error: ' + data.message, 'error');
                }
            })
            .catch(error => mostrarMensaje('Error: ' + error, 'error'));
        }

        function editarValorUVA(nroCuota, elemento) {
            const valorActual = elemento.textContent.replace('$', '').trim() || '';
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'valor-uva-input';
            input.value = valorActual;
            input.step = '0.01';
            input.min = '0';

            elemento.replaceWith(input);
            input.focus();
            input.select();

            function guardar() {
                const valor = input.value;

                if (!valor || valor <= 0) {
                    mostrarMensaje('Valor inválido', 'error');
                    cargarDatos();
                    return;
                }

                fetch('api/hipoteca_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=actualizar_valor_uva_cuota&nro_cuota=${nroCuota}&valor_uva=${valor}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarMensaje('Valor UVA actualizado', 'success');
                        cargarDatos();
                    } else {
                        mostrarMensaje('Error: ' + data.message, 'error');
                    }
                })
                .catch(error => mostrarMensaje('Error: ' + error, 'error'));
            }

            input.addEventListener('blur', guardar);
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') guardar();
            });
        }

        function actualizarEstadisticas(stats) {
            const pagadas = stats.pagadas || 0;
            const impagas = stats.impagas || 0;
            const total = pagadas + impagas;

            document.getElementById('textPagadas').textContent = pagadas + ' Pagadas';
            document.getElementById('textImpagas').textContent = impagas + ' Impagas';

            if (total > 0) {
                const pctPagadas = (pagadas / total) * 100;
                const pctImpagas = (impagas / total) * 100;

                const progressPagadas = document.getElementById('progressPagadas');
                const progressImpagas = document.getElementById('progressImpagas');

                progressPagadas.style.width = pctPagadas + '%';
                progressPagadas.textContent = pctPagadas.toFixed(0) + '%';

                progressImpagas.style.width = pctImpagas + '%';
                progressImpagas.textContent = pctImpagas.toFixed(0) + '%';
            }
        }

        function formatoFecha(fecha) {
            const [año, mes, día] = fecha.split('-');
            return `${día}/${mes}/${año}`;
        }

        function mostrarMensaje(texto, tipo) {
            const msg = document.getElementById('mensaje');
            msg.textContent = texto;
            msg.className = 'alert mensaje alert-' + (tipo === 'success' ? 'success' : 'danger');
            msg.classList.remove('d-none');
            setTimeout(() => msg.classList.add('d-none'), 4000);
        }

        function mostrarLoader(show) {
            document.querySelector('.loader').style.display = show ? 'flex' : 'none';
        }

        setInterval(cargarDatos, 30000);
    </script>
</body>
</html>
