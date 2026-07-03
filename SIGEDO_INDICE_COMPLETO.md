# 📚 SIGEDO: ÍNDICE COMPLETO DE DOCUMENTACIÓN

> Guía de navegación por toda la documentación de SIGEDO

---

## 🎯 ¿POR DÓNDE EMPIEZO?

### Eres principiante (no sabes Java/Hibernate)

1. **Comienza aquí:** [`SIGEDO_GUIA_PRINCIPIANTE.md`](SIGEDO_GUIA_PRINCIPIANTE.md)
   - Qué es SIGEDO en términos simples
   - Flujo principal
   - Tipos de documentos
   - Estados de máquina
   - Actores y roles
   - Tecnologías básicas

2. **Luego entender:** [`SIGEDO_ARQUITECTURA_PROFUNDA.md`](SIGEDO_ARQUITECTURA_PROFUNDA.md)
   - Cómo está organizado el código
   - Capas (web, servicios, persistencia)
   - Módulos (common, model, dao, service, web)
   - Relaciones entre clases
   - Flujo de una petición

3. **Casos reales:** [`SIGEDO_FLUJOS_CASOS_USO.md`](SIGEDO_FLUJOS_CASOS_USO.md)
   - Flujo 1: Ingreso de documento (paso a paso)
   - Flujo 2: Auditoría completa (qué ocurre cada día)
   - Flujo 3: Cambio de estado (validaciones)
   - Flujo 4: Firma digital (ProcesoLento)
   - Flujo 5: Reportes
   - Flujo 6: Permisos y roles

4. **Hacer cambios:** [`SIGEDO_DESARROLLO_PRACTICO.md`](SIGEDO_DESARROLLO_PRACTICO.md)
   - Caso práctico completo (agregar campo)
   - Paso a paso: qué archivo editar
   - Compilación y testing
   - Debugging
   - Checklist de cambios

### Eres programador con experiencia

1. **Arquitectura:** [`SIGEDO_ARQUITECTURA_PROFUNDA.md`](SIGEDO_ARQUITECTURA_PROFUNDA.md)
   - Diseño de capas
   - Patrones (DAOs, Services, DTOs)
   - Relaciones E-R
   - Flujo de peticiones

2. **BD:** [`SIGEDO_BASE_DATOS.md`](SIGEDO_BASE_DATOS.md)
   - Schema completo (DDL)
   - Índices y optimización
   - Consultas SQL + Hibernate
   - Constraints y validación
   - Monitoreo de performance

3. **APIs:** [`SIGEDO_APIS_REST.md`](SIGEDO_APIS_REST.md)
   - Endpoints REST (GET, POST, PUT, DELETE)
   - Autenticación JWT
   - Códigos HTTP
   - Ejemplos con curl, JavaScript, Python

4. **Desarrollo:** [`SIGEDO_DESARROLLO_PRACTICO.md`](SIGEDO_DESARROLLO_PRACTICO.md)
   - Caso práctico paso a paso
   - Ejemplos de código real
   - Testing local
   - Debugging en IDE

---

## 📖 DOCUMENTACIÓN POR TEMA

### Conceptos Fundamentales

| Documento | Tema | Para quién |
|-----------|------|-----------|
| `SIGEDO_GUIA_PRINCIPIANTE.md` | Qué es SIGEDO en términos simples | Principiantes |
| `GLOSARIO_TECNICO.md` | Explicación de palabras técnicas raras | Todos |
| `GUIA_SIGEDO_PARA_USUARIOS.md` | Cómo funciona desde perspectiva usuario | Usuarios |

### Arquitectura y Diseño

| Documento | Tema | Contenido |
|-----------|------|----------|
| `SIGEDO_ARQUITECTURA_PROFUNDA.md` | Estructura general | Capas, módulos, relaciones |
| `SIGEDO_BASE_DATOS.md` | Diseño de BD | Tablas, índices, SQL |
| `SIGEDO_APIS_REST.md` | Interfaz externa | Endpoints, autenticación |

### Flujos y Casos de Uso

| Documento | Flujos |
|-----------|--------|
| `SIGEDO_FLUJOS_CASOS_USO.md` | Ingreso, Auditoría, Estados, Firma, Reportes, Permisos |

### Desarrollo Práctico

| Documento | Qué aprendes |
|-----------|-------------|
| `SIGEDO_DESARROLLO_PRACTICO.md` | Cómo hacer cambios reales, paso a paso |

---

## 🗂️ DOCUMENTACIÓN EN SIGEDO/

Si entras al repositorio `/home/pablog/git/sigedo`, encontrarás:

