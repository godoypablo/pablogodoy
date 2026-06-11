<?php
// ── FUNCIÓN PARA CALCULAR EDAD ────────────────────────
function calcularEdad($fechaNacimiento) {
    try {
        $partes = explode('/', $fechaNacimiento);
        if (count($partes) < 3) return 0;

        $dia = (int)$partes[0];
        $mes = (int)$partes[1];
        $año = (int)$partes[2];

        $hoy = new DateTime();
        $nacimiento = new DateTime("$año-$mes-$dia");
        $edad = $hoy->diff($nacimiento)->y;

        return $edad;
    } catch (Exception $e) {
        return 0;
    }
}

// ── FUNCIÓN PARA OBTENER SIGNO DEL ZODÍACO ────────────
function obtenerSignoZodiaco($fechaNacimiento) {
    $partes = explode('/', $fechaNacimiento);
    $mes = (int)$partes[1];
    $dia = (int)$partes[0];

    if (($mes == 12 && $dia >= 22) || ($mes == 1 && $dia <= 19)) return '♑ Capricornio';
    if (($mes == 1 && $dia >= 20) || ($mes == 2 && $dia <= 18)) return '♒ Acuario';
    if (($mes == 2 && $dia >= 19) || ($mes == 3 && $dia <= 20)) return '♓ Piscis';
    if (($mes == 3 && $dia >= 21) || ($mes == 4 && $dia <= 19)) return '♈ Aries';
    if (($mes == 4 && $dia >= 20) || ($mes == 5 && $dia <= 20)) return '♉ Tauro';
    if (($mes == 5 && $dia >= 21) || ($mes == 6 && $dia <= 20)) return '♊ Geminis';
    if (($mes == 6 && $dia >= 21) || ($mes == 7 && $dia <= 22)) return '♋ Cancer';
    if (($mes == 7 && $dia >= 23) || ($mes == 8 && $dia <= 22)) return '♌ Leo';
    if (($mes == 8 && $dia >= 23) || ($mes == 9 && $dia <= 22)) return '♍ Virgo';
    if (($mes == 9 && $dia >= 23) || ($mes == 10 && $dia <= 22)) return '♎ Libra';
    if (($mes == 10 && $dia >= 23) || ($mes == 11 && $dia <= 21)) return '♏ Escorpio';
    if (($mes == 11 && $dia >= 22) || ($mes == 12 && $dia <= 21)) return '♐ Sagitario';
    return '♑ Capricornio';
}

// ── GRUPO DE VIAJEROS ────────────────────────────────────
$grupo_raw = [
    ['nombre' => 'SEBASTIAN LLENSA',     'dni' => '25546552', 'nacimiento' => '15/12/1976', 'zodiaco' => '♐ Sagitario'],
    ['nombre' => 'PABLO ANDRÉS GODOY',   'dni' => '22342593', 'nacimiento' => '04/02/1972', 'zodiaco' => '♒ Acuario'],
    ['nombre' => 'JORGE MARTIN ZUTTION', 'dni' => '21512748', 'nacimiento' => '18/11/1970', 'zodiaco' => '♏ Escorpio'],
    ['nombre' => 'RICARDO ANDRÉS RIVAS', 'dni' => '255466083', 'nacimiento' => '07/03/1977', 'zodiaco' => '♓ Piscis'],
];

// Agregar edad y signo del zodíaco a cada persona
foreach ($grupo_raw as &$persona) {
    $persona['edad'] = calcularEdad($persona['nacimiento']);
    $persona['zodiaco'] = obtenerSignoZodiaco($persona['nacimiento']);
}

// Ordenar por edad descendente (mayor a menor)
usort($grupo_raw, function($a, $b) {
    return $b['edad'] - $a['edad'];
});

$grupo = $grupo_raw;

// ── ITINERARIO DE VUELOS ─────────────────────────────────
$vuelos = [
    [
        'num' => '1',
        'ruta' => 'Buenos Aires → Auckland',
        'fecha' => 'Mar 29 DIC 2026 → Mié 30 DIC 2026',
        'salida' => '02:00 (EZE)',
        'llegada' => '08:40 (AKL)',
        'aerolinea' => 'China Eastern Airlines MU 0746',
        'duracion' => '14h 40min',
        'avion' => 'Boeing 777-300ER',
        'clase' => 'Turista',
        'estado' => '✅ Confirmado'
    ],
    [
        'num' => '2',
        'ruta' => 'Auckland → Sydney',
        'fecha' => 'Mié 30 DIC 2026',
        'salida' => '16:30 (AKL)',
        'llegada' => '18:15 (SYD)',
        'aerolinea' => 'Air New Zealand NZ111',
        'duracion' => '1h 45min',
        'avion' => 'Estándar',
        'clase' => 'Turista',
        'estado' => '✅ Confirmado'
    ],
    [
        'num' => '3',
        'ruta' => 'Sydney → Christchurch',
        'fecha' => 'Dom 3 ENE 2027',
        'salida' => '07:20 (SYD - Kingsford Smith T1)',
        'llegada' => '12:25 (CHC)',
        'aerolinea' => 'Qantas Airways QF 191',
        'duracion' => '3h 5min',
        'avion' => 'Estándar',
        'clase' => 'Turista',
        'estado' => '✅ Confirmado'
    ],
    [
        'num' => '4',
        'ruta' => 'Auckland → Buenos Aires (REGRESO)',
        'fecha' => 'Jue 14 ENE 2027 → Vie 15 ENE 2027',
        'salida' => '20:55 (AKL)',
        'llegada' => '16:55 (EZE)',
        'aerolinea' => 'China Eastern Airlines MU 0745',
        'duracion' => '12h',
        'avion' => 'Boeing 777-300ER',
        'clase' => 'Turista',
        'estado' => '✅ Confirmado'
    ]
];

