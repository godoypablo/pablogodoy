/**
 * JavaScript para Sistema de Gastos Personales
 */

// Interceptar 401 en todas las llamadas a la API propia
const _fetch = window.fetch.bind(window);
window.fetch = async function(...args) {
    const response = await _fetch(...args);
    const url = typeof args[0] === 'string' ? args[0] : (args[0]?.url ?? '');
    if (response.status === 401 && url.startsWith('api/')) {
        window.location.href = 'login.php';
    }
    return response;
};

const API_URL = 'api/gastos_api.php';

// Paleta de colores Cifra — misma que login (índigo + esmeralda + complementarios)
const CIFRA_PALETTE = [
    '#6366f1','#4f46e5','#818cf8','#a78bfa',
    '#10b981','#059669','#34d399','#0d9488',
    '#0ea5e9','#0284c7','#7c3aed','#9333ea',
    '#f59e0b','#d97706','#ef4444','#dc2626',
    '#ec4899','#db2777','#6b7280','#374151',
];

function _cifraColorPickerHtml(inputId, currentColor) {
    const cur = (currentColor || '#6b7280').toLowerCase();
    const swatches = CIFRA_PALETTE.map(c =>
        `<button type="button" class="cifra-cp-swatch${c.toLowerCase() === cur ? ' activo' : ''}"
                 style="background:${c}" data-color="${c}"
                 onclick="cifraColorSelect('${inputId}','${c}',event)"></button>`
    ).join('');
    return `<div class="cifra-cp" id="cifra-cp-${inputId}">
        <button type="button" class="cifra-cp-btn" style="background:${cur}"
                onclick="cifraColorToggle('${inputId}',event)"></button>
        <input type="hidden" id="${inputId}" value="${cur}">
        <div class="cifra-cp-swatches d-none">${swatches}</div>
    </div>`;
}

function cifraColorToggle(inputId, e) {
    e.stopPropagation();
    const wrap = document.getElementById('cifra-cp-' + inputId);
    const swatches = wrap.querySelector('.cifra-cp-swatches');
    const isOpen = !swatches.classList.contains('d-none');
    document.querySelectorAll('.cifra-cp-swatches').forEach(s => s.classList.add('d-none'));
    if (!isOpen) swatches.classList.remove('d-none');
}

function cifraColorSelect(inputId, color, e) {
    e.stopPropagation();
    const wrap = document.getElementById('cifra-cp-' + inputId);
    wrap.querySelector('.cifra-cp-btn').style.background = color;
    wrap.querySelector('input[type="hidden"]').value = color;
    wrap.querySelectorAll('.cifra-cp-swatch').forEach(s =>
        s.classList.toggle('activo', s.dataset.color === color));
    wrap.querySelector('.cifra-cp-swatches').classList.add('d-none');
}

// Scroll tracker para chips catNav
let _catScrollHandler  = null;
let _catScrollRafPending = false;
let _catScrollLastCat  = null;

// Estado de la aplicación
const app = {
    mesActual: new Date().getMonth() + 1,
    anioActual: new Date().getFullYear(),
    datos: null,
    guardandoCambios: false,
    dtIngresos: null, // ya no se usa (ingresos en modal unificado)
    dtGastos: null,
    categorias: [],
    categoriasColapsadas: new Set(),
    categoriaFiltrada: null,   // mobile: filtro activo (null = mostrar todo)
    importesOcultos: false,    // privacidad: blur en todos los importes
    cuentas: [],
    tipoCambioUSD: null,  // cotización dólar oficial (cacheada por día)
};

// Cerrar color pickers al hacer clic fuera
document.addEventListener('click', () => {
    document.querySelectorAll('.cifra-cp-swatches').forEach(s => s.classList.add('d-none'));
});

// Inicializar aplicación
document.addEventListener('DOMContentLoaded', () => {
    inicializarSelectores();
    actualizarLabelFiltro();

    // Filtro mes/año: collapse nativo Bootstrap + persistencia localStorage
    const elFiltro = document.getElementById('contenidoFiltroMes');
    const elIconFiltro = document.getElementById('iconFiltroMes');
    elFiltro.addEventListener('show.bs.collapse', () => {
        elIconFiltro.className = 'bi bi-chevron-up';
        localStorage.setItem('cifra-filtro-abierto', '1');
    });
    elFiltro.addEventListener('hide.bs.collapse', () => {
        elIconFiltro.className = 'bi bi-chevron-down';
        localStorage.setItem('cifra-filtro-abierto', '0');
    });
    if (localStorage.getItem('cifra-filtro-abierto') === '1') {
        new bootstrap.Collapse(elFiltro, { toggle: false }).show();
    }

    // CSS vars para sticky: header fija su altura, topbar se pega debajo, catNav debajo de ambos
    const _setStickyOffsets = () => {
        const headerH = document.querySelector('.header')?.offsetHeight || 0;
        const topbarH = document.querySelector('.cifra-topbar')?.offsetHeight || 0;
        document.documentElement.style.setProperty('--header-height',   headerH + 'px');
        document.documentElement.style.setProperty('--sticky-nav-top', (headerH + topbarH) + 'px');
        _initCatScrollTracker();
    };
    _setStickyOffsets();
    window.addEventListener('resize', _setStickyOffsets);

    app.importesOcultos = localStorage.getItem('cifra-oculto') === '1';
    _aplicarOcultarImportes();
    cargarDatos();
    sincronizarIconDarkMode();
    document.getElementById('btnCargar').addEventListener('click', cargarDatos);
    document.getElementById('selectMes').addEventListener('change', cargarDatos);
    document.getElementById('selectAnio').addEventListener('change', cargarDatos);
    document.getElementById('inputBusqueda')?.addEventListener('input', e => aplicarBusqueda(e.target.value));
    document.getElementById('inputBusquedaMov')?.addEventListener('input', e => _renderizarMovimientos(e.target.value));

    // Al abrir modal ingresos, renderizar si los datos ya están cargados
    document.getElementById('modalIngresos').addEventListener('show.bs.modal', () => {
        if (app.datos) renderizarModalIngresos();
    });

    // iOS: no dispara beforeinstallprompt → banner propio
    const isIOS        = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isStandalone = window.navigator.standalone === true
                      || window.matchMedia('(display-mode: standalone)').matches;
    if (isIOS && !isStandalone && !localStorage.getItem('cifra-ios-dismissed')) {
        setTimeout(_mostrarBannerInstalariOS, 2500);
    }
});

// Registrar Service Worker (PWA)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js', { updateViaCache: 'none' })
            .then(reg => {
                // Cuando el nuevo SW toma el control, recargar para servir assets frescos
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    window.location.reload();
                });
                // Forzar chequeo de actualización en cada carga
                reg.update();
            })
            .catch(err => console.warn('Service Worker no registrado:', err));
    });
}

// ── PWA Install prompt (Android Chrome) ─────────────────────
let _deferredInstallPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    _deferredInstallPrompt = e;
    // No mostrar si ya lo cerró en esta sesión
    if (!sessionStorage.getItem('cifra-install-dismissed')) {
        _mostrarBannerInstalar();
    }
});

window.addEventListener('appinstalled', () => {
    _ocultarBannerInstalar();
    _deferredInstallPrompt = null;
});

function _mostrarBannerInstalar() {
    if (document.getElementById('bannerInstalar')) return;
    const banner = document.createElement('div');
    banner.id = 'bannerInstalar';
    banner.className = 'banner-instalar';
    banner.innerHTML = `
        <div class="banner-instalar-inner">
            <i class="bi bi-phone-fill me-2 flex-shrink-0"></i>
            <span class="flex-grow-1">Instalá Cifra como app</span>
            <button class="btn btn-sm btn-light fw-600 me-2" onclick="instalarApp()">Instalar</button>
            <button class="btn btn-sm btn-link text-white p-0 opacity-75" onclick="_cerrarBannerInstalar()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    `;
    document.body.appendChild(banner);
}

function _ocultarBannerInstalar() {
    document.getElementById('bannerInstalar')?.remove();
}

function _cerrarBannerInstalar() {
    _ocultarBannerInstalar();
    sessionStorage.setItem('cifra-install-dismissed', '1');
}

async function instalarApp() {
    if (!_deferredInstallPrompt) return;
    _deferredInstallPrompt.prompt();
    const { outcome } = await _deferredInstallPrompt.userChoice;
    if (outcome === 'accepted') {
        _deferredInstallPrompt = null;
        _ocultarBannerInstalar();
    }
}

// ── Banner iOS (Safari no dispara beforeinstallprompt) ───────
function _mostrarBannerInstalariOS() {
    if (document.getElementById('bannerInstalariOS')) return;
    const banner = document.createElement('div');
    banner.id = 'bannerInstalar';          // mismo id → mismo CSS
    banner.className = 'banner-instalar banner-instalar-ios';
    banner.innerHTML = `
        <div class="banner-instalar-inner" style="flex-direction:column;align-items:flex-start;gap:0.4rem">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-phone-fill flex-shrink-0"></i>
                    <strong>Instalá Cifra como app</strong>
                </div>
                <button class="btn btn-sm btn-link text-white p-0 opacity-75"
                    onclick="_cerrarBannerInstalariOS()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="d-flex align-items-center gap-2" style="font-size:0.83rem;opacity:0.9">
                <span>Tocá</span>
                <i class="bi bi-box-arrow-up" style="font-size:1.15rem"></i>
                <span>y después elegí</span>
                <span style="background:rgba(255,255,255,0.15);padding:0.1rem 0.5rem;border-radius:6px;font-weight:600">
                    Agregar a inicio
                </span>
            </div>
        </div>
    `;
    document.body.appendChild(banner);
}

function _cerrarBannerInstalariOS() {
    document.getElementById('bannerInstalar')?.remove();
    localStorage.setItem('cifra-ios-dismissed', '1');
}

// ============================================================
// Dark mode
// ============================================================
function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.getAttribute('data-bs-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('cifra-theme', newTheme);
    sincronizarIconDarkMode();
    // Re-renderizar elementos con colores de categoría inyectados inline
    renderizarCatNav();
    if (app.dtGastos) app.dtGastos.draw(false);
}

// Ajusta un color HEX oscuro para que sea legible en dark mode (lightness ≥ 60%)
function _ajustarColorParaDark(hex) {
    if (!hex || hex.length < 7) return '#94A3B8';
    const r = parseInt(hex.slice(1, 3), 16) / 255;
    const g = parseInt(hex.slice(3, 5), 16) / 255;
    const b = parseInt(hex.slice(5, 7), 16) / 255;
    const max = Math.max(r, g, b), min = Math.min(r, g, b);
    const l = (max + min) / 2;
    if (l >= 0.40) return hex;  // ya suficientemente claro, sin cambios
    const d = max - min;
    const s = d === 0 ? 0 : (l > 0.5 ? d / (2 - max - min) : d / (max + min));
    let h = 0;
    if (d !== 0) {
        switch (max) {
            case r: h = ((g - b) / d + (g < b ? 6 : 0)) / 6; break;
            case g: h = ((b - r) / d + 2) / 6; break;
            case b: h = ((r - g) / d + 4) / 6; break;
        }
    }
    return `hsl(${Math.round(h * 360)}, ${Math.max(Math.round(s * 100), 28)}%, 65%)`;
}

// Devuelve el color de categoría ajustado según el tema activo
function _colorCategoria(color) {
    const c = color || '#6B7280';
    return document.documentElement.getAttribute('data-bs-theme') === 'dark'
        ? _ajustarColorParaDark(c)
        : c;
}

// Ocultar/mostrar todos los importes en pantalla (modo privacidad)
function toggleImportesOcultos() {
    app.importesOcultos = !app.importesOcultos;
    localStorage.setItem('cifra-oculto', app.importesOcultos ? '1' : '0');
    _aplicarOcultarImportes();
}

function _aplicarOcultarImportes() {
    document.documentElement.classList.toggle('cifra-oculto', app.importesOcultos);
    const icon = document.getElementById('iconOcultarImportes');
    if (icon) icon.className = app.importesOcultos ? 'bi bi-eye-slash' : 'bi bi-eye';
}

function sincronizarIconDarkMode() {
    const icon = document.getElementById('iconDarkMode');
    if (!icon) return;
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}

// ============================================================
// Formato currency para inputs
// ============================================================
function formatearImporteDisplay(valor) {
    const n = parseFloat(valor) || 0;
    if (n === 0) return '';
    return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(n);
}

function parsearImporte(texto) {
    if (!texto && texto !== 0) return 0;
    let limpio = String(texto)
        .replace(/[U$\s]/g, '')     // eliminar U$D, $, espacios
        .trim();

    // Coma es separador decimal, punto es miles
    // Buscar cuál es el separador: el último punto/coma
    const lastComma = limpio.lastIndexOf(',');
    const lastDot = limpio.lastIndexOf('.');

    let resultado;
    if (lastComma > lastDot) {
        // La coma es el separador decimal
        resultado = limpio.replace(/\./g, '').replace(',', '.');
    } else if (lastDot > lastComma) {
        // El punto es el separador decimal (user already used . as decimal)
        resultado = limpio.replace(/,/g, '');
    } else {
        // Sin separadores o solo coma
        resultado = limpio.replace(/\./g, '').replace(',', '.');
    }

    return parseFloat(resultado) || 0;
}

// Inicializar selectores de mes y año
function inicializarSelectores() {
    const selectMes = document.getElementById('selectMes');
    const selectAnio = document.getElementById('selectAnio');

    // Configurar mes actual
    selectMes.value = app.mesActual;

    // Configurar año actual
    selectAnio.value = app.anioActual;

    // Generar opciones de años (5 años atrás y 2 hacia adelante)
    const anioMin = app.anioActual - 5;
    const anioMax = app.anioActual + 2;

    selectAnio.innerHTML = '';
    for (let anio = anioMax; anio >= anioMin; anio--) {
        const option = document.createElement('option');
        option.value = anio;
        option.textContent = anio;
        if (anio === app.anioActual) {
            option.selected = true;
        }
        selectAnio.appendChild(option);
    }
}

// Cargar datos desde la API
async function cargarDatos() {
    const selectMes = document.getElementById('selectMes');
    const selectAnio = document.getElementById('selectAnio');

    app.mesActual = parseInt(selectMes.value);
    app.anioActual = parseInt(selectAnio.value);

    // Limpiar búsqueda al cambiar de mes
    const inputB = document.getElementById('inputBusqueda');
    if (inputB) inputB.value = '';
    app.busqueda = null;

    mostrarLoading();

    try {
        const [response, respCuentas, respTarjetas] = await Promise.all([
            fetch(`${API_URL}?mes=${app.mesActual}&anio=${app.anioActual}`),
            fetch(`api/cuentas_api.php?mes=${app.mesActual}&anio=${app.anioActual}`),
            fetch(`${TARJETAS_API}`)
        ]);
        const result     = await response.json();
        const resCuentas = await respCuentas.json();
        const resTarjetas = await respTarjetas.json();

        if (result.success) {
            app.datos = result.data;
            if (resCuentas.success) app.cuentas = resCuentas.data;
            if (resTarjetas.success) tarjetasActuales = resTarjetas.data || [];

            // Inicializar categoría filtrada: recuperar de localStorage o usar "Gastos Varios"
            const catGuardada = localStorage.getItem('cifra-categoria-filtrada');
            if (catGuardada) {
                app.categoriaFiltrada = catGuardada;
            } else {
                // Buscar "Gastos Varios" por defecto
                const gastosVarios = app.datos.conceptos?.find(c => c.tipo === 'gasto' && c.categoria_nombre === 'Gastos Varios');
                app.categoriaFiltrada = gastosVarios?.categoria_id?.toString() || null;
            }

            renderizarDatos();
            cargarDashboardTarjetas();
        } else {
            mostrarError('Error al cargar los datos: ' + result.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    } finally {
        ocultarLoading();
    }

}

// Renderizar datos en la interfaz
function renderizarDatos() {
    if (!app.datos) return;

    const resARS = app.datos.resumen?.ARS || { total_ingresos: 0, ingresos_cobrados: 0, total_gastos: 0, gastos_pagados: 0 };
    const resUSD = app.datos.resumen?.USD || { total_ingresos: 0, ingresos_cobrados: 0, total_gastos: 0, gastos_pagados: 0 };

    // Saldo Real: suma de saldo_actual de todas las cuentas (dinero que realmente tenés)
    const saldoRealARS = app.cuentas.filter(c => (c.moneda || 'ARS') === 'ARS').reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);
    const saldoRealUSD = app.cuentas.filter(c => c.moneda === 'USD').reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);

    // Gastos Pendientes: total_gastos - gastos_pagados del mes (deuda separada)
    const gastosPendientesARS = resARS.total_gastos - resARS.gastos_pagados;
    const gastosPendientesUSD = resUSD.total_gastos - resUSD.gastos_pagados;

    // Disponible = Saldo Real (sin mezclar con gastos pendientes)
    const disponibleARS = saldoRealARS;
    const disponibleUSD = saldoRealUSD;

    // Topbar ARS: Disponible
    const elSFH = document.getElementById('saldoFiltroHeader');
    if (elSFH) {
        elSFH.textContent = formatearMoneda(disponibleARS);
        elSFH.style.color = disponibleARS >= 0 ? 'var(--cifra-pos)' : 'var(--cifra-neg)';
    }
    // Topbar USD — solo visible si hay ingresos o gastos USD en el mes
    const hayUSD = resUSD.total_ingresos > 0 || resUSD.total_gastos > 0;
    const elStatUSD = document.getElementById('statUSD');
    if (elStatUSD) elStatUSD.classList.toggle('d-none', !hayUSD);
    const elUSDH = document.getElementById('saldoUSDHeader');
    if (elUSDH) {
        elUSDH.textContent = formatearMoneda(disponibleUSD, 'USD');
        elUSDH.style.color = disponibleUSD >= 0 ? 'var(--cifra-pos)' : 'var(--cifra-neg)';
    }

    const elTGH = document.getElementById('totalGastosHeader');
    if (elTGH) elTGH.textContent = formatearMoneda(resARS.total_gastos);
    const elPPH = document.getElementById('gastosPorPagarHeader');
    if (elPPH) elPPH.textContent = formatearMoneda(gastosPendientesARS);

    // Destruir DataTable gastos antes de limpiar tbody
    if (app.dtGastos) { app.dtGastos.destroy(); app.dtGastos = null; }

    // Renderizar gastos — ordenado: múltiples → importe > 0 → importe = 0
    let gastos = app.datos.conceptos.filter(c => c.tipo === 'gasto');
    gastos.sort((a, b) => {
        // Primero: permite múltiples
        if (a.permite_multiples !== b.permite_multiples) {
            return (b.permite_multiples ? 1 : 0) - (a.permite_multiples ? 1 : 0);
        }
        // Luego: importe descendente (mayor a menor)
        if (Math.abs(a.importe - b.importe) > 0.001) {
            return b.importe - a.importe;
        }
        // Finalmente: por nombre alfabético
        return (a.nombre || '').localeCompare(b.nombre || '');
    });

    const tbodyGastos = document.getElementById('tablaGastos');
    tbodyGastos.innerHTML = '';

    // Eliminar contenedor anterior si existe
    const oldContainer = document.getElementById('conceptos-cards-container');
    if (oldContainer) oldContainer.remove();

    const cardsContainer = document.createElement('div');
    cardsContainer.id = 'conceptos-cards-container';
    cardsContainer.className = 'conceptos-cards-container';

    gastos.forEach(concepto => {
        if (concepto.permite_multiples == 1) {
            // Cards renderizadas como divs separados, no en la tabla
            const card = crearCardConcepto(concepto, 'gasto');
            cardsContainer.appendChild(card);
        } else {
            // Filas simples en DataTables
            const filas = crearFilasConcepto(concepto, 'gasto');
            filas.forEach(fila => tbodyGastos.appendChild(fila));
        }
    });

    // Insertar cards después de la tabla
    const tabla = document.getElementById('dtGastos');
    tabla.parentElement.insertBefore(cardsContainer, tabla.nextSibling);

    // Actualizar título del mes
    document.getElementById('mesAnioActual').textContent =
        `${String(app.mesActual).padStart(2, '0')}/${app.anioActual}`;
    actualizarLabelFiltro();

    inicializarDataTables();
    renderizarCatNav();
    aplicarFiltroCategoria();
    mostrarBannerPeriodo();
    mostrarBannerVencimientos();
    renderizarCuentas();
    renderizarCardCuentasHome();

    // Si el modal de ingresos está abierto, re-renderizarlo con datos frescos
    if (document.getElementById('modalIngresos')?.classList.contains('show')) {
        renderizarModalIngresos();
    }
    if (document.getElementById('modalResumen')?.classList.contains('show')) {
        renderizarGerencial();
    }
}

function mostrarBannerVencimientos() {
    const modalBody = document.getElementById('modalVencimientosBody');
    const badgeMenu = document.getElementById('badgeMenuVenc');
    const badgeVenc = document.getElementById('badgeVencMenu');

    // Reset badges
    badgeMenu.classList.add('d-none');
    badgeVenc.classList.add('d-none');
    modalBody.innerHTML = '<p class="text-center text-muted py-3">Sin vencimientos próximos</p>';

    if (!app.datos?.conceptos) return;

    const hoy     = new Date().toISOString().split('T')[0];
    const en7dias = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

    const candidatos = app.datos.conceptos.filter(c =>
        c.tipo === 'gasto' && c.fecha_vencimiento && c.pagado !== 1
    );
    const fechas = candidatos.map(c => ({ ...c, fv: c.fecha_vencimiento.split('T')[0] }));

    const vencidos = fechas.filter(c => c.fv < hoy);
    const proximos = fechas.filter(c => c.fv >= hoy && c.fv <= en7dias);

    if (vencidos.length === 0 && proximos.length === 0) return;

    // Actualizar badges
    const totalBadge = vencidos.length + proximos.length;
    badgeMenu.textContent = totalBadge;
    badgeMenu.classList.remove('d-none');
    badgeVenc.textContent = totalBadge;
    badgeVenc.classList.remove('d-none');

    const totalVencidos = vencidos.reduce((s, c) => s + (parseFloat(c.importe) || 0), 0);
    const totalProximos = proximos.reduce((s, c) => s + (parseFloat(c.importe) || 0), 0);

    const filaItem = (c, esVencido) => `
        <div class="venc-fila">
            <span class="venc-nombre">${c.nombre}</span>
            <span class="venc-fecha ${esVencido ? 'venc-fecha-vencido' : ''}">${esVencido ? 'vencido' : formatearFechaCorta(c.fv)}</span>
            <span class="venc-importe">${formatearMoneda(parseFloat(c.importe) || 0, c.moneda || 'ARS')}</span>
        </div>`;

    modalBody.innerHTML = `
        ${vencidos.length ? `
        <div class="venc-grupo">
            <div class="venc-grupo-label venc-grupo-label-vencido">Vencidos · ${formatearMoneda(totalVencidos)}</div>
            ${vencidos.map(c => filaItem(c, true)).join('')}
        </div>` : ''}
        ${proximos.length ? `
        <div class="venc-grupo">
            <div class="venc-grupo-label">Esta semana · ${formatearMoneda(totalProximos)}</div>
            ${proximos.map(c => filaItem(c, false)).join('')}
        </div>` : ''}`;
}

function mostrarBannerPeriodo() {
    // Limpiar banner anterior si existía
    document.getElementById('bannerPeriodo')?.remove();

    if (app.datos.periodo_existe) return;

    // Mes anterior (maneja enero → diciembre del año anterior)
    let prevMes = app.mesActual - 1;
    let prevAnio = app.anioActual;
    if (prevMes < 1) { prevMes = 12; prevAnio--; }

    const nombreActual = `${obtenerNombreMes(app.mesActual)} ${app.anioActual}`;
    const nombrePrev   = `${obtenerNombreMes(prevMes)} ${prevAnio}`;

    const banner = document.createElement('div');
    banner.id = 'bannerPeriodo';
    banner.className = 'alert alert-info d-flex align-items-center gap-3 mb-4';
    banner.innerHTML = `
        <i class="bi bi-calendar-plus flex-shrink-0 fs-5"></i>
        <div class="flex-grow-1">
            <strong>No hay datos para ${nombreActual}.</strong>
            Podés empezar en blanco o copiar los importes del mes anterior.
        </div>
        <div class="d-flex gap-2 flex-shrink-0">
            <button class="btn btn-outline-secondary btn-sm" onclick="this.closest('#bannerPeriodo').remove()">
                <i class="bi bi-pencil me-1"></i>Empezar en blanco
            </button>
            <button class="btn btn-info btn-sm text-white" onclick="copiarPeriodoAnterior(${prevMes}, ${prevAnio})">
                <i class="bi bi-copy me-1"></i>Copiar de ${nombrePrev}
            </button>
        </div>
    `;

    // Insertar debajo del alertContainer
    const ref = document.getElementById('alertContainer');
    ref.parentNode.insertBefore(banner, ref.nextSibling);
}

async function copiarPeriodoAnterior(fromMes, fromAnio) {
    const nombreFrom   = `${obtenerNombreMes(fromMes)} ${fromAnio}`;
    const nombreTo     = `${obtenerNombreMes(app.mesActual)} ${app.anioActual}`;

    if (!confirm(
        `¿Copiar los importes de ${nombreFrom} a ${nombreTo}?\n\n` +
        `Solo se copian conceptos de entrada única. Podés editar los valores después.`
    )) return;

    try {
        const response = await fetch(API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'copiar_periodo',
                from_mes:  fromMes,
                from_anio: fromAnio,
                to_mes:    app.mesActual,
                to_anio:   app.anioActual
            })
        });
        const result = await response.json();
        if (result.success) {
            mostrarToast(`Copiados ${result.data.copiados} registros desde ${nombreFrom}`, 'success');
            await cargarDatos();
        } else {
            mostrarError('Error al copiar: ' + result.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    }
}

function inicializarDataTables() {
    app.dtGastos = $('#dtGastos').DataTable({
        paging:   false,
        info:     false,
        ordering: false,
        dom:      't',
        autoWidth: false,
        language: { emptyTable: 'Sin datos para este período' },
        drawCallback: function() {
            inyectarCabecerasCategorias(this.api());
            _initCatScrollTracker();
        }
    });
}

