<?php
// Configuración de usuario y notas personales extraídas del video
$usuario_logueado = true; 
$mis_notas = [
    "HolaFly" => "Activar al bajar del avión. Soporte 24/7 vía WhatsApp.",
    "Revolut" => "Usar para evitar comisiones. NZ es 99% tarjeta.",
    "CamperMate" => "Indispensable para pernoctar gratis (Self-contained).",
    "Pack'n Save" => "Supermercado más barato de NZ. Buscar logos amarillos.",
    "Gull/Waitomo" => "Gasolineras Low-Cost recomendadas."
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Hub: Nueva Zelanda y Australia 2027</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2ecc71">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; color: #1a1d20; }
        .app-card { border-radius: 16px; transition: 0.3s; border: none !important; background: #fff; }
        .app-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
        .badge-note { font-size: 0.85rem; background-color: #e7f3ff; color: #0056b3; border-radius: 8px; }
        .btn-store { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px; }
        .section-title { border-left: 4px solid #2ecc71; padding-left: 15px; margin-bottom: 20px; font-weight: 700; }
        .map-container { border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .header-logo { font-size: 3rem; line-height: 1; }
        @media (max-width: 576px) {
            .header-logo { font-size: 2rem; }
            .display-5 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active">Nueva Zelanda y Australia 2027</li>
        </ol>
    </nav>

    <header class="text-center mb-5">
        <div class="d-flex justify-content-center align-items-center gap-3 mb-2">
            <span class="header-logo">🇳🇿</span>
            <h1 class="display-5 fw-bold text-dark m-0">Nueva Zelanda y Australia 2027</h1>
            <span class="header-logo">🇦🇺</span>
        </div>
        <p class="lead text-muted">(Los 4 magníficos)</p>
    </header>

    <div class="row">
        <div class="col-lg-7">
            <h2 class="section-title">Descargar Apps</h2>
            <div class="row g-3 mb-5">
                <?php
                $apps = [
                    ["n" => "CamperMate", "d" => "Campings y servicios", "i" => "bi-map", "link" => "https://play.google.com/store/search?q=CamperMate&c=apps&hl=es_AR"],
                    ["n" => "HolaFly", "d" => "eSIM Internet Ilimitado", "i" => "bi-wifi", "link" => "https://play.google.com/store/search?q=HolaFly&c=apps&hl=es_AR"],
                    ["n" => "MetService", "d" => "Clima oficial NZ", "i" => "bi-cloud-sun", "link" => "https://play.google.com/store/search?q=MetService&c=apps&hl=es_AR"],
                    ["n" => "Gaspy", "d" => "Combustible al mejor precio", "i" => "bi-fuel-pump", "link" => "https://play.google.com/store/search?q=Gaspy&c=apps&hl=es_AR"],
                    ["n" => "Citas en NZL", "d" => "Social y Dating NZ", "i" => "bi-heart-fill", "link" => "https://play.google.com/store/apps/details?id=com.chat.newzealanddating&hl=es_AR"],
                    ["n" => "Citas en AUS", "d" => "Social y Dating AUS", "i" => "bi-chat-heart-fill", "link" => "https://play.google.com/store/apps/details?id=com.mingle.AussieMingle&hl=es_DO"]
                ];

                foreach ($apps as $app): ?>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm p-3 app-card border-0">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success me-3">
                                    <i class="<?= $app['i'] ?> fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?= $app['n'] ?></h6>
                                    <small class="text-muted"><?= $app['d'] ?></small>
                                </div>
                            </div>
                            <?php if (isset($mis_notas[$app['n']])): ?>
                                <div class="badge-note p-2 mb-3"><i class="bi bi-info-circle me-1"></i><?= $mis_notas[$app['n']] ?></div>
                            <?php endif; ?>
                            <a href="<?= $app['link'] ?>" target="_blank" rel="noopener noreferrer" class="btn btn-dark w-100 btn-store">
                                <i class="bi bi-google-play me-2"></i> Abrir en Play Store
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="section-title">Recorrido en Motorhome</h2>
            <div class="map-container mb-5 text-center p-4 bg-white">
                <p class="text-muted">Accede al mapa personalizado de Google Maps:</p>
                <a href="https://maps.app.goo.gl/VwANiYRUAFdV42fv9?g_st=aw" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg shadow-sm">
                    <i class="bi bi-geo-alt-fill me-2"></i>Abrir Mapa del Viaje
                </a>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <h3 class="fw-bold mb-4 text-success"><i class="bi bi-lightning-charge me-2"></i>Tips Estratégicos</h3>
                <ul class="list-unstyled">
                    <li class="mb-3"><strong>💰 Presupuesto:</strong> Aprox. <strong>USD 3,240</strong> por persona (3 semanas todo incluido).</li>
                    <li class="mb-3"><strong>🚐 Certificado:</strong> Alquilar siempre una camper <em>"Self-Contained"</em> para dormir gratis en zonas permitidas.</li>
                    <li class="mb-3"><strong>🗓️ Temporada:</strong> Verano austral (Octubre a Abril).</li>
                    <li class="mb-3"><strong>💳 Finanzas:</strong> Tarjetas internacionales para evitar comisiones en moneda local (NZD/AUD).</li>
                </ul>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white">
                <h3 class="fw-bold mb-4 text-warning"><i class="bi bi-star-fill me-2"></i>Destinos Clave</h3>
                <div class="small">
                    <p><strong>Isla Norte:</strong> Hobbiton, Waitomo (Cuevas), Rotorua y el cruce del Tongariro.</p>
                    <p><strong>Isla Sur:</strong> Milford Sound, Lago Pukaki, Mount Cook y Queenstown.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-5 text-center py-4 border-top">
        <p class="text-muted small">© 2027 Pablo Godoy | Información de viaje optimizada</p>
    </footer>
</div>

</body>
</html>