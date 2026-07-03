# 💻 SIGEDO: GUÍA DE DESARROLLO PRÁCTICO

> Cómo hacer cambios reales en SIGEDO paso a paso

---

## 🎯 CASO PRÁCTICO: "Agregar campo de Teléfono a Usuario"

Seguiremos exactamente qué archivos editar, línea por línea.

### PASO 1: Agregar campo en MODELO

**Archivo:** `model/src/main/java/ar/gob/tcer/modelo/Usuario.java`

```java
@Entity
@Table(name = "USUARIO")
public class Usuario {
    @Id
    @GeneratedValue(strategy = GenerationType.SEQUENCE)
    private Long id;
    
    private String nombre;
    private String email;
    
    // ← AGREGAR AQUÍ:
    private String telefono;  // ← NUEVA LÍNEA
    
    private String passwordHash;
    
    // ... resto de campos
    
    // Getter y Setter para teléfono
    public String getTelefono() {
        return telefono;
    }
    
    public void setTelefono(String telefono) {
        this.telefono = telefono;
    }
}
```

### PASO 2: Crear Migración de Base de Datos

**Archivo:** `src/main/resources/db/migration/V001_agregar_telefono_usuario.sql`

```sql
-- Agregar columna a tabla existente
ALTER TABLE USUARIO ADD COLUMN telefono VARCHAR(20);

-- Agregar constraint de validación
ALTER TABLE USUARIO ADD CONSTRAINT check_telefono_formato 
    CHECK (telefono IS NULL OR telefono ~ '^\+?[0-9\s\-\(\)]{10,}$');

-- Crear índice si las búsquedas por teléfono serán frecuentes
CREATE INDEX idx_usuario_telefono ON USUARIO(telefono);
```

### PASO 3: Actualizar DAO (Acceso a Datos)

**Archivo:** `dao-api/src/main/java/ar/gob/tcer/persistencia/UsuarioDAO.java`

```java
public interface UsuarioDAO {
    Usuario findById(Long id);
    Usuario findByEmail(String email);
    
    // ← AGREGAR:
    /**
     * Buscar usuario por teléfono
     */
    Usuario findByTelefono(String telefono);
    
    /**
     * Buscar usuarios cuyo teléfono contenga el texto
     */
    List<Usuario> buscarPorTelefono(String telefono);
    
    Usuario save(Usuario usuario);
}
```

**Archivo:** `dao-impl/src/main/java/ar/gob/tcer/persistencia/impl/UsuarioDAOImpl.java`

```java
@Repository
public class UsuarioDAOImpl implements UsuarioDAO {
    
    @Autowired
    private SessionFactory sessionFactory;
    
    @Override
    public Usuario findById(Long id) {
        Session session = sessionFactory.getCurrentSession();
        return session.get(Usuario.class, id);
    }
    
    @Override
    public Usuario findByEmail(String email) {
        Session session = sessionFactory.getCurrentSession();
        Query query = session.createQuery(
            "FROM Usuario u WHERE u.email = :email"
        );
        query.setParameter("email", email);
        return (Usuario) query.uniqueResult();
    }
    
    // ← AGREGAR ESTOS MÉTODOS:
    @Override
    public Usuario findByTelefono(String telefono) {
        Session session = sessionFactory.getCurrentSession();
        Query query = session.createQuery(
            "FROM Usuario u WHERE u.telefono = :telefono"
        );
        query.setParameter("telefono", telefono);
        return (Usuario) query.uniqueResult();
    }
    
    @Override
    public List<Usuario> buscarPorTelefono(String telefono) {
        Session session = sessionFactory.getCurrentSession();
        Query query = session.createQuery(
            "FROM Usuario u WHERE u.telefono LIKE :telefono " +
            "ORDER BY u.nombre"
        );
        query.setParameter("telefono", "%" + telefono + "%");
        return query.list();
    }
    
    @Override
    @Transactional
    public Usuario save(Usuario usuario) {
        Session session = sessionFactory.getCurrentSession();
        session.saveOrUpdate(usuario);
        return usuario;
    }
}
```

### PASO 4: Actualizar SERVICIOS

**Archivo:** `service-api/src/main/java/ar/gob/tcer/servicio/ServicioUsuario.java`

