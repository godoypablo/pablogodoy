# 🌐 SIGEDO: APIs REST EN PROFUNDIDAD

> Cómo otros sistemas se comunican con SIGEDO mediante APIs

---

## 📌 INTRODUCCIÓN A REST

**REST** = Representational State Transfer

Idea simple: Usar HTTP estándar para acceder a recursos.

```
GET     /api/documentos        → Obtener lista
POST    /api/documentos        → Crear nuevo
GET     /api/documentos/123    → Obtener por ID
PUT     /api/documentos/123    → Actualizar
DELETE  /api/documentos/123    → Eliminar
```

---

## 🏗️ ESTRUCTURA DE APIs EN SIGEDO

### Ubicación del Código

```
web/src/main/java/ar/gob/tcer/web/rest/
├── DocumentoAPI.java          ← Endpoints /api/documentos
├── AuditoriaAPI.java          ← Endpoints /api/auditorias
├── FirmaElectronicaAPI.java   ← Endpoints /api/firmas
├── UsuarioAPI.java            ← Endpoints /api/usuarios
├── ReportesAPI.java           ← Endpoints /api/reportes
└── dto/
    ├── DocumentoDTO.java       ← Datos para enviar/recibir
    ├── InformeDTO.java
    └── FirmaDTO.java
```

---

## 📋 API 1: DOCUMENTOS

### Endpoint: GET /api/documentos

**Obtener lista de documentos (con filtros)**

```java
@Path("/documentos")
@Produces(MediaType.APPLICATION_JSON)
public class DocumentoAPI {
    
    @Autowired
    private ServicioDocumento servicioDocumento;
    
    /**
     * Obtener lista de documentos con filtros opcionales
     * 
     * GET /api/documentos?estado=EN_AUDITORIA&auditor_id=42&limit=10
     */
    @GET
    public Response obtenerDocumentos(
        @QueryParam("estado") String estado,
        @QueryParam("auditor_id") Long auditorId,
        @QueryParam("institucion_id") Long institucionId,
        @QueryParam("fecha_desde") String fechaDesde,
        @QueryParam("fecha_hasta") String fechaHasta,
        @QueryParam("limit") @DefaultValue("50") Integer limit,
        @QueryParam("offset") @DefaultValue("0") Integer offset) {
        
        try {
            // PASO 1: Construir filtros
            DocumentoFiltro filtro = new DocumentoFiltro();
            
            if (estado != null) {
                filtro.setEstado(TipoEstadoDocumento.valueOf(estado));
            }
            
            if (auditorId != null) {
                filtro.setAuditorId(auditorId);
            }
            
            if (institucionId != null) {
                filtro.setInstitucionId(institucionId);
            }
            
            if (fechaDesde != null) {
                filtro.setFechaDesde(LocalDate.parse(fechaDesde));
            }
            
            if (fechaHasta != null) {
                filtro.setFechaHasta(LocalDate.parse(fechaHasta));
            }
            
            // PASO 2: Buscar documentos
            List<Documento> documentos = servicioDocumento.buscar(
                filtro, 
                limit, 
                offset
            );
            
            // PASO 3: Convertir a DTO (JSON)
            List<DocumentoDTO> dtos = documentos.stream()
                .map(d -> convertirADTO(d))
                .collect(Collectors.toList());
            
            // PASO 4: Retornar respuesta
            return Response
                .ok(new ListaDocumentosResponse(
                    dtos,
                    documentos.size(),
                    limit,
                    offset
                ))
                .build();
                
        } catch (Exception e) {
            return Response
                .status(Response.Status.INTERNAL_SERVER_ERROR)
                .entity(new ErrorResponse("Error al buscar documentos: " + e.getMessage()))
                .build();
        }
    }
    
    private DocumentoDTO convertirADTO(Documento d) {
        DocumentoDTO dto = new DocumentoDTO();
        dto.setId(d.getId());
        dto.setTitulo(d.getTitulo());
        dto.setMonto(d.getMonto());
        dto.setEstado(d.getEstado().toString());
        dto.setFechaIngreso(d.getFechaIngreso());
        dto.setAuditorNombre(d.getAuditor() != null ? 
            d.getAuditor().getNombre() : null);
        dto.setInstitucionNombre(d.getInstitucion() != null ? 
            d.getInstitucion().getNombre() : null);
        return dto;
    }
}
```

**Ejemplo de llamada:**

```bash
curl "http://localhost:8090/tcer/api/documentos?estado=EN_AUDITORIA&limit=10" \
  -H "Authorization: Bearer TOKEN_JWT"
```

**Respuesta (JSON):**

