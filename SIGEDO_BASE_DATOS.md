# 🗄️ SIGEDO: BASE DE DATOS EN PROFUNDIDAD

> Entender exactamente cómo se almacenan los datos, relaciones, índices y optimizaciones

---

## 📊 DIAGRAMA E-R (Entity-Relationship)

```
┌─────────────────────────────────────────────────────────────┐
│                        USUARIO                              │
│                    ┌──────────┐                              │
│                    │ id       │ ← PK                         │
│                    │ nombre   │                              │
│                    │ email    │ (UNIQUE)                     │
│                    │ password │ (hash)                       │
│                    │ rol_id   │ → FK a ROL                   │
│                    │ activo   │                              │
│                    └──────────┘                              │
│                         ▲                                    │
│                         │ 1 : N                              │
│                         │                                    │
│   ┌─────────────────────┼─────────────────────┐            │
│   │                     │                     │            │
│   │                     │                     │            │
┌──────────────┐     ┌────────────┐      ┌─────────────┐    │
│  DOCUMENTO   │     │ ACTUACION  │      │   INFORME   │    │
├──────────────┤     ├────────────┤      │  AUDITOR    │    │
│ id (PK)      │     │ id (PK)    │      ├─────────────┤    │
│ titulo       │     │ doc_id (FK)│─────→│ id (PK)     │    │
│ estado       │     │ usuario_id │      │ doc_id (FK) │    │
│ monto        │     │ accion     │      │ auditor_id  │    │
│ tipo         │     │ fecha      │      │ conclusión  │    │
│ fecha_ingreso│◄────│ hora       │      │ observación │    │
│ auditor_id (FK)    │ ip_address │      │ fecha       │    │
│ institucion_id     └────────────┘      └─────────────┘    │
└──────────────┘                                              │
      │                                                       │
      │ N : 1                                                 │
      │                                                       │
      └──────────────────────────────┐                        │
                                    │                        │
                             ┌──────────────┐                │
                             │  INSTITUCION │                │
                             ├──────────────┤                │
                             │ id (PK)      │                │
                             │ nombre       │                │
                             │ tipo_org     │                │
                             │ cuit         │                │
                             └──────────────┘                │
                                    │                        │
                                    │ 1 : N                  │
                                    │                        │
                             ┌──────────────┐                │
                             │  DEPENDENCIA │                │
                             ├──────────────┤                │
                             │ id (PK)      │                │
                             │ inst_id (FK) │                │
                             │ nombre       │                │
                             │ jefe_id (FK) │                │
                             └──────────────┘                │
                                                             │
     ┌──────────────────────────────────────────────────┐   │
     │                                                  │   │
┌──────────────────┐                          ┌──────────────┐
│ FIRMA_ELECTRONICA│                          │    CARGO     │
├──────────────────┤                          ├──────────────┤
│ id (PK)          │                          │ id (PK)      │
│ doc_id (FK)      │                          │ nombre       │
│ usuario_id (FK)  │                          │ descripción  │
│ fecha_firma      │                          └──────────────┘
│ certificado      │
│ hash_firma       │
│ estado           │
└──────────────────┘
                              └─────────────────┘
```

---

## 🗂️ TABLAS PRINCIPALES

### 1. TABLA DOCUMENTO (Central)