| Archivo | Propósito |
|---------|-----------|
| `CLAUDE.md` | Instrucciones para Claude Code (este repo) |
| `README.md` | Descripción general del proyecto |
| `GLOSARIO_TECNICO.md` | Palabras técnicas explicadas |
| `GUIA_SIGEDO_PARA_USUARIOS.md` | Manual para usuarios |
| `MAPA_RAPIDO_CAMBIOS.md` | Dónde editar para cada cambio |
| `CONFIGURACION_CLASIFICACION_ESTADOS_DOCUMENTO.md` | Estados configurables |
| `LEVANTAR_SERVIDOR.md` | Cómo iniciar en desarrollo |
| `MIGRACION_JAVA11_CAMBIOS.md` | Cambios para Java 11 |

---

## 🎓 RUTAS DE APRENDIZAJE RECOMENDADAS

### Ruta 1: "Quiero entender qué es SIGEDO" (30 minutos)

1. Leer: [`SIGEDO_GUIA_PRINCIPIANTE.md`](SIGEDO_GUIA_PRINCIPIANTE.md)
2. Secciones: "¿QUÉ ES SIGEDO?" hasta "FLUJO PRINCIPAL"
3. Ver: Diagrama de flujo
4. Entender: 7 tipos de documentos y estados principales

**Resultado:** Sabes qué hace SIGEDO sin necesidad de programar

---

### Ruta 2: "Quiero entender cómo está organizado" (1 hora)

1. Leer: [`SIGEDO_GUIA_PRINCIPIANTE.md`](SIGEDO_GUIA_PRINCIPIANTE.md) completo
2. Leer: [`SIGEDO_ARQUITECTURA_PROFUNDA.md`](SIGEDO_ARQUITECTURA_PROFUNDA.md) secciones:
   - VISIÓN GENERAL (4 capas)
   - ESTRUCTURA DE MÓDULOS (common, model, dao, service, web)
   - FLUJO DE UNA PETICIÓN

**Resultado:** Entiendes por qué el código está organizado así

---

### Ruta 3: "Quiero ver un caso real paso a paso" (2 horas)

1. Leer: [`SIGEDO_FLUJOS_CASOS_USO.md`](SIGEDO_FLUJOS_CASOS_USO.md)
   - FLUJO 1: Ingreso de Documento
   - Sigue cada paso desde UI hasta BD

**Resultado:** Entiendes exactamente qué código se ejecuta en cada acción

---

### Ruta 4: "Quiero aprender a programar cambios en SIGEDO" (4-8 horas)

1. Leer: [`SIGEDO_ARQUITECTURA_PROFUNDA.md`](SIGEDO_ARQUITECTURA_PROFUNDA.md) completo
2. Leer: [`SIGEDO_BASE_DATOS.md`](SIGEDO_BASE_DATOS.md) secciones principales
3. Leer: [`SIGEDO_DESARROLLO_PRACTICO.md`](SIGEDO_DESARROLLO_PRACTICO.md) completo
4. Practicar: Agregar un campo nuevo siguiendo el case práctico
5. Levantar servidor: `mvn jetty:run -pl web`
6. Probar en navegador y API

**Resultado:** Puedes hacer cambios reales en SIGEDO sin ayuda

---

### Ruta 5: "Quiero entender las APIs REST" (2 horas)

1. Leer: [`SIGEDO_APIS_REST.md`](SIGEDO_APIS_REST.md)
   - INTRODUCCIÓN A REST
   - API 1: DOCUMENTOS (GET, POST, PUT, DELETE)
   - EJEMPLOS DE CLIENTES (curl, JavaScript, Python)

2. Practicar:
   ```bash
   curl "http://localhost:8090/tcer/api/documentos"
   ```

**Resultado:** Entiendes cómo integrar sistemas externos con SIGEDO

---

## 🔍 BUSCA UN TEMA ESPECÍFICO

### "No entiendo qué es un DAO"
→ Lee: `SIGEDO_ARQUITECTURA_PROFUNDA.md` sección **"dao-api y dao-impl"**
→ Luego: `GLOSARIO_TECNICO.md` busca "DAO"

### "¿Cómo cambio el estado de un documento?"
→ Lee: `SIGEDO_FLUJOS_CASOS_USO.md` sección **"FLUJO 3: CAMBIO DE ESTADO"**
→ Código: `ServicioEstados.cambiarEstado()`

### "¿Cómo agregar un nuevo tipo de documento?"
→ Lee: `SIGEDO_DESARROLLO_PRACTICO.md` sección **"CASO PRÁCTICO"** (adapta para tu caso)
→ BD: `SIGEDO_BASE_DATOS.md` sección "TABLA DOCUMENTO"

