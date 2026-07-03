# 🏗️ SIGEDO: ARQUITECTURA EN PROFUNDIDAD

> Entender exactamente cómo está organizado SIGEDO, qué hace cada pieza y cómo se comunican

---

## 📊 VISIÓN GENERAL (4 capas)

```
┌────────────────────────────────────────────────┐
│ CAPA 1: PRESENTACIÓN (Web)                     │
│ - Navegador HTML/JS                            │
│ - Páginas Wicket                               │
│ - APIs REST                                    │
├────────────────────────────────────────────────┤
│ CAPA 2: APLICACIÓN (Lógica de Negocio)         │
│ - Servicios (ServicioXXX)                      │
│ - Validaciones                                 │
│ - Orquestación de procesos                     │
├────────────────────────────────────────────────┤
│ CAPA 3: ACCESO A DATOS (Persistencia)          │
│ - DAOs (Data Access Objects)                   │
│ - Queries SQL/Hibernate                        │
│ - Transacciones                                │
├────────────────────────────────────────────────┤
│ CAPA 4: BASE DE DATOS                          │
│ - PostgreSQL                                   │
│ - Tablas, índices, constraints                 │
└────────────────────────────────────────────────┘
```

---

## 🗂️ ESTRUCTURA DE MÓDULOS

### **common/** — Utilidades Compartidas

```
common/
├── excepciones/
│   ├── SigedoException.java          → Excepción base
│   ├── DocumentoNoEncontradoException.java
│   ├── EstadoInvalidoException.java
│   └── PermisoDenegadoException.java
├── utilidades/
│   ├── DateUtils.java               → Funciones de fecha
│   ├── StringUtils.java             → Funciones de string
│   ├── ValidationUtils.java         → Validaciones comunes
│   └── NumberUtils.java             → Manejo de números
├── constantes/
│   ├── TiposDocumento.java          → Tipos documentales
│   ├── RolesUsuario.java            → Roles del sistema
│   └── MensajesError.java           → Mensajes de error
└── configuracion/
    ├── AppConfig.java               → Configuración global
    └── LoggerFactory.java           → Factory de loggers
```

**Para qué:** Evitar código duplicado. Si algo se usa en 2+ módulos → va en `common/`.

---

### **model/** — Entidades y Dominio

Este es el "corazón" del sistema. Define QUÉ ES cada cosa.

```
model/
├── documento/
│   ├── Documento.java               → Clase base (ENTIDAD)
│   ├── Expediente.java              → Subclase de Documento
│   ├── RendicionOrganismo.java      → Subclase de Documento
│   ├── RendicionSubsidio.java       → Subclase de Documento
│   ├── Multa.java                   → Subclase de Documento
│   ├── OmisionRenditiva.java        → Subclase de Documento
│   └── EstadoDocumento.java         → Estados posibles
├── auditoria/
│   ├── Actuacion.java               → Registro de acciones
│   ├── InformeAuditor.java          → Informe técnico
│   ├── InformeValorativo.java       → Análisis financiero
│   └── Observacion.java             → Anotaciones
├── usuarios/
│   ├── Usuario.java                 → Entidad usuario
│   ├── Rol.java                     → Roles (ENUM)
│   └── Permiso.java                 → Permisos granulares
├── instituciones/
│   ├── Institucion.java             → Organismo gubernamental
│   ├── Dependencia.java             → Departamento de institución
│   └── Cargo.java                   → Posiciones de personal
├── firma/
│   ├── FirmaElectronica.java        → Registro de firma
│   ├── Certificado.java             → Certificado digital
│   └── EstadoFirma.java             → Estados de firma (ENUM)
└── configuracion/
    └── ConfiguracionClasificacionEstadoDocumento.java → Reglas de transición
```

**Clave:** Las clases en `model/` se mapean automáticamente a **tablas en PostgreSQL** usando Hibernate.

#### Ejemplo: Documento.java (Clase Padre)

