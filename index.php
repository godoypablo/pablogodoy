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
        .hero { position: relative; overflow: hidden; margin-top: -68px; padding: 148px 0 104px; background:
            radial-gradient(720px 520px at 80% 32%, rgba(37,99,235,.38), transparent 62%),
            radial-gradient(520px 420px at 0% 100%, rgba(56,189,248,.12), transparent 60%),
            #0b1224; color: #cbd5e1; }
        .hero::before { content: ''; position: absolute; inset: 0; pointer-events: none;
            background-image: linear-gradient(rgba(255,255,255,.045) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.045) 1px, transparent 1px);
            background-size: 56px 56px;
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, #000 30%, transparent 80%);
                    mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, #000 30%, transparent 80%); }
        .hero .contenedor { position: relative; display: grid; grid-template-columns: 1.08fr .92fr; gap: 48px; align-items: center; }
        .pill { display: inline-flex; align-items: center; gap: .55rem; padding: .4rem .9rem .4rem .7rem; border-radius: 999px; border: 1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.04); font-size: .8rem; font-weight: 400; color: #cbd5e1; }
        .pill-punto { position: relative; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; }
        .pill-punto::after { content: ''; position: absolute; inset: -4px; border-radius: 50%; background: rgba(34,197,94,.35); animation: latido 2s ease-out infinite; }
        @keyframes latido { 0% { transform: scale(.6); opacity: 1; } 100% { transform: scale(1.8); opacity: 0; } }
        .hero h1 { margin-top: 1.4rem; color: #fff; font-size: clamp(2.5rem, 5.8vw, 4.3rem); font-weight: 500; line-height: 1.05; letter-spacing: -0.035em; text-wrap: balance; }
        .hero-nombre { display: block; margin-bottom: 1rem; font-family: 'Inter', system-ui, sans-serif; font-size: .82rem; font-weight: 500; line-height: 1.4; letter-spacing: .2em; text-transform: uppercase; color: #93c5fd; }
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
        .hero-cifras b { display: block; font-family: 'Inter Tight', sans-serif; font-size: 1.85rem; font-weight: 300; color: #fff; letter-spacing: -0.02em; line-height: 1.1; }
        .hero-cifras span { display: block; font-size: .8rem; line-height: 1.35; color: #94a3b8; margin-top: .2rem; }
        /* Retrato que "sale" del círculo (geometría ligada a img/retrato: centro 50%/56%, radio 42%) */
        .hero-retrato { position: relative; width: min(470px, 100%); aspect-ratio: 1; justify-self: center; }
        .hero-retrato::before { content: ''; position: absolute; left: 8%; right: 8%; top: 14%; bottom: 2%; border-radius: 50%;
            background: radial-gradient(circle at 32% 26%, #60a5fa 0%, #2563eb 42%, #1e3a8a 100%);
            box-shadow: 0 0 0 1px rgba(255,255,255,.1), 0 30px 90px rgba(37,99,235,.5); }
        .hero-retrato::after { content: ''; position: absolute; left: 3%; right: 3%; top: 9%; bottom: -3%; border-radius: 50%; border: 1px dashed rgba(148,163,184,.28); animation: orbita 60s linear infinite; }
        @keyframes orbita { to { transform: rotate(360deg); } }
        .hero-retrato picture, .hero-retrato img { position: relative; z-index: 1; display: block; width: 100%; height: auto; }
        .hero-retrato img { filter: drop-shadow(0 24px 40px rgba(0,0,0,.35)); }
        .chip { position: absolute; z-index: 2; display: flex; align-items: center; gap: .65rem; padding: .6rem .9rem .6rem .6rem; border-radius: 16px; background: rgba(15,23,42,.62); -webkit-backdrop-filter: blur(14px); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,.14); box-shadow: 0 18px 40px rgba(0,0,0,.35); color: #94a3b8; font-size: .76rem; line-height: 1.3; animation: flotar 6s ease-in-out infinite; }
        .chip strong { display: block; color: #fff; font-size: .82rem; font-weight: 500; }
        .chip-icono { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; font-size: 1.05rem; flex-shrink: 0; }
        .chip-titulo { left: -10%; bottom: 12%; }
        .chip-titulo .chip-icono { background: rgba(56,189,248,.16); color: #7dd3fc; }
        .chip-salud { right: -8%; bottom: -3%; animation-delay: -3s; }
        .chip-salud .chip-icono { background: rgba(45,212,191,.16); color: #5eead4; }
        @keyframes flotar { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        @media (prefers-reduced-motion: reduce) { .pill-punto::after, .hero-retrato::after, .chip { animation: none; } }

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

        /* Proyectos */
        .grilla-proyectos { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
        .proyecto { background: #fff; border: 1px solid var(--linea); border-radius: var(--radio); overflow: hidden; display: flex; flex-direction: column; }
        .proyecto-vista { aspect-ratio: 16 / 10; display: grid; place-items: center; padding: 28px; position: relative; overflow: hidden; }
        .proyecto-cuerpo { padding: 28px; display: flex; flex-direction: column; gap: .6rem; flex: 1; }
        .proyecto-tipo { font-size: .7rem; font-weight: 500; color: var(--acento); text-transform: uppercase; letter-spacing: .16em; }
        .proyecto h3 { font-size: 1.3rem; font-weight: 500; }
        .proyecto h3 img { display: block; height: 44px; width: auto; }
        .logo-cifra { display: inline-flex; align-items: center; gap: .45rem; padding: .35rem .85rem .35rem .7rem; border-radius: 8px; background: #211e54; color: #fff; font-size: 1.3rem; font-weight: 700; letter-spacing: -0.01em; line-height: 1.2; }
        .logo-cifra i { font-size: 1.05rem; }
        .proyecto p { color: var(--suave); }
        .etiquetas { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .25rem; }
        .etiquetas span { font-size: .76rem; font-weight: 400; padding: .25rem .6rem; border-radius: 999px; background: var(--fondo-alt); border: 1px solid var(--linea); color: var(--texto); }
        .proyecto-link { margin-top: auto; padding-top: .75rem; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: .35rem; }
        .proyecto-privado { margin-top: auto; padding-top: .75rem; font-size: .88rem; color: var(--suave); display: inline-flex; align-items: center; gap: .35rem; }

        /* Mockup TurnitoAhora (ilustrativo) */
        .vista-turnito { background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 50%, #ede9fe 100%); }
        .tel { width: 200px; background: #fff; border-radius: 22px; box-shadow: 0 18px 40px rgba(76,29,149,.18); padding: 14px; font-size: .62rem; color: #334155; }
        .tel-cab { display: flex; align-items: center; gap: .45rem; margin-bottom: .6rem; }
        .tel-avatar { width: 26px; height: 26px; border-radius: 50%; background: #db2777; color: #fff; display: grid; place-items: center; font-weight: 700; font-size: .6rem; }
        .tel-cab strong { display: block; font-size: .7rem; color: #0f172a; }
        .tel-dias { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-bottom: .5rem; }
        .tel-dias span { text-align: center; padding: 4px 0; border-radius: 6px; background: #f1f5f9; }
        .tel-dias span.sel { background: #db2777; color: #fff; }
        .tel-horas { display: grid; grid-template-columns: repeat(3, 1fr); gap: 4px; }
        .tel-horas span { text-align: center; padding: 5px 0; border-radius: 6px; border: 1px solid #e2e8f0; }
        .tel-horas span.sel { border-color: #db2777; color: #db2777; font-weight: 700; }
        .tel-ok { margin-top: .6rem; background: #dcfce7; color: #166534; border-radius: 8px; padding: 6px; text-align: center; font-weight: 600; }

        /* Mockup Cifra (ilustrativo, datos ficticios) */
        .vista-cifra { background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 60%, #d1fae5 100%); }
        .panel-cifra { width: 100%; max-width: 330px; background: #fff; border-radius: 14px; box-shadow: 0 18px 40px rgba(49,46,129,.16); padding: 14px; font-size: .66rem; }
        .panel-cifra-cab { display: flex; justify-content: space-between; align-items: center; margin-bottom: .6rem; }
        .panel-cifra-cab strong { font-size: .85rem; color: #0f172a; }
        .panel-cifra-cab strong b { color: #6366f1; }
        .kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: .7rem; }
        .kpi { background: #f8fafc; border-radius: 8px; padding: 6px 7px; }
        .kpi small { display: block; color: #64748b; font-size: .56rem; }
        .kpi b { color: #0f172a; font-size: .74rem; }
        .barras { display: flex; align-items: flex-end; gap: 5px; height: 58px; padding: 0 2px; border-bottom: 1px solid #e2e8f0; }
        .barras span { flex: 1; border-radius: 3px 3px 0 0; background: linear-gradient(#818cf8, #6366f1); }
        .filas { margin-top: .55rem; display: grid; gap: 4px; }
        .filas div { display: flex; justify-content: space-between; padding: 4px 6px; border-radius: 6px; background: #f8fafc; }
        .filas .ok { color: #059669; font-weight: 600; }

        /* Sobre mí */
        .sobre-mi { display: grid; grid-template-columns: .55fr 1.45fr; gap: 56px; align-items: center; }
        .sobre-retrato { display: block; width: 100%; max-width: 260px; aspect-ratio: 1; justify-self: center; border-radius: 50%; overflow: hidden; box-shadow: 0 0 0 6px #fff, 0 0 0 7px var(--linea), 0 12px 30px rgba(15,23,42,.10); }
        .sobre-retrato img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .sobre-mi p { margin-top: 1rem; }
        .sobre-mi .sobre-intro { margin-top: .9rem; font-family: 'Inter Tight', 'Inter', sans-serif; font-size: clamp(1.2rem, 2.3vw, 1.45rem); font-weight: 300; line-height: 1.5; letter-spacing: -0.01em; color: var(--tinta); }
        .sobre-mi .sobre-intro strong { font-weight: 500; }
        .logo-hsc { display: inline-block; height: 2.6em; width: auto; vertical-align: middle; margin: -.4em .1em -.3em; }
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

        /* Responsive */
        @media (max-width: 960px) {
            .grilla-pasos { grid-template-columns: repeat(2, 1fr); row-gap: 32px; }
            .credenciales { grid-template-columns: 1fr 1fr 1fr; }
        }
        @media (max-width: 860px) {
            section { padding: 64px 0; }
            .hero { padding: 108px 0 72px; }
            .hero .contenedor, .sobre-mi, .contacto { grid-template-columns: 1fr; gap: 40px; }
            .hero-retrato { width: min(380px, 94%); }
            .sobre-retrato { max-width: 220px; }
            .chip { font-size: .7rem; padding: .5rem .75rem .5rem .5rem; }
            .chip strong { font-size: .76rem; }
            .chip-icono { width: 28px; height: 28px; font-size: .9rem; }
            .chip-titulo { left: -3%; bottom: 6%; }
            .chip-salud { right: -3%; bottom: -9%; }
            .grilla-servicios, .grilla-proyectos { grid-template-columns: 1fr; }
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
            .hero-cifras b { font-size: 1.5rem; }
            .hero-cifras span { font-size: .72rem; }
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
            <span class="marca-texto"><strong>Pablo Godoy</strong><span>Lic. en Sistemas de Información</span></span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Abrir menú" aria-expanded="false"><i class="bi bi-list"></i></button>
        <ul class="nav-links" id="navLinks">
            <li><a href="#servicios">Servicios</a></li>
            <li><a href="#proceso">Cómo trabajo</a></li>
            <li><a href="#proyectos">Proyectos</a></li>
            <li><a href="#sobre-mi">Sobre mí</a></li>
            <li><a href="#contacto" class="btn btn-primario">Pedí presupuesto</a></li>
            <li><a href="/cifra/login.php?next=%2Fpanel%2F" class="nav-acceso" rel="nofollow" title="Acceso privado" aria-label="Acceso privado"><i class="bi bi-lock" aria-hidden="true"></i><span>Acceso</span></a></li>
        </ul>
    </div>
</header>

<main>
<!-- ── Hero ───────────────────────────────────────────────── -->
<section class="hero" id="inicio">
    <div class="contenedor">
        <div class="hero-texto">
            <span class="pill"><span class="pill-punto"></span> Disponible para nuevos proyectos</span>
            <h1><span class="hero-nombre">Pablo Andrés Godoy</span>Tecnología <span class="serif">a&nbsp;medida</span> para tu organización.</h1>
            <p class="hero-bajada">Sitios web, apps para Android e iPhone, sistemas de gestión y consultoría. <strong>Más de 25 años</strong> convirtiendo ideas en soluciones que funcionan, desde Paraná para todo el país.</p>
            <div class="hero-acciones">
                <a href="#contacto" class="btn btn-luz"><i class="bi bi-send"></i> Pedí tu presupuesto</a>
                <a href="<?= htmlspecialchars($waLink) ?>" class="btn btn-vidrio" target="_blank" rel="noopener"><i class="bi bi-whatsapp"></i> Escribime por WhatsApp</a>
            </div>
            <div class="hero-cifras">
                <div><b>+25</b><span>años de experiencia</span></div>
                <div><b>Nº 495</b><span>Matrícula Coprocier</span></div>
                <div><b>24 h</b><span>tiempo de respuesta</span></div>
            </div>
        </div>
        <div class="hero-retrato">
            <picture>
                <source srcset="/img/retrato.webp" type="image/webp">
                <img src="/img/retrato.png" alt="Pablo Andrés Godoy, Licenciado en Sistemas de Información" width="470" height="470" fetchpriority="high">
            </picture>
            <div class="chip chip-titulo">
                <span class="chip-icono"><i class="bi bi-mortarboard-fill"></i></span>
                <div><strong>Lic. en Sistemas de Información</strong>Matriculado · Coprocier</div>
            </div>
            <div class="chip chip-salud">
                <span class="chip-icono"><i class="bi bi-heart-pulse-fill"></i></span>
                <div><strong>Especialista en salud</strong>+25 años en el sector</div>
            </div>
        </div>
    </div>
</section>

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
                <h3>Apps para Android e iPhone</h3>
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
        <div class="grilla-pasos">
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

<!-- ── Proyectos ──────────────────────────────────────────── -->
<section id="proyectos" class="seccion-alt">
    <div class="contenedor">
        <div class="seccion-cabecera revelar">
            <span class="eyebrow">Proyectos</span>
            <h2>Algunos trabajos <span class="serif">realizados</span>.</h2>
            <p>Productos en uso real, diseñados y desarrollados de punta a punta.</p>
        </div>
        <div class="grilla-proyectos">
            <article class="proyecto revelar">
                <div class="proyecto-vista vista-turnito" aria-hidden="true">
                    <div class="tel">
                        <div class="tel-cab"><span class="tel-avatar">FA</span><div><strong>Flor Alisados</strong>Paraná · Alisado 3 h</div></div>
                        <div class="tel-dias"><span>lun 12</span><span class="sel">mar 13</span><span>mié 14</span><span>jue 15</span></div>
                        <div class="tel-horas"><span>09:00</span><span class="sel">09:30</span><span>10:00</span><span>14:00</span><span>14:30</span><span>15:00</span></div>
                        <div class="tel-ok">✓ ¡Listo! Tu turno está reservado</div>
                    </div>
                </div>
                <div class="proyecto-cuerpo">
                    <span class="proyecto-tipo">Plataforma web · SaaS</span>
                    <h3><img src="/img/logo/turnitoahora.png" alt="turnitoAhora" width="286" height="59" loading="lazy"></h3>
                    <p>Agenda de turnos online para peluquerías, estética, uñas, tatuajes y más. Los clientes reservan solos desde Instagram o WhatsApp, sin registrarse, y el sistema evita turnos superpuestos.</p>
                    <div class="etiquetas"><span>Reservas online</span><span>Avisos por WhatsApp</span><span>Multi-negocio</span></div>
                    <a href="https://www.turnitoahora.com.ar" class="proyecto-link" target="_blank" rel="noopener">Visitar turnitoahora.com.ar <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </article>
            <article class="proyecto revelar">
                <div class="proyecto-vista vista-cifra" aria-hidden="true">
                    <div class="panel-cifra">
                        <div class="panel-cifra-cab"><strong>Cifra<b>.</b></strong><span>Octubre 2026</span></div>
                        <div class="kpis">
                            <div class="kpi"><small>Ingresos</small><b>$ 1.250.000</b></div>
                            <div class="kpi"><small>Pagado</small><b>$ 842.300</b></div>
                            <div class="kpi"><small>Disponible</small><b>$ 407.700</b></div>
                        </div>
                        <div class="barras"><span style="height:45%"></span><span style="height:70%"></span><span style="height:55%"></span><span style="height:85%"></span><span style="height:60%"></span><span style="height:40%"></span><span style="height:75%"></span><span style="height:50%"></span></div>
                        <div class="filas">
                            <div><span>Servicios</span><span class="ok">Pagado</span></div>
                            <div><span>Tarjeta · cuota 3/6</span><span>$ 58.900</span></div>
                        </div>
                    </div>
                </div>
                <div class="proyecto-cuerpo">
                    <span class="proyecto-tipo">App web instalable (PWA)</span>
                    <h3><span class="logo-cifra"><i class="bi bi-bar-chart-fill" aria-hidden="true"></i>Cifra</span></h3>
                    <p>Sistema de gestión financiera: cuentas, gastos e ingresos mensuales, tarjetas de crédito con proyección de cuotas y seguimiento de créditos en UVA. Se instala en el celular como una app.</p>
                    <div class="etiquetas"><span>PHP + MySQL</span><span>Gráficos</span><span>Funciona offline</span></div>
                    <span class="proyecto-privado"><i class="bi bi-lock"></i> Sistema privado · imagen ilustrativa con datos ficticios</span>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- ── Sobre mí ───────────────────────────────────────────── -->
<section id="sobre-mi">
    <div class="contenedor sobre-mi">
        <picture class="sobre-retrato revelar">
            <source srcset="/img/pablo-godoy.webp" type="image/webp">
            <img src="/img/pablo-godoy.jpg" alt="Pablo Andrés Godoy, Licenciado en Sistemas de Información" width="260" height="260" loading="lazy">
        </picture>
        <div class="revelar">
            <h2 class="eyebrow">Sobre Pablo Godoy</h2>
            <p class="sobre-intro">Soy <strong>Pablo A. Godoy</strong>, y hace más de dos décadas desarrollo software para el <strong>sector público y privado</strong>, con especial foco en <strong>proyectos orientados a la salud</strong>.</p>
            <p>Entre otros clientes, desde hace más de 25 años desarrollo soluciones para <a href="https://www.hsc.com.ar" target="_blank" rel="noopener"><img src="/img/logo/hsc.png" class="logo-hsc" alt="HSC S.A. — Health System Consulting" width="173" height="81" loading="lazy"></a>, consultora especializada en informática para centros de salud, hospitales, obras sociales y prepagas. En el sector público trabajo en sistemas de gestión donde la seguridad, la trazabilidad y la confiabilidad no son opcionales.</p>
            <p>Hoy pongo esa experiencia al servicio de empresas, comercios, industrias e instituciones que quieren usar la tecnología para trabajar mejor. Me ocupo personalmente de cada proyecto: hablás siempre con quien lo desarrolla.</p>
            <div class="credenciales">
                <div class="credencial"><i class="bi bi-mortarboard"></i><strong>Lic. en Sistemas de Información</strong><span>Título universitario</span></div>
                <div class="credencial"><i class="bi bi-award"></i><strong>Mat. Nº 495</strong><span>Coprocier · Entre Ríos</span></div>
                <div class="credencial"><i class="bi bi-heart-pulse"></i><strong>+25 años</strong><span>Público, privado y salud</span></div>
            </div>
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
            <a href="#proyectos">Proyectos</a>
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

    // Borde de la barra al hacer scroll
    const nav = document.getElementById('nav');
    addEventListener('scroll', () => nav.classList.toggle('con-borde', scrollY > 8), { passive: true });

    // Aparición suave de secciones
    const obs = new IntersectionObserver(entradas => entradas.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('visible'); obs.unobserve(e.target); }
    }), { threshold: 0.12 });
    document.querySelectorAll('.revelar').forEach(el => obs.observe(el));

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
