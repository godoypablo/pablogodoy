# 🔧 Cómo Ejecutar el Script (Paso a Paso)

Tu versión de MariaDB no soporta `IF EXISTS`. Ejecuta los comandos **uno por uno**.

---

## ⚡ Opción 1: Ejecutar los comandos por separado (RECOMENDADO)

### En phpMyAdmin:
1. Ve a `gastos_personales` → SQL
2. Ejecuta CADA comando por separado (copiar + ejecutar)

---

## 🔽 PASO 1: Eliminar total_pesos

Copia y ejecuta:

```sql
ALTER TABLE cuotas_hipoteca DROP COLUMN `total_pesos`;
```

**Si da error:** "Unknown column" → No importa, significa que no existe. Continúa.

**Si funciona:** ✅ Continúa con PASO 2.

---

## 🔽 PASO 2: Eliminar valor_uva

Copia y ejecuta:

```sql
ALTER TABLE cuotas_hipoteca DROP COLUMN `valor_uva`;
```

**Si da error:** "Unknown column" → No importa, continúa.

**Si funciona:** ✅ Continúa con PASO 3.

---

## 🔽 PASO 3: Renombrar total → total_uva

Copia y ejecuta:

```sql
ALTER TABLE cuotas_hipoteca
CHANGE COLUMN `total` `total_uva` DECIMAL(10, 2) NOT NULL;
```

**Este DEBE funcionar.** Si da error, dile al usuario que revise si `total` existe.

---

## 🔽 PASO 4: Agregar valor_uva

Copia y ejecuta:

```sql
ALTER TABLE cuotas_hipoteca
ADD COLUMN `valor_uva` DECIMAL(10, 2) NULL COMMENT 'Valor de 1 UVA en pesos' AFTER `total_uva`;
```

**Debe funcionar.** ✅

---

## 🔽 PASO 5: Agregar total_pesos (generada)

Copia y ejecuta:

```sql
ALTER TABLE cuotas_hipoteca
ADD COLUMN `total_pesos` DECIMAL(12, 2) GENERATED ALWAYS AS (total_uva * COALESCE(valor_uva, 0)) STORED COMMENT 'Total en pesos' AFTER `valor_uva`;
```

**Debe funcionar.** ✅

---

## ✅ Verificar que quedó correcto

Ejecuta:

```sql
DESCRIBE cuotas_hipoteca;
```

**Deberías ver estas columnas (en orden):**

```
id
nro_cuota
estado
fecha_vencimiento
capital
interes
total_uva          ← RENOMBRADO
valor_uva          ← NUEVO
total_pesos        ← NUEVO (generado)
fecha_pago
observaciones
fecha_actualizacion
```

---

## 📊 Actualizar valor de UVA

Una vez que la tabla esté correcta, ejecuta:

```sql
UPDATE cuotas_hipoteca SET valor_uva = 1025.50;
```

**(Reemplaza 1025.50 con el valor actual)**

---

## 🧪 Probar que funciona

Ejecuta:

```sql
SELECT nro_cuota, estado, total_uva, valor_uva, total_pesos 
FROM cuotas_hipoteca 
WHERE nro_cuota <= 5;
```

**Deberías ver valores en total_pesos como 328266.63, 342207.87, etc.**

---

## 🎯 Resumen

| Paso | Comando | Resultado Esperado |
|------|---------|-------------------|
| 1 | DROP total_pesos | ✅ O error (no importa) |
| 2 | DROP valor_uva | ✅ O error (no importa) |
| 3 | CHANGE total → total_uva | ✅ Debe funcionar |
| 4 | ADD valor_uva | ✅ Debe funcionar |
| 5 | ADD total_pesos | ✅ Debe funcionar |
| 6 | UPDATE valor_uva = 1025.50 | ✅ Debe funcionar |

---

**Listo. Ejecuta uno por uno y listo.** ✅