// ── PRESUPUESTO ──────────────────────────────────────────
$presupuesto = [
    ['item' => '✈️ Vuelos internacionales Buenos Aires ↔ Auckland × 4', 'estado' => '✅ Confirmado', 'nota' => 'China Eastern Airlines MU 0746/0745 — USD 1,177.15 (aprox. USD 294/pax)'],
    ['item' => '✈️ Vuelo Auckland → Sydney × 4', 'estado' => '✅ Confirmado', 'nota' => 'Air New Zealand NZ111 — 30 DIC 2026 (aprox. USD 80-100/pax)'],
    ['item' => '✈️ Vuelo Sydney → Christchurch × 4', 'estado' => '✅ Confirmado', 'nota' => 'Qantas Airways QF 191 — USD 1,776.39 (aprox. USD 444/pax con descuento Prime)'],
    ['item' => '🏨 Hospedaje Sydney (3 noches × 4 pax)', 'estado' => '📋 Estimado', 'nota' => 'Mié 30 dic - Dom 3 ene — ~NZD 350-450/noche (aprox. USD 210-270/noche)'],
    ['item' => '🏨 Hospedaje Auckland (1-2 noches × 4 pax)', 'estado' => '📋 Estimado', 'nota' => 'Mar 12/13 ene - Jue 14 ene — ~NZD 250-350/noche (aprox. USD 150-210/noche)'],
    ['item' => '🚐 Alquiler motorhome (8-9 días)', 'estado' => '📋 Estimado', 'nota' => 'Maui/Motorhome Republic — USD 4,000-5,000 ó NZD 7,000-8,500 (ajustado a 8-9 días vs 10)'],
    ['item' => '🏕️ Campings & Holiday Parks (7 noches)', 'estado' => '📋 Estimado', 'nota' => 'Self-contained rated — ~NZD 50-80/noche (aprox. USD 30-48/noche) = ~NZD 350-560'],
    ['item' => '⛴️ Ferry Picton → Wellington', 'estado' => '📋 Estimado', 'nota' => 'Interislander o Bluebridge — USD 550 / NZD 900 (4 pax + motorhome)'],
    ['item' => '⛽ Combustible (8-9 días, ~1200-1500 km)', 'estado' => '📋 Estimado', 'nota' => 'App Gaspy — ~NZD 200-300 (USD 120-180) con Gull/Waitomo'],
    ['item' => '🛒 Comida & supermercado', 'estado' => '📋 Estimado', 'nota' => 'Pack\'n Save como base — ~NZD 800-1200 (USD 480-720) para 16 días × 4 pax'],
    ['item' => '🎫 Actividades & entradas', 'estado' => '📋 Estimado', 'nota' => 'Hobbiton (USD 100-120), Tongariro (USD 50-80), Milford Sound (USD 150-200)... = USD 1,000-1,500'],
    ['item' => '📱 eSIM HolaFly × 4', 'estado' => '⏳ Pendiente', 'nota' => 'Internet ilimitado NZ/AUS — ~USD 40-50/pax = USD 160-200'],
    ['item' => '🛡️ Seguro de viaje × 4', 'estado' => '⏳ Pendiente', 'nota' => 'Obligatorio Oceanía — ~USD 35-50/pax = USD 140-200 (20 días)'],
    ['item' => '💳 Gastos diversos (tips, parking, etc.)', 'estado' => '⏳ Estimado', 'nota' => 'Estimado — ~USD 200-300 (contingencia del 5-10%)'],
];

// ── PARADAS PRINCIPALES ──────────────────────────────────
$paradas = [
    ['num' => 1, 'ciudad' => 'Christchurch', 'tipo' => 'Inicio', 'hospedaje' => 'South Brighton Holiday Park', 'supermercado' => 'Pack\'n Save Moorhouse', 'salud' => '24 Hour Surgery', 'km_siguiente' => 280, 'siguiente' => 'Hokitika', 'coords' => '-43.5321,172.6362', 'info' => 'Recojo del motorhome en el aeropuerto. Punto de partida de la aventura.'],
    ['num' => 2, 'ciudad' => 'Hokitika', 'tipo' => 'Isla Sur', 'hospedaje' => 'Hokitika Kiwi Holiday Park', 'supermercado' => 'Hokitika Supermarket', 'salud' => 'Hokitika Medical Centre', 'km_siguiente' => 110, 'siguiente' => 'Westport', 'coords' => '-42.7171,171.1945', 'info' => 'Costa oeste de la Isla Sur. Gorge turquesa. Comprar repelente Goodbye Sandfly aquí.'],
    ['num' => 3, 'ciudad' => 'Westport', 'tipo' => 'Isla Sur', 'hospedaje' => 'Carter\'s Beach Holiday Park', 'supermercado' => 'Countdown Westport', 'salud' => 'Westport Medical Centre', 'km_siguiente' => 115, 'siguiente' => 'Abel Tasman', 'coords' => '-42.4495,171.6053', 'info' => 'Colonia de focas Cape Foulwind. Taller de neumáticos disponible.'],
    ['num' => 4, 'ciudad' => 'Abel Tasman NP', 'tipo' => 'Isla Sur', 'hospedaje' => 'Kaiteriteri Beach Camp', 'supermercado' => 'Abel Tasman Supermarket', 'salud' => 'Motueka Medical Centre', 'km_siguiente' => 240, 'siguiente' => 'Taupo', 'coords' => '-41.8400,172.0300', 'info' => 'Parque Nacional. Water taxi disponible. Actividades acuáticas y trekking.'],
    ['num' => 5, 'ciudad' => 'Taupo', 'tipo' => 'Isla Sur/Norte', 'hospedaje' => 'Taupo Debretts Spa Resort', 'supermercado' => 'Countdown Taupo', 'salud' => 'Taupo Medical Centre', 'km_siguiente' => 85, 'siguiente' => 'Rotorua', 'coords' => '-38.1368,176.2497', 'info' => 'Termas naturales Otumuheke (gratis). Huka Falls. Cruce entre islas.'],
    ['num' => 6, 'ciudad' => 'Rotorua', 'tipo' => 'Isla Norte', 'hospedaje' => 'Rotorua Central Holiday Park', 'supermercado' => 'Countdown Rotorua', 'salud' => 'Lakes PrimeCare', 'km_siguiente' => 238, 'siguiente' => 'Auckland', 'coords' => '-38.1368,176.2497', 'info' => 'Te Puia: geisers y cultura maorí. Lodo volcánico. Rotorua informativo.'],
    ['num' => 7, 'ciudad' => 'Auckland', 'tipo' => 'Fin', 'hospedaje' => 'Remuera Motor Lodge', 'supermercado' => 'Countdown Downtown', 'salud' => 'Auckland Medical Centre', 'km_siguiente' => 0, 'siguiente' => 'Aeropuerto', 'coords' => '-37.0082,174.7850', 'info' => 'Devolución del motorhome en el aeropuerto. Final de la aventura.'],
];

