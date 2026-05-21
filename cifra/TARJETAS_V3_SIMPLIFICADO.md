# Módulo Tarjetas - Modelo v3 SIMPLIFICADO

**Fecha:** 2026-05-20  
**Estado:** Refactorización en progreso

## Cambios Principales

### ❌ ELIMINADO
- **Tabla `resumenes_tarjeta`** — No necesaria
- **Concepto de "período"** — Innecesario
- **Estados complejos** (`abierto/cerrado/parcial`) — Reemplazados con lógica simple
- **Campos innecesarios en `cuotas_movimiento`:**
  - `periodo`
  - `fecha_cierre`
  - `estado`
- **Campo innecesario en `movimientos_tarjeta`:**
  - `cuota_pagada_proximo_resumen`

### ✅ MANTENIDO
- **Tabla `tarjetas_credito`** — Configuración de tarjeta
- **Tabla `movimientos_tarjeta`** — Compras realizadas
- **Tabla `cuotas_movimiento`** — Cuotas individuales
  - `numero_cuota` — Número de cuota (1, 2, 3...)
  - `fecha_vencimiento` — Cuándo vence esta cuota
  - `monto` — Importe de esta cuota
  - `pagada` — ¿Ya fue pagada?
  - `fecha_pago` — Cuándo se pagó (historial)

### ✨ NUEVO
- **Campo `fecha_cancelacion` en `movimientos_tarjeta`**
  - Permite anular movimientos sin borrar (auditoría)
  - `NULL` = movimiento activo
  - Fecha = movimiento anulado (no descuenta límite ni cuenta en deuda)

## Lógica Simplificada

### 1. Calcular Primer Vencimiento
```
Inputs: tarjeta_id, fecha_compra
Lógica:
  - Si día_compra <= fecha_cierre_dia
    → Vence en mes siguiente, día vencimiento_dia
  - Si día_compra > fecha_cierre_dia
    → Vence en dos meses, día vencimiento_dia

Ejemplo:
  Tarjeta: cierre=28, vencimiento=4
  Compra 12/05 → 12 <= 28 → Vence 04/06 ✓
  Compra 30/05 → 30 > 28 → Vence 04/07 ✓
```

### 2. Generar Cuotas
```
Inputs: monto_total, cuotas_totales, primer_vencimiento
Lógica:
  - Calcular monto_base = monto_total / cuotas_totales
  - Generar (cuotas-1) cuotas de monto_base
  - Última cuota = monto_total - suma_anterior
    (Ajusta centavos automáticamente)
  - Fechas: primer_venc + 0,1,2...N meses

Ejemplo:
  Monto: $659.998,98 | Cuotas: 18
  Cuota base: $36.666,61
  Cuota 1-17: $36.666,61 c/u
  Cuota 18: $36.666,61 (total exacto)
  
  Fechas:
  Cuota 1: 04/06/2026
  Cuota 2: 04/07/2026
  ...
  Cuota 18: 04/11/2027
```

### 3. Calcular Deuda Pendiente
```sql
SELECT SUM(monto) FROM cuotas_movimiento
WHERE movimiento_id IN (
  SELECT id FROM movimientos_tarjeta 
  WHERE tarjeta_id = ? AND fecha_cancelacion IS NULL
)
AND pagada = 0
```
= Suma de todas las cuotas no pagadas de movimientos activos

### 4. Calcular Límite Disponible
```sql
SELECT (limite_credito - SUM(monto_total)) as disponible
FROM tarjetas_credito tc
LEFT JOIN movimientos_tarjeta mt 
  ON mt.tarjeta_id = tc.id AND mt.fecha_cancelacion IS NULL
WHERE tc.id = ?
```
= Límite - (suma del monto TOTAL de cada compra activa, no por cuota)

**IMPORTANTE:** El límite se reduce por el TOTAL de la compra, no por la próxima cuota.

### 5. Vencimientos Consolidados
```
Agrupa cuotas por fecha_vencimiento
Muestra:
  04/06/2026 · $60.666 (3 cuotas)
    └─ TV TCL 55" · 1/18 · $36.666
    └─ Netflix · 1/12 · $12.000
    └─ Seguro auto · 3/12 · $12.000
```

## Archivos Modificados

