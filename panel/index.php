<?php require __DIR__ . '/_auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel Privado - Pablo Godoy</title>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .seccion-titulo {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #6c757d;
            margin: 2.5rem 0 1rem;
        }
        .seccion-titulo:first-of-type { margin-top: 0; }
        .dashboard-card {
            transition: transform 0.2s ease;
            text-decoration: none;
            color: #333;
            border-radius: 15px;
        }
        .dashboard-card:hover {
            transform: translateY(-6px);
            color: #000;
            box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
        }
        .icon-box { font-size: 2.5rem; margin-bottom: 0.75rem; }
        /* Colores representativos */
        .color-cifra { color: #2ecc71; }
        .color-hipoteca { color: #6366f1; }
        .color-viaje { color: #3498db; }
        .color-claude { color: #d97757; } /* Tono arcilla de Claude */
        .color-linux { color: #87cf3e; }  /* Verde Mint */
        .color-github { color: #24292e; }
        .color-sigedo { color: #006BA6; } /* Azul TCER SIGEDO */
        .color-nutricion { color: #a8d8e8; } /* Azul suave - Nutrición */
        .color-gym { color: #ff6b6b; } /* Rojo vibrante - Gym */
    </style>
</head>
<body>
<div class="container py-5">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
        <div class="d-flex align-items-center gap-3">
            <img src="/img/logo/isotipo.svg" alt="" width="48" height="48">
            <div>
            <h1 class="fw-bold h2 mb-1">Panel Privado</h1>
            <p class="text-muted font-monospace small mb-0">pablogodoy.com.ar</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/" class="btn btn-outline-secondary btn-sm"><i class="bi bi-globe me-1"></i>Ver sitio</a>
            <a href="/cifra/logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a>
        </div>
    </header>

    <h2 class="seccion-titulo"><i class="bi bi-wallet2 me-1"></i> Finanzas</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="/cifra/" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-cifra"><i class="bi bi-cash-stack"></i></div>
                <h3 class="h5 fw-bold">Sistema Cifra</h3>
                <p class="text-secondary small mb-0">Finanzas Personales</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="/cifra/hipoteca.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-hipoteca"><i class="bi bi-house-door"></i></div>
                <h3 class="h5 fw-bold">Cuotas Hipotecarias</h3>
                <p class="text-secondary small mb-0">Crédito UVA - Banco Entre Ríos</p>
            </a>
        </div>
    </div>

    <h2 class="seccion-titulo"><i class="bi bi-journal-code me-1"></i> Notas técnicas</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <a href="/panel/notas/claudecode.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-claude"><i class="bi bi-cpu"></i></div>
                <h3 class="h5 fw-bold">Claude AI</h3>
                <p class="text-secondary small mb-0">Asistente Claude Code</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="/panel/notas/linux.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-linux"><i class="bi bi-ubuntu"></i></div>
                <h3 class="h5 fw-bold">Linux Mint</h3>
                <p class="text-secondary small mb-0">Guías y Comandos</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="/panel/notas/github.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-github"><i class="bi bi-github"></i></div>
                <h3 class="h5 fw-bold">GitHub</h3>
                <p class="text-secondary small mb-0">Guía paso a paso</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="/panel/notas/sigedo.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-sigedo"><i class="bi bi-file-earmark-text"></i></div>
                <h3 class="h5 fw-bold">SIGEDO</h3>
                <p class="text-secondary small mb-0">Gestión Electrónica de Documentos</p>
            </a>
        </div>
    </div>

    <h2 class="seccion-titulo"><i class="bi bi-heart-pulse me-1"></i> Salud</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="/panel/notas/nutricion.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-nutricion"><i class="bi bi-apple"></i></div>
                <h3 class="h5 fw-bold">Nutrición</h3>
                <p class="text-secondary small mb-0">Plan de Alimentación Personalizado</p>
            </a>
        </div>
        <div class="col-md-6 col-lg-4">
            <a href="/panel/notas/gym.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-gym"><i class="bi bi-person-arms-up"></i></div>
                <h3 class="h5 fw-bold">Gym</h3>
                <p class="text-secondary small mb-0">Rutina de Entrenamientos</p>
            </a>
        </div>
    </div>

    <h2 class="seccion-titulo"><i class="bi bi-globe-americas me-1"></i> Viajes</h2>
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="/viaje.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-viaje"><i class="bi bi-airplane-engines"></i></div>
                <h3 class="h5 fw-bold">Viaje NZL &amp; AUS</h3>
                <p class="text-secondary small mb-0">Gira Oceanía 2027</p>
            </a>
        </div>
    </div>
</div>
</body>
</html>
