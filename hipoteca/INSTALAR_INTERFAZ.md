# 📦 Instalación de la Interfaz Web

Cómo poner la interfaz web en funcionamiento.

## 🎯 Requisitos

- ✅ Servidor web (Apache/Nginx) con PHP 7.0+
- ✅ MariaDB corriendo
- ✅ Base de datos `gastos_personales` con tabla `cuotas_hipoteca`
- ✅ Los 3 archivos PHP: `config.php`, `api.php`, `gestionar.php`

---

## 📁 Dónde Poner los Archivos

### Opción 1: En servidor Apache local (RECOMENDADO)

**Linux:**
```bash
cd /var/www/html
mkdir -p hipoteca
cp config.php api.php gestionar.php /var/www/html/hipoteca/
```

**Windows (XAMPP):**
```
C:\xampp\htdocs\hipoteca\
  ├── config.php
  ├── api.php
  └── gestionar.php
```

**Mac (MAMP):**
```
/Applications/MAMP/htdocs/hipoteca/
  ├── config.php
  ├── api.php
  └── gestionar.php
```

---

## 🔧 Verificar que el Servidor Web está Corriendo

### Linux (Apache)
```bash
sudo systemctl status apache2
# Si no corre:
sudo systemctl start apache2
```

### Windows (XAMPP)
- Abre XAMPP Control Panel
- Haz clic en "Start" para Apache

### Mac (MAMP)
- Abre MAMP
- Haz clic en "Start Servers"

---

## ✅ Verificar Instalación

### Paso 1: Verificar que PHP funciona
```bash
# Linux/Mac
curl http://localhost/hipoteca/config.php

# Windows
Abre en navegador: http://localhost/hipoteca/config.php
```

Si ves PHP code (<?php) → PHP está funcionando ✅

---

## 🌐 Acceder a la Interfaz

### Opción 1: Navegador Local
```
http://localhost/hipoteca/gestionar.php
```

### Opción 2: Desde otra PC en la red
```
http://192.168.1.XXX/hipoteca/gestionar.php
(Reemplaza XXX con tu IP)
```

Obtén tu IP:
```bash
# Linux/Mac
hostname -I

# Windows
ipconfig | findstr "IPv4"
```

---

## 🔧 Configuración Inicial

### Paso 1: Editar config.php

Abre `config.php` y verifica/ajusta:

```php
define('DB_HOST', 'localhost');      // Servidor MariaDB
define('DB_USER', 'root');           // Usuario MySQL
define('DB_PASS', '');               // ← Contraseña (si tienes)
define('DB_NAME', 'gastos_personales'); // Nombre BD
define('DB_PORT', 3306);             // Puerto (3306 por defecto)
```

### Paso 2: Probar conexión

Carga la interfaz en navegador y debería mostrar la tabla con los datos. Si no:

**Abre la consola del navegador (F12) y mira los errores.**

---

## 🚀 Primer Uso

### Paso 1: Abrir interfaz
```
http://localhost/hipoteca/gestionar.php
```

### Paso 2: Ver los datos
Deberías ver:
- Tabla con 120 cuotas
- Estadísticas (Pagadas, Impagas, etc.)

### Paso 3: Actualizar Valor UVA
1. Campo superior: "Valor UVA (ARS)"
2. Ingresa valor de https://ikiwi.net.ar/calculadoras/uva-a-pesos/
3. Haz clic en "Actualizar UVA"
4. Ver que se recalculan los totales en $

### Paso 4: Cambiar estado de cuota
1. Busca una cuota (ej: Nro 5)
2. Haz clic en el checkbox
3. Debería cambiar a PAGADA

---

## 🐛 Troubleshooting

### Error: "Cannot connect to server"

**Causa:** El servidor web no está corriendo

**Solución:**
```bash
# Linux
sudo systemctl start apache2

# Mac (MAMP)
Abre MAMP y haz clic en "Start"
```

---

### Error: "Error de conexión: Access denied"

**Causa:** Contraseña incorrecta o usuario no existe

**Solución:**
1. Verifica en terminal:
```bash
mysql -u root -p -e "SHOW DATABASES;"
```

2. Si pide contraseña y la ingresaste en `config.php`, ingresa la misma
3. Actualiza `config.php` con los datos correctos

---

### Error: "Unknown database"

**Causa:** La BD `gastos_personales` no existe

**Solución:**
```bash
mysql -u root -p -e "CREATE DATABASE gastos_personales;"
```

---

### No veo los datos en la tabla

**Causa:** La tabla `cuotas_hipoteca` no existe o está vacía

**Solución:**
1. Ejecuta los scripts SQL:
   - `01_create_table.sql`
   - `02_insert_cuotas.sql`
   - `00_alterar_tabla.sql`

2. Verifica que los datos están:
```bash
mysql -u root -p gastos_personales -e "SELECT COUNT(*) FROM cuotas_hipoteca;"
```

Debería mostrar: 120

---

### La interfaz carga pero no puedo cambiar estado

**Causa:** `api.php` no está accesible o hay error en PHP

**Solución:**
1. Abre la consola (F12) → Network → verifica que `api.php` responde
2. Verifica permisos de archivos:
```bash
chmod 644 /var/www/html/hipoteca/*.php
```

---

## 📋 Checklist

- [ ] Apache/Nginx/MAMP corriendo
- [ ] PHP 7.0+ instalado (`php -v`)
- [ ] MariaDB corriendo (`mysql -u root`)
- [ ] BD `gastos_personales` existe
- [ ] Tabla `cuotas_hipoteca` existe con datos
- [ ] Archivos copiados a `/var/www/html/hipoteca/`
- [ ] `config.php` tiene credenciales correctas
- [ ] http://localhost/hipoteca/gestionar.php carga

---

## 🎉 ¡Listo!

Si llegaste aquí, tu interfaz debería estar funcionando. 

**Próximos pasos:**
1. Actualiza el valor de UVA
2. Marca cuotas como pagadas según corresponda
3. Disfruta viendo tus cuotas en pesos ARS

---

**Preguntas frecuentes en terminal:**

```bash
# Ver si Apache está corriendo
systemctl status apache2

# Ver si MariaDB está corriendo
systemctl status mariadb

# Acceder a MariaDB
mysql -u root -p

# Ver BD
SHOW DATABASES;

# Ver tabla
USE gastos_personales;
SHOW TABLES;
DESC cuotas_hipoteca;
```

---

**¡Éxito! 🚀**