```java
public interface ServicioUsuario {
    Usuario obtenerPorId(Long id);
    Usuario obtenerPorEmail(String email);
    
    // ← AGREGAR:
    /**
     * Obtener usuario por teléfono
     */
    Usuario obtenerPorTelefono(String telefono);
    
    /**
     * Buscar usuarios por teléfono
     */
    List<Usuario> buscarPorTelefono(String telefono);
    
    /**
     * Actualizar teléfono del usuario
     */
    void actualizarTelefono(Long usuarioId, String nuevoTelefono);
    
    Usuario guardar(Usuario usuario);
}
```

**Archivo:** `service-impl/src/main/java/ar/gob/tcer/servicio/impl/ServicioUsuarioImpl.java`

```java
@Service
@Transactional
public class ServicioUsuarioImpl implements ServicioUsuario {
    
    @Autowired
    private UsuarioDAO usuarioDAO;
    
    @Autowired
    private ActuacionDAO actuacionDAO;
    
    @Override
    public Usuario obtenerPorId(Long id) {
        return usuarioDAO.findById(id);
    }
    
    @Override
    public Usuario obtenerPorEmail(String email) {
        return usuarioDAO.findByEmail(email);
    }
    
    // ← AGREGAR ESTOS MÉTODOS:
    @Override
    public Usuario obtenerPorTelefono(String telefono) {
        if (telefono == null || telefono.trim().isEmpty()) {
            throw new IllegalArgumentException("Teléfono no puede estar vacío");
        }
        
        // Normalizar teléfono (remover espacios, guiones, etc.)
        String telefonoNormalizado = normalizarTelefono(telefono);
        
        Usuario usuario = usuarioDAO.findByTelefono(telefonoNormalizado);
        if (usuario == null) {
            throw new UsuarioNoEncontradoException(
                "No existe usuario con teléfono: " + telefono
            );
        }
        return usuario;
    }
    
    @Override
    public List<Usuario> buscarPorTelefono(String telefono) {
        if (telefono == null || telefono.trim().isEmpty()) {
            return new ArrayList<>();
        }
        
        String telefonoNormalizado = normalizarTelefono(telefono);
        return usuarioDAO.buscarPorTelefono(telefonoNormalizado);
    }
    
    @Override
    public void actualizarTelefono(Long usuarioId, String nuevoTelefono) {
        Usuario usuario = usuarioDAO.findById(usuarioId);
        if (usuario == null) {
            throw new UsuarioNoEncontradoException("Usuario no encontrado");
        }
        
        // Validar teléfono
        if (!esFormatoValido(nuevoTelefono)) {
            throw new IllegalArgumentException(
                "Formato de teléfono inválido: " + nuevoTelefono
            );
        }
        
        String telefonoAnterior = usuario.getTelefono();
        String telefonoNormalizado = normalizarTelefono(nuevoTelefono);
        
        usuario.setTelefono(telefonoNormalizado);
        usuarioDAO.save(usuario);
        
        // Registrar cambio en auditoría
        registrarCambioAuditoria(
            usuario,
            "Teléfono actualizado de " + telefonoAnterior + 
            " a " + telefonoNormalizado
        );
    }
    
    @Override
    public Usuario guardar(Usuario usuario) {
        // Validaciones
        if (usuario.getNombre() == null || 
            usuario.getNombre().trim().isEmpty()) {
            throw new IllegalArgumentException("Nombre es requerido");
        }
        
        if (usuario.getEmail() == null || 
            usuario.getEmail().trim().isEmpty()) {
            throw new IllegalArgumentException("Email es requerido");
        }
        
        // Si tiene teléfono, validar formato
        if (usuario.getTelefono() != null && 
            !usuario.getTelefono().trim().isEmpty()) {
            if (!esFormatoValido(usuario.getTelefono())) {
                throw new IllegalArgumentException(
                    "Formato de teléfono inválido"
                );
            }
            usuario.setTelefono(
                normalizarTelefono(usuario.getTelefono())
            );
        }
        
        return usuarioDAO.save(usuario);
    }
    
    // Métodos auxiliares
    private String normalizarTelefono(String telefono) {
        if (telefono == null) return null;
        
        // Remover espacios, guiones, paréntesis
        String normalizado = telefono
            .replaceAll("[\\s\\-\\(\\)]", "")
            .trim();
        
        return normalizado;
    }
    
    private boolean esFormatoValido(String telefono) {
        if (telefono == null || telefono.isEmpty()) {
            return false;
        }
        
        String normalizado = normalizarTelefono(telefono);
        
        // Validar: mínimo 10 dígitos, máximo 15
        return normalizado.matches("^\\+?[0-9]{10,15}$");
    }
    
    private void registrarCambioAuditoria(Usuario usuario, String descripcion) {
        Actuacion accion = new Actuacion();
        accion.setUsuario(usuario);
        accion.setAccion(descripcion);
        accion.setFecha(LocalDate.now());
        accion.setHora(LocalTime.now());
        
        actuacionDAO.save(accion);
    }
}
```

