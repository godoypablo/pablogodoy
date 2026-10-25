# Documentación del Proyecto PabloGodoy

## 📌 Contenidos Principales

### 1. Claude Code: Manual de Cabecera

**Objetivo:** Estrategias de bajo consumo de tokens para programadores Pro.

#### Comandos Mágicos de Terminal

| Comando | Función | Beneficio |
|---------|---------|-----------|
| `/compact` | Resume el historial del chat actual | Reduce el costo de cada mensaje nuevo |
| `/reset` | Limpia todo el contexto acumulado | Cero costo de memoria anterior |
| `/init` | Configura el archivo `CLAUDE.md` | Evita repetir instrucciones |
| `/clear` | Limpia el texto visual de la pantalla | Mejora el orden visual |
| `/help` | Lista todos los comandos internos | Guía rápida de uso |

#### Configuración de Archivos "Ignorar"

Archivos **imprescindibles** en `.clfignore`:
- `node_modules/`
- `dist/`
- `.git/`
- `package-lock.json`
- `*.log`

**Ahorro Estimado:** 30-60% de tokens por sesión al evitar archivos gigantes de dependencias.

#### Metodología de Programación Eficiente

- **Menciones Directas:** No pidas "busca en el proyecto". Di: "Revisa `src/auth.js`". Obligar a Claude a leer un solo archivo es mucho más barato que dejarlo escanear todo.

- **Regla de Oro CLAUDE.md:** Escribe aquí tus preferencias de estilo (ej: "Usa Tailwind", "Código en ES6"). Claude leerá esto una vez y no tendrás que gastar tokens dándole instrucciones en cada chat.

- **Planear antes de Actuar:** Antes de pedir código, dile: "Hazme un resumen de los cambios que harás". Si el plan está mal, lo corriges con 2 frases en lugar de pagar por 100 líneas de código erróneas.

#### Consejos de Cabecera

- **Modulariza:** Divide archivos grandes en pequeños. Claude gasta menos leyendo 5 archivos de 50 líneas que uno de 250.
- **Sesiones Atómicas:** Resuelve una tarea (ej: el Login), verifica que funcione, haz `/reset` y empieza la siguiente tarea (ej: el Registro).
- **Evita el "Lock":** Nunca dejes que Claude analice archivos como `package-lock.json` o `yarn.lock`. Son puro texto innecesario.

---

### 2. GitHub: Guía Paso a Paso

**Objetivo:** Flujo de trabajo simple y directo para versionado de código.

#### REGLA MÁS IMPORTANTE

```bash
# ANTES DE TRABAJAR:
git pull

# AL TERMINAR:
git add .
git commit -m "avance"
git push
```

#### Primera Vez (Crear Proyecto)

```bash
mkdir proyecto-nuevo
cd proyecto-nuevo
git init
git add .
git commit -m "inicio"
git remote add origin https://github.com/godoypablo/proyecto-nuevo.git
git branch -M main
git push -u origin main
```
#### Trabajar Todos los Días

**Paso 1:** `git pull`
**Paso 2:** Editar archivos normalmente
**Paso 3:** `git add . && git commit -m "avance"`
**Paso 4:** `git push`

#### Cambiar de PC

```bash
# Primera vez en otra PC
git clone https://github.com/godoypablo/proyecto-nuevo.git
cd proyecto-nuevo

# Otro día en otra PC
git pull
```

#### Subir al Servidor Web

NUNCA copiar archivos manualmente. En el servidor:

```bash
git pull
```

#### Volver Atrás (Versiones Anteriores)

```bash
# Ver versiones
git log --oneline

# Volver a una versión específica
git reset --hard <CODIGO>
git push --force

# O de forma segura (sin force):
git revert HEAD
git push
```

#### Si Algo Falla

```bash
git status
```

Debe decir: "En la rama main - Tu rama está actualizada con 'origin/main'"

---

### 3. Linux: Command Center Pro 2026

**Objetivo:** Comandos esenciales para dominar la terminal.

#### Rutinas y Mantenimiento

```bash
# Update & Cleanup (mantenimiento total)
sudo apt update && sudo apt upgrade -y && sudo apt autoremove
```

#### Navegación

```bash
# Listar archivos ordenados por tamaño
ls -laS --block-size=M

# Print Working Directory (ubicación actual)
pwd
```

#### Archivos

```bash
# Buscar texto en archivos recursivamente
grep -r "texto_a_buscar" /ruta/carpeta

# Descomprimir archivos .tar
tar -xvf archivo.tar
```

#### Usuarios y Permisos

```bash
# Dar permisos de ejecución
chmod +x script.sh
```

#### Sistema

```bash
# Ver procesos que más RAM consumen
ps aux --sort=-%mem | head -n 10

# Ver últimos errores del sistema
journalctl -xe
```

#### Redes

```bash
# Analizar calidad de conexión (ping + traceroute)
mtr google.com
```

---

