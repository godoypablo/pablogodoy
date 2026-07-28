# 🏠 Integración de Cuotas Hipotecarias en Cifra

La interfaz de gestión de cuotas hipotecarias ahora está integrada dentro de Cifra con autenticación.

## 📁 Archivos Agregados

```
cifra/
├── hipoteca.php              ← Interfaz de cuotas (protegida con login)
└── api/
    └── hipoteca_api.php      ← API de cuotas (requiere autenticación)
```

## 🔐 Seguridad

- ✅ Requiere login de Cifra (usa `require_auth_or_redirect()`)
- ✅ API protegida (usa `require_auth_or_401()`)
- ✅ Los datos se guardan en la BD existente `gastos_personales`
- ✅ Mantiene el estilo visual de Cifra

## 🚀 Acceso

### URL Directa
```
http://localhost/cifra/hipoteca.php
```

### Desde el Menú de Cifra
Agregar al menú hamburguesa en `index.php`:

```html
<li><hr class="dropdown-divider"></li>
<li>
    <a class="dropdown-item d-flex align-items-center gap-2" href="hipoteca.php">
        <i class="bi bi-building menu-icon"></i>
        Cuotas Hipotecarias
    </a>
</li>
```

**Ubicación en index.php:** Después de la línea con "Movimientos", antes de "Conceptos"

---

## 🔧 Configuración

### Ya Está Configurado

- ✅ Autenticación: usa `config/auth_check.php` de Cifra
- ✅ Base de datos: usa `config/database.php` (PDO)
- ✅ Estilos: usa Bootstrap 5 + CSS de Cifra
- ✅ API: en `/api/hipoteca_api.php` (patrón consistente)

---

## 📊 Funcionalidades

### ✅ Cambiar Estado
- Haz clic en el checkbox para marcar como PAGADA/IMPAGA
- Se registra automáticamente la fecha de pago

### 💰 Actualizar Valor UVA
- Campo en la parte superior
- Se recalculan automáticamente todos los totales en pesos
- Obtén el valor en: https://ikiwi.net.ar/calculadoras/uva-a-pesos/

### 📈 Estadísticas en Tiempo Real
- Cuotas pagadas/impagas
- Montos en pesos
- Se actualizan cada 30 segundos

---

## 🎯 Cómo Usar

### Primer Acceso
1. Inicia sesión en Cifra normalmente
2. Abre la URL: `http://localhost/cifra/hipoteca.php`
3. O agrega el enlace en el menú y haz clic

### Pagar una Cuota
1. Busca la cuota en la tabla
2. Haz clic en el checkbox
3. ✅ Cambia a PAGADA y se registra la fecha

### Actualizar Valor UVA
1. Busca el valor en ikiwi
2. Ingresa en el campo "Valor de 1 UVA (ARS)"
3. Haz clic en "Actualizar"
4. Todos los totales en $ se recalculan

---

## 🔗 Agregar al Menú

Edita `/cifra/index.php` y busca esta línea (aproximadamente línea 96):

```html
<li>
    <a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="abrirModalAnual();return false;">
        <i class="bi bi-calendar3-range menu-icon"></i>
        Vista anual
    </a>
</li>
```

Agrega ANTES de eso:

```html
<li><hr class="dropdown-divider"></li>
<li>
    <a class="dropdown-item d-flex align-items-center gap-2" href="hipoteca.php">
        <i class="bi bi-building menu-icon"></i>
        Cuotas Hipotecarias
    </a>
</li>
```

---

## 📋 Verificar Instalación

1. ✅ Los archivos están en su lugar:
   - `/cifra/hipoteca.php`
   - `/cifra/api/hipoteca_api.php`

2. ✅ La tabla `cuotas_hipoteca` existe en la BD `gastos_personales`

3. ✅ Ejecutaste los scripts SQL:
   - `00_alterar_tabla.sql`
   - `02_insert_cuotas.sql` (si la tabla estaba vacía)

4. ✅ Actualizaste el valor de UVA al menos una vez

---

## 🐛 Troubleshooting

### Error 401: "No autorizado"
→ Asegúrate de estar logueado en Cifra
→ Si usas remember-me, puede haber expirado

### No veo la tabla
→ Asegúrate de que la tabla `cuotas_hipoteca` existe:
```bash
mysql -u root -p gastos_personales -e "DESCRIBE cuotas_hipoteca;"
```

### Los totales en $ salen $0.00
→ Necesitas actualizar el valor de UVA primero
→ El campo `valor_uva` debe tener un número > 0

### El menú no muestra el enlace
→ Asegúrate de editar `index.php` correctamente
→ Recarga la página (Ctrl+F5)

---

## 🎨 Estilo Visual

- Mantiene la identidad visual de Cifra (Bootstrap 5, colores, tipografía)
- Responsive: funciona en desktop, tablet, mobile
- Dark mode: se adapta automáticamente
- Animaciones suaves: carga, mensajes

---

## 📝 Notas

- La interfaz está **completamente integrada** en Cifra
- No hay archivos separados en `/hipoteca/` más (salvo los SQL y documentación)
- La API sigue el patrón de Cifra: `/api/hipoteca_api.php`
- Autenticación centralizada: usa sesiones de Cifra
- Base de datos compartida: `gastos_personales`

---

## ✅ Próximos Pasos

1. ✅ Los archivos están en lugar
2. ✅ Agrega el enlace al menú de Cifra (opcional)
3. ✅ Accede a `hipoteca.php` desde navegador
4. ✅ Actualiza el valor de UVA
5. ✅ Marca cuotas como pagadas

**¡Listo! Tu gestión de cuotas está dentro de Cifra.** 🎉
