# 📊 Actualizar Valores de UVA por Mes

Guía para actualizar los valores de UVA a medida que varían mensualmente.

---

## 🎯 El Problema

- **Julio 2026:** UVA = $2,020.51 (valor actual)
- **Agosto - Febrero 2037:** UVA = Desconocido (futuro)

No podemos usar un valor fijo porque la UVA cambia cada mes. Por eso:
- **Valor 0** = "Valor no conocido aún"
- **Total en pesos = $0.00** hasta que se actualice

---

## 📝 Opción 1: Script SQL (phpMyAdmin - Una sola vez)

### Paso 1: En phpMyAdmin

```
1. Ve a: gastos_personales → SQL
2. Copia TODO el contenido de: actualizar_valor_uva_por_mes.sql
3. Pega en phpMyAdmin
4. Haz clic en "Ejecutar"
```

**Resultado:**
- ✅ Julio 2026 (cuota 5): UVA = $2,020.51
- ✅ Agosto 2026 en adelante: UVA = 0

---

## 🖱️ Opción 2: Interfaz Web (Actualizar mes a mes)

**Mucho más fácil:**

### Cuando sepas el valor de UVA para agosto:

1. Abre: https://www.pablogodoy.com.ar/cifra/hipoteca.php
2. Inicia sesión en Cifra
3. **Haz clic en la columna "Valor UVA ($)"** de la cuota de agosto
4. Ingresa el valor (ej: `2050.00`)
5. Presiona Enter
6. ✅ Se actualiza automáticamente

**Repite para cada mes cuando sepas el valor.**

---

## 📅 Calendario de Actualización Recomendado

| Mes | Cuota | Fecha Vto | Acción |
|-----|-------|-----------|--------|
| Marzo 2026 | 1 | 27/03 | ✅ Ya pagada - UVA: 1,025.50 |
| Abril 2026 | 2 | 27/04 | ✅ Ya pagada - UVA: 1,025.50 |
| Mayo 2026 | 3 | 27/05 | ✅ Ya pagada - UVA: 1,025.50 |
| Junio 2026 | 4 | 27/06 | ✅ Ya pagada - UVA: 1,025.50 |
| Julio 2026 | 5 | 27/07 | ✅ Actualizada - UVA: 2,020.51 |
| Agosto 2026 | 6 | 27/08 | ⏳ Pendiente - UVA: 0 |
| Sept 2026 | 7 | 28/09 | ⏳ Pendiente - UVA: 0 |
| ... | ... | ... | ... |

---

## 🔄 Flujo Recomendado

### **Cada mes (aproximadamente el 27):**

1. **Paga la cuota en el banco** (transferencia, etc)
2. **Abre hipoteca.php en Cifra**
3. **Marca la cuota como PAGADA** (checkbox)
4. **Busca el valor de UVA actual** en: https://ikiwi.net.ar/calculadoras/uva-a-pesos/
5. **Haz clic en "Valor UVA"** y actualiza con el valor de ese mes
6. ✅ **Listo** - El total en pesos se calcula automáticamente

---

## 📍 Dónde Obtener el Valor de UVA

### **Sitios Confiables:**

1. **ikiwi.net.ar** (recomendado)
   - https://ikiwi.net.ar/calculadoras/uva-a-pesos/
   - Actualizado diariamente
   - Simple y directo

2. **BCRA (Banco Central)**
   - https://www.bcra.gob.ar/
   - Oficial
   - Histórico completo

3. **API (programático)**
   - https://api.bcra.gob.ar/estadisticas/v1.1/promedioreferencial
   - JSON, para scripts

---

## 💡 Ejemplo Práctico

### **Agosto 2026 - Cuando sepas el valor:**

Supongamos que en agosto 2026 la UVA es $2,050.00

**En ipoteca.php:**
1. Busca cuota 6 (Agosto 2026)
2. Haz clic en la celda "Valor UVA ($)" que dice "0"
3. Aparece un input
4. Ingresa: `2050.00`
5. Presiona Enter
6. ✅ Automáticamente se actualiza:
   - Valor UVA: $2,050.00
   - Total UVA: 333.74
   - **Total en pesos: $683,709.70** ← Se calcula solo

---

## 🗂️ Script Alternativo (Por Rango de Meses)

Si quieres actualizar varios meses a la vez:

```sql
-- Actualizar cuotas 6-12 (Agosto a Marzo 2027) con valores diferentes
UPDATE cuotas_hipoteca SET valor_uva = 2050.00 WHERE nro_cuota = 6;  -- Agosto
UPDATE cuotas_hipoteca SET valor_uva = 2080.00 WHERE nro_cuota = 7;  -- Sept
UPDATE cuotas_hipoteca SET valor_uva = 2100.00 WHERE nro_cuota = 8;  -- Oct
UPDATE cuotas_hipoteca SET valor_uva = 2120.00 WHERE nro_cuota = 9;  -- Nov
UPDATE cuotas_hipoteca SET valor_uva = 2150.00 WHERE nro_cuota = 10; -- Dic
-- ... etc
```

---

## ⚙️ Alternativa: Script Python (Automático)

Si quieres, puedo crear un script Python que:
1. Obtenga el valor de UVA de ikiwi/BCRA automáticamente
2. Lo actualice en la BD por fecha actual

De momento, **la forma manual es simple:**
- **Opción 1 (Fácil):** Haz clic en hipoteca.php y edita
- **Opción 2 (SQL):** Actualiza via phpMyAdmin

---

## ✅ Checklist

- [ ] Ejecuté `actualizar_valor_uva_por_mes.sql` en phpMyAdmin
- [ ] Julio 2026 muestra UVA = $2,020.51
- [ ] Agosto en adelante muestra UVA = 0
- [ ] Sé dónde obtener el valor de UVA cada mes (ikiwi)
- [ ] Sé cómo actualizar (clic en la celda o SQL)

---

## 📞 Preguntas Frecuentes

### **¿Puedo dejar en 0 mientras tanto?**
✅ Sí. Significa "no calculado aún". El total en pesos será $0.00.

### **¿Se actualiza automáticamente?**
❌ No. Es manual. Cada mes actualizas cuando sepas el valor.

### **¿Qué pasa si me equivoco de valor?**
✅ Solo haz clic nuevamente y corriges.

### **¿Los valores históricos están correctos?**
✅ Marzo-Junio 2026: UVA = $1,025.50 (correcto para cuando se pagaron)
✅ Julio 2026: UVA = $2,020.51 (valor actual)

---

**¡Listo! Ahora tienes valores realistas.** 📊
