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

## 🎯 Resumen Ejecutivo

Este documento centraliza:
1. **Productividad con Claude:** Estrategias de bajo consumo de tokens
2. **Versionado de Código:** Flujo simplificado de GitHub
3. **Línea de Comandos:** Referencia rápida de comandos Linux útiles
4. **Viaje Planificado:** Toda la información del viaje a NZ-AUS 2027

**Última actualización:** 2026-06-10