// Inyectar filas de cabecera de categoría en el tbody después de cada draw de DataTables
function inyectarCabecerasCategorias(api) {
    // Calcular totales por categoría y moneda desde los datos en memoria
    const totalesPorCat = {};
    if (app.datos && app.datos.conceptos) {
        app.datos.conceptos.forEach(c => {
            if (c.categoria_id) {
                if (!totalesPorCat[c.categoria_id]) totalesPorCat[c.categoria_id] = { ARS: 0, USD: 0 };
                const m = c.moneda || 'ARS';
                totalesPorCat[c.categoria_id][m] += parseFloat(c.importe || 0);
            }
        });
    }

    let lastCatId = null;
    api.rows().every(function() {
        const tr = this.node();
        if (!tr || tr.classList.contains('categoria-header')) return;

        const catId    = tr.dataset.categoriaId    || '';
        const nombre   = tr.dataset.categoriaNombre || '';
        const color    = tr.dataset.categoriaColor  || '';
        const icono    = tr.dataset.categoriaIcono  || '';

        if (!catId || catId === lastCatId) return;
        lastCatId = catId;

        const colapsada = app.categoriasColapsadas.has(catId);
        const t = totalesPorCat[catId] || { ARS: 0, USD: 0 };
        const arsStr = t.ARS > 0 ? formatearMoneda(t.ARS) : (t.USD === 0 ? '—' : '');
        const usdStr = t.USD > 0 ? `<small class="d-block" style="font-size:0.65rem;opacity:0.8">${formatearMoneda(t.USD, 'USD')}</small>` : '';
        const totalStr = arsStr + usdStr || '—';

        const headerTr = document.createElement('tr');
        headerTr.className = 'categoria-header';
        headerTr.dataset.catToggleId = catId;
        const td = document.createElement('td');
        td.colSpan = 2;
        const displayColor = _colorCategoria(color);
        td.style.setProperty('--cat-color', displayColor);
        td.innerHTML = `<div class="categoria-header-inner">
            <i class="bi ${colapsada ? 'bi-chevron-right' : 'bi-chevron-down'} categoria-toggle-chevron" style="color:${displayColor}; font-size:0.65rem;"></i>
            ${icono ? `<i class="bi ${icono}" style="color:${displayColor}; font-size:0.78rem;"></i>` : ''}
            <span class="categoria-header-label" style="color:${displayColor}">${nombre}</span>
            <span class="categoria-header-total ms-auto text-end" style="color:${displayColor}">${totalStr}</span>
        </div>`;
        headerTr.appendChild(td);
        // En desktop: click en header colapsa/expande. En mobile: solo chips filtran.
        headerTr.addEventListener('click', () => { if (window.innerWidth >= 768) toggleCategoria(catId); });
        tr.parentNode.insertBefore(headerTr, tr);
    });

    // Reaplicar estado colapsado a las filas de concepto
    api.rows().every(function() {
        const tr = this.node();
        if (!tr || tr.classList.contains('categoria-header')) return;
        const catId = tr.dataset.categoriaId || '';
        if (catId && app.categoriasColapsadas.has(catId)) {
            tr.classList.add('cat-fila-colapsada');
        } else {
            tr.classList.remove('cat-fila-colapsada');
        }
    });

    // Reaplicar filtro mobile si estaba activo
    aplicarFiltroCategoria();
}

function toggleCategoria(catId) {
    const colapsada = app.categoriasColapsadas.has(catId);
    if (colapsada) {
        app.categoriasColapsadas.delete(catId);
    } else {
        app.categoriasColapsadas.add(catId);
    }
    const ahoraColapsada = !colapsada;

    // Actualizar ícono del encabezado
    const headerTr = document.querySelector(`[data-cat-toggle-id="${catId}"]`);
    if (headerTr) {
        const chevron = headerTr.querySelector('.categoria-toggle-chevron');
        if (chevron) chevron.className = `bi ${ahoraColapsada ? 'bi-chevron-right' : 'bi-chevron-down'} categoria-toggle-chevron`;
    }

    // Mostrar/ocultar filas de conceptos de esa categoría
    document.querySelectorAll(`[data-categoria-id="${catId}"]`).forEach(tr => {
        tr.classList.toggle('cat-fila-colapsada', ahoraColapsada);
        // Si es una fila de concepto múltiple con detalle abierto, cerrarlo
        if (ahoraColapsada && tr.classList.contains('concepto-multiple-header')) {
            const dtInstance = app.dtGastos;
            if (dtInstance) {
                const row = dtInstance.row(tr);
                if (row.child.isShown()) {
                    row.child.hide();
                    const arrow = tr.querySelector('.detalle-arrow');
                    if (arrow) arrow.classList.replace('bi-chevron-up', 'bi-chevron-down');
                }
            }
        }
    });

}

// Nav de categorías — chips horizontales filtro (solo mobile, d-md-none)
function renderizarCatNav() {
    const el = document.getElementById('catNav');
    if (!el || !app.datos) return;

    const cats = {};
    (app.datos.conceptos || []).filter(c => c.tipo === 'gasto').forEach(c => {
        const id = String(c.categoria_id || '0');
        if (!cats[id]) cats[id] = {
            id,
            nombre:          c.categoria_nombre || 'Sin cat.',
            color:           c.categoria_color  || '#6B7280',
            icono:           c.categoria_icono  || 'bi-tag',
            esSinCategoria:  !c.categoria_id,
            totalARS: 0,
            totalUSD: 0,
        };
        if ((c.moneda || 'ARS') === 'USD') cats[id].totalUSD += parseFloat(c.importe || 0);
        else                                cats[id].totalARS  += parseFloat(c.importe || 0);
    });

    // Orden: categorías con nombre primero; "Sin cat." al final
    const sorted = Object.values(cats).sort((a, b) => {
        if (a.esSinCategoria && !b.esSinCategoria) return 1;
        if (!a.esSinCategoria && b.esSinCategoria) return -1;
        return 0;
    });

    const chips = sorted.map(cat => {
        const activa = app.categoriaFiltrada === cat.id;
        const total  = cat.totalARS > 0
            ? formatearMoneda(cat.totalARS)
            : (cat.totalUSD > 0 ? formatearMoneda(cat.totalUSD, 'USD') : '—');
        return `<button class="cat-chip${activa ? ' activa' : ''}"
                    style="--chip-color:${_colorCategoria(cat.color)}"
                    data-cat-id="${cat.id}"
                    onclick="seleccionarCategoria('${cat.id}')">
                    <i class="bi ${cat.icono} cat-chip-icon"></i>
                    <span class="cat-chip-nombre">${cat.nombre}</span>
                    <span class="cat-chip-total">${total}</span>
                </button>`;
    }).join('');

    el.innerHTML = `<div class="cat-nav-scroll">${chips}</div>`;

    // Agregar chip de tarjetas
    renderizarChipTarjetas();
}

// Filtro por chip: seleccionar → mostrar solo esa categoría (sin deseleccionar)
function seleccionarCategoria(catId) {
    app.categoriaFiltrada = catId;
    localStorage.setItem('cifra-categoria-filtrada', catId);
    _limpiarChipEnVista();
    _catScrollLastCat = null;
    renderizarCatNav();
    aplicarFiltroCategoria();
    // Scroll al inicio del contenido si se activó un filtro
    if (app.categoriaFiltrada) {
        setTimeout(() => {
            const contenido = document.getElementById('contenidoPrincipal');
            if (!contenido) return;
            const stickyH = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--sticky-nav-top')
            ) || 0;
            const catNavH = document.getElementById('catNav')?.offsetHeight || 0;
            const top = contenido.getBoundingClientRect().top + window.scrollY - (stickyH + catNavH + 8);
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        }, 60);
    }
}

// Scroll-tracker: resalta el chip de la categoría visible en el tope de pantalla
function _initCatScrollTracker() {
    if (_catScrollHandler) {
        window.removeEventListener('scroll', _catScrollHandler);
        _catScrollHandler = null;
    }

    _catScrollHandler = () => {
        if (_catScrollRafPending) return;
        _catScrollRafPending = true;
        requestAnimationFrame(() => {
            _catScrollRafPending = false;
            if (app.categoriaFiltrada) return;

            const stickyNavTop = parseInt(
                getComputedStyle(document.documentElement).getPropertyValue('--sticky-nav-top')
            ) || 0;
            const catNavH = document.getElementById('catNav')?.offsetHeight || 0;
            const zone = stickyNavTop + catNavH + 4;

            const headers = [...document.querySelectorAll('#tablaGastos .categoria-header')];
            let currentCat = null;
            for (const h of headers) {
                if (h.getBoundingClientRect().top <= zone) currentCat = h.dataset.catToggleId;
                else break;
            }

            if (currentCat !== _catScrollLastCat) {
                _catScrollLastCat = currentCat;
                if (currentCat) _resaltarChipEnVista(currentCat);
                else _limpiarChipEnVista();
            }
        });
    };
    window.addEventListener('scroll', _catScrollHandler, { passive: true });
}

function _resaltarChipEnVista(catId) {
    if (app.categoriaFiltrada) return;
    const nav = document.querySelector('.cat-nav-scroll');
    if (!nav) return;

    let targetChip = null;
    nav.querySelectorAll('.cat-chip').forEach(chip => {
        const isTarget = chip.dataset.catId === catId;
        chip.classList.toggle('en-vista', isTarget);
        if (isTarget) targetChip = chip;
    });

    if (targetChip) {
        const navRect   = nav.getBoundingClientRect();
        const chipRect  = targetChip.getBoundingClientRect();
        const scrollLeft = nav.scrollLeft + (chipRect.left - navRect.left)
                         - (navRect.width / 2) + (chipRect.width / 2);
        nav.scrollTo({ left: Math.max(0, scrollLeft), behavior: 'smooth' });
    }
}

function _limpiarChipEnVista() {
    document.querySelectorAll('.cat-chip.en-vista').forEach(c => c.classList.remove('en-vista'));
}

// Aplicar/quitar filtro de categoría en las filas del tbody y cards
function aplicarFiltroCategoria() {
    const catId = app.categoriaFiltrada;

    // Filtrar filas de tabla
    document.querySelectorAll('#tablaGastos tr[data-categoria-id]').forEach(tr => {
        const trCat = tr.dataset.categoriaId || '';
        tr.classList.toggle('cat-fila-oculta', catId !== null && trCat !== catId);
    });
    document.querySelectorAll('#tablaGastos tr.categoria-header').forEach(tr => {
        tr.classList.toggle('cat-fila-oculta', catId !== null);
    });

    // Filtrar cards
    document.querySelectorAll('.concepto-card[data-categoria-id]').forEach(card => {
        const cardCat = card.dataset.categoriaId || '0';
        card.classList.toggle('cat-fila-oculta', catId !== null && cardCat !== catId);
    });
}

function _normalizar(str) {
    return (str || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function aplicarBusqueda(q) {
    q = _normalizar(q);
    app.busqueda = q;

    const btnClear = document.getElementById('btnLimpiarBusqueda');
    if (btnClear) btnClear.classList.toggle('d-none', !q);

    if (!q) {
        document.querySelectorAll('#tablaGastos tr.cat-fila-busqueda').forEach(tr => tr.classList.remove('cat-fila-busqueda'));
        aplicarFiltroCategoria();
        return;
    }

    // Al buscar, desactivar filtro de categoría
    app.categoriaFiltrada = null;
    renderizarCatNav();

    // Filas de concepto: mostrar solo las que coinciden
    document.querySelectorAll('#tablaGastos tr[data-concepto-nombre]').forEach(tr => {
        const nombre = _normalizar(tr.dataset.conceptoNombre);
        tr.classList.toggle('cat-fila-busqueda', !nombre.includes(q));
        tr.classList.remove('cat-fila-oculta');
    });

    // Headers: mostrar solo si tienen al menos una fila visible
    document.querySelectorAll('#tablaGastos tr.categoria-header').forEach(headerTr => {
        const catId = headerTr.dataset.catToggleId;
        const hayVisible = !!document.querySelector(
            `#tablaGastos tr[data-categoria-id="${catId}"]:not(.cat-fila-busqueda):not(.cat-fila-colapsada)`
        );
        headerTr.classList.toggle('cat-fila-busqueda', !hayVisible);
        headerTr.classList.remove('cat-fila-oculta');
    });
}

function limpiarBusqueda() {
    const input = document.getElementById('inputBusqueda');
    if (input) { input.value = ''; input.focus(); }
    aplicarBusqueda('');
}

// Crear filas de concepto — devuelve array de <tr>
function crearFilasConcepto(concepto, tipo) {
    if (concepto.permite_multiples == 1) {
        return crearFilasMultiple(concepto, tipo);
    }
    return [crearFilaSimple(concepto, tipo)];
}

// Fila para concepto de entrada única (comportamiento original)
function crearFilaSimple(concepto, tipo) {
    const tr = document.createElement('tr');

    tr.dataset.conceptoNombre = concepto.nombre || '';
    if (concepto.categoria_id) {
        tr.dataset.categoriaId     = concepto.categoria_id;
        tr.dataset.categoriaNombre = concepto.categoria_nombre || '';
        tr.dataset.categoriaColor  = concepto.categoria_color  || '';
        tr.dataset.categoriaIcono  = concepto.categoria_icono  || '';
    }

    // Columna nombre
    const tdNombre = document.createElement('td');
    tdNombre.setAttribute('data-order', concepto.nombre);
    const divNombre = document.createElement('div');
    divNombre.className = 'concepto-nombre';

    // Fila vencida: rojo si hay vencimiento, ya pasó y no está pagado
    const hoy = new Date().toISOString().split('T')[0];
    if (concepto.fecha_vencimiento && concepto.fecha_vencimiento < hoy && concepto.pagado !== 1) {
        tr.classList.add('tr-vencido');
    }

    // Botón pagado (gastos) / cobrado (ingresos)
    {
        const isPaid = concepto.pagado === 1;
        const esIngreso = tipo === 'ingreso';
        const btnPagado = document.createElement('button');
        btnPagado.className = `btn-pagado${isPaid ? ' pagado' : ''}`;
        btnPagado.title = isPaid
            ? (esIngreso ? 'Marcar como no cobrado' : 'Marcar como no pagado')
            : (esIngreso ? 'Marcar como cobrado'    : 'Marcar como pagado');
        btnPagado.innerHTML = `<i class="bi ${isPaid ? 'bi-check-circle-fill' : 'bi-circle'}"></i>`;
        btnPagado.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePagado(concepto.registro_id, btnPagado, tr, concepto.id);
        });
        divNombre.appendChild(btnPagado);
        if (isPaid) tr.classList.add('tr-pagado');
    }

    divNombre.appendChild(document.createTextNode(concepto.nombre));

    // Editor fecha de vencimiento: span con formato dd/mm/yy + input oculto
    const wrapVenc = document.createElement('div');
    wrapVenc.className = 'vencimiento-wrap';
    const icoVenc = document.createElement('i');
    icoVenc.className = 'bi bi-calendar-x';
    icoVenc.title = 'Fecha de vencimiento — el registro se moverá al mes correspondiente';
    const spanVenc = document.createElement('span');
    spanVenc.className = 'vencimiento-texto';
    spanVenc.textContent = concepto.fecha_vencimiento
        ? formatearFechaCorta(concepto.fecha_vencimiento)
        : 'vence';
    const inputVenc = document.createElement('input');
    inputVenc.type  = 'date';
    inputVenc.className = 'input-vencimiento';
    inputVenc.value = concepto.fecha_vencimiento ? concepto.fecha_vencimiento.split('T')[0] : '';
    const abrirPickerVenc = e => {
        e.stopPropagation();
        try { inputVenc.showPicker(); } catch(_) { inputVenc.focus(); }
    };
    icoVenc.addEventListener('click', abrirPickerVenc);
    spanVenc.addEventListener('click', abrirPickerVenc);
    inputVenc.addEventListener('change', () => {
        spanVenc.textContent = inputVenc.value ? formatearFechaCorta(inputVenc.value) : 'vence';
        if (concepto.registro_id) {
            guardarVencimiento(concepto.registro_id, inputVenc.value, tr);
        }
    });
    wrapVenc.appendChild(icoVenc);
    wrapVenc.appendChild(spanVenc);
    wrapVenc.appendChild(inputVenc);
    divNombre.appendChild(wrapVenc);

    // Selector de cuenta (solo si ya existe registro)
    if (concepto.registro_id) {
        divNombre.appendChild(crearSelectorCuenta(concepto.registro_id, concepto.cuenta_id, concepto.moneda || 'ARS'));
    }

    tdNombre.appendChild(divNombre);
    tr.appendChild(tdNombre);

    // Columna importe
    const tdImporte = document.createElement('td');
    tdImporte.className = 'text-end';
    tdImporte.setAttribute('data-order', concepto.importe);

    const inputGroup = document.createElement('div');
    inputGroup.className = 'd-flex justify-content-end align-items-center gap-2';

    if (concepto.pagado === 1) {
        // Pagado: mostrar como label estático
        const span = document.createElement('span');
        span.className = 'importe-pagado fw-medium';
        span.textContent = concepto.importe > 0 ? formatearMoneda(concepto.importe, concepto.moneda || 'ARS') : '—';
        inputGroup.appendChild(span);
    } else {
        // No pagado: input editable
        const input = document.createElement('input');
        input.type = 'text';
        input.inputMode = 'decimal';
        input.className = 'input-importe form-control';
        input.value = concepto.importe > 0 ? formatearMoneda(concepto.importe, concepto.moneda || 'ARS') : '';
        input.dataset.conceptoId = concepto.id;
        input.dataset.registroId = concepto.registro_id || '';
        input.placeholder = (concepto.moneda === 'USD') ? 'U$D 0,00' : '$ 0,00';

        input.addEventListener('focus', () => {
            const raw = parsearImporte(input.value);
            input.value = raw > 0 ? String(raw).replace('.', ',') : '';
            input.select();
        });
        input.addEventListener('blur', () => {
            const importe = parsearImporte(input.value);
            input.value = importe > 0 ? formatearMoneda(importe) : '';
            guardarImporte(concepto.id, importe, concepto.registro_id, input);
        });
        input.addEventListener('keypress', (e) => { if (e.key === 'Enter') input.blur(); });
        input.addEventListener('input', () => {
            input.classList.add('unsaved');
            input.classList.remove('saved');
        });

        // Botón SMVM para Cuota Alimentaria
        if (concepto.nombre.toLowerCase().includes('alimentaria')) {
            const btnSmvm = document.createElement('button');
            btnSmvm.className = 'btn btn-smvm';
            btnSmvm.title = 'Sugerir valor del SMVM vigente';
            btnSmvm.innerHTML = '<i class="bi bi-robot"></i>';
            btnSmvm.addEventListener('click', () => sugerirSMVM(input, btnSmvm, app.mesActual, app.anioActual));
            inputGroup.appendChild(btnSmvm);
        }

        // Botón Elena Limpieza
        if (concepto.nombre.toLowerCase().includes('elena')) {
            const btnElena = document.createElement('button');
            btnElena.className = 'btn btn-smvm';
            btnElena.title = 'Sugerir importe empleada doméstica (4h/semana)';
            btnElena.innerHTML = '<i class="bi bi-robot"></i>';
            btnElena.addEventListener('click', () => sugerirElena(input, btnElena, app.mesActual, app.anioActual));
            inputGroup.appendChild(btnElena);
        }

        // Botón Spotify Duo
        if (concepto.nombre.toLowerCase().includes('spotify')) {
            const btnSpotify = document.createElement('button');
            btnSpotify.className = 'btn btn-smvm';
            btnSpotify.title = 'Sugerir precio Spotify Duo para el mes seleccionado';
            btnSpotify.innerHTML = '<i class="bi bi-robot"></i>';
            btnSpotify.addEventListener('click', () => sugerirSpotifyDuo(input, btnSpotify, app.mesActual, app.anioActual));
            inputGroup.appendChild(btnSpotify);
        }

        // Botón YouTube Premium
        if (concepto.nombre.toLowerCase().includes('youtube')) {
            const btnYt = document.createElement('button');
            btnYt.className = 'btn btn-smvm';
            btnYt.title = 'Sugerir precio YouTube Premium al tipo de cambio actual';
            btnYt.innerHTML = '<i class="bi bi-robot"></i>';
            btnYt.addEventListener('click', () => sugerirYoutubePremium(input, btnYt));
            inputGroup.appendChild(btnYt);
        }

        // Botón: repetir importe del mes anterior
        if ((concepto.importe_mes_anterior ?? 0) > 0 && !(concepto.importe > 0)) {
            const btnPrev = document.createElement('button');
            btnPrev.className = 'btn btn-smvm btn-repetir-anterior';
            btnPrev.title = `Repetir mes anterior: ${formatearMoneda(concepto.importe_mes_anterior, concepto.moneda || 'ARS')}`;
            btnPrev.innerHTML = '<i class="bi bi-arrow-repeat"></i>';
            btnPrev.addEventListener('click', () => {
                input.value = formatearMoneda(concepto.importe_mes_anterior, concepto.moneda || 'ARS');
                guardarImporte(concepto.id, concepto.importe_mes_anterior, concepto.registro_id, input);
            });
            inputGroup.appendChild(btnPrev);
        }

        const wrap = document.createElement('div');
        wrap.className = 'importe-input-wrap';
        wrap.appendChild(input);
        inputGroup.appendChild(wrap);
    }

    tdImporte.appendChild(inputGroup);
    tr.appendChild(tdImporte);

    return tr;
}

// Sugerir SMVM desde la API de datos.gob.ar para el mes/año seleccionado
async function sugerirSMVM(inputElement, btnElement, mes, anio) {
    const iconOriginal = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        // Último día del mes seleccionado como límite de la consulta
        const ultimoDia = new Date(anio, mes, 0).getDate();
        const endDate = `${anio}-${String(mes).padStart(2, '0')}-${ultimoDia}`;

        const response = await fetch(
            `https://apis.datos.gob.ar/series/api/series/?ids=57.1_SMVMM_0_M_34&end_date=${endDate}&limit=1&sort=desc`
        );
        if (!response.ok) throw new Error('No se pudo obtener el SMVM');
        const json = await response.json();

        if (!json.data || json.data.length === 0) {
            throw new Error('No hay datos disponibles para el período seleccionado');
        }

        const [fechaISO, valor] = json.data[0];
        const [anioSmvm, mesSmvm] = fechaISO.split('-');
        const fechaFormateada = `${obtenerNombreMes(parseInt(mesSmvm))} ${anioSmvm}`;

        const confirmar = confirm(
            `SMVM vigente en ${obtenerNombreMes(mes)} ${anio}:\n${formatearMoneda(valor)} (ref: ${fechaFormateada})\n\n¿Usar este valor para Cuota Alimentaria?`
        );

        if (confirmar) {
            inputElement.value = valor;
            inputElement.classList.add('unsaved');
            inputElement.focus();
            inputElement.blur();
        }
    } catch (error) {
        mostrarError('No se pudo obtener el SMVM: ' + error.message);
    } finally {
        btnElement.disabled = false;
        btnElement.innerHTML = iconOriginal;
    }
}

// Crear card como div independiente (no en tabla DataTables)
function crearCardConcepto(concepto, tipo) {
    const cantRegistros = concepto.detalle ? concepto.detalle.length : 0;
    const totalImporte = concepto.importe || 0;
    const moneda = concepto.moneda || 'ARS';

    // Card container principal
    const card = document.createElement('div');
    card.className = 'concepto-card';
    card.id = `card-${concepto.id}`;
    card.dataset.conceptoId = concepto.id;
    card.dataset.conceptoNombre = concepto.nombre || '';
    card.dataset.categoriaId = concepto.categoria_id || '0';
    if (concepto.categoria_id) {
        card.dataset.categoriaNombre = concepto.categoria_nombre || '';
        card.dataset.categoriaColor = concepto.categoria_color || '';
        card.dataset.categoriaIcono = concepto.categoria_icono || '';
    }

    // Card header (expandible)
    const cardHeader = document.createElement('div');
    cardHeader.className = 'concepto-card-header';
    cardHeader.addEventListener('click', () => toggleDetalle(concepto.id));

    // Header: chevron + nombre + badge + total
    const headerContent = document.createElement('div');
    headerContent.className = 'concepto-card-header-content';

    const chevron = document.createElement('i');
    chevron.className = 'bi bi-chevron-right concepto-card-chevron';
    chevron.id = `arrow-${concepto.id}`;
    headerContent.appendChild(chevron);

    const nombre = document.createElement('span');
    nombre.className = 'concepto-card-nombre';
    nombre.textContent = concepto.nombre;
    headerContent.appendChild(nombre);

    const badge = document.createElement('span');
    badge.className = 'concepto-card-badge';
    badge.id = `badge-count-${concepto.id}`;
    badge.textContent = cantRegistros;
    headerContent.appendChild(badge);

    const total = document.createElement('span');
    total.className = 'concepto-card-total';
    total.id = `total-concepto-${concepto.id}`;
    total.textContent = formatearMoneda(totalImporte, moneda);
    headerContent.appendChild(total);

    cardHeader.appendChild(headerContent);
    card.appendChild(cardHeader);

    // Card body (detalle — oculto por defecto)
    const cardBody = document.createElement('div');
    cardBody.className = 'concepto-card-body';
    cardBody.id = `card-body-${concepto.id}`;

    // Contenido del detalle
    const detalleContent = crearContenidoDetalle(concepto, moneda);
    cardBody.appendChild(detalleContent);

    card.appendChild(cardBody);

    return card;
}

// Filas para concepto de múltiples entradas: card expandible (mantener para compatibility)
function crearFilasMultiple(concepto, tipo) {
    return []; // Cards se renderizan ahora como divs separados
}

// Contenido de detalle para card expandible (lista de registros)
function crearContenidoDetalle(concepto, moneda) {
    const wrapper = document.createElement('div');
    wrapper.className = 'concepto-card-detalle';

    // Lista de registros
    const lista = document.createElement('div');
    lista.className = 'concepto-detalle-lista';

    if (concepto.detalle && concepto.detalle.length > 0) {
        concepto.detalle.forEach(reg => {
            const itemReg = crearItemRegistroDetalle(reg, concepto.id, moneda);
            lista.appendChild(itemReg);
        });
    } else {
        const vacio = document.createElement('div');
        vacio.className = 'concepto-detalle-vacio';
        vacio.innerHTML = '<p class="text-muted text-center mb-0">Sin registros este mes</p>';
        lista.appendChild(vacio);
    }

    wrapper.appendChild(lista);

    // Formulario para agregar nuevo registro
    const formNuevo = crearFormNuevoRegistroCard(concepto.id, moneda, concepto.cuenta_id_default);
    wrapper.appendChild(formNuevo);

    return wrapper;
}