// ── APPS POR CATEGORÍA ───────────────────────────────────
$apps = [
    ['cat' => '💰 Gastos compartidos', 'items' => [
        ['nombre' => 'Tricount', 'desc' => 'Dividir cuentas entre los 4', 'nota' => 'Grupo ya creado: "AUSTRALIA + NZ"'],
    ]],
    ['cat' => '🚐 Vida en motorhome', 'items' => [
        ['nombre' => 'CamperMate', 'desc' => 'Campings y servicios self-contained', 'nota' => 'Indispensable para pernoctar gratis. Filtrar por Self-Contained'],
        ['nombre' => 'Penny', 'desc' => 'Gestión de lavandería y enchufes', 'nota' => ''],
        ['nombre' => 'Where is the toilet', 'desc' => 'Buscador de baños públicos', 'nota' => ''],
    ]],
    ['cat' => '🧭 Exploración', 'items' => [
        ['nombre' => 'Roady', 'desc' => 'Atracciones durante la ruta', 'nota' => ''],
        ['nombre' => 'Civitatis', 'desc' => 'Tours y actividades en destino', 'nota' => ''],
    ]],
    ['cat' => '☁️ Clima', 'items' => [
        ['nombre' => 'MetService NZ', 'desc' => 'Clima oficial de Nueva Zelanda', 'nota' => 'El más confiable en NZ, especialmente en montaña'],
    ]],
    ['cat' => '📱 Conectividad & Finanzas', 'items' => [
        ['nombre' => 'HolaFly', 'desc' => 'eSIM — Internet ilimitado', 'nota' => 'Activar al bajar del avión. Soporte 24/7 WhatsApp'],
        ['nombre' => 'Revolut', 'desc' => 'Tarjeta sin comisiones en NZD/AUD', 'nota' => 'NZ es 99% tarjeta. Evita el recargo por moneda extranjera'],
        ['nombre' => 'Gaspy', 'desc' => 'Combustible al mejor precio', 'nota' => 'Buscar Gull o Waitomo. Ahorra hasta 10¢/litro'],
        ['nombre' => "Pack'n Save", 'desc' => 'Supermercado más barato de NZ', 'nota' => 'Logos amarillos. Ideal para abastecerse en la camper'],
    ]],
];