### PASO 5: Agregar campo a PÁGINA WEB

**Archivo:** `web/src/main/java/ar/gob/tcer/web/usuario/PaginaEditarUsuario.java`

```java
public class PaginaEditarUsuario extends WebPage {
    
    @Autowired
    private ServicioUsuario servicioUsuario;
    
    private Long usuarioId;
    
    public PaginaEditarUsuario(Long usuarioId) {
        this.usuarioId = usuarioId;
        
        Usuario usuario = servicioUsuario.obtenerPorId(usuarioId);
        if (usuario == null) {
            throw new RestartResponseException(
                new PaginaError("Usuario no encontrado")
            );
        }
        
        Form<UsuarioDTO> form = new Form<UsuarioDTO>(
            "formulario", 
            new Model<>(convertirADTO(usuario))) {
            
            @Override
            protected void onSubmit() {
                guardarCambios(this.getModelObject());
            }
        };
        
        // Campo: Nombre
        TextField<String> nombre = new TextField<>("nombre");
        nombre.setRequired(true);
        form.add(nombre);
        
        // Campo: Email
        EmailTextField email = new EmailTextField("email");
        email.setRequired(true);
        form.add(email);
        
        // ← AGREGAR CAMPO TELÉFONO:
        TextField<String> telefono = new TextField<>("telefono");
        telefono.add(new PatternValidator("^\\+?[0-9\\s\\-\\(\\)]{10,}$",
            "Teléfono debe tener al menos 10 dígitos"));
        form.add(telefono);
        
        // Botón Guardar
        Button guardar = new Button("guardar") {
            @Override
            public void onSubmit() {
                // El form ya manejó onSubmit
            }
        };
        form.add(guardar);
        
        // Botón Cancelar
        Link cancelar = new Link("cancelar") {
            @Override
            public void onClick() {
                setResponsePage(new PaginaBusquedaUsuarios());
            }
        };
        form.add(cancelar);
        
        add(form);
    }
    
    private void guardarCambios(UsuarioDTO dto) {
        try {
            Usuario usuario = servicioUsuario.obtenerPorId(usuarioId);
            usuario.setNombre(dto.getNombre());
            usuario.setEmail(dto.getEmail());
            
            // ← GUARDAR TELÉFONO:
            usuario.setTelefono(dto.getTelefono());
            
            servicioUsuario.guardar(usuario);
            
            getSession().info("✅ Usuario actualizado correctamente");
            setResponsePage(new PaginaDetalleUsuario(usuarioId));
            
        } catch (IllegalArgumentException e) {
            error("❌ " + e.getMessage());
        } catch (Exception e) {
            error("❌ Error al guardar: " + e.getMessage());
        }
    }
    
    private UsuarioDTO convertirADTO(Usuario usuario) {
        UsuarioDTO dto = new UsuarioDTO();
        dto.setId(usuario.getId());
        dto.setNombre(usuario.getNombre());
        dto.setEmail(usuario.getEmail());
        dto.setTelefono(usuario.getTelefono());  // ← AGREGAR
        return dto;
    }
}
```

**HTML correspondiente** (`PaginaEditarUsuario.html`):