```sql
CREATE TABLE DOCUMENTO (
    -- Identificación
    id BIGSERIAL PRIMARY KEY,
    
    -- Tipo (discriminador para herencia)
    tipo VARCHAR(100) NOT NULL CHECK (
        tipo IN ('RENDICION_ORGANISMO', 'RENDICION_SUBSIDIO', 
                 'EXPEDIENTE', 'MULTA', 'OMISION_RENDITIVA',
                 'OFICIO_CEDULA', 'LEGAJO_RENDITIVO')
    ),
    
    -- Datos del documento
    titulo VARCHAR(500) NOT NULL,
    descripcion TEXT,
    monto NUMERIC(15, 2),
    
    -- Estados
    estado VARCHAR(100) NOT NULL DEFAULT 'INGRESADO' CHECK (
        estado IN ('INGRESADO', 'CARATULADO', 'EN_AUDITORIA',
                   'PENDIENTE_INFORME', 'INFORME_GENERADO',
                   'EN_REVISION_SUPERIOR', 'APROBADO',
                   'PENDIENTE_CERTIFICACION', 'CERTIFICADO',
                   'ARCHIVADO')
    ),
    
    -- Relaciones
    institucion_id BIGINT REFERENCES INSTITUCION(id),
    dependencia_id BIGINT REFERENCES DEPENDENCIA(id),
    auditor_id BIGINT REFERENCES USUARIO(id),
    
    -- Fechas
    fecha_ingreso DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_asignacion TIMESTAMP,
    fecha_certificacion TIMESTAMP,
    
    -- Auditoría
    creado_por_id BIGINT REFERENCES USUARIO(id),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modificado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Control
    es_confidencial BOOLEAN DEFAULT FALSE,
    requiere_firma BOOLEAN DEFAULT TRUE,
    
    -- Índices
    CONSTRAINT unique_titulo_mes_institucion 
        UNIQUE (titulo, DATE_TRUNC('month', fecha_ingreso), institucion_id)
);

-- ÍNDICES para optimizar búsquedas
CREATE INDEX idx_documento_estado ON DOCUMENTO(estado);
CREATE INDEX idx_documento_auditor ON DOCUMENTO(auditor_id);
CREATE INDEX idx_documento_institucion ON DOCUMENTO(institucion_id);
CREATE INDEX idx_documento_dependencia ON DOCUMENTO(dependencia_id);
CREATE INDEX idx_documento_fecha_ingreso ON DOCUMENTO(fecha_ingreso DESC);
CREATE INDEX idx_documento_tipo ON DOCUMENTO(tipo);
CREATE INDEX idx_documento_estado_auditor ON DOCUMENTO(estado, auditor_id);
```

**Explicación:**

- `tipo VARCHAR(100) CHECK (...)` → Discriminador para herencia (polimorfismo)
- `monto NUMERIC(15, 2)` → 15 dígitos totales, 2 decimales (dinero)
- `estado VARCHAR(100) CHECK (...)` → Solo valores válidos
- `UNIQUE` constraint → Evita duplicados en el mismo mes
- Índices → Acelera búsquedas por estado, auditor, fecha

### 2. TABLA ACTUACION (Auditoría)

```sql
CREATE TABLE ACTUACION (
    -- Identificación
    id BIGSERIAL PRIMARY KEY,
    
    -- A qué afecta
    documento_id BIGINT NOT NULL REFERENCES DOCUMENTO(id) 
        ON DELETE CASCADE,
    
    -- Quién la hizo
    usuario_id BIGINT REFERENCES USUARIO(id),
    
    -- Qué pasó
    accion VARCHAR(500) NOT NULL,
    detalles TEXT,
    
    -- Cuándo y dónde
    fecha DATE NOT NULL DEFAULT CURRENT_DATE,
    hora TIME NOT NULL DEFAULT CURRENT_TIME,
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    direccion_ip INET,
    navegador VARCHAR(500),
    
    -- Estado antes/después
    estado_anterior VARCHAR(100),
    estado_nuevo VARCHAR(100),
    
    -- Índices
    CONSTRAINT check_tiene_datos 
        CHECK (usuario_id IS NOT NULL OR accion IS NOT NULL)
);

CREATE INDEX idx_actuacion_documento ON ACTUACION(documento_id);
CREATE INDEX idx_actuacion_usuario ON ACTUACION(usuario_id);
CREATE INDEX idx_actuacion_fecha ON ACTUACION(fecha DESC);
CREATE INDEX idx_actuacion_documento_fecha 
    ON ACTUACION(documento_id, fecha DESC);
```

**Importancia:** Cada acción se registra completamente para auditoría inmutable.

### 3. TABLA USUARIO

