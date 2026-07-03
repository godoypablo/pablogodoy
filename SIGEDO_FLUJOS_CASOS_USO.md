# 🔄 SIGEDO: FLUJOS DE TRABAJO Y CASOS DE USO

> Entender exactamente qué ocurre en situaciones reales, paso a paso, qué código se ejecuta

---

## 📋 ÍNDICE DE FLUJOS

1. [Flujo 1: Ingreso de Documento](#flujo-1-ingreso-de-documento)
2. [Flujo 2: Auditoría Completa](#flujo-2-auditoría-completa)
3. [Flujo 3: Cambio de Estado](#flujo-3-cambio-de-estado)
4. [Flujo 4: Firma Digital](#flujo-4-firma-digital)
5. [Flujo 5: Generación de Reportes](#flujo-5-generación-de-reportes)
6. [Flujo 6: Gestión de Permisos](#flujo-6-gestión-de-permisos)

---

## 🔴 FLUJO 1: INGRESO DE DOCUMENTO

### Escenario Real
> "Un ministerio envía su Rendición Anual 2024 a través del sistema web"

### Paso 1: Usuario Externo Accede a Formulario

**Actor:** Usuario de organismo gubernamental
**Componente:** `PaginaCargaDocumento.java`
**URL:** `http://localhost:8090/tcer/cargar-documento`

```java
public class PaginaCargaDocumento extends WebPage {
    
    @Autowired
    private ServicioDocumento servicioDocumento;
    
    public PaginaCargaDocumento() {
        Form<DocumentoDTO> form = new Form<DocumentoDTO>(
            "formulario", new Model<>(new DocumentoDTO())) {
            
            @Override
            protected void onSubmit() {
                DocumentoDTO dto = this.getModelObject();
                procesarCarga(dto);
            }
        };
        
        // CAMPO 1: Tipo de documento
        DropDownChoice<String> tipo = new DropDownChoice<>(
            "tipo",
            Arrays.asList(
                "RENDICION_ORGANISMO",
                "RENDICION_SUBSIDIO",
                "LEGAJO_RENDITIVO"
            )
        );
        
        // CAMPO 2: Título
        TextField<String> titulo = new TextField<>("titulo");
        
        // CAMPO 3: Monto
        TextField<BigDecimal> monto = new TextField<>("monto");
        
        // CAMPO 4: Archivo adjunto
        FileUploadField archivo = new FileUploadField("archivo");
        
        form.add(tipo);
        form.add(titulo);
        form.add(monto);
        form.add(archivo);
        add(form);
    }
}
```

### Paso 2: Usuario Llena Datos y Hace Clic en "Enviar"

**Formulario enviado:**
```
Tipo: RENDICION_ORGANISMO
Título: Rendición Anual Ministerio de Educación 2024
Monto: 1500000.00
Archivo: Planilla_Gastos_2024.pdf
```

### Paso 3: Validación en Wicket (Presentación)

**Archivo:** `PaginaCargaDocumento.java`
**Código:**

```java
protected void onSubmit() {
    DocumentoDTO dto = form.getModelObject();
    
    // VALIDACIÓN 1: Campos no vacíos
    if (dto.getTitulo() == null || dto.getTitulo().isEmpty()) {
        error("El título es requerido");
        return;
    }
    
    // VALIDACIÓN 2: Tipo válido
    if (!Arrays.asList("RENDICION_ORGANISMO", "RENDICION_SUBSIDIO")
        .contains(dto.getTipo())) {
        error("Tipo de documento inválido");
        return;
    }
    
    // VALIDACIÓN 3: Monto positivo
    if (dto.getMonto().compareTo(BigDecimal.ZERO) <= 0) {
        error("El monto debe ser mayor a 0");
        return;
    }
    
    // Si todas las validaciones pasaron:
    try {
        Documento documento = servicioDocumento.crearDocumento(dto);
        setResponsePage(new PaginaExitoDocumento(documento));
    } catch (Exception e) {
        error("Error al procesar: " + e.getMessage());
    }
}
```

### Paso 4: Servicios (Lógica de Negocio)

**Archivo:** `service-impl/ServicioDocumentoImpl.java`

```java
@Service
@Transactional  // ← Todo o nada
public class ServicioDocumentoImpl implements ServicioDocumento {
    
    @Autowired
    private DocumentoDAO documentoDAO;
    
    @Autowired
    private ActuacionDAO actuacionDAO;
    
    @Autowired
    private ServicioEstados servicioEstados;
    
    @Override
    public Documento crearDocumento(DocumentoDTO dto) {
        
        // PASO 1: Validaciones de negocio
        validarDocumentoNuevo(dto);
        
        // PASO 2: Crear instancia de Documento
        Documento documento = new Documento();
        documento.setTitulo(dto.getTitulo());
        documento.setMonto(dto.getMonto());
        documento.setTipo(dto.getTipo());
        documento.setFechaIngreso(LocalDate.now());
        documento.setEstado(TipoEstadoDocumento.INGRESADO);
        
        // PASO 3: ¿Es Rendición? → Buscar organismo
        if ("RENDICION_ORGANISMO".equals(dto.getTipo())) {
            Institucion institucion = buscarInstitucionPorUsuario();
            documento.setInstitucion(institucion);
        }
        
        // PASO 4: Guardar en BD
        Documento documentoGuardado = documentoDAO.save(documento);
        
        // PASO 5: Registrar en auditoría
        registrarAccion(documentoGuardado, "Documento ingresado", 
            getUsuarioActual());
        
        // PASO 6: Generar notificaciones
        generarNotificacionesAuditores(documentoGuardado);
        
        return documentoGuardado;
    }
    
    private void validarDocumentoNuevo(DocumentoDTO dto) {
        // ¿Ya existe documento similar?
        if (documentoDAO.findByTituloYMes(dto.getTitulo(), 
            LocalDate.now()) != null) {
            throw new DocumentoDuplicadoException(
                "Ya existe documento similar este mes"
            );
        }
        
        // ¿Usuario tiene permiso?
        if (!getUsuarioActual().tieneRol(Rol.USUARIO_EXTERNO)) {
            throw new PermisoDenegadoException(
                "Solo usuarios externos pueden ingresar documentos"
            );
        }
    }
    
    private void registrarAccion(Documento doc, String descripcion, 
        Usuario usuario) {
        Actuacion accion = new Actuacion();
        accion.setDocumento(doc);
        accion.setAccion(descripcion);
        accion.setFecha(LocalDate.now());
        accion.setHora(LocalTime.now());
        accion.setUsuario(usuario);
        accion.setDireccionIP(getIPDelUsuario());
        
        actuacionDAO.save(accion);
    }
    
    private void generarNotificacionesAuditores(Documento doc) {
        List<Usuario> auditoresDisponibles = 
            servicioUsuario.obtenerAuditoresDisponibles();
        
        for (Usuario auditor : auditoresDisponibles) {
            EnviadorNotificaciones.enviarEmail(
                auditor.getEmail(),
                "Nuevo documento ingresado",
                "Tienes nuevo documento: " + doc.getTitulo()
            );
        }
    }
}
```

### Paso 5: Persistencia en BD (DAO)

**Archivo:** `dao-impl/DocumentoDAOImpl.java`

```java
@Repository
public class DocumentoDAOImpl implements DocumentoDAO {
    
    @Autowired
    private SessionFactory sessionFactory;
    
    @Override
    public Documento save(Documento documento) {
        Session session = sessionFactory.getCurrentSession();
        
        // Hibernate convierte:
        // Objeto Java → INSERT SQL
        Long id = (Long) session.save(documento);
        
        // Obtener el objeto con el ID generado
        documento.setId(id);
        return documento;
    }
}
```

**SQL que Hibernate genera internamente:**

```sql
INSERT INTO DOCUMENTO (
    titulo, 
    monto, 
    tipo, 
    estado, 
    fecha_ingreso, 
    institucion_id, 
    created_at
) VALUES (
    'Rendición Anual Ministerio de Educación 2024',
    1500000.00,
    'RENDICION_ORGANISMO',
    'INGRESADO',
    '2024-07-03',
    42,
    NOW()
);
```

### Paso 6: Registrar Acción en Auditoría

```sql
INSERT INTO ACTUACION (
    documento_id,
    accion,
    fecha,
    hora,
    usuario_id,
    direccion_ip
) VALUES (
    1001,
    'Documento ingresado',
    '2024-07-03',
    '14:23:45',
    201,
    '192.168.1.100'
);
```

### Paso 7: Respuesta al Usuario

```
✅ ÉXITO!

Documento ingresado exitosamente.
ID: 1001
Titulo: Rendición Anual Ministerio de Educación 2024
Estado: INGRESADO
Próxima acción: Será asignado a un auditor en las próximas horas
```

### Resumen: De Principio a Fin

```
Usuario web (UI)
    ↓ Llena formulario, hace clic
Wicket (Presentación)
    ↓ Valida formato, llama servicio
ServicioDocumento (Lógica)
    ↓ Valida reglas negocio, crea objeto, ordena guardar
DocumentoDAO (Persistencia)
    ↓ Usa Hibernate para INSERT SQL
PostgreSQL (BD)
    ↓ Inserta fila en DOCUMENTO
    ↓ Genera ACTUACION (auditoría)
    ↓ Retorna confirmación
ServicioDocumento
    ↓ Genera notificaciones
Wicket
    ↓ Muestra página de éxito
Usuario web ← ¡Documento creado con ID 1001!
```

---

## 🔵 FLUJO 2: AUDITORÍA COMPLETA

### Escenario Real
> "Auditor Juan recibe documento asignado, lo revisa, genera informe y lo envía a su jefe"

### Día 1 - Mañana: Documento Llega

```
SIGEDO automáticamente:
1. Busca auditores disponibles
2. Asigna Juan como auditor
3. Cambia estado de INGRESADO → CARATULADO
4. Envía email a Juan: "Tienes nueva tarea"
```

**Código que lo hace:**

```java
// En ServicioDocumentoImpl.java
private void asignarAuditorAutomaticamente(Documento doc) {
    Usuario auditorDisponible = 
        servicioUsuario.obtenerAuditorConMenosCarga();
    
    doc.setAuditor(auditorDisponible);
    documentoDAO.save(doc);
    
    // Registrar acción
    registrarAccion(doc, 
        "Asignado a " + auditorDisponible.getNombre(), 
        null);  // Sistema
    
    // Enviar notificación
    EnviadorNotificaciones.enviarEmail(
        auditorDisponible.getEmail(),
        "Documento asignado: " + doc.getTitulo()
    );
}
```

### Día 2: Juan Abre el Documento

**Página:** `PaginaDetalleDocumento.java`

```java
public class PaginaDetalleDocumento extends WebPage {
    
    public PaginaDetalleDocumento(Long documentoId) {
        Documento doc = servicioDocumento.obtenerDocumento(documentoId);
        
        // Mostrar información del documento
        add(new Label("titulo", doc.getTitulo()));
        add(new Label("monto", doc.getMonto().toString()));
        add(new Label("estado", doc.getEstado().toString()));
        
        // SECCIÓN 1: Historial de acciones
        List<Actuacion> acciones = servicioAuditoria
            .obtenerActuacionesDelDocumento(doc);
        
        add(new ListView<Actuacion>("acciones", acciones) {
            @Override
            protected void populateItem(ListItem<Actuacion> item) {
                Actuacion accion = item.getModelObject();
                item.add(new Label("cuando", 
                    accion.getFecha() + " " + accion.getHora()));
                item.add(new Label("quien", accion.getUsuario().getNombre()));
                item.add(new Label("que", accion.getAccion()));
            }
        });
        
        // SECCIÓN 2: Generar Informe de Auditor
        Form<InformeAuditorDTO> formInforme = 
            new Form<>("formInforme") {
            @Override
            protected void onSubmit() {
                generarInforme(doc);
            }
        };
        
        TextArea<String> conclusiones = new TextArea<>(
            "conclusiones");
        TextArea<String> observaciones = new TextArea<>(
            "observaciones");
        
        Button generar = new Button("generar");
        
        formInforme.add(conclusiones);
        formInforme.add(observaciones);
        formInforme.add(generar);
        add(formInforme);
    }
    
    private void generarInforme(Documento doc) {
        String conclusiones = getRequest().getParameter("conclusiones");
        String observaciones = getRequest().getParameter("observaciones");
        
        servicioAuditoria.generarInformeAuditor(
            doc,
            conclusiones,
            observaciones,
            getUsuarioActual()
        );
        
        info("Informe generado exitosamente");
    }
}
```

### Días 3-5: Auditor Hace Análisis

**Acciones de Juan:**

1. **Descarga documentos adjuntos**
   - Lee planilla de gastos
   - Verifica montos
   - Busca inconsistencias

2. **Cambia estado a EN_AUDITORÍA**

```java
// En ServicioEstadosImpl
public void cambiarEstado(Long docId, TipoEstadoDocumento nuevoEstado) {
    Documento doc = documentoDAO.findById(docId);
    
    // VALIDACIÓN: ¿Es transición legal?
    ConfiguracionClasificacionEstadoDocumento config = 
        servicioEstados.obtenerConfiguracion(
            doc.getTipo(),
            doc.getEstado(),
            nuevoEstado
        );
    
    if (config == null || !config.esTransicionPermitida()) {
        throw new EstadoInvalidoException(
            "No se puede ir de " + doc.getEstado() + 
            " a " + nuevoEstado
        );
    }
    
    // Cambiar estado
    doc.setEstado(nuevoEstado);
    documentoDAO.save(doc);
    
    // Registrar acción
    registrarAccion(doc, "Estado cambiado a " + nuevoEstado, 
        getUsuarioActual());
}
```

3. **Escribe observaciones**

```
OBSERVACIONES:
- Gasto en "Capacitación Docentes" = $50,000 no está autorizado en presupuesto
- Falta comprobante de pago para 3 documentos
- Monto total coincide con declaración

RECOMENDACION:
- Solicitar re-aprobación del gasto de capacitación
- Investigar 3 pagos faltantes
```

4. **Genera Informe**

```java
// ServicioAuditoriaImpl.java
@Override
@Transactional
public InformeAuditor generarInformeAuditor(
    Documento documento,
    String conclusiones,
    String observaciones,
    Usuario auditor) {
    
    InformeAuditor informe = new InformeAuditor();
    informe.setDocumento(documento);
    informe.setAuditor(auditor);
    informe.setConclusiones(conclusiones);
    informe.setObservaciones(observaciones);
    informe.setFechaGeneracion(LocalDate.now());
    informe.setEstado(EstadoInforme.PENDIENTE_REVISION);
    
    informeDAO.save(informe);
    
    // Cambiar estado a INFORME_GENERADO
    documento.setEstado(TipoEstadoDocumento.INFORME_GENERADO);
    documentoDAO.save(documento);
    
    registrarAccion(documento, "Informe de auditor generado", auditor);
    
    // Notificar a jefe
    notificarJefeSobreNuevoInforme(informe);
    
    return informe;
}
```

### Día 6: Jefe Revisa

**Página:** `PaginaRevisionInformes.java`

El jefe ve el informe de Juan:

```
Documento: Rendición Anual Ministerio de Educación 2024
Auditor: Juan García
Monto: $1,500,000
Conclusiones: Conforme con observaciones

Observaciones:
- Gasto en "Capacitación Docentes" = $50,000 no está autorizado
- Faltan 3 comprobantes

Recomendación:
- Solicitar re-aprobación de capacitación
- Investigar pagos faltantes

[BOTÓN: APROBAR] [BOTÓN: RECHAZAR] [BOTÓN: PEDIR_CORRECCIONES]
```

El jefe hace clic en **APROBAR**:

```java
@Override
public void aprobarInforme(Long informeId) {
    InformeAuditor informe = informeDAO.findById(informeId);
    Documento documento = informe.getDocumento();
    
    informe.setEstado(EstadoInforme.APROBADO);
    informe.setFechaAprobacion(LocalDate.now());
    informeDAO.save(informe);
    
    documento.setEstado(TipoEstadoDocumento.APROBADO);
    documentoDAO.save(documento);
    
    registrarAccion(documento, 
        "Informe aprobado por " + getUsuarioActual().getNombre(),
        getUsuarioActual());
}
```

### Día 7: Sistema Automáticamente Pide Firma

**Flujo automático:**

```java
// Job programado (corre cada 2 horas)
@Scheduled(fixedRate = 7200000)  // 2 horas
public void verificarDocumentosPendienteFirma() {
    List<Documento> documentosParaFirmar = 
        documentoDAO.findByEstado(
            TipoEstadoDocumento.PENDIENTE_CERTIFICACION
        );
    
    for (Documento doc : documentosParaFirmar) {
        Usuario jefe = doc.getJefeDependencia();
        
        // Solicitar firma
        servicioFirmaElectronica.solicitarFirma(doc, jefe);
        
        // Registrar
        registrarAccion(doc, 
            "Solicitada firma a " + jefe.getNombre(),
            null);
    }
}
```

---

## 🟢 FLUJO 3: CAMBIO DE ESTADO

### Estados Configurables

**Base de Datos: Tabla CONFIGURACION_CLASIFICACION_ESTADO_DOCUMENTO**

```sql
SELECT * FROM CONFIGURACION_CLASIFICACION_ESTADO_DOCUMENTO
WHERE tipo_documento = 'RENDICION_ORGANISMO'
  AND estado_origen = 'EN_AUDITORIA';

Resultado:
estado_origen      | estado_destino          | permitido | requiere_jefe
EN_AUDITORIA       | PENDIENTE_INFORME       | true      | false
EN_AUDITORIA       | PENDIENTE_CORRECCIONES  | true      | false
EN_AUDITORIA       | ARCHIVADO               | true      | true
EN_AUDITORIA       | CERTIFICADO             | false     | (no importa)
```

### Validación de Transiciones

```java
@Override
public boolean esTransicionValida(
    Documento documento,
    TipoEstadoDocumento estadoDestino) {
    
    ConfiguracionClasificacionEstadoDocumento config = 
        configDAO.find(
            documento.getTipo(),           // Tipo de documento
            documento.getEstado(),         // Estado actual
            estadoDestino                  // Estado destino
        );
    
    if (config == null || !config.esPermitido()) {
        return false;
    }
    
    // Validaciones adicionales
    if (config.requiereAprobacionJefe()) {
        if (getUsuarioActual().getRol() != Rol.JEFE_DEPENDENCIA) {
            return false;  // Solo jefe puede
        }
    }
    
    return true;
}
```

---

## 🟠 FLUJO 4: FIRMA DIGITAL

### Sistema de Monitoreo (ProcesoLento)

**Problema:** Preguntar cada 100ms si hay firmas pendientes = 36,000 requests/hora (muy lento)

**Solución:** Usar `ProcesoLento` para preguntar cada 120 segundos

```java
public class PaginaControlFirmaElectronica extends WebPage {
    
    @Autowired
    private ServicioFirmaElectronica servicioFirma;
    
    private ProcesoLento procesoLento = new ProcesoLento() {
        @Override
        protected void ejecutar() {
            verificarFirmasPendientes();
        }
    };
    
    public PaginaControlFirmaElectronica() {
        // Cada 120 segundos, revisar si hay firmas
        procesoLento.ejecutarProcesoLentoEnXDecimasDeSegundos(1200);
        
        add(new AjaxLink("actualizarAhora") {
            @Override
            public void onClick(AjaxRequestTarget target) {
                // Usuario hace clic manualmente
                verificarFirmasPendientes();
                target.add(PaginaControlFirmaElectronica.this);
            }
        });
    }
    
    private void verificarFirmasPendientes() {
        List<FirmaElectronica> pendientes = 
            servicioFirma.obtenerFirmasPendientes(getUsuarioActual());
        
        for (FirmaElectronica firma : pendientes) {
            Label label = new Label("firma", 
                "Espera tu firma: " + firma.getDocumento().getTitulo());
            add(label);
        }
    }
}
```

### Proceso de Firma

```
1. Documento llega a estado PENDIENTE_CERTIFICACION

2. SIGEDO automáticamente solicita firma al jefe
   → Email: "Tienes documento pendiente de firma"

3. Jefe abre página de firmas

4. Ve lista:
   - Rendición Ministerio Educación - $1,500,000
   - Rendición Subsidio Salud - $250,000
   - [BOTÓN: FIRMAR]

5. Jefe hace clic en FIRMAR

6. Se abre aplicación de firma digital externa
   → Pide contraseña del certificado digital

7. Jefe ingresa contraseña

8. Se firma digitalmente el PDF

9. SIGEDO registra:
   - Quién firmó: Carlos García (Jefe)
   - Cuándo: 2024-07-08 10:30:15
   - Qué: ID Documento 1001
   - Con qué: Certificado A1 - CNE

10. Documento pasa a CERTIFICADO

11. PDF generado con firma + timestamp
    → Usuario externo recibe notificación
    → Puede descargar PDF oficial
```

**Código:**

```java
@Override
@Transactional
public void registrarFirmaElectronica(
    Long documentoId,
    FirmaElectronicaDTO firmaData) {
    
    Documento documento = documentoDAO.findById(documentoId);
    Usuario jefe = getUsuarioActual();
    
    // Crear registro de firma
    FirmaElectronica firma = new FirmaElectronica();
    firma.setDocumento(documento);
    firma.setJefeFirma(jefe);
    firma.setFechaFirma(LocalDateTime.now());
    firma.setCertificadoUtilizado(firmaData.getNumeroCertificado());
    firma.setHashFirma(firmaData.getHashFirma());
    firma.setEstado(EstadoFirma.COMPLETADA);
    
    firmaDAO.save(firma);
    
    // Cambiar estado de documento
    documento.setEstado(TipoEstadoDocumento.CERTIFICADO);
    documento.setFechaCertificacion(LocalDate.now());
    documentoDAO.save(documento);
    
    // Generar PDF certificado
    byte[] pdfCertificado = generarPDFCertificado(documento, firma);
    guardarPDFEnSistema(documentoId, pdfCertificado);
    
    // Registrar en auditoría
    registrarAccion(documento, 
        "Documento firmado digitalmente por " + jefe.getNombre(),
        jefe);
    
    // Notificar a usuario externo
    enviarNotificacionCertificacion(documento);
}
```

---

## 🟣 FLUJO 5: GENERACIÓN DE REPORTES

### Reporte: "Estado por Auditor"

**Página:** `PaginaReporteEstadoAuditores.java`

```java
public class PaginaReporteEstadoAuditores extends WebPage {
    
    @Autowired
    private ServicioReportes servicioReportes;
    
    public PaginaReporteEstadoAuditores() {
        // Parámetros
        Label fecha = new Label("fecha", LocalDate.now().toString());
        
        // Generar datos
        ReporteEstadoAuditores reporte = 
            servicioReportes.generarReporteEstadoAuditores();
        
        // Mostrar tabla
        add(new ListView<AuditorEstado>("auditores", 
            reporte.getAuditores()) {
            @Override
            protected void populateItem(ListItem<AuditorEstado> item) {
                AuditorEstado estado = item.getModelObject();
                
                item.add(new Label("nombre", estado.getNombre()));
                item.add(new Label("documentosAsignados", 
                    estado.getCantidadAsignados()));
                item.add(new Label("enAuditoria", 
                    estado.getCantidadEnAuditoria()));
                item.add(new Label("completados", 
                    estado.getCantidadCompletados()));
                item.add(new Label("pendientes", 
                    estado.getCantidadPendientes()));
                item.add(new Label("demoraPromedio", 
                    estado.getDemoraPromedioDias() + " días"));
            }
        });
        
        // Botón exportar a PDF
        add(new Link("exportarPDF") {
            @Override
            public void onClick() {
                byte[] pdf = servicioReportes
                    .exportarReporteAPDF(reporte);
                descargarArchivo("reporte_auditores.pdf", pdf);
            }
        });
    }
}
```

**SQL que se ejecuta:**

```sql
SELECT 
    u.nombre,
    COUNT(d.id) AS documentos_asignados,
    COUNT(CASE WHEN d.estado = 'EN_AUDITORIA' THEN 1 END) AS en_auditoria,
    COUNT(CASE WHEN d.estado = 'CERTIFICADO' THEN 1 END) AS completados,
    COUNT(CASE WHEN d.estado != 'CERTIFICADO' THEN 1 END) AS pendientes,
    ROUND(AVG(EXTRACT(DAY FROM NOW() - d.fecha_ingreso)), 1) AS demora_promedio
FROM DOCUMENTO d
JOIN USUARIO u ON d.auditor_id = u.id
WHERE d.auditor_id IS NOT NULL
GROUP BY u.id, u.nombre
ORDER BY demora_promedio DESC
```

---

## 🟡 FLUJO 6: GESTIÓN DE PERMISOS

### Control de Acceso Basado en Roles (RBAC)

```java
public class PaginaBusquedaDocumento extends WebPage {
    
    public PaginaBusquedaDocumento() {
        Usuario usuario = getUsuarioActual();
        
        // AUDITOR: Solo ve sus documentos asignados
        if (usuario.tieneRol(Rol.AUDITOR)) {
            List<Documento> misDocumentos = servicioDocumento
                .obtenerDocumentosAsignados(usuario);
            mostrarDocumentos(misDocumentos);
        }
        
        // JEFE: Ve documentos de su dependencia
        if (usuario.tieneRol(Rol.JEFE_DEPENDENCIA)) {
            List<Documento> documentosDependencia = servicioDocumento
                .obtenerDocumentosDependencia(usuario.getDependencia());
            mostrarDocumentos(documentosDependencia);
        }
        
        // ADMIN: Ve todos los documentos
        if (usuario.tieneRol(Rol.ADMIN)) {
            List<Documento> todosLosDocumentos = servicioDocumento
                .obtenerTodosLosDocumentos();
            mostrarDocumentos(todosLosDocumentos);
        }
        
        // USUARIO_EXTERNO: Solo ve sus propios documentos
        if (usuario.tieneRol(Rol.USUARIO_EXTERNO)) {
            List<Documento> misRendiciones = servicioDocumento
                .obtenerRendicionesDelOrganismo(usuario.getInstitucion());
            mostrarDocumentos(misRendiciones);
        }
    }
}
```

### Validación de Permisos en Servicios

```java
@Override
public void cambiarEstado(Long docId, TipoEstadoDocumento nuevoEstado) {
    Usuario usuario = getUsuarioActual();
    Documento documento = documentoDAO.findById(docId);
    
    // VALIDACIÓN 1: ¿Usuario tiene acceso a este documento?
    if (!puedeLeerDocumento(usuario, documento)) {
        throw new PermisoDenegadoException("No tienes acceso a este documento");
    }
    
    // VALIDACIÓN 2: ¿Usuario tiene permiso para cambiar estado?
    if (!puedeModificarDocumento(usuario, documento)) {
        throw new PermisoDenegadoException(
            "No tienes permiso para modificar este documento"
        );
    }
    
    // VALIDACIÓN 3: ¿La transición es válida?
    if (!servicioEstados.esTransicionValida(documento, nuevoEstado)) {
        throw new EstadoInvalidoException(
            "Transición no permitida: " + documento.getEstado() + 
            " → " + nuevoEstado
        );
    }
    
    // ... resto de la lógica
}

private boolean puedeLeerDocumento(Usuario usuario, Documento documento) {
    if (usuario.tieneRol(Rol.ADMIN)) return true;
    if (usuario.tieneRol(Rol.AUDITOR) && 
        usuario.equals(documento.getAuditor())) return true;
    if (usuario.tieneRol(Rol.JEFE_DEPENDENCIA) &&
        usuario.getDependencia().equals(documento.getDependencia()))
        return true;
    if (usuario.tieneRol(Rol.USUARIO_EXTERNO) &&
        usuario.getInstitucion().equals(documento.getInstitucion()))
        return true;
    return false;
}

private boolean puedeModificarDocumento(Usuario usuario, Documento documento) {
    if (usuario.tieneRol(Rol.ADMIN)) return true;
    if (usuario.tieneRol(Rol.AUDITOR) && 
        usuario.equals(documento.getAuditor())) return true;
    if (usuario.tieneRol(Rol.JEFE_DEPENDENCIA)) return true;
    return false;
}
```

---

## 🎯 MATRIZ DE FLUJOS

| Flujo | Duración | Actores | Estados | Registros |
|-------|----------|--------|--------|-----------|
| **Ingreso** | Minutos | Usuario externo, Sistema | INGRESADO → CARATULADO | Documento, Actuación |
| **Auditoría** | Días (2-5) | Auditor, Jefe | EN_AUDITORÍA → INFORME_GENERADO | Actuación, InformeAuditor |
| **Revisión** | Minutos | Jefe | INFORME_GENERADO → APROBADO | Actuación |
| **Firma** | Minutos | Jefe | PENDIENTE_CERTIFICACIÓN → CERTIFICADO | FirmaElectronica, Actuación |
| **Archivo** | Automático | Sistema | CERTIFICADO → ARCHIVADO | Actuación |

---

Cada flujo está completamente registrado en **auditoría**: quién, qué, cuándo, por qué, desde dónde.