```java
@Entity
@Table(name = "DOCUMENTO")
@Inheritance(strategy = InheritanceType.SINGLE_TABLE)
@DiscriminatorColumn(name = "TIPO")
public class Documento {
    @Id
    @GeneratedValue(strategy = GenerationType.SEQUENCE)
    private Long id;
    
    private String titulo;
    private LocalDate fechaIngreso;
    private BigDecimal monto;
    
    @Enumerated(EnumType.STRING)
    private TipoEstadoDocumento estado;
    
    @ManyToOne
    private Usuario auditorAsignado;
    
    @OneToMany(mappedBy = "documento")
    private List<Actuacion> actuaciones = new ArrayList<>();
}
```

**Qué significa:**
- `@Entity` → "Esta clase representa una fila en BD"
- `@Table(name = "DOCUMENTO")` → "Se guarda en tabla DOCUMENTO"
- `@Id` → "Este es el identificador único"
- `@ManyToOne` → "Muchos documentos pueden tener 1 auditor"
- `@OneToMany` → "1 documento tiene múltiples acciones registradas"

#### Ejemplo: Expediente.java (Subclase)

```java
@Entity
@DiscriminatorValue("EXPEDIENTE")
public class Expediente extends Documento {
    private String numeroExpediente;
    private LocalDate fechaAuditoriaPrevista;
    
    @OneToMany
    private List<Documento> documentosAdjuntos = new ArrayList<>();
}
```

**Qué significa:** Es un `Documento` pero con tipo específico `EXPEDIENTE`. Se guarda en la MISMA tabla que Documento, solo cambia la columna TIPO.

---

### **dao-api/** — Contratos de Acceso a Datos

Define CÓMO acceder a datos (interfaces sin implementación).

```
dao-api/
├── DocumentoDAO.java
│   public Documento findById(Long id);
│   public List<Documento> findAll();
│   public void save(Documento d);
│   public List<Documento> findByEstado(TipoEstadoDocumento estado);
│   public List<Documento> findByAuditor(Usuario auditor);
├── ActuacionDAO.java
├── UsuarioDAO.java
├── InformeAuditorDAO.java
├── FirmaElectronicaDAO.java
└── ConfiguracionEstadoDAO.java
```

**Por qué existe:** Desacoplamiento. El servicio NO sabe si usa SQL directo o Hibernate. Solo conoce la interfaz.

#### Ejemplo: DocumentoDAO.java

```java
public interface DocumentoDAO {
    Documento findById(Long id);
    List<Documento> findAll();
    List<Documento> findByEstado(TipoEstadoDocumento estado);
    List<Documento> findByAuditor(Usuario auditor);
    List<Documento> findByRangoFechas(LocalDate desde, LocalDate hasta);
    Documento save(Documento documento);
    void delete(Long id);
}
```

**Nota:** Solo define QEUR se necesita, no CÓMO se hace.

---

### **dao-impl/** — Implementación Real del Acceso a Datos

Aquí se escriben las **queries SQL** y se usa **Hibernate**.

```
dao-impl/
├── DocumentoDAOImpl.java
│   public Documento findById(Long id) {
│       // Implementación real con Hibernate
│   }
├── ActuacionDAOImpl.java
├── UsuarioDAOImpl.java
├── InformeAuditorDAOImpl.java
├── FirmaElectronicaDAOImpl.java
└── ConfiguracionEstadoDAOImpl.java
```

#### Ejemplo: DocumentoDAOImpl.java

```java
@Repository
public class DocumentoDAOImpl implements DocumentoDAO {
    
    @Autowired
    private SessionFactory sessionFactory;
    
    @Override
    public Documento findById(Long id) {
        Session session = sessionFactory.getCurrentSession();
        return session.get(Documento.class, id);
    }
    
    @Override
    public List<Documento> findByEstado(TipoEstadoDocumento estado) {
        Session session = sessionFactory.getCurrentSession();
        Query query = session.createQuery(
            "FROM Documento d WHERE d.estado = :estado"
        );
        query.setParameter("estado", estado);
        return query.list();
    }
    
    @Override
    @Transactional
    public Documento save(Documento documento) {
        Session session = sessionFactory.getCurrentSession();
        session.saveOrUpdate(documento);
        return documento;
    }
}
```

