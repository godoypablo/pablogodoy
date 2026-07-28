# 📋 Instrucciones - Implementar en phpMyAdmin

## 🎯 Objetivo
Agregar tabla de cuotas hipotecarias a tu BD `gastos_personales` existente.

---

## ⚡ Pasos Rápidos (3 minutos)

### 1️⃣ Abre phpMyAdmin
```
http://localhost/phpmyadmin
```

### 2️⃣ Selecciona la BD `gastos_personales`
- En la lista izquierda, haz clic en `gastos_personales`

### 3️⃣ Ejecuta el script de creación
- Haz clic en la pestaña **SQL**
- Copia todo el contenido de `01_create_table.sql`
- Pégalo en el editor
- Haz clic en **Ejecutar**

**Resultado esperado:**
```
✓ La tabla cuotas_hipoteca ha sido creada exitosamente
```

### 4️⃣ Ejecuta el script de inserts
- Limpia el editor SQL (Ctrl+A → Delete)
- Copia todo el contenido de `02_insert_cuotas.sql`
- Pégalo en el editor
- Haz clic en **Ejecutar**

**Resultado esperado:**
```
✓ Se han insertado 120 registros
```

### 5️⃣ Actualiza el valor de UVA
- Abre `03_update_valor_uva.sql`
- Busca en https://ikiwi.net.ar/calculadoras/uva-a-pesos/ el valor actual de UVA
- Reemplaza `1025.50` con el valor actual (ej: `1045.67`)
- Copia la línea actualizada
- Pégala en phpMyAdmin y ejecuta

---

## 📝 Paso a Paso Detallado

### Paso 1: Crear la tabla

**En phpMyAdmin:**
1. Navega a `gastos_personales` → SQL
2. Copia este código:

```sql
CREATE TABLE IF NOT EXISTS cuotas_hipoteca (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nro_cuota INT NOT NULL UNIQUE,
    estado ENUM('PAGADA', 'IMPAGA') NOT NULL DEFAULT 'IMPAGA',
    fecha_vencimiento DATE NOT NULL,
    capital DECIMAL(10, 2) NOT NULL COMMENT 'Capital en UVAs',
    interes DECIMAL(10, 2) NOT NULL COMMENT 'Interés en UVAs',
    total DECIMAL(10, 2) NOT NULL COMMENT 'Total en UVAs (Capital + Interés)',
    valor_uva DECIMAL(10, 2) NULL COMMENT 'Valor de la UVA al momento de pago (en pesos)',
    total_pesos DECIMAL(12, 2) GENERATED ALWAYS AS (total * COALESCE(valor_uva, 0)) STORED COMMENT 'Total en pesos = Total UVA * Valor UVA',
    fecha_pago DATE NULL,
    observaciones VARCHAR(500),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fecha_vencimiento (fecha_vencimiento),
    INDEX idx_nro_cuota (nro_cuota)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Cuotas hipotecarias en UVAs - Banco Entre Ríos';
```

3. Haz clic en **Ejecutar**

**✅ Listo: Tabla creada**

---

### Paso 2: Insertar los 120 registros

**En phpMyAdmin:**
1. Pestaña SQL → Limpia el editor
2. Copia el contenido completo de `02_insert_cuotas.sql`
3. Pégalo en el editor
4. Haz clic en **Ejecutar**

**✅ Listo: 120 cuotas insertadas**

---

### Paso 3: Actualizar valor de UVA

**Primero, obtener el valor actual:**
1. Abre https://ikiwi.net.ar/calculadoras/uva-a-pesos/
2. Anota el valor de la UVA (ej: `1025.50`)

**Luego, en phpMyAdmin:**
1. Pestaña SQL
2. Ejecuta:

```sql
UPDATE cuotas_hipoteca SET valor_uva = 1025.50;
```

(Reemplaza `1025.50` con el valor actual)

3. Haz clic en **Ejecutar**

**✅ Listo: Todos los totales en pesos se calculan automáticamente**

---

## 🔍 Verificar que Funciona

**En phpMyAdmin, ejecuta:**

```sql
SELECT nro_cuota, estado, fecha_vencimiento, total, valor_uva, total_pesos 
FROM cuotas_hipoteca 
WHERE nro_cuota <= 5;
```

**Deberías ver algo así:**