// ── DESTINOS ─────────────────────────────────────────────
$destinos = [
    ['isla' => 'Norte', 'lugares' => ['Hobbiton (Matamata)', 'Waitomo (Cuevas)', 'Rotorua (Geotermia)', 'Tongariro (Trekking)']],
    ['isla' => 'Sur',   'lugares' => ['Milford Sound (Fiordos)', 'Lago Pukaki (Glaciar)', 'Mount Cook (Pico)', 'Queenstown (Aventura)']],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expedición Oceanía 2027 — Los 4 Magníficos</title>

    <!-- PWA: Web App Manifest -->
    <link rel="manifest" href="/manifest-viaje.json">

    
    <!-- PWA: Meta tags -->
    <meta name="theme-color" content="#064e3b">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Viaje NZL-AUS">
    <link rel="apple-touch-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 180 180'><rect fill='%23064e3b' width='180' height='180' rx='45'/><text x='90' y='128' font-size='100' text-anchor='middle'>🇳🇿</text></svg>">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/favicon.svg">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #064e3b;
            --accent: #0ea5e9;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --purple: #7c3aed;
        }
        body {
            background: linear-gradient(135deg, #f0fdf4 0%, #f0f9ff 100%);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .main-wrapper { max-width: 1100px; margin: 0 auto; padding: 15px; }

        /* ENCABEZADO */
        .v-header {
            background: white;
            color: var(--primary);
            padding: 25px 20px;
            border-bottom: 2px solid #e0e7ff;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .v-header-content { flex: 1; min-width: 250px; }
        .v-header h1 { font-size: 1.6rem; font-weight: 600; margin: 5px 0; letter-spacing: 0.3px; }
        .v-header .subtitle { font-size: 0.85rem; opacity: 0.7; font-weight: 500; }
        .flags { font-size: 2rem; margin: 5px 0; }
        .btn-mapa {
            padding: 10px 20px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            white-space: nowrap;
            transition: 0.3s;
            font-size: 0.9rem;
        }
        .btn-mapa:hover { background: #0d8fcf; color: white; }

        /* TABS COMO BOTONES */
        .nav-tabs {
            border-bottom: none !important;
            background: transparent;
            border-radius: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 15px 0;
            justify-content: center;
        }
        .nav-tabs .nav-link {
            color: #64748b;
            border: 2px solid #e0e7ff !important;
            padding: 8px 14px;
            font-weight: 600;
            transition: all 0.3s;
            background: white;
            border-radius: 6px;
            font-size: 0.85rem;
        }
        .nav-tabs .nav-link:hover {
            color: var(--accent);
            border-color: var(--accent);
            box-shadow: 0 2px 8px rgba(14,165,233,0.15);
            transform: translateY(-1px);
        }
        .nav-tabs .nav-link.active {
            color: white !important;
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            box-shadow: 0 2px 8px rgba(14,165,233,0.25);
        }

        /* CONTENIDO TABS */
        .tab-content { background: transparent; padding: 0; border-radius: 0; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* SECCIONES */
        .sec-group {
            background: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }
        .sec-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* GRUPO */
        .group-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        .member-card {
            background: linear-gradient(135deg, rgba(6,78,59,.05) 0%, rgba(14,165,233,.05) 100%);
            padding: 12px;
            border-radius: 6px;
            border-left: 3px solid var(--accent);
        }
        .member-name { font-weight: 700; color: var(--primary); font-size: 0.95rem; }
        .member-dni { font-size: 0.8rem; color: #64748b; margin-top: 4px; }

        /* VUELOS */
        .flight-item {
            background: linear-gradient(135deg, rgba(14,165,233,.08) 0%, rgba(34,197,94,.08) 100%);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 12px;
            border-left: 3px solid var(--accent);
        }
        .flight-num {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 3px 10px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.75rem;
            margin-bottom: 8px;
        }
        .flight-route { font-weight: 700; font-size: 0.95rem; color: var(--primary); margin-bottom: 8px; }
        .flight-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; font-size: 0.8rem; }
        .flight-detail-label { font-weight: 600; color: var(--primary); font-size: 0.78rem; }
        .flight-detail-value { color: #64748b; margin-top: 2px; }

        /* PRESUPUESTO */
        .budget-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            border-left: 3px solid var(--accent);
        }
        .budget-item-title { font-weight: 600; color: #1e293b; font-size: 0.9rem; }
        .budget-item-status {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 5px;
            padding: 2px 6px;
            border-radius: 3px;
            background: rgba(34,197,94,.1);
        }
        .budget-item-nota { font-size: 0.78rem; color: #64748b; margin-top: 4px; }

        /* PARADAS */
        .parada-item {
            background: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            border-left: 3px solid var(--accent);
            cursor: pointer;
            transition: all 0.3s;
        }
        .parada-item:hover {
            box-shadow: 0 4px 12px rgba(14,165,233,0.15);
            transform: translateX(3px);
            background: #f8fafc;
        }
        .parada-num {
            display: inline-block;
            background: var(--accent);
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            text-align: center;
            line-height: 26px;
            font-weight: 700;
            margin-right: 8px;
            font-size: 0.8rem;
        }
        .parada-ciudad { font-weight: 600; color: var(--primary); font-size: 0.95rem; }
        .parada-hospedaje { font-size: 0.85rem; color: #64748b; margin-top: 3px; }
        .parada-distancia { font-size: 0.75rem; background: rgba(14,165,233,0.1); color: var(--accent); padding: 2px 6px; border-radius: 3px; display: inline-block; margin-top: 4px; font-weight: 600; }

        /* MODALES */
        .modal-backdrop { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-backdrop.active { display: block; }
        .parada-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 20px;
            max-width: 500px;
            width: 90%;
            z-index: 1001;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .parada-modal.active { display: block; }
        .parada-modal h2 { color: var(--primary); margin: 0 0 10px 0; font-size: 1.2rem; }
        .parada-modal p { margin: 5px 0; font-size: 0.9rem; color: #64748b; }
        .parada-modal-map {
            width: 100%;
            height: 250px;
            border-radius: 8px;
            margin: 15px 0;
            overflow: hidden;
            background: #f0f0f0;
        }
        .parada-modal-info {
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 0.85rem;
            line-height: 1.5;
        }
        .parada-modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }
        .parada-modal-close:hover { color: var(--primary); }

        /* APPS */
        .app-category { margin-bottom: 15px; }
        .app-cat-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary);
            margin-bottom: 10px;
            padding-left: 10px;
            border-left: 3px solid var(--accent);
        }
        .app-item {
            background: white;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 6px;
            border-left: 2px solid #e0e7ff;
        }
        .app-name { font-weight: 600; color: var(--primary); font-size: 0.9rem; }
        .app-desc { font-size: 0.8rem; color: #64748b; margin-top: 2px; }

        /* TIMELINE */
        .timeline-item {
            display: flex;
            margin-bottom: 12px;
            gap: 10px;
        }
        .timeline-marker {
            width: 32px;
            height: 32px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
            font-size: 0.75rem;
        }
        .timeline-content {
            flex: 1;
            background: white;
            padding: 10px;
            border-radius: 5px;
            border-left: 3px solid var(--accent);
        }
        .timeline-content.group-odd {
            background: #ECECF1;
        }
        .timeline-content.group-even {
            background: white;
        }
        .timeline-title { font-weight: 700; color: var(--primary); font-size: 0.9rem; }
        .timeline-date { font-size: 0.8rem; color: #64748b; margin-top: 2px; }
        .timeline-time { font-size: 0.85rem; font-weight: 600; color: var(--primary); margin-top: 4px; }
        .timeline-note { font-size: 0.75rem; color: #999; margin-top: 4px; }

        /* DOCUMENTACIÓN */
        .doc-item {
            background: white;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }
        .doc-icon { color: var(--success); font-weight: 700; }

        /* DESTINOS */
        .destination-island {
            background: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 8px;
        }
        .island-title { font-weight: 700; font-size: 0.95rem; color: var(--primary); margin-bottom: 6px; }
        .place-item { color: #1e293b; font-size: 0.85rem; margin-bottom: 4px; }
        .place-item::before { content: '✈️ '; }

        /* FOOTER */
        footer {
            text-align: center;
            padding: 15px;
            color: #64748b;
            font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .main-wrapper { padding: 10px; }
            .v-header { flex-direction: column; text-align: center; padding: 20px 15px; }
            .v-header h1 { font-size: 1.4rem; }
            .v-header-content { text-align: center; }
            .flags { font-size: 1.8rem; }
            .btn-mapa { padding: 8px 16px; font-size: 0.85rem; }
            .nav-tabs { padding: 10px 0; gap: 6px; }
            .nav-tabs .nav-link { padding: 7px 12px; font-size: 0.8rem; }
            .sec-group { padding: 12px; margin-bottom: 12px; }
            .sec-title { font-size: 1rem; margin-bottom: 10px; }
            .flight-details { grid-template-columns: repeat(2, 1fr); }
            .group-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 480px) {
            .main-wrapper { padding: 8px; }
            .v-header { padding: 15px 10px; margin-bottom: 15px; }
            .v-header h1 { font-size: 1.2rem; margin: 3px 0; }
            .v-header .subtitle { font-size: 0.75rem; }
            .flags { font-size: 1.5rem; }
            .nav-tabs { padding: 8px 0; gap: 5px; }
            .nav-tabs .nav-link { padding: 6px 10px; font-size: 0.75rem; }
            .sec-group { padding: 10px; margin-bottom: 10px; }
            .sec-title { font-size: 0.95rem; gap: 5px; }
            .flight-details { grid-template-columns: 1fr; gap: 8px; }
        }
    </style>
</head>
<body>

<div class="main-wrapper">

    <!-- ENCABEZADO CON BOTÓN MAPA -->
    <div class="v-header">
        <div class="v-header-content">
            <div class="flags">🇳🇿</div>
            <h1>Gira Oceanía 2027</h1>
        </div>
        <a href="https://maps.app.goo.gl/VwANiYRUAFdV42fv9?g_st=aw" target="_blank" class="btn-mapa">
            <i class="bi bi-geo-alt-fill"></i> Mapa del Recorrido
        </a>
    </div>

    <!-- TABS -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-itinerario" data-bs-toggle="tab" data-bs-target="#itinerario" type="button" role="tab">
                <i class="bi bi-airplane"></i> Vuelos
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-cronologia" data-bs-toggle="tab" data-bs-target="#cronologia" type="button" role="tab">
                <i class="bi bi-clock-history"></i> Cronología
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-paradas" data-bs-toggle="tab" data-bs-target="#paradas" type="button" role="tab">
                <i class="bi bi-map-fill"></i> Paradas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-presupuesto" data-bs-toggle="tab" data-bs-target="#presupuesto" type="button" role="tab">
                <i class="bi bi-receipt"></i> Presupuesto
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-grupo" data-bs-toggle="tab" data-bs-target="#grupo" type="button" role="tab">
                <i class="bi bi-people-fill"></i> Integrantes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-astrologia" data-bs-toggle="tab" data-bs-target="#astrologia" type="button" role="tab">
                <i class="bi bi-star-fill"></i> Astrología
            </button>
        </li>
    </ul>

    <!-- CONTENIDO TABS -->
    <div class="tab-content">

        <!-- TAB 1: ITINERARIO DE VUELOS -->
        <div class="tab-pane fade show active" id="itinerario" role="tabpanel">
            <div class="sec-group">
                <div class="sec-title"><i class="bi bi-airplane"></i> Vuelos</div>
                <?php foreach ($vuelos as $vuelo): ?>
                    <div class="flight-item">
                        <span class="flight-num">Vuelo <?= $vuelo['num'] ?></span>
                        <div class="flight-route"><?= $vuelo['ruta'] ?></div>
                        <div class="flight-details">
                            <div>
                                <div class="flight-detail-label">📅 Fecha</div>
                                <div class="flight-detail-value"><?= $vuelo['fecha'] ?></div>
                            </div>
                            <div>
                                <div class="flight-detail-label">🕐 Salida</div>
                                <div class="flight-detail-value"><?= $vuelo['salida'] ?></div>
                            </div>
                            <div>
                                <div class="flight-detail-label">🛬 Llegada</div>
                                <div class="flight-detail-value"><?= $vuelo['llegada'] ?></div>
                            </div>
                            <div>
                                <div class="flight-detail-label">✈️ Aerolínea</div>
                                <div class="flight-detail-value"><?= $vuelo['aerolinea'] ?></div>
                            </div>
                            <div>
                                <div class="flight-detail-label">⏱️ Duración</div>
                                <div class="flight-detail-value"><?= $vuelo['duracion'] ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB 2: CRONOLOGÍA -->
        <div class="tab-pane fade" id="cronologia" role="tabpanel">
            <div class="sec-group">
                <div class="sec-title"><i class="bi bi-clock-history"></i> Cronología Completa del Viaje</div>
                <div style="display: flex; flex-direction: column; gap: 15px;">

                    <div class="timeline-item">
                        <div class="timeline-marker">1</div>
                        <div class="timeline-content group-odd">
                            <div class="timeline-title">📍 Buenos Aires — Aeropuerto Ezeiza</div>
                            <div class="timeline-date">Martes 29 de diciembre de 2026</div>
                            <div class="timeline-time">⏰ Llegar: 23:00 (3 horas antes)</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">✈️</div>
                        <div class="timeline-content group-odd">
                            <div class="timeline-title">✈️ Vuelo 1: Buenos Aires → Auckland</div>
                            <div class="timeline-date">Mar 29 dic → Mié 30 dic</div>
                            <div class="timeline-time">🕐 02:00 - 08:40 | Duración: 14h 40min</div>
                            <div class="timeline-note">China Eastern Airlines MU 0746</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">✓</div>
                        <div class="timeline-content group-odd">
                            <div class="timeline-title">📍 Auckland — Conexión</div>
                            <div class="timeline-date">Miércoles 30 de diciembre</div>
                            <div class="timeline-time">⏰ 08:40 - 16:30 | Disponible: 7h 50min</div>
                            <div class="timeline-note">Tiempo suficiente para desembarque, equipaje y check-in</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">2</div>
                        <div class="timeline-content group-even">
                            <div class="timeline-title">📍 Auckland — Aeropuerto (segundo vuelo)</div>
                            <div class="timeline-date">Miércoles 30 de diciembre</div>
                            <div class="timeline-time">⏰ Llegar: 13:30 (3 horas antes)</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">✈️</div>
                        <div class="timeline-content group-even">
                            <div class="timeline-title">✈️ Vuelo 2: Auckland → Sydney</div>
                            <div class="timeline-date">Miércoles 30 de diciembre 2026</div>
                            <div class="timeline-time">🕐 16:30 - 18:15 | Duración: 1h 45min</div>
                            <div class="timeline-note">Air New Zealand NZ111</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">🇦🇺</div>
                        <div class="timeline-content group-even" onclick="openModal('modal-sydney')" style="cursor: pointer;">
                            <div class="timeline-title">📍 Sydney — Hospedaje</div>
                            <div class="timeline-date">Mié 30 dic — Dom 3 ene (3 noches)</div>
                            <div class="timeline-time">⏰ 18:15 - 07:20 | Disponible: 2d 13h</div>
                            <div style="font-size: 0.75rem; color: var(--accent); margin-top: 6px;">🗺️ Haz clic para ver el mapa</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">3</div>
                        <div class="timeline-content group-odd">
                            <div class="timeline-title">📍 Sydney — Aeropuerto Kingsford Smith</div>
                            <div class="timeline-date">Domingo 3 de enero de 2027</div>
                            <div class="timeline-time">⏰ Llegar: 04:20 (3 horas antes)</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">✈️</div>
                        <div class="timeline-content group-odd">
                            <div class="timeline-title">✈️ Vuelo 3: Sydney → Christchurch</div>
                            <div class="timeline-date">Domingo 3 de enero 2027</div>
                            <div class="timeline-time">🕐 07:20 - 12:25 | Duración: 3h 5min</div>
                            <div class="timeline-note">Qantas Airways QF 191</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">🚐</div>
                        <div class="timeline-content group-odd">
                            <div class="timeline-title">🚐 Christchurch — Motorhome (8-9 días)</div>
                            <div class="timeline-date">Dom 3 ene — Mar 11-12 ene (8-9 días)</div>
                            <div class="timeline-time">⏰ 12:25 - Recorrido Isla Sur e Isla Norte</div>
                            <div class="timeline-note">Devolución en Auckland el 11 o 12 de enero</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">🇳🇿</div>
                        <div class="timeline-content group-even" onclick="openModal('modal-auckland')" style="cursor: pointer;">
                            <div class="timeline-title">📍 Auckland — Hospedaje</div>
                            <div class="timeline-date">Mar 12-13 ene — Jue 14 ene (1-2 noches)</div>
                            <div class="timeline-time">⏰ Post motorhome | Check-in pendiente</div>
                            <div style="font-size: 0.75rem; color: var(--accent); margin-top: 6px;">🗺️ Haz clic para ver el mapa</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">4</div>
                        <div class="timeline-content group-even">
                            <div class="timeline-title">📍 Auckland — Aeropuerto (regreso)</div>
                            <div class="timeline-date">Jueves 14 de enero de 2027</div>
                            <div class="timeline-time">⏰ Llegar: 17:55 (3 horas antes)</div>
                        </div>
                    </div>

                    <div class="timeline-item">
                        <div class="timeline-marker">✈️</div>
                        <div class="timeline-content group-even">
                            <div class="timeline-title">✈️ Vuelo 4: Auckland → Buenos Aires</div>
                            <div class="timeline-date">Jue 14 ene → Vie 15 ene</div>
                            <div class="timeline-time">🕐 20:55 - 16:55 | Duración: 12h</div>
                            <div class="timeline-note">China Eastern Airlines MU 0745</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- TAB 3: PARADAS -->
        <div class="tab-pane fade" id="paradas" role="tabpanel">
            <div class="sec-group">
                <div class="sec-title"><i class="bi bi-map-fill"></i> Recorrido en Motorhome</div>
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px;">💡 Haz clic en cualquier ciudad para ver su ubicación en el mapa</p>
                <?php foreach ($paradas as $parada): ?>
                    <div class="parada-item" onclick="openModal('modal-<?= $parada['num'] ?>')">
                        <span class="parada-num"><?= $parada['num'] ?></span>
                        <div>
                            <div class="parada-ciudad"><?= $parada['ciudad'] ?> <small style="color: #999;">— <?= $parada['tipo'] ?></small></div>
                            <div class="parada-hospedaje">🏨 <?= $parada['hospedaje'] ?></div>
                            <?php if ($parada['km_siguiente'] > 0): ?>
                                <span class="parada-distancia">📍 <?= $parada['km_siguiente'] ?> km → <?= $parada['siguiente'] ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- MODAL PARA ESTA PARADA -->
                    <div class="modal-backdrop" id="backdrop-<?= $parada['num'] ?>" onclick="closeModal('modal-<?= $parada['num'] ?>')"></div>
                    <div class="parada-modal" id="modal-<?= $parada['num'] ?>">
                        <button class="parada-modal-close" onclick="closeModal('modal-<?= $parada['num'] ?>')">✕</button>
                        <h2><?= $parada['ciudad'] ?></h2>
                        <p style="font-size: 0.8rem; color: #999;"><?= $parada['tipo'] ?></p>

                        <div class="parada-modal-map">
                            <iframe width="100%" height="100%" frameborder="0" style="border:0;" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d50000!2d<?= explode(',', $parada['coords'])[1] ?>!3d<?= explode(',', $parada['coords'])[0] ?>!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f10!5e0!3m2!1sen!2snz!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
                        </div>

                        <div class="parada-modal-info">
                            <strong>📋 Información de la Parada:</strong><br>
                            <?= $parada['info'] ?>
                        </div>

                        <div style="font-size: 0.85rem; margin: 10px 0;">
                            <p><strong>🏨 Hospedaje:</strong> <?= $parada['hospedaje'] ?></p>
                            <p><strong>🛒 Supermercado:</strong> <?= $parada['supermercado'] ?></p>
                            <p><strong>🏥 Centro de Salud:</strong> <?= $parada['salud'] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- MODALES DE HOSPEDAJES EN CRONOLOGÍA -->
                <!-- MODAL SYDNEY -->
                <div class="modal-backdrop" id="backdrop-sydney" onclick="closeModal('modal-sydney')"></div>
                <div class="parada-modal" id="modal-sydney">
                    <button class="parada-modal-close" onclick="closeModal('modal-sydney')">✕</button>
                    <h2>Sydney</h2>
                    <p style="font-size: 0.8rem; color: #999;">Hospedaje — Mié 30 dic → Dom 3 ene</p>

                    <div class="parada-modal-map">
                        <iframe width="100%" height="100%" frameborder="0" style="border:0;" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d50000!2d151.2093!3d-33.8688!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f10!5e0!3m2!1sen!2sau!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
                    </div>

                    <div class="parada-modal-info">
                        <strong>📍 Ubicación:</strong> Sydney, Nueva Gales del Sur, Australia<br>
                        <strong>✈️ Aeropuerto cercano:</strong> Sydney Kingsford Smith (SYD) — ~13 km<br>
                        <strong>🕐 Disponibilidad:</strong> 3 noches (30 dic - 3 ene)
                    </div>

                    <div style="font-size: 0.85rem; margin: 10px 0;">
                        <p><strong>📌 Nota:</strong> Hospedaje pendiente de contratar</p>
                        <p><strong>💡 Recomendación:</strong> Buscar alojamiento en zonas como Newtown, Surry Hills o Coogee Beach</p>
                    </div>
                </div>

                <!-- MODAL AUCKLAND -->
                <div class="modal-backdrop" id="backdrop-auckland" onclick="closeModal('modal-auckland')"></div>
                <div class="parada-modal" id="modal-auckland">
                    <button class="parada-modal-close" onclick="closeModal('modal-auckland')">✕</button>
                    <h2>Auckland</h2>
                    <p style="font-size: 0.8rem; color: #999;">Hospedaje — Mar 12-13 ene → Jue 14 ene</p>

                    <div class="parada-modal-map">
                        <iframe width="100%" height="100%" frameborder="0" style="border:0;" src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d50000!2d174.7850!3d-37.0082!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f10!5e0!3m2!1sen!2snz!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
                    </div>

                    <div class="parada-modal-info">
                        <strong>📍 Ubicación:</strong> Auckland, Nueva Zelanda<br>
                        <strong>✈️ Aeropuerto cercano:</strong> Auckland Airport (AKL) — devolución del motorhome<br>
                        <strong>🕐 Disponibilidad:</strong> 1-2 noches (12/13 ene - 14 ene)
                    </div>

                    <div style="font-size: 0.85rem; margin: 10px 0;">
                        <p><strong>📌 Nota:</strong> Hospedaje pendiente de contratar</p>
                        <p><strong>💡 Sugerencia:</strong> Buscar alojamiento cercano al aeropuerto para la madrugada del vuelo de regreso</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: PRESUPUESTO -->
        <div class="tab-pane fade" id="presupuesto" role="tabpanel">
            <div class="sec-group">
                <div class="sec-title"><i class="bi bi-receipt"></i> Presupuesto y Logística</div>
                <?php foreach ($presupuesto as $item): ?>
                    <div class="budget-item">
                        <div class="budget-item-title"><?= $item['item'] ?></div>
                        <div class="budget-item-status"><?= $item['estado'] ?></div>
                        <div class="budget-item-nota">📌 <?= $item['nota'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB 5: INTEGRANTES -->
        <div class="tab-pane fade" id="grupo" role="tabpanel">

            <!-- SUB-TABS PARA INTEGRANTES -->
            <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="subtab-integrantes" data-bs-toggle="tab" data-bs-target="#subtab-int-integrantes" type="button" role="tab">
                        <i class="bi bi-people-fill"></i> El Grupo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subtab-prep" data-bs-toggle="tab" data-bs-target="#subtab-int-prep" type="button" role="tab">
                        <i class="bi bi-checklist"></i> Preparación
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subtab-apps" data-bs-toggle="tab" data-bs-target="#subtab-int-apps" type="button" role="tab">
                        <i class="bi bi-phone"></i> Apps
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="subtab-destinos" data-bs-toggle="tab" data-bs-target="#subtab-int-destinos" type="button" role="tab">
                        <i class="bi bi-compass"></i> Destinos
                    </button>
                </li>
            </ul>

            <!-- CONTENIDO SUB-TABS -->
            <div class="tab-content">

                <!-- SUB-TAB 1: EL GRUPO -->
                <div class="tab-pane fade show active" id="subtab-int-integrantes" role="tabpanel">
                    <div class="sec-group">
                        <div class="sec-title"><i class="bi bi-people-fill"></i> Integrantes del Grupo</div>
                        <div class="group-grid">
                            <?php foreach ($grupo as $persona): ?>
                                <div class="member-card">
                                    <div class="member-name"><?= $persona['nombre'] ?></div>
                                    <div class="member-dni">👤 <strong><?= $persona['edad'] ?> años</strong> — <?= $persona['zodiaco'] ?></div>
                                    <div class="member-dni">📋 DNI: <strong><?= $persona['dni'] ?></strong></div>
                                    <div class="member-dni">🎂 Nacido: <strong><?= $persona['nacimiento'] ?></strong></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- SUB-TAB 3: PREPARACIÓN -->
                <div class="tab-pane fade" id="subtab-int-prep" role="tabpanel">
                    <div class="sec-group">
                        <div class="sec-title"><i class="bi bi-card-text"></i> Documentación Especial</div>
                        <div class="doc-item"><span class="doc-icon">✅</span> <strong>DNI argentino vigente</strong></div>
                        <div class="doc-item"><span class="doc-icon">✅</span> <strong>Licencia de conducir</strong> (original)</div>
                        <div class="doc-item"><span class="doc-icon">✅</span> <strong>Permiso Internacional ACA</strong> (librito gris)</div>
                        <div class="doc-item"><span class="doc-icon">✅</span> <strong>Pasaporte vigente</strong></div>
                        <div class="doc-item"><span class="doc-icon">✅</span> <strong>Seguro de viaje</strong></div>
                    </div>
                </div>

                <!-- SUB-TAB 4: APPS -->
                <div class="tab-pane fade" id="subtab-int-apps" role="tabpanel">
                    <div class="sec-group">
                        <div class="sec-title"><i class="bi bi-phone"></i> Apps Imprescindibles</div>
                        <?php foreach ($apps as $cat): ?>
                            <div class="app-category">
                                <div class="app-cat-title"><?= $cat['cat'] ?></div>
                                <?php foreach ($cat['items'] as $app): ?>
                                    <div class="app-item">
                                        <div class="app-name"><?= $app['nombre'] ?></div>
                                        <div class="app-desc"><?= $app['desc'] ?></div>
                                        <?php if ($app['nota']): ?>
                                            <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">💡 <?= $app['nota'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- SUB-TAB 5: DESTINOS -->
                <div class="tab-pane fade" id="subtab-int-destinos" role="tabpanel">
                    <div class="sec-group">
                        <div class="sec-title"><i class="bi bi-compass"></i> Destinos Principales</div>
                        <?php foreach ($destinos as $dest): ?>
                            <div class="destination-island">
                                <div class="island-title">Isla <?= $dest['isla'] ?></div>
                                <?php foreach ($dest['lugares'] as $lugar): ?>
                                    <div class="place-item"><?= $lugar ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div><!-- /sub-tab-content -->

        </div>

        <!-- TAB 6: ASTROLOGÍA GRUPAL -->
        <div class="tab-pane fade" id="astrologia" role="tabpanel">
            <div class="sec-group">
                <div class="sec-title"><i class="bi bi-star-fill"></i> Dinámica Grupal por Signo Zodiacal</div>

                <!-- ESCORPIO: JORGE -->
                <div style="background: linear-gradient(135deg, rgba(239,68,68,.08) 0%, rgba(244,63,94,.08) 100%); padding: 14px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #ef4444;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 1.5rem;">♏</span>
                        <div>
                            <div style="font-weight: 700; color: #7f1d1d; font-size: 0.95rem;">JORGE — Escorpio (55 años)</div>
                            <div style="font-size: 0.8rem; color: #991b1b;">El Líder Estratégico</div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                        <p style="margin: 6px 0;"><strong>Rol en el viaje:</strong> Capitán del grupo, toma decisiones finales, observa dinámicas</p>
                        <p style="margin: 6px 0;"><strong>Fortalezas:</strong> Intuitivo, decisivo, ve problemas antes que otros, protege al grupo</p>
                        <p style="margin: 6px 0;"><strong>En ruta:</strong> Decide rutas alternativas, mantiene el control de situaciones complejas</p>
                    </div>
                </div>

                <!-- ACUARIO: PABLO -->
                <div style="background: linear-gradient(135deg, rgba(59,130,246,.08) 0%, rgba(96,165,250,.08) 100%); padding: 14px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #3b82f6;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 1.5rem;">♒</span>
                        <div>
                            <div style="font-weight: 700; color: #1e3a8a; font-size: 0.95rem;">PABLO — Acuario (54 años)</div>
                            <div style="font-size: 0.8rem; color: #1e40af;">El Innovador</div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                        <p style="margin: 6px 0;"><strong>Rol en el viaje:</strong> Propone ideas creativas e inesperadas</p>
                        <p style="margin: 6px 0;"><strong>Fortalezas:</strong> Independiente, humanitario, piensa diferente, calmado</p>
                        <p style="margin: 6px 0;"><strong>En ruta:</strong> Sugiere desvíos interesantes, cuestiona lo obvio, aporta perspectivas únicas</p>
                    </div>
                </div>

                <!-- SAGITARIO: SEBASTIÁN -->
                <div style="background: linear-gradient(135deg, rgba(251,146,60,.08) 0%, rgba(253,186,116,.08) 100%); padding: 14px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #fb923c;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 1.5rem;">🏹</span>
                        <div>
                            <div style="font-weight: 700; color: #7c2d12; font-size: 0.95rem;">SEBASTIÁN — Sagitario (49 años)</div>
                            <div style="font-size: 0.8rem; color: #92400e;">El Aventurero Nato</div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                        <p style="margin: 6px 0;"><strong>Rol en el viaje:</strong> Motor emocional, impulsor de experiencias y aventuras</p>
                        <p style="margin: 6px 0;"><strong>Fortalezas:</strong> Optimista, honesto, expansivo, amante de libertad</p>
                        <p style="margin: 6px 0;"><strong>En ruta:</strong> Anima exploración, contagia entusiasmo, ríe constante, rompe tensiones</p>
                    </div>
                </div>

                <!-- PISCIS: RICARDO -->
                <div style="background: linear-gradient(135deg, rgba(34,197,94,.08) 0%, rgba(74,222,128,.08) 100%); padding: 14px; border-radius: 6px; margin-bottom: 12px; border-left: 4px solid #22c55e;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 1.5rem;">🐠</span>
                        <div>
                            <div style="font-weight: 700; color: #166534; font-size: 0.95rem;">RICARDO — Piscis (49 años)</div>
                            <div style="font-size: 0.8rem; color: #15803d;">El Mediador Empático</div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                        <p style="margin: 6px 0;"><strong>Rol en el viaje:</strong> Equilibrador emocional, mediador de conflictos</p>
                        <p style="margin: 6px 0;"><strong>Fortalezas:</strong> Sensible, empático, creativo, intuición fuerte</p>
                        <p style="margin: 6px 0;"><strong>En ruta:</strong> Propone pausas para disfrutar momentos, alivia tensiones, aporta creatividad</p>
                    </div>
                </div>

                <!-- PRONÓSTICO -->
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #f0f9ff 100%); padding: 14px; border-radius: 6px; margin-top: 15px; border: 2px solid #0ea5e9;">
                    <div style="font-weight: 700; color: var(--primary); margin-bottom: 12px; font-size: 0.95rem;">✨ Pronóstico: Viaje Armonioso</div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                        <div style="background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #0ea5e9;">
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">Química General</div>
                            <div style="color: #fbbf24; font-size: 1.2rem; margin-top: 4px;">⭐⭐⭐⭐⭐</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Excelente — Máxima complementariedad</div>
                        </div>

                        <div style="background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #0ea5e9;">
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">Toma de Decisiones</div>
                            <div style="color: #fbbf24; font-size: 1.2rem; margin-top: 4px;">⭐⭐⭐⭐</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Buena — Todos aportan perspectivas</div>
                        </div>

                        <div style="background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #0ea5e9;">
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">Resolución de Conflictos</div>
                            <div style="color: #fbbf24; font-size: 1.2rem; margin-top: 4px;">⭐⭐⭐⭐⭐</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Excelente — Piscis media, todos calmados</div>
                        </div>

                        <div style="background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #0ea5e9;">
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">Aventura & Diversión</div>
                            <div style="color: #fbbf24; font-size: 1.2rem; margin-top: 4px;">⭐⭐⭐⭐⭐</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Excelente — Sagitario lidera expediciones</div>
                        </div>

                        <div style="background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #0ea5e9;">
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">Riesgo de Aburrimiento</div>
                            <div style="color: #fbbf24; font-size: 1.2rem; margin-top: 4px;">⭐</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Muy bajo — Demasiada diversidad</div>
                        </div>

                        <div style="background: white; padding: 10px; border-radius: 5px; border-left: 3px solid #0ea5e9;">
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">Compatibilidad Final</div>
                            <div style="color: #10b981; font-size: 1rem; margin-top: 4px; font-weight: 700;">✅ IDEAL</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">Grupo perfectamente balanceado</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- /tab-content -->

    <footer>
        <p>🌏 Viaje planificado para 2027 | Última actualización: <?= date('d/m/Y H:i') ?></p>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- PWA: Registrar Service Worker -->
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw-viaje.js').then(reg => {
    console.log('✅ Service Worker registrado para Gira Oceanía 2027');
  }).catch(err => {
    console.log('⚠️ Error al registrar Service Worker:', err);
  });
}
</script>

<script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.getElementById('backdrop-' + modalId.split('-')[1]);
    modal.classList.add('active');
    backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.getElementById('backdrop-' + modalId.split('-')[1]);
    modal.classList.remove('active');
    backdrop.classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Cerrar modal al presionar ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.parada-modal.active');
        modals.forEach(modal => {
            const modalId = modal.id;
            closeModal(modalId);
        });
    }
});
</script>
</body>
</html>