```sql
CREATE TABLE USUARIO (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,  -- Nunca almacena password en claro
    rol_id BIGINT REFERENCES ROL(id),
    dependencia_id BIGINT REFERENCES DEPENDENCIA(id),
    activo BOOLEAN DEFAULT TRUE,
    ultimo_login TIMESTAMP,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT email_valido CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$')
);

CREATE INDEX idx_usuario_email ON USUARIO(email);
CREATE INDEX idx_usuario_rol ON USUARIO(rol_id);
CREATE INDEX idx_usuario_activo ON USUARIO(activo);
```

### 4. TABLA INFORME_AUDITOR

```sql
CREATE TABLE INFORME_AUDITOR (
    id BIGSERIAL PRIMARY KEY,
    
    -- A qué documento
    documento_id BIGINT NOT NULL REFERENCES DOCUMENTO(id),
    
    -- Quién lo hizo
    auditor_id BIGINT NOT NULL REFERENCES USUARIO(id),
    
    -- Contenido
    conclusiones TEXT NOT NULL,
    observaciones TEXT,
    recomendaciones TEXT,
    
    -- Análisis
    monto_auditado NUMERIC(15, 2),
    monto_observado NUMERIC(15, 2),
    observaciones_criticas INTEGER DEFAULT 0,
    
    -- Estado del informe
    estado VARCHAR(50) CHECK (
        estado IN ('PENDIENTE_REVISION', 'APROBADO', 'RECHAZADO', 'MODIFICADO')
    ),
    
    -- Fechas
    fecha_generacion DATE NOT NULL DEFAULT CURRENT_DATE,
    fecha_aprobacion DATE,
    
    -- Quién aprobó
    aprobado_por_id BIGINT REFERENCES USUARIO(id),
    
    CONSTRAINT unique_informe_por_documento 
        UNIQUE (documento_id, auditor_id),
    
    CONSTRAINT validar_dinero 
        CHECK (monto_observado >= 0 AND monto_observado <= monto_auditado)
);

CREATE INDEX idx_informe_documento ON INFORME_AUDITOR(documento_id);
CREATE INDEX idx_informe_auditor ON INFORME_AUDITOR(auditor_id);
CREATE INDEX idx_informe_estado ON INFORME_AUDITOR(estado);
```

### 5. TABLA FIRMA_ELECTRONICA

```sql
CREATE TABLE FIRMA_ELECTRONICA (
    id BIGSERIAL PRIMARY KEY,
    
    -- Qué se firmó
    documento_id BIGINT NOT NULL REFERENCES DOCUMENTO(id),
    
    -- Quién firmó
    usuario_id BIGINT NOT NULL REFERENCES USUARIO(id),
    
    -- Cuándo
    fecha_firma TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    -- Con qué
    numero_certificado VARCHAR(255),
    emisor_certificado VARCHAR(255),
    valido_desde DATE,
    valido_hasta DATE,
    
    -- Validación
    hash_firma VARCHAR(512),  -- SHA-256 de la firma
    timestamp_autoridad_certificacion TIMESTAMP,
    es_valida BOOLEAN DEFAULT TRUE,
    
    -- Archivo
    pdf_certificado BYTEA,  -- El PDF firmado
    
    CONSTRAINT check_fecha_validez 
        CHECK (valido_desde <= valido_hasta)
);

CREATE INDEX idx_firma_documento ON FIRMA_ELECTRONICA(documento_id);
CREATE INDEX idx_firma_usuario ON FIRMA_ELECTRONICA(usuario_id);
CREATE INDEX idx_firma_fecha ON FIRMA_ELECTRONICA(fecha_firma DESC);
```

### 6. TABLA INSTITUCION

