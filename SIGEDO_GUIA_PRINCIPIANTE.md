# 🚀 SIGEDO: GUÍA PARA PRINCIPIANTES (Sin experiencia en Java)

> Trabajas en SIGEDO pero no sabes Java, Hibernate, Spring... Aquí aprenderás qué es esto sin complicaciones.

---

## 📌 EN UNA FRASE

**SIGEDO** es un sistema web que gestiona documentos de auditoría para el Tribunal de Cuentas de Entre Ríos. Los documentos pasan por múltiples etapas (revisión, auditoría, firma digital) y el sistema registra todo: quién hizo qué, cuándo y por qué.

---

## 🎯 ¿QUÉ HACE SIGEDO?

### Analogía Simple:
Imagina un **banco que procesa solicitudes de crédito**:
1. Recibes solicitud (cliente)
2. La revisas (empleado 1)
3. La auditas (empleado 2)
4. Tu jefe la aprueba (empleado 3)
5. Se firma digitalmente (abogado)
6. Se archiva (secretaria)

**SIGEDO hace lo mismo, pero para documentos fiscales del gobierno.**

---

## 📊 FLUJO PRINCIPAL (Simplificado)

```
Organismo envía documento
        ↓
SIGEDO lo recibe y clasifica
        ↓
Se asigna a un auditor
        ↓
Auditor lo revisa (múltiples etapas)
        ↓
Se firma digitalmente
        ↓
Se genera PDF oficial
        ↓
Organismo descarga el resultado
        ↓
SIGEDO lo archiva permanentemente
```

---

## 🗂️ TIPOS DE DOCUMENTOS (7 principales)

| Tipo | Ejemplo | Quién lo envía |
|------|---------|----------------|
| **RENDICIÓN_ORGANISMO** | Cuenta anual de un ministerio | Ministerio |
| **RENDICIÓN_SUBSIDIO** | Informe de cómo se gastó subsidio | Beneficiario |
| **EXPEDIENTE** | Carpeta de auditoría | TCER (interno) |
| **MULTA** | Penalización | TCER (interno) |
| **OMISIÓN_RENDITIVA** | Aviso de no entregar cuenta | TCER (automático) |
| **OFICIO/CÉDULA** | Comunicación oficial | TCER |
| **LEGAJO_RENDITIVO** | Archivo contable completo | Contabilidad |

---

## 🔄 ESTADOS DE UN DOCUMENTO (Los más comunes)

Un documento pasa por estos estados (como una máquina que avanza de etapa en etapa):

```
INGRESADO
    ↓
CARATULADO (se asigna auditor)
    ↓
EN_AUDITORÍA (se revisa)
    ↓
PENDIENTE_INFORME (se escribe informe)
    ↓
EN_REVISIÓN_SUPERIOR (jefe revisa)
    ↓
PENDIENTE_CERTIFICACIÓN (listo para firmar)
    ↓
CERTIFICADO (firmado ✅)
    ↓
ARCHIVADO (guardado para siempre)
```

**Nota:** El sistema permite crear reglas personalizadas. El admin puede configurar qué estados son válidos sin tocar código.

---

## 👥 ACTORES (¿QUIÉNES USAN ESTO?)

| Actor | Qué hace |
|-------|----------|
| **Auditor** | Revisa documentos, genera informes |
| **Contador** | Verifica números, auditoría contable |
| **Abogado** | Revisa aspectos legales |
| **Jefe** | Aprueba trabajos de auditores |
| **Admin** | Configura el sistema, gestiona permisos |
| **Usuario Externo** | Envía documentos, recibe resultados |

---

## 🏗️ ARQUITECTURA (Como un edificio)

El sistema tiene **3 pisos**:

```
┌─────────────────────────┐
│  WEB (INTERFAZ)         │  ← Páginas, botones, formularios
├─────────────────────────┤
│  SERVICIOS (LÓGICA)     │  ← Reglas del negocio
├─────────────────────────┤
│  BASE DE DATOS          │  ← Donde se guarda todo
└─────────────────────────┘
```

### Piso 3 (Abajo): BASE DE DATOS
- **PostgreSQL** (base de datos profesional)
- 100+ tablas (DOCUMENTO, USUARIO, INFORME_AUDITOR, etc.)
- Almacena todo permanentemente

