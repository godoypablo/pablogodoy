# 🗄️ Configuración de MariaDB

Guía paso a paso para configurar el crédito hipotecario con MariaDB.

## 🚀 Opción 1: Setup Automático (Recomendado)

```bash
cd /home/pablog/git/PabloGodoy/hipoteca
chmod +x setup.sh
./setup.sh
```

El script hará:
1. Verificar que Python3 está instalado
2. Instalar `mysql-connector-python` si falta
3. Pedir tus credenciales de MariaDB
4. Crear la BD automáticamente
5. Cargar los 120 datos de cuotas

## 🔧 Opción 2: Setup Manual

### Paso 1: Instalar conector Python

```bash
pip install mysql-connector-python
```

### Paso 2: Verificar que MariaDB está corriendo

```bash
# En Linux
sudo systemctl status mariadb

# Si no está corriendo:
sudo systemctl start mariadb
```

### Paso 3: Editar credenciales

Abre `setup_mariadb.py` y encuentra estas líneas (alrededor de 155):

```python
HOST = 'localhost'
USER = 'root'
PASSWORD = ''  # Cambiar si tienes contraseña
DATABASE = 'hipoteca'
```

Ajusta según tus credenciales.

### Paso 4: Ejecutar setup

```bash
cd /home/pablog/git/PabloGodoy/hipoteca
python3 setup_mariadb.py
```

**Debería mostrar:**
```
✅ Conectado a MariaDB
✅ Base de datos 'hipoteca' verificada/creada
✅ Tabla 'cuotas_hipoteca' creada/verificada
✅ 120 cuotas cargadas en la BD

📊 Estado del Crédito Hipotecario
   • Cuotas pagadas: 4
   • Cuotas impagas: 116
   • Monto pagado: 1327.21 UVAs
   • Monto pendiente: 60254.91 UVAs
```

## 📊 Ver Estadísticas

```bash
python3 setup_mariadb.py stats
```

## 🗃️ Estructura de la Tabla

```sql
CREATE TABLE cuotas_hipoteca (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nro_cuota INT NOT NULL UNIQUE,              -- 1-120
    estado ENUM('PAGADA', 'IMPAGA'),            -- Estado actual
    fecha_vencimiento DATE NOT NULL,            -- Fecha de vencimiento
    capital DECIMAL(10, 2) NOT NULL,           -- Capital en UVAs
    interes DECIMAL(10, 2) NOT NULL,           -- Interés en UVAs
    total DECIMAL(10, 2) NOT NULL,             -- Capital + Interés
    fecha_pago DATE NULL,                       -- Cuando se pagó (si aplica)
    observaciones VARCHAR(500),                -- Notas libres
    fecha_actualizacion TIMESTAMP               -- Última actualización
)
```

## 🔍 Consultas Útiles

### Ver todas las cuotas impagas

```sql
SELECT nro_cuota, fecha_vencimiento, total
FROM cuotas_hipoteca
WHERE estado = 'IMPAGA'
ORDER BY fecha_vencimiento;
```

### Ver próxima cuota a vencer

```sql
SELECT nro_cuota, fecha_vencimiento, total
FROM cuotas_hipoteca
WHERE estado = 'IMPAGA'
ORDER BY fecha_vencimiento
LIMIT 1;
```

### Ver resumen de pagos

```sql
SELECT
    COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as pagadas,
    COUNT(CASE WHEN estado = 'IMPAGA' THEN 1 END) as impagas,
    SUM(CASE WHEN estado = 'PAGADA' THEN total ELSE 0 END) as monto_pagado,
    SUM(CASE WHEN estado = 'IMPAGA' THEN total ELSE 0 END) as monto_pendiente
FROM cuotas_hipoteca;
```

### Marcar una cuota como pagada

```sql
UPDATE cuotas_hipoteca
SET estado = 'PAGADA', fecha_pago = CURDATE()
WHERE nro_cuota = 5;
```

### Agregar una nota a una cuota

```sql
UPDATE cuotas_hipoteca
SET observaciones = 'Pagado por transferencia'
WHERE nro_cuota = 5;
```

## 🐛 Troubleshooting

### Error: "Access denied for user 'root'@'localhost'"

**Causa:** Contraseña incorrecta o usuario no existe

**Solución:**
```bash
# Conectar sin contraseña
mysql -u root -h localhost

# O con contraseña
mysql -u root -p -h localhost
```

### Error: "#1064 - You have an error in your SQL syntax"

**Causa:** Sintaxis SQL incorrecta para MariaDB

**Solución:**
- ✅ Usa `schema.sql` (ya está optimizado)
- ✅ Usa `setup_mariadb.py` (script correcto)
- ❌ NO intentes pegar SQL de SQLite

### MariaDB no está corriendo

```bash
# Ver estado
sudo systemctl status mariadb

# Iniciar
sudo systemctl start mariadb

# Reiniciar
sudo systemctl restart mariadb

# Ver logs
sudo journalctl -u mariadb -n 50
```

### Verificar conexión

```bash
# Desde terminal
mysql -u root -h localhost -e "SELECT VERSION();"

# Desde Python
python3 -c "import mysql.connector; print(mysql.connector.__version__)"
```

## 📱 Integración con Aplicaciones

### Ejemplo en Python

```python
import mysql.connector

conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='hipoteca'
)

cursor = conn.cursor(dictionary=True)
cursor.execute("SELECT * FROM cuotas_hipoteca WHERE estado = 'IMPAGA'")

for cuota in cursor.fetchall():
    print(f"Cuota {cuota['nro_cuota']}: {cuota['total']} UVAs (vence {cuota['fecha_vencimiento']})")

cursor.close()
conn.close()
```

### Ejemplo en PHP

```php
$conn = new mysqli("localhost", "root", "", "hipoteca");

if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

$sql = "SELECT * FROM cuotas_hipoteca WHERE estado = 'IMPAGA' ORDER BY fecha_vencimiento";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    echo "Cuota " . $row["nro_cuota"] . ": " . $row["total"] . " UVAs";
}

$conn->close();
```

## 📋 Checklist de Configuración

- [ ] Python3 instalado
- [ ] `mysql-connector-python` instalado
- [ ] MariaDB corriendo (`systemctl status mariadb`)
- [ ] Credenciales correctas en `setup_mariadb.py`
- [ ] `setup_mariadb.py` ejecutado sin errores
- [ ] Estadísticas visibles con `setup_mariadb.py stats`
- [ ] Tabla `cuotas_hipoteca` contiene 120 registros
- [ ] Puedo conectar con `mysql -u root`

## 🎯 Próximos Pasos

1. **Interfaz Web:** Abre `index.html` para ver/editar cuotas
2. **Automatización:** Crea un cron job para alertas de pagos próximos
3. **API REST:** Expone los datos mediante una API (Python Flask/FastAPI)
4. **Reportes:** Genera reportes mensuales en PDF

---

**¿Problemas?** Revisa [README.md](README.md) o contacta al soporte.