```html
<html xmlns:wicket="http://wicket.apache.org">
<head>
    <title>Editar Usuario</title>
</head>
<body>
    <div class="container">
        <h1>Editar Usuario</h1>
        
        <form wicket:id="formulario">
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" wicket:id="nombre" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" wicket:id="email" class="form-control" required>
            </div>
            
            <!-- ← AGREGAR CAMPO TELÉFONO: -->
            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="tel" wicket:id="telefono" 
                       class="form-control" 
                       placeholder="Ej: (11) 1234-5678"
                       pattern="^\+?[0-9\s\-\(\)]{10,}$">
                <small class="form-text text-muted">
                    Mínimo 10 dígitos (se aceptan espacios, guiones y paréntesis)
                </small>
            </div>
            
            <button type="submit" wicket:id="guardar" class="btn btn-primary">
                Guardar
            </button>
            <a wicket:id="cancelar" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>
```

### PASO 6: Agregar API REST

**Archivo:** `web/src/main/java/ar/gob/tcer/web/rest/UsuarioAPI.java`

```java
@Path("/usuarios")
@Produces(MediaType.APPLICATION_JSON)
public class UsuarioAPI {
    
    @Autowired
    private ServicioUsuario servicioUsuario;
    
    /**
     * Obtener usuario por teléfono
     * GET /api/usuarios/por-telefono?telefono=1234567890
     */
    @GET
    @Path("/por-telefono")
    public Response obtenerPorTelefono(
        @QueryParam("telefono") String telefono) {
        
        try {
            Usuario usuario = servicioUsuario.obtenerPorTelefono(telefono);
            return Response.ok(convertirADTO(usuario)).build();
            
        } catch (UsuarioNoEncontradoException e) {
            return Response
                .status(Response.Status.NOT_FOUND)
                .entity(new ErrorResponse(e.getMessage()))
                .build();
        }
    }
    
    /**
     * Buscar usuarios por teléfono
     * GET /api/usuarios/buscar-telefono?q=123
     */
    @GET
    @Path("/buscar-telefono")
    public Response buscarPorTelefono(
        @QueryParam("q") String telefonoTermino) {
        
        List<Usuario> usuarios = servicioUsuario
            .buscarPorTelefono(telefonoTermino);
        
        List<UsuarioDTO> dtos = usuarios.stream()
            .map(this::convertirADTO)
            .collect(Collectors.toList());
        
        return Response.ok(dtos).build();
    }
    
    /**
     * Actualizar teléfono
     * PUT /api/usuarios/{id}/telefono
     */
    @PUT
    @Path("{id}/telefono")
    @Consumes(MediaType.APPLICATION_JSON)
    public Response actualizarTelefono(
        @PathParam("id") Long usuarioId,
        CambioTelefonoDTO cambio) {
        
        try {
            servicioUsuario.actualizarTelefono(
                usuarioId,
                cambio.getNuevoTelefono()
            );
            
            return Response.ok(
                new SuccessResponse("Teléfono actualizado correctamente")
            ).build();
            
        } catch (IllegalArgumentException e) {
            return Response
                .status(Response.Status.BAD_REQUEST)
                .entity(new ErrorResponse(e.getMessage()))
                .build();
        }
    }
    
    private UsuarioDTO convertirADTO(Usuario usuario) {
        UsuarioDTO dto = new UsuarioDTO();
        dto.setId(usuario.getId());
        dto.setNombre(usuario.getNombre());
        dto.setEmail(usuario.getEmail());
        dto.setTelefono(usuario.getTelefono());  // ← INCLUIR
        return dto;
    }
}
```

### PASO 7: COMPILAR Y PROBAR

**Compilar:**

```bash
cd /home/pablog/git/sigedo
mvn clean install -DskipTests
```

**Levantar servidor:**

```bash
mvn jetty:run -pl web
```

**Probar en navegador:**

```
http://localhost:8090/tcer/usuario/editar/42
```

**Probar con API:**

```bash
# Buscar por teléfono
curl "http://localhost:8090/tcer/api/usuarios/por-telefono?telefono=1234567890"

# Actualizar teléfono
curl -X PUT "http://localhost:8090/tcer/api/usuarios/42/telefono" \
  -H "Content-Type: application/json" \
  -d '{
    "nuevoTelefono": "0358-1234567"
  }'
```

---

## ✅ CHECKLIST DE CAMBIOS

Cuando hagas cambios en SIGEDO, verifica:

