<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Personal - Pablo Godoy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .dashboard-card {
            transition: transform 0.2s ease;
            text-decoration: none;
            color: #333;
            border-radius: 15px;
        }
        .dashboard-card:hover {
            transform: translateY(-8px);
            color: #000;
            box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important;
        }
        .icon-box { font-size: 3rem; margin-bottom: 1rem; }
        /* Colores representativos */
        .color-cifra { color: #2ecc71; }
        .color-viaje { color: #3498db; }
        .color-claude { color: #d97757; } /* Tono arcilla de Claude */
        .color-linux { color: #87cf3e; }  /* Verde Mint */
        .color-github { color: #24292e; }
    </style>
</head>
<body>

<div class="container py-5">
    <header class="text-center mb-5">
        <h1 class="fw-bold">Panel de Control</h1>
        <p class="text-muted font-monospace">pablogodoy.com.ar</p>
    </header>

    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <a href="/cifra/login.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-cifra"><i class="bi bi-cash-stack"></i></div>
                <h3 class="h5 fw-bold">Sistema Cifra</h3>
                <p class="text-secondary small">Finanzas Personales</p>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="/viaje-nzl-aus.php" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-viaje"><i class="bi bi-airplane-engines"></i></div>
                <h3 class="h5 fw-bold">Viaje NZL & AUS</h3>
                <p class="text-secondary small">Documentación de Viaje</p>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="claudecode.html" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-claude"><i class="bi bi-cpu"></i></div>
                <h3 class="h5 fw-bold">Claude AI</h3>
                <p class="text-secondary small">Asistente Claude Code</p>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="linux.html" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-linux"><i class="bi bi-ubuntu"></i></div>
                <h3 class="h5 fw-bold">Linux Mint</h3>
                <p class="text-secondary small">Guías y Comandos</p>
            </a>
        </div>

        <div class="col-md-6 col-lg-4">
            <a href="github.html" class="card h-100 border-0 shadow-sm dashboard-card p-4 text-center">
                <div class="icon-box color-github"><i class="bi bi-github"></i></div>
                <h3 class="h5 fw-bold">GitHub</h3>
                <p class="text-secondary small">Mis Repositorios</p>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>