```json
{
  "documentos": [
    {
      "id": 1001,
      "titulo": "Rendición Anual Ministerio de Educación 2024",
      "monto": 1500000.00,
      "estado": "EN_AUDITORIA",
      "fechaIngreso": "2024-07-03",
      "auditorNombre": "Juan García",
      "institucionNombre": "Ministerio de Educación"
    },
    {
      "id": 1002,
      "titulo": "Rendición Subsidio Salud",
      "monto": 250000.00,
      "estado": "EN_AUDITORIA",
      "fechaIngreso": "2024-07-01",
      "auditorNombre": "María López",
      "institucionNombre": "Secretaría de Salud"
    }
  ],
  "total": 2,
  "limit": 10,
  "offset": 0
}
```

---

### Endpoint: GET /api/documentos/{id}

**Obtener un documento específico**

```java
/**
 * Obtener documento por ID
 * 
 * GET /api/documentos/1001
 */
@GET
@Path("{id}")
public Response obtenerDocumento(@PathParam("id") Long docId) {
    try {
        Documento documento = servicioDocumento.obtenerDocumento(docId);
        
        if (documento == null) {
            return Response
                .status(Response.Status.NOT_FOUND)
                .entity(new ErrorResponse("Documento no encontrado"))
                .build();
        }
        
        DocumentoDTO dto = convertirADTO(documento);
        return Response.ok(dto).build();
        
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    }
}
```

**Ejemplo:**

```bash
curl "http://localhost:8090/tcer/api/documentos/1001"
```

**Respuesta:**

```json
{
  "id": 1001,
  "titulo": "Rendición Anual Ministerio de Educación 2024",
  "descripcion": "Cuenta anual del año fiscal 2024",
  "monto": 1500000.00,
  "estado": "EN_AUDITORIA",
  "fechaIngreso": "2024-07-03",
  "auditorId": 42,
  "auditorNombre": "Juan García",
  "institucionId": 10,
  "institucionNombre": "Ministerio de Educación"
}
```

---

### Endpoint: POST /api/documentos

**Crear documento nuevo**

```java
/**
 * Crear nuevo documento
 * 
 * POST /api/documentos
 * Content-Type: application/json
 * Body: {
 *   "titulo": "Mi Rendición",
 *   "tipo": "RENDICION_ORGANISMO",
 *   "monto": 50000.00
 * }
 */
@POST
@Consumes(MediaType.APPLICATION_JSON)
public Response crearDocumento(DocumentoDTO documentoDTO) {
    try {
        // VALIDACIÓN 1: Usuario autenticado
        Usuario usuario = obtenerUsuarioActual();
        if (usuario == null) {
            return Response
                .status(Response.Status.UNAUTHORIZED)
                .build();
        }
        
        // VALIDACIÓN 2: Datos requeridos
        if (documentoDTO.getTitulo() == null || 
            documentoDTO.getTitulo().isEmpty()) {
            return Response
                .status(Response.Status.BAD_REQUEST)
                .entity(new ErrorResponse("Título es requerido"))
                .build();
        }
        
        // PASO 1: Crear objeto desde DTO
        Documento documento = new Documento();
        documento.setTitulo(documentoDTO.getTitulo());
        documento.setTipo(documentoDTO.getTipo());
        documento.setMonto(documentoDTO.getMonto());
        documento.setInstitucion(usuario.getInstitucion());
        
        // PASO 2: Guardar
        Documento documentoGuardado = servicioDocumento
            .crearDocumento(documento);
        
        // PASO 3: Retornar con el nuevo ID
        return Response
            .status(Response.Status.CREATED)
            .entity(convertirADTO(documentoGuardado))
            .header("Location", "/api/documentos/" + documentoGuardado.getId())
            .build();
            
    } catch (DocumentoDuplicadoException e) {
        return Response
            .status(Response.Status.CONFLICT)
            .entity(new ErrorResponse("Documento duplicado: " + e.getMessage()))
            .build();
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    }
}
```

**Ejemplo:**

```bash
curl -X POST "http://localhost:8090/tcer/api/documentos" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Rendición Trimestral 2024",
    "tipo": "RENDICION_ORGANISMO",
    "monto": 500000.00
  }'
```

**Respuesta:**

```json
{
  "id": 1003,
  "titulo": "Rendición Trimestral 2024",
  "tipo": "RENDICION_ORGANISMO",
  "monto": 500000.00,
  "estado": "INGRESADO"
}
```

---

### Endpoint: PUT /api/documentos/{id}

**Actualizar documento**

