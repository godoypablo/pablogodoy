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

## 🎯 Resumen Ejecutivo

Este documento centraliza:
1. **Productividad con Claude:** Estrategias de bajo consumo de tokens
2. **Versionado de Código:** Flujo simplificado de GitHub
3. **Línea de Comandos:** Referencia rápida de comandos Linux útiles
4. **Viaje Planificado:** Toda la información del viaje a NZ-AUS 2027
5. **SIGEDO:** Sistema de gestión electrónica de documentos para TCER

**Última actualización:** 2026-07-03