### 4. Viaje Nueva Zelanda y Australia 2027 - "Gira Oceanía"

**Objetivo:** Sitio web interactivo con información completa del viaje a Nueva Zelanda y Australia (3 semanas).

**Ubicación:** `https://www.pablogodoy.com.ar/viaje.php`
**Instalable:** Progressive Web App (PWA) - instalabre como app en teléfono (Android e iOS)

#### Integrantes del Grupo (Los 4 Magníficos)

| Persona | Edad | Nacimiento | Signo |
|---------|------|-----------|-------|
| **JORGE MARTIN ZUTTION** | 55 años | 18/11/1970 | ♏ Escorpio |
| **PABLO ANDRÉS GODOY** | 54 años | 04/02/1972 | ♒ Acuario |
| **SEBASTIAN LLENSA** | 49 años | 15/12/1976 | ♐ Sagitario |
| **RICARDO ANDRÉS RIVAS** | 49 años | 07/03/1977 | ♓ Piscis |

#### Vuelos Confirmados (4 Vuelos)

1. **Buenos Aires → Auckland** - 29/30 DIC 2026 (14h 40min) - China Eastern Airlines MU 0746
2. **Auckland → Sydney** - 30 DIC 2026 (1h 45min) - Air New Zealand NZ111
3. **Sydney → Christchurch** - 03 ENE 2027 (3h 5min) - Qantas Airways QF 191
4. **Auckland → Buenos Aires** - 14/15 ENE 2027 (12h) - China Eastern Airlines MU 0745

#### Itinerario (17 días)

- **Hospedaje Sydney:** 3 noches (30 dic - 3 ene)
- **Motorhome:** 8-9 días (Christchurch → Auckland)
  - Paradas: Christchurch → Hokitika → Westport → Abel Tasman → Taupo → Rotorua → Auckland
- **Hospedaje Auckland:** 1-2 noches (12-14 ene)

#### Presupuesto Estimado

- **Total:** ~USD 13,000 por persona (4 pax)
- **Desglose:**
  - Vuelos internacionales: USD 294/pax
  - Vuelos internos: USD 100-120/pax
  - Motorhome (8-9 días): USD 1,000-1,250/pax
  - Hospedajes: USD 360-480/pax
  - Combustible: USD 30-45/pax
  - Campings: USD 87-140/pax
  - Comida & supermercado: USD 120-180/pax
  - Actividades: USD 250-375/pax
  - Otros (eSIM, seguro, misc): USD 200-300/pax

#### Apps Recomendadas

| App | Función | Prioridad |
|-----|---------|-----------|
| **CamperMate** | Campings self-contained | 🔴 Crítica |
| **HolaFly** | eSIM Internet ilimitado | 🔴 Crítica |
| **Revolut** | Tarjeta sin comisiones NZD/AUD | 🔴 Crítica |
| **MetService** | Clima oficial NZ | 🟡 Importante |
| **Gaspy** | Combustible al mejor precio | 🟡 Importante |
| **Pack'n Save** | Supermercado más barato | 🟡 Importante |
| **Tricount** | Dividir cuentas compartidas | 🟢 Opcional |
| **Roady** | Atracciones en ruta | 🟢 Opcional |
| **Civitatis** | Tours y actividades | 🟢 Opcional |

#### Destinos Clave

**Isla Norte:**
- Hobbiton (Matamata)
- Waitomo (Cuevas)
- Rotorua (Geotermia)
- Tongariro (Trekking)

**Isla Sur:**
- Milford Sound (Fiordos)
- Lago Pukaki (Glaciar)
- Mount Cook (Pico)
- Queenstown (Aventura)

#### Documentación Requerida

- ✅ DNI argentino vigente
- ✅ Pasaporte vigente
- ✅ Licencia de conducir (original)
- ✅ Permiso Internacional ACA (librito gris)
- ✅ Seguro de viaje internacional

---

### 5. SIGEDO - Sistema de Gestión Electrónica de Documentos

**Objetivo:** Gestionar documentos de auditoría para el Tribunal de Cuentas de Entre Ríos (TCER).

**Ubicación:** `/home/pablog/git/sigedo`

#### ¿Qué es SIGEDO?

**SIGEDO = Sistema Integrado de Gestión Electrónica de Documentos**

Sistema web que procesa documentos fiscales (rendiciones, subsidios, multas) a través de múltiples etapas:
- Recepción y clasificación
- Auditoría técnica
- Revisión de superiores
- Firma digital
- Certificación y archivo

Mantiene un registro completo de auditoría: quién hizo qué, cuándo y por qué.

#### Flujo Principal

```
Organismo envía documento → SIGEDO clasifica → Se asigna auditor
    ↓
Auditor revisa (múltiples etapas) → Se firma digitalmente
    ↓
Se genera PDF oficial → Se archiva permanentemente
```

#### Tecnología