```java
/**
 * Actualizar documento existente
 * 
 * PUT /api/documentos/1001
 * Content-Type: application/json
 */
@PUT
@Path("{id}")
@Consumes(MediaType.APPLICATION_JSON)
public Response actualizarDocumento(
    @PathParam("id") Long docId,
    DocumentoDTO documentoDTO) {
    
    try {
        Documento documento = servicioDocumento.obtenerDocumento(docId);
        
        if (documento == null) {
            return Response
                .status(Response.Status.NOT_FOUND)
                .build();
        }
        
        // Actualizar solo campos permitidos
        if (documentoDTO.getTitulo() != null) {
            documento.setTitulo(documentoDTO.getTitulo());
        }
        if (documentoDTO.getMonto() != null) {
            documento.setMonto(documentoDTO.getMonto());
        }
        
        servicioDocumento.actualizarDocumento(documento);
        
        return Response.ok(convertirADTO(documento)).build();
        
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    }
}
```

---

### Endpoint: DELETE /api/documentos/{id}

**Eliminar documento**

```java
/**
 * Eliminar documento
 * 
 * DELETE /api/documentos/1001
 */
@DELETE
@Path("{id}")
public Response eliminarDocumento(@PathParam("id") Long docId) {
    try {
        servicioDocumento.eliminarDocumento(docId);
        return Response.noContent().build();
        
    } catch (DocumentoNoEncontradoException e) {
        return Response
            .status(Response.Status.NOT_FOUND)
            .build();
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    }
}
```

---

## 📋 API 2: CAMBIO DE ESTADO

### Endpoint: POST /api/documentos/{id}/cambiar-estado

```java
@Path("/documentos/{id}/cambiar-estado")
@POST
@Consumes(MediaType.APPLICATION_JSON)
public Response cambiarEstado(
    @PathParam("id") Long docId,
    CambioEstadoDTO cambio) {
    
    try {
        Usuario usuario = obtenerUsuarioActual();
        
        // Validar permisos
        if (!usuario.tieneRol(Rol.AUDITOR) && 
            !usuario.tieneRol(Rol.JEFE_DEPENDENCIA)) {
            return Response
                .status(Response.Status.FORBIDDEN)
                .entity(new ErrorResponse("No tienes permisos para cambiar estado"))
                .build();
        }
        
        // Cambiar estado
        servicioDocumento.cambiarEstado(
            docId,
            TipoEstadoDocumento.valueOf(cambio.getNuevoEstado()),
            cambio.getObservaciones()
        );
        
        return Response.ok(
            new SuccessResponse("Estado cambiado correctamente")
        ).build();
        
    } catch (EstadoInvalidoException e) {
        return Response
            .status(Response.Status.BAD_REQUEST)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    }
}
```

**Ejemplo:**

```bash
curl -X POST "http://localhost:8090/tcer/api/documentos/1001/cambiar-estado" \
  -H "Content-Type: application/json" \
  -d '{
    "nuevoEstado": "PENDIENTE_INFORME",
    "observaciones": "Documento revisado correctamente"
  }'
```

---

## 📋 API 3: AUDITORÍA

### Endpoint: GET /api/documentos/{id}/auditoria

**Obtener historial completo de un documento**

```java
@Path("/documentos/{id}/auditoria")
@GET
public Response obtenerHistorialAuditoria(@PathParam("id") Long docId) {
    try {
        List<Actuacion> acciones = servicioAuditoria
            .obtenerActuacionesDelDocumento(docId);
        
        List<ActuacionDTO> dtos = acciones.stream()
            .map(a -> new ActuacionDTO(
                a.getFecha().toString() + " " + a.getHora().toString(),
                a.getUsuario().getNombre(),
                a.getAccion(),
                a.getEstadoAnterior(),
                a.getEstadoNuevo()
            ))
            .collect(Collectors.toList());
        
        return Response.ok(new HistorialResponse(dtos)).build();
        
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .entity(new ErrorResponse(e.getMessage()))
            .build();
    }
}
```

**Respuesta:**

```json
{
  "acciones": [
    {
      "cuandoFechaHora": "2024-07-08 14:30:00",
      "quienNombre": "Carlos García",
      "queAccion": "Estado cambiado",
      "estadoAnterior": "EN_AUDITORIA",
      "estadoNuevo": "PENDIENTE_INFORME"
    },
    {
      "cuandoFechaHora": "2024-07-07 09:15:30",
      "quienNombre": "Juan García",
      "queAccion": "Asignado como auditor",
      "estadoAnterior": "INGRESADO",
      "estadoNuevo": "CARATULADO"
    }
  ]
}
```

---

## 📊 API 4: REPORTES

### Endpoint: GET /api/reportes/estado-auditores

```java
@Path("/reportes/estado-auditores")
@GET
@Produces(MediaType.APPLICATION_JSON)
public Response reporteEstadoAuditores() {
    try {
        ReporteEstadoAuditores reporte = servicioReportes
            .generarReporteEstadoAuditores();
        
        return Response.ok(reporte).build();
        
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .build();
    }
}
```

