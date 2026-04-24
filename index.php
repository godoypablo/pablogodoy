<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pablo Godoy — Desarrollador Web</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="/cifra/assets/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/cifra/assets/icons/apple-touch-icon.png">
    <meta name="theme-color" content="#7c3aed">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --verde:       #22c55e;
            --verde-dark:  #16a34a;
            --lila:        #7c3aed;
            --lila-dark:   #6d28d9;
            --lila-light:  #a78bfa;
            --dark:        #0f172a;
            --dark-2:      #1e293b;
            --muted:       #94a3b8;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* ── NAV ─────────────────────────────────────────── */
        .nav-bar {
            background: rgba(15,23,42,.96);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(124,58,237,.18);
        }
        .nav-bar .navbar-brand {
            display: flex; align-items: center; gap: .6rem;
            text-decoration: none; color: #fff; font-weight: 700;
            font-size: 1rem; letter-spacing: -.02em;
        }
        .nav-link-item {
            color: var(--muted) !important;
            font-size: .875rem; font-weight: 500;
            padding: .5rem 1rem !important;
            transition: color .2s;
        }
        .nav-link-item:hover { color: #fff !important; }
        .nav-cta {
            background: linear-gradient(135deg, var(--lila), #5b21b6);
            color: #fff !important; border-radius: 8px;
            padding: .4rem 1.2rem !important; font-weight: 600;
            transition: opacity .2s;
        }
        .nav-cta:hover { opacity: .82; }

        /* ── HERO ────────────────────────────────────────── */
        .hero {
            background: var(--dark);
            min-height: 100vh;
            display: flex; align-items: center;
            position: relative; overflow: hidden;
            padding: 100px 0 60px;
        }
        .orb {
            position: absolute; border-radius: 50%;
            filter: blur(90px); opacity: .13; pointer-events: none;
        }
        .orb-1 { width: 520px; height: 520px; background: var(--lila); top: -160px; right: -80px; }
        .orb-2 { width: 420px; height: 420px; background: var(--verde); bottom: -100px; left: -60px; }

        .hero-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(124,58,237,.14);
            border: 1px solid rgba(124,58,237,.28);
            color: var(--lila-light); border-radius: 100px;
            padding: .3rem .9rem; font-size: .78rem; font-weight: 500;
            margin-bottom: 1.5rem;
        }
        .hero h1 {
            font-size: clamp(2.4rem, 5.5vw, 4.2rem);
            font-weight: 800; color: #fff;
            line-height: 1.1; letter-spacing: -.03em; margin-bottom: 1rem;
        }
        .hero h1 .accent {
            background: linear-gradient(135deg, var(--lila-light), var(--verde));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero-sub {
            font-size: clamp(.95rem, 1.8vw, 1.15rem);
            color: var(--muted); max-width: 500px;
            line-height: 1.65; margin-bottom: 2.5rem;
        }
        .btn-primary-pg {
            display: inline-flex; align-items: center; gap: .5rem;
            background: linear-gradient(135deg, var(--lila), #5b21b6);
            color: #fff; border: none; border-radius: 10px;
            padding: .75rem 1.75rem; font-weight: 600; font-size: .95rem;
            text-decoration: none; transition: transform .2s, box-shadow .2s;
        }
        .btn-primary-pg:hover {
            color: #fff; transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(124,58,237,.4);
        }
        .btn-ghost-pg {
            display: inline-flex; align-items: center; gap: .5rem;
            background: transparent; color: var(--muted);
            border: 1px solid rgba(148,163,184,.3); border-radius: 10px;
            padding: .75rem 1.75rem; font-weight: 500; font-size: .95rem;
            text-decoration: none; transition: all .2s;
        }
        .btn-ghost-pg:hover { color: #fff; border-color: rgba(255,255,255,.3); }

        .stack-row { margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,.07); }
        .stack-label {
            color: #475569; font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .1em; margin-bottom: .85rem;
        }
        .stack-pill {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.1);
            color: #cbd5e1; border-radius: 8px;
            padding: .35rem .75rem; font-size: .78rem; font-weight: 500; margin: .2rem;
        }

        /* ── SECCIONES GENERALES ──────────────────────────── */
        section { padding: 80px 0; }
        .sec-label {
            display: inline-block; color: var(--lila);
            font-size: .72rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .12em; margin-bottom: .6rem;
        }
        .sec-title {
            font-size: clamp(1.65rem, 3vw, 2.4rem);
            font-weight: 800; letter-spacing: -.02em; line-height: 1.2; margin-bottom: .9rem;
        }
        .sec-desc { color: #64748b; font-size: .95rem; line-height: 1.7; max-width: 500px; }

        /* ── SERVICIOS ───────────────────────────────────── */
        #servicios { background: #f8fafc; }
        .srv-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 2rem; height: 100%;
            transition: transform .25s, box-shadow .25s, border-color .25s;
            position: relative; overflow: hidden;
        }
        .srv-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--lila), var(--verde));
            opacity: 0; transition: opacity .25s;
        }
        .srv-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.07); border-color: rgba(124,58,237,.2); }
        .srv-card:hover::before { opacity: 1; }
        .srv-icon {
            width: 46px; height: 46px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; margin-bottom: 1.1rem;
        }
        .si-lila  { background: rgba(124,58,237,.1);  color: var(--lila); }
        .si-verde { background: rgba(34,197,94,.1);   color: var(--verde-dark); }
        .si-slate { background: rgba(100,116,139,.1); color: #475569; }
        .srv-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: .5rem; letter-spacing: -.01em; }
        .srv-card p  { font-size: .855rem; color: #64748b; line-height: 1.65; margin: 0; }

        /* ── PROYECTOS ───────────────────────────────────── */
        #proyectos { background: var(--dark); }
        #proyectos .sec-label { color: var(--verde); }
        #proyectos .sec-title { color: #fff; }
        #proyectos .sec-desc  { color: var(--muted); }

        .proj-card {
            background: var(--dark-2); border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px; overflow: hidden;
        }
        .proj-body { padding: 2.5rem; }
        .proj-badge {
            display: inline-block;
            background: rgba(34,197,94,.14); border: 1px solid rgba(34,197,94,.28);
            color: var(--verde); border-radius: 100px;
            padding: .2rem .75rem; font-size: .7rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: .08em; margin-bottom: 1rem;
        }
        .proj-title { color: #fff; font-size: 1.7rem; font-weight: 800; letter-spacing: -.02em; margin-bottom: .7rem; }
        .proj-desc  { color: var(--muted); font-size: .92rem; line-height: 1.7; margin-bottom: 1.4rem; }
        .proj-feat  {
            display: flex; align-items: flex-start; gap: .55rem;
            color: #cbd5e1; font-size: .855rem; margin-bottom: .45rem;
        }
        .proj-feat i { color: var(--verde); margin-top: 2px; flex-shrink: 0; }
        .btn-proj {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.28);
            color: var(--verde); border-radius: 10px;
            padding: .6rem 1.4rem; font-weight: 600; font-size: .875rem;
            text-decoration: none; transition: background .2s; margin-top: .75rem;
        }
        .btn-proj:hover { background: rgba(34,197,94,.2); color: var(--verde); }
        .proj-visual {
            background: linear-gradient(135deg, rgba(124,58,237,.13), rgba(34,197,94,.08));
            display: flex; align-items: center; justify-content: center;
            min-height: 220px; font-size: 5.5rem;
            border-top: 1px solid rgba(255,255,255,.05);
        }

        /* ── VIAJE ───────────────────────────────────────── */
        #viaje { background: #f8fafc; }
        .viaje-card {
            background: linear-gradient(135deg, var(--dark-2), var(--dark));
            border-radius: 20px; padding: 2.5rem; color: #fff;
            position: relative; overflow: hidden;
        }
        .viaje-card::after {
            content: '✈'; position: absolute; right: 2.5rem; top: 50%;
            transform: translateY(-50%); font-size: 9rem; opacity: .04; pointer-events: none;
        }
        .viaje-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            background: rgba(59,130,246,.18); border: 1px solid rgba(59,130,246,.3);
            color: #93c5fd; border-radius: 100px;
            padding: .25rem .75rem; font-size: .74rem; font-weight: 600; margin-bottom: 1rem;
        }
        .pwa-pill {
            display: inline-flex; align-items: center; gap: .35rem;
            background: rgba(34,197,94,.12); border: 1px solid rgba(34,197,94,.25);
            color: #86efac; border-radius: 100px;
            padding: .2rem .65rem; font-size: .72rem; font-weight: 600;
        }
        .viaje-card h3 { font-size: 1.5rem; font-weight: 800; letter-spacing: -.02em; margin-bottom: .45rem; }
        .viaje-card p  { color: var(--muted); font-size: .9rem; line-height: 1.65; max-width: 440px; margin-bottom: 1.5rem; }
        .btn-viaje {
            display: inline-flex; align-items: center; gap: .5rem;
            background: rgba(59,130,246,.15); border: 1px solid rgba(59,130,246,.3);
            color: #93c5fd; border-radius: 10px;
            padding: .6rem 1.4rem; font-weight: 600; font-size: .875rem;
            text-decoration: none; transition: background .2s;
        }
        .btn-viaje:hover { background: rgba(59,130,246,.25); color: #bfdbfe; }

        /* ── RECURSOS ────────────────────────────────────── */
        #recursos { background: #fff; }
        .rec-item {
            display: flex; align-items: center; gap: 1rem;
            padding: 1rem 1.2rem; border: 1px solid #e2e8f0; border-radius: 12px;
            text-decoration: none; color: #1e293b; transition: all .2s; margin-bottom: .75rem;
        }
        .rec-item:hover {
            border-color: rgba(124,58,237,.3); background: rgba(124,58,237,.03);
            color: #1e293b; transform: translateX(4px);
        }
        .rec-icon {
            width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .rec-item h4 { font-size: .875rem; font-weight: 600; margin: 0 0 .1rem; }
        .rec-item p  { font-size: .775rem; color: #64748b; margin: 0; }
        .rec-arrow   { margin-left: auto; color: #cbd5e1; font-size: .9rem; transition: transform .2s, color .2s; }
        .rec-item:hover .rec-arrow { transform: translateX(3px); color: var(--lila); }

        /* ── CONTACTO ────────────────────────────────────── */
        #contacto { background: var(--dark); }
        #contacto .sec-label { color: var(--verde); }
        #contacto .sec-title { color: #fff; }
        .contact-box {
            background: var(--dark-2); border: 1px solid rgba(255,255,255,.07);
            border-radius: 20px; padding: 3rem; text-align: center;
        }
        .contact-box p { color: var(--muted); font-size: .95rem; line-height: 1.7; max-width: 460px; margin: 0 auto 2rem; }
        .btn-contact {
            display: inline-flex; align-items: center; gap: .55rem;
            background: linear-gradient(135deg, var(--lila), var(--verde));
            color: #fff; border-radius: 12px;
            padding: .85rem 2rem; font-weight: 700; font-size: 1rem;
            text-decoration: none; transition: opacity .2s, transform .2s;
        }
        .btn-contact:hover { opacity: .88; transform: translateY(-2px); color: #fff; }

        /* ── FOOTER ──────────────────────────────────────── */
        footer {
            background: #020617; padding: 2rem 0;
            border-top: 1px solid rgba(255,255,255,.05);
        }
        footer p { color: #475569; font-size: .8rem; margin: 0; }

        /* ── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 768px) {
            section { padding: 60px 0; }
            .hero  { padding: 90px 0 50px; }
            .proj-body  { padding: 1.75rem; }
            .viaje-card { padding: 1.75rem; }
            .contact-box { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

<!-- NAV -->
<nav class="navbar navbar-expand-lg nav-bar fixed-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <svg viewBox="0 0 48 48" width="36" height="36" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="lg-nav" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#7c3aed"/>
                        <stop offset="100%" stop-color="#22c55e"/>
                    </linearGradient>
                </defs>
                <rect width="48" height="48" rx="12" fill="url(#lg-nav)"/>
                <text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle"
                      font-family="Inter,sans-serif" font-weight="800" font-size="17" fill="white">PG</text>
            </svg>
            Pablo Godoy
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                <li class="nav-item"><a href="#servicios" class="nav-link nav-link-item">Servicios</a></li>
                <li class="nav-item"><a href="#proyectos" class="nav-link nav-link-item">Proyectos</a></li>
                <li class="nav-item"><a href="#viaje"     class="nav-link nav-link-item">Viaje</a></li>
                <li class="nav-item"><a href="#recursos"  class="nav-link nav-link-item">Recursos</a></li>
                <li class="nav-item ms-lg-2">
                    <a href="#contacto" class="nav-link nav-cta">Contacto</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="hero-badge">
                    <i class="bi bi-circle-fill" style="font-size:.45rem;color:var(--verde)"></i>
                    Disponible para proyectos
                </div>
                <h1>Desarrollo web<br>hecho con <span class="accent">propósito</span></h1>
                <p class="hero-sub">
                    Soy Pablo Godoy, desarrollador web full stack. Construyo aplicaciones a medida,
                    rápidas y fáciles de usar, pensadas para resolver problemas reales.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#proyectos" class="btn-primary-pg">
                        <i class="bi bi-layers-fill"></i> Ver proyectos
                    </a>
                    <a href="#contacto" class="btn-ghost-pg">
                        <i class="bi bi-envelope"></i> Hablemos
                    </a>
                </div>
                <div class="stack-row">
                    <p class="stack-label">Stack principal</p>
                    <span class="stack-pill"><i class="bi bi-filetype-php"></i> PHP</span>
                    <span class="stack-pill"><i class="bi bi-filetype-js"></i> JavaScript</span>
                    <span class="stack-pill"><i class="bi bi-bootstrap"></i> Bootstrap</span>
                    <span class="stack-pill"><i class="bi bi-filetype-css"></i> CSS</span>
                    <span class="stack-pill"><i class="bi bi-database"></i> MySQL</span>
                    <span class="stack-pill"><i class="bi bi-phone"></i> PWA</span>
                </div>
            </div>

            <div class="col-lg-4 d-none d-lg-flex justify-content-center">
                <svg viewBox="0 0 200 200" width="300" height="300" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="lg-hero" x1="0" y1="0" x2="200" y2="200" gradientUnits="userSpaceOnUse">
                            <stop offset="0%" stop-color="#7c3aed" stop-opacity=".7"/>
                            <stop offset="100%" stop-color="#22c55e" stop-opacity=".5"/>
                        </linearGradient>
                    </defs>
                    <rect x="16" y="16" width="168" height="168" rx="36" fill="url(#lg-hero)"/>
                    <text x="50%" y="47%" dominant-baseline="middle" text-anchor="middle"
                          font-family="Inter,sans-serif" font-weight="800" font-size="66" fill="white" opacity=".92">PG</text>
                    <text x="50%" y="68%" dominant-baseline="middle" text-anchor="middle"
                          font-family="Inter,sans-serif" font-weight="400" font-size="13" fill="white" opacity=".45" letter-spacing="4">dev</text>
                </svg>
            </div>
        </div>
    </div>
</section>

<!-- SERVICIOS -->
<section id="servicios">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6">
                <span class="sec-label">Servicios</span>
                <h2 class="sec-title">¿Qué puedo hacer por vos?</h2>
                <p class="sec-desc">Desde una landing page hasta una app web completa. Me adapto a lo que tu proyecto necesita.</p>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="srv-card">
                    <div class="srv-icon si-lila"><i class="bi bi-window-stack"></i></div>
                    <h3>Aplicaciones web</h3>
                    <p>Sistemas a medida: gestión, facturación, dashboards, CRMs. Soluciones que se adaptan a tu flujo de trabajo.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="srv-card">
                    <div class="srv-icon si-verde"><i class="bi bi-phone"></i></div>
                    <h3>PWA & Mobile-first</h3>
                    <p>Apps instalables en el celular sin pasar por las tiendas. Funcionan offline y se sienten nativas.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="srv-card">
                    <div class="srv-icon si-slate"><i class="bi bi-brush"></i></div>
                    <h3>Diseño UI/UX</h3>
                    <p>Interfaces limpias, responsivas y accesibles. Dark mode incluido. Diseño que no solo se ve bien, sino que se usa bien.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="srv-card">
                    <div class="srv-icon si-lila"><i class="bi bi-database-gear"></i></div>
                    <h3>Backend & APIs</h3>
                    <p>APIs REST en PHP con MySQL. Lógica de negocios robusta, transacciones y control de integridad de datos.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="srv-card">
                    <div class="srv-icon si-verde"><i class="bi bi-arrow-repeat"></i></div>
                    <h3>Migraciones & refactors</h3>
                    <p>Modernizá sistemas legacy. Mejoro código existente sin romper lo que ya funciona.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="srv-card">
                    <div class="srv-icon si-slate"><i class="bi bi-headset"></i></div>
                    <h3>Soporte continuo</h3>
                    <p>No desaparezco después de entregar. Mantenimiento, nuevas funcionalidades y acompañamiento post-lanzamiento.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- PROYECTOS -->
<section id="proyectos">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6">
                <span class="sec-label">Proyectos</span>
                <h2 class="sec-title">Trabajo real, resultados concretos</h2>
                <p class="sec-desc">Un ejemplo de lo que construyo: aplicaciones que resuelven problemas del día a día.</p>
            </div>
        </div>
        <div class="proj-card">
            <div class="row g-0">
                <div class="col-lg-7">
                    <div class="proj-body">
                        <span class="proj-badge"><i class="bi bi-circle-fill me-1" style="font-size:.45rem"></i>En producción</span>
                        <h3 class="proj-title">Sistema Cifra</h3>
                        <p class="proj-desc">
                            Aplicación de finanzas personales multi-cuenta y multi-moneda.
                            Desarrollada desde cero como PWA instalable, funciona offline y gestiona
                            ingresos, gastos y saldos bancarios en tiempo real.
                        </p>
                        <div class="proj-feat"><i class="bi bi-check-circle-fill"></i><span>Múltiples cuentas con control de saldo en tiempo real</span></div>
                        <div class="proj-feat"><i class="bi bi-check-circle-fill"></i><span>Soporte ARS y USD con sugerencias automáticas de importes</span></div>
                        <div class="proj-feat"><i class="bi bi-check-circle-fill"></i><span>PWA instalable en iOS y Android, funciona offline</span></div>
                        <div class="proj-feat"><i class="bi bi-check-circle-fill"></i><span>Dark mode, diseño mobile-first, autenticación con remember-me</span></div>
                        <a href="/cifra/login.php" class="btn-proj">
                            <i class="bi bi-arrow-up-right-square"></i> Ver aplicación
                        </a>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="proj-visual">
                        <i class="bi bi-cash-stack" style="color:var(--verde);opacity:.65"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VIAJE -->
<section id="viaje">
    <div class="container">
        <div class="viaje-card">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3">
                <span class="viaje-badge"><i class="bi bi-airplane-engines"></i> Próximo viaje</span>
                <span class="pwa-pill"><i class="bi bi-phone"></i> Instalable como app</span>
            </div>
            <h3>Nueva Zelanda &amp; Australia</h3>
            <p>Un espacio compartido para planificar el viaje con el grupo. Vuelos, estadías, actividades y todo lo que necesitamos coordinar en un solo lugar. Instalala en tu celular para tenerla siempre a mano.</p>
            <a href="/viaje-nzl-aus.php" class="btn-viaje">
                <i class="bi bi-map"></i> Abrir planificador
            </a>
        </div>
    </div>
</section>

<!-- RECURSOS -->
<section id="recursos">
    <div class="container">
        <div class="row mb-4">
            <div class="col-lg-6">
                <span class="sec-label">Recursos</span>
                <h2 class="sec-title">Notas &amp; referencias</h2>
                <p class="sec-desc">Material que uso y comparto: guías, herramientas y apuntes técnicos.</p>
            </div>
        </div>
        <div class="col-lg-6">
            <a href="claudecode.html" class="rec-item">
                <div class="rec-icon" style="background:rgba(217,119,87,.1);color:#d97757"><i class="bi bi-cpu"></i></div>
                <div>
                    <h4>Claude AI &amp; Claude Code</h4>
                    <p>Guía de uso del asistente y la CLI de desarrollo</p>
                </div>
                <i class="bi bi-arrow-right rec-arrow"></i>
            </a>
            <a href="linux.html" class="rec-item">
                <div class="rec-icon" style="background:rgba(135,207,62,.1);color:#87cf3e"><i class="bi bi-ubuntu"></i></div>
                <div>
                    <h4>Linux Mint</h4>
                    <p>Comandos frecuentes y configuraciones útiles</p>
                </div>
                <i class="bi bi-arrow-right rec-arrow"></i>
            </a>
            <a href="github.html" class="rec-item">
                <div class="rec-icon" style="background:rgba(36,41,46,.08);color:#24292e"><i class="bi bi-github"></i></div>
                <div>
                    <h4>GitHub</h4>
                    <p>Mis repositorios y proyectos open source</p>
                </div>
                <i class="bi bi-arrow-right rec-arrow"></i>
            </a>
        </div>
    </div>
</section>

<!-- CONTACTO -->
<section id="contacto">
    <div class="container">
        <div class="text-center mb-5">
            <span class="sec-label">Contacto</span>
            <h2 class="sec-title">¿Tenés un proyecto en mente?</h2>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="contact-box">
                    <p>Contame qué necesitás y vemos cómo puedo ayudarte. Respondo rápido.</p>
                    <a href="mailto:godoypablo@gmail.com" class="btn-contact">
                        <i class="bi bi-envelope-fill"></i> godoypablo@gmail.com
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="container d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="d-flex align-items-center gap-2">
            <svg viewBox="0 0 48 48" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="lg-footer" x1="0" y1="0" x2="48" y2="48" gradientUnits="userSpaceOnUse">
                        <stop offset="0%" stop-color="#7c3aed"/>
                        <stop offset="100%" stop-color="#22c55e"/>
                    </linearGradient>
                </defs>
                <rect width="48" height="48" rx="12" fill="url(#lg-footer)"/>
                <text x="50%" y="56%" dominant-baseline="middle" text-anchor="middle"
                      font-family="Inter,sans-serif" font-weight="800" font-size="17" fill="white">PG</text>
            </svg>
            <p>Pablo Godoy — Desarrollador Web</p>
        </div>
        <p>pablogodoy.com.ar</p>
    </div>
</footer>

<script>
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