| Componente | Tecnología |
|-----------|-----------|
| **Lenguaje** | Java 11 |
| **Framework Web** | Wicket 6.13.0 |
| **Base de Datos** | PostgreSQL |
| **ORM** | Hibernate 3.5.6 |
| **Inyección de Dependencias** | Spring 5.3.39 |
| **APIs REST** | Apache CXF 3.0.16 |
| **Reportes** | JasperReports 6.20.6 |
| **Build** | Maven |

#### Arquitectura de Módulos

```
sigedo/
├── common/         ← Utilidades compartidas
├── model/          ← Entidades (Documento, Usuario, etc.)
├── dao-api/        ← Interfaces de acceso a datos
├── dao-impl/       ← Implementación (SQL/Hibernate)
├── service-api/    ← Interfaces de lógica de negocio
├── service-impl/   ← Implementación de servicios
└── web/            ← Interfaz web (Wicket) + APIs REST
```

#### Tipos de Documentos

| Tipo | Descripción |
|------|------------|
| RENDICIÓN_ORGANISMO | Cuenta anual de ministerios/secretarías |
| RENDICIÓN_SUBSIDIO | Informe de gastos de subsidios |
| EXPEDIENTE | Carpeta de auditoría (generada internamente) |
| MULTA | Penalizaciones por incumplimiento |
| OMISIÓN_RENDITIVA | Aviso de no entrega de cuenta |
| OFICIO/CÉDULA | Comunicaciones oficiales |
| LEGAJO_RENDITIVO | Archivo contable completo |

#### Estados Típicos de un Documento

```
INGRESADO → CARATULADO → EN_AUDITORÍA → PENDIENTE_INFORME
    ↓
EN_REVISIÓN_SUPERIOR → PENDIENTE_CERTIFICACIÓN → CERTIFICADO → ARCHIVADO
```

**Nota:** El sistema permite 100+ estados configurables sin tocar código.

#### Actores

| Rol | Responsabilidad |
|-----|-----------------|
| **Auditor** | Revisa documentos, genera informes |
| **Contador** | Verifica aspecto financiero |
| **Abogado** | Revisa conformidad legal |
| **Jefe** | Aprueba trabajos, firma digitalmente |
| **Admin** | Configura sistema, permisos, estados |
| **Usuario Externo** | Envía documentos, recibe resultados |

#### Levantar el Servidor (Desarrollo)

**Requisitos:**
- Java 11+
- Maven
- PostgreSQL corriendo

**Pasos:**

1. **Compilar (primera vez o después de cambios)**
   ```bash
   cd /home/pablog/git/sigedo
   mvn clean install -DskipTests
   ```

2. **Levantar servidor Jetty**
   ```bash
   mvn jetty:run -pl web
   ```

3. **Acceder en navegador**
   ```
   http://localhost:8090/tcer/
   ```

4. **Detener**
   ```
   Ctrl+C en terminal
   ```

#### Cambios Comunes

**Agregar un nuevo campo a un documento:**
1. Editar modelo → `model/src/main/java/.../Documento.java`
2. Editar DAO si necesita búsquedas → `dao-impl/.../DocumentoDAO.java`
3. Editar página web → `web/src/main/java/.../PaginaControlDocumento.java`
4. Compilar: `mvn clean install -DskipTests`

**Cambiar un mensaje en pantalla:**
- Editar página → `web/src/main/java/.../PaginaXXX.java`
- Buscar texto y cambiar
- Compilar

**Agregar validación:**
- Editar servicio → `service-impl/.../ServicioXXX.java`
- Agregar lógica de validación
- Compilar

#### Patrones Importantes

**ProcesoLento (Procesamiento Diferido):**
- Problema: Monitorear estado cada 100ms = 1000 requests/hora (lento)
- Solución: Esperar 120 segundos, hacer 1 request
- Clase: `ar.gob.tcer.web.common.ProcesoLento`
- Uso: `ejecutarProcesoLentoEnXDecimasDeSegundos(300)` = esperar 30 seg

**Estados Configurables:**
- No hardcodear estados válidos
- Usar `ConfiguracionClasificacionEstadoDocumento`
- Admin configura transiciones sin tocar código

**Discriminador (Polimorfismo):**
- Todos los tipos en tabla DOCUMENTO con columna TIPO
- Subclases Java (Expediente, RendicionOrganismo, etc.)
- Una tabla, múltiples tipos

#### Documentación

- **Guía Principiante:** `SIGEDO_GUIA_PRINCIPIANTE.md` (leer aquí si no sabes Java)
- **Glosario Técnico:** `GLOSARIO_TECNICO.md` (qué significa cada palabra rara)
- **Guía de Usuario:** `GUIA_SIGEDO_PARA_USUARIOS.md` (cómo funciona desde perspectiva usuario)
- **Mapa Rápido:** `MAPA_RAPIDO_CAMBIOS.md` (dónde editar para cada cambio)

#### Compilación y Troubleshooting