// Item individual dentro de la lista de detalle
function crearItemRegistroDetalle(reg, conceptoId, moneda) {
    const item = document.createElement('div');
    item.className = 'concepto-detalle-item';
    item.id = `registro-${reg.id}`;

    // Fecha
    const fecha = document.createElement('span');
    fecha.className = 'concepto-detalle-fecha';
    fecha.textContent = formatearFechaCorta(reg.fecha);

    // Descripción
    const desc = document.createElement('span');
    desc.className = 'concepto-detalle-desc';
    desc.textContent = reg.observaciones || '—';

    // Importe editable
    const importeWrapper = document.createElement('div');
    importeWrapper.className = 'concepto-detalle-importe-wrapper';

    const importeDisplay = document.createElement('span');
    importeDisplay.className = 'concepto-detalle-importe';
    importeDisplay.id = `det-importe-${reg.id}`;
    importeDisplay.textContent = formatearMoneda(reg.importe, moneda);
    importeDisplay.title = 'Doble click para editar';
    importeDisplay.style.cursor = 'pointer';

    const importeInput = document.createElement('input');
    importeInput.type = 'text';
    importeInput.className = 'concepto-detalle-importe-input';
    importeInput.value = reg.importe;
    importeInput.style.display = 'none';

    // Toggle entre display e input
    importeDisplay.addEventListener('dblclick', () => {
        importeDisplay.style.display = 'none';
        importeInput.style.display = 'inline';
        importeInput.focus();
        importeInput.select();
    });

    // Guardar al perder focus o Enter
    const guardarImporte = async () => {
        const nuevoImporte = parsearImporte(importeInput.value);
        if (Math.abs(nuevoImporte - reg.importe) > 0.001) {
            try {
                const response = await fetch(API_URL, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ registro_id: reg.id, importe: nuevoImporte })
                });
                const result = await response.json();
                if (result.success) {
                    mostrarToast('Importe actualizado', 'success');
                    await cargarDatos();
                } else {
                    mostrarError('Error: ' + result.message);
                }
            } catch (error) {
                mostrarError('Error de conexión: ' + error.message);
            }
        } else {
            // Sin cambios: solo volver a display
            importeDisplay.style.display = 'inline';
            importeInput.style.display = 'none';
        }
    };

    importeInput.addEventListener('blur', guardarImporte);
    importeInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') guardarImporte();
    });

    importeWrapper.appendChild(importeDisplay);
    importeWrapper.appendChild(importeInput);

    // Botón borrar
    const btnDel = document.createElement('button');
    btnDel.className = 'concepto-detalle-btn-del';
    btnDel.title = 'Eliminar registro';
    btnDel.innerHTML = '<i class="bi bi-trash3"></i>';
    btnDel.addEventListener('click', (e) => {
        e.stopPropagation();
        eliminarRegistroMultiple(reg.id, conceptoId);
    });

    item.appendChild(fecha);
    item.appendChild(desc);
    item.appendChild(importeWrapper);
    item.appendChild(btnDel);

    return item;
}

// Tabla interior con los registros del concepto múltiple (mantener para compatibility)
function crearTablaDetalle(concepto) {
    const wrapper = document.createElement('div');
    wrapper.className = 'detalle-wrapper';

    const tabla = document.createElement('table');
    tabla.className = 'table table-sm mb-0 detalle-tabla';

    const tbody = document.createElement('tbody');

    // Filas de registros existentes
    if (concepto.detalle && concepto.detalle.length > 0) {
        concepto.detalle.forEach(reg => {
            tbody.appendChild(crearFilaRegistroDetalle(reg, concepto.id, concepto.moneda || 'ARS'));
        });
    } else {
        const trVacio = document.createElement('tr');
        trVacio.className = 'fila-sin-registros';
        trVacio.innerHTML = `<td colspan="4" class="text-muted text-center py-2 fst-italic">Sin registros este mes</td>`;
        tbody.appendChild(trVacio);
    }

    // Fila formulario para agregar
    tbody.appendChild(crearFilaFormNuevoRegistro(concepto.id, concepto.moneda || 'ARS', concepto.cuenta_id_default));

    tabla.appendChild(tbody);
    wrapper.appendChild(tabla);
    return wrapper;
}

// Fila de un registro individual dentro del detalle
function crearFilaRegistroDetalle(reg, conceptoId, moneda = 'ARS') {
    const tr = document.createElement('tr');
    tr.id = `registro-${reg.id}`;
    const isPaid = reg.pagado === 1;
    if (isPaid) tr.classList.add('tr-pagado');

    const hoy = new Date().toISOString().split('T')[0];
    if (reg.fecha_vencimiento && reg.fecha_vencimiento.split('T')[0] < hoy && !isPaid) {
        tr.classList.add('tr-vencido');
    }

    const fechaVenc = reg.fecha_vencimiento ? reg.fecha_vencimiento.split('T')[0] : '';

    tr.innerHTML = `
        <td class="ps-3" style="width:110px">
            <div class="d-flex align-items-center gap-2">
                <button class="btn-pagado${isPaid ? ' pagado' : ''}"
                    title="${isPaid ? 'Marcar como no pagado' : 'Marcar como pagado'}"
                    onclick="togglePagado(${reg.id}, this, this.closest('tr'))">
                    <i class="bi ${isPaid ? 'bi-check-circle-fill' : 'bi-circle'}"></i>
                </button>
                <span class="text-muted" style="font-size:0.78rem">${formatearFechaCorta(reg.fecha)}</span>
            </div>
        </td>
        <td class="text-muted fst-italic" style="font-size:0.82rem">${reg.observaciones || '<span class="opacity-50">—</span>'}</td>
        <td class="text-end fw-medium det-importe">${formatearMoneda(reg.importe, moneda)}</td>
        <td class="text-end pe-3" style="width:50px">
            <button class="btn btn-outline-danger btn-sm py-0 px-1"
                title="Eliminar"
                onclick="eliminarRegistroMultiple(${reg.id}, ${conceptoId})">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    // Agregar selector de cuenta en la primera celda (bajo pagado+fecha)
    const tdFecha = tr.querySelector('td:first-child');
    const wrapCuenta = crearSelectorCuenta(reg.id, reg.cuenta_id, moneda);
    wrapCuenta.classList.add('cuenta-wrap-detalle');
    tdFecha.appendChild(wrapCuenta);

    return tr;
}

// Formulario para agregar nuevo registro dentro de card
function crearFormNuevoRegistroCard(conceptoId, moneda = 'ARS', cuentaDefault = null) {
    const form = document.createElement('div');
    form.className = 'concepto-card-form';
    form.id = `form-nuevo-${conceptoId}`;

    const hoy = new Date().toISOString().split('T')[0];

    // Selector de cuenta
    const cuentasMoneda = (app.cuentas || []).filter(c => (c.moneda || 'ARS') === moneda);
    const opcionesCuenta = cuentasMoneda
        .map(c => `<option value="${c.id}" ${c.id == cuentaDefault ? 'selected' : ''}>${c.nombre}</option>`)
        .join('');

    form.innerHTML = `
        <div class="concepto-card-form-row">
            <div class="concepto-card-form-group">
                <label class="concepto-card-form-label">Fecha</label>
                <input type="date" class="form-control form-control-sm concepto-card-input"
                    id="fecha-nuevo-${conceptoId}" value="${hoy}">
            </div>
            ${cuentasMoneda.length > 0 ? `
            <div class="concepto-card-form-group">
                <label class="concepto-card-form-label">Cuenta</label>
                <select id="cuenta-nuevo-${conceptoId}" class="form-select form-select-sm concepto-card-input">
                    ${opcionesCuenta}
                </select>
            </div>
            ` : ''}
            <div class="concepto-card-form-group">
                <label class="concepto-card-form-label">Descripción</label>
                <input type="text" class="form-control form-control-sm concepto-card-input"
                    id="obs-nuevo-${conceptoId}" placeholder="(opcional)">
            </div>
            <div class="concepto-card-form-group">
                <label class="concepto-card-form-label">Importe</label>
                <div class="d-flex gap-2 align-items-end">
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm concepto-card-input"
                        id="importe-nuevo-${conceptoId}" placeholder="0.00">
                    <button class="btn btn-success btn-sm py-1 px-3"
                        title="Agregar"
                        onclick="agregarRegistroMultiple(${conceptoId})">
                        <i class="bi bi-plus-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    const inputImporte = form.querySelector(`#importe-nuevo-${conceptoId}`);
    if (inputImporte) {
        inputImporte.addEventListener('focus', () => inputImporte.select());
        inputImporte.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') agregarRegistroMultiple(conceptoId);
        });
    }

    return form;
}

// Fila con formulario para agregar nuevo registro
function crearFilaFormNuevoRegistro(conceptoId, moneda = 'ARS', cuentaDefault = null) {
    const tr = document.createElement('tr');
    tr.className = 'fila-nuevo-registro';
    tr.id = `form-nuevo-${conceptoId}`;

    const hoy = new Date().toISOString().split('T')[0];

    // Selector de cuenta filtrado por moneda del concepto
    const cuentasMoneda = (app.cuentas || []).filter(c => (c.moneda || 'ARS') === moneda);
    const opcionesCuenta = cuentasMoneda
        .map(c => `<option value="${c.id}" ${c.id == cuentaDefault ? 'selected' : ''}>${c.nombre}</option>`)
        .join('');
    const cuentaSelectHtml = cuentasMoneda.length > 0
        ? `<select id="cuenta-nuevo-${conceptoId}" class="form-select form-select-sm mt-1" style="font-size:0.72rem">${opcionesCuenta}</select>`
        : '';

    tr.innerHTML = `
        <td class="ps-3" style="width:110px">
            <span class="form-field-label">Fecha</span>
            <input type="date" class="form-control form-control-sm input-vencimiento-detalle"
                id="fecha-nuevo-${conceptoId}" value="${hoy}">
            ${cuentaSelectHtml}
        </td>
        <td>
            <span class="form-field-label">Descripción</span>
            <input type="text" class="form-control form-control-sm"
                id="obs-nuevo-${conceptoId}" placeholder="(opcional)">
        </td>
        <td>
            <span class="form-field-label">Importe ${moneda === 'USD' ? 'U$D' : '$'}</span>
            <div class="det-importe-wrap">
                <input type="number" step="0.01" min="0" class="form-control form-control-sm text-end"
                    id="importe-nuevo-${conceptoId}" placeholder="0.00">
            </div>
        </td>
        <td class="text-end pe-3 align-middle" style="width:50px">
            <button class="btn btn-success btn-sm py-0 px-2"
                title="Agregar"
                onclick="agregarRegistroMultiple(${conceptoId})">
                <i class="bi bi-plus-lg"></i>
            </button>
        </td>
    `;

    // Seleccionar todo al enfocar y guardar con Enter en el campo importe
    const inputImporte = tr.querySelector(`#importe-nuevo-${conceptoId}`);
    inputImporte.addEventListener('focus', () => inputImporte.select());
    inputImporte.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') agregarRegistroMultiple(conceptoId);
    });

    return tr;
}

// Toggle expandir/colapsar detalle (cards)
function toggleDetalle(conceptoId) {
    const card = document.getElementById(`card-${conceptoId}`);
    const cardBody = document.getElementById(`card-body-${conceptoId}`);
    const arrow = document.getElementById(`arrow-${conceptoId}`);

    if (!card || !cardBody) return;

    const isExpanded = cardBody.classList.contains('expandido');

    if (isExpanded) {
        cardBody.classList.remove('expandido');
        arrow.classList.replace('bi-chevron-down', 'bi-chevron-right');
    } else {
        cardBody.classList.add('expandido');
        arrow.classList.replace('bi-chevron-right', 'bi-chevron-down');
    }
}

// Agregar nuevo registro a concepto múltiple
async function agregarRegistroMultiple(conceptoId) {
    const fecha = document.getElementById(`fecha-nuevo-${conceptoId}`).value;
    const importeVal = document.getElementById(`importe-nuevo-${conceptoId}`).value;
    const observaciones = document.getElementById(`obs-nuevo-${conceptoId}`).value.trim();

    const importe = parsearImporte(importeVal);

    if (!fecha) {
        mostrarError('Ingresá una fecha.');
        return;
    }
    if (importe <= 0) {
        mostrarError('El importe debe ser mayor a 0.');
        return;
    }

    // Cuenta: primero el selector inline del form, luego cuenta_id_default del concepto
    const cuentaSelectEl = document.getElementById(`cuenta-nuevo-${conceptoId}`);
    const cuentaId = cuentaSelectEl
        ? (parseInt(cuentaSelectEl.value) || null)
        : ((app.datos?.conceptos || []).find(c => c.id == conceptoId)?.cuenta_id_default || null);

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                concepto_id: conceptoId,
                mes: app.mesActual,
                anio: app.anioActual,
                fecha,
                importe,
                observaciones: observaciones || null,
                ...(cuentaId ? { cuenta_id: cuentaId } : {})
            })
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast('Registro agregado', 'success');
            await cargarDatos();
            toggleDetalle(conceptoId);
        } else {
            mostrarError('Error al guardar: ' + result.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    }
}

// Eliminar registro individual de concepto múltiple
async function eliminarRegistroMultiple(registroId, conceptoId) {
    if (!confirm('¿Eliminar este registro?')) return;

    try {
        const response = await fetch(API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registro_id: registroId })
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast('Registro eliminado', 'success');
            await cargarDatos();
            toggleDetalle(conceptoId);
        } else {
            mostrarError('Error al eliminar: ' + result.message);
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
    }
}

// Historial de tarifas hora — Personal casas particulares, tareas generales, sin retiro
// Fuente: ARCA (ex-AFIP) — actualizar cuando la CNTCP publique nuevas escalas
const ELENA_TARIFAS = {
    '2025-01': 3089.00,
    '2025-02': 3089.00,
    '2025-03': 3089.00,
    '2025-04': 3089.00,
    '2025-05': 3089.00,
    '2025-06': 3089.00,
    '2025-07': 3229.09,
    '2025-08': 3261.38,
    '2025-09': 3293.99,
    '2025-10': 3293.99,
    '2025-11': 3340.11,
    '2025-12': 3383.53,
    '2026-01': 3494.25,
    '2026-02': 3546.67,
    '2026-03': 3599.87,
    '2026-04': 3599.87
};

// Obtener tarifa hora vigente para un mes/año dado
function getTarifaElena(mes, anio) {
    const claveBuscada = `${anio}-${String(mes).padStart(2, '0')}`;
    const claves = Object.keys(ELENA_TARIFAS).sort();
    let tarifa = null;
    let claveVigente = null;
    for (const clave of claves) {
        if (clave <= claveBuscada) {
            tarifa = ELENA_TARIFAS[clave];
            claveVigente = clave;
        }
    }
    return { tarifa, desde: claveVigente };
}

// Sugerir importe de Elena para el mes/año seleccionado
function sugerirElena(inputElement, btnElement, mes, anio) {
    const { tarifa, desde } = getTarifaElena(mes, anio);

    if (!tarifa) {
        mostrarError(`No hay tarifa registrada para ${obtenerNombreMes(mes)} ${anio}.`);
        return;
    }

    const HORAS_POR_VISITA = 4;
    const total4 = Math.round(tarifa * HORAS_POR_VISITA * 4);
    const total5 = Math.round(tarifa * HORAS_POR_VISITA * 5);

    const [anioDesde, mesDesde] = desde.split('-');
    const refTexto = desde === `${anio}-${String(mes).padStart(2, '0')}`
        ? ''
        : ` (vigente desde ${obtenerNombreMes(parseInt(mesDesde))} ${anioDesde})`;

    const msg =
        `Empleada doméstica — ${obtenerNombreMes(mes)} ${anio}\n` +
        `Tareas generales, sin retiro${refTexto}\n` +
        `Valor hora: ${formatearMoneda(tarifa)}\n\n` +
        `  • 4 semanas (16h): ${formatearMoneda(total4)}\n` +
        `  • 5 semanas (20h): ${formatearMoneda(total5)}\n\n` +
        `¿Usar cálculo de 4 semanas (${formatearMoneda(total4)})?`;

    const confirmar = confirm(msg);

    if (confirmar) {
        inputElement.value = total4;
        inputElement.classList.add('unsaved');
        inputElement.focus();
        inputElement.blur();
    }
}

// Historial de precios Spotify Duo en Argentina (ARS)
// Clave: 'YYYY-MM' — agregar nueva entrada cada vez que Spotify actualice el precio
const SPOTIFY_DUO_PRECIOS = {
    '2026-04': 4399
};

// Obtener el precio de Spotify Duo para un mes/año dado
// Si no existe entrada exacta, usa el precio vigente más reciente anterior a esa fecha
function getPrecioSpotifyDuo(mes, anio) {
    const claveBuscada = `${anio}-${String(mes).padStart(2, '0')}`;
    const claves = Object.keys(SPOTIFY_DUO_PRECIOS).sort();

    let precioVigente = null;
    let claveVigente = null;

    for (const clave of claves) {
        if (clave <= claveBuscada) {
            precioVigente = SPOTIFY_DUO_PRECIOS[clave];
            claveVigente = clave;
        }
    }

    return { precio: precioVigente, desde: claveVigente };
}

// Sugerir precio Spotify Duo para el mes/año seleccionado
function sugerirSpotifyDuo(inputElement, btnElement, mes, anio) {
    const { precio, desde } = getPrecioSpotifyDuo(mes, anio);

    if (!precio) {
        mostrarError(`No hay precio registrado de Spotify Duo para ${obtenerNombreMes(mes)} ${anio}.`);
        return;
    }

    const [anioDesde, mesDesde] = desde.split('-');
    const refTexto = desde === `${anio}-${String(mes).padStart(2, '0')}`
        ? ''
        : ` (vigente desde ${obtenerNombreMes(parseInt(mesDesde))} ${anioDesde})`;

    const confirmar = confirm(
        `Spotify Plan Duo — ${obtenerNombreMes(mes)} ${anio}${refTexto}:\n\n` +
        `${formatearMoneda(precio)}\n\n` +
        `¿Usar este valor?`
    );

    if (confirmar) {
        inputElement.value = precio;
        inputElement.classList.add('unsaved');
        inputElement.focus();
        inputElement.blur();
    }
}

// Precio base YouTube Premium individual en Argentina (USD)
// Verificar en tu cuenta de YouTube si cambia
const YOUTUBE_PREMIUM_USD = 3.19;

// Obtener y cachear cotización del dólar oficial (válido por el día)
async function fetchTipoCambioUSD() {
    const hoy    = new Date().toISOString().split('T')[0];
    const cached = localStorage.getItem('cifra-tc-usd');
    if (cached) {
        try {
            const { fecha, venta } = JSON.parse(cached);
            if (fecha === hoy) { app.tipoCambioUSD = venta; return; }
        } catch (_) {}
    }
    try {
        const resp = await fetch('https://dolarapi.com/v1/dolares/oficial');
        if (!resp.ok) return;
        const data = await resp.json();
        app.tipoCambioUSD = data.venta;
        localStorage.setItem('cifra-tc-usd', JSON.stringify({ fecha: hoy, venta: data.venta }));
    } catch (_) {}
}

// Sugerir precio de YouTube Premium (ahora en USD directamente)
async function sugerirYoutubePremium(inputElement, btnElement) {
    const iconOriginal = btnElement.innerHTML;
    btnElement.disabled = true;
    btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        // Obtener cotización para mostrar equivalente informativo
        await fetchTipoCambioUSD();
        const equiv = app.tipoCambioUSD
            ? `\nEquivalente al dólar oficial: ${formatearMoneda(YOUTUBE_PREMIUM_USD * app.tipoCambioUSD)}`
            : '';

        const confirmar = confirm(
            `YouTube Premium Individual: U$D ${YOUTUBE_PREMIUM_USD}${equiv}\n\n¿Usar este valor?`
        );
        if (confirmar) {
            inputElement.value = String(YOUTUBE_PREMIUM_USD).replace('.', ',');
            inputElement.classList.add('unsaved');
            inputElement.focus();
            inputElement.blur();
        }
    } catch (error) {
        mostrarError('No se pudo obtener la cotización: ' + error.message);
    } finally {
        btnElement.disabled = false;
        btnElement.innerHTML = iconOriginal;
    }
}

// Obtener icono según el concepto
// Retorna { icon, color } — colores de la paleta Cifra
function obtenerIconoConcepto(nombre, tipo) {
    const n = nombre.toLowerCase();

    // ── INGRESOS ──────────────────────────────────────────────
    if (tipo === 'ingreso') {
        if (n.includes('sueldo') || n.includes('salario'))          return { icon: 'bi-briefcase-fill',          color: '#16A34A' };
        if (n.includes('ahorro'))                                   return { icon: 'bi-piggy-bank-fill',          color: '#0891B2' };
        if (n.includes('ipem') || n.includes('jubilacion'))         return { icon: 'bi-mortarboard-fill',         color: '#2563EB' };
        if (n.includes('tcer') || n.includes('hsc'))                return { icon: 'bi-building-fill-check',     color: '#16A34A' };
        return                                                             { icon: 'bi-plus-circle-fill',          color: '#16A34A' };
    }

    // ── VIVIENDA ──────────────────────────────────────────────
    if (n.includes('alquiler') || n.includes('departamento'))       return { icon: 'bi-building',                color: '#2563EB' };

    // ── SUPERMERCADO / COMPRAS ────────────────────────────────
    if (n.includes('supermercado') || n.includes('mercado'))        return { icon: 'bi-cart4',                   color: '#0891B2' };

    // ── COMBUSTIBLE ───────────────────────────────────────────
    if (n.includes('nafta') || n.includes('combustible')
        || n.includes('etios') || n.includes('tornado'))            return { icon: 'bi-fuel-pump-fill',           color: '#D97706' };

    // ── ENERGÍA ELÉCTRICA ─────────────────────────────────────
    if (n.includes('enersa') || n.includes('electric')
        || n.includes('luz'))                                       return { icon: 'bi-lightning-charge-fill',    color: '#FBBF24' };

    // ── GAS ───────────────────────────────────────────────────
    if (n.includes('redengas') || n.includes(' gas'))               return { icon: 'bi-fire',                    color: '#EA580C' };

    // ── DEPORTE / FITNESS ─────────────────────────────────────
    if (n.includes('rowing'))                                       return { icon: 'bi-bicycle',                  color: '#DC2626' };
    if (n.includes('gimnasio') || n.includes('fitness'))            return { icon: 'bi-heart-pulse-fill',         color: '#DB2777' };

    // ── STREAMING / ENTRETENIMIENTO ───────────────────────────
    if (n.includes('youtube'))                                      return { icon: 'bi-youtube',                  color: '#DC2626' };
    if (n.includes('spotify'))                                      return { icon: 'bi-music-note-beamed',        color: '#16A34A' };

    // ── CUOTA ALIMENTARIA / FAMILIA ───────────────────────────
    if (n.includes('alimentaria'))                                  return { icon: 'bi-people-fill',              color: '#0891B2' };

    // ── IMPUESTOS NACIONALES (AFIP) ───────────────────────────
    if (n.includes('afip') || n.includes('monotributo'))            return { icon: 'bi-receipt',                  color: '#6B7280' };

    // ── IMPUESTOS PROVINCIALES (ATER) ─────────────────────────
    if (n.includes('ater'))                                         return { icon: 'bi-file-earmark-text-fill',   color: '#D97706' };

    // ── SEGUROS (RIVADAVIA) ───────────────────────────────────
    if (n.includes('rivadavia') || n.includes('seguro'))            return { icon: 'bi-shield-fill-check',        color: '#2563EB' };

    // ── INTERNET / TELEFONÍA (PERSONAL / FLOW) ────────────────
    if (n.includes('personal') || n.includes('flow')
        || n.includes('internet') || n.includes('wifi'))            return { icon: 'bi-wifi',                     color: '#0891B2' };

    // ── COLEGIO PROFESIONAL (COPROCIER) ──────────────────────
    if (n.includes('coprocier') || n.includes('colegio prof'))      return { icon: 'bi-pc-display-horizontal',   color: '#4F46E5' };

    // ── CRÉDITO / PRÉSTAMO BANCARIO ───────────────────────────
    if (n.includes('credito') || n.includes('prestamo')
        || n.includes('cuota'))                                     return { icon: 'bi-bank2',                    color: '#DC2626' };

    // ── LIMPIEZA / MUCAMA (ELENA) ─────────────────────────────
    if (n.includes('elena') || n.includes('limpieza')
        || n.includes('mucama'))                                    return { icon: 'bi-bucket-fill',              color: '#7C3AED' };

    // ── SALUD / REMEDIOS ──────────────────────────────────────
    if (n.includes('remedios') || n.includes('farmacia')
        || n.includes('medicamento') || n.includes('salud'))        return { icon: 'bi-capsule-pill',             color: '#DB2777' };

    // ── COCHERA / ESTACIONAMIENTO ─────────────────────────────
    if (n.includes('cochera') || n.includes('garage'))              return { icon: 'bi-p-circle-fill',            color: '#6B7280' };

    // ── AIRE ACONDICIONADO ────────────────────────────────────
    if (n.includes('aire') || n.includes('acondicionado'))          return { icon: 'bi-wind',                     color: '#0891B2' };

    // ── HONORARIOS / PROFESIONALES ────────────────────────────
    if (n.includes('roy') || n.includes('udrizar')
        || n.includes('honorario') || n.includes('profesional'))    return { icon: 'bi-person-workspace',         color: '#4F46E5' };

    return { icon: 'bi-cash-stack', color: '#6B7280' };
}

// Guardar importe (conceptos de entrada única)
async function guardarImporte(conceptoId, importe, registroId, inputElement) {
    if (app.guardandoCambios) return;

    // Acepta número directo o string formateado
    const importeNumerico = typeof importe === 'number' ? importe : parsearImporte(importe);

    // No guardar si es 0 y no existe registro
    if (importeNumerico === 0 && !registroId) {
        if (inputElement) {
            inputElement.classList.remove('unsaved', 'saved');
        }
        return;
    }

    app.guardandoCambios = true;

    if (inputElement) {
        inputElement.classList.add('saving');
        inputElement.classList.remove('unsaved');
    }

    // Leer fecha de vencimiento del mismo row (si el usuario la completó)
    const trRow = inputElement?.closest('tr');
    const fechaVencimiento = trRow?.querySelector('.input-vencimiento')?.value || null;

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                concepto_id: conceptoId,
                mes: app.mesActual,
                anio: app.anioActual,
                importe: importeNumerico,
                ...(fechaVencimiento ? { fecha_vencimiento: fechaVencimiento } : {})
            })
        });

        const result = await response.json();

        if (result.success) {
            if (inputElement) {
                inputElement.classList.add('saved');
                inputElement.classList.remove('saving');
                setTimeout(() => inputElement.classList.remove('saved'), 2000);
                // Sincronizar data-order del td para sorting de DataTables
                const td = inputElement.closest('td');
                if (td) td.setAttribute('data-order', importeNumerico);
            }
            mostrarToast('Guardado correctamente', 'success');
            await cargarDatos();
        } else {
            mostrarError('Error al guardar: ' + result.message);
            if (inputElement) inputElement.classList.remove('saving');
        }
    } catch (error) {
        mostrarError('Error de conexión: ' + error.message);
        if (inputElement) inputElement.classList.remove('saving');
    } finally {
        app.guardandoCambios = false;
    }
}