**Conceptos clave:**
- `@Repository` → "Esta clase accede a la BD"
- `SessionFactory` → "Gestor de conexiones Hibernate"
- `@Transactional` → "Si falla, deshacer todo"
- `Query` → "Consulta a la BD"

---

### **service-api/** — Contratos de Lógica de Negocio

Define QUÉ OPERACIONES puedo hacer con documentos.

```
service-api/
├── ServicioDocumento.java
│   public Documento crearDocumento(DocumentoDTO dto);
│   public void cambiarEstado(Long docId, TipoEstadoDocumento nuevoEstado);
│   public Documento obtenerDocumento(Long id);
│   public void asignarAuditor(Long docId, Usuario auditor);
├── ServicioAuditoria.java
│   public InformeAuditor generarInforme(...);
│   public void adjuntarObservaciones(...);
├── ServicioEstados.java
│   public List<TipoEstadoDocumento> obtenerEstadosSiguientes(...);
│   public boolean esTransicionValida(...);
├── ServicioFirmaElectronica.java
│   public void solicitarFirma(...);
│   public void registrarFirma(...);
└── ServicioUsuario.java
    public Usuario autenticar(String usuario, String password);
    public void cambiarRol(Long usuarioId, Rol nuevoRol);
```

**Por qué existe:** La lógica de negocio está separada de la presentación y persistencia.

#### Ejemplo: ServicioDocumento.java

```java
public interface ServicioDocumento {
    
    /**
     * Crear nuevo documento
     * - Valida que los datos sean correctos
     * - Asigna auditor
     * - Registra en auditoría
     */
    Documento crearDocumento(DocumentoDTO dto);
    
    /**
     * Cambiar estado de un documento
     * - Valida que la transición sea legal
     * - Registra la acción
     * - Genera notificaciones si es necesario
     */
    void cambiarEstado(Long docId, TipoEstadoDocumento nuevoEstado);
    
    /**
     * Obtener documento por ID
     */
    Documento obtenerDocumento(Long id);
    
    /**
     * Asignar auditor responsable
     * - Valida permisos del usuario
     * - Registra asignación
     */
    void asignarAuditor(Long docId, Usuario auditor);
}
```

---

### **service-impl/** — Implementación de Lógica de Negocio

Aquí está la LÓGICA REAL del sistema.

```
service-impl/
├── ServicioDocumentoImpl.java        ← La más importante
├── ServicioAuditoriaImpl.java
├── ServicioEstadosImpl.java
├── ServicioFirmaElectronicaImpl.java
└── ServicioUsuarioImpl.java
```

#### Ejemplo: ServicioDocumentoImpl.java

```java
@Service
@Transactional
public class ServicioDocumentoImpl implements ServicioDocumento {
    
    @Autowired
    private DocumentoDAO documentoDAO;
    
    @Autowired
    private ActuacionDAO actuacionDAO;
    
    @Autowired
    private ServicioEstados servicioEstados;
    
    @Override
    public Documento crearDocumento(DocumentoDTO dto) {
        // PASO 1: Validar
        if (dto.getTitulo() == null || dto.getTitulo().isEmpty()) {
            throw new IllegalArgumentException("Título no puede estar vacío");
        }
        if (dto.getMonto().compareTo(BigDecimal.ZERO) < 0) {
            throw new IllegalArgumentException("Monto no puede ser negativo");
        }
        
        // PASO 2: Crear objeto
        Documento doc = new Documento();
        doc.setTitulo(dto.getTitulo());
        doc.setMonto(dto.getMonto());
        doc.setEstado(TipoEstadoDocumento.INGRESADO);
        doc.setFechaIngreso(LocalDate.now());
        
        // PASO 3: Guardar en BD
        documentoDAO.save(doc);
        
        // PASO 4: Registrar en auditoría
        Actuacion accion = new Actuacion();
        accion.setDocumento(doc);
        accion.setAccion("Documento creado");
        accion.setFecha(LocalDate.now());
        accion.setUsuario(SecurityContextHolder.getContext().getAuthentication()
            .getName());
        actuacionDAO.save(accion);
        
        return doc;
    }
    
    @Override
    public void cambiarEstado(Long docId, TipoEstadoDocumento nuevoEstado) {
        // PASO 1: Obtener documento
        Documento doc = documentoDAO.findById(docId);
        if (doc == null) {
            throw new SigedoException("Documento no encontrado");
        }
        
        // PASO 2: Validar transición
        if (!servicioEstados.esTransicionValida(doc.getEstado(), nuevoEstado)) {
            throw new EstadoInvalidoException(
                "No se puede cambiar de " + doc.getEstado() + 
                " a " + nuevoEstado
            );
        }
        
        // PASO 3: Cambiar estado
        doc.setEstado(nuevoEstado);
        documentoDAO.save(doc);
        
        // PASO 4: Registrar en auditoría
        Actuacion accion = new Actuacion();
        accion.setDocumento(doc);
        accion.setAccion("Estado cambiado a " + nuevoEstado);
        accion.setFecha(LocalDate.now());
        actuacionDAO.save(accion);
    }
}
```

