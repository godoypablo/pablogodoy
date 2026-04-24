<?php
// ── Integrantes ──────────────────────────────────────────
$grupo = [
    ['nombre' => 'Sebastian Llensa',     'ini' => 'SL',  'color' => '#0ea5e9'],
    ['nombre' => 'Pablo Andrés Godoy',   'ini' => 'PAG', 'color' => '#22c55e'],
    ['nombre' => 'Jorge Martin Zuttion', 'ini' => 'JMZ', 'color' => '#f59e0b'],
    ['nombre' => 'Ricardo Andrés Rivas', 'ini' => 'RAR', 'color' => '#a78bfa'],
];

// ── Toolbox de apps ──────────────────────────────────────
$toolbox = [
    ['cat' => 'Gastos compartidos', 'icono_cat' => 'bi-calculator', 'color_cat' => '#7c3aed', 'apps' => [
        ['nombre' => 'Tricount', 'desc' => 'Dividir cuentas entre los 4', 'icono' => 'bi-calculator', 'color' => '#7c3aed',
         'nota' => 'Grupo ya creado: "AUSTRALIA + NZ".',
         'android' => 'https://play.google.com/store/apps/details?id=com.nimko.tricount',
         'ios'     => 'https://apps.apple.com/app/tricount/id635071529'],
    ]],
    ['cat' => 'Vida en motorhome', 'icono_cat' => 'bi-truck', 'color_cat' => '#22c55e', 'apps' => [
        ['nombre' => 'CamperMate', 'desc' => 'Campings y servicios self-contained', 'icono' => 'bi-map', 'color' => '#22c55e',
         'nota' => 'Indispensable para pernoctar gratis. Filtrar por Self-Contained.',
         'android' => 'https://play.google.com/store/search?q=CamperMate&c=apps',
         'ios'     => 'https://apps.apple.com/app/campermate/id674664010'],
        ['nombre' => 'Penny', 'desc' => 'Gestión de lavandería y enchufes', 'icono' => 'bi-plug', 'color' => '#06b6d4',
         'nota' => '',
         'android' => 'https://play.google.com/store/search?q=Penny+laundry&c=apps',
         'ios'     => 'https://apps.apple.com/search?term=penny+app'],
        ['nombre' => 'Where is the toilet', 'desc' => 'Buscador de baños públicos', 'icono' => 'bi-geo-alt', 'color' => '#64748b',
         'nota' => '',
         'android' => 'https://play.google.com/store/search?q=Where+is+the+toilet&c=apps',
         'ios'     => 'https://apps.apple.com/search?term=where+is+the+toilet'],
    ]],
    ['cat' => 'Exploración', 'icono_cat' => 'bi-compass', 'color_cat' => '#f97316', 'apps' => [
        ['nombre' => 'Roady', 'desc' => 'Atracciones durante la ruta', 'icono' => 'bi-signpost-split', 'color' => '#f97316',
         'nota' => '',
         'android' => 'https://play.google.com/store/search?q=Roady+road+trip&c=apps',
         'ios'     => 'https://apps.apple.com/search?term=Roady'],
        ['nombre' => 'Civitatis', 'desc' => 'Tours y actividades en destino', 'icono' => 'bi-binoculars', 'color' => '#ef4444',
         'nota' => '',
         'android' => 'https://play.google.com/store/search?q=Civitatis&c=apps',
         'ios'     => 'https://apps.apple.com/search?term=Civitatis'],
    ]],
    ['cat' => 'Clima', 'icono_cat' => 'bi-cloud-sun', 'color_cat' => '#f59e0b', 'apps' => [
        ['nombre' => 'MetService NZ', 'desc' => 'Clima oficial de Nueva Zelanda', 'icono' => 'bi-cloud-sun', 'color' => '#f59e0b',
         'nota' => 'El más confiable en NZ, especialmente en montaña.',
         'android' => 'https://play.google.com/store/search?q=MetService&c=apps',
         'ios'     => 'https://apps.apple.com/nz/app/metservice-weather-new-zealand/id413613009'],
    ]],
    ['cat' => 'Conectividad & Finanzas', 'icono_cat' => 'bi-credit-card', 'color_cat' => '#3b82f6', 'apps' => [
        ['nombre' => 'HolaFly', 'desc' => 'eSIM — internet ilimitado', 'icono' => 'bi-wifi', 'color' => '#3b82f6',
         'nota' => 'Activar al bajar del avión. Soporte 24/7 WhatsApp.',
         'android' => 'https://play.google.com/store/search?q=HolaFly&c=apps',
         'ios'     => 'https://apps.apple.com/app/holafly/id1434626166'],
        ['nombre' => 'Revolut', 'desc' => 'Tarjeta sin comisiones en NZD/AUD', 'icono' => 'bi-credit-card', 'color' => '#7c3aed',
         'nota' => 'NZ es 99% tarjeta. Evita el recargo por moneda extranjera.',
         'android' => 'https://play.google.com/store/search?q=Revolut&c=apps',
         'ios'     => 'https://apps.apple.com/app/revolut/id932493382'],
        ['nombre' => 'Gaspy', 'desc' => 'Combustible al mejor precio', 'icono' => 'bi-fuel-pump', 'color' => '#ef4444',
         'nota' => 'Buscar Gull o Waitomo. Ahorra hasta 10¢/litro.',
         'android' => 'https://play.google.com/store/search?q=Gaspy&c=apps',
         'ios'     => 'https://apps.apple.com/nz/app/gaspy/id1138685235'],
        ['nombre' => "Pack'n Save", 'desc' => 'Supermercado más barato de NZ', 'icono' => 'bi-cart3', 'color' => '#f97316',
         'nota' => 'Logos amarillos. Ideal para abastecerse en la camper.',
         'android' => 'https://play.google.com/store/search?q=Pack+n+Save&c=apps',
         'ios'     => 'https://apps.apple.com/nz/app/pack-n-save/id1434333317'],
    ]],
];
$total_apps = array_sum(array_map(fn($c) => count($c['apps']), $toolbox));

// ── Mapa ─────────────────────────────────────────────────
$mapas = [
    ['titulo' => 'Recorrido completo NZ', 'desc' => 'Isla Sur → Isla Norte en motorhome',
     'icono' => 'bi-signpost-split', 'link' => 'https://maps.app.goo.gl/VwANiYRUAFdV42fv9?g_st=aw', 'embed' => ''],
];

