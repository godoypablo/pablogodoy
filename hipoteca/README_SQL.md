# 📋 Scripts SQL - Guía Simple

## 🎯 Qué hace cada archivo

### **00_alterar_tabla.sql** (Si ya tienes la tabla creada)
```
✅ Renombra:    total  →  total_uva
✅ Elimina:     el campo valor_uva vacío
✅ Agrega:      nuevo valor_uva (para precio de UVA)
✅ Agrega:      total_pesos (cálculo automático)
```

**Ejecutar solo si ya tienes la tabla con datos.**

---

### **01_create_table.sql** (Si es tabla nueva)
```
✅ Crea la tabla cuotas_hipoteca con estructura correcta
✅ Campos: nro_cuota, estado, fecha, capital, interes, total_uva, valor_uva, total_pesos
```

**Ejecutar si NO tienes la tabla todavía.**

---

### **02_insert_cuotas.sql**
```
✅ Inserta 120 cuotas (1-4 PAGADA, 5-120 IMPAGA)
✅ Con valores correctos de capital e interés en UVAs
```

**Ejecutar DESPUÉS de crear la tabla.**

---

### **03_update_valor_uva.sql**
```
✅ Actualiza el precio de 1 UVA en pesos
✅ total_pesos se calcula automáticamente
```

**Ejecutar DESPUÉS de los inserts. Actualizar regularmente.**

---

## ⚡ Orden de Ejecución

### **Caso 1: Tabla ya existe (actual)**

```
1. 00_alterar_tabla.sql      ← Corrige estructura
2. 03_update_valor_uva.sql   ← Carga valor de UVA
```

### **Caso 2: Tabla nueva (empezar desde 0)**

```
1. 01_create_table.sql       ← Crea tabla
2. 02_insert_cuotas.sql      ← Carga 120 cuotas
3. 03_update_valor_uva.sql   ← Carga valor de UVA
```

---

## 📊 Estructura Final de la Tabla

| Campo | Tipo | Descripción |
|-------|------|------------|
| `id` | INT | ID único (auto) |
| `nro_cuota` | INT | Número 1-120 |
| `estado` | ENUM | PAGADA / IMPAGA |
| `fecha_vencimiento` | DATE | Cuándo vence |
| `capital` | DECIMAL | Capital en UVAs |
| `interes` | DECIMAL | Interés en UVAs |
| `total_uva` | DECIMAL | Capital + Interés en UVAs |
| `valor_uva` | DECIMAL | **Precio de 1 UVA en pesos** ← TÚ ACTUALIZAS |
| `total_pesos` | DECIMAL | **total_uva × valor_uva** ← CALCULA AUTOMÁTICO |
| `fecha_pago` | DATE | Cuándo se pagó |
| `observaciones` | VARCHAR | Notas libres |

---

## 🔄 Cómo Usar el Campo valor_uva

### **El precio de la UVA cambia diariamente**

```
Hoy:      1 UVA = $1025.50
Mañana:   1 UVA = $1025.75
Próximo:  1 UVA = $1026.00
...etc
```

### **Actualizar el valor:**

1. Abre https://ikiwi.net.ar/calculadoras/uva-a-pesos/
2. Nota el valor (ej: `1025.50`)
3. Ejecuta en phpMyAdmin:

```sql
UPDATE cuotas_hipoteca SET valor_uva = 1025.50;
```

**Automáticamente se calculan todos los `total_pesos`.**

---

## ✅ Verificar que Funciona

```sql
-- Ver las primeras 5 cuotas
SELECT nro_cuota, estado, total_uva, valor_uva, total_pesos 
FROM cuotas_hipoteca 
LIMIT 5;
```

**Debería mostrar:**

| nro_cuota | estado | total_uva | valor_uva | total_pesos |
|-----------|--------|-----------|-----------|------------|
| 1 | PAGADA | 320.26 | 1025.50 | 328,266.63 |
| 2 | PAGADA | 333.81 | 1025.50 | 342,207.87 |
| 3 | PAGADA | 329.29 | 1025.50 | 337,761.41 |
| 4 | PAGADA | 342.85 | 1025.50 | 351,511.98 |
| 5 | IMPAGA | 320.57 | 1025.50 | 328,584.49 |

---

## 💡 Ejemplos de Consultas Útiles

### Próximas 5 cuotas impagas (en pesos)
```sql
SELECT nro_cuota, fecha_vencimiento, total_uva, total_pesos 
FROM cuotas_hipoteca 
WHERE estado = 'IMPAGA'
ORDER BY fecha_vencimiento
LIMIT 5;
```

### Resumen de pagos
```sql
SELECT 
    COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as cuotas_pagadas,
    COUNT(CASE WHEN estado = 'IMPAGA' THEN 1 END) as cuotas_impagas,
    SUM(CASE WHEN estado = 'PAGADA' THEN total_pesos END) as total_pagado_pesos,
    SUM(CASE WHEN estado = 'IMPAGA' THEN total_pesos END) as total_pendiente_pesos
FROM cuotas_hipoteca
WHERE valor_uva IS NOT NULL;
```

### Marcar una cuota como pagada
```sql
UPDATE cuotas_hipoteca 
SET estado = 'PAGADA', fecha_pago = CURDATE()
WHERE nro_cuota = 5;
```

---

## 🚨 Casos Especiales

### ¿Qué pasa si no actualizo `valor_uva`?
- El campo `total_pesos` mostrará `0.00`
- Actualiza `valor_uva` para que se calcule

### ¿Qué pasa si actualizo a mitad de mes?
- Solo ese valor se usa para todas las cuotas
- Si quieres valores históricos diferentes, debes guardarlos en otro lugar

### ¿Puedo tener diferentes valores de UVA por cuota?
- Sí, pero tendría que usar DATETIME en lugar de solo el valor
- Por ahora, usa un solo valor actual para todas

---

## 📝 En Resumen

| Archivo | Cuándo | Qué hace |
|---------|--------|----------|
| `00_alterar_tabla.sql` | Tabla ya existe | Renombra/agrega campos |
| `01_create_table.sql` | Tabla nueva | Crea tabla |
| `02_insert_cuotas.sql` | Inicial | Carga 120 cuotas |
| `03_update_valor_uva.sql` | Regularmente | Actualiza precio UVA |

**¡Listo! Ahora puedes gestionar tus cuotas hipotecarias en pesos.** 💰