**Error: "Puerto 8090 en uso"**
```bash
# Encuentra qué proceso usa el puerto
netstat -tulpn | grep 8090

# O cambia puerto en web/pom.xml → <port>XXXX</port>
```

**Error: "BUILD FAILURE"**
- Verificar Java 11: `java -version`
- Limpiar: `mvn clean install -DskipTests`

**La app levanta pero no funciona:**
- Revisar logs en terminal donde corre Jetty
- Revisar `jetty.log` en raíz del proyecto

---

### 6. Crédito Hipotecario - Banco Entre Ríos (UVAs)

**Objetivo:** Gestionar y dar seguimiento a las 120 cuotas del crédito hipotecario en UVAs.

**Ubicación:** `/cifra/hipoteca.php` (integrado en Cifra con autenticación)  
**BD:** `gastos_personales.cuotas_hipoteca` (MariaDB)

#### Información del Crédito

| Dato | Valor |
|------|-------|
| **Banco** | Banco Entre Ríos |
| **Acreedor** | GODOY PABLO ANDRES |
| **Tipo** | Hipotecario |
| **Moneda** | UVAs (Unidades de Valor Adquisitivo) |
| **Total de cuotas** | 120 |
| **Período** | Marzo 2026 - Febrero 2036 |
| **Cuotas pagadas** | 4 (mar-jun 2026) |
| **Cuotas impagas** | 116 (jul 2026 - feb 2036) |
| **Monto total pagado** | 1,327.21 UVAs |
| **Monto pendiente** | 60,254.91 UVAs |

#### Acceso

**URL:** `https://www.pablogodoy.com.ar/cifra/hipoteca.php`

**Requisitos:**
- ✅ Loguearse en Cifra primero
- ✅ Menú de Cifra → "Cuotas Hipotecarias"

**Protección:**
- ✅ Autenticación vía sesión de Cifra (`require_auth_or_redirect()`)
- ✅ API protegida con `require_auth_or_401()`

#### Estructura de Base de Datos

**Tabla:** `cuotas_hipoteca`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `nro_cuota` | INT | Número 1-120 |
| `estado` | ENUM | PAGADA / IMPAGA |
| `fecha_vencimiento` | DATE | Fecha vto cuota |
| `capital` | DECIMAL | Capital en UVAs |
| `interes` | DECIMAL | Interés en UVAs |
| `total_uva` | DECIMAL | Total en UVAs |
| `valor_uva` | DECIMAL | Valor de 1 UVA en $ARS |
| `total_pesos` | DECIMAL | Calculado: total_uva × valor_uva |
| `fecha_pago` | DATE | Cuándo se pagó (auto) |

#### Características

✅ **Checkbox para cambiar estado** PAGADA ↔ IMPAGA  
✅ **Editar valor_uva por cuota** (clic en la celda)  
✅ **Total en pesos calculado automáticamente**  
✅ **Estadísticas en tiempo real**  
✅ **Historial de fecha_pago**  

#### Grilla de Cuotas (hipoteca.php)

**Columnas (tabla desktop, todas visibles con títulos centrados):**
Nro | Estado | Fecha Vto | Capital (UVA) | Interés (UVA) | Total (UVA) | Valor UVA ($) | **Arca ($)** | Cuota Bco ($) | Fecha Pago

- **Arca ($):** campo calculado en el frontend (no está en la BD) = `interés (UVA) × valor_uva`. Se muestra en verde (`.arca-valor`), con 2 decimales.
- **Cuota Bco ($):** antes se llamaba "Total ($)" — es `total_uva × valor_uva`.
- Las tarjetas mobile replican las mismas columnas (incluye Arca y Fecha Pago) para mantener paridad con la tabla.

#### Valores de UVA (Actualización Mensual)

**Conocidos:**
- Mar-Jun 2026: $1,025.50 (4 cuotas pagadas)
- Jul 2026: $2,020.51 (actualizado)
- Ago 2026+: $0.00 (pendiente)

**Procedimiento mensual:**
1. Busca valor en: https://ikiwi.net.ar/calculadoras/uva-a-pesos/
2. Abre hipoteca.php
3. Haz clic en "Valor UVA ($)" de esa cuota
4. Ingresa valor (ej: `2050.00`)
5. Presiona Enter → se calcula automáticamente

**Archivos de referencia:**
- `actualizar_valor_uva_por_mes.sql` - Script para actualizar masivamente
- `ACTUALIZAR_VALORES_UVA.md` - Guía completa

#### Archivos del Proyecto

```
cifra/
├── hipoteca.php             ← Interfaz (protegida con login)
└── api/
    └── hipoteca_api.php     ← API REST (autenticada)

cifra/scripts/hipoteca/      ← bloqueado a la web (.htaccess)
├── datos_cuotas.json        ← 120 cuotas (backup)
├── 00_alterar_tabla.sql     ← Estructura de BD
├── 01_create_table.sql
├── 02_insert_cuotas.sql     ← Datos iniciales
├── actualizar_valor_uva_por_mes.sql ← Script actualización
└── ACTUALIZAR_VALORES_UVA.md ← Guía mantenimiento
```