### Backend
- ✨ `lib/tarjetas_financiero_v3.php` — Nueva clase simplificada
- ✨ `api/tarjetas_api_v3.php` — Nueva API simplificada
- 📝 `scripts/migrar_tarjetas_v3_simplificado.sql` — Script de migración

### Frontend
- 🔄 `assets/js/app.js` — Actualizado para usar v3
  - `TARJETAS_API = 'api/tarjetas_api_v3.php'`
  - `abrirDetalleTarjeta()` — Dashboard simplificado
  - `renderizarVencimientosConsolidados()` — Nuevo
  - `renderizarMovimientosSimple()` — Nuevo
  - `guardarMovimiento()` — Simplificado
  - `anularMovimientoTarjeta()` — Nuevo
- 🔄 `sw.js` — CACHE_NAME bumpeado (38 → 40)

## Dashboard Nueva UI

```
╔════════════════════════════════╗
║ Mastercard Bersa ••••4         ║
║ Banco Bersa                    ║
╠════════════════════════════════╣
║ Límite:        $500.000        ║
║ Disponible:   -$160.000 ⚠️     ║
╠════════════════════════════════╣
║ 📅 Próximo vencimiento: 04/06  ║
║ 💰 Total a pagar: $60.666      ║
╠════════════════════════════════╣
║ Próximos pagos:                ║
║                                ║
║ 04/06 · $60.666                ║
║  TV TCL 55" · 1/18 · $36.666   ║
║  Netflix · 1/12 · $12.000      ║
║  Seguro auto · 3/12 · $12.000  ║
║                                ║
║ 04/07 · $48.666                ║
║  TV TCL 55" · 2/18 · $36.666   ║
║  Netflix · 2/12 · $12.000      ║
╠════════════════════════════════╣
║ Compras registradas:           ║
║ TV TCL 55" · 18 cuotas · $... ║
║ Netflix · 12 cuotas · $...    ║
║ Seguro auto · 12 cuotas · $...║
╚════════════════════════════════╝
```

## API Endpoints v3

### Tarjetas
- `GET /api/tarjetas_api_v3.php` — Listar tarjetas con deuda/disponible
- `POST /api/tarjetas_api_v3.php` — Crear tarjeta
- `PUT /api/tarjetas_api_v3.php?id=...` — Editar tarjeta
- `DELETE /api/tarjetas_api_v3.php?id=...` — Desactivar tarjeta

### Movimientos y Cuotas
- `POST /api/tarjetas_api_v3.php?action=movimiento` — Crear movimiento (genera cuotas automáticamente)
- `GET /api/tarjetas_api_v3.php?action=movimientos&tarjeta_id=...` — Listar movimientos
- `DELETE /api/tarjetas_api_v3.php?action=movimiento&id=...` — Anular movimiento

### Vencimientos
- `GET /api/tarjetas_api_v3.php?action=vencimientos_consolidados&tarjeta_id=...` — Vencimientos agrupados por fecha
- `GET /api/tarjetas_api_v3.php?action=proximo_vencimiento&tarjeta_id=...` — Próximo vencimiento

### Límite y Deuda
- `GET /api/tarjetas_api_v3.php?action=disponible&tarjeta_id=...` — {limite, deuda_comprometida, disponible}

### Pagos
- `PATCH /api/tarjetas_api_v3.php?action=marcar_pagada&cuota_id=...` — Marcar cuota como pagada

## Próximos Pasos

1. ✅ Crear archivos v3 (SQL, PHP, API)
2. ✅ Refactorizar app.js (TARJETAS_API, dashboard, funciones)
3. ✅ Bump CACHE_NAME
4. ⏳ **EJECUTAR migración SQL en BD**
5. ⏳ Cambiar `tarjetas_api.php` a `tarjetas_api_v3.php` en línea
6. ⏳ Cambiar `tarjetas_financiero.php` a `tarjetas_financiero_v3.php` en línea
7. ⏳ Probar en desarrollo local
8. ⏳ Deploy a producción

## Rollback Plan

Si hay problemas, los archivos antiguos aún existen:
- `tarjetas_api.php` (versión anterior)
- `tarjetas_financiero.php` (versión anterior)
- Tabla `resumenes_tarjeta` (backup SQL available)

Cambiar `const TARJETAS_API = 'api/tarjetas_api.php'` en app.js.