// ── Presupuesto ───────────────────────────────────────────
$presupuesto = [
    ['item' => 'Vuelos ida y vuelta × 4',      'usd' => null, 'nzd' => null, 'estado' => 'confirmado', 'nota' => 'China Eastern Airlines — Boeing 777-300ER, clase turista'],
    ['item' => 'Alquiler motorhome (10 días)', 'usd' => 4000, 'nzd' => 7000, 'estado' => 'estimado',   'nota' => 'Maui Rentals / Motorhome Republic'],
    ['item' => 'Ferry Picton → Wellington',    'usd' =>  550, 'nzd' =>  900, 'estado' => 'estimado',   'nota' => 'Interislander o Bluebridge'],
    ['item' => 'Combustible',                  'usd' => null, 'nzd' => null, 'estado' => 'pendiente',  'nota' => 'Usar Gaspy para precio óptimo'],
    ['item' => 'Campings & alojamiento',       'usd' => null, 'nzd' => null, 'estado' => 'pendiente',  'nota' => 'Self-contained + holiday parks'],
    ['item' => 'Comida & supermercado',        'usd' => null, 'nzd' => null, 'estado' => 'pendiente',  'nota' => "Pack'n Save como base"],
    ['item' => 'Actividades & entradas',       'usd' => null, 'nzd' => null, 'estado' => 'pendiente',  'nota' => 'Hobbiton, Tongariro, Waitomo...'],
    ['item' => 'eSIM HolaFly × 4',            'usd' => null, 'nzd' => null, 'estado' => 'pendiente',  'nota' => 'Activar al llegar a NZ'],
    ['item' => 'Seguro de viaje × 4',         'usd' => null, 'nzd' => null, 'estado' => 'pendiente',  'nota' => ''],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expedición Oceanía 2027 — Los 4 Magníficos</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="/cifra/assets/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/cifra/assets/icons/apple-touch-icon.png">
    <meta name="theme-color" content="#064e3b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Expedición 2027">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --azul:     #0ea5e9;
            --verde:    #22c55e;
            --verde-d:  #16a34a;
            --teal:     #14b8a6;
            --lila:     #7c3aed;
            --dark:     #0f172a;
            --dark2:    #1e293b;
            --muted:    #94a3b8;
            --bg:       #f0fdf4;
            --borde:    #d1fae5;
            --sombra:   rgba(6,78,59,.07);
        }
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: #1e293b; overflow-x: hidden; padding-bottom: env(safe-area-inset-bottom); }

        /* ── HEADER ─────────────────────────────── */
        .v-header {
            background: linear-gradient(155deg, #064e3b 0%, #065f46 30%, #0c4a6e 70%, #0f172a 100%);
            padding: 3.5rem 0 2.5rem; position: relative; overflow: hidden;
        }
        .v-header::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(ellipse at 85% 15%, rgba(34,197,94,.28) 0%, transparent 50%),
                        radial-gradient(ellipse at 10% 90%, rgba(14,165,233,.22) 0%, transparent 55%);
        }
        .back-link { color: rgba(255,255,255,.5); font-size: .8rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: .35rem; transition: color .2s; margin-bottom: 1.5rem; }
        .back-link:hover { color: #fff; }
        .v-flags { font-size: 2.4rem; filter: drop-shadow(0 2px 8px rgba(0,0,0,.3)); }
        .v-header h1 { font-size: clamp(1.5rem, 4.5vw, 2.5rem); font-weight: 800; color: #fff; letter-spacing: -.025em; line-height: 1.1; margin: .5rem 0 .2rem; text-shadow: 0 2px 12px rgba(0,0,0,.3); }
        .v-header .v-sub { color: rgba(255,255,255,.6); font-size: .85rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
        .v-badge { display: inline-flex; align-items: center; gap: .4rem; background: rgba(34,197,94,.2); border: 1px solid rgba(34,197,94,.4); color: #86efac; border-radius: 100px; padding: .28rem .85rem; font-size: .74rem; font-weight: 600; margin-top: .75rem; }

        /* ── TABS ───────────────────────────────── */
        .v-tabs { background: linear-gradient(90deg, #064e3b, #0c4a6e); border-bottom: 1px solid rgba(255,255,255,.1); position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 16px rgba(0,0,0,.25); }
        .v-tabs .nav { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; }
        .v-tabs .nav::-webkit-scrollbar { display: none; }
        .t-btn { display: flex; align-items: center; gap: .4rem; color: rgba(255,255,255,.5) !important; background: none !important; border: none !important; border-bottom: 3px solid transparent !important; border-radius: 0 !important; padding: .85rem 1.2rem !important; font-size: .83rem; font-weight: 600; white-space: nowrap; transition: color .2s, border-color .2s; cursor: pointer; }
        .t-btn:hover { color: rgba(255,255,255,.8) !important; }
        .t-btn.active { color: #fff !important; border-bottom-color: #86efac !important; }
        .t-count { background: rgba(134,239,172,.25); color: #86efac; border-radius: 100px; padding: .05rem .45rem; font-size: .7rem; font-weight: 700; }

        /* ── LAYOUT ─────────────────────────────── */
        .tab-content { padding: 1.5rem 0 5rem; }
        .sec-title { font-size: .95rem; font-weight: 800; color: #1e293b; padding-left: .85rem; border-left: 4px solid var(--teal); margin-bottom: 1rem; }

        /* ── CARDS BASE ─────────────────────────── */
        .v-card { background: #fff; border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px var(--sombra); margin-bottom: 1rem; }
        .v-card-head { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.1rem; border-bottom: 1px solid #f0fdf4; background: linear-gradient(90deg, #f0fdf4, #f8fafc); }
        .v-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0; }
        .v-card-head h3 { font-size: .95rem; font-weight: 700; margin: 0; }
        .v-card-head p  { font-size: .775rem; color: #64748b; margin: 0; }
        .v-card-body { padding: 1rem 1.1rem; }

        /* ── VUELOS ─────────────────────────────── */
        .flight-row { display: flex; align-items: center; gap: .75rem; padding: .85rem 1.1rem; border-bottom: 1px solid #f0fdf4; }
        .flight-row:last-child { border-bottom: none; }
        .f-dir { width: 32px; height: 32px; border-radius: 8px; background: rgba(14,165,233,.1); color: var(--azul); display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
        .f-route { flex: 1; display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
        .f-airport { text-align: center; }
        .f-code { font-size: 1.1rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .f-city { font-size: .68rem; color: #64748b; font-weight: 500; }
        .f-time { font-size: .8rem; color: #475569; font-weight: 600; }
        .f-line { flex: 1; border-top: 2px dashed #cbd5e1; position: relative; min-width: 30px; }
        .f-line::after { content: '✈'; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -55%); font-size: .85rem; background: var(--bg); padding: 0 .2rem; }
        .f-info { text-align: right; font-size: .75rem; color: #64748b; flex-shrink: 0; }
        .f-date { font-size: .72rem; color: #94a3b8; margin-top: .1rem; }

        /* ── GRUPO ──────────────────────────────── */
        .members-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
        .member-card { display: flex; align-items: center; gap: .75rem; background: #f8fafc; border: 1px solid var(--borde); border-radius: 12px; padding: .75rem; }
        .m-avatar { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 800; color: #fff; flex-shrink: 0; text-shadow: 0 1px 3px rgba(0,0,0,.2); }
        .member-card h4 { font-size: .8rem; font-weight: 700; margin: 0; color: #1e293b; line-height: 1.3; }
        @media (max-width: 400px) { .members-grid { grid-template-columns: 1fr; } }

        /* ── LOGÍSTICA ──────────────────────────── */
        .log-item { display: flex; align-items: flex-start; gap: .7rem; padding: .6rem 1.1rem; border-bottom: 1px solid #f0fdf4; font-size: .855rem; }
        .log-item:last-child { border-bottom: none; }
        .log-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
        .log-item strong { color: #1e293b; display: block; font-size: .855rem; }
        .log-item span { color: #475569; font-size: .82rem; line-height: 1.5; }
        .log-item a { color: var(--azul); font-weight: 600; }

        /* ── RESUMEN RUTA ───────────────────────── */
        .route-bar { display: flex; align-items: center; gap: .5rem; padding: 1rem 1.1rem; background: linear-gradient(90deg, #f0fdf4, #eff6ff); flex-wrap: wrap; }
        .route-stop { display: flex; align-items: center; gap: .35rem; font-size: .8rem; font-weight: 700; color: #1e293b; }
        .route-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--verde); }
        .route-dot.end { background: var(--azul); }
        .route-arrow { color: #cbd5e1; font-size: .7rem; }
        .route-mid { font-size: .7rem; color: #64748b; font-weight: 500; }

        /* ── MÚSICA ─────────────────────────────── */
        .music-card { background: linear-gradient(135deg, #1a0533, #0f172a); border: 1px solid rgba(167,139,250,.2); border-radius: 16px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
        .music-disc { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #a78bfa); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; animation: spin 8s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .music-card h4 { font-size: .9rem; font-weight: 700; color: #fff; margin: 0 0 .2rem; }
        .music-card p  { font-size: .78rem; color: rgba(255,255,255,.55); margin: 0; }

        /* ── INFO CARDS (tab Info) ───────────────── */
        .info-card { background: #fff; border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; margin-bottom: 1rem; box-shadow: 0 2px 8px var(--sombra); }
        .info-card-head { display: flex; align-items: center; gap: .75rem; padding: .9rem 1.1rem; border-bottom: 1px solid #f0fdf4; background: linear-gradient(90deg, #f0fdf4, #f8fafc); }
        .info-card-head h3 { font-size: .95rem; font-weight: 700; margin: 0; }
        .info-card-body { padding: .5rem 0; }
        .info-row { display: flex; align-items: flex-start; gap: .65rem; padding: .55rem 1.1rem; border-bottom: 1px solid #f8fafc; font-size: .855rem; }
        .info-row:last-child { border-bottom: none; }
        .info-row i { flex-shrink: 0; margin-top: 2px; }
        .info-row strong { color: #1e293b; }
        .info-row span   { color: #475569; line-height: 1.5; }

        /* ── STOPS (ruta detallada) ──────────────── */
        .stops-list { display: flex; flex-direction: column; gap: .75rem; }
        .stop-card { background: #fff; border: 1px solid var(--borde); border-radius: 14px; overflow: hidden; box-shadow: 0 2px 8px var(--sombra); }
        .stop-head { display: flex; align-items: center; gap: .8rem; padding: .8rem 1rem; border-bottom: 1px solid #f0fdf4; background: linear-gradient(90deg, #f0fdf4, #f8fafc); }
        .stop-num { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, #065f46, #0c4a6e); color: #fff; display: flex; align-items: center; justify-content: center; font-size: .78rem; font-weight: 800; box-shadow: 0 2px 6px rgba(6,78,59,.3); }
        .stop-num.fin { background: linear-gradient(135deg, #16a34a, #059669); }
        .stop-head h4 { font-size: .9rem; font-weight: 700; margin: 0; color: #1e293b; }
        .stop-body { padding: .3rem 0; }
        .stop-link { display: flex; align-items: flex-start; gap: .65rem; padding: .5rem 1rem; text-decoration: none; color: #334155; font-size: .83rem; transition: background .15s; border-bottom: 1px solid #f8fafc; }
        .stop-link:last-child { border-bottom: none; }
        .stop-link:hover { background: #f0fdf4; color: #1e293b; }
        .sl-icon { width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: .8rem; }
        .stop-link strong { color: #1e293b; margin-right: .2rem; }
        .stop-tip { display: flex; align-items: flex-start; gap: .65rem; padding: .5rem 1rem; font-size: .82rem; background: rgba(245,158,11,.06); border-top: 1px solid rgba(245,158,11,.15); color: #92400e; }
        .stop-tip i { color: #d97706; flex-shrink: 0; }
        .stop-tip a { color: #b45309; font-weight: 600; }

        /* ── LICENCIA ───────────────────────────── */
        .lic-resumen { background: linear-gradient(135deg, #064e3b, #0c4a6e); border-radius: 14px; padding: 1.2rem; margin-bottom: 1rem; }
        .lic-resumen h4 { font-size: .9rem; font-weight: 700; color: #86efac; margin-bottom: .75rem; }
        .lic-check { display: flex; align-items: center; gap: .55rem; font-size: .84rem; color: rgba(255,255,255,.9); padding: .3rem 0; }
        .lic-check i { color: #86efac; flex-shrink: 0; }
        .lic-card { background: #fff; border: 1px solid var(--borde); border-radius: 14px; overflow: hidden; margin-bottom: .75rem; box-shadow: 0 2px 8px var(--sombra); }
        .lic-head { display: flex; align-items: center; gap: .7rem; padding: .8rem 1.1rem; border-bottom: 1px solid #f0fdf4; background: linear-gradient(90deg, #f0fdf4, #f8fafc); }
        .lic-num { width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #065f46, #0c4a6e); color: #fff; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 800; flex-shrink: 0; }
        .lic-head h4 { font-size: .88rem; font-weight: 700; margin: 0; }
        .lic-body { padding: .65rem 1.1rem; font-size: .83rem; color: #475569; line-height: 1.6; }
        .lic-body strong { color: #1e293b; }
        .lic-body a { color: var(--azul); font-weight: 600; }
        .lic-sub { display: flex; align-items: flex-start; gap: .6rem; padding: .4rem 0; border-bottom: 1px solid #f0fdf4; font-size: .82rem; }
        .lic-sub:last-child { border-bottom: none; }
        .lic-sub i { flex-shrink: 0; margin-top: 2px; color: var(--verde-d); }
        .lic-alert { background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.2); border-radius: 12px; padding: .85rem 1.1rem; font-size: .82rem; color: #7f1d1d; line-height: 1.6; margin-top: .5rem; }
        .lic-alert i { color: #ef4444; }

        /* ── REGLAS DE ORO ──────────────────────── */
        .reglas-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; margin-bottom: .75rem; }
        @media (max-width: 600px) { .reglas-grid { grid-template-columns: 1fr; } }
        .regla-card { background: #fff; border: 1px solid var(--borde); border-radius: 12px; padding: 1rem; display: flex; align-items: flex-start; gap: .7rem; box-shadow: 0 2px 8px var(--sombra); }
        .regla-icon { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .regla-card p { font-size: .82rem; color: #475569; margin: 0; line-height: 1.55; }
        .regla-card strong { color: #1e293b; display: block; margin-bottom: .15rem; font-size: .855rem; }
        .btn-ruta { display: flex; align-items: center; justify-content: center; gap: .5rem; background: rgba(14,165,233,.08); border: 1px solid rgba(14,165,233,.25); color: var(--azul); border-radius: 10px; padding: .65rem; font-size: .845rem; font-weight: 600; text-decoration: none; width: 100%; transition: background .2s; }
        .btn-ruta:hover { background: rgba(14,165,233,.16); color: var(--azul); }

        /* ── APPS ───────────────────────────────── */
        .toolbox-cat { display: flex; align-items: center; gap: .6rem; padding: .6rem 0 .4rem; margin-top: 1.25rem; }
        .toolbox-cat:first-child { margin-top: 0; }
        .toolbox-cat-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .85rem; flex-shrink: 0; }
        .toolbox-cat span { font-size: .78rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: .07em; }
        .app-card { background: #fff; border: 1px solid var(--borde); border-radius: 14px; padding: 1.1rem; height: 100%; box-shadow: 0 2px 8px var(--sombra); transition: transform .2s, box-shadow .2s; }
        .app-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(6,78,59,.1); }
        .app-icon { width: 42px; height: 42px; border-radius: 11px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .app-card h4 { font-size: .9rem; font-weight: 700; margin: 0; }
        .app-card .a-desc { font-size: .76rem; color: #64748b; margin: 0; }
        .app-nota { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: .5rem .75rem; font-size: .76rem; color: #166534; line-height: 1.5; margin: .7rem 0; }
        .app-nota i { color: var(--verde-d); }
        .btn-store { display: flex; align-items: center; justify-content: center; gap: .4rem; border-radius: 8px; padding: .42rem; font-size: .74rem; font-weight: 700; text-decoration: none; transition: opacity .2s; flex: 1; }
        .btn-android { background: #1a1a2e; color: #fff; }
        .btn-ios     { background: #1c1c1e; color: #fff; }
        .btn-store:hover { opacity: .82; color: #fff; }

        /* ── MAPA ───────────────────────────────── */
        .map-card { background: #fff; border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; margin-bottom: 1rem; box-shadow: 0 2px 8px var(--sombra); }
        .map-head { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.1rem; border-bottom: 1px solid #f0fdf4; background: linear-gradient(90deg, #f0fdf4, #f8fafc); }
        .map-meta { display: flex; align-items: center; gap: .75rem; }
        .map-head h3 { font-size: .9rem; font-weight: 700; margin: 0; }
        .map-head p  { font-size: .75rem; color: #64748b; margin: 0; }
        .btn-open-map { display: inline-flex; align-items: center; gap: .4rem; background: rgba(14,165,233,.1); border: 1px solid rgba(14,165,233,.25); color: var(--azul); border-radius: 8px; padding: .42rem .9rem; font-size: .78rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background .2s; }
        .btn-open-map:hover { background: rgba(14,165,233,.2); color: var(--azul); }
        .map-embed { width: 100%; height: 340px; border: none; display: block; }

        /* ── PRESUPUESTO ────────────────────────── */
        .budget-table { background: #fff; border: 1px solid var(--borde); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px var(--sombra); }
        .budget-head { display: flex; align-items: center; padding: .85rem 1.1rem; border-bottom: 2px solid #f0fdf4; background: linear-gradient(90deg, #f0fdf4, #f8fafc); }
        .budget-head span { font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .07em; }
        .budget-head .bh-item { flex: 3; }
        .budget-head .bh-usd  { flex: 1; text-align: right; }
        .budget-head .bh-nzd  { flex: 1; text-align: right; }
        .budget-head .bh-est  { flex: 1; text-align: center; }
        .budget-row { display: flex; align-items: flex-start; gap: .5rem; padding: .75rem 1.1rem; border-bottom: 1px solid #f8fafc; font-size: .845rem; }
        .budget-row:last-child { border-bottom: none; }
        .budget-row:hover { background: #f0fdf4; }
        .br-item { flex: 3; }
        .br-item strong { color: #1e293b; display: block; font-size: .845rem; }
        .br-item small  { color: #94a3b8; font-size: .74rem; }
        .br-usd, .br-nzd { flex: 1; text-align: right; font-weight: 700; color: #1e293b; font-size: .845rem; padding-top: 1px; }
        .br-nd { color: #cbd5e1; font-weight: 400; font-size: .78rem; }
        .br-est { flex: 1; text-align: center; padding-top: 2px; }
        .badge-confirmado { display: inline-block; background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.3); color: #15803d; border-radius: 100px; padding: .1rem .55rem; font-size: .68rem; font-weight: 700; }
        .badge-estimado   { display: inline-block; background: rgba(245,158,11,.12); border: 1px solid rgba(245,158,11,.3); color: #b45309; border-radius: 100px; padding: .1rem .55rem; font-size: .68rem; font-weight: 700; }
        .badge-pendiente  { display: inline-block; background: rgba(148,163,184,.1);  border: 1px solid rgba(148,163,184,.25); color: #64748b; border-radius: 100px; padding: .1rem .55rem; font-size: .68rem; font-weight: 700; }
        .budget-total { display: flex; align-items: center; justify-content: space-between; padding: .9rem 1.1rem; background: linear-gradient(90deg, #064e3b, #0c4a6e); }
        .budget-total span { color: rgba(255,255,255,.7); font-size: .8rem; font-weight: 600; }
        .budget-total strong { color: #86efac; font-size: .9rem; }

        /* ── EQUIPAJE ───────────────────────────── */
        .equip-card { background: #fff; border: 1px solid var(--borde); border-radius: 14px; padding: 1.1rem; height: 100%; box-shadow: 0 2px 8px var(--sombra); }
        .equip-card h5 { font-size: .88rem; font-weight: 700; margin-bottom: .9rem; padding-bottom: .6rem; border-bottom: 1px solid #f0fdf4; }
        .check-item { display: flex; align-items: flex-start; gap: .55rem; font-size: .82rem; color: #475569; padding: .3rem 0; line-height: 1.5; }
        .check-item i { color: var(--verde); flex-shrink: 0; margin-top: 2px; }
        .check-item a { color: var(--azul); font-weight: 600; }

        /* ── FOOTER ─────────────────────────────── */
        footer { background: linear-gradient(90deg, #064e3b, #0c4a6e); padding: 1.5rem 0; border-top: 1px solid rgba(255,255,255,.1); }
        footer p { color: rgba(255,255,255,.45); font-size: .78rem; margin: 0; }

        /* ── RESPONSIVE ─────────────────────────── */
        @media (max-width: 576px) {
            .tab-content { padding: 1rem 0 5rem; }
            .v-header { padding: 2.5rem 0 2rem; }
            .f-line::after { display: none; }
            .budget-head .bh-nzd, .br-nzd { display: none; }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="v-header">
    <div class="container">
        <a href="index.php" class="back-link"><i class="bi bi-arrow-left"></i> Inicio</a>
        <div class="v-flags mb-1">🇳🇿 🇦🇺</div>
        <h1>Expedición Oceanía 2027</h1>
        <p class="v-sub">Nueva Zelanda &amp; Australia</p>
        <div class="v-badge"><i class="bi bi-calendar-event"></i> 29 dic 2026 — 14 ene 2027</div>
    </div>
</header>

<!-- TABS -->
<div class="v-tabs">
    <div class="container">
        <ul class="nav" id="vTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="t-btn active" data-bs-toggle="tab" data-bs-target="#t-expedicion" type="button" role="tab">
                    <i class="bi bi-airplane"></i> Expedición
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="t-btn" data-bs-toggle="tab" data-bs-target="#t-ruta" type="button" role="tab">
                    <i class="bi bi-map"></i> Ruta
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="t-btn" data-bs-toggle="tab" data-bs-target="#t-apps" type="button" role="tab">
                    <i class="bi bi-phone"></i> Apps <span class="t-count"><?= $total_apps ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="t-btn" data-bs-toggle="tab" data-bs-target="#t-presupuesto" type="button" role="tab">
                    <i class="bi bi-wallet2"></i> Presupuesto
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="t-btn" data-bs-toggle="tab" data-bs-target="#t-info" type="button" role="tab">
                    <i class="bi bi-lightbulb"></i> Info
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- CONTENIDO -->
<div class="tab-content">

    <!-- ── EXPEDICIÓN ──────────────────────────────── -->
    <div class="tab-pane fade show active" id="t-expedicion" role="tabpanel">
    <div class="container">

        <!-- Grupo -->
        <div class="sec-title mt-0 mb-2" style="margin-top:0"><i class="bi bi-people me-2" style="color:var(--teal)"></i>Los 4 Magníficos</div>
        <div class="members-grid mb-3">
            <?php foreach ($grupo as $m): ?>
            <div class="member-card">
                <div class="m-avatar" style="background:<?= $m['color'] ?>"><?= $m['ini'] ?></div>
                <h4><?= htmlspecialchars($m['nombre']) ?></h4>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Vuelos -->
        <div class="sec-title"><i class="bi bi-airplane me-2" style="color:var(--azul)"></i>Itinerario de vuelos</div>
        <div class="v-card mb-3">
            <div class="v-card-head">
                <div class="v-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-airplane-engines"></i></div>
                <div><h3>China Eastern Airlines</h3><p>Boeing 777-300ER — Clase Turista</p></div>
            </div>
            <!-- Ida -->
            <div class="flight-row">
                <div class="f-dir"><i class="bi bi-airplane-fill" style="transform:rotate(45deg)"></i></div>
                <div class="f-route">
                    <div class="f-airport">
                        <div class="f-code">EZE</div>
                        <div class="f-city">Buenos Aires</div>
                        <div class="f-time">02:00 hs</div>
                        <div class="f-date">Mar 29 dic 2026</div>
                    </div>
                    <div class="f-line"></div>
                    <div class="f-airport">
                        <div class="f-code">AKL</div>
                        <div class="f-city">Auckland</div>
                        <div class="f-time">08:40 hs</div>
                        <div class="f-date">Mié 30 dic 2026</div>
                    </div>
                </div>
            </div>
            <!-- Regreso -->
            <div class="flight-row">
                <div class="f-dir" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-airplane-fill" style="transform:rotate(-135deg)"></i></div>
                <div class="f-route">
                    <div class="f-airport">
                        <div class="f-code">AKL</div>
                        <div class="f-city">Auckland</div>
                        <div class="f-time">20:55 hs</div>
                        <div class="f-date">Jue 14 ene 2027</div>
                    </div>
                    <div class="f-line"></div>
                    <div class="f-airport">
                        <div class="f-code">EZE</div>
                        <div class="f-city">Buenos Aires</div>
                        <div class="f-time">16:55 hs</div>
                        <div class="f-date">Jue 14 ene 2027</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Motorhome & Logística -->
        <div class="sec-title"><i class="bi bi-truck me-2" style="color:var(--verde)"></i>Logística en Nueva Zelanda</div>
        <div class="v-card mb-3">
            <div class="route-bar">
                <div class="route-stop"><div class="route-dot"></div> Christchurch</div>
                <div class="route-arrow"><i class="bi bi-arrow-right"></i></div>
                <div class="route-mid">Isla Sur</div>
                <div class="route-arrow"><i class="bi bi-arrow-right"></i></div>
                <div class="route-mid">Ferry</div>
                <div class="route-arrow"><i class="bi bi-arrow-right"></i></div>
                <div class="route-mid">Isla Norte</div>
                <div class="route-arrow"><i class="bi bi-arrow-right"></i></div>
                <div class="route-stop"><div class="route-dot end"></div> Auckland</div>
            </div>
            <div class="log-item">
                <div class="log-icon" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-calendar3"></i></div>
                <div><strong>Período motorhome</strong><span>3 al 11 de enero de 2027 (10 días aprox.)</span></div>
            </div>
            <div class="log-item">
                <div class="log-icon" style="background:rgba(249,115,22,.1);color:#f97316"><i class="bi bi-truck"></i></div>
                <div><strong>Alquiler</strong><span><a href="https://www.maui.co.nz/" target="_blank" rel="noopener noreferrer">Maui Rentals</a> o <a href="https://www.motorhomerepublic.com/" target="_blank" rel="noopener noreferrer">Motorhome Republic</a></span></div>
            </div>
            <div class="log-item">
                <div class="log-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-water"></i></div>
                <div><strong>Cruce de islas (Ferry)</strong><span><a href="https://www.interislander.co.nz/" target="_blank" rel="noopener noreferrer">Interislander</a> o <a href="https://www.bluebridge.co.nz/" target="_blank" rel="noopener noreferrer">Bluebridge</a> — Picton → Wellington (aprox. 3 hs)</span></div>
            </div>
            <div class="log-item">
                <div class="log-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-shield-check"></i></div>
                <div><strong>Self-Contained</strong><span>Alquilar siempre con certificado SC — habilita camping libre en zonas permitidas</span></div>
            </div>
        </div>

        <!-- Música -->
        <div class="music-card">
            <div class="music-disc">🎸</div>
            <div>
                <h4>Banda sonora del viaje</h4>
                <p>Sticky Fingers — Australia Band<br>Para entrar en clima antes de salir</p>
            </div>
        </div>

    </div>
    </div>

    <!-- ── RUTA ─────────────────────────────────────── -->
    <div class="tab-pane fade" id="t-ruta" role="tabpanel">
    <div class="container">

        <!-- Mapa -->
        <div class="sec-title mt-0" style="margin-top:0"><i class="bi bi-map me-2" style="color:var(--teal)"></i>Mapa del recorrido</div>
        <?php foreach ($mapas as $m): ?>
        <div class="map-card">
            <div class="map-head">
                <div class="map-meta">
                    <div class="v-icon" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-<?= htmlspecialchars($m['icono']) ?>"></i></div>
                    <div><h3><?= htmlspecialchars($m['titulo']) ?></h3><p><?= htmlspecialchars($m['desc']) ?></p></div>
                </div>
                <a href="<?= htmlspecialchars($m['link']) ?>" target="_blank" rel="noopener noreferrer" class="btn-open-map">
                    <i class="bi bi-box-arrow-up-right"></i> Abrir
                </a>
            </div>
            <?php if (!empty($m['embed'])): ?>
                <iframe class="map-embed" src="<?= htmlspecialchars($m['embed']) ?>" allowfullscreen loading="lazy"></iframe>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

        <!-- Paradas -->
        <div class="sec-title"><i class="bi bi-geo-alt me-2" style="color:var(--teal)"></i>Paradas del recorrido</div>
        <div class="stops-list">

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num">1</div><h4>Christchurch <small class="fw-normal text-muted">(Inicio)</small></h4></div>
                <div class="stop-body">
                    <a href="https://www.southbrightonholidaypark.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-house-door"></i></div><div><strong>Dormir:</strong> South Brighton Holiday Park</div></a>
                    <a href="https://www.packnsave.co.nz/find-a-store/south-island/canterbury/moorhouse" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-cart-check"></i></div><div><strong>Compra grande:</strong> Pack'n Save Moorhouse</div></a>
                    <a href="https://www.24hoursurgery.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="bi bi-hospital"></i></div><div><strong>Salud:</strong> 24 Hour Surgery</div></a>
                </div>
            </div>

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num">2</div><h4>Hokitika</h4></div>
                <div class="stop-body">
                    <a href="https://www.hokitikakiwi.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-house-door"></i></div><div><strong>Dormir:</strong> Hokitika Kiwi Holiday Park</div></a>
                    <a href="https://www.doc.govt.nz/parks-and-recreation/places-to-go/west-coast/places/hokitika-area/tracks/hokitika-gorge-track/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-camera"></i></div><div><strong>Visitar:</strong> Hokitika Gorge — agua turquesa</div></a>
                    <div class="stop-tip"><i class="bi bi-lightbulb-fill"></i><div>Comprar repelente <a href="https://goodbyesandfly.co.nz/" target="_blank" rel="noopener noreferrer">Goodbye Sandfly</a> — indispensable en la costa oeste.</div></div>
                </div>
            </div>

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num">3</div><h4>Westport</h4></div>
                <div class="stop-body">
                    <a href="https://cartersbeach.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-house-door"></i></div><div><strong>Dormir:</strong> Carter's Beach Holiday Park</div></a>
                    <a href="https://www.doc.govt.nz/parks-and-recreation/places-to-go/west-coast/places/westport-area/tracks/cape-foulwind-walkway/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-eye"></i></div><div><strong>Visitar:</strong> Colonia de focas Cape Foulwind</div></a>
                    <a href="https://tyregeneral.co.nz/locations/westport/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(249,115,22,.1);color:#f97316"><i class="bi bi-tools"></i></div><div><strong>Neumáticos:</strong> Tyre General Westport</div></a>
                </div>
            </div>

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num">4</div><h4>Parque Nacional Abel Tasman</h4></div>
                <div class="stop-body">
                    <a href="https://experiencekaiteriteri.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-house-door"></i></div><div><strong>Base Camp:</strong> Kaiteriteri Beach Camp</div></a>
                    <a href="https://www.abeltasmanwatertaxi.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-water"></i></div><div><strong>Logística:</strong> Water Taxi</div></a>
                    <a href="https://www.campermate.com/dump-stations/marahau-public-dump-station" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(6,182,212,.1);color:#0891b2"><i class="bi bi-droplet-fill"></i></div><div><strong>Agua/Vaciado:</strong> Marahau Dump Station</div></a>
                </div>
            </div>

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num">5</div><h4>Taupo</h4></div>
                <div class="stop-body">
                    <a href="https://www.taupodebretts.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-house-door"></i></div><div><strong>Dormir + Termas:</strong> Taupo Debretts Spa Resort</div></a>
                    <a href="https://www.lovetaupo.com/en/scenic-attractions/huka-falls/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-water"></i></div><div><strong>Visitar:</strong> Huka Falls</div></a>
                    <a href="https://www.lovetaupo.com/en/operators/otumuheke-stream-spa-park/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-brightness-high"></i></div><div><strong>Termas gratis:</strong> Otumuheke Stream</div></a>
                </div>
            </div>

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num">6</div><h4>Rotorua</h4></div>
                <div class="stop-body">
                    <a href="https://www.tepuia.com/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="bi bi-fire"></i></div><div><strong>Cultura:</strong> Te Puia — geisers y cultura maorí</div></a>
                    <a href="https://www.rotoruanz.com/visit/eat-streat" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-cup-straw"></i></div><div><strong>Comida:</strong> Eat Streat</div></a>
                    <a href="https://www.lakesprimecare.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="bi bi-hospital"></i></div><div><strong>Salud:</strong> Lakes PrimeCare</div></a>
                </div>
            </div>

            <div class="stop-card">
                <div class="stop-head"><div class="stop-num fin">7</div><h4>Auckland <small class="fw-normal text-muted">(Final)</small></h4></div>
                <div class="stop-body">
                    <a href="https://www.remueramotorlodge.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-house-door"></i></div><div><strong>Dormir:</strong> Remuera Motor Lodge</div></a>
                    <a href="https://www.aucklandairport.co.nz/" target="_blank" rel="noopener noreferrer" class="stop-link"><div class="sl-icon" style="background:rgba(100,116,139,.1);color:#64748b"><i class="bi bi-airplane-engines"></i></div><div><strong>Logística:</strong> Devolución motorhome — Aeropuerto AKL</div></a>
                </div>
            </div>

        </div>

        <!-- Licencia -->
        <div class="sec-title mt-4"><i class="bi bi-card-text me-2" style="color:var(--teal)"></i>Licencia para manejar el motorhome</div>
        <div class="lic-resumen">
            <h4>✅ Cada conductor debe llevar</h4>
            <div class="lic-check"><i class="bi bi-check-circle-fill"></i> DNI argentino vigente</div>
            <div class="lic-check"><i class="bi bi-check-circle-fill"></i> Licencia de conducir argentina (carnet físico original)</div>
            <div class="lic-check"><i class="bi bi-check-circle-fill"></i> Permiso Internacional del ACA (el librito gris)</div>
        </div>
        <div class="lic-card">
            <div class="lic-head"><div class="lic-num">1</div><h4>Licencia Nacional Argentina</h4></div>
            <div class="lic-body"><p>Llevar la licencia <strong>vigente y en formato físico original</strong>. Las copias o fotos no son válidas.</p></div>
        </div>
        <div class="lic-card">
            <div class="lic-head"><div class="lic-num">2</div><h4>Permiso Internacional de Conducir (PIC)</h4></div>
            <div class="lic-body">
                <p>La licencia argentina está en español. La ley neozelandesa exige una traducción oficial para licencias que no estén íntegramente en inglés.</p>
                <div style="height:.5rem"></div>
                <div class="lic-sub"><i class="bi bi-geo-alt-fill"></i><div><strong>¿Dónde se saca?</strong> En cualquier oficina del <strong>Automóvil Club Argentino (ACA)</strong>. No es necesario ser socio. Se entrega en el acto.</div></div>
            </div>
        </div>
        <div class="lic-card">
            <div class="lic-head"><div class="lic-num">3</div><h4>Categoría correcta</h4></div>
            <div class="lic-body">
                <div class="lic-sub"><i class="bi bi-check2-circle"></i><div><strong>Categoría B (auto):</strong> Válida hasta 3.500 kg de GVM. La mayoría de los motorhomes para 4 personas entran en este rango.</div></div>
                <div class="lic-sub"><i class="bi bi-exclamation-triangle-fill" style="color:#f59e0b"></i><div><strong>Si es más grande:</strong> Verificar que el GVM no exceda 3.500 kg, de lo contrario se necesita cat. C o E.</div></div>
            </div>
        </div>
        <div class="lic-alert"><i class="bi bi-exclamation-circle-fill me-2"></i><strong>Importante:</strong> Si se olvidan de sacar el PIC del ACA, la única alternativa en NZ es pagar a un traductor oficial certificado por la NZTA — mucho más caro y lento. ¡Saquen el librito gris antes de salir!</div>

        <!-- Reglas de Oro -->
        <div class="sec-title mt-4"><i class="bi bi-shield-check me-2" style="color:var(--teal)"></i>Reglas de Oro en la Ruta</div>
        <div class="reglas-grid">
            <div class="regla-card">
                <div class="regla-icon" style="background:rgba(14,165,233,.1);color:var(--azul)"><i class="bi bi-speedometer2"></i></div>
                <div><strong>Velocidad sugerida</strong><p>Mantenerse entre 80–90 km/h con la motorhome. Las rutas son sinuosas.</p></div>
            </div>
            <div class="regla-card">
                <div class="regla-icon" style="background:rgba(239,68,68,.1);color:#ef4444"><i class="bi bi-fuel-pump"></i></div>
                <div><strong>Combustible</strong><p>Usar <a href="https://www.gaspy.nz/" target="_blank" rel="noopener noreferrer" style="color:var(--azul);font-weight:600">Gaspy</a> para el mejor precio. Priorizar Gull o Waitomo.</p></div>
            </div>
        </div>
        <a href="https://www.journeys.nzta.govt.nz/" target="_blank" rel="noopener noreferrer" class="btn-ruta">
            <i class="bi bi-info-circle"></i> Estado de rutas en tiempo real (NZTA)
        </a>

        <!-- Equipaje -->
        <div class="sec-title mt-4"><i class="bi bi-backpack me-2" style="color:var(--teal)"></i>Equipaje esencial</div>
        <div class="row g-3">
            <div class="col-sm-6">
                <div class="equip-card">
                    <h5>📦 Check-list</h5>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Chaqueta impermeable ligera (<a href="https://www.kathmandu.co.nz/" target="_blank" rel="noopener noreferrer">Kathmandu</a>)</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Zapatillas con agarre / trekking</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Protector solar 50+ (<a href="https://skinnies.co.nz/" target="_blank" rel="noopener noreferrer">Skinnies</a>)</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Adaptador de enchufe Tipo I</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Repelente Goodbye Sandfly (comprar en Hokitika)</span></div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="equip-card">
                    <h5>📋 Documentación</h5>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Pasaporte vigente</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>DNI argentino</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Licencia de conducir (original)</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Permiso Internacional ACA (librito gris)</span></div>
                    <div class="check-item"><i class="bi bi-check-circle-fill"></i><span>Seguro de viaje</span></div>
                </div>
            </div>
        </div>

    </div>
    </div>

    <!-- ── APPS ─────────────────────────────────────── -->
    <div class="tab-pane fade" id="t-apps" role="tabpanel">
    <div class="container">
        <?php foreach ($toolbox as $cat): ?>
        <div class="toolbox-cat">
            <div class="toolbox-cat-icon" style="background:<?= $cat['color_cat'] ?>1a;color:<?= $cat['color_cat'] ?>"><i class="bi <?= $cat['icono_cat'] ?>"></i></div>
            <span><?= htmlspecialchars($cat['cat']) ?></span>
        </div>
        <div class="row g-2 mb-2">
            <?php foreach ($cat['apps'] as $app): ?>
            <div class="col-sm-6">
                <div class="app-card">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="app-icon" style="background:<?= htmlspecialchars($app['color']) ?>1a;color:<?= htmlspecialchars($app['color']) ?>">
                            <i class="bi <?= htmlspecialchars($app['icono']) ?>"></i>
                        </div>
                        <div>
                            <h4><?= htmlspecialchars($app['nombre']) ?></h4>
                            <p class="a-desc"><?= htmlspecialchars($app['desc']) ?></p>
                        </div>
                    </div>
                    <?php if (!empty($app['nota'])): ?>
                    <div class="app-nota"><i class="bi bi-info-circle me-1"></i><?= htmlspecialchars($app['nota']) ?></div>
                    <?php endif; ?>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="<?= htmlspecialchars($app['android']) ?>" target="_blank" rel="noopener noreferrer" class="btn-store btn-android"><i class="bi bi-google-play"></i> Android</a>
                        <a href="<?= htmlspecialchars($app['ios']) ?>"     target="_blank" rel="noopener noreferrer" class="btn-store btn-ios"    ><i class="bi bi-apple"></i> iOS</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    </div>

    <!-- ── PRESUPUESTO ──────────────────────────────── -->
    <div class="tab-pane fade" id="t-presupuesto" role="tabpanel">
    <div class="container">

        <div class="sec-title mt-0" style="margin-top:0"><i class="bi bi-wallet2 me-2" style="color:var(--teal)"></i>Presupuesto estimado</div>

        <div class="budget-table mb-3">
            <div class="budget-head">
                <span class="bh-item">Ítem</span>
                <span class="bh-usd">USD</span>
                <span class="bh-nzd">NZD</span>
                <span class="bh-est">Estado</span>
            </div>
            <?php foreach ($presupuesto as $row): ?>
            <div class="budget-row">
                <div class="br-item">
                    <strong><?= htmlspecialchars($row['item']) ?></strong>
                    <?php if (!empty($row['nota'])): ?><small><?= htmlspecialchars($row['nota']) ?></small><?php endif; ?>
                </div>
                <div class="br-usd">
                    <?php if ($row['usd']): ?>~USD <?= number_format($row['usd'], 0, ',', '.') ?><?php else: ?><span class="br-nd">—</span><?php endif; ?>
                </div>
                <div class="br-nzd">
                    <?php if ($row['nzd']): ?>~NZD <?= number_format($row['nzd'], 0, ',', '.') ?><?php else: ?><span class="br-nd">—</span><?php endif; ?>
                </div>
                <div class="br-est">
                    <span class="badge-<?= $row['estado'] ?>"><?= $row['estado'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="budget-total">
                <span>Total estimado por persona</span>
                <strong>A calcular 🧮</strong>
            </div>
        </div>

        <div class="v-card">
            <div class="v-card-head">
                <div class="v-icon" style="background:rgba(245,158,11,.1);color:#d97706"><i class="bi bi-info-circle"></i></div>
                <div><h3>Sobre este presupuesto</h3><p>Se irá completando a medida que se confirmen los gastos</p></div>
            </div>
            <div class="v-card-body" style="font-size:.855rem;color:#475569;line-height:1.7">
                <p style="margin:0">Los ítems marcados como <span class="badge-estimado">estimado</span> son aproximaciones basadas en tarifas actuales de temporada. Los marcados como <span class="badge-pendiente">pendiente</span> se completarán a medida que se confirmen cotizaciones. La división entre los 4 se gestiona con <strong>Tricount</strong>.</p>
            </div>
        </div>

    </div>
    </div>

    <!-- ── INFO ─────────────────────────────────────── -->
    <div class="tab-pane fade" id="t-info" role="tabpanel">
    <div class="container">

        <div class="info-card">
            <div class="info-card-head"><div class="v-icon" style="background:rgba(245,158,11,.1);color:#d97706"><i class="bi bi-currency-dollar"></i></div><h3>Datos clave del viaje</h3></div>
            <div class="info-card-body">
                <div class="info-row"><i class="bi bi-sun-fill" style="color:#f59e0b"></i><div><strong>Temporada:</strong> <span>Verano austral — temperatura ideal 18–25°C</span></div></div>
                <div class="info-row"><i class="bi bi-truck" style="color:var(--verde)"></i><div><strong>Motorhome:</strong> <span>Siempre certificado "Self-Contained" para camping libre</span></div></div>
                <div class="info-row"><i class="bi bi-credit-card-fill" style="color:#7c3aed"></i><div><strong>Moneda:</strong> <span>NZD (Nueva Zelanda) y AUD (Australia). Usar Revolut.</span></div></div>
                <div class="info-row"><i class="bi bi-wifi" style="color:var(--azul)"></i><div><strong>Internet:</strong> <span>eSIM HolaFly — activar al bajar del avión</span></div></div>
                <div class="info-row"><i class="bi bi-fuel-pump-fill" style="color:#ef4444"></i><div><strong>Combustible:</strong> <span>Buscar Gull o Waitomo con la app Gaspy</span></div></div>
                <div class="info-row"><i class="bi bi-cart3" style="color:#f97316"></i><div><strong>Supermercado:</strong> <span>Pack'n Save (logos amarillos) — el más económico de NZ</span></div></div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-head"><div class="v-icon" style="background:rgba(34,197,94,.1);color:var(--verde)"><i class="bi bi-geo-alt-fill"></i></div><h3>Destinos — Isla Norte</h3></div>
            <div class="info-card-body">
                <div class="info-row"><i class="bi bi-stars" style="color:#a78bfa"></i><div><strong>Waitomo:</strong> <span>Cuevas de luciérnagas</span></div></div>
                <div class="info-row"><i class="bi bi-tree-fill" style="color:var(--verde)"></i><div><strong>Hobbiton:</strong> <span>Set de filmación de El Señor de los Anillos, Matamata</span></div></div>
                <div class="info-row"><i class="bi bi-thermometer-high" style="color:#ef4444"></i><div><strong>Rotorua:</strong> <span>Geotermia, cultura maorí, lodo volcánico</span></div></div>
                <div class="info-row"><i class="bi bi-tsunami" style="color:var(--azul)"></i><div><strong>Tongariro:</strong> <span>Cruce alpino — trekking de un día, volcanes activos</span></div></div>
            </div>
        </div>

        <div class="info-card">
            <div class="info-card-head"><div class="v-icon" style="background:rgba(124,58,237,.1);color:#7c3aed"><i class="bi bi-geo-alt-fill"></i></div><h3>Destinos — Isla Sur</h3></div>
            <div class="info-card-body">
                <div class="info-row"><i class="bi bi-water" style="color:var(--azul)"></i><div><strong>Milford Sound:</strong> <span>Los fiordos más impresionantes del mundo</span></div></div>
                <div class="info-row"><i class="bi bi-snow2" style="color:#7c3aed"></i><div><strong>Lago Pukaki / Mt. Cook:</strong> <span>Agua turquesa glaciar y el pico más alto de NZ</span></div></div>
                <div class="info-row"><i class="bi bi-activity" style="color:#ef4444"></i><div><strong>Queenstown:</strong> <span>Capital de la aventura — bungee, esquí, rafting</span></div></div>
                <div class="info-row"><i class="bi bi-moon-stars-fill" style="color:#f59e0b"></i><div><strong>Tekapo:</strong> <span>Reserva Starlight — cielo estrellado y observatorio</span></div></div>
                <div class="info-row"><i class="bi bi-tree" style="color:var(--verde)"></i><div><strong>Manantiales de Hanmer:</strong> <span>Termas naturales en la montaña</span></div></div>
            </div>
        </div>

    </div>
    </div>

</div><!-- /tab-content -->

<footer>
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <p>🇳🇿 🇦🇺 Expedición Oceanía 2027 — Los 4 Magníficos</p>
        <p>pablogodoy.com.ar</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