**Conceptos:**
- `@Service` → "Esta clase tiene lógica de negocio"
- `@Transactional` → "Todas las operaciones son "todo o nada""
- `@Autowired` → "Spring inyecta automáticamente los DAOs"
- Métodos públicos → "Métodos que otros pueden llamar"

---

### **web/** — Interfaz Web y APIs

Lo que ve el usuario + endpoints para integración.

```
web/
├── PaginaBusquedaDocumento.java
│   └── Página de búsqueda con filtros
├── PaginaControlDocumento.java
│   └── Crear/editar documento
├── PaginaAuditoria.java
│   └── Generar informes de auditor
├── PaginaFirmaElectronica.java
│   └── Monitorear estado de firmas
├── PaginaReportes.java
│   └── Reportes y estadísticas
├── rest/
│   ├── DocumentoAPI.java
│   │   GET /api/documentos → Lista documentos
│   │   POST /api/documentos → Crear documento
│   │   GET /api/documentos/{id} → Obtener por ID
│   │   PUT /api/documentos/{id} → Actualizar
│   │   DELETE /api/documentos/{id} → Eliminar
│   ├── AuditoriaAPI.java
│   │   POST /api/informes → Generar informe
│   ├── FirmaAPI.java
│   │   GET /api/firmas/pendientes → Firmas pendientes
│   └── ReportesAPI.java
│       GET /api/reportes/estado → Estado por auditor
└── componentes/
    ├── PanelDocumento.java      → Componente reutilizable
    ├── ListaAuditores.java      → Dropdown con auditores
    └── CampoMoneda.java         → Campo para moneda
```

#### Ejemplo: PaginaBusquedaDocumento.java

```java
public class PaginaBusquedaDocumento extends WebPage {
    
    @Autowired
    private ServicioDocumento servicioDocumento;
    
    public PaginaBusquedaDocumento() {
        // CAMPO 1: Búsqueda por título
        TextField titulo = new TextField("titulo", new Model<>());
        
        // CAMPO 2: Filtro por estado
        DropDownChoice<TipoEstadoDocumento> estado = 
            new DropDownChoice<>("estado", 
                new Model<>(),
                Arrays.asList(TipoEstadoDocumento.values()));
        
        // CAMPO 3: Filtro por auditor
        DropDownChoice<Usuario> auditor = 
            new DropDownChoice<>("auditor",
                new Model<>(),
                servicioDocumento.listarAuditores());
        
        // BOTÓN: Buscar
        Button buscar = new Button("buscar") {
            @Override
            public void onSubmit() {
                List<Documento> resultados = servicioDocumento.buscar(
                    titulo.getModelObject(),
                    estado.getModelObject(),
                    auditor.getModelObject()
                );
                // Mostrar resultados en tabla
            }
        };
        
        // Agregar componentes a la página
        add(titulo);
        add(estado);
        add(auditor);
        add(buscar);
    }
}
```

---

## 🔄 FLUJO DE UNA PETICIÓN (De principio a fin)

### Usuario hace clic en "Crear Documento"