// Guardar fecha de vencimiento de un registro
async function guardarVencimiento(registroId, fecha, trElement) {
    try {
        const response = await fetch(API_URL, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registro_id: registroId, fecha_vencimiento: fecha || null })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        if (result.data?.mes) {
            const nombreMes = obtenerNombreMes(result.data.mes);
            mostrarToast(`Movido a ${nombreMes} ${result.data.anio}`, 'success');
            await cargarDatos();
        } else {
            const hoy = new Date().toISOString().split('T')[0];
            if (trElement) {
                const isPaid = trElement.classList.contains('tr-pagado');
                trElement.classList.toggle('tr-vencido', fecha && fecha < hoy && !isPaid);
            }
        }
    } catch (error) {
        mostrarError('Error al guardar vencimiento: ' + error.message);
    }
}

// Toggle estado pagado de un registro
async function togglePagado(registroId, btnElement, trElement, conceptoId = null) {
    const isPaid       = btnElement.classList.contains('pagado');
    const newPagado    = isPaid ? 0 : 1;
    let registroCreado = false;

    try {
        // Si no hay registro todavía, crear uno con importe 0 antes de patchear
        if (!registroId && conceptoId && newPagado === 1) {
            const respCrear = await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ concepto_id: conceptoId, mes: app.mesActual, anio: app.anioActual, importe: 0 })
            });
            const resCrear = await respCrear.json();
            if (!resCrear.success) throw new Error(resCrear.message);
            registroId     = resCrear.data.id;
            registroCreado = true;
        }

        const response = await fetch(API_URL, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registro_id: registroId, pagado: newPagado })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const paid = newPagado === 1;
        btnElement.classList.toggle('pagado', paid);
        btnElement.querySelector('i').className = paid ? 'bi bi-check-circle-fill' : 'bi bi-circle';
        btnElement.title = paid ? 'Marcar como no pagado' : 'Marcar como pagado';
        if (trElement) {
            trElement.classList.toggle('tr-pagado', paid);
            if (paid) trElement.classList.remove('tr-vencido'); // pagado → no mostrar rojo
        }
        // Recargar filas y saldos (re-renderiza input/label según estado pagado)
        await cargarDatos();
    } catch (error) {
        mostrarError('Error al actualizar: ' + error.message);
    }
}

// Formatear moneda — acepta 'ARS' (default) o 'USD'
function formatearMoneda(valor, moneda = 'ARS') {
    if (moneda === 'USD') {
        const num = parseFloat(valor);
        const formatted = Math.abs(num).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        return (num < 0 ? '-' : '') + 'U$D\u00A0' + formatted;
    }
    return new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2
    }).format(valor);
}

// Formatear fecha ISO a dd/mm/yy
function formatearFechaCorta(fechaISO) {
    if (!fechaISO) return '';
    const [y, m, d] = fechaISO.split('T')[0].split('-');
    return `${d}/${m}/${y.slice(2)}`;
}

// Obtener nombre del mes
function obtenerNombreMes(numeroMes) {
    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return meses[numeroMes - 1];
}

// Toggle sección resumen

function actualizarLabelFiltro() {
    const el = document.getElementById('filtroMesLabel');
    if (el) el.textContent = `${obtenerNombreMes(app.mesActual)} ${app.anioActual}`;
}

function renderizarResumenPendientes() {
    const el = document.getElementById('resumenPendientes');
    if (!el || !app.datos) return;

    const pendientes = app.datos.conceptos.filter(c =>
        c.tipo === 'gasto' && c.pagado !== 1 && parseFloat(c.importe) > 0
    ).sort((a, b) => parseFloat(b.importe) - parseFloat(a.importe));

    if (!pendientes.length) {
        el.innerHTML = `
            <div class="resumen-pendientes-header">Pendientes de pago</div>
            <div class="resumen-pendientes-vacio">
                <i class="bi bi-check-circle text-success"></i> Todo pagado
            </div>`;
        return;
    }

    const pendientesARS = pendientes.filter(c => (c.moneda || 'ARS') === 'ARS');
    const pendientesUSD = pendientes.filter(c => c.moneda === 'USD');
    const totalARS = pendientesARS.reduce((s, c) => s + parseFloat(c.importe), 0);
    const totalUSD = pendientesUSD.reduce((s, c) => s + parseFloat(c.importe), 0);

    const filasPendiente = (lista, moneda) => lista.map(c => `
        <div class="resumen-pendiente-row">
            <span class="resumen-pendiente-punto" style="background:${c.categoria_color || '#94a3b8'}"></span>
            <span class="resumen-pendiente-nombre">${c.nombre}</span>
            <span class="resumen-pendiente-importe">${formatearMoneda(parseFloat(c.importe), moneda)}</span>
        </div>`).join('');

    const totalUSDHtml = totalUSD > 0 ? `
        <div class="resumen-pendiente-total">
            <span>Total pendiente USD</span>
            <span>${formatearMoneda(totalUSD, 'USD')}</span>
        </div>` : '';

    el.innerHTML = `
        <div class="resumen-pendientes-header">Pendientes de pago</div>
        ${filasPendiente(pendientesARS, 'ARS')}
        ${filasPendiente(pendientesUSD, 'USD')}
        <div class="resumen-pendiente-total">
            <span>Total pendiente ARS</span>
            <span>${formatearMoneda(totalARS)}</span>
        </div>
        ${totalUSDHtml}`;
}


function toggleStatsExtra() {
    const extra = document.getElementById('statsExtra');
    const btn = document.getElementById('btnStatsMore');
    const visible = extra.classList.toggle('visible');
    btn.classList.toggle('expanded', visible);
    if (visible) {
        setTimeout(() => {
            document.addEventListener('click', _cerrarStatsExtra, { once: true, capture: true });
        }, 0);
    }
}
function _cerrarStatsExtra(e) {
    const extra = document.getElementById('statsExtra');
    const btn = document.getElementById('btnStatsMore');
    if (extra && !extra.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
        extra.classList.remove('visible');
        btn.classList.remove('expanded');
    } else if (extra) {
        // clic dentro del dropdown: re-registrar para cerrarlo después
        document.addEventListener('click', _cerrarStatsExtra, { once: true, capture: true });
    }
}

// ============================================================
// Panel Gerencial (integrado en modal Resumen)
// ============================================================

function renderizarGerencial() {
    const body = document.getElementById('modalGerencialBody');
    if (!body || !app.datos) return;

    const resARS = app.datos.resumen?.ARS || { total_ingresos: 0, ingresos_cobrados: 0, total_gastos: 0, gastos_pagados: 0 };
    const resUSD = app.datos.resumen?.USD || { total_ingresos: 0, ingresos_cobrados: 0, total_gastos: 0, gastos_pagados: 0 };

    const cuentasARS     = (app.cuentas || []).filter(c => (c.moneda || 'ARS') === 'ARS');
    const cuentasUSD     = (app.cuentas || []).filter(c => c.moneda === 'USD');
    const totalCtasARS   = cuentasARS.reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);
    const totalCtasUSD   = cuentasUSD.reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);

    const pendientesARS  = Math.max(0, resARS.total_gastos - resARS.gastos_pagados);
    const pendientesUSD  = Math.max(0, resUSD.total_gastos - resUSD.gastos_pagados);
    const disponibleARS  = totalCtasARS - pendientesARS;
    const resultadoARS   = resARS.ingresos_cobrados - resARS.gastos_pagados;
    const pctGastos      = resARS.total_gastos > 0 ? Math.min(100, resARS.gastos_pagados / resARS.total_gastos * 100) : 0;
    const pctIngresos    = resARS.total_ingresos > 0 ? Math.min(100, resARS.ingresos_cobrados / resARS.total_ingresos * 100) : 0;
    const listaPend      = app.datos.conceptos.filter(c => c.tipo === 'gasto' && c.pagado !== 1 && parseFloat(c.importe) > 0)
                             .sort((a, b) => parseFloat(b.importe) - parseFloat(a.importe));
    const cantPend       = listaPend.length;

    // Semáforo
    let semColor, semLabel;
    if (disponibleARS < 0) {
        semColor = 'var(--color-danger)';  semLabel = 'Saldo insuficiente';
    } else if (pendientesARS > disponibleARS) {
        semColor = 'var(--color-warning)'; semLabel = 'Fondos ajustados';
    } else {
        semColor = 'var(--color-success)'; semLabel = 'Situación sólida';
    }

    // Alertas de vencimiento
    const hoy  = new Date(); hoy.setHours(0,0,0,0);
    const en7d = new Date(hoy.getTime() + 7 * 86400000);
    const fmtDate = d => { const [y,m,dd] = d.split('-'); return `${dd}/${m}/${y.slice(2)}`; };
    const diasHasta = d => {
        const [y,m,dd] = d.split('-');
        const diff = Math.round((new Date(+y, m-1, +dd) - hoy) / 86400000);
        return diff === 0 ? 'hoy' : diff === 1 ? 'mañana' : `en ${diff}d`;
    };
    const diasDesde = d => {
        const [y,m,dd] = d.split('-');
        const diff = Math.round((hoy - new Date(+y, m-1, +dd)) / 86400000);
        return diff <= 0 ? 'hoy' : diff === 1 ? 'ayer' : `hace ${diff}d`;
    };
    const vencidos = app.datos.conceptos.filter(c => {
        if (c.tipo !== 'gasto' || c.pagado === 1 || !c.fecha_vencimiento) return false;
        const [y,m,dd] = c.fecha_vencimiento.split('-');
        return new Date(+y, m-1, +dd) < hoy;
    }).sort((a, b) => parseFloat(b.importe) - parseFloat(a.importe));
    const proximos = app.datos.conceptos.filter(c => {
        if (c.tipo !== 'gasto' || c.pagado === 1 || !c.fecha_vencimiento) return false;
        const [y,m,dd] = c.fecha_vencimiento.split('-');
        const f = new Date(+y, m-1, +dd);
        return f >= hoy && f <= en7d;
    }).sort((a, b) => {
        const [y1,m1,d1] = a.fecha_vencimiento.split('-');
        const [y2,m2,d2] = b.fecha_vencimiento.split('-');
        return new Date(+y1, m1-1, +d1) - new Date(+y2, m2-1, +d2);
    });

    // Categorías (solo gastos)
    const catMap = {};
    app.datos.conceptos.filter(c => c.tipo === 'gasto' && parseFloat(c.importe) > 0).forEach(c => {
        const k = c.categoria_id || 0;
        if (!catMap[k]) catMap[k] = {
            nombre: c.categoria_nombre || 'Sin cat.',
            color:  _colorCategoria(c.categoria_color || '#94a3b8'),
            totalARS: 0, totalUSD: 0,
            presupuesto: 0,
        };
        (c.moneda || 'ARS') === 'USD' ? (catMap[k].totalUSD += parseFloat(c.importe))
                                       : (catMap[k].totalARS += parseFloat(c.importe));
    });
    // Enriquecer con presupuesto desde app.categorias
    Object.keys(catMap).forEach(k => {
        const catData = (app.categorias || []).find(c => c.id === parseInt(k));
        if (catData) catMap[k].presupuesto = parseFloat(catData.presupuesto) || 0;
    });
    const cats        = Object.values(catMap).sort((a, b) => b.totalARS - a.totalARS);
    const totalCatARS = cats.reduce((s, c) => s + c.totalARS, 0);

    // ── HTML ──────────────────────────────────────────────────────────
    const resColor   = resultadoARS >= 0 ? 'var(--cifra-pos)' : 'var(--cifra-neg)';
    const dispColor  = disponibleARS >= 0 ? 'var(--cifra-pos)' : 'var(--cifra-neg)';
    const dispBg     = disponibleARS >= 0 ? 'rgba(52,211,153,0.1)' : 'rgba(248,113,113,0.1)';

    // Sección 1 — KPIs
    const kpisHtml = `
    <div class="ger-section">
        <div class="ger-section-title"><i class="bi bi-calendar3 me-1"></i>Flujo del mes</div>
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <div class="ger-kpi">
                    <div class="ger-kpi-label"><i class="bi bi-arrow-up-circle me-1"></i>Cobrado</div>
                    <div class="ger-kpi-valor" style="color:var(--cifra-pos)">${formatearMoneda(resARS.ingresos_cobrados)}</div>
                    <div class="ger-prog"><div class="ger-prog-bar" style="width:${pctIngresos.toFixed(1)}%;background:var(--color-success)"></div></div>
                    <div class="ger-kpi-sub">${pctIngresos.toFixed(0)}% de ${formatearMoneda(resARS.total_ingresos)} esperado</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ger-kpi">
                    <div class="ger-kpi-label"><i class="bi bi-arrow-down-circle me-1"></i>Pagado</div>
                    <div class="ger-kpi-valor" style="color:var(--cifra-neg)">${formatearMoneda(resARS.gastos_pagados)}</div>
                    <div class="ger-prog"><div class="ger-prog-bar" style="width:${pctGastos.toFixed(1)}%;background:var(--color-danger)"></div></div>
                    <div class="ger-kpi-sub">${pctGastos.toFixed(0)}% de ${formatearMoneda(resARS.total_gastos)} total</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ger-kpi ger-kpi-clickable" data-bs-toggle="collapse" data-bs-target="#gerPendDetalles" aria-expanded="false">
                    <div class="ger-kpi-label"><i class="bi bi-hourglass-split me-1"></i>Por pagar <i class="bi bi-chevron-down ms-1 ger-pend-chevron" style="font-size:0.6rem;opacity:0.6;transition:transform .2s"></i></div>
                    <div class="ger-kpi-valor" style="color:var(--color-warning)">${formatearMoneda(pendientesARS)}</div>
                    <div class="ger-kpi-sub">${cantPend} concepto${cantPend !== 1 ? 's' : ''} pendiente${cantPend !== 1 ? 's' : ''}</div>
                    ${pendientesUSD > 0 ? `<div class="ger-kpi-sub">${formatearMoneda(pendientesUSD,'USD')}</div>` : ''}
                    <div class="collapse" id="gerPendDetalles">
                        <div class="ger-pend-lista">
                            ${listaPend.map(c => `
                            <div class="ger-pend-fila">
                                <span class="ger-pend-nombre">${c.nombre}</span>
                                <span class="ger-pend-importe">${formatearMoneda(c.importe, c.moneda || 'ARS')}</span>
                            </div>`).join('')}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="ger-kpi">
                    <div class="ger-kpi-label"><i class="bi bi-calculator me-1"></i>Resultado del mes</div>
                    <div class="ger-kpi-valor" style="color:${resColor}">${resultadoARS >= 0 ? '+' : ''}${formatearMoneda(resultadoARS)}</div>
                    <div class="ger-kpi-sub">Cobrado − Pagado este mes</div>
                    <div class="ger-semaforo mt-1">
                        <span class="ger-sem-dot" style="background:${semColor}"></span>
                        <span style="color:${semColor};font-size:0.68rem;font-weight:600">${semLabel}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>`;

    // Sección 2 — Distribución con donut chart SVG
    const R_d = 55, CX_d = 75, CY_d = 75, SW_d = 20;
    const circ_d = 2 * Math.PI * R_d;
    const catsConGastos = cats.filter(c => c.totalARS > 0);
    let cumPct = 0;
    const donutSegs = catsConGastos.map(cat => {
        const pct = cat.totalARS / totalCatARS;
        const dashLen = pct * circ_d;
        const dashOff = circ_d * (1 - cumPct);
        cumPct += pct;
        return `<circle cx="${CX_d}" cy="${CY_d}" r="${R_d}" fill="none"
            stroke="${cat.color}" stroke-width="${SW_d}"
            stroke-dasharray="${dashLen.toFixed(2)} ${(circ_d - dashLen).toFixed(2)}"
            stroke-dashoffset="${dashOff.toFixed(2)}"
            transform="rotate(-90 ${CX_d} ${CY_d})"/>`;
    }).join('');
    const donutSvg = catsConGastos.length ? `
        <svg viewBox="0 0 150 150" class="ger-donut-svg">
            <circle cx="${CX_d}" cy="${CY_d}" r="${R_d}" fill="none"
                stroke="rgba(100,116,139,0.1)" stroke-width="${SW_d}"/>
            ${donutSegs}
            <text x="${CX_d}" y="${CY_d - 8}" text-anchor="middle"
                font-family="Inter,sans-serif" font-size="9" fill="#6b7280">Total ARS</text>
            <text x="${CX_d}" y="${CY_d + 9}" text-anchor="middle"
                font-family="Montserrat,sans-serif" font-size="10" font-weight="700"
                fill="currentColor">${formatearMoneda(totalCatARS)}</text>
        </svg>` : '';
    const donutLegend = cats.map(cat => {
        const pct = totalCatARS > 0 ? cat.totalARS / totalCatARS * 100 : 0;
        const parts = [];
        if (cat.totalARS > 0) parts.push(formatearMoneda(cat.totalARS));
        if (cat.totalUSD > 0) parts.push(formatearMoneda(cat.totalUSD, 'USD'));
        // Presupuesto
        let barColor = cat.color;
        let presupHtml = '';
        if (cat.presupuesto > 0) {
            const pctPres = Math.round(cat.totalARS / cat.presupuesto * 100);
            barColor = pctPres > 100 ? 'var(--color-danger)' : pctPres > 80 ? '#f59e0b' : cat.color;
            const presupColor = pctPres > 100 ? 'var(--color-danger)' : pctPres > 80 ? '#f59e0b' : 'var(--color-gray)';
            presupHtml = `<span class="ger-leg-presup" style="color:${presupColor}" title="Presupuesto: ${formatearMoneda(cat.presupuesto)}">${pctPres}%&nbsp;presup.</span>`;
        }
        return `<div class="ger-leg-item">
            <span class="ger-leg-dot" style="background:${cat.color}"></span>
            <span class="ger-leg-nombre">${cat.nombre}</span>
            <div class="ger-leg-bar-wrap">
                <div class="ger-leg-bar" style="width:${pct.toFixed(1)}%;background:${barColor}"></div>
            </div>
            <span class="ger-leg-importe">${parts.join(' + ') || '—'}</span>
            <span class="ger-leg-pct">${pct > 0 ? pct.toFixed(0) + '%' : ''}</span>
            ${presupHtml}
        </div>`;
    }).join('');
    const distribHtml = `
    <div class="ger-section ger-distrib-new">
        <div class="ger-section-title"><i class="bi bi-pie-chart-fill me-1"></i>Distribución de gastos</div>
        ${cats.length ? `<div class="ger-distrib-layout">
            <div class="ger-donut-wrap">${donutSvg}</div>
            <div class="ger-donut-legend">${donutLegend}</div>
        </div>` : '<p class="text-muted small mb-0 mt-2">Sin gastos este período.</p>'}
    </div>`;

    // Sección 4 — Alertas
    let alertasInner = '';
    if (!vencidos.length && !proximos.length) {
        alertasInner = `<div class="ger-ok"><i class="bi bi-check-circle-fill me-2"></i>Sin alertas de vencimiento</div>`;
    } else {
        if (vencidos.length) {
            alertasInner += `<div class="ger-alerta-titulo" style="color:var(--color-danger)">
                <i class="bi bi-exclamation-circle-fill me-1"></i>Vencidos (${vencidos.length})</div>`;
            alertasInner += vencidos.map(c => `<div class="ger-fila">
                <span class="ger-fila-nombre">${c.nombre}</span>
                <span class="ger-fila-valor" style="color:var(--color-danger)">${formatearMoneda(c.importe, c.moneda||'ARS')}</span>
                <span class="ger-fila-meta">${fmtDate(c.fecha_vencimiento)} · ${diasDesde(c.fecha_vencimiento)}</span>
            </div>`).join('');
        }
        if (proximos.length) {
            alertasInner += `<div class="ger-alerta-titulo${vencidos.length ? ' mt-3' : ''}" style="color:var(--color-warning)">
                <i class="bi bi-clock-fill me-1"></i>Próximos 7 días (${proximos.length})</div>`;
            alertasInner += proximos.map(c => `<div class="ger-fila">
                <span class="ger-fila-nombre">${c.nombre}</span>
                <span class="ger-fila-valor" style="color:var(--color-warning)">${formatearMoneda(c.importe, c.moneda||'ARS')}</span>
                <span class="ger-fila-meta">${fmtDate(c.fecha_vencimiento)} · ${diasHasta(c.fecha_vencimiento)}</span>
            </div>`).join('');
        }
    }

    body.innerHTML = kpisHtml
        + `<div class="row g-0 ger-mid-row">
               <div class="col-12 col-md-5 ger-col-border"><div id="resumenPendientes"></div></div>
               <div class="col-12 col-md-7">${distribHtml}</div>
           </div>`
        + `<div class="ger-section"><div class="ger-section-title"><i class="bi bi-bell me-1"></i>Alertas</div>${alertasInner}</div>`;

    renderizarResumenPendientes();
}

function abrirModalResumen() {
    const modal = document.getElementById('modalResumen');
    renderizarGerencial();
    modal.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
        bootstrap.Tooltip.getOrCreateInstance(el, { trigger: 'hover focus' })
    );
    new bootstrap.Modal(modal).show();
}

function descargarResumenPDF() {
    document.body.classList.add('cifra-printing');
    window.onafterprint = () => document.body.classList.remove('cifra-printing');
    window.print();
}

function abrirModalCuentas() {
    new bootstrap.Modal(document.getElementById('modalCuentas')).show();
    cargarCuentas();
}

function abrirModalIngresos() {
    document.getElementById('mesAnioIngresos').textContent =
        `${String(app.mesActual).padStart(2, '0')}/${app.anioActual}`;
    ocultarFormNuevoIngreso();
    renderizarModalIngresos();
    new bootstrap.Modal(document.getElementById('modalIngresos')).show();
}

function renderizarModalIngresos() {
    const body = document.getElementById('modalIngresosBody');
    if (!body) return;

    const ingresos = (app.datos?.conceptos || []).filter(c => c.tipo === 'ingreso');

    if (!ingresos.length) {
        body.innerHTML = '<p class="text-center text-muted py-4">Sin conceptos de ingreso. Usá "Nuevo ingreso" para agregar.</p>';
        document.getElementById('totalIngresosModal').innerHTML = '';
        return;
    }

    const rows = ingresos.map(c => {
        const isPaid   = c.pagado === 1;
        const fechaVal = c.fecha ? c.fecha.split('T')[0] : '';
        const moneda   = c.moneda || 'ARS';
        const imp      = c.importe > 0 ? formatearMoneda(c.importe, moneda) : '';
        const cuentasOpts = app.cuentas
            .filter(cu => (cu.moneda || 'ARS') === moneda)
            .map(cu => `<option value="${cu.id}">${cu.nombre}</option>`).join('');
        const hasReg   = !!c.registro_id;

        return `
        <div class="ingreso-row ${isPaid ? 'ingreso-row-cobrado' : ''}"
             data-concepto-id="${c.id}"
             data-registro-id="${c.registro_id || ''}">

            <button class="btn-pagado ${isPaid ? 'pagado' : ''}"
                    title="${isPaid ? 'Marcar como no cobrado' : 'Marcar como cobrado'}"
                    onclick="toggleCobradoIngreso(${c.registro_id || 'null'}, ${c.id}, this)">
                <i class="bi ${isPaid ? 'bi-check-circle-fill' : 'bi-circle'}"></i>
            </button>

            <div class="ingreso-body">
                <div class="ingreso-linea1" id="ingreso-nombre-wrap-${c.id}">
                    <span class="ingreso-nombre">${c.nombre}</span>
                    <button class="btn-edit-ingreso" title="Editar concepto"
                            onclick="mostrarEditConceptoIngreso(${c.id})">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                </div>
                <div class="ingreso-linea2">
                    <select class="ingreso-cuenta form-select form-select-sm"
                            data-cuenta-actual="${c.cuenta_id || ''}"
                            ${!hasReg ? 'disabled' : `onchange="guardarCuentaRegistro(${c.registro_id}, this.value || null)"`}>
                        <option value="">— Cuenta —</option>
                        ${cuentasOpts}
                    </select>
                    <input type="date" class="ingreso-fecha form-control form-control-sm"
                           value="${fechaVal}"
                           ${!hasReg ? 'disabled title="Ingresá el importe primero"' : `onchange="guardarFechaIngreso(${c.registro_id}, this.value, this)"`}>
                </div>
            </div>
            <div class="ingreso-importe-col">
                <div class="importe-input-wrap"><input type="text" inputmode="decimal"
                       class="ingreso-importe input-importe form-control form-control-sm"
                       value="${imp}"
                       data-concepto-id="${c.id}"
                       data-registro-id="${c.registro_id || ''}"
                       placeholder="0,00"
                       onfocus="const v=parsearImporte(this.value);this.value=v>0?String(v).replace('.',','):'';this.select()"
                       onblur="guardarImporteIngreso(this)"
                       onkeypress="if(event.key==='Enter')this.blur()"
                       oninput="this.classList.add('unsaved');this.classList.remove('saved')"></div>
            </div>
        </div>`;
    }).join('');

    body.innerHTML = `<div class="ingreso-lista">${rows}</div>`;

    // Setear valor del select de cuenta (no se puede en template string directamente)
    ingresos.forEach(c => {
        if (!c.cuenta_id) return;
        const row = body.querySelector(`[data-concepto-id="${c.id}"]`);
        if (row) {
            const sel = row.querySelector('.ingreso-cuenta');
            if (sel) sel.value = c.cuenta_id;
        }
    });

    // Totales
    const total    = ingresos.reduce((s, c) => s + parseFloat(c.importe || 0), 0);
    const cobrado  = ingresos.filter(c => c.pagado === 1).reduce((s, c) => s + parseFloat(c.importe || 0), 0);
    const pendiente = total - cobrado;

    const tooltips = [
        { label: 'Total',     valor: total,     cls: '',           title: 'Suma de todos los ingresos del mes, cobrados o no' },
        { label: 'Cobrado',   valor: cobrado,   cls: 'ingreso-cobrado-valor', title: 'Ingresos ya recibidos (marcados con ✓)' },
        { label: 'Pendiente', valor: pendiente, cls: 'text-muted', title: 'Ingresos que todavía no cobraste' },
    ];

    const container = document.getElementById('totalIngresosModal');
    container.innerHTML = tooltips.map(t => `
        <div class="ingreso-total-item">
            <span class="ingreso-total-label">
                ${t.label}
                <i class="bi bi-info-circle ingreso-total-info"
                   data-bs-toggle="tooltip"
                   data-bs-placement="top"
                   data-bs-title="${t.title}"></i>
            </span>
            <span class="ingreso-total-valor ${t.cls}">${formatearMoneda(t.valor)}</span>
        </div>`).join('');

    container.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
        new bootstrap.Tooltip(el, { trigger: 'hover focus' })
    );
}

