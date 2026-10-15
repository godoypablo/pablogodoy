# Cifra — finanzas personales, single-user

## Stack
PHP+MySQL | Bootstrap5 | DataTables | Chart.js | PWA | `php -S localhost:8000`
DB: gastos_personales — config/database.php

**Librerías:**
- Bootstrap 5.3.2 (CSS+JS)
- Bootstrap Icons 1.11.2
- DataTables 2.0.8 (JS+CSS)
- jQuery 3.7.1 (requerido por DataTables)
- Chart.js 4.4.0 (gráficos de proyección en tarjetas)

## DB
```
conceptos:           tipo(ingreso|gasto), activo, permite_multiples, categoria_id, cuenta_id_default
registros_mensuales: concepto_id, importe, mes, anio, fecha, pagado, fecha_vencimiento, cuenta_id — sin UNIQUE
categorias:          nombre, color(hex), icono(BS), orden, activo
cuentas:             nombre, banco, tipo(cuenta_corriente|caja_ahorro|billetera), color(hex), saldo_actual, fecha_saldo, activo
movimientos_cuenta:  tipo(ingreso|pago_gasto|transferencia|extraccion), cuenta_origen_id, cuenta_destino_id, importe, fecha, descripcion, registro_id?
```

## Cuentas del usuario
1=Entre Ríos (cuenta_corriente) | 2=Santander (caja_ahorro) | 3=Personal Pay (billetera) | 4=Efectivo (billetera, #22c55e)

## APIs (api/)
```
gastos_api.php      GET ?mes&anio → incluye importe_mes_anterior por concepto | POST | POST {accion:'cobrar_ingreso'} | PATCH {registro_id, pagado|fecha|fecha_vencimiento|cuenta_id} | DELETE {registro_id}
conceptos_api.php   GET | POST | PUT {cuenta_id_default} | DELETE
categorias_api.php  GET | POST | PUT | DELETE
cuentas_api.php     GET ?mes&anio → cuentas+total_pagado_mes | POST | PUT {id,saldo_actual}
movimientos_api.php POST transferencia | POST extraccion (el GET quedó sin uso: se quitó la pantalla Movimientos)
tarjetas_api.php    GET ?mes&anio → tarjetas + 12 meses leídos de la planilla | POST | PUT ?id
```

## Circuito saldo (gastos_api.php) — COMPLETO Y VERIFICADO
- POST permite_multiples=1 (pagado=1): INSERT registro + movimiento `pago_gasto` + saldo_actual−importe — en transacción
- POST permite_multiples=0 (nuevo): INSERT con pagado=0, aplica cuenta_id_default — sin movimiento
- POST permite_multiples=0 (existente, pagado=1): UPDATE importe + ajusta saldo/movimiento por la diferencia — en transacción
- PATCH pagado=1: valida saldo 422 → INSERT movimiento + saldo±importe | si ingreso sin cuenta y importe>0 → 422 "Seleccioná una cuenta"
- PATCH pagado=0: elimina movimiento + saldo±importe (revierte)
- PATCH cuenta_id (ya pagado): valida saldo nueva cuenta → ajusta ambas cuentas + migra movimiento
- DELETE: busca movimiento por registro_id → restaura saldo + DELETE movimiento + DELETE registro — en transacción
- Gastos sin cuenta_id: no generan movimiento ni tocan saldo (permitido)
- Ingresos cobrados: tipo `ingreso`, cuenta_destino_id, saldo+importe; al revertir saldo−importe
- Ingresos sin cuenta_id al cobrar: 422 si importe>0 (obliga a seleccionar cuenta antes)

## Cobro de ingreso en un paso — POST {accion:'cobrar_ingreso', concepto_id, mes, anio, importe, cuenta_id, fecha?}
- `cobrarIngreso()` en gastos_api.php, todo en una transacción: revierte el movimiento anterior del registro (si había) y crea el nuevo → el saldo queda bien aunque cambien importe, cuenta o fecha
- Upsert del registro con pagado=1; fecha = la enviada, la que ya tenía o hoy
- importe=0 → anula el cobro y borra el registro | exige cuenta (422) y misma moneda que el concepto
- Guarda la cuenta usada como `cuenta_id_default` del concepto (el mes siguiente aparece en esa cuenta)
- Solo ingresos con permite_multiples=0

## Frontend (app.js)
- Estado: `app.{mesActual, anioActual, datos, guardandoCambios, dtGastos, categorias[], cuentas[]}`
- DataTables: ordering:false; drawCallback inyecta `<tr.categoria-header>`
- Input: neutral→rojo→pulso→verde 2s | `guardandoCambios` previene saves concurrentes
- Dark mode: localStorage('cifra-theme') | Fechas: `formatearFechaCorta()` → dd/mm/yy
- Sugerencias en `crearFilaSimple()`: SMVM←dtos.gob.ar | Elena/Spotify←hardcoded | YouTube←USD×dolarapi.com | **Repetir mes anterior** (`btn-repetir-anterior`, ícono `bi-arrow-repeat`) — aparece si `importe_mes_anterior > 0` y `importe == 0`
- `guardarCuentaRegistro()`: tras PATCH actualiza `app.datos` en memoria (evita re-render que revierte selección)
- `periodoExiste` eliminado — saldo_actual siempre se muestra real en cuentas y topbar
- **Parseo de importes:** `parsearImporte()` interpreta **coma siempre como separador decimal**, punto como miles. Usa: `1.234,56` → 1234.56 | `1,56` → 1.56 | Aplicado a: inputs de importe (conceptos/cards), saldo de cuentas

## Layout — pantalla principal
- Filtro mes/año: Bootstrap collapse, default contraído, localStorage('cifra-filtro-abierto')
- Topbar sticky: `#saldoFiltroHeader` = **ingresos_cobrados − gastos_pagados del mes** | `#totalCuentasTopbar` = suma saldos reales | `#totalGastosHeader` = total gastos del mes | `#gastosPorPagarHeader` = pendientes de pago
- Card Gastos: **sin header** — empieza directo en `card-body p-0`; los totales viven en el topbar
- FAB `.fab` bottom-right z-index:1039 → #modalGastoRapido (permite_multiples=1) — estilos en `<style>` de index.php, NO inline
- `catNav` (chips de categorías) y `busquedaWrap` (buscador) están **fuera** de `#contenidoPrincipal`; se muestran en `ocultarLoading()` con `.classList.remove('d-none')`
- Chips filtran por categoría en desktop y mobile — `.cat-fila-oculta` es regla global (no solo @media mobile)
- Buscador filtra filas por `data-concepto-nombre` en tiempo real; `.cat-fila-busqueda` oculta filas que no coinciden

## Modal Administrar Conceptos
- Tabs: Gastos | Categorías (ambas `.modal-dialog-scrollable`)
- `.conceptos-toolbar` sticky (top:0, z-index:10, bg var(--bs-body-bg)): buscador `form-control-sm` + botón "Nuevo" — se mantiene visible al scrollear
- Gastos: `oninput="filtrarConceptos('listaGastos', this.value)"` → oculta `.concepto-item` por `data-nombre`
- Categorías: `oninput="filtrarCategorias(this.value)"` → oculta filas `tbody-categorias` por `data-nombre`
- Orden: conceptos servidos alfabéticamente (`ORDER BY c.nombre ASC` en gastos_api.php)

## Hamburguesa
Resumen | Cuentas e ingresos | — | Vencimientos | — | Conceptos | — | Tarjetas
Resumen/Cuentas e ingresos → modales | Vencimientos → modal+badges | `abrirModalIngresos()` es alias de `abrirModalCuentas()`

## Modales — reglas generales
- Todos: `modal-dialog-scrollable` (header/footer fijos, body scrolleable)
- NO usar border-success/danger/primary en cards ni bg-success/danger en headers
- Fuentes responsivas: `clamp()` en valores monetarios grandes

## Resumen modal
- Ingresos: clicable → collapse `#resumenIngresosDetalle` con items individuales — `renderizarResumenIngresos()`
- Solo muestra: Disponible (ingresos−gastos_pagados) — SIN Pendiente ni Proyección
- Barras por categoría: `renderizarResumenCategorias()` | Lista pendientes: `renderizarResumenPendientes()`
- Tooltips ⓘ en todos los valores numéricos (Bootstrap, trigger hover+focus)
- `saldo_disponible = ingresos − gastos_pagados` | barra progreso 4px (pctPagado)

## Modal "Cuentas e ingresos" (#modalCuentas) — reemplaza a los viejos modales Cuentas e Ingresos
- `renderizarCuentas()` → #cardCuentas: por cuenta, cabecera (dot + nombre + tipo + saldo real + transferir/extracción/actualizar saldo) y debajo sus ingresos del mes
- Ingreso va en la cuenta del registro del mes o, si no hay, en `cuenta_id_default` (`_cuentaDeIngreso()`); sin cuenta → grupo "Sin cuenta asignada" con select (`asignarCuentaIngreso()` → PUT conceptos)
- Fila `_filaIngresoCuenta()` (.ci-row): círculo + nombre/lápiz + estado + input importe. Estados: `.ci-vacio` | `.ci-pendiente` (importe sin cobrar, círculo ámbar) | `.ci-cobrado` (verde, fecha editable)
- **Escribir importe + Enter/blur = cobrado** (`guardarImporteIngresoCuenta()` → `_cobrarIngreso()` → POST cobrar_ingreso). Vaciarlo en uno cobrado pide confirmación y anula. Esc cancela
- Círculo: pendiente → cobra con ese importe | cobrado → PATCH pagado=0 (queda pendiente, revierte saldo) | vacío → foco al input
- Cambiar fecha (`cambiarFechaIngreso()`) → re-cobra con la nueva fecha | Lápiz (`mostrarEditConceptoIngreso`): si cambia la cuenta de un ingreso ya cobrado, mueve la plata
- Consolidado: "Ingresos del mes cobrados" (+ pendiente) y total por moneda | Footer: Nuevo ingreso (con cuenta + importe ya queda cobrado) · Nueva cuenta
- Validación saldo HTTP 422 al marcar pagado Y al reasignar cuenta en gasto ya pagado
- Transferencia: #modalTransferencia | Extracción: solo no-billetera
- `total_pagado_mes`: JOIN conceptos filtrando `tipo='gasto'` (si no, suma ingresos — bug histórico)

## Vencimientos (permite_multiples=0)
- `.vencimiento-wrap` → span dd/mm/yy + input[date] oculto; click → showPicker()
- Mobile: flex-basis:100% en .concepto-nombre → nueva línea

## Detalle múltiple (permite_multiples=1)
- POST siempre pagado=1, sin fecha_vencimiento
- Renderizado: Cards como divs separados (`#conceptos-cards-container`), no en tabla DataTables
- Filtro categoría: oculta cards por clase `.cat-fila-oculta` (sin duplicación — contenedor anterior se elimina en `renderizarDatos()`)
- Cols: [pagado+fecha + .cuenta-wrap-detalle | descripción | importe | trash]
- Form: [fecha | descripción | importe | +] | Labels `.form-field-label` (0.58rem uppercase)
- Badge contador `.badge-count` a la izquierda de la flecha

## Categorías
- Header: chevron + dot + ícono + label + total (color inline)
- `.categoria-header-label`: 0.72rem | `.categoria-header-total`: 0.8rem
- Edit: todo en `.cat-nombre-edit` con `d-flex flex-wrap` (nombre+orden+botones), colspan="2" — evita overflow mobile

## Movimientos
- **Pantalla eliminada (2026-10-14).** La tabla `movimientos_cuenta` se mantiene: es la que permite revertir cobros/pagos y registra transferencias y extracciones

## Cuota Crédito ← Cuotas hipotecarias (`lib/hipoteca_sync.php`)
- `sincronizarCuotaCredito($db, $mes, $anio)` se ejecuta en el GET de gastos_api.php (envuelto en try/catch)
- Concepto `CONCEPTO_CUOTA_CREDITO_ID = 35`: importe = `ROUND(total_uva × valor_uva, 2)` ("Cuota Bco $") y fecha_vencimiento de la cuota de `cuotas_hipoteca` que **vence en ese mes**
- No hace nada si no hay cuota ese mes o falta el valor UVA | **No modifica registros ya pagados** | Crea el registro si no existe (cuenta por defecto del concepto)
- GET marca el concepto con `origen: 'hipoteca'` → en la planilla el importe es solo lectura (`.importe-origen`, link a hipoteca.php)

## Tarjetas de Crédito (simplificado 2026-10-14) — tarjetas_api.php
- **Solo lectura de la planilla.** El resumen de cada mes se carga en los conceptos de gasto vinculados a la tarjeta (categoría Bancos): `tarjetas_credito.concepto_id` (pesos) y `concepto_usd_id` (dólares). Importe, vencimiento y pagado salen de `registros_mensuales`
- Vencimientos los muestra solos (son gastos con fecha_vencimiento) | el pago descuenta de la cuenta como cualquier gasto
- Modal Tarjetas: gráfico Chart.js apilado (pesos, 12 meses) + por tarjeta tabla Mes | Pesos | Var. % | Dólares | Vence | estado. Alta/edición: nombre, banco, últimos 4, concepto pesos/dólares, activa
- Chip "Tarjetas" en catNav = suma en pesos de los conceptos vinculados del mes visible
- **Eliminado:** compras, cuotas, cierres, simulador, alertas, widget próximo vencimiento, sincronizar (tarjetas_api_v3.php, lib/tarjetas_financiero*.php). Las tablas `movimientos_tarjeta`, `cuotas_movimiento`, `cierres_tarjeta` quedan en la BD sin uso
- Migración: `scripts/tarjetas_simplificar_20261014.sql` (agrega concepto_usd_id, crea "Tarjeta Visa USD"/"Tarjeta Mastercard USD", vincula tarjeta 1→concepto 29, tarjeta 2→concepto 7)
- Vencimientos: totales por grupo separados por moneda (no suma pesos con dólares)

## Login (login.php)
- Logo Montserrat + barra degradé índigo→verde + card shadow
- Toggle ojo para contraseña: `#btnTogglePass` → alterna type password/text + icono bi-eye/bi-eye-slash
- Foco inputs en índigo | btn-ingresar con gradiente + microanimación hover

## PWA / Deploy
- SW: HTML network-first, assets cache-first | CACHE_NAME formato: `cifra-YYYYMMDD-N`
- **Bump CACHE_NAME en sw.js cada vez que cambie CSS o JS** — fuerza re-descarga en todos los dispositivos
- Si hay varios bumps en el mismo día, incrementar el sufijo: `-1`, `-2`, etc.
- Ejemplo: cambio en gráfico tarjetas (Chart.js + CSS) → bump CACHE_NAME, sube `sw.js`
- Deploy FTP con NetBeans desde `/home/pablo/git/pablogodoy/` → Upload Directory: `/httpdocs` (configurado en nbproject/)
- Deploy típico: `index.php` siempre | `app.js` si JS cambió | `styles.css` si CSS cambió | `sw.js` si hay bump | `gastos_api.php` si API cambió
- manifest.json: `"id": "/cifra/"`, `"start_url": "/cifra/index.php"`, `"scope": "/cifra/"` — paths absolutos
- Android: para limpiar caché de SW → Chrome → ⋮ → Configuración del sitio → Almacenamiento → Borrar
- Play Store: TWA via PWABuilder (USD 25, HTTPS) | App Store: Capacitor.js (USD 99/año)