```sql
CREATE TABLE INSTITUCION (
    id BIGSERIAL PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    cuit VARCHAR(15),
    tipo_organismo VARCHAR(100) CHECK (
        tipo_organismo IN ('MINISTERIO', 'SECRETARIA', 'AGENCIA',
                           'ORGANISMO_DESCENTRADO', 'EMPRESA_ESTATAL')
    ),
    domicilio VARCHAR(500),
    telefono VARCHAR(20),
    email VARCHAR(255),
    jefe_contact VARCHAR(255),
    activa BOOLEAN DEFAULT TRUE
);

CREATE INDEX idx_institucion_nombre ON INSTITUCION(nombre);
CREATE INDEX idx_institucion_tipo ON INSTITUCION(tipo_organismo);
```

### 7. TABLA CONFIGURACION_CLASIFICACION_ESTADO_DOCUMENTO

**Esta tabla almacena las REGLAS de transición de estados** (sin hardcodear en código)

```sql
CREATE TABLE CONFIGURACION_CLASIFICACION_ESTADO_DOCUMENTO (
    id BIGSERIAL PRIMARY KEY,
    
    -- A qué aplica la regla
    tipo_documento VARCHAR(100),  -- NULL = aplica a todos
    estado_origen VARCHAR(100) NOT NULL,
    estado_destino VARCHAR(100) NOT NULL,
    
    -- ¿Es permitida la transición?
    permitida BOOLEAN DEFAULT TRUE,
    
    -- Restricciones adicionales
    requiere_aprobacion_jefe BOOLEAN DEFAULT FALSE,
    requiere_firma_digital BOOLEAN DEFAULT FALSE,
    requiere_informe_auditor BOOLEAN DEFAULT FALSE,
    
    -- Descripción
    descripcion TEXT,
    
    -- Auditoría
    creada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modificada_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT unique_transicion 
        UNIQUE (tipo_documento, estado_origen, estado_destino)
);

-- Ejemplos de datos
INSERT INTO CONFIGURACION_CLASIFICACION_ESTADO_DOCUMENTO 
(tipo_documento, estado_origen, estado_destino, permitida, requiere_jefe)
VALUES
('RENDICION_ORGANISMO', 'INGRESADO', 'CARATULADO', TRUE, FALSE),
('RENDICION_ORGANISMO', 'CARATULADO', 'EN_AUDITORIA', TRUE, FALSE),
('RENDICION_ORGANISMO', 'EN_AUDITORIA', 'PENDIENTE_INFORME', TRUE, FALSE),
('RENDICION_ORGANISMO', 'PENDIENTE_INFORME', 'CERTIFICADO', FALSE, TRUE),
('RENDICION_ORGANISMO', 'EN_AUDITORIA', 'CERTIFICADO', FALSE, TRUE),
('EXPEDIENTE', 'INGRESADO', 'CERTIFICADO', FALSE, TRUE);
```

---

## 📈 CONSULTAS COMUNES (SQL + Hibernate)

### Consulta 1: Obtener documentos por auditor y estado

```sql
-- SQL directo
SELECT d.* FROM DOCUMENTO d
WHERE d.auditor_id = 42
  AND d.estado = 'EN_AUDITORIA'
ORDER BY d.fecha_ingreso DESC;
```

```java
// Hibernate/JPA equivalente
List<Documento> documentos = session.createQuery(
    "FROM Documento d WHERE d.auditor.id = :auditorId " +
    "AND d.estado = :estado " +
    "ORDER BY d.fechaIngreso DESC"
)
.setParameter("auditorId", 42L)
.setParameter("estado", TipoEstadoDocumento.EN_AUDITORIA)
.list();
```

### Consulta 2: Contar documentos por estado

```sql
SELECT 
    estado,
    COUNT(*) as cantidad
FROM DOCUMENTO
WHERE fecha_ingreso >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY estado
ORDER BY cantidad DESC;
```

```java
// Hibernate
List<Object[]> resultados = session.createQuery(
    "SELECT d.estado, COUNT(d) FROM Documento d " +
    "WHERE d.fechaIngreso >= :hace30Dias " +
    "GROUP BY d.estado " +
    "ORDER BY COUNT(d) DESC"
)
.setParameter("hace30Dias", LocalDate.now().minusDays(30))
.list();

for (Object[] fila : resultados) {
    TipoEstadoDocumento estado = (TipoEstadoDocumento) fila[0];
    Long cantidad = (Long) fila[1];
    System.out.println(estado + ": " + cantidad);
}
```