```
1. NAVEGADOR (Cliente)
   ↓
   Usuario rellena formulario:
   - Título: "Rendición Subsidio 2024"
   - Monto: 50000.00
   - Hace clic en "Crear"

2. WICKET (web/) - Presentación
   ↓
   PaginaControlDocumento.java recibe datos
   - Valida campos visibles (no vacíos, formato)
   - Crea DocumentoDTO

3. SERVICIO (service-impl/) - Lógica
   ↓
   ServicioDocumentoImpl.crearDocumento(dto)
   - Valida reglas de negocio
   - Verifica permisos del usuario
   - Asigna auditor automáticamente
   - Crea objeto Documento

4. DAO (dao-impl/) - Persistencia
   ↓
   DocumentoDAOImpl.save(documento)
   - Usa Hibernate para convertir objeto Java a SQL
   - Inserta en tabla DOCUMENTO

5. BASE DE DATOS (PostgreSQL)
   ↓
   INSERT INTO DOCUMENTO (titulo, monto, estado, ...)
   VALUES ('Rendición...', 50000, 'INGRESADO', ...)
   
6. RESPUESTA (vuelta)
   ↓
   Documento guardado con ID = 12345
   ↓
   Registrar en auditoría (Actuacion table)
   ↓
   Notificar al usuario: "✅ Documento creado exitosamente"
   ↓
   Redirigir a página de detalle del documento
```

---

## 🌳 RELACIONES ENTRE ENTIDADES

```
USUARIO
  ├─ Muchos → DOCUMENTO (como auditor_asignado)
  ├─ Muchos → ACTUACION (quién realizó la acción)
  ├─ 1 → ROL (auditor, contador, jefe)
  └─ Muchos → PERMISO (qué puede hacer)

DOCUMENTO
  ├─ 1 → USUARIO (auditor_asignado)
  ├─ Muchos → ACTUACION (historial de cambios)
  ├─ Muchos → INFORME_AUDITOR (informes técnicos)
  ├─ Muchos → FIRMA_ELECTRONICA (firmas digitales)
  ├─ 1 → INSTITUCION (organismo que envió)
  └─ Muchos → DOCUMENTO (si es expediente, adjuntos)

INSTITUCION
  ├─ Muchos → DEPENDENCIA (departamentos)
  └─ Muchos → DOCUMENTO (documentos que envía)

ACTUACION
  ├─ 1 → DOCUMENTO (a cuál documento afecta)
  └─ 1 → USUARIO (quién la realizó)

FIRMA_ELECTRONICA
  ├─ 1 → DOCUMENTO (qué se firmó)
  ├─ 1 → USUARIO (quién firmó)
  └─ 1 → CERTIFICADO (con qué certificado)
```

---

## 🔐 ROLES Y PERMISOS

### Roles Principales

| Rol | Permisos |
|-----|----------|
| **AUDITOR** | Crear informes, cambiar estado, ver documentos asignados |
| **CONTADOR** | Generar reportes contables, auditar números |
| **ABOGADO** | Revisar aspectos legales, generar opiniones |
| **JEFE_DEPENDENCIA** | Aprobar trabajos, generar firmas, ver reportes |
| **ADMIN** | Todo (configurar sistema, crear usuarios, etc.) |
| **USUARIO_EXTERNO** | Enviar documentos, ver estado, descargar resultados |

### Control de Acceso (ejemplo)

```java
@Override
public void cambiarEstado(Long docId, TipoEstadoDocumento nuevoEstado) {
    Usuario usuario = getCurrentUser();
    
    // Validar: solo auditor o jefe pueden cambiar estado
    if (!usuario.tieneRol(Rol.AUDITOR) && 
        !usuario.tieneRol(Rol.JEFE_DEPENDENCIA)) {
        throw new PermisoDenegadoException("No tienes permiso");
    }
    
    // ... resto de la lógica
}
```

---

## 📊 DIAGRAMA COMPLETO DE CAPAS Y FLUJO

