# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Estructura del repositorio

Este repositorio aloja un sitio personal. La aplicación principal está en `cifra/` — ver `cifra/CLAUDE.md` para documentación detallada de esa app.

| Ruta | Descripción |
|------|-------------|
| `cifra/` | Aplicación de finanzas personales (PHP+MySQL+Bootstrap5+PWA) |
| `index.php` | Redirect legacy a cifra/ |
| `manifest.json` | Manifest PWA del dashboard raíz |
| `claudecode.html`, `github.html`, `linux.html` | Páginas estáticas informativas |
| `viaje-nzl-aus.php` | Página de viaje (estática) |

## Desarrollo local

```bash
# Servidor de desarrollo
php -S localhost:8000

# Test de conexión a la DB
php cifra/scripts/test_conexion.php
```

No hay sistema de build, bundler, ni test runner. No existe CI/CD.

## Deploy

Deploy manual por FTP. Ver `cifra/CLAUDE.md` sección **PWA / Deploy** para la checklist completa (incluyendo cuándo hacer bump de `CACHE_NAME` en `sw.js`).

## Base de datos

MySQL remoto en `164.163.56.6:3306`, DB `gastos_personales`. Credenciales en `cifra/config/database.php` (excluido de git).