### Consulta 3: Auditor con más trabajo pendiente

```sql
SELECT 
    u.id,
    u.nombre,
    COUNT(d.id) as documentos_pendientes
FROM USUARIO u
LEFT JOIN DOCUMENTO d ON u.id = d.auditor_id 
    AND d.estado IN ('INGRESADO', 'CARATULADO', 'EN_AUDITORIA')
WHERE u.rol_id = (SELECT id FROM ROL WHERE nombre = 'AUDITOR')
  AND u.activo = TRUE
GROUP BY u.id, u.nombre
HAVING COUNT(d.id) = (
    SELECT MAX(cnt) FROM (
        SELECT COUNT(d2.id) as cnt
        FROM USUARIO u2
        LEFT JOIN DOCUMENTO d2 ON u2.id = d2.auditor_id
        WHERE u2.rol_id = (SELECT id FROM ROL WHERE nombre = 'AUDITOR')
        GROUP BY u2.id
    ) subquery
)
ORDER BY documentos_pendientes DESC;
```

```java
// Equivalente en Hibernate (más complejo, se recomienda SQL nativo)
String sql = "SELECT u.id, u.nombre, COUNT(d.id) as cnt " +
    "FROM USUARIO u " +
    "LEFT JOIN DOCUMENTO d ON u.id = d.auditor.id " +
    "WHERE u.rol.nombre = 'AUDITOR' AND u.activo = TRUE " +
    "GROUP BY u.id, u.nombre " +
    "ORDER BY cnt DESC";

List<Object[]> resultados = session.createSQLQuery(sql)
    .list();
```

### Consulta 4: Historial completo de un documento

```sql
SELECT 
    a.fecha,
    a.hora,
    u.nombre,
    a.accion,
    a.estado_anterior,
    a.estado_nuevo
FROM ACTUACION a
JOIN USUARIO u ON a.usuario_id = u.id
WHERE a.documento_id = 1001
ORDER BY a.fecha_hora DESC;
```

```java
List<Actuacion> historial = session.createQuery(
    "FROM Actuacion a WHERE a.documento.id = :docId " +
    "ORDER BY a.fechaHora DESC"
)
.setParameter("docId", 1001L)
.list();

for (Actuacion accion : historial) {
    System.out.println(accion.getFecha() + " " + accion.getHora() + 
        ": " + accion.getUsuario().getNombre() + " - " + 
        accion.getAccion());
}
```

### Consulta 5: Rendimiento de auditores (últimos 30 días)

```sql
SELECT 
    u.nombre,
    COUNT(DISTINCT d.id) as documentos_procesados,
    AVG(EXTRACT(DAY FROM CURRENT_TIMESTAMP - d.fecha_ingreso)) 
        as dias_promedio,
    COUNT(CASE WHEN d.estado = 'CERTIFICADO' THEN 1 END) 
        as certificados,
    ROUND(100.0 * COUNT(CASE WHEN d.estado = 'CERTIFICADO' THEN 1 END) / 
        NULLIF(COUNT(d.id), 0), 2) as porcentaje_completado
FROM USUARIO u
LEFT JOIN DOCUMENTO d ON u.id = d.auditor_id 
    AND d.fecha_ingreso >= CURRENT_DATE - INTERVAL '30 days'
WHERE u.rol_id = (SELECT id FROM ROL WHERE nombre = 'AUDITOR')
GROUP BY u.id, u.nombre
ORDER BY documentos_procesados DESC;
```

---

## 🔍 ÍNDICES (Optimización)

### Índices por Uso Frecuente