async function toggleCobradoIngreso(registroId, conceptoId, btn) {
    const isPaid   = btn.classList.contains('pagado');
    const newPagado = isPaid ? 0 : 1;

    // Si no hay registro, crear uno vacío primero
    if (!registroId && newPagado === 1) {
        const r = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ concepto_id: conceptoId, mes: app.mesActual, anio: app.anioActual, importe: 0 })
        });
        const res = await r.json();
        if (!res.success) { mostrarError(res.message); return; }
        registroId = res.data.id;
    }
    if (!registroId) return;

    try {
        const resp = await fetch(API_URL, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registro_id: registroId, pagado: newPagado })
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);
        await cargarDatos(); // re-renderiza el modal vía renderizarDatos()
    } catch (e) {
        mostrarError('Error: ' + e.message);
    }
}

async function guardarFechaIngreso(registroId, valor, input) {
    if (!registroId) return;
    try {
        const resp = await fetch(API_URL, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registro_id: registroId, fecha: valor || null })
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);
        // Actualizar en memoria
        const c = app.datos?.conceptos?.find(c => c.registro_id == registroId);
        if (c) c.fecha = valor;
    } catch (e) {
        mostrarError('Error al guardar fecha: ' + e.message);
        if (input) input.classList.add('is-invalid');
    }
}

async function guardarImporteIngreso(input) {
    const importe    = parsearImporte(input.value);
    input.value      = importe > 0 ? formatearMoneda(importe) : '';
    const conceptoId = parseInt(input.dataset.conceptoId);
    const registroId = input.dataset.registroId ? parseInt(input.dataset.registroId) : null;
    // guardarImporte ya llama cargarDatos(), que re-renderiza el modal si está abierto
    await guardarImporte(conceptoId, importe, registroId, input);
}

function mostrarEditConceptoIngreso(conceptoId) {
    const wrap = document.getElementById(`ingreso-nombre-wrap-${conceptoId}`);
    if (!wrap) return;

    const concepto = app.datos?.conceptos?.find(c => c.id == conceptoId);
    if (!concepto) return;

    const cuentasOpts = [
        '<option value="">— Sin cuenta —</option>',
        ...app.cuentas.map(cu =>
            `<option value="${cu.id}">${cu.nombre}</option>`)
    ].join('');

    wrap.innerHTML = `
        <div class="ingreso-edit-form">
            <input type="text" class="form-control form-control-sm" style="max-width:160px"
                   id="edit-ing-nombre-${conceptoId}" value="${concepto.nombre}">
            <select class="form-select form-select-sm" style="max-width:130px"
                    id="edit-ing-cuenta-${conceptoId}">
                ${cuentasOpts}
            </select>
            <button class="btn btn-success btn-sm" onclick="guardarEditConceptoIngreso(${conceptoId})">
                <i class="bi bi-check-lg"></i>
            </button>
            <button class="btn btn-outline-secondary btn-sm" onclick="renderizarModalIngresos()">
                <i class="bi bi-x-lg"></i>
            </button>
            <button class="btn btn-outline-danger btn-sm" title="Desactivar concepto"
                    onclick="toggleActivoConcepto(${conceptoId}, 0).then(()=>cargarDatos())">
                <i class="bi bi-eye-slash"></i>
            </button>
        </div>`;

    // Preseleccionar cuenta por defecto actual
    const selCuenta = document.getElementById(`edit-ing-cuenta-${conceptoId}`);
    if (selCuenta && concepto.cuenta_id_default) selCuenta.value = concepto.cuenta_id_default;
}

async function guardarEditConceptoIngreso(conceptoId) {
    const nombre    = document.getElementById(`edit-ing-nombre-${conceptoId}`)?.value?.trim();
    const cuentaDef = document.getElementById(`edit-ing-cuenta-${conceptoId}`)?.value || null;
    if (!nombre) { mostrarError('El nombre no puede estar vacío'); return; }

    try {
        const resp = await fetch(CONCEPTOS_API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: conceptoId, nombre, cuenta_id_default: cuentaDef || null })
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);
        mostrarToast('Concepto actualizado', 'success');
        await cargarDatos(); // re-renderiza todo
    } catch (e) {
        mostrarError('Error: ' + e.message);
    }
}

function mostrarFormNuevoIngreso() {
    const form = document.getElementById('formNuevoIngreso');
    form.classList.remove('d-none');

    // Poblar select de cuentas
    const sel = document.getElementById('nuevoIngresoCuenta');
    sel.innerHTML = '<option value="">— Sin cuenta —</option>' +
        app.cuentas.map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');

    document.getElementById('nuevoIngresoNombre').value  = '';
    document.getElementById('nuevoIngresoImporte').value = '';
    document.getElementById('nuevoIngresoNombre').focus();
}

function ocultarFormNuevoIngreso() {
    document.getElementById('formNuevoIngreso')?.classList.add('d-none');
}

async function guardarNuevoIngreso() {
    const nombre   = document.getElementById('nuevoIngresoNombre').value.trim();
    const cuentaDef = document.getElementById('nuevoIngresoCuenta').value || null;
    const importeStr = document.getElementById('nuevoIngresoImporte').value;
    const importe  = parsearImporte(importeStr);

    if (!nombre) { mostrarError('El nombre es obligatorio'); return; }

    try {
        // 1. Crear concepto
        const respC = await fetch(CONCEPTOS_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, tipo: 'ingreso', cuenta_id_default: cuentaDef || null })
        });
        const resC = await respC.json();
        if (!resC.success) throw new Error(resC.message);
        const nuevoConceptoId = resC.data?.id;

        // 2. Crear registro del mes si hay importe
        if (nuevoConceptoId && importe > 0) {
            await fetch(API_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    concepto_id: nuevoConceptoId,
                    mes: app.mesActual,
                    anio: app.anioActual,
                    importe
                })
            });
        }

        ocultarFormNuevoIngreso();
        mostrarToast(`"${nombre}" agregado`, 'success');
        await cargarDatos();
    } catch (e) {
        mostrarError('Error: ' + e.message);
    }
}

function abrirModalVencimientos() {
    new bootstrap.Modal(document.getElementById('modalVencimientos')).show();
}

// Mostrar loading
function mostrarLoading() {
    document.getElementById('loading').classList.remove('d-none');
    document.getElementById('contenidoPrincipal').classList.add('d-none');
}

// Ocultar loading
function ocultarLoading() {
    document.getElementById('loading').classList.add('d-none');
    document.getElementById('contenidoPrincipal').classList.remove('d-none');
    document.getElementById('catNav')?.classList.remove('d-none');
    document.getElementById('busquedaWrap')?.classList.remove('d-none');
}

// Mostrar error
function mostrarError(mensaje) {
    const alertContainer = document.getElementById('alertContainer');
    alertContainer.innerHTML = `
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}

// ============================================================
// ABM Conceptos
// ============================================================

const CONCEPTOS_API_URL = 'api/conceptos_api.php';
const CATEGORIAS_API_URL = 'api/categorias_api.php';

async function abrirModalConceptos() {
    const modal = new bootstrap.Modal(document.getElementById('modalConceptos'));
    modal.show();
    await Promise.all([cargarConceptosModal(), cargarCategoriasModal()]);
}

async function cargarConceptosModal() {
    try {
        const [respConceptos, respCategorias] = await Promise.all([
            fetch(CONCEPTOS_API_URL),
            fetch(CATEGORIAS_API_URL)
        ]);
        const resC   = await respConceptos.json();
        const resCat = await respCategorias.json();
        if (!resC.success) throw new Error(resC.message);

        app.categorias = resCat.success ? resCat.data : [];

        const gastos = resC.data.filter(c => c.tipo === 'gasto');
        renderizarListaConceptos('listaGastos', gastos);
        poblarSelectCategorias();
    } catch (error) {
        mostrarError('Error al cargar conceptos: ' + error.message);
    }
}

// Poblar todos los <select> de categorías con la lista actual de app.categorias
function poblarSelectCategorias() {
    const opciones = ['<option value="">— Sin categoría —</option>',
        ...app.categorias.map(c =>
            `<option value="${c.id}" style="color:${c.color}">${c.icono ? '● ' : ''}${c.nombre}</option>`
        )
    ].join('');

    ['nuevoCategoria'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { const val = el.value; el.innerHTML = opciones; el.value = val; }
    });

    // También los selects de edición inline (edit-categoria-N)
    document.querySelectorAll('[id^="edit-categoria-"]').forEach(el => {
        const val = el.value;
        el.innerHTML = opciones;
        el.value = val;
    });
}

function renderizarListaConceptos(containerId, conceptos) {
    const container = document.getElementById(containerId);
    if (conceptos.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-2">Sin conceptos.</p>';
        return;
    }

    const opcionesCat = ['<option value="">— Sin cat. —</option>',
        ...app.categorias.map(cat =>
            `<option value="${cat.id}">${cat.nombre}</option>`
        )
    ].join('');

    const lista = document.createElement('div');
    lista.className = 'concepto-lista';

    conceptos.forEach(c => {
        const activo     = c.activo == 1;
        const multiples  = c.permite_multiples == 1;
        const esUSD      = (c.moneda || 'ARS') === 'USD';
        const catBadge   = c.categoria_id
            ? `<span class="badge rounded-pill" style="background:${c.categoria_color};color:#fff;font-size:0.68rem">${c.categoria_nombre}</span>`
            : '';

        const item = document.createElement('div');
        item.className = 'concepto-item';
        item.id = `fila-concepto-${c.id}`;
        item.dataset.nombre = c.nombre.toLowerCase();

        item.innerHTML = `
            <!-- Vista lectura -->
            <div class="concepto-ver">
                <div class="concepto-ver-main">
                    <span class="concepto-ver-nombre ${!activo ? 'text-muted text-decoration-line-through' : ''}">${c.nombre}</span>
                    <div class="concepto-ver-meta">
                        ${catBadge}
                        <span class="concepto-meta-chip">Ord: ${c.orden}</span>
                        <span class="badge ${multiples ? 'bg-info' : 'bg-secondary bg-opacity-25 text-secondary border'}" style="font-size:0.68rem">
                            ${multiples ? 'Multi' : 'Único'}
                        </span>
                        <span class="badge ${esUSD ? 'text-bg-warning' : 'bg-secondary bg-opacity-25 text-secondary border'}" style="font-size:0.68rem">
                            ${esUSD ? 'USD' : 'ARS'}
                        </span>
                        <span class="badge ${activo ? 'bg-success' : 'bg-secondary'}" style="font-size:0.68rem">
                            ${activo ? 'Activo' : 'Inactivo'}
                        </span>
                    </div>
                </div>
                <div class="concepto-ver-btns">
                    <button class="btn btn-outline-primary btn-sm" title="Editar" onclick="editarConcepto(${c.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn ${activo ? 'btn-outline-warning' : 'btn-outline-success'} btn-sm"
                        title="${activo ? 'Desactivar' : 'Activar'}"
                        onclick="toggleActivoConcepto(${c.id}, ${activo ? 0 : 1})">
                        <i class="bi ${activo ? 'bi-eye-slash' : 'bi-eye'}"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" title="Eliminar"
                        onclick="eliminarConcepto(${c.id}, '${c.nombre.replace(/'/g, "\\'")}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>

            <!-- Vista edición -->
            <div class="concepto-edit d-none">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-4">
                        <label class="form-label form-label-sm mb-1">Nombre</label>
                        <input type="text" class="form-control form-control-sm"
                            id="edit-nombre-${c.id}" value="${c.nombre}">
                    </div>
                    <div class="col-8 col-sm-3">
                        <label class="form-label form-label-sm mb-1">Categoría</label>
                        <select class="form-select form-select-sm" id="edit-categoria-${c.id}">
                            ${opcionesCat}
                        </select>
                    </div>
                    <div class="col-4 col-sm-2">
                        <label class="form-label form-label-sm mb-1">Orden</label>
                        <input type="number" class="form-control form-control-sm text-center"
                            id="edit-orden-${c.id}" value="${c.orden}" min="1">
                    </div>
                    <div class="col-6 col-sm-1 d-flex flex-column align-items-center">
                        <label class="form-label form-label-sm mb-1">Multi</label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="edit-multiples-${c.id}" ${multiples ? 'checked' : ''}>
                        </div>
                    </div>
                    <div class="col-6 col-sm-1">
                        <label class="form-label form-label-sm mb-1">Moneda</label>
                        <select class="form-select form-select-sm" id="edit-moneda-${c.id}"
                                onchange="actualizarCtaDefEdit(${c.id})">
                            <option value="ARS" ${!esUSD ? 'selected' : ''}>ARS</option>
                            <option value="USD" ${esUSD ? 'selected' : ''}>USD</option>
                        </select>
                    </div>
                    <div class="col-6 col-sm-2">
                        <label class="form-label form-label-sm mb-1">Cuenta default</label>
                        <select class="form-select form-select-sm" id="edit-cuentadef-${c.id}"></select>
                    </div>
                    <div class="col-6 col-sm-2 d-flex gap-1 justify-content-end align-items-end">
                        <button class="btn btn-success btn-sm flex-fill" onclick="guardarEdicionConcepto(${c.id})">
                            <i class="bi bi-check-lg"></i> Guardar
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="cancelarEdicionConcepto(${c.id})">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        if (c.categoria_id) {
            const sel = item.querySelector(`#edit-categoria-${c.id}`);
            if (sel) sel.value = c.categoria_id;
        }

        // Poblar cuentas filtradas por moneda del concepto
        const selCtaDef = item.querySelector(`#edit-cuentadef-${c.id}`);
        if (selCtaDef) {
            const mon = c.moneda || 'ARS';
            selCtaDef.innerHTML = '<option value="">— Sin cuenta —</option>' +
                (app.cuentas || []).filter(cu => (cu.moneda || 'ARS') === mon)
                    .map(cu => `<option value="${cu.id}">${cu.nombre}</option>`).join('');
            if (c.cuenta_id_default) selCtaDef.value = c.cuenta_id_default;
        }

        lista.appendChild(item);
    });

    container.innerHTML = '';
    container.appendChild(lista);

    // Re-aplicar filtro activo si el buscador tiene texto
    const inputId = containerId === 'listaGastos' ? 'buscarGastos' : null;
    const q = inputId ? (document.getElementById(inputId)?.value || '') : '';
    if (q) filtrarConceptos(containerId, q);
}

function editarConcepto(id) {
    const fila = document.getElementById(`fila-concepto-${id}`);
    fila.querySelector('.concepto-ver').classList.add('d-none');
    fila.querySelector('.concepto-edit').classList.remove('d-none');
    document.getElementById(`edit-nombre-${id}`).focus();
}

function cancelarEdicionConcepto(id) {
    const fila = document.getElementById(`fila-concepto-${id}`);
    fila.querySelector('.concepto-ver').classList.remove('d-none');
    fila.querySelector('.concepto-edit').classList.add('d-none');
}

async function guardarEdicionConcepto(id) {
    const nombre = document.getElementById(`edit-nombre-${id}`).value.trim();
    const orden  = document.getElementById(`edit-orden-${id}`).value;
    const permite_multiples = document.getElementById(`edit-multiples-${id}`).checked ? 1 : 0;
    const catEl  = document.getElementById(`edit-categoria-${id}`);
    const categoria_id = catEl && catEl.value !== '' ? parseInt(catEl.value) : null;
    const moneda = document.getElementById(`edit-moneda-${id}`)?.value || 'ARS';
    const ctaDef = document.getElementById(`edit-cuentadef-${id}`)?.value || null;

    if (!nombre) {
        mostrarError('El nombre no puede estar vacío.');
        return;
    }

    try {
        const response = await fetch(CONCEPTOS_API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, nombre, orden, permite_multiples, categoria_id, moneda, cuenta_id_default: ctaDef || null })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Concepto actualizado', 'success');
        await cargarConceptosModal();
        await cargarDatos();
    } catch (error) {
        mostrarError('Error al guardar: ' + error.message);
    }
}

async function toggleActivoConcepto(id, nuevoActivo) {
    const accion = nuevoActivo ? 'activar' : 'desactivar';
    if (!confirm(`¿Seguro que querés ${accion} este concepto?`)) return;

    try {
        const response = await fetch(CONCEPTOS_API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, activo: nuevoActivo })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast(`Concepto ${nuevoActivo ? 'activado' : 'desactivado'}`, 'success');
        await cargarConceptosModal();
        await cargarDatos();
    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

async function eliminarConcepto(id, nombre) {
    if (!confirm(`¿Eliminar el concepto "${nombre}"?\n\nSolo es posible si el importe está en 0 en todos los meses.`)) return;

    try {
        const response = await fetch(CONCEPTOS_API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (!result.success) {
            mostrarError(result.message);
            return;
        }

        mostrarToast('Concepto eliminado', 'success');
        await cargarConceptosModal();
        await cargarDatos();
    } catch (error) {
        mostrarError('Error al eliminar: ' + error.message);
    }
}

function actualizarCtaDefNuevo() {
    const moneda = document.getElementById('nuevoMoneda')?.value || 'ARS';
    const sel = document.getElementById('nuevoCtaDef');
    if (!sel) return;
    sel.innerHTML = '<option value="">— Sin cuenta —</option>' +
        (app.cuentas || []).filter(c => (c.moneda || 'ARS') === moneda)
            .map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
}

function actualizarCtaDefEdit(id) {
    const moneda = document.getElementById(`edit-moneda-${id}`)?.value || 'ARS';
    const sel = document.getElementById(`edit-cuentadef-${id}`);
    if (!sel) return;
    const prev = sel.value;
    sel.innerHTML = '<option value="">— Sin cuenta —</option>' +
        (app.cuentas || []).filter(c => (c.moneda || 'ARS') === moneda)
            .map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
    sel.value = prev;
}

function mostrarFormNuevo(tipo) {
    const form = document.getElementById('formNuevoConcepto');
    document.getElementById('nuevoTipo').value = tipo;
    document.getElementById('nuevoNombre').value = '';
    document.getElementById('nuevoOrden').value = '';
    document.getElementById('nuevoPermiteMultiples').checked = false;
    document.getElementById('nuevoCategoria').value = '';
    document.getElementById('nuevoMoneda').value = 'ARS';
    actualizarCtaDefNuevo();
    form.classList.remove('d-none');
    document.getElementById('nuevoNombre').focus();
}

function cancelarNuevoConcepto() {
    document.getElementById('formNuevoConcepto').classList.add('d-none');
}

async function guardarNuevoConcepto() {
    const nombre = document.getElementById('nuevoNombre').value.trim();
    const tipo   = document.getElementById('nuevoTipo').value;
    const orden  = document.getElementById('nuevoOrden').value;
    const permite_multiples = document.getElementById('nuevoPermiteMultiples').checked ? 1 : 0;
    const catVal = document.getElementById('nuevoCategoria').value;
    const categoria_id = catVal !== '' ? parseInt(catVal) : null;
    const moneda = document.getElementById('nuevoMoneda')?.value || 'ARS';
    const ctaDef = document.getElementById('nuevoCtaDef')?.value || null;

    if (!nombre) {
        mostrarError('El nombre no puede estar vacío.');
        return;
    }

    try {
        const response = await fetch(CONCEPTOS_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, tipo, orden: orden || undefined, permite_multiples, categoria_id, moneda, cuenta_id_default: ctaDef || null })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Concepto creado correctamente', 'success');
        cancelarNuevoConcepto();
        await cargarConceptosModal();
        await cargarDatos();

        const tabBtn = document.getElementById('tab-gastos-btn');
        if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    } catch (error) {
        mostrarError('Error al crear: ' + error.message);
    }
}

// ============================================================
// ABM Categorías
// ============================================================

async function cargarCategoriasModal() {
    try {
        const response = await fetch(CATEGORIAS_API_URL);
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
        app.categorias = result.data;
        renderizarListaCategorias(result.data);
        poblarSelectCategorias();
    } catch (error) {
        mostrarError('Error al cargar categorías: ' + error.message);
    }
}

function renderizarListaCategorias(categorias) {
    const container = document.getElementById('listaCategorias');
    if (!container) return;

    if (categorias.length === 0) {
        container.innerHTML = '<p class="text-muted text-center py-2">Sin categorías. Creá la primera.</p>';
        return;
    }

    const tabla = document.createElement('table');
    tabla.className = 'table table-sm table-hover align-middle mb-0';
    tabla.innerHTML = `
        <thead class="table-light">
            <tr>
                <th style="width:44px"></th>
                <th>Nombre</th>
                <th class="text-center" style="width:60px">Orden</th>
                <th style="width:80px"></th>
            </tr>
        </thead>
        <tbody id="tbody-categorias"></tbody>
    `;

    const tbody = tabla.querySelector('tbody');
    let draggingEl = null;

    categorias.forEach(cat => {
        const tr = document.createElement('tr');
        tr.dataset.nombre = cat.nombre.toLowerCase();
        tr.id = `fila-categoria-${cat.id}`;
        tr.dataset.catId = cat.id;
        tr.draggable = true;
        tr.innerHTML = `
            <td style="vertical-align:middle">
                <span class="cat-drag-handle" title="Arrastrar para reordenar"><i class="bi bi-grip-vertical"></i></span>
                <span class="categoria-dot d-inline-block ms-1" style="background:${cat.color}; width:10px; height:10px; border-radius:50%; vertical-align:middle"></span>
            </td>
            <td colspan="2">
                <span class="cat-nombre-texto d-flex justify-content-between align-items-center">
                    <span>
                        ${cat.icono ? `<i class="bi ${cat.icono} me-1" style="color:${cat.color}"></i>` : ''}
                        ${cat.nombre}
                    </span>
                    <span class="cat-orden-texto text-muted small me-1">${cat.orden}</span>
                </span>
                <div class="cat-nombre-edit d-none d-flex flex-wrap gap-1 align-items-center">
                    ${_cifraColorPickerHtml(`edit-cat-color-${cat.id}`, cat.color)}
                    <input type="text" id="edit-cat-nombre-${cat.id}" class="form-control form-control-sm flex-grow-1" style="min-width:80px" value="${cat.nombre}">
                    <input type="text" id="edit-cat-icono-${cat.id}" class="form-control form-control-sm flex-shrink-0" style="width:90px" placeholder="bi-house-fill" value="${cat.icono || ''}">
                    <input type="number" id="edit-cat-orden-${cat.id}" class="form-control form-control-sm text-center flex-shrink-0" style="width:50px" value="${cat.orden}" min="1">
                    <input type="number" id="edit-cat-presupuesto-${cat.id}" class="form-control form-control-sm flex-shrink-0" style="width:90px" placeholder="Presup." title="Presupuesto mensual (opcional)" min="0" step="1000" value="${cat.presupuesto > 0 ? cat.presupuesto : ''}">
                    <button class="btn btn-success btn-sm flex-shrink-0" onclick="guardarEdicionCategoria(${cat.id})"><i class="bi bi-check-lg"></i></button>
                    <button class="btn btn-outline-secondary btn-sm flex-shrink-0" onclick="cancelarEdicionCategoria(${cat.id})"><i class="bi bi-x-lg"></i></button>
                </div>
            </td>
            <td class="text-end" style="vertical-align:middle">
                <div class="cat-acciones-ver d-flex gap-1 justify-content-end">
                    <button class="btn btn-outline-primary btn-sm" title="Editar" onclick="editarCategoria(${cat.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-outline-danger btn-sm" title="Eliminar" onclick="eliminarCategoria(${cat.id}, '${cat.nombre.replace(/'/g, "\\'")}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;

        tr.addEventListener('dragstart', (e) => {
            draggingEl = tr;
            e.dataTransfer.effectAllowed = 'move';
            setTimeout(() => tr.classList.add('cat-dragging'), 0);
        });

        tr.addEventListener('dragend', () => {
            tr.classList.remove('cat-dragging');
            tbody.querySelectorAll('tr').forEach(r => r.classList.remove('cat-drag-over-top', 'cat-drag-over-bottom'));
            draggingEl = null;
        });

        tr.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggingEl || draggingEl === tr) return;
            const mid = tr.getBoundingClientRect().top + tr.getBoundingClientRect().height / 2;
            tbody.querySelectorAll('tr').forEach(r => r.classList.remove('cat-drag-over-top', 'cat-drag-over-bottom'));
            tr.classList.add(e.clientY < mid ? 'cat-drag-over-top' : 'cat-drag-over-bottom');
        });

        tr.addEventListener('drop', async (e) => {
            e.preventDefault();
            if (!draggingEl || draggingEl === tr) return;
            const mid = tr.getBoundingClientRect().top + tr.getBoundingClientRect().height / 2;
            tbody.insertBefore(draggingEl, e.clientY < mid ? tr : tr.nextSibling);
            tr.classList.remove('cat-drag-over-top', 'cat-drag-over-bottom');
            await guardarOrdenCategorias(tbody);
        });

        tbody.appendChild(tr);
    });

    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive';
    wrapper.appendChild(tabla);
    container.innerHTML = '';
    container.appendChild(wrapper);

    // Re-aplicar filtro activo si el buscador tiene texto
    const q = document.getElementById('buscarCategorias')?.value || '';
    if (q) filtrarCategorias(q);
}

function filtrarConceptos(containerId, query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll(`#${containerId} .concepto-item`).forEach(item => {
        item.style.display = (!q || item.dataset.nombre?.includes(q)) ? '' : 'none';
    });
}

function filtrarCategorias(query) {
    const q = query.trim().toLowerCase();
    document.querySelectorAll('#tbody-categorias tr').forEach(tr => {
        tr.style.display = (!q || tr.dataset.nombre?.includes(q)) ? '' : 'none';
    });
}

async function guardarOrdenCategorias(tbody) {
    const rows = Array.from(tbody.querySelectorAll('tr[data-cat-id]'));
    const updates = rows.map((tr, i) => ({ id: parseInt(tr.dataset.catId), orden: i + 1 }));

    // Actualizar visualmente los números de orden
    updates.forEach(({ id, orden }) => {
        const span = document.querySelector(`#fila-categoria-${id} .cat-orden-texto`);
        if (span) span.textContent = orden;
    });

    try {
        await Promise.all(updates.map(({ id, orden }) =>
            fetch(CATEGORIAS_API_URL, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, orden })
            })
        ));
        mostrarToast('Orden guardado', 'success');
        await cargarDatos();
    } catch (error) {
        mostrarError('Error al guardar orden: ' + error.message);
    }
}

function mostrarFormNuevaCategoria() {
    document.getElementById('catNombre').value       = '';
    document.getElementById('catColor').value        = '#2563EB';
    document.getElementById('catIcono').value        = '';
    document.getElementById('catOrden').value        = '';
    document.getElementById('catPresupuesto').value  = '';
    document.getElementById('formNuevaCategoria').classList.remove('d-none');
    document.getElementById('catNombre').focus();
}

function cancelarNuevaCategoria() {
    document.getElementById('formNuevaCategoria').classList.add('d-none');
}

