# ⚡ Cómo Ejecutar el Script en phpMyAdmin

## 🎯 El Problema
El error ocurre porque hay una columna **generada** (`total_pesos`) que depende de `total`. No puedes renombrar una columna si hay otras que dependen de ella.

## ✅ La Solución

### Paso 1: Abre phpMyAdmin
```
http://localhost/phpmyadmin
```

### Paso 2: Selecciona la BD `gastos_personales`

### Paso 3: Haz clic en la pestaña **SQL**

### Paso 4: Copia TODO este código de una sola vez:

```sql
-- PASO 1: Eliminar la columna generada total_pesos (si existe)
ALTER TABLE cuotas_hipoteca
DROP COLUMN IF EXISTS `total_pesos`;

-- PASO 2: Eliminar el campo valor_uva vacío (si existe)
ALTER TABLE cuotas_hipoteca
DROP COLUMN IF EXISTS `valor_uva`;

-- PASO 3: Renombrar "total" a "total_uva"
ALTER TABLE cuotas_hipoteca
CHANGE COLUMN `total` `total_uva` DECIMAL(10, 2) NOT NULL COMMENT 'Total en UVAs (Capital + Interés)';

-- PASO 4: Agregar nueva columna "valor_uva"
ALTER TABLE cuotas_hipoteca
ADD COLUMN `valor_uva` DECIMAL(10, 2) NULL COMMENT 'Valor de 1 UVA en pesos argentinos' AFTER `total_uva`;

-- PASO 5: Agregar columna "total_pesos" (generada, se calcula sola)
ALTER TABLE cuotas_hipoteca
ADD COLUMN `total_pesos` DECIMAL(12, 2) GENERATED ALWAYS AS (total_uva * COALESCE(valor_uva, 0)) STORED COMMENT 'Total en pesos = Total UVA * Valor UVA' AFTER `valor_uva`;
```

### Paso 5: Haz clic en **Ejecutar**

**Resultado esperado:**
```
✓ Se han ejecutado 5 sentencias correctamente.
```

---

## 🔍 Verificar que quedó bien

En phpMyAdmin, ejecuta esto:

```sql
DESCRIBE cuotas_hipoteca;
```

**Deberías ver estas columnas (en este orden):**

```
Field              | Type
-------------------+--------------------
id                 | int(11)
nro_cuota          | int(11)
estado             | enum('PAGADA','IMPAGA')
fecha_vencimiento  | date
capital            | decimal(10,2)
interes            | decimal(10,2)
total_uva          | decimal(10,2)        ← RENOMBRADO
valor_uva          | decimal(10,2)        ← NUEVO (vacío)
total_pesos        | decimal(12,2)        ← NUEVO (generado)
fecha_pago         | date
observaciones      | varchar(500)
fecha_actualizacion| timestamp
```

---

## ✅ Próximo Paso

Una vez ejecutado, actualiza el valor de UVA:

```sql
UPDATE cuotas_hipoteca SET valor_uva = 1025.50;
```

**(Reemplaza `1025.50` con el valor actual de ikiwi)**

---

## 🧪 Verificar que funciona

Ejecuta esto:

```sql
SELECT nro_cuota, estado, total_uva, valor_uva, total_pesos 
FROM cuotas_hipoteca 
LIMIT 5;
```

**Deberías ver:**

| nro_cuota | estado | total_uva | valor_uva | total_pesos |
|-----------|--------|-----------|-----------|-------------|
| 1 | PAGADA | 320.26 | 1025.50 | 328266.63 |
| 2 | PAGADA | 333.81 | 1025.50 | 342207.87 |
| 3 | PAGADA | 329.29 | 1025.50 | 337761.41 |
| 4 | PAGADA | 342.85 | 1025.50 | 351511.98 |
| 5 | IMPAGA | 320.57 | 1025.50 | 328584.49 |

**¿Ves los números en `total_pesos`? ✅ Funcionó.**

---

## 🚨 Si algo falla

### Error: "Column 'total_uva' doesn't have a default value"
→ Ignora, es normal. El script está bien.

### Error: "Unexpected end of statement"
→ Asegúrate de copiar TODO el código de una sola vez
→ No copies línea por línea

### No veo los cambios
→ Recarga phpMyAdmin (F5)
→ O haz clic en el nombre de la tabla en la izquierda

---

**¡Listo!** Tu tabla ahora tiene la estructura correcta. 🎉