### Piso 2 (Medio): SERVICIOS (La lógica del negocio)
- **ServicioDocumento** — Cómo se procesa un documento
- **ServicioAuditoria** — Cómo se audita
- **ServicioEstados** — Qué transiciones son válidas
- Reglas, validaciones, cálculos

### Piso 1 (Arriba): INTERFAZ WEB
- **Páginas** — Lo que ves en el navegador
- **Formularios** — Para buscar, crear, editar
- **Reportes** — Gráficos y PDFs
- **APIs** — Para integración con otros sistemas

---

## 💾 ESTRUCTURA DE CARPETAS

```
sigedo/
├── model/              ← Definiciones (qué es un Documento, un Usuario, etc.)
├── dao-api/            ← "Formas de guardar" (interfaces)
├── dao-impl/           ← Implementación real (cómo guardar en BD)
├── service-api/        ← "Formas de hacer operaciones" (interfaces)
├── service-impl/       ← Implementación real (lógica de negocio)
├── web/                ← Lo que ves en el navegador
└── common/             ← Herramientas compartidas
```

**Regla de oro:** Si necesitas guardar algo → usa `dao-impl`. Si necesitas lógica de negocio → usa `service-impl`.

---

## 🔧 TECNOLOGÍAS (QUÉ ESTÁ ESCRITO EN QUÉ)

| Tecnología | Qué es | Para qué |
|-----------|--------|----------|
| **Java 11** | Lenguaje de programación | Compilado, rápido, seguro |
| **Spring** | Framework de organización | Gestiona componentes, inyecta dependencias |
| **Wicket** | Framework web | Crea páginas con componentes reutilizables |
| **Hibernate** | ORM (traductor) | Convierte objetos Java en filas de BD |
| **PostgreSQL** | Base de datos | Almacena datos profesionalmente |
| **Apache CXF** | API REST | Comunicación entre sistemas |
| **JasperReports** | Generador de reportes | Crea PDFs bonitos |

**Lo importante:** No necesitas entender cómo funciona cada una. Solo necesitas saber cuál editar para cada cambio.

---

## 🚀 CÓMO LEVANTAR EL SERVIDOR

### Paso 1: Compilar
```bash
cd /home/pablog/git/sigedo
mvn clean install -DskipTests
```
(Espera ~50 segundos, debe terminar con BUILD SUCCESS)

### Paso 2: Levantar servidor
```bash
mvn jetty:run -pl web
```

### Paso 3: Abrir en navegador
```
http://localhost:8090/tcer/
```

### Paso 4: Detener
```
Ctrl+C en la terminal
```

---

## 📋 CAMBIOS COMUNES (¿Dónde editar?)

### "Necesito agregar un campo nuevo"
**Ejemplo:** Agregar "Fecha de auditoría prevista"

1. **Edita el modelo** → `model/src/main/java/.../Expediente.java`
2. **Edita el DAO** (si necesita búsquedas especiales) → `dao-impl/...`
3. **Edita la página web** → `web/src/main/java/.../PaginaControlExpediente.java`
4. **Compila:** `mvn clean install -DskipTests`

### "Necesito cambiar un mensaje en pantalla"
**Archivo:** `web/src/main/java/.../PaginaBusqueda.java`
- Busca el texto
- Cámbialo
- Compila

### "Necesito agregar validación"
**Ejemplo:** El monto no debe ser negativo

1. **Edita el servicio** → `service-impl/.../ServicioDocumentoImpl.java`
2. Agrega: `if (documento.getMonto() < 0) throw new IllegalArgumentException(...)`
3. Compila

### "Necesito agregar un nuevo estado"
1. **Edita el enum** → `model/src/main/java/.../TipoEstadoDocumento.java`
2. Agrega: `PENDIENTE_REVISIÓN_LEGAL,`
3. Edita la configuración de estados (admin panel)
4. Compila

---

## 📚 GLOSARIO RÁPIDO (Palabras raras)