- [ ] **Model:** Agregué campo/clase en `model/`
- [ ] **DAO:** Agregué interfaz en `dao-api/` e implementación en `dao-impl/`
- [ ] **Service:** Agregué interfaz en `service-api/` e implementación en `service-impl/`
- [ ] **Web:** Agregué/actualicé página en `web/`
- [ ] **API:** Agregué endpoint en `web/rest/`
- [ ] **BD:** Creé script SQL para migración
- [ ] **Test:** Agregué pruebas unitarias (si aplica)
- [ ] **Validación:** Validé reglas de negocio en servicio
- [ ] **Auditoría:** Registré cambios importantes
- [ ] **Compilación:** `mvn clean install -DskipTests` sin errores
- [ ] **Runtime:** Levanté con `mvn jetty:run -pl web`
- [ ] **Manual:** Probé en navegador
- [ ] **API:** Probé con curl o Postman
- [ ] **Git:** Commiteé cambios

---

## 🐛 DEBUGGING

### Ver logs en tiempo real

```bash
# Terminal 1: Iniciar servidor
cd /home/pablog/git/sigedo
mvn jetty:run -pl web

# Terminal 2: Ver logs
tail -f jetty.log
```

### Breakpoints en IDE

Si usas IntelliJ o VS Code con debugger:

```java
public void guardarCambios(Usuario usuario) {
    // Aquí se puede poner breakpoint
    // Click izq en número de línea
    servicioUsuario.guardar(usuario);  // ← Línea 42
    info("Guardado");
}
```

### Logging en código

```java
import lombok.extern.slf4j.Slf4j;

@Slf4j
public class ServicioUsuarioImpl {
    
    public void guardar(Usuario usuario) {
        log.info("Guardando usuario: " + usuario.getId());
        log.debug("Datos: " + usuario.toString());
        
        try {
            usuarioDAO.save(usuario);
            log.info("Usuario guardado exitosamente");
        } catch (Exception e) {
            log.error("Error al guardar usuario " + usuario.getId(), e);
            throw e;
        }
    }
}
```

Ver logs:

```bash
tail -f jetty.log | grep "Usuario"
```

---

## 🔄 FLUJO COMPLETO DE DESARROLLO

```
1. ENTENDER REQUERIMIENTO
   "Agregar teléfono a usuario"
   
2. DISEÑAR CAMBIOS
   - BD: Agregar columna
   - Modelo: Agregar field
   - Persistencia: Agregar métodos
   - Servicios: Agregar lógica
   - UI: Agregar campo en formulario
   - API: Agregar endpoints
   
3. IMPLEMENTAR
   - Editar archivos siguiendo orden
   - Compilar cada cambio
   
4. TESTING
   - Compilar sin errores
   - Ejecutar en local
   - Probar manualmente en navegador
   - Probar API con curl
   
5. VALIDAR
   - BD: Datos persistidos correctamente
   - UI: Formulario funciona
   - API: Devuelve JSON correcto
   - Auditoría: Cambios registrados
   
6. COMMIT
   git add .
   git commit -m "Agregar teléfono a usuario"
   git push
```

---

## 📝 TEMPLATE DE ARCHIVO PARA NUEVAS CLASES

Cuando crees una nueva clase, usa este template:

```java
package ar.gob.tcer.modelo;

import javax.persistence.*;
import java.time.LocalDate;
import lombok.Data;

/**
 * [Descripción de qué es esta clase]
 * 
 * Ejemplo: Documento fiscal enviado para auditoría
 */
@Entity
@Table(name = "MI_TABLA")
@Data  // Lombok genera getters, setters, equals, toString
public class MiClase {
    
    @Id
    @GeneratedValue(strategy = GenerationType.SEQUENCE)
    private Long id;
    
    @Column(name = "campo1", nullable = false)
    private String campo1;
    
    @Column(name = "fecha", nullable = false)
    private LocalDate fecha;
    
    @ManyToOne
    @JoinColumn(name = "usuario_id")
    private Usuario usuario;
    
    @OneToMany(mappedBy = "miClase")
    private List<OtraClase> items = new ArrayList<>();
    
    // Validaciones
    @PrePersist
    public void validarAntesDePersistir() {
        if (campo1 == null || campo1.isEmpty()) {
            throw new IllegalArgumentException("Campo1 es requerido");
        }
    }
}
```

---

Este es el flujo completo. Practicando con estos casos, aprenderás cómo hacer cualquier cambio en SIGEDO.
