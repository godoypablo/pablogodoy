<?php
// Pantalla de clave del grupo para /viaje.php (la incluye viaje_require_acceso())
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gira Oceanía 2027 — Acceso</title>
    <link rel="manifest" href="/manifest-viaje.json">
    <meta name="theme-color" content="#064e3b">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #064e3b 0%, #0f766e 100%);
            font-family: system-ui, sans-serif;
        }
        .acceso-card {
            width: 100%;
            max-width: 360px;
            margin: 1rem;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 12px 40px rgba(0,0,0,0.25);
        }
        .bandera { font-size: 2.5rem; line-height: 1; }
        .btn-entrar { background: #064e3b; border: none; }
        .btn-entrar:hover { background: #0f766e; }
    </style>
</head>
<body>
    <div class="card acceso-card">
        <div class="card-body p-4 text-center">
            <div class="bandera mb-2">🇳🇿 🇦🇺</div>
            <h1 class="h5 fw-bold mb-1">Gira Oceanía 2027</h1>
            <p class="text-muted small mb-4">Los 4 Magníficos — ingresá la clave del grupo</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <input type="password" name="viaje_clave" class="form-control mb-3 text-center"
                       placeholder="Clave del viaje" autocomplete="current-password" required autofocus>
                <button type="submit" class="btn btn-primary btn-entrar w-100">Entrar</button>
            </form>
            <p class="text-muted mb-0 mt-3" style="font-size:0.75rem">Se recuerda en este dispositivo por 6 meses.</p>
        </div>
    </div>
</body>
</html>
