# 💰 Gestor de Cuotas Hipotecarias

Sistema de seguimiento para el crédito hipotecario en UVAs del **Banco Entre Ríos**.

## 📋 Contenido

- **Crédito:** Hipotecario
- **Banco:** Banco Entre Ríos
- **Acreedor:** GODOY PABLO ANDRES
- **Moneda:** UVAs (Unidades de Valor Adquisitivo)
- **Total cuotas:** 120
- **Período:** Marzo 2026 - Febrero 2036

## 📊 Estado Actual

- ✅ **4 cuotas pagadas** (marzo - junio 2026)
- ⏳ **116 cuotas impagas** (julio 2026 - febrero 2036)
- 💰 **Monto pagado:** 1,327.21 UVAs
- 📈 **Monto pendiente:** 60,254.91 UVAs

## 🚀 Cómo Usar

### Opción 1: Interfaz Web (Recomendado)

1. Abre `index.html` en tu navegador
2. Haz clic en el checkbox para marcar cuotas como "Pagada" o "Impaga"
3. Los cambios se guardan automáticamente en el navegador
4. Usa los filtros para buscar cuotas específicas
5. Exporta a CSV cuando necesites

**Ventajas:**
- No requiere instalación
- Interfaz visual intuitiva
- Datos se guardan localmente en el navegador
- Funciona offline

### Opción 2: Base de Datos MariaDB (Recomendado para producción)

Si usas MariaDB/MySQL como motor de BD:

**Requisitos previos:**
```bash
# Instalar conector MySQL para Python
pip install mysql-connector-python

# Asegurarse de que MariaDB está corriendo
sudo systemctl status mariadb
```

**Configurar y cargar datos:**
```bash
# Editar las credenciales en setup_mariadb.py (líneas 155-159)
# O crear .env con tus datos

# Crear la BD y cargar datos
python3 setup_mariadb.py

# Ver estadísticas
python3 setup_mariadb.py stats
```

**Configuración:**
1. Copia `.env.example` → `.env`
2. Edita `.env` con tus credenciales de MariaDB
3. Ejecuta `python3 setup_mariadb.py`

### Opción 3: Base de Datos SQLite (Local, sin servidor)

Si prefieres una BD simple sin servidor:

```bash
# Crear la base de datos SQLite
python3 setup.py

# Ver estadísticas
python3 setup.py stats
```

Esto crea `hipoteca.db` con toda la información.

### Opción 3: Datos en JSON

`datos_cuotas.json` contiene todos los datos estructurados:
- Información del préstamo
- Resumen del estado
- Detalle de cada una de las 120 cuotas

## 📁 Archivos

```
hipoteca/
├── index.html           ← Aplicación web interactiva (⭐ abre aquí)
├── datos_cuotas.json    ← Datos de las 120 cuotas
├── schema.sql           ← Esquema de base de datos SQLite
├── setup.py            ← Script para crear BD
└── README.md           ← Este archivo
```

## 🎯 Funcionalidades

### En la interfaz web:

✅ **Gestión de estado**
- Marcar/desmarcar cuotas como pagadas
- Los cambios se guardan automáticamente

✅ **Búsqueda y filtrado**
- Buscar por número de cuota o fecha
- Filtrar solo pagadas o impagas

✅ **Estadísticas en tiempo real**
- Cuotas pagadas/impagas
- Monto pagado/pendiente
- Actualizaciones instantáneas

✅ **Exportación**
- Descargar datos en formato CSV
- Útil para contabilidad

## 💾 Almacenamiento de Datos

### Navegador (LocalStorage)
- Los cambios se guardan automáticamente en el navegador
- No requiere servidor
- Funciona offline
- **Limitación:** Se borra si limpias caché del navegador

### Base de Datos (SQLite)
- Persistencia permanente
- Útil si necesitas análisis más avanzados
- Integrable con otros sistemas
- Requiere Python 3

## 📊 Campos de Cada Cuota

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| **NroCuota** | Número de cuota | 1, 2, 3... 120 |
| **Estado** | PAGADA o IMPAGA | PAGADA |
| **F.Vto** | Fecha de vencimiento | 27/03/2026 |
| **Capital** | Capital ajustado por UVA | 127.59 |
| **Int** | Interés | 192.67 |
| **Total** | Capital + Interés | 320.26 |

## 📈 Próximas Cuotas

```
Cuota 5  → 27/07/2026 → 320.57 UVAs
Cuota 6  → 27/08/2026 → 333.74 UVAs
Cuota 7  → 28/09/2026 → 338.17 UVAs
...
Cuota 120 → 27/02/2036 → 331.33 UVAs
```

## 🔒 Seguridad

- Los datos se guardan **localmente en tu navegador**
- No se envía información a servidores externos
- No hay conexión a internet requerida
- Puedes revisar el código HTML si necesitas

## 🛠️ Personalización

### Cambiar donde se guarda (navegador vs BD):

**Para usar SQLite en lugar de LocalStorage:**
1. Ejecuta `python3 setup.py` para crear la BD
2. Modifica `index.html` para leer de SQLite en lugar de JSON
3. Usa `setup.py stats` para ver información

### Agregar notas/observaciones:

Puedes editar `datos_cuotas.json` para agregar observaciones:

```json
{
  "nro_cuota": 1,
  "estado": "PAGADA",
  "observaciones": "Pagado el 25/03/2026"
}
```

## 📞 Datos del Crédito

- **Banco:** Banco Entre Ríos
- **Acreedor:** GODOY PABLO ANDRES
- **Tipo:** Hipotecario
- **Moneda:** UVAs
- **Total cuotas:** 120
- **Vencimiento final:** 27 de febrero de 2036

## ⚠️ Notas Importantes

1. **UVAs:** Las cuotas están expresadas en Unidades de Valor Adquisitivo (UVA). Su valor en pesos se actualiza diariamente según el BCRA.

2. **Datos reales:** Extráidos del PDF oficial del Banco Entre Ríos. Asegúrate de mantenerlo actualizado si hay cambios.

3. **Respaldo:** Es recomendable hacer un respaldo de `datos_cuotas.json` y `hipoteca.db` regularmente.

## 🔧 Troubleshooting

### Error: "mysql-connector-python not found"
```bash
pip install mysql-connector-python
```

### Error: "#1064 - You have an error in your SQL syntax"
- ✅ Usa `schema.sql` (ya está actualizado para MariaDB)
- ✅ Usa `setup_mariadb.py` (script compatible con MySQL/MariaDB)
- ❌ No uses `setup.py` con MariaDB (es para SQLite)

### Error: "Access denied for user 'root'@'localhost'"
1. Verifica que MariaDB esté corriendo: `sudo systemctl status mariadb`
2. Verifica tu contraseña en `setup_mariadb.py` (línea 158)
3. Prueba conectar manualmente: `mysql -u root -p`

### Error: "Database does not exist"
- El script crea automáticamente la BD si no existe
- Si el error persiste, crea manualmente: `CREATE DATABASE hipoteca;`

### MariaDB está corriendo pero no me conecta
```bash
# Verifica que MariaDB escucha en 3306
netstat -tlnp | grep mariadb

# O reinicia MariaDB
sudo systemctl restart mariadb
```

## 📝 Última actualización

**Datos extraídos:** Julio 2026  
**Estado:** 4 cuotas pagadas, 116 impagas  
**Motor BD:** MariaDB/MySQL compatible

---

**Creado con ❤️ para gestión de hipotecas en UVAs**