```sql
-- Búsqueda por estado (MUY FRECUENTE)
CREATE INDEX idx_documento_estado ON DOCUMENTO(estado);

-- Asignación a auditor (FRECUENTE)
CREATE INDEX idx_documento_auditor ON DOCUMENTO(auditor_id);

-- Búsqueda por fecha (FRECUENTE)
CREATE INDEX idx_documento_fecha ON DOCUMENTO(fecha_ingreso DESC);

-- Filtro combinado (AUDITOR + ESTADO)
CREATE INDEX idx_documento_auditor_estado 
    ON DOCUMENTO(auditor_id, estado);

-- Búsqueda por institución
CREATE INDEX idx_documento_institucion ON DOCUMENTO(institucion_id);

-- Historial de documento
CREATE INDEX idx_actuacion_documento_fecha 
    ON ACTUACION(documento_id, fecha_hora DESC);
```

### Explicar Plan (EXPLAIN PLAN)

Antes de ejecutar una query lenta:

```sql
EXPLAIN ANALYZE
SELECT d.* FROM DOCUMENTO d
WHERE d.auditor_id = 42 AND d.estado = 'EN_AUDITORIA'
ORDER BY d.fecha_ingreso DESC;
```

**Buen resultado:** "Index Scan" (usa índice, rápido)  
**Mal resultado:** "Seq Scan" (lee toda la tabla, lento)

---

## 🔐 SEGURIDAD EN BD

### Constraint de Integridad Referencial

```sql
-- Cascada: Si se borra usuario, se borran sus acciones
ALTER TABLE ACTUACION 
    ADD CONSTRAINT fk_actuacion_usuario 
    FOREIGN KEY (usuario_id) REFERENCES USUARIO(id) 
    ON DELETE CASCADE;

-- Restrict: No permite borrar si hay documentos asociados
ALTER TABLE DOCUMENTO 
    ADD CONSTRAINT fk_documento_auditor 
    FOREIGN KEY (auditor_id) REFERENCES USUARIO(id) 
    ON DELETE RESTRICT;
```

### Validación de Datos

```sql
-- Dinero no negativo
ALTER TABLE DOCUMENTO ADD CONSTRAINT check_monto_positivo 
    CHECK (monto >= 0);

-- Email válido
ALTER TABLE USUARIO ADD CONSTRAINT check_email_valido 
    CHECK (email ~ '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$');

-- Fechas coherentes
ALTER TABLE FIRMA_ELECTRONICA ADD CONSTRAINT check_fecha_validez 
    CHECK (valido_desde <= valido_hasta);
```

---

## 📊 ESTADÍSTICAS Y MONITOREO

### Ver tamaño de tablas

```sql
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) 
        as size
FROM pg_tables
WHERE schemaname = 'public'
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

**Salida esperada:**
```
schemaname | tablename  | size
-----------+------------+----------
public     | documento  | 150 MB
public     | actuacion  | 250 MB
public     | usuario    | 2 MB
```

### Índices y su uso

```sql
SELECT 
    indexname,
    idx_scan as veces_usado,
    idx_tup_read as registros_escaneados,
    idx_tup_fetch as registros_retornados
FROM pg_stat_user_indexes
ORDER BY idx_scan DESC;
```

### Queries lentas

```sql
-- Ver queries que están corriendo ahora
SELECT 
    pid,
    usename,
    state,
    query,
    query_start
FROM pg_stat_activity
WHERE state != 'idle'
ORDER BY query_start DESC;
```

---

## 🚀 CONSEJOS DE RENDIMIENTO

1. **Usa índices para filtros frecuentes** (WHERE, ORDER BY)
2. **Evita SELECT *** → Selecciona solo columnas necesarias**
3. **Usa LIMIT para resultados grandes**
4. **Agrupa correctamente** (GROUP BY debe tener todas las no-agregadas)
5. **Cachea resultados** que no cambian frecuentemente
6. **VACUUM periódicamente** para limpiar BD

```bash
# Limpiar y analizar BD
vacuum full analyze;
```

---

SIGEDO usa esta base de datos como "fuente de verdad" para todas las operaciones.