| Palabra | Qué significa |
|---------|---------------|
| **DAO** | Data Access Object (cómo guardar en BD) |
| **Hibernate** | Traductor entre Java y SQL |
| **Entity** | Clase Java que representa una fila en BD |
| **Service** | Clase con lógica de negocio |
| **@Autowired** | "Spring, por favor inyecta esta herramienta automáticamente" |
| **Spring** | Framework que organiza todo |
| **Wicket** | Framework para hacer páginas web |
| **Transactional** | "Si algo falla, deshaz todo" |
| **ORM** | Object-Relational Mapping (traductor Java ↔ SQL) |
| **REST/API** | Forma de comunicarse entre programas |

---

## 🎓 FLUJO REAL: EJEMPLO

### Caso: "Auditar una Rendición de Subsidio"

#### Día 1 - Mañana
1. Organismo envía "Rendición Subsidio 2024"
2. Sistema automáticamente:
   - Crea documento en estado **INGRESADO**
   - Registra quién lo creó, cuándo, por qué (auditoría)
   - Busca auditor disponible

#### Día 1 - Tarde
3. Sistema asigna a "Juan" (auditor)
4. Cambia estado a **CARATULADO**
5. Juan recibe email: "Tienes nueva tarea"

#### Día 2-3: Juan revisa
6. Juan abre el documento
7. Verifica: ¿Están todos los anexos? ¿Los montos cuadran?
8. Si falta algo:
   - Cambia a **PENDIENTE_DOCUMENTACIÓN**
   - Escribe nota: "Falta comprobante del 15/3"
   - Sistema registra en auditoría
9. Organismo sube documento faltante
10. Juan verifica, cambia a **DOCUMENTACIÓN_COMPLETA**

#### Día 4-5: Auditoría profunda
11. Juan hace análisis técnico:
    - Revisa que dinero no se haya desviado
    - Busca anomalías
    - Toma notas
12. Genera **Informe de Auditor**:
    - ✅ Conclusión: "Conforme"
    - 📝 Observación: "Gasto en capacitación no autorizada"
    - 💡 Recomendación: "Implementar pre-aprobación"
13. Cambia estado a **INFORME_GENERADO**

#### Día 6: Supervisor revisa
14. Carlos (jefe) revisa el trabajo de Juan
15. Lo aprueba
16. Cambia a **REVISADO_SUPERIOR**

#### Día 7: Firma digital
17. Sistema prepara para **CERTIFICACIÓN**
18. Pide firma electrónica a Carlos
19. Carlos firma digitalmente
20. Sistema registra: "Firmado por Carlos el 07/07/2024"

#### Día 8: Fin
21. Documento pasa a **CERTIFICADO** ✅
22. Se genera PDF oficial con firma
23. Organismo recibe notificación: "Tu rendición fue aprobada"
24. Todo queda archivado para siempre

---

## 🔑 REGLAS DE ORO

1. **Siempre compila después de editar:** `mvn clean install -DskipTests`
2. **El estado de un documento es una máquina:** No puede retroceder sin permiso
3. **Registra todo en auditoría:** Quién, qué, cuándo, por qué
4. **No hardcodees datos:** Usa configuración
5. **Usa transacciones:** "Todo o nada"
6. **La base de datos es la fuente de verdad:** Si está allí, es real

---

## 📞 PRÓXIMOS PASOS

1. ✅ **Leer esta guía** (ya lo hiciste)
2. **Levantar el servidor** con `mvn jetty:run -pl web`
3. **Explorar una página** real: abre `web/src/main/java/.../PaginaBusquedaDocumento.java`
4. **Entender un servicio**: abre `service-impl/.../ServicioDocumentoImpl.java`
5. **Hacer un cambio pequeño**: edita un mensaje, agrega un campo
6. **Compilar y probar**: `mvn clean install`
7. **Compartir dudas:** pregunta mientras avanzamos

---

## 🆘 CUANDO TENGAS DUDAS

- **"¿Dónde está X?"** → Busca en carpeta `web/src/main/java/`
- **"¿Cómo guardo datos?"** → Usa `dao-impl/`
- **"¿Cómo hago validaciones?"** → Usa `service-impl/`
- **"¿Qué es esa palabra rara?"** → Lee `GLOSARIO_TECNICO.md`

---

**Resumen:** SIGEDO es un sistema de auditoría estatal que recibe documentos, los procesa a través de múltiples etapas, los audita, firma digitalmente y los archiva. Todo registrado para auditoría permanente.

**¡Ahora estás listo para trabajar en SIGEDO!**