**Respuesta:**

```json
{
  "fecha": "2024-07-08",
  "auditores": [
    {
      "id": 42,
      "nombre": "Juan García",
      "documentosAsignados": 15,
      "enAuditoria": 8,
      "completados": 5,
      "pendientes": 2,
      "demoraPromedioDias": 5.3
    },
    {
      "id": 43,
      "nombre": "María López",
      "documentosAsignados": 12,
      "enAuditoria": 6,
      "completados": 4,
      "pendientes": 2,
      "demoraPromedioDias": 4.8
    }
  ]
}
```

---

## 🔐 AUTENTICACIÓN Y AUTORIZACIÓN

### JWT (JSON Web Token)

```java
@Path("/auth/login")
@POST
@Consumes(MediaType.APPLICATION_JSON)
public Response login(CredencialesDTO credenciales) {
    try {
        Usuario usuario = servicioUsuario.autenticar(
            credenciales.getEmail(),
            credenciales.getPassword()
        );
        
        if (usuario == null) {
            return Response
                .status(Response.Status.UNAUTHORIZED)
                .entity(new ErrorResponse("Email o contraseña inválidos"))
                .build();
        }
        
        // Generar JWT token
        String token = generarJWT(usuario);
        
        return Response.ok(new LoginResponse(token, usuario)).build();
        
    } catch (Exception e) {
        return Response
            .status(Response.Status.INTERNAL_SERVER_ERROR)
            .build();
    }
}

private String generarJWT(Usuario usuario) {
    return JWT.create()
        .withSubject(usuario.getEmail())
        .withClaim("user_id", usuario.getId())
        .withClaim("roles", usuario.getRoles().stream()
            .map(Rol::getNombre)
            .collect(Collectors.toList()))
        .withExpiresAt(new Date(System.currentTimeMillis() + 
            86400000))  // 24 horas
        .sign(Algorithm.HMAC256(SECRET_KEY));
}
```

**Ejemplo de uso:**

```bash
# 1. Obtener token
curl -X POST "http://localhost:8090/tcer/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "juan@tcer.gov.ar",
    "password": "miContraseña123"
  }'

# Respuesta
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "usuario": {
    "id": 42,
    "nombre": "Juan García"
  }
}

# 2. Usar token en próximas peticiones
curl "http://localhost:8090/tcer/api/documentos" \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
```

---

## ⚙️ CÓDIGOS DE RESPUESTA HTTP

| Código | Significado | Cuándo |
|--------|-----------|--------|
| **200** | OK | Petición exitosa, devuelve datos |
| **201** | Created | Recurso creado exitosamente |
| **204** | No Content | Operación exitosa, sin datos en respuesta |
| **400** | Bad Request | Datos inválidos (ej: monto negativo) |
| **401** | Unauthorized | No autenticado |
| **403** | Forbidden | Autenticado pero sin permisos |
| **404** | Not Found | Recurso no existe |
| **409** | Conflict | Conflicto (ej: documento duplicado) |
| **500** | Server Error | Error interno del servidor |

---

## 🧪 EJEMPLOS DE CLIENTES

### Cliente: JavaScript/Fetch

```javascript
// Obtener documentos
fetch('http://localhost:8090/tcer/api/documentos?estado=EN_AUDITORIA', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));

// Crear documento
fetch('http://localhost:8090/tcer/api/documentos', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    titulo: 'Nueva Rendición',
    tipo: 'RENDICION_ORGANISMO',
    monto: 100000.00
  })
})
.then(response => response.json())
.then(data => console.log('Creado:', data.id))
.catch(error => console.error('Error:', error));
```

### Cliente: Python/requests

```python
import requests
import json

BASE_URL = "http://localhost:8090/tcer/api"
headers = {"Authorization": f"Bearer {token}"}

# GET lista
response = requests.get(
    f"{BASE_URL}/documentos?estado=EN_AUDITORIA",
    headers=headers
)
documentos = response.json()

# POST crear
nueva_documento = {
    "titulo": "Rendición Especial",
    "tipo": "RENDICION_SUBSIDIO",
    "monto": 50000.00
}
response = requests.post(
    f"{BASE_URL}/documentos",
    json=nueva_documento,
    headers=headers
)
print(f"Creado con ID: {response.json()['id']}")

# PUT actualizar
actualizar = {"titulo": "Título Actualizado"}
response = requests.put(
    f"{BASE_URL}/documentos/1001",
    json=actualizar,
    headers=headers
)

# DELETE
response = requests.delete(
    f"{BASE_URL}/documentos/1001",
    headers=headers
)
```

---

SIGEDO expone completamente su funcionalidad mediante APIs REST, permitiendo integración con sistemas externos.
