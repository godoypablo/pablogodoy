# Cifra — finanzas personales, single-user

## Stack
PHP+MySQL | Bootstrap5 | DataTables | PWA | `php -S localhost:8000`
DB: gastos_personales — config/database.php

## DB
```
conceptos:           tipo(ingreso|gasto), activo, permite_multiples, categoria_id, cuenta_id_default
registros_mensuales: concepto_id, importe, mes, anio, fecha, pagado, fecha_vencimiento, cuenta_id — sin UNIQUE
categorias:          nombre, color(hex), icono(BS), orden, activo
cuentas:             nombre, banco, tipo(cuenta_corriente|caja_ahorro|billetera), color(hex), saldo_actual, fecha_saldo, activo
movimientos_cuenta:  tipo(ingreso|pago_gasto|transferencia|extraccion), cuenta_origen_id, cuenta_destino_id, importe, fecha, descripcion, registro_id?
tarjetas:            nombre, banco, limite DECIMAL, cierre_dia TINYINT, vencimiento_dia TINYINT, color CHAR(7), activo
resumenes_tarjeta:   tarjeta_id, mes, anio, total_consumido DECIMAL, pagado, fecha_pago, cuenta_pago_id, movimiento_id — UNIQUE(tarjeta_id,mes,anio)
consumos_tarjeta:    tarjeta_id, resumen_id, descripcion, importe, fecha, mes, anio, categoria_id, cuotas_total?, cuota_numero?, consumo_padre_id?
```

