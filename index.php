<?php
$wa     = '5493434504750';
$waLink = 'https://wa.me/' . $wa . '?text=' . rawurlencode('Hola Pablo, te escribo desde tu web. Quería consultarte por ');
$enviado = isset($_GET['enviado']);
$errorForm = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pablo Godoy | Desarrollo web y software a medida en Paraná</title>
    <meta name="description" content="Pablo Godoy, Lic. en Sistemas de Información en Paraná, Entre Ríos. Sitios web, apps para Android e iPhone, software a medida y consultoría. +25 años.">
    <link rel="canonical" href="https://www.pablogodoy.com.ar/">
    <meta name="theme-color" content="#0f172a">

    <!-- Open Graph: vista previa al compartir por WhatsApp / redes -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_AR">
    <meta property="og:url" content="https://www.pablogodoy.com.ar/">
    <meta property="og:title" content="Pablo Godoy | Desarrollo web y software a medida">
    <meta property="og:description" content="Sitios web, apps para Android e iPhone, software a medida y consultoría informática. Paraná, Entre Ríos.">
    <meta property="og:image" content="https://www.pablogodoy.com.ar/img/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/img/logo/apple-touch-icon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Inter+Tight:wght@300;400;500;600&family=Instrument+Serif:ital@1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "ProfessionalService",
        "name": "Pablo Godoy",
        "alternateName": ["Pablo Andrés Godoy", "Lic. Pablo Andrés Godoy"],
        "description": "Desarrollo web, apps para Android e iPhone, software a medida y consultoría informática en Paraná, Entre Ríos.",
        "url": "https://www.pablogodoy.com.ar/",
        "image": "https://www.pablogodoy.com.ar/img/pablo-godoy.jpg",
        "logo": "https://www.pablogodoy.com.ar/img/logo/icon-512.png",
        "telephone": "+54 9 343 450-4750",
        "areaServed": "AR",
        "address": { "@type": "PostalAddress", "addressLocality": "Paraná", "addressRegion": "Entre Ríos", "addressCountry": "AR" },
        "sameAs": ["https://www.linkedin.com/in/godoypablo1972/"],
        "founder": { "@type": "Person", "name": "Pablo Andrés Godoy", "alternateName": "Pablo Godoy", "jobTitle": "Licenciado en Sistemas de Información", "sameAs": ["https://www.linkedin.com/in/godoypablo1972/"] },
        "knowsAbout": ["Desarrollo web", "Aplicaciones móviles", "Software a medida", "Consultoría informática", "Informática médica", "Sistemas de salud"]
    }
    </script>

    <style>
        :root {
            --tinta: #0f172a;
            --texto: #334155;
            --suave: #64748b;
            --linea: #e2e8f0;
            --fondo: #ffffff;
            --fondo-alt: #f8fafc;
            --acento: #2563eb;
            --acento-osc: #1d4ed8;
            --acento-claro: #eff6ff;
            --verde-wa: #25d366;
            --radio: 16px;
            --sombra: 0 1px 2px rgba(15,23,42,.04), 0 8px 24px rgba(15,23,42,.06);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 72px; }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--texto); background: var(--fondo); line-height: 1.7; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; text-rendering: optimizeLegibility; font-feature-settings: 'cv11', 'ss01'; }
        strong, b { font-weight: 600; }
        a { color: var(--acento); }
        img { max-width: 100%; display: block; }
        .contenedor { width: 100%; max-width: 1120px; margin: 0 auto; padding: 0 20px; }
        h1, h2, h3 { font-family: 'Inter Tight', 'Inter', sans-serif; color: var(--tinta); font-weight: 600; line-height: 1.2; letter-spacing: -0.02em; }
        .serif { font-family: 'Instrument Serif', Georgia, serif; font-style: italic; font-weight: 400; letter-spacing: -0.01em; }
        h2 .serif { font-size: 1.06em; color: var(--acento); }
        section { padding: 88px 0; }
        .seccion-alt { background: var(--fondo-alt); }
        .eyebrow { display: inline-block; font-family: 'Inter', system-ui, sans-serif; font-size: .72rem; font-weight: 500; line-height: 1.6; letter-spacing: .18em; text-transform: uppercase; color: var(--acento); margin-bottom: .75rem; }
        .seccion-cabecera { max-width: 640px; margin-bottom: 48px; }
        .seccion-cabecera h2 { font-size: clamp(1.65rem, 3.6vw, 2.25rem); font-weight: 500; }
        .seccion-cabecera p { margin-top: .75rem; color: var(--suave); font-size: 1.04rem; font-weight: 300; }

        /* Botones */
        .btn { display: inline-flex; align-items: center; gap: .5rem; padding: .85rem 1.4rem; border-radius: 12px; font-weight: 500; font-size: .95rem; letter-spacing: .005em; text-decoration: none; border: 1px solid transparent; cursor: pointer; transition: background .15s, transform .15s, box-shadow .15s; font-family: inherit; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primario { background: var(--acento); color: #fff; box-shadow: 0 6px 16px rgba(37,99,235,.25); }
        .btn-primario:hover { background: var(--acento-osc); }
        .btn-secundario { background: #fff; color: var(--tinta); border-color: var(--linea); }
        .btn-secundario:hover { border-color: #cbd5e1; }

        /* Navegación (oscura, translúcida sobre el hero) */
        .nav { position: sticky; top: 0; z-index: 50; background: rgba(11,18,36,.72); backdrop-filter: saturate(160%) blur(14px); -webkit-backdrop-filter: saturate(160%) blur(14px); border-bottom: 1px solid transparent; transition: border-color .2s, background .2s; }
        .nav.con-borde { border-bottom-color: rgba(255,255,255,.08); background: rgba(11,18,36,.88); }
        .nav .contenedor { display: flex; align-items: center; justify-content: space-between; height: 68px; }
        .marca { display: flex; align-items: center; gap: .7rem; text-decoration: none; }
        .marca-iso { width: 40px; height: 40px; flex-shrink: 0; border-radius: 10px; box-shadow: 0 0 0 1px rgba(255,255,255,.12); }
        .marca-texto { line-height: 1.15; }
        .marca-texto strong { display: block; color: #fff; font-size: 1rem; font-weight: 600; font-family: 'Inter Tight', sans-serif; letter-spacing: -0.005em; }
        .marca-texto span { display: block; margin-top: .1rem; font-size: .76rem; font-weight: 400; letter-spacing: .01em; color: #cbd5e1; }
        .nav-links { display: flex; gap: 1.75rem; align-items: center; list-style: none; }
        .nav-links a { color: #cbd5e1; text-decoration: none; font-weight: 400; font-size: .92rem; transition: color .15s; }
        .nav-links a:hover { color: #fff; }
        .nav-links .btn { padding: .55rem 1rem; color: #fff; }
        .nav-links .nav-acceso { display: inline-flex; align-items: center; gap: .5rem; font-size: 1.05rem; color: #94a3b8; }
        .nav-acceso span { display: none; }
        .nav-toggle { display: none; background: none; border: 0; font-size: 1.6rem; color: #fff; cursor: pointer; }

        /* Hero (oscuro, con retrato recortado) */
        .hero { position: relative; overflow: hidden; margin-top: -68px; padding: 112px 0 96px; background:
            radial-gradient(720px 520px at 80% 32%, rgba(37,99,235,.38), transparent 62%),
            radial-gradient(520px 420px at 0% 100%, rgba(56,189,248,.12), transparent 60%),
            #0b1224; color: #cbd5e1; }
        .hero::before { content: ''; position: absolute; inset: 0; pointer-events: none;
            background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 56px 56px;
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, #000 30%, transparent 80%);
                    mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, #000 30%, transparent 80%); }
        .hero .contenedor { position: relative; display: grid; grid-template-columns: 1.2fr .8fr; gap: 40px; align-items: center; }
        .pill { display: inline-flex; align-items: center; gap: .55rem; padding: .4rem .9rem .4rem .7rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04); font-size: .8rem; font-weight: 400; color: #cbd5e1; }
        .pill-punto { position: relative; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; }
        .pill-punto::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; background: rgba(34,197,94,.35); animation: latido 2s ease-out infinite; }
        @keyframes latido { 0% { transform: scale(.6); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }
        .hero h1 { margin-top: 1.5rem; color: #fff; font-size: clamp(2.5rem, 4.6vw, 3.5rem); font-weight: 500; line-height: 1.05; letter-spacing: -0.035em; text-wrap: balance; }
        /* El nombre ya está en la barra: se oculta a la vista pero sigue en el H1 (SEO) */
        .hero-nombre { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
        .hero-titular { display: block; }
        .hero h1 .serif { font-size: 1.06em; letter-spacing: -0.01em; padding-right: .06em; background: linear-gradient(100deg, #7dd3fc 0%, #60a5fa 55%, #a5b4fc 100%); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .hero-bajada { margin-top: 1.5rem; font-size: clamp(1.02rem, 1.9vw, 1.15rem); font-weight: 300; line-height: 1.7; color: #a3b1c6; max-width: 33rem; text-wrap: pretty; }
        .hero-bajada strong { color: #e2e8f0; font-weight: 500; }
        .hero-acciones { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2.25rem; }
        .btn-luz { background: linear-gradient(135deg, #38bdf8 0%, #2563eb 100%); color: #fff; box-shadow: 0 10px 30px rgba(37,99,235,.45), inset 0 1px 0 rgba(255,255,255,.25); }
        .btn-luz:hover { box-shadow: 0 14px 36px rgba(37,99,235,.55), inset 0 1px 0 rgba(255,255,255,.25); }
        .btn-vidrio { background: rgba(255,255,255,.06); color: #fff; border-color: rgba(255,255,255,.16); }
        .btn-vidrio:hover { background: rgba(255,255,255,.1); }
        .btn-vidrio .bi-whatsapp { color: var(--verde-wa); }
        .hero-cifras { display: grid; grid-template-columns: repeat(3, auto); justify-content: start; gap: 2.5rem; margin-top: 2.75rem; padding-top: 1.75rem; border-top: 1px solid rgba(255,255,255,.08); }
        .hero-cifras b { display: block; font-family: 'Inter Tight', sans-serif; font-size: 1.6rem; font-weight: 300; color: #cbd5e1; letter-spacing: -0.02em; line-height: 1.1; }
        .hero-cifras span { display: block; font-size: .76rem; line-height: 1.35; color: #7c8aa0; margin-top: .2rem; }
        /* Logo en círculo (img/logo/logo-hero: fondo azul noche propio, el círculo lo recorta) */
        /* La inclinación 3D (--rx/--ry) la maneja el JS según el mouse */
        .hero-logo { position: relative; width: min(470px, 100%); aspect-ratio: 1; justify-self: center; transform: perspective(900px) rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)); transition: transform .4s ease-out; }
        .hero-logo::before { content: ''; position: absolute; inset: 8%; border-radius: 50%; animation: halo 3.6s ease-out infinite; }
        .hero-logo::after { content: ''; position: absolute; inset: 3%; border-radius: 50%; border: 1px dashed rgba(148,163,184,.28); animation: orbita 60s linear infinite; }
        @keyframes orbita { to { transform: rotate(360deg); } }
        @keyframes halo { 0% { box-shadow: 0 0 0 0 rgba(56,189,248,.35); } 100% { box-shadow: 0 0 0 34px rgba(56,189,248,0); } }
        .hero-logo picture { position: absolute; inset: 8%; z-index: 1; display: block; border-radius: 50%; overflow: hidden; background: #09152f; animation: flotar 7s ease-in-out infinite;
            box-shadow: 0 0 0 1px rgba(96,165,250,.28), 0 0 60px rgba(56,189,248,.18), 0 30px 90px rgba(37,99,235,.45); }
        .hero-logo picture::after { content: ''; position: absolute; inset: 0; background: linear-gradient(115deg, transparent 38%, rgba(255,255,255,.13) 50%, transparent 62%); transform: translateX(-100%); animation: barrido 7s ease-in-out infinite; pointer-events: none; }
        @keyframes barrido { 0%, 55% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }
        .hero-logo img { display: block; width: 100%; height: 100%; object-fit: cover; }
        /* Puntos de luz que giran sobre la órbita */
        .hero-orbita { position: absolute; inset: 3%; z-index: 2; border-radius: 50%; pointer-events: none; animation: orbita 16s linear infinite; }
        .hero-orbita::before, .hero-orbita::after { content: ''; position: absolute; left: 50%; border-radius: 50%; }
        .hero-orbita::before { top: -4px; width: 8px; height: 8px; margin-left: -4px; background: #7dd3fc; box-shadow: 0 0 14px 4px rgba(56,189,248,.7); }
        .hero-orbita::after { bottom: -3px; width: 6px; height: 6px; margin-left: -3px; background: #a5b4fc; box-shadow: 0 0 12px 3px rgba(129,140,248,.6); }
        @keyframes flotar { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        /* Red de nodos animada del fondo (canvas, ver JS) */
        .hero-red { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; opacity: .8;
            -webkit-mask-image: radial-gradient(ellipse 90% 80% at 60% 45%, #000 35%, transparent 85%);
                    mask-image: radial-gradient(ellipse 90% 80% at 60% 45%, #000 35%, transparent 85%); }
        /* Entrada escalonada del hero (--d = demora) */
        .entrar { animation: entrar .9s cubic-bezier(.2,.7,.2,1) var(--d, 0s) backwards; }
        @keyframes entrar { from { opacity: 0; transform: translateY(22px); } }
        .hero-logo.entrar { animation-name: entrar-logo; animation-duration: 1.2s; }
        @keyframes entrar-logo { from { opacity: 0; transform: scale(.86); } }
        .hero-cifras b { font-variant-numeric: tabular-nums; }
        /* Destello que recorre el botón principal */
        .btn-luz { position: relative; overflow: hidden; }
        .btn-luz::after { content: ''; position: absolute; top: 0; bottom: 0; left: 0; width: 40%; background: linear-gradient(90deg, transparent, rgba(255,255,255,.35), transparent); transform: translateX(-150%) skewX(-20deg); animation: destello 4.5s ease-in-out infinite; pointer-events: none; }
        @keyframes destello { 0%, 65% { transform: translateX(-150%) skewX(-20deg); } 100% { transform: translateX(350%) skewX(-20deg); } }
        .hero h1 .serif { background-size: 220% auto; animation: brillo-texto 7s ease-in-out infinite alternate; }
        @keyframes brillo-texto { to { background-position: 100% 50%; } }
        @media (prefers-reduced-motion: reduce) { .pill-punto::after, .hero-logo::before, .hero-logo::after, .hero-logo picture, .hero-logo picture::after, .hero-orbita, .entrar, .btn-luz::after, .hero h1 .serif { animation: none; } }

        /* Cinta de servicios que se desplaza */
        .cinta { background: #0b1224; border-top: 1px solid rgba(255,255,255,.06); padding: 22px 0; color: #cbd5e1; }
        .cinta-ventana { overflow: hidden; -webkit-mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); }
        .cinta-pista { display: flex; width: max-content; animation: cinta 48s linear infinite; }
        .cinta:hover .cinta-pista { animation-play-state: paused; }
        .cinta ul { display: flex; flex-shrink: 0; list-style: none; }
        .cinta li { display: flex; align-items: center; white-space: nowrap; font-family: 'Inter Tight', 'Inter', sans-serif; font-size: 1.08rem; font-weight: 300; letter-spacing: -0.005em; }
        .cinta li i { color: #60a5fa; margin-right: .55rem; }
        .cinta li::after { content: '✦'; margin: 0 1.6rem; color: #38bdf8; font-size: .65rem; opacity: .7; }
        @keyframes cinta { to { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) { .cinta-pista { animation: none; } }

        /* Servicios */
        .grilla-servicios { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .servicio { background: #fff; border: 1px solid var(--linea); border-radius: var(--radio); padding: 32px; transition: box-shadow .2s, border-color .2s, transform .2s; }
        .servicio:hover { box-shadow: var(--sombra); border-color: #cbd5e1; transform: translateY(-2px); }
        .servicio-icono { width: 52px; height: 52px; border-radius: 14px; background: var(--acento-claro); color: var(--acento); display: grid; place-items: center; font-size: 1.5rem; margin-bottom: 1.25rem; }
        .servicio h3 { font-size: 1.2rem; font-weight: 500; }
        .servicio p { margin-top: .5rem; color: var(--suave); }
        .servicio ul { margin-top: 1rem; list-style: none; display: grid; gap: .4rem; font-size: .93rem; }
        .servicio li { display: flex; gap: .5rem; align-items: flex-start; }
        .servicio li i { color: var(--acento); margin-top: .15rem; }
        .servicio-destacado { grid-column: 1 / -1; display: grid; grid-template-columns: auto 1fr 1fr; gap: 8px 32px; align-items: start; background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%); border-color: #bfdbfe; }
        .servicio-destacado .servicio-icono { background: #fff; color: #0d9488; margin-bottom: 0; }
        .servicio-destacado ul { margin-top: 0; }
        .servicio-destacado li i { color: #0d9488; }
        .badge-especialidad { display: inline-block; font-size: .68rem; font-weight: 500; letter-spacing: .14em; text-transform: uppercase; color: #0f766e; background: #ccfbf1; border-radius: 999px; padding: .2rem .65rem; margin-bottom: .5rem; }

        /* Proceso */
        .grilla-pasos { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; counter-reset: paso; }
        .paso { position: relative; padding-top: 3.25rem; }
        .paso::before { counter-increment: paso; content: counter(paso, decimal-leading-zero); position: absolute; top: 0; left: 0; font-family: 'Inter Tight', sans-serif; font-size: 2.1rem; font-weight: 300; color: #94a3b8; letter-spacing: -.02em; }
        .paso h3 { font-size: 1.08rem; font-weight: 500; }
        .paso p { margin-top: .4rem; color: var(--suave); font-size: .95rem; }

        /* Sobre mí */
        .sobre-mi { display: grid; grid-template-columns: .6fr 1.4fr; gap: 56px; align-items: stretch; }
        /* Columna azul con la foto (la foto se funde con el panel para no resaltar) */
        .sobre-panel { position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1.1rem; padding: 44px 24px; border-radius: 20px; text-align: center; color: #a3b1c6;
            background: radial-gradient(320px 260px at 30% 15%, rgba(56,189,248,.22), transparent 70%), linear-gradient(165deg, #1e3a8a 0%, #12245a 50%, #0b1224 100%);
            box-shadow: 0 20px 50px rgba(30,58,138,.18); }
        .sobre-panel::before { content: ''; position: absolute; inset: 0; pointer-events: none;
            background-image: linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 32px 32px;
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 30%, #000 20%, transparent 80%);
                    mask-image: radial-gradient(ellipse 80% 70% at 50% 30%, #000 20%, transparent 80%); }
        .sobre-panel > * { position: relative; }
        /* Retrato que "sale" del círculo, en chico (geometría ligada a img/retrato: centro 50%/56%, radio 42%) */
        .sobre-retrato { display: block; width: 150px; aspect-ratio: 1; }
        .sobre-retrato::before { content: ''; position: absolute; left: 8%; right: 8%; top: 14%; bottom: 2%; border-radius: 50%;
            background: radial-gradient(circle at 32% 26%, rgba(96,165,250,.55) 0%, rgba(37,99,235,.35) 50%, rgba(30,58,138,.25) 100%);
            box-shadow: 0 0 0 1px rgba(255,255,255,.12); }
        .sobre-retrato img { position: relative; display: block; width: 100%; height: auto; }
        .sobre-panel-texto strong { display: block; color: #fff; font-family: 'Inter Tight', 'Inter', sans-serif; font-size: 1.1rem; font-weight: 500; }
        .sobre-panel-texto span { display: block; margin-top: .15rem; font-size: .82rem; color: #93c5fd; }
        .sobre-panel-lugar { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .8rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.05); font-size: .78rem; color: #cbd5e1; }
        .sobre-panel-lugar i { color: #7dd3fc; }
        .sobre-mi p { margin-top: 1rem; }
        .sobre-mi .sobre-intro { margin-top: .9rem; font-family: 'Inter Tight', 'Inter', sans-serif; font-size: clamp(1.2rem, 2.3vw, 1.45rem); font-weight: 300; line-height: 1.5; letter-spacing: -0.01em; color: var(--tinta); }
        .sobre-mi .sobre-intro strong { font-weight: 500; }
        .credenciales { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 2rem; }
        .credencial { border: 1px solid var(--linea); border-radius: 14px; padding: 16px; background: #fff; }
        .credencial i { color: var(--acento); font-size: 1.3rem; }
        .credencial strong { display: block; color: var(--tinta); margin-top: .4rem; font-size: .93rem; font-weight: 500; }
        .credencial span { font-size: .82rem; color: var(--suave); }

        /* Contacto */
        .contacto { display: grid; grid-template-columns: .9fr 1.1fr; gap: 48px; align-items: start; }
        .contacto-info h2 { font-size: clamp(1.65rem, 3.6vw, 2.25rem); font-weight: 500; }
        .contacto-info p { margin-top: 1rem; color: var(--suave); }
        .contacto-canales { margin-top: 2rem; display: grid; gap: 12px; }
        .canal { display: flex; align-items: center; gap: 1rem; padding: 16px 18px; border: 1px solid var(--linea); border-radius: 14px; text-decoration: none; color: var(--tinta); background: #fff; transition: border-color .15s, box-shadow .15s; }
        .canal:hover { border-color: #cbd5e1; box-shadow: var(--sombra); }
        .canal i { font-size: 1.5rem; }
        .canal small { display: block; color: var(--suave); font-size: .82rem; }
        .canal .bi-whatsapp { color: var(--verde-wa); }
        .canal .bi-geo-alt { color: var(--acento); }
        .canal .bi-linkedin { color: #0a66c2; }
        form.formulario { background: #fff; border: 1px solid var(--linea); border-radius: 20px; padding: 32px; box-shadow: var(--sombra); display: grid; gap: 16px; }
        .fila-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .campo label { display: block; font-size: .84rem; font-weight: 500; color: var(--tinta); margin-bottom: .35rem; }
        .campo label span { color: var(--suave); font-weight: 400; }
        .campo input, .campo select, .campo textarea { width: 100%; font: inherit; font-size: .97rem; padding: .75rem .9rem; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; color: var(--tinta); transition: border-color .15s, box-shadow .15s; }
        .campo textarea { min-height: 130px; resize: vertical; }
        .campo input:focus, .campo select:focus, .campo textarea:focus { outline: none; border-color: var(--acento); box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
        .trampa { position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden; }
        .form-aviso { padding: .8rem 1rem; border-radius: 10px; font-size: .92rem; display: none; }
        .form-aviso.ok { display: block; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .form-aviso.error { display: block; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .form-pie { font-size: .8rem; color: var(--suave); }

        /* Footer */
        footer { background: var(--tinta); color: #94a3b8; padding: 48px 0 32px; font-size: .9rem; }
        footer .contenedor { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 24px; align-items: center; }
        footer strong { color: #fff; }
        .footer-logo { height: 30px; width: auto; margin-bottom: 14px; }
        footer a { color: #94a3b8; text-decoration: none; }
        footer a:hover { color: #fff; }
        .footer-links { display: flex; gap: 1.25rem; flex-wrap: wrap; }

        /* WhatsApp flotante */
        .wa-flotante { position: fixed; right: 20px; bottom: 20px; z-index: 60; width: 58px; height: 58px; border-radius: 50%; background: var(--verde-wa); color: #fff; display: grid; place-items: center; font-size: 1.75rem; box-shadow: 0 10px 24px rgba(37,211,102,.4); text-decoration: none; transition: transform .15s; }
        .wa-flotante:hover { transform: scale(1.06); color: #fff; }

        /* Aparición suave al hacer scroll */
        .revelar { opacity: 0; transform: translateY(16px); transition: opacity .6s ease, transform .6s ease; }
        .revelar.visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .revelar { opacity: 1; transform: none; transition: none; } html { scroll-behavior: auto; } }

        /* Efectos al pasar el mouse (van después de .revelar para poder pisar su transform) */
        .servicio.revelar { transition: opacity .6s ease, transform .35s ease, box-shadow .25s, border-color .25s; }
        .servicio.revelar:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(37,99,235,.10); border-color: #bfdbfe; }
        .servicio { position: relative; }
        .servicio > * { position: relative; }
        .servicio::before { content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none; opacity: 0; transition: opacity .3s;
            background: radial-gradient(380px circle at var(--mx, 50%) var(--my, 0%), rgba(37,99,235,.10), transparent 45%); }
        .servicio:hover::before { opacity: 1; }
        .servicio-icono { transition: transform .35s cubic-bezier(.3,1.6,.5,1), background .25s, color .25s; }
        .servicio:hover .servicio-icono { transform: translateY(-3px) rotate(-6deg); background: var(--acento); color: #fff; }
        .servicio-destacado:hover .servicio-icono { background: #0d9488; color: #fff; }
        .credencial { transition: border-color .2s, transform .2s, box-shadow .2s; }
        .credencial:hover { border-color: #bfdbfe; transform: translateY(-2px); box-shadow: var(--sombra); }
        .canal { transition: border-color .15s, box-shadow .15s, transform .15s; }
        .canal:hover { transform: translateX(4px); }

        /* Línea que une los pasos y se llena al aparecer */
        .grilla-pasos { position: relative; }
        .grilla-pasos::before, .grilla-pasos::after { content: ''; position: absolute; top: -22px; left: 0; right: 0; height: 2px; border-radius: 2px; }
        .grilla-pasos::before { background: var(--linea); }
        .grilla-pasos::after { background: linear-gradient(90deg, #38bdf8, #2563eb, #818cf8); transform: scaleX(0); transform-origin: left; transition: transform 1.8s cubic-bezier(.4,0,.2,1) .2s; }
        .grilla-pasos.visible::after { transform: none; }
        .paso::after { content: ''; position: absolute; top: -27px; left: 0; width: 12px; height: 12px; border-radius: 50%; background: #fff; border: 2px solid #cbd5e1; transition: border-color .4s, box-shadow .4s, background .4s; }
        .paso::before { transition: color .5s; }
        .grilla-pasos.visible .paso::after { background: var(--acento); border-color: var(--acento); box-shadow: 0 0 0 5px rgba(37,99,235,.15); }
        .grilla-pasos.visible .paso::before { color: var(--acento); }
        .grilla-pasos.visible .paso:nth-child(2)::after, .grilla-pasos.visible .paso:nth-child(2)::before { transition-delay: .6s; }
        .grilla-pasos.visible .paso:nth-child(3)::after, .grilla-pasos.visible .paso:nth-child(3)::before { transition-delay: 1.1s; }
        .grilla-pasos.visible .paso:nth-child(4)::after, .grilla-pasos.visible .paso:nth-child(4)::before { transition-delay: 1.6s; }

        /* Franja "webmaster profesional" antes de Contacto */
        .cta-banda { position: relative; overflow: hidden; text-align: center; color: #a3b1c6; background:
            radial-gradient(640px 320px at 50% 0%, rgba(37,99,235,.38), transparent 70%),
            radial-gradient(420px 300px at 100% 100%, rgba(56,189,248,.12), transparent 65%), #0b1224; }
        .cta-banda::before { content: ''; position: absolute; inset: 0; pointer-events: none;
            background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 56px 56px;
            -webkit-mask-image: radial-gradient(ellipse 70% 80% at 50% 30%, #000 20%, transparent 75%);
                    mask-image: radial-gradient(ellipse 70% 80% at 50% 30%, #000 20%, transparent 75%); }
        .cta-banda::after { content: ''; position: absolute; top: 0; left: 0; width: 35%; height: 1px; background: linear-gradient(90deg, transparent, #38bdf8, transparent); animation: recorrer 6s linear infinite; }
        @keyframes recorrer { from { transform: translateX(-100%); } to { transform: translateX(300%); } }
        .cta-contenido { position: relative; max-width: 860px; }
        .cta-banda .eyebrow { color: #93c5fd; }
        .cta-banda h2 { color: #fff; font-size: clamp(1.8rem, 4.2vw, 2.8rem); font-weight: 500; letter-spacing: -0.03em; text-wrap: balance; }
        .cta-banda h2 .serif { background: linear-gradient(100deg, #7dd3fc 0%, #60a5fa 55%, #a5b4fc 100%); -webkit-background-clip: text; background-clip: text; color: transparent; padding-right: .06em; }
        .cta-banda p { margin: 1.1rem auto 0; max-width: 36rem; font-size: 1.06rem; font-weight: 300; }
        .cta-garantias { display: flex; flex-wrap: wrap; justify-content: center; gap: .6rem; margin-top: 1.75rem; list-style: none; }
        .cta-garantias li { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .9rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04); font-size: .86rem; color: #e2e8f0; }
        .cta-garantias i { color: #7dd3fc; }
        .cta-banda .hero-acciones { justify-content: center; }
        @media (prefers-reduced-motion: reduce) { .cta-banda::after { animation: none; } }

        /* Barra de progreso de lectura */
        .nav-progreso { position: absolute; left: 0; bottom: -1px; width: 100%; height: 2px; transform-origin: 0 50%; transform: scaleX(0); background: linear-gradient(90deg, #38bdf8, #2563eb, #a5b4fc); pointer-events: none; }

        /* Pulso del botón flotante de WhatsApp */
        .wa-flotante::before { content: ''; position: absolute; inset: 0; border-radius: 50%; background: var(--verde-wa); z-index: -1; animation: pulso-wa 2.6s ease-out infinite; }
        @keyframes pulso-wa { 0% { transform: scale(1); opacity: .55; } 100% { transform: scale(1.7); opacity: 0; } }
        @media (prefers-reduced-motion: reduce) { .wa-flotante::before { animation: none; } }

        /* Responsive */
        @media (max-width: 960px) {
            .grilla-pasos { grid-template-columns: repeat(2, 1fr); row-gap: 32px; }
            .grilla-pasos::before, .grilla-pasos::after, .paso::after { display: none; }
            .credenciales { grid-template-columns: 1fr 1fr 1fr; }
        }
        @media (max-width: 860px) {
            section { padding: 64px 0; }
            .hero { padding: 92px 0 72px; }
            .hero .contenedor, .sobre-mi, .contacto { grid-template-columns: 1fr; gap: 40px; }
            .hero-logo { width: min(380px, 94%); }
            /* Una sola columna: el texto y el H1 se "disuelven" en la grilla para ordenar:
               pill → nombre → logo → titular, bajada y botones → cifras */
            .hero .contenedor { gap: 0; }
            .hero-texto, .hero h1 { display: contents; }
            .hero h1 { font-size: clamp(1.7rem, 8.2vw, 2.05rem); }
            .pill { justify-self: start; }
            .hero-logo { order: 1; width: min(320px, 86%); margin: 28px 0 32px; }
            .hero-titular, .hero-bajada, .hero-acciones { order: 2; }
            .hero-bajada { font-size: .98rem; margin-top: 1.25rem; }
            .hero-titular, .hero-bajada { text-align: justify; text-wrap: wrap; -webkit-hyphens: auto; hyphens: auto; }
            .hero-cifras { order: 3; justify-content: center; text-align: center; gap: 2rem; margin-top: 28px; padding-top: 0; border-top: 0; }
            .hero-cifras b { font-size: 1.25rem; color: #a3b1c6; }
            .hero-cifras span { font-size: .7rem; color: #64748b; }
            .sobre-panel { padding: 32px 20px; }
            .sobre-retrato { width: 130px; }
            .grilla-servicios { grid-template-columns: 1fr; }
            .servicio-destacado { grid-template-columns: 1fr; gap: 16px; }
            .nav-toggle { display: block; }
            .nav-links { position: absolute; top: 68px; left: 0; right: 0; flex-direction: column; align-items: stretch; gap: 0; background: #0b1224; border-bottom: 1px solid rgba(255,255,255,.08); padding: 8px 20px 16px; display: none; }
            .nav-links.abierto { display: flex; }
            .nav-links a { padding: .8rem 0; border-bottom: 1px solid rgba(255,255,255,.06); }
            .nav-links .btn { margin-top: .75rem; justify-content: center; border-bottom: 0; }
            .nav-links .nav-acceso { font-size: .93rem; justify-content: center; border-bottom: 0; }
            .nav-acceso span { display: inline; }
        }
        @media (max-width: 560px) {
            .servicio, form.formulario { padding: 24px; }
            .fila-2, .credenciales { grid-template-columns: 1fr; }
            .grilla-pasos { grid-template-columns: 1fr; }
            .hero-acciones .btn { flex: 1 1 100%; justify-content: center; }
            .hero-cifras { grid-template-columns: repeat(3, 1fr); gap: 1rem; }
            .hero-cifras b { font-size: 1.15rem; }
            .hero-cifras span { font-size: .68rem; }
            .marca-texto span { font-size: .72rem; }
        }
    </style>
</head>
<body>

<!-- ── Navegación ─────────────────────────────────────────── -->
<header class="nav" id="nav">
    <div class="contenedor">
        <a href="#inicio" class="marca" aria-label="Pablo Godoy, inicio">
            <img src="/img/logo/isotipo.svg" class="marca-iso" alt="" width="40" height="40">
            <span class="marca-texto"><strong>Pablo Andrés Godoy</strong><span>Lic. en Sistemas de Información</span></span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false"><i class="bi bi-list"></i></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="#servicios">Servicios</a></li>
            <li><a href="#proceso">Cómo trabajo</a></li>
            <li><a href="#sobre-mi">Sobre mí</a></li>
            <li><a href="#contacto" class="btn btn-primario">Pedí presupuesto</a></li>
            <li><a href="/cifra/login.php?next=%2Fpanel%2F" class="nav-acceso" rel="nofollow" title="Acceso privado" aria-label="Acceso privado"><i class="bi bi-lock" aria-hidden="true"></i><span>Acceso</span></a></li>
        </ul>
    </div>
    <div class="nav-progreso" id="navProgreso"></div>
</header>

<main>
<!-- ── Hero ───────────────────────────────────────────────── -->
<section class="hero" id="inicio">
    <canvas class="hero-red" id="heroRed" aria-hidden="true"></canvas>
    <div class="contenedor">
        <div class="hero-texto">
            <span class="pill entrar"><span class="pill-punto"></span> Disponible para nuevos proyectos</span>
            <h1><span class="hero-nombre entrar" style="--d:.1s">Pablo Andrés Godoy</span><span class="hero-titular entrar" style="--d:.15s">Tecnología <span class="serif">a&nbsp;medida</span> para tu organización.</span></h1>
            <p class="hero-bajada entrar" style="--d:.25s">Sitios web, apps para Android e iPhone, sistemas de gestión y consultoría. <strong>Más de 25 años</strong> convirtiendo ideas en soluciones que funcionan, desde Paraná para todo el país.</p>
            <div class="hero-acciones entrar" style="--d:.4s">
                <a href="#contacto" class="btn btn-luz"><i class="bi bi-send"></i> Pedí tu presupuesto</a>
                <a href="<?= htmlspecialchars($waLink) ?>" class="btn btn-vidrio" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Escribime por WhatsApp</a>
            </div>
            <div class="hero-cifras entrar" style="--d:.55s">
                <div><b data-contar="25" data-pre="+">+25</b><span>años de experiencia</span></div>
                <div><b data-contar="495" data-pre="Nº ">Nº 495</b><span>Matrícula Coprocier</span></div>
                <div><b data-contar="24" data-suf=" h">24 h</b><span>tiempo de respuesta</span></div>
            </div>
        </div>
        <div class="hero-logo entrar" style="--d:.2s">
            <span class="hero-orbita" aria-hidden="true"></span>
            <picture>
                <source srcset="/img/logo/logo-hero.webp" type="image/webp">
                <img src="/img/logo/logo-hero.jpg" alt="Logo de Pablo Godoy: iniciales PG entre redes y símbolos de código" width="600" height="600" fetchpriority="high">
            </picture>
        </div>
    </div>
</section>

<!-- ── Cinta de servicios ─────────────────────────────────── -->
<?php
$cinta = [
    ['window-stack', 'Sitios web'], ['bag', 'Tiendas online'], ['search', 'SEO y Google'],
    ['hdd-network', 'Hosting y dominios'], ['tools', 'Mantenimiento web'], ['phone', 'Apps Android e iPhone'],
    ['diagram-3', 'Sistemas de gestión'], ['heart-pulse', 'Software para salud'], ['shield-check', 'Seguridad y respaldos'],
];
?>
<div class="cinta">
    <div class="cinta-ventana">
        <div class="cinta-pista">
            <?php foreach ([false, true] as $copia): ?>
            <ul<?= $copia ? ' aria-hidden="true"' : '' ?>>
                <?php foreach ($cinta as [$icono, $texto]): ?><li><i class="bi bi-<?= $icono ?>"></i><?= $texto ?></li><?php endforeach; ?>
            </ul>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Servicios ──────────────────────────────────────────── -->
<section id="servicios" class="seccion-alt">
    <div class="contenedor">
        <div class="seccion-cabecera revelar">
            <span class="eyebrow">Servicios</span>
            <h2>Todo lo que tu proyecto necesita, con <span class="serif">un solo responsable</span>.</h2>
            <p>Desde una web para presentar tu empresa hasta un sistema que ordene tu operación diaria.</p>
        </div>
        <div class="grilla-servicios">
            <article class="servicio revelar">
                <div class="servicio-icono"><i class="bi bi-window-stack"></i></div>
                <h3>Sitios web</h3>
                <p>Institucionales, comerciales e industriales. Rápidos, seguros y pensados para que te encuentren y te contacten.</p>
                <ul>
                    <li><i class="bi bi-check2"></i> Diseño adaptado a celulares</li>
                    <li><i class="bi bi-check2"></i> Posicionamiento en Google (SEO)</li>
                    <li><i class="bi bi-check2"></i> Catálogos, formularios y tiendas online</li>
                </ul>
            </article>
            <article class="servicio revelar">
                <div class="servicio-icono"><i class="bi bi-phone"></i></div>
                <h3>Apps para Android y iOS</h3>
                <p>Aplicaciones móviles para tus clientes o tu equipo, publicadas en Google Play y App Store.</p>
                <ul>
                    <li><i class="bi bi-check2"></i> Una sola base para ambas plataformas</li>
                    <li><i class="bi bi-check2"></i> Apps web instalables (PWA)</li>
                    <li><i class="bi bi-check2"></i> Notificaciones y funcionamiento sin conexión</li>
                </ul>
            </article>
            <article class="servicio revelar">
                <div class="servicio-icono"><i class="bi bi-diagram-3"></i></div>
                <h3>Software a medida</h3>
                <p>Sistemas de gestión que se adaptan a tu forma de trabajar, y no al revés.</p>
                <ul>
                    <li><i class="bi bi-check2"></i> Turnos, stock, ventas, expedientes</li>
                    <li><i class="bi bi-check2"></i> Integración con sistemas existentes</li>
                    <li><i class="bi bi-check2"></i> Reportes y tableros de control</li>
                </ul>
            </article>
            <article class="servicio revelar">
                <div class="servicio-icono"><i class="bi bi-lightbulb"></i></div>
                <h3>Consultoría informática</h3>
                <p>Te ayudo a tomar buenas decisiones tecnológicas antes de invertir.</p>
                <ul>
                    <li><i class="bi bi-check2"></i> Diagnóstico y relevamiento de procesos</li>
                    <li><i class="bi bi-check2"></i> Seguridad, respaldos e infraestructura</li>
                    <li><i class="bi bi-check2"></i> Auditoría de sistemas y sitios existentes</li>
                </ul>
            </article>
            <article class="servicio servicio-destacado revelar">
                <div class="servicio-icono"><i class="bi bi-heart-pulse"></i></div>
                <div>
                    <span class="badge-especialidad">Especialización</span>
                    <h3>Sistemas para salud</h3>
                    <p>Más de 25 años desarrollando software para el sector salud. Conozco cómo trabajan clínicas, consultorios, obras sociales y prepagas, y lo que exige manejar información de pacientes.</p>
                </div>
                <ul>
                    <li><i class="bi bi-check2"></i> Turnos, historias clínicas y gestión de consultorios</li>
                    <li><i class="bi bi-check2"></i> Prestaciones, autorizaciones y facturación para obras sociales y prepagas</li>
                    <li><i class="bi bi-check2"></i> Integración entre prestadores y financiadores</li>
                    <li><i class="bi bi-check2"></i> Confidencialidad y resguardo de datos sensibles</li>
                </ul>
            </article>
        </div>
    </div>
</section>

<!-- ── Cómo trabajo ───────────────────────────────────────── -->
<section id="proceso">
    <div class="contenedor">
        <div class="seccion-cabecera revelar">
            <span class="eyebrow">Cómo trabajo</span>
            <h2>Un proceso claro, <span class="serif">sin sorpresas</span>.</h2>
        </div>
        <div class="grilla-pasos trazo">
            <div class="paso revelar">
                <h3>Charlamos</h3>
                <p>Me contás qué necesitás. Entiendo tu negocio y tus objetivos antes de hablar de tecnología.</p>
            </div>
            <div class="paso revelar">
                <h3>Propuesta</h3>
                <p>Recibís un presupuesto detallado con alcance, plazos y costos cerrados.</p>
            </div>
            <div class="paso revelar">
                <h3>Desarrollo</h3>
                <p>Avanzamos por etapas, con entregas parciales que podés ver y probar.</p>
            </div>
            <div class="paso revelar">
                <h3>Puesta en marcha</h3>
                <p>Publicación, capacitación y soporte posterior para que todo siga funcionando.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Sobre mí ───────────────────────────────────────────── -->
<section id="sobre-mi" class="seccion-alt">
    <div class="contenedor sobre-mi">
        <div class="sobre-panel revelar">
            <picture class="sobre-retrato">
                <source srcset="/img/retrato.webp" type="image/webp">
                <img src="/img/retrato.png" alt="Pablo Andrés Godoy, Licenciado en Sistemas de Información" width="150" height="150" loading="lazy">
            </picture>
            <div class="sobre-panel-texto"><strong>Pablo A. Godoy</strong><span>Lic. en Sistemas de Información</span></div>
            <span class="sobre-panel-lugar"><i class="bi bi-geo-alt"></i> Paraná, Entre Ríos</span>
        </div>
        <div class="revelar">
            <h2 class="eyebrow">Sobre Pablo Godoy</h2>
            <p class="sobre-intro">Soy <strong>Pablo A. Godoy</strong>, y hace más de dos décadas desarrollo software para el <strong>sector público y privado</strong>, con especial foco en <strong>proyectos orientados a la salud</strong>.</p>
            <p>Entre otros clientes, desde hace más de 25 años desarrollo soluciones para <a href="https://www.hsc.com.ar" target="_blank" rel="noopener">HSC S.A.</a>, consultora especializada en informática para centros de salud, hospitales, obras sociales y prepagas. En el sector público trabajo en sistemas de gestión donde la seguridad, la trazabilidad y la confiabilidad no son opcionales.</p>
            <p>Hoy pongo esa experiencia al servicio de empresas, comercios, industrias e instituciones que quieren usar la tecnología para trabajar mejor. Me ocupo personalmente de cada proyecto: hablás siempre con quien lo desarrolla.</p>
            <div class="credenciales">
                <div class="credencial"><i class="bi bi-mortarboard"></i><strong>Lic. en Sistemas de Información</strong><span>Título universitario</span></div>
                <div class="credencial"><i class="bi bi-award"></i><strong>Mat. Nº 495</strong><span>Coprocier · Entre Ríos</span></div>
                <div class="credencial"><i class="bi bi-heart-pulse"></i><strong>+25 años</strong><span>Público, privado y salud</span></div>
            </div>
        </div>
    </div>
</section>

<!-- ── Webmaster profesional ─────────────────────────────── -->
<section class="cta-banda">
    <div class="contenedor cta-contenido revelar">
        <span class="eyebrow">Webmaster profesional</span>
        <h2>Tu sitio web, en manos de <span class="serif">un profesional</span>.</h2>
        <p>Diseño, desarrollo, dominio, hosting, SEO y mantenimiento: me ocupo de todo para que tu web esté siempre online, rápida y trayéndote clientes.</p>
        <ul class="cta-garantias">
            <li><i class="bi bi-phone"></i> Adaptada a celulares</li>
            <li><i class="bi bi-lightning-charge"></i> Rápida y en Google</li>
            <li><i class="bi bi-shield-check"></i> Segura y con respaldos</li>
            <li><i class="bi bi-person-check"></i> Hablás siempre conmigo</li>
        </ul>
        <div class="hero-acciones">
            <a href="#contacto" class="btn btn-luz"><i class="bi bi-send"></i> Pedí tu presupuesto</a>
            <a href="<?= htmlspecialchars($waLink) ?>" class="btn btn-vidrio" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Escribime por WhatsApp</a>
        </div>
    </div>
</section>

<!-- ── Contacto ───────────────────────────────────────────── -->
<section id="contacto" class="seccion-alt">
    <div class="contenedor contacto">
        <div class="contacto-info revelar">
            <span class="eyebrow">Contacto</span>
            <h2>Contame tu <span class="serif">idea</span>.</h2>
            <p>La primera charla no tiene costo. Te respondo dentro de las 24 horas hábiles con los próximos pasos.</p>
            <div class="contacto-canales">
                <a href="<?= htmlspecialchars($waLink) ?>" class="canal" target="_blank" rel="noopener">
                    <i class="bi bi-whatsapp"></i>
                    <div><strong>WhatsApp</strong><small>343 450-4750</small></div>
                </a>
                <a href="https://www.linkedin.com/in/godoypablo1972/" class="canal" target="_blank" rel="noopener me">
                    <i class="bi bi-linkedin"></i>
                    <div><strong>LinkedIn</strong><small>linkedin.com/in/godoypablo1972</small></div>
                </a>
                <div class="canal">
                    <i class="bi bi-geo-alt"></i>
                    <div><strong>Paraná, Entre Ríos</strong><small>Atención presencial y remota en todo el país</small></div>
                </div>
            </div>
        </div>

        <form class="formulario revelar" id="formContacto" action="/contacto.php" method="POST" novalidate>
            <div class="form-aviso <?= $enviado ? 'ok' : ($errorForm ? 'error' : '') ?>" id="formAviso" role="status" aria-live="polite">
                <?= $enviado ? '¡Gracias! Te respondo a la brevedad.' : ($errorForm ? 'No se pudo enviar. Revisá los datos o escribime por WhatsApp.' : '') ?>
            </div>
            <div class="fila-2">
                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" autocomplete="name" required maxlength="100">
                </div>
                <div class="campo">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" required maxlength="150">
                </div>
            </div>
            <div class="fila-2">
                <div class="campo">
                    <label for="telefono">Teléfono <span>(opcional)</span></label>
                    <input type="tel" id="telefono" name="telefono" autocomplete="tel" maxlength="40">
                </div>
                <div class="campo">
                    <label for="servicio">¿Qué necesitás?</label>
                    <select id="servicio" name="servicio">
                        <option>Sitio web</option>
                        <option>App Android / iPhone</option>
                        <option>Software a medida</option>
                        <option>Consultoría</option>
                        <option>Otro</option>
                    </select>
                </div>
            </div>
            <div class="campo">
                <label for="mensaje">Tu consulta</label>
                <textarea id="mensaje" name="mensaje" required maxlength="4000" placeholder="Contame brevemente tu proyecto, plazos y cualquier dato útil."></textarea>
            </div>
            <div class="trampa" aria-hidden="true">
                <label for="sitio_web">No completar</label>
                <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>
            <input type="hidden" name="t" value="<?= time() ?>">
            <button type="submit" class="btn btn-primario" id="btnEnviar"><i class="bi bi-send"></i> Enviar consulta</button>
            <p class="form-pie">Tus datos se usan solo para responder tu consulta.</p>
        </form>
    </div>
</section>
</main>

<!-- ── Footer ─────────────────────────────────────────────── -->
<footer>
    <div class="contenedor">
        <div>
            <img src="/img/logo/logotipo-blanco.svg" class="footer-logo" alt="pablogodoy_" width="143" height="30"><br>
            <strong>Lic. Pablo Andrés Godoy</strong><br>
            Licenciado en Sistemas de Información · Mat. Prof. Coprocier Nº 495 · Paraná, Entre Ríos, Argentina
        </div>
        <div class="footer-links">
            <a href="#servicios">Servicios</a>
            <a href="#contacto">Contacto</a>
            <a href="https://www.linkedin.com/in/godoypablo1972/" target="_blank" rel="noopener me"><i class="bi bi-linkedin"></i> LinkedIn</a>
            <a href="/cifra/login.php?next=%2Fpanel%2F" rel="nofollow">Acceso</a>
        </div>
    </div>
    <div class="contenedor" style="margin-top:24px;font-size:.8rem">© <?= date('Y') ?> Pablo Andrés Godoy</div>
</footer>

<a href="<?= htmlspecialchars($waLink) ?>" class="wa-flotante" target="_blank" rel="noopener" aria-label="Escribime por WhatsApp"><i class="bi bi-whatsapp"></i></a>

<script>
    // Menú móvil
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    navToggle.addEventListener('click', () => {
        const abierto = navLinks.classList.toggle('abierto');
        navToggle.setAttribute('aria-expanded', abierto);
        navToggle.innerHTML = abierto ? '<i class="bi bi-x-lg"></i>' : '<i class="bi bi-list"></i>';
    });
    navLinks.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
        navLinks.classList.remove('abierto');
        navToggle.innerHTML = '<i class="bi bi-list"></i>';
    }));

    const sinMovimiento = matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Borde de la barra y progreso de lectura al hacer scroll
    const nav = document.getElementById('nav');
    const progreso = document.getElementById('navProgreso');
    const alScroll = () => {
        nav.classList.toggle('con-borde', scrollY > 8);
        const max = document.documentElement.scrollHeight - innerHeight;
        progreso.style.transform = `scaleX(${max > 0 ? Math.min(1, scrollY / max) : 0})`;
    };
    addEventListener('scroll', alScroll, { passive: true });
    alScroll();

    // Aparición suave de secciones (escalonada dentro de cada grilla)
    document.querySelectorAll('.grilla-servicios, .grilla-pasos').forEach(g =>
        [...g.children].forEach((el, i) => el.style.transitionDelay = `${i * 110}ms`));
    const obs = new IntersectionObserver(entradas => entradas.forEach(e => {
        if (!e.isIntersecting) return;
        e.target.classList.add('visible');
        obs.unobserve(e.target);
        // Sacar la demora para que no afecte los efectos de hover
        setTimeout(() => e.target.style.transitionDelay = '', 1400);
    }), { threshold: 0.12 });
    document.querySelectorAll('.revelar, .trazo').forEach(el => obs.observe(el));

    // Cifras del hero que cuentan desde cero
    if (!sinMovimiento) document.querySelectorAll('[data-contar]').forEach(el => {
        const fin = +el.dataset.contar, pre = el.dataset.pre || '', suf = el.dataset.suf || '';
        const inicio = performance.now() + 600, duracion = 1600;
        el.style.minWidth = el.offsetWidth + 'px';
        el.textContent = pre + '0' + suf;
        const paso = t => {
            const k = Math.min(1, Math.max(0, (t - inicio) / duracion));
            el.textContent = pre + Math.round(fin * (1 - Math.pow(1 - k, 3))) + suf;
            if (k < 1) requestAnimationFrame(paso);
        };
        requestAnimationFrame(paso);
    });

    // Red de nodos animada en el fondo del hero (se pausa fuera de pantalla)
    (() => {
        const cv = document.getElementById('heroRed');
        if (!cv || sinMovimiento) return;
        const ctx = cv.getContext('2d');
        const hero = cv.parentElement;
        const DIST = 130, DIST_RATON = 190;
        let w = 0, h = 0, nodos = [], raton = null, enPantalla = true, corriendo = false;

        const medir = () => {
            const dpr = Math.min(devicePixelRatio || 1, 2);
            const nuevoAncho = hero.clientWidth;
            h = hero.clientHeight;
            cv.width = nuevoAncho * dpr; cv.height = h * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            if (nuevoAncho === w && nodos.length) return; // en el celular la barra de direcciones cambia el alto: no regenerar
            w = nuevoAncho;
            const cantidad = Math.round(Math.min(70, w * h / 15000));
            nodos = Array.from({ length: cantidad }, () => ({
                x: Math.random() * w, y: Math.random() * h,
                vx: (Math.random() - .5) * .4, vy: (Math.random() - .5) * .4,
            }));
        };

        const cuadro = () => {
            if (!enPantalla || document.hidden) { corriendo = false; return; }
            ctx.clearRect(0, 0, w, h);
            for (const p of nodos) {
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;
            }
            ctx.lineWidth = 1;
            for (let i = 0; i < nodos.length; i++) {
                const a = nodos[i];
                for (let j = i + 1; j < nodos.length; j++) {
                    const b = nodos[j], d = Math.hypot(a.x - b.x, a.y - b.y);
                    if (d < DIST) {
                        ctx.strokeStyle = `rgba(96,165,250,${(1 - d / DIST) * .32})`;
                        ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(b.x, b.y); ctx.stroke();
                    }
                }
                if (raton) {
                    const d = Math.hypot(a.x - raton.x, a.y - raton.y);
                    if (d < DIST_RATON) {
                        ctx.strokeStyle = `rgba(125,211,252,${(1 - d / DIST_RATON) * .6})`;
                        ctx.beginPath(); ctx.moveTo(a.x, a.y); ctx.lineTo(raton.x, raton.y); ctx.stroke();
                    }
                }
                ctx.fillStyle = 'rgba(147,197,253,.75)';
                ctx.beginPath(); ctx.arc(a.x, a.y, 1.7, 0, Math.PI * 2); ctx.fill();
            }
            requestAnimationFrame(cuadro);
        };
        const arrancar = () => {
            if (corriendo || !enPantalla || document.hidden) return;
            corriendo = true;
            requestAnimationFrame(cuadro);
        };

        medir();
        let espera;
        addEventListener('resize', () => { clearTimeout(espera); espera = setTimeout(medir, 150); });
        new IntersectionObserver(([e]) => { enPantalla = e.isIntersecting; arrancar(); }).observe(hero);
        document.addEventListener('visibilitychange', arrancar);
        hero.addEventListener('pointermove', e => {
            const r = hero.getBoundingClientRect();
            raton = { x: e.clientX - r.left, y: e.clientY - r.top };
        });
        hero.addEventListener('pointerleave', () => raton = null);
        arrancar();
    })();

    // Inclinación 3D del logo y luz que sigue al mouse en las tarjetas (solo con mouse)
    if (!sinMovimiento && matchMedia('(hover: hover)').matches) {
        const logo = document.querySelector('.hero-logo');
        const hero = document.getElementById('inicio');
        hero.addEventListener('pointermove', e => {
            const r = logo.getBoundingClientRect();
            const x = Math.max(-1, Math.min(1, (e.clientX - r.left - r.width / 2) / r.width));
            const y = Math.max(-1, Math.min(1, (e.clientY - r.top - r.height / 2) / r.height));
            logo.style.setProperty('--ry', `${(x * 12).toFixed(2)}deg`);
            logo.style.setProperty('--rx', `${(-y * 12).toFixed(2)}deg`);
        });
        hero.addEventListener('pointerleave', () => { logo.style.setProperty('--rx', '0deg'); logo.style.setProperty('--ry', '0deg'); });

        document.querySelectorAll('.servicio').forEach(el => el.addEventListener('pointermove', e => {
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${e.clientX - r.left}px`);
            el.style.setProperty('--my', `${e.clientY - r.top}px`);
        }));
    }

    // Envío del formulario sin recargar
    const form = document.getElementById('formContacto');
    const aviso = document.getElementById('formAviso');
    const btn = document.getElementById('btnEnviar');
    form.addEventListener('submit', async (ev) => {
        ev.preventDefault();
        if (!form.checkValidity()) {
            aviso.className = 'form-aviso error';
            aviso.textContent = 'Completá nombre, un email válido y tu consulta.';
            form.reportValidity();
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando…';
        try {
            const r = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'fetch' } });
            const d = await r.json();
            aviso.className = 'form-aviso ' + (d.ok ? 'ok' : 'error');
            aviso.textContent = d.mensaje;
            if (d.ok) form.reset();
        } catch (e) {
            aviso.className = 'form-aviso error';
            aviso.textContent = 'No se pudo enviar. Escribime por WhatsApp, por favor.';
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send"></i> Enviar consulta';
    });
</script>
</body>
</html>