async function guardarNuevaCategoria() {
    const nombre      = document.getElementById('catNombre').value.trim();
    const color       = document.getElementById('catColor').value;
    const icono       = document.getElementById('catIcono').value.trim();
    const orden       = document.getElementById('catOrden').value;
    const presupuesto = document.getElementById('catPresupuesto').value;

    if (!nombre) {
        mostrarError('El nombre de la categoría no puede estar vacío.');
        return;
    }

    try {
        const response = await fetch(CATEGORIAS_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre, color, icono: icono || '', orden: orden || undefined, presupuesto: presupuesto !== '' ? parseFloat(presupuesto) : null })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Categoría creada', 'success');
        cancelarNuevaCategoria();
        await cargarCategoriasModal();
    } catch (error) {
        mostrarError('Error al crear categoría: ' + error.message);
    }
}

function editarCategoria(id) {
    const fila = document.getElementById(`fila-categoria-${id}`);
    fila.querySelectorAll('.cat-nombre-texto, .cat-acciones-ver').forEach(el => el.classList.add('d-none'));
    fila.querySelector('.cat-nombre-edit').classList.remove('d-none');
    document.getElementById(`edit-cat-nombre-${id}`).focus();
}

function cancelarEdicionCategoria(id) {
    const fila = document.getElementById(`fila-categoria-${id}`);
    fila.querySelectorAll('.cat-nombre-texto, .cat-acciones-ver').forEach(el => el.classList.remove('d-none'));
    fila.querySelector('.cat-nombre-edit').classList.add('d-none');
}

async function guardarEdicionCategoria(id) {
    const nombre      = document.getElementById(`edit-cat-nombre-${id}`).value.trim();
    const color       = document.getElementById(`edit-cat-color-${id}`).value;
    const icono       = document.getElementById(`edit-cat-icono-${id}`).value.trim();
    const orden       = document.getElementById(`edit-cat-orden-${id}`).value;
    const presupuesto = document.getElementById(`edit-cat-presupuesto-${id}`).value;

    if (!nombre) {
        mostrarError('El nombre no puede estar vacío.');
        return;
    }

    try {
        const response = await fetch(CATEGORIAS_API_URL, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, nombre, color, icono, orden: parseInt(orden) || 0, presupuesto: presupuesto !== '' ? parseFloat(presupuesto) : null })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Categoría actualizada', 'success');
        await cargarCategoriasModal();
        await cargarDatos();
    } catch (error) {
        mostrarError('Error al guardar: ' + error.message);
    }
}

async function eliminarCategoria(id, nombre) {
    if (!confirm(`¿Eliminar la categoría "${nombre}"?\nLos conceptos asociados quedarán sin categoría.`)) return;

    try {
        const response = await fetch(CATEGORIAS_API_URL, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Categoría eliminada', 'success');
        await cargarCategoriasModal();
        await cargarDatos();
    } catch (error) {
        mostrarError('Error al eliminar: ' + error.message);
    }
}

// ============================================================
// Cuentas bancarias
// ============================================================

function crearSelectorCuenta(registroId, cuentaId, moneda = 'ARS') {
    const wrap = document.createElement('div');
    wrap.className = 'cuenta-wrap';

    const dot = document.createElement('span');
    dot.className = 'cuenta-dot';
    const cuentaActual = app.cuentas.find(c => c.id == cuentaId);
    dot.style.background = cuentaActual ? cuentaActual.color : '#d1d5db';

    const sel = document.createElement('select');
    sel.className = 'cuenta-select';

    const optNone = new Option('Cuenta…', '');
    sel.appendChild(optNone);
    // Solo mostrar cuentas de la misma moneda que el concepto
    app.cuentas.filter(c => (c.moneda || 'ARS') === moneda).forEach(c => {
        const opt = new Option(c.nombre, c.id);
        if (c.id == cuentaId) opt.selected = true;
        sel.appendChild(opt);
    });

    sel.addEventListener('change', async () => {
        const nuevaId = sel.value ? parseInt(sel.value) : null;
        const nuevaCuenta = app.cuentas.find(c => c.id == nuevaId);
        dot.style.background = nuevaCuenta ? nuevaCuenta.color : '#d1d5db';
        await guardarCuentaRegistro(registroId, nuevaId);
    });

    wrap.appendChild(dot);
    wrap.appendChild(sel);
    return wrap;
}

async function guardarCuentaRegistro(registroId, cuentaId) {
    try {
        const response = await fetch(API_URL, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registro_id: registroId, cuenta_id: cuentaId })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        // Actualizar app.datos en memoria para que el modal no revierta la selección
        if (app.datos?.conceptos) {
            const c = app.datos.conceptos.find(c => c.registro_id == registroId);
            if (c) c.cuenta_id = cuentaId ? parseInt(cuentaId) : null;
        }
        mostrarToast('Cuenta guardada', 'success');
        await cargarCuentas();
    } catch (error) {
        mostrarError('Error al guardar cuenta: ' + error.message);
    }
}

async function cargarCuentas() {
    try {
        const resp = await fetch(`api/cuentas_api.php?mes=${app.mesActual}&anio=${app.anioActual}`);
        const result = await resp.json();
        if (result.success) {
            app.cuentas = result.data;
            renderizarCuentas();
            renderizarCardCuentasHome();
            renderizarDatos(); // Actualizar topbar con nuevo saldo real
        }
    } catch (_) {}
}

function renderizarCardCuentasHome() {
    const el = document.getElementById('totalCuentasTopbar');
    if (!el || !app.cuentas || !app.cuentas.length) return;
    const totalARS = app.cuentas.filter(c => (c.moneda || 'ARS') === 'ARS').reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);
    el.textContent = formatearMoneda(totalARS);
}

function renderizarCuentas() {
    const contenedor = document.getElementById('cardCuentas');
    if (!contenedor) return;
    if (!app.cuentas || app.cuentas.length === 0) {
        contenedor.innerHTML = '';
        return;
    }

    const totalARS  = app.cuentas.filter(c => (c.moneda || 'ARS') === 'ARS').reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);
    const totalUSD  = app.cuentas.filter(c => c.moneda === 'USD').reduce((s, c) => s + parseFloat(c.saldo_actual || 0), 0);

    const tipoLabel = {
        cuenta_corriente: 'Cta. corriente',
        caja_ahorro:      'Caja de ahorro',
        billetera:        'Billetera virtual'
    };

    const filasHtml = app.cuentas.map(c => {
        const saldo    = parseFloat(c.saldo_actual || 0);
        const fechaStr = c.fecha_saldo ? formatearFechaCorta(c.fecha_saldo) : '—';

        const monedaCuenta = c.moneda || 'ARS';
        const badgeUSD = monedaCuenta === 'USD'
            ? '<span class="badge text-bg-warning ms-1" style="font-size:0.6rem;vertical-align:middle">USD</span>'
            : '';

        return `
        <div class="cuenta-item">
            <div class="cuenta-item-header">
                <div class="d-flex align-items-center gap-2">
                    <span class="cuenta-dot-lg" style="background:${c.color}"></span>
                    <div>
                        <span class="cuenta-nombre">${c.nombre}${badgeUSD}</span>
                        <span class="cuenta-tipo">${tipoLabel[c.tipo] || c.tipo}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="text-end">
                        <div class="cuenta-stat-valor">${formatearMoneda(saldo, monedaCuenta)}</div>
                        <div class="cuenta-stat-fecha">${fechaStr}</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-ghost-muted btn-sm" title="Transferir" onclick="abrirModalTransferencia(${c.id})">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                        ${c.tipo !== 'billetera' ? `<button class="btn btn-ghost-muted btn-sm" title="Extracción Efectivo" onclick="registrarExtraccion(${c.id})"><i class="bi bi-cash-stack"></i></button>` : ''}
                        <button class="btn btn-ghost-muted btn-sm" title="Actualizar saldo" onclick="actualizarSaldoCuenta(${c.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');

    const consolidadoUSD = totalUSD > 0
        ? `<div class="cuenta-consolidado-row">
               <span class="cuenta-consolidado-label">Total en cuentas USD</span>
               <span class="cuenta-consolidado-valor">${formatearMoneda(totalUSD, 'USD')}</span>
           </div>` : '';

    contenedor.innerHTML = `
    <div class="cuenta-lista">${filasHtml}</div>
    <div class="cuenta-consolidado">
        <div class="cuenta-consolidado-row">
            <span class="cuenta-consolidado-label">Total en cuentas ARS</span>
            <span class="cuenta-consolidado-valor">${formatearMoneda(totalARS)}</span>
        </div>
        ${consolidadoUSD}
    </div>`;

    contenedor.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el =>
        new bootstrap.Tooltip(el, { trigger: 'hover focus' })
    );
}

function mostrarFormNuevaCuenta() {
    const contenedor = document.getElementById('cardCuentas');
    if (contenedor.querySelector('#formNuevaCuenta')) return; // ya abierto

    const form = document.createElement('div');
    form.id = 'formNuevaCuenta';
    form.className = 'nueva-cuenta-form';
    form.innerHTML = `
        <div class="nueva-cuenta-titulo">Nueva cuenta</div>
        <div class="nueva-cuenta-campos">
            <input type="text" id="nc-nombre" class="form-control form-control-sm" placeholder="Nombre *">
            <input type="text" id="nc-banco"  class="form-control form-control-sm" placeholder="Banco / entidad">
            <select id="nc-tipo" class="form-select form-select-sm">
                <option value="caja_ahorro">Caja de ahorro</option>
                <option value="cuenta_corriente">Cuenta corriente</option>
                <option value="billetera">Billetera virtual</option>
            </select>
            <div class="nueva-cuenta-color">
                <label class="nueva-cuenta-color-label">Color</label>
                ${_cifraColorPickerHtml('nc-color', '#6b7280')}
            </div>
            <input type="text" inputmode="decimal" id="nc-saldo" class="form-control form-control-sm" placeholder="Saldo inicial (0)">
        </div>
        <div class="nueva-cuenta-acciones">
            <button class="btn btn-sm btn-secondary" onclick="document.getElementById('formNuevaCuenta').remove()">Cancelar</button>
            <button class="btn btn-sm btn-primary" onclick="guardarNuevaCuenta()">Guardar</button>
        </div>`;
    contenedor.appendChild(form);
    document.getElementById('nc-nombre').focus();
}

async function guardarNuevaCuenta() {
    const nombre = document.getElementById('nc-nombre').value.trim();
    if (!nombre) { mostrarError('El nombre es obligatorio'); return; }

    const payload = {
        nombre,
        banco:        document.getElementById('nc-banco').value.trim(),
        tipo:         document.getElementById('nc-tipo').value,
        color:        document.getElementById('nc-color').value,
        saldo_actual: parsearImporte(document.getElementById('nc-saldo').value),
    };

    try {
        const resp = await fetch('api/cuentas_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);
        mostrarToast('Cuenta creada', 'success');
        await cargarCuentas();
        document.getElementById('formNuevaCuenta')?.remove();
    } catch (e) {
        mostrarError('Error al crear cuenta: ' + e.message);
    }
}

function abrirModalTransferencia(cuentaOrigenId = null) {
    const sel1 = document.getElementById('transfOrigen');
    const sel2 = document.getElementById('transfDestino');

    const options = app.cuentas.map(c =>
        `<option value="${c.id}">${c.nombre}</option>`
    ).join('');
    sel1.innerHTML = options;
    sel2.innerHTML = options;

    if (cuentaOrigenId) sel1.value = cuentaOrigenId;
    // Pre-seleccionar destino distinto al origen
    const otra = app.cuentas.find(c => c.id != (cuentaOrigenId || app.cuentas[0]?.id));
    if (otra) sel2.value = otra.id;

    document.getElementById('transfImporte').value    = '';
    document.getElementById('transfDescripcion').value = '';

    new bootstrap.Modal(document.getElementById('modalTransferencia')).show();
}

async function realizarTransferencia() {
    const origen      = parseInt(document.getElementById('transfOrigen').value);
    const destino     = parseInt(document.getElementById('transfDestino').value);
    const importe     = parsearImporte(document.getElementById('transfImporte').value);
    const descripcion = document.getElementById('transfDescripcion').value.trim();

    if (origen === destino) { mostrarError('Las cuentas deben ser distintas'); return; }
    if (!importe || importe <= 0) { mostrarError('Importe inválido'); return; }

    try {
        const resp = await fetch('api/movimientos_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo: 'transferencia', cuenta_origen_id: origen, cuenta_destino_id: destino, importe, descripcion })
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        bootstrap.Modal.getInstance(document.getElementById('modalTransferencia'))?.hide();
        mostrarToast('Transferencia realizada', 'success');
        await cargarCuentas();
    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

async function registrarExtraccion(cuentaId) {
    const cuenta = app.cuentas.find(c => c.id == cuentaId);
    if (!cuenta) return;

    const inputStr = prompt(`Extracción Efectivo — ${cuenta.nombre}\nImporte:`);
    if (inputStr === null) return;

    const importe = parsearImporte(inputStr);
    if (!importe || importe <= 0) { mostrarError('Importe inválido'); return; }

    try {
        const resp = await fetch('api/movimientos_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tipo: 'extraccion',
                cuenta_id: cuentaId,
                importe,
                fecha: new Date().toISOString().split('T')[0]
            })
        });
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast(`Extracción de ${formatearMoneda(importe)} registrada`, 'success');
        await cargarDatos();
    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

function abrirModalGastoRapido() {
    // Poblar selector con conceptos múltiples de gasto activos
    const sel = document.getElementById('grConcepto');
    const conceptos = (app.datos?.conceptos || [])
        .filter(c => c.tipo === 'gasto' && c.permite_multiples == 1 && c.activo != 0);
    sel.innerHTML = conceptos.length
        ? conceptos.map(c => `<option value="${c.id}" data-default-cuenta="${c.cuenta_id_default || ''}" data-moneda="${c.moneda || 'ARS'}">${c.nombre}</option>`).join('')
        : '<option value="">— Sin conceptos disponibles —</option>';

    const selCuenta = document.getElementById('grCuenta');
    const grImporte = document.getElementById('grImporte');

    // Actualiza cuentas e importe según moneda del concepto seleccionado
    const actualizarPorConcepto = () => {
        const opt = sel.options[sel.selectedIndex];
        if (!opt) return;
        const moneda = opt.dataset.moneda || 'ARS';
        selCuenta.innerHTML = '<option value="">Seleccioná una cuenta…</option>' +
            app.cuentas.filter(c => (c.moneda || 'ARS') === moneda)
                .map(c => `<option value="${c.id}">${c.nombre}</option>`).join('');
        if (opt.dataset.defaultCuenta) selCuenta.value = opt.dataset.defaultCuenta;
        grImporte.placeholder = moneda === 'USD' ? 'U$D 0,00' : '$ 0,00';
    };

    actualizarPorConcepto();
    sel.onchange = actualizarPorConcepto;

    // Fecha por defecto: hoy
    document.getElementById('grFecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('grImporte').value = '';
    document.getElementById('grDescripcion').value = '';

    new bootstrap.Modal(document.getElementById('modalGastoRapido')).show();
    setTimeout(() => document.getElementById('grImporte').focus(), 400);
}

async function guardarGastoRapido() {
    const conceptoId = parseInt(document.getElementById('grConcepto').value);
    const cuentaId   = parseInt(document.getElementById('grCuenta').value) || null;
    const fecha      = document.getElementById('grFecha').value;
    const importe    = parsearImporte(document.getElementById('grImporte').value);
    const desc       = document.getElementById('grDescripcion').value.trim();

    if (!conceptoId) { mostrarError('Seleccioná un concepto.'); return; }
    if (!cuentaId)   { mostrarError('Seleccioná una cuenta.'); return; }
    if (!fecha)      { mostrarError('Ingresá una fecha.'); return; }
    if (importe <= 0){ mostrarError('El importe debe ser mayor a 0.'); return; }

    try {
        const res = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                concepto_id: conceptoId,
                cuenta_id: cuentaId,
                mes: app.mesActual,
                anio: app.anioActual,
                fecha,
                importe,
                observaciones: desc || null
            })
        });
        const result = await res.json();
        if (result.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalGastoRapido')).hide();
            mostrarToast('Gasto agregado', 'success');
            await cargarDatos();
        } else {
            mostrarError('Error: ' + result.message);
        }
    } catch (e) {
        mostrarError('Error de conexión.');
    }
}

const _CAT_ICONO = {
    'Alimentación': 'bi-bag-fill',
    'Transporte': 'bi-car-front-fill',
    'Vivienda': 'bi-house-fill',
    'Entretenimiento': 'bi-film',
    'Educación': 'bi-book-fill',
    'Salud': 'bi-heart-pulse-fill',
    'Servicios': 'bi-wrench-adjustable-circle-fill',
    'Compras': 'bi-bag-check-fill',
    'Viajes': 'bi-airplane-fill',
    'Otros': 'bi-tag-fill'
};

const _MOV_TIPO = {
    ingreso:       { label: 'Cobro',         icon: 'bi-arrow-down-circle', cls: 'mov-cobro'         },
    pago_gasto:    { label: 'Pago',          icon: 'bi-arrow-up-circle',   cls: 'mov-pago'          },
    transferencia: { label: 'Transferencia', icon: 'bi-arrow-left-right',  cls: 'mov-transferencia' },
    extraccion:    { label: 'Extracción',    icon: 'bi-cash-stack',        cls: 'mov-extraccion'    },
};

let _movData = [];

async function abrirModalMovimientos() {
    const modal = new bootstrap.Modal(document.getElementById('modalMovimientos'));
    modal.show();
    await _cargarMovimientos();
}

async function _cargarMovimientos() {
    const body = document.getElementById('modalMovimientosBody');
    body.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    const elMesAnio = document.getElementById('movMesAnio');
    if (elMesAnio) elMesAnio.textContent = `${obtenerNombreMes(app.mesActual)} ${app.anioActual}`;

    try {
        const resp = await fetch(`api/movimientos_api.php?limit=200&mes=${app.mesActual}&anio=${app.anioActual}`);
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);
        _movData = result.data;
        _renderizarMovimientos('');
    } catch (error) {
        body.innerHTML = `<div class="alert alert-danger m-3">Error: ${error.message}</div>`;
    }
}

function _renderizarMovimientos(q) {
    q = _normalizar(q);
    const body    = document.getElementById('modalMovimientosBody');
    const btnClear = document.getElementById('btnLimpiarMov');
    if (btnClear) btnClear.classList.toggle('d-none', !q);

    const data = q ? _movData.filter(m => {
        const cuenta = m.cuenta_origen || m.cuenta_destino || '';
        const desc   = m.observaciones || m.descripcion || '';
        const tipo   = (_MOV_TIPO[m.tipo]?.label || m.tipo);
        return _normalizar(cuenta + ' ' + desc + ' ' + tipo).includes(q);
    }) : _movData;

    // Resumen
    const resumen = document.getElementById('movResumen');
    if (resumen) {
        const cobros   = data.filter(m => m.tipo === 'ingreso').reduce((s, m) => s + m.importe, 0);
        const pagos    = data.filter(m => m.tipo === 'pago_gasto').reduce((s, m) => s + m.importe, 0);
        const transf   = data.filter(m => m.tipo === 'transferencia').length;
        const items = [
            cobros > 0 ? `<span class="mov-res-chip mov-cobro"><i class="bi bi-arrow-down-circle me-1"></i>${formatearMoneda(cobros)}</span>` : '',
            pagos  > 0 ? `<span class="mov-res-chip mov-pago"><i class="bi bi-arrow-up-circle me-1"></i>${formatearMoneda(pagos)}</span>`   : '',
            transf > 0 ? `<span class="mov-res-chip mov-transferencia"><i class="bi bi-arrow-left-right me-1"></i>${transf} transf.</span>` : '',
        ].filter(Boolean);
        resumen.innerHTML = items.join('') || '<span class="text-muted">Sin movimientos</span>';
    }

    if (!data.length) {
        body.innerHTML = '<p class="text-center text-muted py-4">Sin resultados</p>';
        return;
    }

    // Agrupar por fecha
    const porFecha = {};
    data.forEach(m => {
        const fechaParte = (m.fecha || '').split('T')[0].split(' ')[0];
        if (!porFecha[fechaParte]) porFecha[fechaParte] = [];
        porFecha[fechaParte].push(m);
    });

    const html = Object.entries(porFecha).map(([fecha, movs]) => {
        const [yy, mm, dd] = fecha.split('-');
        const diasSemana = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        const diaSem = diasSemana[new Date(fecha + 'T12:00:00').getDay()] || '';
        const fechaLabel = fecha ? `${diaSem} ${dd}/${mm}/${(yy||'').slice(2)}` : '—';

        const filas = movs.map(m => {
            const t = _MOV_TIPO[m.tipo] || { label: m.tipo, icon: 'bi-circle', cls: '' };
            const cuentaStr = m.tipo === 'transferencia'
                ? `${m.cuenta_origen || '?'} → ${m.cuenta_destino || '?'}`
                : (m.cuenta_origen || m.cuenta_destino || '—');
            const desc = m.observaciones || m.descripcion || '';
            const esIngreso = m.tipo === 'ingreso';
            const importeStr = (esIngreso ? '+' : '−') + formatearMoneda(m.importe).replace('-','');

            return `
            <div class="mov-fila">
                <div class="mov-icono ${t.cls}"><i class="bi ${t.icon}"></i></div>
                <div class="mov-info">
                    <div class="mov-tipo-cuenta"><span class="mov-tipo-label">${t.label}</span><span class="mov-cuenta">${cuentaStr}</span></div>
                    ${desc ? `<div class="mov-desc">${desc}</div>` : ''}
                </div>
                <div class="mov-importe ${t.cls}">${importeStr}</div>
            </div>`;
        }).join('');

        return `
        <div class="mov-grupo">
            <div class="mov-fecha-header">${fechaLabel}</div>
            ${filas}
        </div>`;
    }).join('');

    body.innerHTML = html;
}

function limpiarBusquedaMov() {
    const input = document.getElementById('inputBusquedaMov');
    if (input) { input.value = ''; }
    _renderizarMovimientos('');
}

async function actualizarSaldoCuenta(cuentaId) {
    const cuenta = app.cuentas.find(c => c.id == cuentaId);
    if (!cuenta) return;

    const actual = parseFloat(cuenta.saldo_actual || 0);
    const input  = prompt(
        `Nuevo saldo de ${cuenta.nombre}:\n(Usá coma como decimal — ej: 125.000,50)`,
        actual > 0 ? String(actual).replace('.', ',') : ''
    );
    if (input === null) return;

    const saldo = parsearImporte(input);
    if (isNaN(saldo) || saldo < 0) {
        mostrarError('Importe inválido.');
        return;
    }

    try {
        const response = await fetch('api/cuentas_api.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: cuentaId, saldo_actual: saldo })
        });
        const result = await response.json();
        if (!result.success) throw new Error(result.message);
        mostrarToast(`Saldo de ${cuenta.nombre} actualizado`, 'success');
        await cargarCuentas();
    } catch (error) {
        mostrarError('Error al actualizar saldo: ' + error.message);
    }
}

// ============================================================
// Mostrar toast (notificación pequeña)
function mostrarToast(mensaje, tipo = 'success') {
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    const toastId = 'toast-' + Date.now();
    const iconClass = tipo === 'success' ? 'bi-check-circle-fill text-success' : 'bi-info-circle-fill text-info';

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi ${iconClass} me-2"></i>
                    ${mensaje}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 2000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', () => toastElement.remove());
}

// ============================================================
// VISTA ANUAL
// ============================================================

const ANUAL_API_URL = 'api/anual_api.php';

function abrirModalAnual() {
    new bootstrap.Modal(document.getElementById('modalAnual')).show();
    cargarVistaAnual(new Date().getFullYear());
}

async function cargarVistaAnual(anio) {
    const body = document.getElementById('modalAnualBody');
    body.innerHTML = '<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Cargando...</div>';
    try {
        const resp   = await fetch(`${ANUAL_API_URL}?anio=${anio}`);
        const result = await resp.json();
        if (!result.success) throw new Error(result.message);
        _renderizarTablaAnual(result.data);
    } catch (e) {
        body.innerHTML = `<div class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle me-2"></i>${e.message}</div>`;
    }
}

function _formatCompacto(v) {
    if (!v || v === 0) return '—';
    if (v >= 1000000) return '$\u00a0' + (v / 1000000).toFixed(1).replace('.', ',') + 'M';
    if (v >= 1000)    return '$\u00a0' + Math.round(v / 1000) + 'k';
    return '$\u00a0' + Math.round(v);
}

function _renderizarTablaAnual(data) {
    const body       = document.getElementById('modalAnualBody');
    const meses      = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const anioActual = new Date().getFullYear();
    const mesActual  = new Date().getMonth(); // 0-indexed
    const anio       = data.anio;

    const btnNext = anio >= anioActual
        ? `<button class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-chevron-right"></i></button>`
        : `<button class="btn btn-sm btn-outline-secondary" onclick="cargarVistaAnual(${anio + 1})"><i class="bi bi-chevron-right"></i></button>`;

    let html = `
    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
        <button class="btn btn-sm btn-outline-secondary" onclick="cargarVistaAnual(${anio - 1})"><i class="bi bi-chevron-left"></i></button>
        <span class="fw-bold">${anio}</span>
        ${btnNext}
    </div>
    <div class="anual-table-wrap">
    <table class="table table-sm anual-table mb-0">
    <thead><tr>
        <th class="anual-cat-col">Categoría</th>
        ${meses.map((m, i) => `<th class="anual-mes-col text-end${i === mesActual && anio === anioActual ? ' anual-mes-actual' : ''}">${m}</th>`).join('')}
        <th class="anual-mes-col anual-total-col text-end">Total</th>
    </tr></thead>
    <tbody>`;

    data.categorias.forEach(cat => {
        const maxVal = Math.max(...cat.meses.filter(v => v > 0), 0);
        html += `<tr>
            <td class="anual-cat-col"><span class="anual-cat-dot" style="background:${cat.color}"></span>${cat.nombre}</td>
            ${cat.meses.map((v, i) => {
                const cls = [
                    'text-end',
                    (i === mesActual && anio === anioActual) ? 'anual-mes-actual' : '',
                    (v > 0 && v === maxVal) ? 'anual-cel-max' : '',
                    v === 0 ? 'text-muted' : '',
                ].filter(Boolean).join(' ');
                return `<td class="${cls}">${_formatCompacto(v)}</td>`;
            }).join('')}
            <td class="text-end anual-total-col fw-medium">${_formatCompacto(cat.total)}</td>
        </tr>`;
    });

    html += `<tr class="anual-total-row">
        <td class="anual-cat-col fw-bold">Total</td>
        ${data.totales_mes.map((v, i) => {
            const cls = ['text-end', 'fw-bold', (i === mesActual && anio === anioActual) ? 'anual-mes-actual' : ''].filter(Boolean).join(' ');
            return `<td class="${cls}">${_formatCompacto(v)}</td>`;
        }).join('')}
        <td class="text-end anual-total-col fw-bold">${_formatCompacto(data.total_anual)}</td>
    </tr>`;

    html += `</tbody></table></div>`;
    body.innerHTML = html;
}