## Cuentas del usuario
1=Entre Ríos (cuenta_corriente) | 2=Santander (caja_ahorro) | 3=Personal Pay (billetera) | 4=Efectivo (billetera, #22c55e)

## APIs (api/)
```
gastos_api.php      GET ?mes&anio → incluye importe_mes_anterior por concepto | POST | PATCH {registro_id, pagado|fecha|fecha_vencimiento|cuenta_id} | DELETE {registro_id}
conceptos_api.php   GET | POST | PUT {cuenta_id_default} | DELETE
categorias_api.php  GET | POST | PUT | DELETE
cuentas_api.php     GET ?mes&anio → cuentas+total_pagado_mes | POST | PUT {id,saldo_actual}
movimientos_api.php GET ?mes&anio&limit | POST transferencia | POST extraccion
tarjetas_api.php    GET ?action=lista&mes&anio | GET ?action=resumenes&tarjeta_id | GET ?action=consumos&tarjeta_id&mes&anio | POST action=consumo | POST action=pago | POST action=nueva_tarjeta | PUT {id,...} | DELETE {consumo_id, eliminar_todas?}
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
Resumen | Cuentas | — | Ingresos | Vencimientos | — | Movimientos | — | Conceptos
Resumen/Cuentas → modales | Vencimientos → modal+badges | Ingresos → modal unificado

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

## Cuentas modal
- `renderizarCuentas()` → #cardCuentas (sin wrapper .card)
- Por cuenta: dot-lg + nombre + tipo | solo saldo real (sin asignado/diferencia)
- `crearSelectorCuenta()` → .cuenta-wrap (fila simple) / .cuenta-wrap-detalle (múltiple)
- Validación saldo HTTP 422 al marcar pagado Y al reasignar cuenta en gasto ya pagado
- Alta: "Nueva cuenta" en footer → form inline `.nueva-cuenta-form`
- Transferencia: #modalTransferencia | Extracción: solo no-billetera
- `total_pagado_mes`: JOIN conceptos filtrando `tipo='gasto'` (si no, suma ingresos — bug histórico)

## Ingresos modal
- Layout dos líneas por ítem:
  - Línea 1 `.ingreso-linea1`: nombre + btn-edit-ingreso + importe (input)
  - Línea 2 `.ingreso-linea2`: select cuenta (flex:1) + input fecha (7.5rem fijo)
- Wrapper `.ingreso-body` (flex-direction:column) al lado de `.btn-pagado`
- `renderizarModalIngresos()` en app.js

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

## Movimientos modal
- Layout flex por fila: fecha(dd/mm/yy)+hora(hh:mm, oculta si es 00:00) | tipo+cuenta | descripción+importe
- Sin tabla, diseño responsive puro flex
- Filtro por mes/año al abrir: `api/movimientos_api.php?limit=200&mes=X&anio=Y`
- Buscador interno (`#inputBusquedaMov`) filtra en cliente por texto libre
- Resumen chips en header: total cobros/pagos/transferencias/extracciones del mes
- `_cargarMovimientos()` / `_renderizarMovimientos(q)` / `limpiarBusquedaMov()` en app.js

## Tarjetas de crédito — dos modales separados

### Configurar tarjetas (#modalConfigurarTarjetas)
- Menú: Hamburguesa → **"Configurar tarjetas"** (nuevo)
- Por mes/año: lista cada tarjeta activa con inputs para:
  - **Límite** (editable, parsea como moneda)
  - **Cierre día** (1-31, ej: Santander = 7 en mayo, 11 en junio)
  - **Vencimiento día** (1-31, ej: 15 o 19)
- Guardar → POST action=configurar_mes → crea/actualiza resumen de cada tarjeta con cierre_dia y vencimiento_dia
- **Diferencia clave:** cierre_dia y vencimiento_dia se guardan en `resumenes_tarjeta` (por período), no en `tarjetas` (tabla estática)

### Ver consumos (#modalTarjetas)
- Menú: Hamburguesa → **"Tarjetas"**
- Header sticky: chips con nombre tarjeta + porcentaje uso + resumen total usado/disponible
- Tabs scrolleables `.tarj-tabs-scroll`: una tab por tarjeta activa con dot de color + nombre + % usado
- Cuerpo: barra de progreso (rojo >85%, azul ≤85%) | resumen mes/importe | historial
- **Formulario nuevo consumo:** fecha | descripción | importe | categoría | **cuotas (1-18)** | **cuota # (si >1)** | preview dinámico | +
- **Cuotas:** campo opcional. Si >1: muestra "N cuotas de $X — genera de Mes A a Mes B" (preview dinámico con mes actual como referencia)
- **Consumos con cuotas:** badge X/N en lista (ej: "2/6"), DELETE pide confirmación si múltiples hermanos
- **Consumo padre:** `consumo_padre_id=NULL` es cuota 1, hermanos tienen `consumo_padre_id=id_cuota1`; calcula mes de inicio retrocediendo según `cuota_numero`
- Mes/año de consumo: según `cierre_dia` del resumen del mes (si día > cierre_dia, resumen va al mes siguiente)
- **Sin botón Pagar:** los pagos de tarjeta se registran en módulo Gastos (registros_mensuales)
- Historial: lista de resúmenes anteriores (mes/importe) con opción ver detalles
- Validación DELETE: 409 si resumen pagado, permite eliminar cuota individual O todas las hermanas con `eliminar_todas=true`

## Login (login.php)
- Logo Montserrat + barra degradé índigo→verde + card shadow
- Toggle ojo para contraseña: `#btnTogglePass` → alterna type password/text + icono bi-eye/bi-eye-slash
- Foco inputs en índigo | btn-ingresar con gradiente + microanimación hover

## PWA / Deploy
- SW: HTML network-first, assets cache-first | CACHE_NAME formato: `cifra-YYYYMMDD-N`
- **Bump CACHE_NAME en sw.js cada vez que cambie CSS o JS** — fuerza re-descarga en todos los dispositivos
- Si hay varios bumps en el mismo día, incrementar el sufijo: `-1`, `-2`, etc.
- Deploy FTP con NetBeans desde `/home/pablo/git/pablogodoy/` → Upload Directory: `/httpdocs` (configurado en nbproject/)
- Deploy típico: index.php siempre | app.js si JS cambió | styles.css si CSS cambió | sw.js si hay bump | gastos_api.php si API cambió
- manifest.json: `"id": "/cifra/"`, `"start_url": "/cifra/index.php"`, `"scope": "/cifra/"` — paths absolutos
- Android: para limpiar caché de SW → Chrome → ⋮ → Configuración del sitio → Almacenamiento → Borrar
- Play Store: TWA via PWABuilder (USD 25, HTTPS) | App Store: Capacitor.js (USD 99/año)