| nro_cuota | estado | fecha_vencimiento | total  | valor_uva | total_pesos |
|-----------|--------|-------------------|--------|-----------|------------|
| 1         | PAGADA | 2026-03-27        | 320.26 | 1025.50   | 328,266.63 |
| 2         | PAGADA | 2026-04-27        | 333.81 | 1025.50   | 342,207.87 |
| 3         | PAGADA | 2026-05-27        | 329.29 | 1025.50   | 337,761.41 |
| 4         | PAGADA | 2026-06-29        | 342.85 | 1025.50   | 351,511.98 |
| 5         | IMPAGA | 2026-07-27        | 320.57 | 1025.50   | 328,584.49 |

---

## 📊 Columnas Explicadas

| Columna | Qué es | Ejemplo |
|---------|--------|---------|
| **nro_cuota** | Número de cuota (1-120) | 1 |
| **estado** | PAGADA o IMPAGA | PAGADA |
| **fecha_vencimiento** | Fecha de vencimiento | 2026-03-27 |
| **capital** | Capital en UVAs | 127.59 |
| **interes** | Interés en UVAs | 192.67 |
| **total** | Total en UVAs | 320.26 |
| **valor_uva** | Valor de 1 UVA en pesos | 1025.50 |
| **total_pesos** | Total en pesos (calculado automáticamente) | 328,266.63 |
| **fecha_pago** | Cuándo se pagó (llenar manualmente) | NULL |
| **observaciones** | Notas libres | "Pagado por transferencia" |

---

## 🔄 Actualizar Valor de UVA Regularmente

El valor de la UVA cambia diariamente. Para obtener el valor más actualizado:

### Opción A: ikiwi (online)
1. Abre https://ikiwi.net.ar/calculadoras/uva-a-pesos/
2. Lee el valor actual
3. Actualiza en phpMyAdmin

### Opción B: BCRA (oficial)
1. API: https://api.bcra.gob.ar/estadisticas/v1.1/promedioreferencial
2. O visita: https://www.bcra.gob.ar/

### SQL para actualizar:
```sql
UPDATE cuotas_hipoteca SET valor_uva = NUEVO_VALOR;
```

---

## 💡 Ejemplos de Uso

### Buscar todas las cuotas impagas ordenadas por fecha
```sql
SELECT nro_cuota, fecha_vencimiento, total, total_pesos 
FROM cuotas_hipoteca 
WHERE estado = 'IMPAGA'
ORDER BY fecha_vencimiento
LIMIT 10;
```

### Ver resumen de pagos
```sql
SELECT 
    COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as pagadas,
    COUNT(CASE WHEN estado = 'IMPAGA' THEN 1 END) as impagas,
    SUM(CASE WHEN estado = 'PAGADA' THEN total_pesos ELSE 0 END) as pagado_pesos,
    SUM(CASE WHEN estado = 'IMPAGA' THEN total_pesos ELSE 0 END) as pendiente_pesos
FROM cuotas_hipoteca;
```

### Marcar una cuota como pagada
```sql
UPDATE cuotas_hipoteca 
SET estado = 'PAGADA', fecha_pago = CURDATE(), observaciones = 'Pagado'
WHERE nro_cuota = 5;
```

### Próxima cuota a vencer
```sql
SELECT nro_cuota, fecha_vencimiento, total_pesos 
FROM cuotas_hipoteca 
WHERE estado = 'IMPAGA'
ORDER BY fecha_vencimiento
LIMIT 1;
```

---

## ✅ Checklist

- [ ] Abrí phpMyAdmin
- [ ] Seleccioné la BD `gastos_personales`
- [ ] Ejecuté `01_create_table.sql` → tabla creada
- [ ] Ejecuté `02_insert_cuotas.sql` → 120 registros insertados
- [ ] Busqué el valor de UVA en ikiwi
- [ ] Ejecuté `03_update_valor_uva.sql` → valores en pesos calculados
- [ ] Verifiqué que los datos están correctos

---

## 🚨 Si Algo Falla

### Error: "Table already exists"
→ La tabla ya existe. Puedes:
- Dejar como está (los datos no se duplican)
- O eliminarla primero: `DROP TABLE cuotas_hipoteca;`

### Error: "#1064 - SQL Syntax error"
→ Copia el SQL completo sin espacios iniciales

### No puedo ver las cuotas
→ Ejecuta: `SELECT COUNT(*) FROM cuotas_hipoteca;`
→ Debería mostrar: 120

### El valor en pesos no aparece
→ Asegúrate de ejecutar el `UPDATE` con el valor de UVA

---

**¡Listo! Ya tienes tu tabla de hipotecas en MariaDB.** 🎉