```
┌─────────────────────────────────────────────────────────────┐
│ CAPA WEB (web module)                                       │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ Wicket Pages (PaginaBusqueda, PaginaControl, etc.)   │   │
│ │ REST APIs (GET /api/documentos, POST, etc.)         │   │
│ │ Componentes reutilizables                           │   │
│ └───────────────────────────────────────────────────────┘   │
│                         ↓                                    │
│ Llama a:               ServicioXXX                          │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ CAPA APLICACIÓN (service modules)                           │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ ServicioDocumento                                    │   │
│ │ - crearDocumento()                                   │   │
│ │ - cambiarEstado()                                    │   │
│ │ - asignarAuditor()                                   │   │
│ │                                                      │   │
│ │ ServicioAuditoria                                   │   │
│ │ - generarInforme()                                   │   │
│ │ - adjuntarObservaciones()                            │   │
│ │                                                      │   │
│ │ ServicioEstados                                     │   │
│ │ - obtenerEstadosSiguientes()                         │   │
│ │ - esTransicionValida()                              │   │
│ └───────────────────────────────────────────────────────┘   │
│        ↓ Valida, orquesta, aplica reglas negocio          │
│ Llama a: DAOXX                                             │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ CAPA PERSISTENCIA (dao modules)                             │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ DocumentoDAOImpl                                      │   │
│ │ - findById(id) → Session.get(Documento.class, id)   │   │
│ │ - save(doc) → Session.saveOrUpdate(doc)             │   │
│ │ - findByEstado() → Query con HQL                    │   │
│ │                                                      │   │
│ │ ActuacionDAOImpl                                     │   │
│ │ UsuarioDAOImpl                                       │   │
│ │ FirmaElectronicaDAOImpl                              │   │
│ └───────────────────────────────────────────────────────┘   │
│        ↓ Usa Hibernate para traducir Java → SQL           │
│ Accede a: Base de Datos PostgreSQL                        │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ BASE DE DATOS (PostgreSQL)                                  │
│ ┌───────────────────────────────────────────────────────┐   │
│ │ DOCUMENTO          ACTUACION       USUARIO           │   │
│ │ ├─ id             ├─ id           ├─ id             │   │
│ │ ├─ titulo         ├─ documento_id ├─ nombre         │   │
│ │ ├─ estado         ├─ accion       ├─ rol_id         │   │
│ │ ├─ monto          ├─ fecha        └─ password       │   │
│ │ ├─ auditor_id     └─ usuario_id                     │   │
│ │ └─ tipo                                              │   │
│ │                                                      │   │
│ │ FIRMA_ELECTRONICA  INSTITUCION                      │   │
│ │ ├─ id             ├─ id                             │   │
│ │ ├─ documento_id   ├─ nombre                         │   │
│ │ ├─ usuario_id     └─ tipo_organismo                 │   │
│ │ └─ fecha_firma                                       │   │
│ └───────────────────────────────────────────────────────┘   │
│                                                             │
│ ÍNDICES: documento_estado, documento_auditor,              │
│          actuacion_documento, firma_documento              │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔌 INTEGRACIÓN CON SISTEMAS EXTERNOS

SIGEDO expone **APIs REST** para integración:

```
Sistema Externo
    ↓
GET /api/documentos?estado=CERTIFICADO
    ↓
SIGEDO devuelve JSON
    ↓
{
  "documentos": [
    {
      "id": 123,
      "titulo": "Rendición Subsidio 2024",
      "estado": "CERTIFICADO",
      "monto": 50000.00,
      "auditor": "Juan García",
      "fechaCertificacion": "2024-07-15"
    }
  ]
}
```

---

## 🎯 RESUMEN DE RESPONSABILIDADES

| Módulo | Responsabilidad | NO hace |
|--------|-----------------|---------|
| **web/** | Mostrar en navegador, recibir input usuario | Validaciones de negocio |
| **service-impl/** | Lógica, validaciones, reglas de negocio | Acceso a BD directo |
| **dao-impl/** | Guardar/recuperar datos con SQL/Hibernate | Validaciones de negocio |
| **model/** | Definir estructura de datos | Lógica, persistencia |
| **common/** | Herramientas compartidas | Lógica específica |

---

Esta es la **arquitectura profesional de capas** que SIGEDO implementa. Cada capa tiene responsabilidad clara, facilitando mantenimiento, testing y reutilización de código.
