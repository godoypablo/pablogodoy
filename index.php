<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pablo Godoy · Lic. en Sistemas</title>
    <meta name="description" content="Pablo Godoy, Licenciado en Sistemas. Desarrollo web, apps para Android e iPhone, software a medida y consultoría informática. Sitio en construcción.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --texto: #1f2937; --suave: #6b7280; --acento: #2563eb; --fondo: #f8fafc; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--fondo);
            color: var(--texto);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            text-align: center;
        }
        .monograma {
            width: 64px; height: 64px;
            border-radius: 16px;
            background: var(--texto);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.5rem; letter-spacing: -0.5px;
            margin: 0 auto 1.5rem;
        }
        h1 { font-size: clamp(2rem, 6vw, 3rem); font-weight: 700; letter-spacing: -1px; }
        .titulo { color: var(--suave); font-size: 1.1rem; margin-top: 0.25rem; }
        .servicios { margin-top: 2rem; color: var(--texto); line-height: 1.8; }
        .aviso {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.4rem 1rem;
            border-radius: 999px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 0.85rem;
            font-weight: 600;
        }
        footer { margin-top: 3rem; font-size: 0.8rem; color: var(--suave); }
        footer a { color: var(--suave); }
    </style>
</head>
<body>
    <div class="monograma">PG</div>
    <h1>Pablo Godoy</h1>
    <p class="titulo">Lic. en Sistemas</p>

    <p class="servicios">
        Desarrollo de sitios web · Apps Android e iPhone<br>
        Software a medida · Consultoría informática
    </p>

    <span class="aviso">Sitio en construcción</span>

    <footer>
        Mat. Prof. Coprocier Nº 495 · Entre Ríos, Argentina<br>
        <a href="/cifra/login.php?next=%2Fpanel%2F" rel="nofollow">Acceso</a>
    </footer>
</body>
</html>