// ============================================================
// TARJETAS DE CRÉDITO
// ============================================================

const TARJETAS_API = 'api/tarjetas_api_v3.php';

// Helper para fetch a API con credenciales
function fetchAPI(url, options = {}) {
    return fetch(url, {
        credentials: 'include',
        ...options
    });
}
let tarjetasActuales = [];
let tarjetaSeleccionada = null;

// Abrir modal principal de tarjetas
function abrirModalTarjetas() {
    const modal = new bootstrap.Modal(document.getElementById('modalTarjetas'), { keyboard: false });
    modal.show();
    cargarTarjetas();
}

// Cargar y renderizar tarjetas
async function cargarTarjetas() {
    const body = document.getElementById('modalTarjetasBody');

    try {
        const resp = await fetchAPI(TARJETAS_API);
        const result = await resp.json();

        if (!result.success) throw new Error(result.message || 'Error al cargar tarjetas');

        tarjetasActuales = result.data || [];

        if (tarjetasActuales.length === 0) {
            body.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-inbox fs-1 text-muted mb-3" style="display:block"></i>
                    <p class="text-muted">Sin tarjetas registradas</p>
                </div>`;
            return;
        }

        // Renderizar tarjetas como cards
        let html = '<div class="container-fluid p-3"><div class="row g-3">';

        tarjetasActuales.forEach(t => {
            const porcentajeUso = t.limite_credito > 0 ? Math.round((t.deuda_comprometida / t.limite_credito) * 100) : 0;
            const colorUso = porcentajeUso > 80 ? 'danger' : porcentajeUso > 50 ? 'warning' : 'success';

            html += `
                <div class="col-12 col-md-6">
                    <div class="card h-100 tarj-card" style="border-left:4px solid #6366f1;cursor:pointer" onclick="abrirDetalleTarjeta(${t.id})">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="card-title mb-1">${t.nombre_tarjeta}</h6>
                                    <small class="text-muted">${t.banco}</small>
                                    ${t.ultimos_4 ? `<small class="text-muted d-block">••••${t.ultimos_4}</small>` : ''}
                                </div>
                                <span class="badge bg-${t.activa ? 'success' : 'secondary'}" style="font-size:0.7rem">
                                    ${t.activa ? 'Activa' : 'Inactiva'}
                                </span>
                            </div>
                            <hr class="my-2">
                            <div class="mb-2">
                                <small class="text-muted">Deuda comprometida</small>
                                <div class="fw-bold">${formatearMoneda(t.deuda_comprometida)}</div>
                            </div>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Disponible</small>
                                    <span class="badge bg-${colorUso}">${porcentajeUso}%</span>
                                </div>
                                <div class="progress" style="height:6px">
                                    <div class="progress-bar bg-${colorUso}" style="width:${porcentajeUso}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">${formatearMoneda(t.disponible)} de ${formatearMoneda(t.limite_credito)}</small>
                            <hr class="my-2">
                            <small class="text-muted">
                                <i class="bi bi-calendar-event me-1"></i>Cierre: día ${t.fecha_cierre_dia} | Vence: día ${t.fecha_vencimiento_dia}
                            </small>
                        </div>
                    </div>
                </div>`;
        });

        html += '</div></div>';
        body.innerHTML = html;

        // Actualizar selector en modal movimiento
        actualizarSelectTarjetasMovimiento();

        // Actualizar chip de tarjetas en la pantalla principal
        renderizarChipTarjetas();

    } catch (error) {
        body.innerHTML = `<div class="alert alert-danger m-3">${error.message}</div>`;
    }
}

// Abrir detalle de tarjeta
async function abrirDetalleTarjeta(tarjetaId) {
    tarjetaId = String(tarjetaId);
    tarjetaSeleccionada = parseInt(tarjetaId);
    const body = document.getElementById('modalTarjetasBody');

    try {
        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        // Cargar datos en paralelo
        const [respVencimientos, respMovimientos, respDisponible] = await Promise.all([
            fetchAPI(`${TARJETAS_API}?action=vencimientos_consolidados&tarjeta_id=${tarjetaId}`),
            fetchAPI(`${TARJETAS_API}?action=movimientos&tarjeta_id=${tarjetaId}`),
            fetchAPI(`${TARJETAS_API}?action=disponible&tarjeta_id=${tarjetaId}`)
        ]);

        const resultVencimientos = await respVencimientos.json();
        const resultMovimientos = await respMovimientos.json();
        const resultDisponible = await respDisponible.json();

        if (!resultVencimientos.success) throw new Error(resultVencimientos.message);

        const tarjeta = tarjetasActuales.find(t => String(t.id) === tarjetaId);
        if (!tarjeta) {
            throw new Error(`Tarjeta no encontrada: ${tarjetaId}`);
        }

        const vencimientos = resultVencimientos.data || [];
        const movimientos = resultMovimientos.success ? resultMovimientos.data : [];
        const disponible_info = resultDisponible.success ? resultDisponible.data : null;

        const proximo = vencimientos.length > 0 ? vencimientos[0] : null;

        let html = `
            <div class="p-3 tarj-modal">
                <div class="mb-3">
                    <button class="btn btn-sm btn-outline-secondary" onclick="cargarTarjetas()">
                        <i class="bi bi-chevron-left me-1"></i>Volver
                    </button>
                </div>

                <div class="card mb-4" style="border-top:3px solid var(--color-primary)">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="mb-1">${tarjeta.nombre_tarjeta}</h5>
                                <p class="text-muted mb-0">
                                    <small>${tarjeta.banco}</small>
                                    ${tarjeta.ultimos_4 ? `<span class="text-muted ms-2">•</span> <small class="ms-2">•••• ${tarjeta.ultimos_4}</small>` : ''}
                                </p>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-secondary" onclick="abrirCierresModal(${tarjetaId})" title="Editar cierres y vencimientos">
                                    <i class="bi bi-calendar2"></i>
                                </button>
                                <button class="btn btn-outline-secondary" onclick="editarTarjeta(${tarjetaId})" title="Editar tarjeta">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">Límite de Crédito</small>
                                <div class="fw-bold fs-6">${formatearMoneda(disponible_info?.limite || 0)}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">Disponible</small>
                                <div class="fw-bold fs-6" style="color: ${(disponible_info?.disponible || 0) < 0 ? '#dc3545' : '#10b981'}">
                                    ${formatearMoneda(disponible_info?.disponible || 0)}
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">Comprometido</small>
                                <div class="fw-bold fs-6">${formatearMoneda(disponible_info?.deuda_comprometida || 0)}</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block mb-1">Uso</small>
                                <div class="fw-bold fs-6">
                                    ${((disponible_info?.deuda_comprometida || 0) / (disponible_info?.limite || 1) * 100).toFixed(0)}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                ${proximo ? `
                <div class="alert alert-primary mb-4">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <small class="text-muted d-block">📅 Próximo vencimiento</small>
                        <strong>${parsearFechaISO(proximo.fecha_vencimiento).toLocaleDateString('es-AR', {day:'2-digit', month:'short', year:'numeric'})}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-baseline">
                        <small class="text-muted d-block">💰 Total a pagar</small>
                        <strong class="fs-6">${formatearMoneda(proximo.total)}</strong>
                    </div>
                </div>
                ` : '<div class="alert alert-info mb-4">Sin cuotas pendientes</div>'}

                <!-- Pestañas -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-pagos" data-bs-toggle="tab" data-bs-target="#content-pagos" type="button" role="tab" aria-controls="content-pagos" aria-selected="true">
                            <i class="bi bi-calendar-event me-2"></i>Próximos Pagos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-compras" data-bs-toggle="tab" data-bs-target="#content-compras" type="button" role="tab" aria-controls="content-compras" aria-selected="false">
                            <i class="bi bi-bag me-2"></i>Compras
                        </button>
                    </li>
                </ul>

                <!-- Contenido pestañas -->
                <div class="tab-content mb-4">
                    <!-- Pestaña: Próximos Pagos -->
                    <div class="tab-pane fade show active" id="content-pagos" role="tabpanel" aria-labelledby="tab-pagos">
                        ${renderizarVencimientosConsolidados(vencimientos)}
                    </div>

                    <!-- Pestaña: Compras -->
                    <div class="tab-pane fade" id="content-compras" role="tabpanel" aria-labelledby="tab-compras">
                        <div class="mb-3">
                            <button class="btn btn-primary btn-sm mb-3" onclick="abrirFormularioMovimiento(${tarjetaId})">
                                <i class="bi bi-plus-lg me-1"></i>Agregar Compra
                            </button>
                        </div>
                        ${renderizarMovimientosSimple(movimientos)}
                    </div>
                </div>
            </div>`;

        body.innerHTML = html;

    } catch (error) {
        body.innerHTML = `<div class="alert alert-danger m-3">${error.message}</div>`;
    }
}

// Parsear fecha YYYY-MM-DD sin ambigüedad de zona horaria
function parsearFechaISO(dateString) {
    const [year, month, day] = dateString.split('-').map(Number);
    return new Date(year, month - 1, day);
}

// Renderizar vencimientos consolidados por fecha con acordeón por mes
function renderizarVencimientosConsolidados(vencimientos) {
    if (!vencimientos || vencimientos.length === 0) {
        return '<div class="alert alert-info alert-sm">Sin cuotas pendientes</div>';
    }

    // Agrupar por mes (YYYY-MM)
    const porMes = {};
    vencimientos.forEach(venc => {
        const fecha = venc.fecha_vencimiento.substring(0, 7); // YYYY-MM
        if (!porMes[fecha]) {
            porMes[fecha] = { total: 0, vencimientos: [] };
        }
        porMes[fecha].total += venc.total;
        porMes[fecha].vencimientos.push(venc);
    });

    const meses = Object.keys(porMes).sort();
    let html = '';

    meses.forEach((mes, idx) => {
        const mesData = porMes[mes];
        const [anio, month] = mes.split('-');
        const mesNombre = new Date(anio, parseInt(month) - 1).toLocaleDateString('es-AR', {month:'long', year:'numeric'});
        const abierto = idx === 0 ? '' : ' d-none'; // Primer mes expandido

        html += `
            <div class="tarj-mes-grupo">
                <div class="tarj-mes-header" onclick="toggleMesTarjetas(this)">
                    <span>${mesNombre.charAt(0).toUpperCase() + mesNombre.slice(1)} · ${formatearMoneda(mesData.total)}</span>
                    <i class="bi bi-chevron-${idx === 0 ? 'down' : 'right'}"></i>
                </div>
                <div class="tarj-mes-body${abierto}">`;

        mesData.vencimientos.forEach(venc => {
            const fecha = parsearFechaISO(venc.fecha_vencimiento);
            const fechaStr = fecha.toLocaleDateString('es-AR', {day:'2-digit', month:'long'});

            html += `
                    <div class="tarj-vencimiento-grupo">
                        <div class="tarj-vencimiento-header">
                            <span class="fecha">📅 ${fechaStr}</span>
                            <span class="total">${formatearMoneda(venc.total)}</span>
                        </div>
                        <div class="tarj-cuotas-lista">`;

            venc.cuotas.forEach(cuota => {
                html += `
                            <div class="tarj-cuota-item">
                                <input type="checkbox" class="form-check-input tarj-cuota-check"
                                       data-cuota-id="${cuota.cuota_id}" onchange="marcarCuotaPagada(${cuota.cuota_id}, this)">
                                <div class="tarj-cuota-info">
                                    <div class="tarj-cuota-descripcion">${cuota.descripcion}</div>
                                    <div class="tarj-cuota-numero">Cuota ${cuota.numero_cuota}/${cuota.cuotas_totales}</div>
                                </div>
                                <div class="tarj-cuota-monto">${formatearMoneda(cuota.monto)}</div>
                            </div>`;
            });

            html += `
                        </div>
                    </div>`;
        });

        html += `
                </div>
            </div>`;
    });

    return html;
}

// Toggle acordeón de meses
function toggleMesTarjetas(header) {
    const body = header.nextElementSibling;
    const icon = header.querySelector('i');
    const abierto = !body.classList.contains('d-none');
    body.classList.toggle('d-none', abierto);
    icon.className = abierto ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
}

// Marcar cuota como pagada
async function marcarCuotaPagada(cuotaId, checkbox) {
    checkbox.disabled = true;
    try {
        const resp = await fetchAPI(`${TARJETAS_API}?action=marcar_pagada&cuota_id=${cuotaId}`, { method: 'PATCH' });
        const result = await resp.json();

        if (result.success) {
            // Animar fade-out
            const fila = checkbox.closest('.tarj-cuota-item');
            fila.style.opacity = '0';
            fila.style.transition = 'opacity 0.3s ease-out';

            setTimeout(() => {
                fila.remove();
                // Actualizar totales si quedan cuotas
                _actualizarTotalesVencimientoDespuesPago(cuotaId);
                mostrarToast('Cuota marcada como pagada', 'success');
            }, 300);
        } else {
            checkbox.disabled = false;
            checkbox.checked = false;
            mostrarError('Error: ' + result.message);
        }
    } catch (error) {
        checkbox.disabled = false;
        checkbox.checked = false;
        mostrarError('Error: ' + error.message);
    }
}

// Actualizar totales después de pagar una cuota
function _actualizarTotalesVencimientoDespuesPago(cuotaId) {
    // Recalcular total del vencimiento
    const cuotaItem = document.querySelector(`[data-cuota-id="${cuotaId}"]`)?.closest('.tarj-cuota-item');
    if (!cuotaItem) {
        // Si no hay más cuotas en ese vencimiento, recalcular totales de tarjeta
        if (tarjetaSeleccionada) {
            abrirDetalleTarjeta(tarjetaSeleccionada);
        }
    }
}

// Renderizar movimientos en formato simple (lista)
function renderizarMovimientosSimple(movimientos) {
    if (!movimientos || movimientos.length === 0) {
        return '<div class="alert alert-info alert-sm">Sin movimientos registrados</div>';
    }

    return movimientos.map(m => {
        const descEscaped = (m.descripcion || '').replace(/"/g, '&quot;');
        const comercioEscaped = (m.comercio || '').replace(/"/g, '&quot;');
        const categoriaEscaped = (m.categoria || '').replace(/"/g, '&quot;');
        const detalles = [];

        detalles.push(`📅 ${formatearFechaCorta(m.fecha_compra)}`);
        detalles.push(`${m.cuotas_totales} cuota${m.cuotas_totales > 1 ? 's' : ''}`);
        if (m.comercio) detalles.push(`${m.comercio}`);

        return `
            <div class="tarj-movimiento-fila">
                <div class="tarj-movimiento-info">
                    <div class="tarj-movimiento-titulo">${m.descripcion || '(sin descripción)'}</div>
                    <div class="tarj-movimiento-detalles">
                        ${detalles.map(d => `<span>${d}</span>`).join('')}
                    </div>
                </div>
                <div class="tarj-movimiento-monto">${formatearMoneda(m.monto_total)}</div>
                <div class="tarj-movimiento-acciones">
                    <button class="btn btn-sm btn-outline-primary" onclick="editarMovimientoTarjeta(${m.id}, '${descEscaped}', '${comercioEscaped}', '${categoriaEscaped}')" title="Editar">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="anularMovimientoTarjeta(${m.id})" title="Anular">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Renderizar proyección mensual
function renderizarProyeccion(proyeccion) {
    // Filtrar períodos con total_pendiente = 0 y calcular estados correctos
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const periodosConDeuda = proyeccion
        .filter(p => p.total_pendiente > 0)
        .map(p => {
            // Calcular estado financiero basado en fecha_vencimiento vs hoy
            const fechaVenc = parsearFechaISO(p.fecha_vencimiento);
            fechaVenc.setHours(0, 0, 0, 0);
            const diasAlVencimiento = Math.ceil((fechaVenc - hoy) / (1000 * 60 * 60 * 24));

            let estado = 'pendiente';
            if (p.pagado) {
                estado = 'pagado';
            } else if (diasAlVencimiento < 0) {
                estado = 'vencido';
            } else if (diasAlVencimiento === 0) {
                estado = 'hoy';
            } else if (diasAlVencimiento <= 7) {
                estado = 'proximo';
            }

            return { ...p, estado_financiero: estado };
        });

    if (periodosConDeuda.length === 0) {
        return '<div class="alert alert-info">Sin cuotas pendientes</div>';
    }

    return periodosConDeuda.map(p => {
        const iconos = {
            'pagado': 'bi-check-circle-fill',
            'vencido': 'bi-exclamation-triangle-fill',
            'hoy': 'bi-lightning-fill',
            'proximo': 'bi-alarm',
            'pendiente': 'bi-hourglass-split'
        };

        const colores = {
            'pagado': '#198754',
            'vencido': '#dc3545',
            'hoy': '#ffc107',
            'proximo': '#fd7e14',
            'pendiente': '#0dcaf0'
        };

        const botones = !p.pagado && p.total_pendiente > 0
            ? `<button class="btn btn-sm btn-success ms-2" onclick="_abrirFormPago(${p.id}, ${p.total_pendiente})">
                   <i class="bi bi-cash me-1"></i>Pagar
               </button>`
            : '';

        return `
            <div class="d-flex align-items-center gap-2 p-2 mb-2 tarj-periodo-principal" style="border-left:3px solid ${colores[p.estado_financiero]}">
                <div>
                    <i class="bi ${iconos[p.estado_financiero]}" style="color:${colores[p.estado_financiero]}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold">${p.periodo}</div>
                    <small class="text-muted">
                        Vence: ${parsearFechaISO(p.fecha_vencimiento).toLocaleDateString('es-AR', {day:'2-digit', month:'short'})}
                        · ${p.cuotas_pagadas}/${p.cuotas_totales} cuotas
                    </small>
                </div>
                <div class="text-end d-flex align-items-center gap-2">
                    <div>
                        <div class="fw-bold">${formatearMoneda(p.total_pendiente)}</div>
                        <small class="text-muted d-block">${p.estado_financiero.toUpperCase()}</small>
                    </div>
                    ${botones}
                </div>
            </div>`;
    }).join('');
}

// Renderizar movimientos (consumos) detallados
function renderizarMovimientosDetalle(movimientos, tarjetaId) {
    if (!movimientos || movimientos.length === 0) {
        return '<div class="alert alert-info alert-sm">Sin movimientos registrados</div>';
    }

    return movimientos.map(m => `
        <div class="p-2 mb-2 tarj-periodo-principal border-start ps-3" style="border-left-width:3px;border-left-color:#6366f1;">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="flex-grow-1">
                    <div class="fw-bold">${m.descripcion || '(sin descripción)'}</div>
                    <small class="text-muted">
                        ${formatearFechaCorta(m.fecha_compra)} ·
                        ${m.cuotas_totales} cuota${m.cuotas_totales > 1 ? 's' : ''}
                        (${m.cuotas_pagadas}/${m.cuotas_totales} pagada${m.cuotas_pagadas !== 1 ? 's' : ''})
                    </small>
                </div>
                <div class="text-end">
                    <div class="fw-bold">${formatearMoneda(m.monto_total)}</div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-link p-0" onclick="_editarConsumoTarjeta(${m.id}, '${m.fecha_compra}', ${m.cuota_pagada_proximo_resumen || 1}, ${m.cuotas_totales}, '${(m.descripcion || '').replace(/'/g, "\\'")}', ${m.monto_total})" title="Editar">
                            <i class="bi bi-pencil-sm"></i>
                        </button>
                        <button class="btn btn-link p-0 text-danger" onclick="_eliminarConsumoTarjeta(${m.id})" title="Eliminar">
                            <i class="bi bi-trash-sm"></i>
                        </button>
                    </div>
                </div>
            </div>
            ${m.comercio ? `<small class="text-muted d-block">${m.comercio}</small>` : ''}
        </div>
    `).join('');
}

// Abrir formulario de tarjeta (nueva)
function abrirFormularioTarjeta() {
    document.getElementById('formTarjeta').reset();
    document.getElementById('ftMonto')?.remove(); // Limpiar si existe
    document.getElementById('modalFormTarjetaLabel').textContent = '+ Nueva Tarjeta';
    const modal = new bootstrap.Modal(document.getElementById('modalFormTarjeta'), { keyboard: false });
    modal.show();
}

// Guardar tarjeta
async function guardarTarjeta() {
    const banco = document.getElementById('ftBanco').value.trim();
    const nombre = document.getElementById('ftNombre').value.trim();
    const marca = document.getElementById('ftMarca').value;
    const ultimos = document.getElementById('ftUltimos').value.trim();
    const limite = parseFloat(document.getElementById('ftLimite').value) || 0;
    const cierre = parseInt(document.getElementById('ftCierre').value);
    const vencimiento = parseInt(document.getElementById('ftVencimiento').value);
    const titular = document.getElementById('ftTitular').value.trim();

    if (!banco || !nombre || !marca || !cierre || !vencimiento) {
        mostrarError('Complete los campos obligatorios');
        return;
    }

    if (cierre < 1 || cierre > 31 || vencimiento < 1 || vencimiento > 31) {
        mostrarError('Días deben estar entre 1 y 31');
        return;
    }

    try {
        const resp = await fetch(TARJETAS_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                banco,
                nombre_tarjeta: nombre,
                marca,
                ultimos_4: ultimos,
                limite_credito: limite,
                fecha_cierre_dia: cierre,
                fecha_vencimiento_dia: vencimiento,
                titular
            })
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Tarjeta creada', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalFormTarjeta')).hide();

        // Recargar tarjetas
        const respT = await fetch(`${TARJETAS_API}`);
        const resT = await respT.json();
        if (resT.success) tarjetasActuales = resT.data || [];
        renderizarChipTarjetas();

        cargarTarjetas();

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Abrir formulario movimiento
function abrirFormularioMovimiento(tarjetaId) {
    document.getElementById('formMovimiento').reset();
    document.getElementById('mvTarjeta').value = tarjetaId || '';
    document.getElementById('mvFecha').valueAsDate = new Date();
    document.getElementById('mvCuotas').value = '1';
    actualizarOpcionesCuota();

    const modal = new bootstrap.Modal(document.getElementById('modalMovimientoTarjeta'), { keyboard: false });
    modal.show();
}

// Actualizar opciones de cuota al cambiar cantidad
document.addEventListener('input', (e) => {
    if (e.target.id === 'mvCuotas') {
        actualizarOpcionesCuota();
    }
});

function actualizarOpcionesCuota() {
    const cuotas = parseInt(document.getElementById('mvCuotas').value) || 1;
    const select = document.getElementById('mvCuotaPagar');
    let html = '';

    for (let i = 1; i <= cuotas; i++) {
        const label = i === 1 ? 'Cuota 1 (primera)' : `Cuota ${i}`;
        html += `<option value="${i}" ${i === 1 ? 'selected' : ''}>${label}</option>`;
    }

    select.innerHTML = html;
}

// Guardar movimiento (compra)
async function guardarMovimiento() {
    const tarjetaId = parseInt(document.getElementById('mvTarjeta').value);
    const fecha = document.getElementById('mvFecha').value;
    const descripcion = document.getElementById('mvDescripcion').value.trim();
    const comercio = document.getElementById('mvComercio').value.trim();
    const categoria = document.getElementById('mvCategoria').value.trim();
    const monto = parsearImporte(document.getElementById('mvMonto').value);
    const cuotas = parseInt(document.getElementById('mvCuotas').value);

    if (!tarjetaId || !fecha || !descripcion || !monto || monto <= 0 || !cuotas) {
        mostrarError('Complete los campos obligatorios correctamente');
        return;
    }

    try {
        const resp = await fetchAPI(`${TARJETAS_API}?action=movimiento`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tarjeta_id: tarjetaId,
                fecha_compra: fecha,
                descripcion,
                comercio: comercio || '',
                categoria: categoria || '',
                monto_total: monto,
                cuotas_totales: cuotas
            })
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast(`Compra registrada - ${result.data.cuotas_generadas} cuotas generadas`, 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalMovimientoTarjeta')).hide();

        // Recargar tarjetas y volver a detalle
        const respT = await fetchAPI(`${TARJETAS_API}`);
        const resT = await respT.json();
        if (resT.success) tarjetasActuales = resT.data || [];
        renderizarChipTarjetas();

        abrirDetalleTarjeta(tarjetaId);

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Editar movimiento (descripción, comercio, categoría)
function editarMovimientoTarjeta(movimientoId, descActual, comercioActual, categoriaActual) {
    const html = `
        <div class="modal fade" id="modalEditarMovimiento" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Movimiento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" id="editDesc" class="form-control" value="${descActual}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comercio</label>
                            <input type="text" id="editComercio" class="form-control" value="${comercioActual}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <input type="text" id="editCategoria" class="form-control" value="${categoriaActual}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="guardarEdicionMovimiento(${movimientoId})">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Crear modal
    let modalDiv = document.getElementById('modalEditarMovimiento');
    if (modalDiv) modalDiv.remove();

    document.body.insertAdjacentHTML('beforeend', html);
    const modal = new bootstrap.Modal(document.getElementById('modalEditarMovimiento'), { keyboard: false });
    modal.show();
}

// Guardar edición de movimiento
async function guardarEdicionMovimiento(movimientoId) {
    const descripcion = document.getElementById('editDesc').value.trim();
    const comercio = document.getElementById('editComercio').value.trim();
    const categoria = document.getElementById('editCategoria').value.trim();

    if (!descripcion) {
        mostrarError('La descripción es obligatoria');
        return;
    }

    try {
        const resp = await fetchAPI(`${TARJETAS_API}?action=movimiento&id=${movimientoId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                descripcion,
                comercio,
                categoria
            })
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Movimiento actualizado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalEditarMovimiento')).hide();

        // Recargar detalle
        if (tarjetaSeleccionada) {
            abrirDetalleTarjeta(tarjetaSeleccionada);
        }

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Anular movimiento (marca como cancelado, no borra)
async function anularMovimientoTarjeta(movimientoId) {
    if (!confirm('¿Anular este movimiento? Las cuotas se ocultarán del cálculo de deuda.')) return;

    try {
        const resp = await fetchAPI(`${TARJETAS_API}?action=movimiento&id=${movimientoId}`, {
            method: 'DELETE'
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Movimiento anulado', 'success');

        // Recargar detalle
        if (tarjetaSeleccionada) {
            abrirDetalleTarjeta(tarjetaSeleccionada);
        }

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Actualizar select de tarjetas en modal movimiento
function actualizarSelectTarjetasMovimiento() {
    const select = document.getElementById('mvTarjeta');
    let html = '<option value="">— Seleccionar —</option>';

    tarjetasActuales.forEach(t => {
        html += `<option value="${t.id}">${t.nombre_tarjeta} (${t.banco})</option>`;
    });

    select.innerHTML = html;
}

// Abrir modal para editar cierres y vencimientos
async function abrirCierresModal(tarjetaId) {
    try {
        tarjetaId = parseInt(tarjetaId, 10);
        const resp = await fetchAPI(`${TARJETAS_API}?action=cierres&tarjeta_id=${tarjetaId}`);
        const result = await resp.json();

        if (!result.success) throw new Error(result.message);

        const cierres = result.data || [];
        const tarjeta = tarjetasActuales.find(t => parseInt(t.id, 10) === tarjetaId);

        let html = `
            <div class="modal fade" id="modalCierres" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cierres y Vencimientos - ${tarjeta?.nombre_tarjeta}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">`;

        if (cierres.length === 0) {
            html += `
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>No hay cierres configurados.</strong><br>
                                Genera cierres automáticos basados en los días de cierre y vencimiento de la tarjeta.
                            </div>
                            <button class="btn btn-primary w-100" onclick="generarCierresAuto(${tarjetaId})">
                                <i class="bi bi-plus-circle me-2"></i>
                                Generar Cierres para los Próximos 12 Meses
                            </button>`;
        } else {
            html += `
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mes/Año</th>
                                            <th>Fecha de Cierre</th>
                                            <th>Fecha de Vencimiento</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaCierres">`;

            cierres.forEach(cierre => {
            const mesNombre = new Date(cierre.anio, cierre.mes - 1).toLocaleDateString('es-AR', {month:'long', year:'numeric'});
            html += `
                                        <tr>
                                            <td>${mesNombre.charAt(0).toUpperCase() + mesNombre.slice(1)}</td>
                                            <td>
                                                <input type="date" class="form-control form-control-sm cierre-${cierre.id}"
                                                       value="${cierre.fecha_cierre}">
                                            </td>
                                            <td>
                                                <input type="date" class="form-control form-control-sm vencimiento-${cierre.id}"
                                                       value="${cierre.fecha_vencimiento}">
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary"
                                                        onclick="guardarCierre(${tarjetaId}, ${cierre.id}, ${cierre.anio}, ${cierre.mes})"
                                                        title="Guardar cambios">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </td>
                                        </tr>`;
            });

            html += `
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted small mt-3">
                                💡 Edita las fechas según los días reales de cierre y vencimiento de cada mes.
                            </p>`;
        }

        html += `
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>`;

        let modalDiv = document.getElementById('modalCierres');
        if (modalDiv) modalDiv.remove();

        document.body.insertAdjacentHTML('beforeend', html);
        const modal = new bootstrap.Modal(document.getElementById('modalCierres'), { keyboard: false });
        modal.show();

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Generar cierres automáticamente para los próximos 12 meses
async function generarCierresAuto(tarjetaId) {
    try {
        const resp = await fetchAPI(`${TARJETAS_API}?action=generar_cierres&tarjeta_id=${tarjetaId}`, {
            method: 'POST'
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast(result.message, 'success');

        // Reabrir modal para mostrar los cierres generados
        setTimeout(() => abrirCierresModal(tarjetaId), 500);

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Guardar cambios en un cierre
async function guardarCierre(tarjetaId, cierreId, anio, mes) {
    const fechaCierre = document.querySelector(`.cierre-${cierreId}`).value;
    const fechaVencimiento = document.querySelector(`.vencimiento-${cierreId}`).value;

    if (!fechaCierre || !fechaVencimiento) {
        mostrarError('Las fechas no pueden estar vacías');
        return;
    }

    try {
        const resp = await fetchAPI(`${TARJETAS_API}?action=cierres&tarjeta_id=${tarjetaId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                anio: anio,
                mes: mes,
                fecha_cierre: fechaCierre,
                fecha_vencimiento: fechaVencimiento
            })
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Cierre actualizado', 'success');
    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Editar tarjeta (placeholder)
function editarTarjeta(tarjetaId) {
    const tarjeta = tarjetasActuales.find(t => t.id === tarjetaId);
    if (!tarjeta) return;

    // Implementar si es necesario
    mostrarToast('Edición no implementada aún', 'info');
}

// Editar consumo (movimiento)
function _editarConsumoTarjeta(consumoId, fechaActual, cuotaActual, cuotasTotal, descActual, importeActual) {
    const html = `
        <div class="modal fade" id="modalEditConsumo" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Consumo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label form-field-label">Fecha</label>
                            <input type="date" id="editFecha" class="form-control form-control-sm" value="${fechaActual}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-field-label">Descripción</label>
                            <input type="text" id="editDesc" class="form-control form-control-sm" value="${descActual}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-field-label">Importe</label>
                            <input type="text" inputmode="decimal" id="editImporte" class="form-control form-control-sm text-end" value="${importeActual.toString().replace('.', ',')}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-field-label">Cuota a Pagar en Próximo Resumen</label>
                            <select id="editCuota" class="form-select form-select-sm">
                                ${Array.from({length: cuotasTotal}, (_, i) => {
                                    const num = i + 1;
                                    const label = num === 1 ? 'Cuota 1 (primera)' : `Cuota ${num}`;
                                    return `<option value="${num}" ${num === cuotaActual ? 'selected' : ''}>${label}</option>`;
                                }).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="_guardarEdicionConsumo(${consumoId})">Guardar</button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remover modal anterior si existe
    const modalViejo = document.getElementById('modalEditConsumo');
    if (modalViejo) modalViejo.remove();

    // Crear e inyectar modal
    const div = document.createElement('div');
    div.innerHTML = html;
    document.body.appendChild(div.firstElementChild);

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalEditConsumo'), { keyboard: false });
    modal.show();
}

// Eliminar consumo
async function _eliminarConsumoTarjeta(consumoId) {
    if (!confirm('¿Eliminar este consumo? Se eliminarán todas sus cuotas.')) {
        return;
    }

    try {
        const resp = await fetch(`${TARJETAS_API}?action=movimientos&movimiento_id=${consumoId}`, {
            method: 'DELETE'
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Consumo eliminado', 'success');

        // Recargar detalle de la tarjeta y actualizar chip
        if (tarjetaSeleccionada) {
            abrirDetalleTarjeta(tarjetaSeleccionada);
            const respT = await fetch(`${TARJETAS_API}`);
            const resT = await respT.json();
            if (resT.success) tarjetasActuales = resT.data || [];
            renderizarChipTarjetas();
        }

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Guardar edición de consumo
async function _guardarEdicionConsumo(consumoId) {
    const fecha = document.getElementById('editFecha').value;
    const desc = document.getElementById('editDesc').value.trim();
    const importe = parsearImporte(document.getElementById('editImporte').value);
    const cuota = parseInt(document.getElementById('editCuota').value);

    if (!fecha || importe <= 0) {
        mostrarError('Complete los campos correctamente');
        return;
    }

    try {
        const resp = await fetch(`${TARJETAS_API}?action=movimientos&movimiento_id=${consumoId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                fecha_compra: fecha,
                descripcion: desc,
                importe: importe,
                cuota_pagada_proximo_resumen: cuota
            })
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Consumo actualizado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalEditConsumo')).hide();

        // Recargar detalle de la tarjeta y actualizar chip
        if (tarjetaSeleccionada) {
            abrirDetalleTarjeta(tarjetaSeleccionada);
            // Recargar datos de tarjetas para actualizar chip
            const respT = await fetch(`${TARJETAS_API}`);
            const resT = await respT.json();
            if (resT.success) tarjetasActuales = resT.data || [];
            renderizarChipTarjetas();
        }

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// Abrir formulario de pago de período
function _abrirFormPago(resumenId, total) {
    const cuentasHtml = app.cuentas
        .filter(c => c.activo === 1 || c.activo === '1')
        .map(c => `<option value="${c.id}">${c.nombre} (${formatearMoneda(c.saldo_actual)})</option>`)
        .join('');

    if (!cuentasHtml) {
        mostrarError('No hay cuentas activas disponibles');
        return;
    }

    const html = `
        <div class="modal fade" id="modalPagoTarjeta" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pagar Período</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label form-field-label">Cuenta de Débito</label>
                            <select id="pagoCuentaId" class="form-select form-select-sm">
                                <option value="">— Seleccionar —</option>
                                ${cuentasHtml}
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label form-field-label">Importe a Pagar</label>
                            <input type="text" inputmode="decimal" id="pagoImporte" class="form-control form-control-sm text-end" value="${total.toString().replace('.', ',')}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="_confirmarPago(${resumenId})">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remover modal anterior si existe
    const modalViejo = document.getElementById('modalPagoTarjeta');
    if (modalViejo) modalViejo.remove();

    // Crear e inyectar modal
    const div = document.createElement('div');
    div.innerHTML = html;
    document.body.appendChild(div.firstElementChild);

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('modalPagoTarjeta'), { keyboard: false });
    modal.show();
}

// Confirmar pago de período
async function _confirmarPago(resumenId) {
    const cuentaId = parseInt(document.getElementById('pagoCuentaId').value);
    const importe = parsearImporte(document.getElementById('pagoImporte').value);

    if (!cuentaId || importe <= 0) {
        mostrarError('Seleccione una cuenta e importe válido');
        return;
    }

    try {
        const resp = await fetch(`${TARJETAS_API}?action=pago`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                resumen_id: resumenId,
                cuenta_id: cuentaId,
                importe: importe
            })
        });

        const result = await resp.json();
        if (!result.success) throw new Error(result.message);

        mostrarToast('Pago registrado', 'success');
        bootstrap.Modal.getInstance(document.getElementById('modalPagoTarjeta')).hide();

        // Recargar detalle de tarjeta y cuentas
        if (tarjetaSeleccionada) {
            abrirDetalleTarjeta(tarjetaSeleccionada);
        }
        cargarDatos(); // Actualizar cuentas y saldos

    } catch (error) {
        mostrarError('Error: ' + error.message);
    }
}

// ============================================================
// DASHBOARD - TARJETAS
// ============================================================

/**
 * Cargar datos del dashboard de tarjetas
 * Actualiza widgets en el topbar
 */
async function cargarDashboardTarjetas() {
    try {
        // Obtener próximo vencimiento de TODAS las tarjetas
        const proximos = [];

        for (const tarjeta of tarjetasActuales) {
            const resp = await fetch(`${TARJETAS_API}?action=proximo_vencimiento&tarjeta_id=${tarjeta.id}`);
            const result = await resp.json();

            if (result.success && result.data) {
                proximos.push({
                    tarjeta_id: tarjeta.id,
                    tarjeta_nombre: tarjeta.nombre_tarjeta,
                    ...result.data
                });
            }
        }

        // Encontrar el próximo vencimiento más cercano
        if (proximos.length > 0) {
            proximos.sort((a, b) => new Date(a.fecha_vencimiento) - new Date(b.fecha_vencimiento));
            const proximo = proximos[0];

            mostrarWidgetProximoVencimiento(proximo);
            verificarAlertasTarjetas();
        } else {
            ocultarWidgetProximoVencimiento();
        }

    } catch (error) {
        console.error('Error cargando dashboard tarjetas:', error);
    }
}

/**
 * Mostrar widget "Próximo Vencimiento" en topbar
 */
function mostrarWidgetProximoVencimiento(vencimiento) {
    const stat = document.getElementById('statProximoVenc');
    const valor = document.getElementById('proximoVencHeader');

    if (!stat || !valor) return;

    const fecha = new Date(vencimiento.fecha_vencimiento);
    const hoy = new Date();
    const dias = Math.ceil((fecha - hoy) / (1000 * 60 * 60 * 24));

    let texto = '';
    let clase = '';

    if (dias < 0) {
        texto = 'Vencido';
        clase = 'text-danger';
    } else if (dias === 0) {
        texto = 'HOY';
        clase = 'text-warning';
    } else if (dias <= 3) {
        texto = `${dias} día${dias === 1 ? '' : 's'}`;
        clase = 'text-warning';
    } else if (dias <= 7) {
        texto = `${dias} día${dias === 1 ? '' : 's'}`;
        clase = 'text-info';
    } else {
        texto = fecha.toLocaleDateString('es-AR', { day: 'numeric', month: 'short' });
        clase = '';
    }

    valor.textContent = texto;
    valor.className = `topbar-stat-valor ${clase}`;
    stat.classList.remove('d-none');

    // Tooltip con más detalles
    stat.title = `${vencimiento.tarjeta_nombre} - ${formatearMoneda(vencimiento.total)} - ${vencimiento.cuotas_pendientes} cuota${vencimiento.cuotas_pendientes === 1 ? '' : 's'}`;
}

/**
 * Ocultar widget si no hay vencimientos
 */
function ocultarWidgetProximoVencimiento() {
    const stat = document.getElementById('statProximoVenc');
    if (stat) stat.classList.add('d-none');
}

/**
 * Verificar y mostrar alertas de tarjetas
 */
async function verificarAlertasTarjetas() {
    const alertas = [];

    for (const tarjeta of tarjetasActuales) {
        const resp = await fetch(`${TARJETAS_API}?action=disponible&tarjeta_id=${tarjeta.id}`);
        const result = await resp.json();

        if (result.success) {
            const { limite, deuda_comprometida, disponible } = result.data;

            // Alerta: Límite superado (disponible negativo)
            if (disponible < 0) {
                alertas.push({
                    tipo: 'danger',
                    icon: 'bi-exclamation-triangle-fill',
                    mensaje: `${tarjeta.nombre_tarjeta}: Límite SUPERADO por ${formatearMoneda(Math.abs(disponible))}`
                });
            }
            // Alerta: Casi lleno (80%+)
            else if (deuda_comprometida > (limite * 0.8)) {
                const pct = Math.round((deuda_comprometida / limite) * 100);
                alertas.push({
                    tipo: 'warning',
                    icon: 'bi-exclamation-circle-fill',
                    mensaje: `${tarjeta.nombre_tarjeta}: ${pct}% del límite utilizado`
                });
            }
        }
    }

    mostrarAlertasTarjetas(alertas);
}

/**
 * Renderizar alertas en el DOM (debajo del topbar)
 */
function mostrarAlertasTarjetas(alertas) {
    let container = document.getElementById('alertasTarjetasContainer');

    if (alertas.length === 0) {
        if (container) container.remove();
        return;
    }

    if (!container) {
        container = document.createElement('div');
        container.id = 'alertasTarjetasContainer';
        container.style.cssText = 'margin: 1rem 0; display: flex; flex-direction: column; gap: 0.5rem;';

        const header = document.querySelector('header');
        if (header) {
            header.parentNode.insertBefore(container, header.nextSibling);
        }
    }

    let html = '';
    alertas.forEach(alerta => {
        html += `
            <div class="alert alert-${alerta.tipo} mb-0 d-flex align-items-center gap-2" style="margin: 0 1rem">
                <i class="bi ${alerta.icon}" style="flex-shrink:0"></i>
                <span>${alerta.mensaje}</span>
            </div>`;
    });

    container.innerHTML = html;
}

/**
 * Renderizar chip "Tarjetas" en catNav
 * Muestra deuda total comprometida
 */
function renderizarChipTarjetas() {
    const catNavScroll = document.querySelector('.cat-nav-scroll');
    if (!catNavScroll) return;

    // Calcular deuda total
    let deudaTotal = 0;
    tarjetasActuales.forEach(t => {
        deudaTotal += parseFloat(t.deuda_comprometida || 0);
    });

    // Remover chip existente
    let chip = document.getElementById('chipTarjetas');
    if (chip) chip.remove();

    // Crear chip con el mismo formato que los chips de categoría
    chip = document.createElement('button');
    chip.id = 'chipTarjetas';
    chip.className = 'cat-chip';
    chip.style.setProperty('--chip-color', '#10b981');
    chip.onclick = () => abrirModalTarjetas();
    chip.innerHTML = `
        <i class="bi bi-credit-card cat-chip-icon"></i>
        <span class="cat-chip-nombre">Tarjetas</span>
        <span class="cat-chip-total">${formatearMoneda(deudaTotal)}</span>`;
    catNavScroll.appendChild(chip);
}

/**
 * Actualizar dashboard cuando se guarda un movimiento
 */
function actualizarDashboardDespuesPago() {
    cargarDatos();
}

// ============================================================
// SIMULADOR DE COMPRA
// ============================================================

/**
 * Abrir modal simulador
 */
function abrirSimulador() {
    document.getElementById('formSimulador').reset();
    document.getElementById('simFecha').valueAsDate = new Date();
    document.getElementById('simCuotas').value = '1';

    // Llenar select de tarjetas
    const select = document.getElementById('simTarjeta');
    let html = '<option value="">— Seleccionar —</option>';

    tarjetasActuales.forEach(t => {
        html += `<option value="${t.id}">${t.nombre_tarjeta} (${t.banco})</option>`;
    });

    select.innerHTML = html;

    // Limpiar resultado
    document.getElementById('resultadoSimulacion').innerHTML = '';

    const modal = new bootstrap.Modal(document.getElementById('modalSimulador'), { keyboard: false });
    modal.show();
}

/**
 * Actualizar simulación en tiempo real
 */
async function actualizarSimulacion() {
    const tarjetaId = parseInt(document.getElementById('simTarjeta').value);
    const fecha = document.getElementById('simFecha').value;
    const monto = parseFloat(document.getElementById('simMonto').value) || 0;
    const cuotas = parseInt(document.getElementById('simCuotas').value) || 1;
    const cuotaPagar = parseInt(document.getElementById('simCuotaPagar').value) || 1;

    // Actualizar opciones de cuota
    actualizarOpcionesCuotaSimulador(cuotas);

    // Validaciones básicas
    if (!tarjetaId || !fecha || monto <= 0 || cuotas < 1) {
        document.getElementById('resultadoSimulacion').innerHTML = '';
        return;
    }

    try {
        // Obtener datos de la tarjeta
        const tarjeta = tarjetasActuales.find(t => t.id === tarjetaId);
        if (!tarjeta) return;

        // Obtener deuda actual para cálculo de impacto
        const respDisponible = await fetch(`${TARJETAS_API}?action=disponible&tarjeta_id=${tarjetaId}`);
        const resultDisponible = await respDisponible.json();

        if (!resultDisponible.success) return;

        const deudaActual = resultDisponible.data.deuda_comprometida;
        const limiteActual = resultDisponible.data.limite;
        const disponibleActual = resultDisponible.data.disponible;

        // Cálculos post-compra
        const montoParaCuota = monto / cuotas;
        const deudaNueva = deudaActual + monto;
        const disponibleNuevo = limiteActual - deudaNueva;
        const porcentajeUso = (deudaNueva / limiteActual) * 100;

        // Obtener proyección actual
        const respProyeccion = await fetch(`${TARJETAS_API}?action=proyeccion&tarjeta_id=${tarjetaId}&meses=12`);
        const resultProyeccion = await respProyeccion.json();

        const proyeccionActual = resultProyeccion.data || [];

        // Renderizar resultado
        let html = `
            <div class="sim-resultado">
                <!-- Cambio en deuda -->
                <div class="mb-4">
                    <h6 class="mb-3">Impacto en Deuda Comprometida</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="sim-card">
                                <small class="text-muted">Deuda Actual</small>
                                <div class="sim-value">${formatearMoneda(deudaActual)}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sim-card">
                                <small class="text-muted">+ Esta Compra</small>
                                <div class="sim-value" style="color:#6366f1">${formatearMoneda(monto)}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sim-card" style="background:rgba(99,102,241,0.05);border:2px solid #6366f1">
                                <small class="text-muted">Deuda NUEVA</small>
                                <div class="sim-value fw-bold">${formatearMoneda(deudaNueva)}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Disponible -->
                <div class="mb-4">
                    <h6 class="mb-3">Disponible Restante</h6>
                    <div class="sim-card">
                        <small class="text-muted">Límite: ${formatearMoneda(limiteActual)}</small>
                        <div class="progress mb-2" style="height:8px">
                            <div class="progress-bar ${porcentajeUso > 80 ? 'bg-danger' : porcentajeUso > 50 ? 'bg-warning' : 'bg-success'}"
                                 style="width:${Math.min(porcentajeUso, 100)}%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small><strong>${Math.round(porcentajeUso)}%</strong> utilizado</small>
                            <small><strong>${formatearMoneda(disponibleNuevo)}</strong> disponible</small>
                        </div>
                    </div>
                </div>

                <!-- Período y recomendación -->
                <div class="mb-4">
                    <h6 class="mb-3">Período y Recomendación</h6>
                    ${renderizarRecomendacionPeriodo(tarjeta, fecha)}
                </div>

                <!-- Cuotas mensuales -->
                <div class="mb-4">
                    <h6 class="mb-3">Cuotas Mensuales (${formatearMoneda(montoParaCuota)}/mes)</h6>
                    ${renderizarCuotasSimulacion(fecha, cuotas, montoParaCuota, tarjeta)}
                </div>

                <!-- Impacto en próximos meses -->
                <div class="mb-4">
                    <h6 class="mb-3">Proyección Próximos Meses (CON esta compra)</h6>
                    ${renderizarProyeccionConCompra(proyeccionActual, fecha, cuotas, montoParaCuota, tarjeta)}
                </div>
            </div>`;

        document.getElementById('resultadoSimulacion').innerHTML = html;

    } catch (error) {
        console.error('Error en simulación:', error);
        document.getElementById('resultadoSimulacion').innerHTML = `
            <div class="alert alert-danger">Error: ${error.message}</div>`;
    }
}

/**
 * Actualizar opciones de cuota en simulador
 */
function actualizarOpcionesCuotaSimulador(cuotas) {
    const select = document.getElementById('simCuotaPagar');
    let html = '';

    for (let i = 1; i <= cuotas; i++) {
        const label = i === 1 ? 'Cuota 1 (primera)' : `Cuota ${i}`;
        html += `<option value="${i}" ${i === 1 ? 'selected' : ''}>${label}</option>`;
    }

    select.innerHTML = html;
}

/**
 * Renderizar recomendación de período
 */
function renderizarRecomendacionPeriodo(tarjeta, fechaCompra) {
    const fecha = new Date(fechaCompra);
    const dia = fecha.getDate();
    const cierre = tarjeta.fecha_cierre_dia;

    let html = '';

    if (dia <= cierre) {
        html += `
            <div class="alert alert-info mb-0">
                <i class="bi bi-check-circle me-2"></i>
                <strong>Entra en resumen ACTUAL</strong> (cierre ${cierre})
                <br><small class="text-muted">Vencimiento: ${tarjeta.fecha_vencimiento_dia} del mes siguiente</small>
            </div>`;
    } else {
        html += `
            <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Entra en PRÓXIMO resumen</strong> (después del cierre ${cierre})
                <br><small class="text-muted">Se pagará 2 meses después de la compra</small>
            </div>`;
    }

    return html;
}

/**
 * Renderizar cuotas de la compra simulada
 */
function renderizarCuotasSimulacion(fecha, cuotas, montoParaCuota, tarjeta) {
    const fechaInicio = new Date(fecha);
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    let html = '<div class="sim-cuotas-list">';

    for (let i = 1; i <= cuotas; i++) {
        const fechaCuota = new Date(fechaInicio);
        fechaCuota.setMonth(fechaCuota.getMonth() + (i - 1));

        const mesLabel = meses[fechaCuota.getMonth()];
        const anio = fechaCuota.getFullYear();

        html += `
            <div class="sim-cuota-item">
                <span class="sim-cuota-numero">${i}</span>
                <span class="sim-cuota-mes">${mesLabel} ${anio}</span>
                <span class="sim-cuota-monto">${formatearMoneda(montoParaCuota)}</span>
            </div>`;
    }

    html += '</div>';
    return html;
}

/**
 * Renderizar proyección con la compra incluida
 */
function renderizarProyeccionConCompra(proyeccionActual, fechaCompra, cuotas, montoParaCuota, tarjeta) {
    // Simular adición de cuotas a los períodos
    const fechaInicio = new Date(fechaCompra);
    const meses = {};

    // Copiar proyección actual
    proyeccionActual.forEach(p => {
        meses[p.periodo] = { ...p };
    });

    // Agregar cuotas de la compra simulada
    for (let i = 1; i <= cuotas; i++) {
        const fechaCuota = new Date(fechaInicio);
        fechaCuota.setMonth(fechaCuota.getMonth() + (i - 1));

        const periodo = fechaCuota.toISOString().substring(0, 7); // YYYY-MM

        if (!meses[periodo]) {
            meses[periodo] = {
                periodo,
                total_pendiente: 0,
                cuotas_pendientes: 0,
                estado_resumen: 'abierto'
            };
        }

        meses[periodo].total_pendiente += montoParaCuota;
        meses[periodo].cuotas_pendientes += 1;
    }

    // Renderizar
    const periodos = Object.values(meses).sort((a, b) => a.periodo.localeCompare(b.periodo)).slice(0, 6);

    let html = '<div class="sim-proyeccion-list">';

    periodos.forEach(p => {
        const iconos = {
            'abierto': 'bi-hourglass-split',
            'cerrado': 'bi-check-circle-fill',
            'vencido': 'bi-exclamation-triangle-fill',
            'parcial': 'bi-dash-circle'
        };

        const colores = {
            'abierto': '#0dcaf0',
            'cerrado': '#198754',
            'vencido': '#dc3545',
            'parcial': '#0d6efd'
        };

        html += `
            <div class="sim-proyeccion-item">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi ${iconos[p.estado_resumen]}" style="color:${colores[p.estado_resumen]}"></i>
                    <strong>${p.periodo}</strong>
                </div>
                <div class="text-end">
                    <div class="fw-bold">${formatearMoneda(p.total_pendiente)}</div>
                    <small class="text-muted">${p.cuotas_pendientes} cuota${p.cuotas_pendientes === 1 ? '' : 's'}</small>
                </div>
            </div>`;
    });

    html += '</div>';
    return html;
}

/**
 * Confirmar compra desde simulador (ir a formulario movimiento)
 */
function confirmarCompraSimulada() {
    const tarjetaId = parseInt(document.getElementById('simTarjeta').value);
    const fecha = document.getElementById('simFecha').value;
    const monto = parseFloat(document.getElementById('simMonto').value);
    const cuotas = parseInt(document.getElementById('simCuotas').value);
    const cuotaPagar = parseInt(document.getElementById('simCuotaPagar').value);

    // Cerrar simulador
    bootstrap.Modal.getInstance(document.getElementById('modalSimulador')).hide();

    // Abrir formulario movimiento pre-llenado
    document.getElementById('mvTarjeta').value = tarjetaId;
    document.getElementById('mvFecha').value = fecha;
    document.getElementById('mvMonto').value = monto;
    document.getElementById('mvCuotas').value = cuotas;
    document.getElementById('mvCuotaPagar').value = cuotaPagar;

    actualizarOpcionesCuota();

    const modal = new bootstrap.Modal(document.getElementById('modalMovimientoTarjeta'), { keyboard: false });
    modal.show();
}
