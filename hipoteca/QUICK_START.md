# ⚡ Inicio Rápido - MariaDB

**¿Primeros pasos? Aquí está todo lo que necesitas.**

## 🎯 Tu Objetivo

Configurar una base de datos MariaDB con tus 120 cuotas hipotecarias y poder consultarlas/editarlas.

## 3️⃣ Pasos Simples

### 1️⃣ Instalar dependencias (2 min)

```bash
pip install mysql-connector-python
```

### 2️⃣ Configurar credenciales (1 min)

Abre `setup_mariadb.py` y edita estas líneas:

```python
HOST = 'localhost'      # Tu servidor MariaDB
USER = 'root'           # Tu usuario
PASSWORD = ''           # Tu contraseña (dejar vacío si no tienes)
DATABASE = 'hipoteca'   # Nombre de la BD
```

### 3️⃣ Ejecutar setup (30 segundos)

```bash
cd /home/pablog/git/PabloGodoy/hipoteca
python3 setup_mariadb.py
```

**✅ Debería salir algo así:**
```
✅ Base de datos 'hipoteca' verificada/creada
✅ Tabla 'cuotas_hipoteca' creada/verificada
✅ 120 cuotas cargadas en la BD

📊 Estado del Crédito Hipotecario
   • Cuotas pagadas: 4
   • Cuotas impagas: 116
   • Monto pagado: 1327.21 UVAs
   • Monto pendiente: 60254.91 UVAs
```

---

## 📁 Archivos Importantes

| Archivo | Para qué sirve |
|---------|---|
| `setup_mariadb.py` | 🐍 Crear BD y cargar datos |
| `schema.sql` | 📋 Estructura de la tabla (SQL puro) |
| `index.html` | 🌐 Interface web (abrir en navegador) |
| `datos_cuotas.json` | 📊 Datos de 120 cuotas |
| `MARIADB_SETUP.md` | 📖 Guía detallada (troubleshooting) |

---

## 🔧 Verificar Que Funciona

```bash
# Ver estadísticas
python3 setup_mariadb.py stats

# Conectar a MariaDB directamente
mysql -u root -h localhost hipoteca
```

---

## 🌐 Usar la Interfaz Web

```bash
# Opción A: Doble click
open index.html

# Opción B: Servidor local
python3 -m http.server 8000
# Luego: http://localhost:8000/index.html
```

**En la web puedes:**
- ✅ Marcar cuotas como pagadas/impagas (checkbox)
- ✅ Buscar por número o fecha
- ✅ Ver estadísticas en tiempo real
- ✅ Exportar a CSV

---

## ❌ Si Hay Errores

### Error: "mysql-connector-python not found"
```bash
pip install mysql-connector-python
```

### Error: "Access denied for user 'root'"
- Verifica tu contraseña en `setup_mariadb.py`
- Prueba conectar manualmente: `mysql -u root -p -h localhost`

### Error: "Can't connect to MariaDB server"
```bash
# Verificar que MariaDB está corriendo
sudo systemctl status mariadb

# Si no está, iniciarlo
sudo systemctl start mariadb
```

### Error: "#1064 - You have an error in your SQL syntax"
- ✅ Este problema ya está RESUELTO
- Usa `setup_mariadb.py` (no `setup.py`)
- Usa `schema.sql` que es compatible con MariaDB

---

## 📊 Próximas Cuotas a Pagar

```
Cuota 5  → 27/07/2026 → 320.57 UVAs
Cuota 6  → 27/08/2026 → 333.74 UVAs
Cuota 7  → 28/09/2026 → 338.17 UVAs
...
Cuota 120 → 27/02/2036 → 331.33 UVAs
```

---

## 🚀 Lo Que Puedes Hacer Ahora

### Opción A: Solo Web (sin BD)
- Abre `index.html` en navegador
- Los cambios se guardan localmente
- No necesitas MariaDB

### Opción B: Con MariaDB (persistente)
- Ejecuta `python3 setup_mariadb.py` ✅ Ya hecho
- Los datos se guardan en la BD
- Puedes consultarlos desde cualquier lado

### Opción C: Integrar con tu App
```python
# Python
import mysql.connector
conn = mysql.connector.connect(
    host='localhost', user='root', database='hipoteca'
)
# ... tu código
```

---

## 📞 Soporte Rápido

| Problema | Solución |
|----------|----------|
| No veo datos en la web | Asegúrate de que `datos_cuotas.json` esté presente |
| MariaDB no responde | `sudo systemctl restart mariadb` |
| Olvidé la contraseña | Edita `setup_mariadb.py` línea 158 |
| Quiero ver el SQL | Lee `schema.sql` |
| Necesito más info | Lee `MARIADB_SETUP.md` |

---

## ✅ Checklist

- [ ] `pip install mysql-connector-python` ✓
- [ ] Edité credenciales en `setup_mariadb.py` ✓
- [ ] Ejecuté `python3 setup_mariadb.py` ✓
- [ ] Vi 120 cuotas cargadas ✓
- [ ] Abro `index.html` en navegador ✓
- [ ] Puedo marcar/desmarcar cuotas ✓

---

**¿Listo?** Abre `index.html` en tu navegador. 🚀

Para más info, lee [MARIADB_SETUP.md](MARIADB_SETUP.md)