### "¿Cómo crear un nuevo endpoint API?"
→ Lee: `SIGEDO_APIS_REST.md` sección **"API 1: DOCUMENTOS"** (copia el patrón)
→ Referencia: `web/src/main/java/ar/gob/tcer/web/rest/`

### "¿Dónde están los índices de BD?"
→ Lee: `SIGEDO_BASE_DATOS.md` sección **"ÍNDICES (Optimización)"**

### "¿Cuáles son los estados posibles?"
→ Lee: `SIGEDO_GUIA_PRINCIPIANTE.md` sección **"ESTADOS DE UN DOCUMENTO"**
→ Referencia: `model/src/main/java/.../TipoEstadoDocumento.java`

### "¿Qué permisos tiene cada rol?"
→ Lee: `SIGEDO_FLUJOS_CASOS_USO.md` sección **"FLUJO 6: GESTIÓN DE PERMISOS"**
→ BD: `SIGEDO_BASE_DATOS.md` tabla ROL

### "¿Cómo funciona la auditoría?"
→ Lee: `SIGEDO_FLUJOS_CASOS_USO.md` sección **"API 3: AUDITORÍA"**
→ BD: `SIGEDO_BASE_DATOS.md` tabla ACTUACION

---

## 🛠️ HERRAMIENTAS ÚTILES

### Ver documentación sin salir de la terminal

```bash
# Leer en terminal (con less para scroll)
less SIGEDO_GUIA_PRINCIPIANTE.md

# Buscar dentro de un archivo
grep "FLUJO" SIGEDO_FLUJOS_CASOS_USO.md

# Contar líneas
wc -l SIGEDO_*.md
```

### Buscar en todo el código

```bash
# Buscar dónde se usa "cambiarEstado"
grep -r "cambiarEstado" /home/pablog/git/sigedo/

# Buscar en específico en archivos Java
grep -r "cambiarEstado" /home/pablog/git/sigedo/*.java
```

---

## 📊 ESTADÍSTICAS DE DOCUMENTACIÓN

| Documento | Líneas | Tema |
|-----------|--------|------|
| `SIGEDO_GUIA_PRINCIPIANTE.md` | 380 | Introducción |
| `SIGEDO_ARQUITECTURA_PROFUNDA.md` | 650 | Diseño técnico |
| `SIGEDO_FLUJOS_CASOS_USO.md` | 700 | Casos de uso reales |
| `SIGEDO_BASE_DATOS.md` | 550 | Base de datos |
| `SIGEDO_APIS_REST.md` | 600 | Interfaz externa |
| `SIGEDO_DESARROLLO_PRACTICO.md` | 800 | Ejercicios prácticos |
| **Total** | **3,680** | Documentación completa |

---

## ✅ CHECKLIST DE LECTURA

Marca conforme leas cada documento:

### Beginner (Obligatorio)
- [ ] `SIGEDO_GUIA_PRINCIPIANTE.md` - Entender qué es SIGEDO
- [ ] `GLOSARIO_TECNICO.md` - Aprender terminología
- [ ] `SIGEDO_ARQUITECTURA_PROFUNDA.md` - Estructura del código

### Intermediate (Fuerte recomendación)
- [ ] `SIGEDO_FLUJOS_CASOS_USO.md` - Ver casos reales
- [ ] `SIGEDO_BASE_DATOS.md` - Entender persistencia
- [ ] `SIGEDO_DESARROLLO_PRACTICO.md` - Hacer cambios

### Advanced (Si trabajas con APIs/BD)
- [ ] `SIGEDO_APIS_REST.md` - Integración externa
- [ ] `MAPA_RAPIDO_CAMBIOS.md` - Referencia rápida
- [ ] `LEVANTAR_SERVIDOR.md` - Setup completo

---

## 🚀 SIGUIENTE PASO

Elige tu ruta según tu situación:

- **Soy principiante:** Comienza con Ruta 1 (30 min)
- **Quiero entender cómo funciona:** Ruta 2 (1 hora)
- **Quiero ver código ejecutándose:** Ruta 3 (2 horas)
- **Quiero hacer cambios:** Ruta 4 (1 día)
- **Necesito integrar sistemas:** Ruta 5 (2 horas)

Después de completar tu ruta, vuelve a este índice y busca el tema específico que necesites.

---

**Última actualización:** 2026-07-03  
**Total de documentos:** 9  
**Líneas totales:** 3,680+  
**Tiempo de lectura completa:** 8-10 horas