La carpeta raíz `hipoteca/` (versión vieja sin login) fue eliminada el 2026-10-14.

#### Seguridad del sitio (2026-10-14)

- **Login único** (`cifra/config/auth_check.php`), reutilizable por el futuro `/panel/`. Credenciales en `cifra/config/auth.local.php` (**fuera de Git**; plantilla: `auth.local.example.php`).
- Límite: 5 intentos fallidos / 15 min por IP · `session_regenerate_id` al loguear · cookies `Secure` · `login.php?next=/ruta` vuelve a la página pedida.
- `.htaccess` raíz: bloquea `*.md`, `*.sql`, `*.sh`, `*.py`, dotfiles, `cifra/{config,scripts,tests,lib,docs}`, `cifra/test_*` + headers de seguridad (HSTS, nosniff, frame-options).
- APIs: errores de BD se loguean con `error_log`, al cliente solo va un mensaje genérico (`cifra_error_publico()`).
- **Panel privado** (`/panel/`): `panel/index.php` + ayudamemorias en `panel/notas/*.php`; cada uno empieza con `require __DIR__ . '/../_auth.php'`. Las URLs viejas `/*.html` redirigen (301) a `/panel/notas/*.php`.
- **Viaje** (`/viaje.php`, no se mueve para no romper la PWA del grupo): `viaje_require_acceso()` pide la clave del grupo (`VIAJE_PASS_HASH` en `auth.local.php`, cookie `viaje_acceso` 180 días). Logueado en Cifra entra directo. Pantalla: `cifra/config/viaje_acceso.php`.
- **Landing** (`index.php`): "Pablo Godoy · Lic. en Sistemas de Información" (Mat. Coprocier Nº 495). Secciones: hero, cinta, servicios, cómo trabajo, sobre mí (fondo gris), franja webmaster, contacto. La sección "Proyectos / Algunos trabajos realizados" se quitó el 2026-10-24 hasta definir nuevo contenido (las versiones anteriores con turnitoAhora, Cifra y HSC están en el historial de Git). WhatsApp `wa.me/5493434504750`. Foto: `img/pablo-godoy.{webp,jpg}`. Link "Acceso" en el footer → `/cifra/login.php?next=/panel/`.
- **Diseño landing**: hero oscuro (#0b1224, grilla sutil + brillo azul) con navegación translúcida oscura. Tipografías: Inter (texto), Inter Tight (títulos), Instrument Serif itálica (`.serif`, palabra destacada en cada título). Tipografía sutil: títulos peso 500 (h1 3.5rem máx., en 2 renglones; columnas del hero 1.2fr/.8fr, h2 2.25rem), cifras y números de pasos en 300, negritas 600 (500 dentro de bajadas), etiquetas en mayúsculas 500 con tracking .16–.18em. No usar 700/800 salvo en logos. Hero: logo `img/logo/logo-hero.{webp,jpg}` (600×600, fondo azul noche propio) recortado en círculo (`.hero-logo`: órbita punteada con dos puntos de luz `.hero-orbita`, halo que late, flota, destello y se inclina en 3D con el mouse; sin chips). **Efectos** (CSS + JS vanilla, todos se apagan con `prefers-reduced-motion`): red de nodos en canvas detrás del hero (`#heroRed`, se pausa fuera de pantalla), entrada escalonada (`.entrar` + `--d`), cifras que cuentan (`data-contar`/`data-pre`/`data-suf`; sutiles). **Hero en el celular (≤860px)**: `.hero-texto` y el `h1` usan `display: contents` y se reordenan con `order`: pill → logo (máx. 320px) → `.hero-titular` + bajada + botones → cifras; h1 en `clamp(1.7rem, 8.2vw, 2.05rem)` (así la 1ª línea es siempre "Tecnología a medida para"); titular y bajada justificados (`text-align: justify` + `hyphens: auto`). El H1 sigue conteniendo el nombre (SEO), partido en `.hero-nombre` + `.hero-titular`; `.hero-nombre` se oculta a la vista en todas las resoluciones (sigue en el H1) porque la barra ya dice "Pablo Andrés Godoy"; hero con padding-top 112px (92px en el celular), destello en `.btn-luz`, cinta de servicios que se desplaza (`.cinta`, array `$cinta` en PHP), tarjetas de servicios con luz que sigue al mouse, línea de progreso en "Cómo trabajo" (`.trazo`), franja `.cta-banda` "Webmaster profesional" antes de Contacto, barra de progreso de lectura (`#navProgreso`) y pulso en el botón de WhatsApp. Los hovers de `.servicio` van después de `.revelar` en el CSS para poder pisar su `transform`. Retrato recortado `img/retrato.{webp,png}` (fondo transparente, recortado en círculo abajo; el círculo de CSS usa centro 50%/56% y radio 42%: si se cambia la imagen, mantener esa geometría) va chico en "Sobre mí" (`.sobre-retrato`, 150px, 130px en el celular), dentro de una columna azul `.sobre-panel` (degradé azul noche + grilla, con nombre, título y "Paraná, Entre Ríos") y con el círculo semitransparente para que no resalte. Pablo hizo www.hsc.com.ar (dato para cuando vuelva la sección de trabajos). "Sobre mí": sin título, arranca con "Soy Pablo A. Godoy…" (`.sobre-intro`); usa el retrato recortado dentro del panel azul; `img/pablo-godoy.jpg` se mantiene porque lo usa el JSON-LD; en el texto HSC es un link de texto ("HSC S.A."); su logo está en `img/logo/hsc.png` (sin usar por ahora).
- **Formulario** (`contacto.php`): `mail()` a godoypablo@gmail.com desde `web@pablogodoy.com.ar`; anti-spam: campo trampa `sitio_web`, mínimo 3 s, 3 envíos/hora por IP. Responde JSON si `X-Requested-With: fetch`.
- **Marca** (`img/logo/`): isotipo `isotipo.svg` (bloque azul noche #1e3a8a→#0f172a + "PG" Inter 800 blanco + cursor celeste #38bdf8; Pablo lo prefiere a una versión simplificada del logo del hero que se probó el 2026-10-24), `isologotipo(-blanco).svg` (isotipo + "Pablo Godoy" + "LIC. EN SISTEMAS DE INFORMACIÓN"), `logotipo(-blanco).svg` ("pablogodoy_" con cursor que parpadea). Letras convertidas a trazos (no dependen de fuentes). También `favicon.svg`, `apple-touch-icon.png`, `icon-512.png`, `img/og-image.jpg` (1200×630 para WhatsApp/redes).
- SEO: meta + Open Graph + JSON-LD `ProfessionalService`, `robots.txt` (excluye /cifra/, /panel/, /viaje.php), `sitemap.xml`. Title empieza con "Pablo Godoy |"; H1 lleva `.hero-nombre` ("Pablo Andrés Godoy") antes del titular; "Sobre mí" es un `<h2 class="eyebrow">Sobre Pablo Godoy</h2>`. Meta descripción ≤ ~155 caracteres con el nombre adelante.
- **LinkedIn**: `https://www.linkedin.com/in/godoypablo1972/` en Contacto (canal), footer y JSON-LD `sameAs` (empresa y persona), con `rel="noopener me"`.
- **Nunca** abrir por navegador/curl scripts de `cifra/scripts/`: se ejecutan (ej. `regenerar_cuotas_cierre_id.php` borra y regenera cuotas). Correrlos solo por CLI.

#### Flujo Típico de Uso

1. **Loguearse en Cifra** → `https://www.pablogodoy.com.ar/cifra/`
2. **Menú** → "Cuotas Hipotecarias"
3. **Ver tabla** con 120 cuotas
4. **Cada mes:**
   - Pagar cuota en el banco
   - Marcar como PAGADA (checkbox)
   - Actualizar Valor UVA (clic en celda)
   - Total en $ se calcula automáticamente

#### Importancia

- **UVAs:** Valor cambia diariamente según BCRA/inflación
- **Tracking:** Crítico controlar pagos (vencimiento ~27 cada mes)
- **Transparencia:** Ver deuda real en pesos (no solo UVAs)

---

### 7. Gym - Rutina de Entrenamientos

**Objetivo:** Rutina de gimnasia profesional con 3 entrenamientos semanales (Lunes, Miércoles, Viernes) con imágenes de ejercicios y guía de recomendaciones.

**Ubicación:** `/home/pablog/git/PabloGodoy/gym.html`  
**Acceso:** `https://www.pablogodoy.com.ar/gym.html`

#### Características Principales

✅ **Diseño Responsivo Profesional**
- Header limpio con fondo blanco y borde rojo
- Tabs interactivos en móvil (sin scroll innecesario)
- Grid adaptativo para desktop (3 días juntos)

✅ **3 Días de Entrenamiento**
- **Lunes:** Pecho & Espalda (7 ejercicios)
- **Miércoles:** Piernas & Cuadríceps (6 ejercicios)
- **Viernes:** Hombros & Brazos (7 ejercicios)

✅ **19 Imágenes de Ejercicios**
- Cada ejercicio con foto profesional recortada
- Fondo blanco puro para mejor visualización
- Dimensiones optimizadas: 220px altura, object-fit: contain

✅ **Cards de Ejercicios**
- Nombre del ejercicio
- Equipamiento necesario (badges con colores)
- Descripción breve de cada movimiento
- Efecto hover suave y elegante

✅ **Sección de Recomendaciones**
- Calentar antes de entrenar
- Mantener buena hidratación
- Alimentación balanceada
- Descanso y recuperación
- Progresión gradual
- Forma correcta prioritaria

#### Estructura de Archivos

```
/home/pablog/git/PabloGodoy/

gym.html                      ← Página principal (profesional)
index.php                     ← Panel principal (actualizado con tarjeta Gym)

gym-images/                   ← 19 imágenes de ejercicios
├── press_banca_con_barra.png
├── remo_con_barra.png
├── press_inclinado_con_mancuernas.png
├── remo_con_mancuerna.png
├── aperturas_en_maquina.png
├── jalon_al_pecho_con_agarre_supino.png
├── face_pulls.png
├── sentadilla_con_barra.png
├── peso_muerto_rumano.png
├── prensa_inclinada.png
├── curl_femoral_acostado.png
├── extension_de_cuadriceps.png
├── elevacion_de_talones_en_maquina.png
├── press_militar_con_barra.png
├── elevaciones_laterales_con_mancuernas.png
├── curl_con_barra.png
├── fondos_en_maquina.png
├── curl_martillo_con_mancuernas.png
└── extension_de_triceps_en_polea.png
```

#### Tecnología y Estilos

| Aspecto | Detalle |
|---------|---------|
| **Framework** | Bootstrap 5.3.0 (responsividad) |
| **Tipografía** | Inter (Google Fonts) |
| **Colores** | Rojo vibrante (#ff6b6b), blanco, grises |
| **Grid** | CSS Grid adaptativo (300px mín. en desktop, 1 columna en móvil) |
| **Animaciones** | Smooth transitions, hover effects, slide-in animations |
| **Tabs en Móvil** | JavaScript vanilla para cambio de días sin scroll |

#### Estilo Visual

**Header:**
- Fondo blanco con borde rojo inferior
- Título grande (3.2rem) sin emoji
- Subtitle en mayúsculas: "ENTRENAMIENTO COMPLETO"

**Cards de Ejercicios:**
- Borde superior de 3px con gradiente rojo
- Sombra sutil (0 2px 8px rgba)
- Hover: elevación (-8px) con sombra aumentada
- Imagen 220px con fondo blanco
- Contenido con padding generoso (1.75rem)

**Responsividad:**
- **Desktop:** Muestra 3 días simultáneamente
- **Móvil (<768px):** 
  - Tabs para cambiar entre días
  - Solo muestra un día a la vez
  - 1 columna de ejercicios
  - Sin scroll innecesario

#### Cambios Recientes (2026-08-19)

**Versión 1.0 - Release Inicial:**
- ✅ Creación de gym.html con diseño profesional
- ✅ Extracción y procesamiento de 19 imágenes
- ✅ Integración en index.php (nueva tarjeta "Gym")
- ✅ Optimización de imágenes: fondo blanco, objeto-fit: contain
- ✅ Mejora de responsividad: tabs interactivos para móvil
- ✅ Rediseño visual: header limpio, colores profesionales

**Última actualización:** 2026-08-19 / Agosto

#### Uso en el Servidor

Simplemente ejecuta en el hosting:

```bash
git pull
```

Los cambios incluyen:
- `gym.html` (página completa)
- `index.php` (actualizado con tarjeta)
- `gym-images/` (carpeta con 19 imágenes)

---

### 8. Método de Desarrollo de Agentes de IA (en PHP)

**Objetivo:** Toda funcionalidad con IA del proyecto (Cifra, panel, landing) se construye siguiendo esta hoja de ruta de 7 capas, **en PHP en lugar de Python**, sobre el hosting actual (PHP + Apache, MySQL 5.7, deploy por `git pull`/FTP). Sin Docker, sin servicios Python.

| Capa | Hoja de ruta original | Cómo se hace en este proyecto |
|------|----------------------|-------------------------------|
| **1. Fundamentos** | Python, JavaScript, Git | **PHP 8** (backend y agentes), JavaScript vanilla (frontend), Git (flujo de la sección 2) |
| **2. LLMs** | GPT, Claude, Gemini, Llama | **Claude**, con el SDK oficial `anthropic-ai/sdk` (`composer require anthropic-ai/sdk`). Modelo por defecto `claude-opus-5-5`; otro modelo (ej. Haiku para tareas masivas y simples) solo si Pablo lo decide. Otros proveedores, solo si se piden explícitamente |
| **3. Frameworks de IA** | LangChain, LangGraph, LlamaIndex | No hay equivalentes serios en PHP y no hacen falta: **SDK oficial** → tool runner (`BetaRunnableTool` + `toolRunner()`) o loop manual. Los flujos de varios pasos se escriben como clases PHP simples |
| **4. Habilidades de agentes** | Prompts, herramientas, RAG, memoria, multiagente | **Prompts** en archivos versionados (`prompts/*.md`), system prompt fijo y cacheado. **Herramientas** = funciones PHP que reutilizan la lógica existente (ej. `cobrarIngreso()`), con esquemas `strict`. **RAG** = consultar MySQL y pasar el contexto. **Memoria** = tablas MySQL. **Multiagente** solo si un agente simple no alcanza |
| **5. Bases de datos** | Pinecone, ChromaDB, FAISS, PostgreSQL | **MySQL 5.7** (no tiene tipo vector). Volumen chico → `FULLTEXT` de MySQL o embeddings guardados en JSON con similitud coseno en PHP. Una base vectorial externa solo si el volumen lo exige. Ojo: Anthropic no ofrece embeddings, requieren otro proveedor |
| **6. APIs** | REST, GraphQL, MCP | **REST en PHP** como las de `cifra/api/` (JSON, `require_auth_or_401()`, errores con `cifra_error_publico()`). GraphQL no se usa. MCP solo si hace falta integrar herramientas externas |
| **7. Despliegue** | Docker, FastAPI, Vercel, AWS | **Hosting actual**: archivos PHP + `git pull`. `vendor/` de Composer se sube junto al código si el servidor no tiene Composer. API key en `config/*.local.php` **fuera de Git** (como `auth.local.php`). Tareas largas → cron, no en el request web |

**Reglas fijas:**
- La API key **nunca** llega al navegador: el frontend llama a un endpoint PHP propio y ese endpoint llama a Claude.
- Herramientas que mueven plata o borran datos (saldos, cobros, pagos) **piden confirmación** del usuario antes de ejecutarse.
- Empezar por lo más simple: una llamada → flujo con herramientas → agente. Solo se sube de nivel si el anterior no alcanza.
- Toda respuesta del modelo se valida antes de usarla (`stop_reason`, esquema JSON); nunca se ejecuta texto libre.
- **Hosting: PHP 8.2, sin Composer** (verificado 2026-10-14). Composer se corre **solo en local**; la carpeta `vendor/` resultante se versiona en Git (el servidor la recibe con `git pull`) y se bloquea el acceso web con `.htaccess` (`Require all denied`). `composer.json` y `composer.lock` también van a Git. Al instalar paquetes, fijar `"config": {"platform": {"php": "8.2"}}` para que las dependencias sean compatibles con el servidor.

---

---

## 🎯 Resumen Ejecutivo

Este documento centraliza:
1. **Productividad con Claude:** Estrategias de bajo consumo de tokens
2. **Versionado de Código:** Flujo simplificado de GitHub
3. **Línea de Comandos:** Referencia rápida de comandos Linux útiles
4. **Viaje Planificado:** Toda la información del viaje a NZ-AUS 2027
5. **SIGEDO:** Sistema de gestión electrónica de documentos para TCER
6. **Crédito Hipotecario:** Gestor de 120 cuotas en UVAs (integrado en Cifra)
7. **Gym:** Rutina profesional de entrenamientos (Lunes, Miércoles, Viernes)
8. **Agentes de IA:** Método de desarrollo en PHP (7 capas, SDK oficial de Claude, hosting actual)

---

## 🏗️ Cambios Recientes (2026-09-22)

### Crédito Hipotecario - Mejoras en Grilla ✅ NUEVO

**Implementación Completa (`cifra/hipoteca.php`):**
- ✅ Nueva columna calculada **Arca ($)** = interés (UVA) × valor_uva, en verde, 2 decimales
- ✅ Columna "Total ($)" renombrada a **Cuota Bco ($)**
- ✅ Títulos de columnas centrados en la tabla desktop
- ✅ Todas las columnas visibles sin ocultamiento responsivo (Capital, Interés, Fecha Pago ya no se ocultan en desktop)
- ✅ Tarjetas mobile actualizadas con las mismas columnas para mantener paridad

**Última actualización:** 2026-09-22 / Septiembre

---

## 🏗️ Cambios Recientes (2026-08-19)

### Gym - Rutina de Entrenamientos ✅ NUEVO

**Implementación Completa:**
- ✅ Página `gym.html` con diseño profesional
- ✅ 19 imágenes de ejercicios (recortadas y optimizadas)
- ✅ Tarjeta "Gym" agregada en `index.php`
- ✅ Responsividad completa (desktop + móvil con tabs)

**Características:**
- ✅ 3 días de entrenamiento (Lunes, Miércoles, Viernes)
- ✅ 20 ejercicios totales con imágenes profesionales
- ✅ Tabs interactivos en móvil (sin scroll innecesario)
- ✅ Cards elegantes con hover effects
- ✅ Sección de recomendaciones con 6 consejos
- ✅ Diseño moderno: header blanco, bordes rojos, tipografía Inter

**Estilos Implementados:**
- ✅ CSS Grid responsivo (300px mín. desktop, 1 col móvil)
- ✅ Animaciones suaves (slideInUp, hover transforms)
- ✅ Paleta profesional: rojo (#ff6b6b), blanco, grises
- ✅ Badges de equipamiento con colores
- ✅ JavaScript vanilla para tabs sin dependencias

**Última actualización:** 2026-08-19 / Agosto
