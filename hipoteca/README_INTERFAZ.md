# 🌐 Interfaz Web de Gestión de Cuotas

Sistema web para gestionar las cuotas hipotecarias directamente desde el navegador.

## 📋 Archivos

```
config.php      ← Configuración de conexión a BD
api.php         ← API que maneja las actualizaciones
gestionar.php   ← Interfaz web principal
```

## ⚡ Instalación Rápida

### Paso 1: Copiar los 3 archivos PHP

Copia estos archivos a tu servidor web (ej: `/var/www/html/hipoteca/`):
- `config.php`
- `api.php`
- `gestionar.php`

### Paso 2: Configurar la conexión (si es necesario)

Abre `config.php` y ajusta si tienes contraseña:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // ← Tu contraseña si la tienes
define('DB_NAME', 'gastos_personales');
```

### Paso 3: Abrir en navegador

```
http://localhost/hipoteca/gestionar.php
```

---

## 🎯 Funcionalidades

### ✅ Cambiar Estado de Cuota

1. En la tabla, haz clic en el **checkbox** de una cuota
2. Automáticamente cambia entre **PAGADA** → **IMPAGA**
3. Se registra automáticamente la fecha de pago

**Ejemplo:**
```
Cuota 5: IMPAGA → Haces clic → PAGADA (fecha_pago = hoy)
```

### 💰 Actualizar Valor de UVA

1. En el campo superior "Valor UVA (ARS)", ingresa el valor actual
2. Haz clic en **Actualizar UVA**
3. Automáticamente se recalculan todos los **Total ($)**

**Ejemplo:**
```
Valor UVA: 1025.50
Cuota 1: 320.26 UVA × 1025.50 = $328,266.63
```

### 📊 Estadísticas en Tiempo Real

Se actualizan automáticamente:
- **Cuotas Pagadas:** Cantidad
- **Cuotas Impagas:** Cantidad
- **Total Pagado:** Suma en pesos
- **Total Pendiente:** Suma en pesos
- **Valor UVA Actual:** El último valor cargado

---

## 📊 Estructura de la Tabla

| Campo | Descripción | Editable |
|-------|------------|----------|
| Nro | Número de cuota (1-120) | ❌ No |
| Estado | PAGADA / IMPAGA | ✅ Checkbox |
| Fecha Vto | Fecha de vencimiento | ❌ No |
| Capital (UVA) | Capital en UVAs | ❌ No |
| Interés (UVA) | Interés en UVAs | ❌ No |
| Total (UVA) | Sum a en UVAs | ❌ No |
| Total ($) | En pesos (calcula automático) | ✅ Indirecto* |
| Fecha Pago | Cuándo se pagó | ✅ Automático |

*El total en pesos se calcula con: `Total (UVA) × Valor UVA`

---

## 🔄 Flujo de Trabajo

### Día de Pago
1. Marca la cuota como **PAGADA** (checkbox)
2. Se registra automáticamente hoy como fecha de pago
3. La estadística se actualiza

### Cambio Mensual de UVA
1. Busca el nuevo valor en https://ikiwi.net.ar/calculadoras/uva-a-pesos/
2. Ingresa el valor en el campo "Valor UVA"
3. Haz clic en "Actualizar UVA"
4. Todos los totales en pesos se recalculan

---

## 🔐 Seguridad

### Conexión a BD
- Usa **MySQLi PreparedStatements** (protegido contra SQL injection)
- No hay autenticación (confía en que accedes desde tu red local)
- Los datos se envían via POST

### Datos Sensibles
- No se almacenan contraseñas en el código
- Los valores se validan en el servidor
- Solo afecta las cuotas que existen

---

## 📱 Características Avanzadas

### Auto-actualización
La página recarga automáticamente cada 30 segundos para ver cambios de otros usuarios (si accede otro desde otra PC).

### Mensajes de Confirmación
Después de cada acción aparece un mensaje:
- ✅ Verde si fue exitoso
- ❌ Rojo si hubo error

### Responsive
Funciona en:
- Desktop (1400px+)
- Tablet
- Mobile

---

## 🐛 Troubleshooting

### Error: "Error de conexión"

**Causa:** Credenciales incorrectas

**Solución:**
1. Verifica que MariaDB está corriendo
2. Verifica usuario/contraseña en `config.php`
3. Verifica que la BD `gastos_personales` existe

```bash
mysql -u root -p -e "SHOW DATABASES;"
```

### Error: "Unknown column"

**Causa:** La tabla no tiene la estructura correcta

**Solución:**
1. Ejecuta el script de alteración (`00_alterar_tabla.sql`)
2. Verifica que existen las columnas: `total_uva`, `valor_uva`, `total_pesos`

```sql
DESCRIBE cuotas_hipoteca;
```

### Los totales en pesos no salen

**Causa:** `valor_uva` está vacío

**Solución:**
1. Ingresa un valor en "Valor UVA (ARS)"
2. Haz clic en "Actualizar UVA"

---

## 💻 Ejemplos de Uso

### Pagar cuota 5 hoy

```
1. Busca "Nro 5" en la tabla
2. Haz clic en el checkbox
3. ✅ Cambia a PAGADA
4. ✅ Se registra fecha_pago = hoy
```

### Actualizar UVA a $1,050.75

```
1. En "Valor UVA (ARS)" ingresa: 1050.75
2. Haz clic en "Actualizar UVA"
3. ✅ Todos los totales en $ se recalculan
4. ✅ La estadística "Valor UVA Actual" muestra $1050.75
```

### Ver cuánto me falta por pagar

```
Mira la estadística "Total Pendiente"
Suma de todas las cuotas IMPAGA en pesos
```

---

## 🔍 API REST (Para integración)

Si quieres integrar con otra aplicación, puedes usar:

### GET Datos
```bash
curl -X POST http://localhost/hipoteca/api.php \
  -d "action=obtener_datos"
```

### Cambiar Estado
```bash
curl -X POST http://localhost/hipoteca/api.php \
  -d "action=actualizar_estado&nro_cuota=5&estado=PAGADA"
```

### Actualizar UVA
```bash
curl -X POST http://localhost/hipoteca/api.php \
  -d "action=actualizar_valor_uva&valor_uva=1025.50"
```

---

## 📝 Notas

- **Respaldo:** Haz respaldo de la BD regularmente
- **Valor UVA:** Actualiza mensualmente desde ikiwi
- **Historial:** Los cambios se guardan automáticamente en MariaDB
- **Acceso:** Accesible desde cualquier PC en la red (si está en `/var/www/html/`)

---

**¡Listo! Tu interfaz de gestión está lista.** 🚀
