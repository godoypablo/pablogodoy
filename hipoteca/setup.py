#!/usr/bin/env python3
"""
Script para configurar la base de datos SQLite con los datos del crédito hipotecario
"""

import sqlite3
import json
import sys
from pathlib import Path

def crear_basedatos():
    """Crea la tabla y carga los datos iniciales"""

    # Crear/conectar a la BD
    db_path = Path(__file__).parent / 'hipoteca.db'
    conn = sqlite3.connect(str(db_path))
    cursor = conn.cursor()

    # Crear tabla
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS cuotas_hipoteca (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nro_cuota INTEGER NOT NULL UNIQUE,
            estado TEXT NOT NULL CHECK(estado IN ('PAGADA', 'IMPAGA')),
            fecha_vencimiento TEXT NOT NULL,
            capital REAL NOT NULL,
            interes REAL NOT NULL,
            total REAL NOT NULL,
            fecha_pago TEXT,
            observaciones TEXT,
            fecha_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ''')

    # Crear índices
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_estado ON cuotas_hipoteca(estado)')
    cursor.execute('CREATE INDEX IF NOT EXISTS idx_fecha_vencimiento ON cuotas_hipoteca(fecha_vencimiento)')

    # Cargar datos del JSON
    json_path = Path(__file__).parent / 'datos_cuotas.json'

    if not json_path.exists():
        print(f"❌ Error: No se encontró {json_path}")
        sys.exit(1)

    with open(json_path, 'r', encoding='utf-8') as f:
        datos = json.load(f)

    # Limpiar tabla existente (opcional)
    cursor.execute('DELETE FROM cuotas_hipoteca')

    # Insertar datos
    for cuota in datos['cuotas']:
        cursor.execute('''
            INSERT INTO cuotas_hipoteca
            (nro_cuota, estado, fecha_vencimiento, capital, interes, total)
            VALUES (?, ?, ?, ?, ?, ?)
        ''', (
            cuota['nro_cuota'],
            cuota['estado'],
            cuota['fecha_vencimiento'],
            cuota['capital'],
            cuota['interes'],
            cuota['total']
        ))

    conn.commit()

    # Mostrar resumen
    pagadas = cursor.execute('SELECT COUNT(*) FROM cuotas_hipoteca WHERE estado = "PAGADA"').fetchone()[0]
    impagas = cursor.execute('SELECT COUNT(*) FROM cuotas_hipoteca WHERE estado = "IMPAGA"').fetchone()[0]

    print(f"""
    ✅ Base de datos creada exitosamente: {db_path}

    📊 Resumen:
       • Total de cuotas: {len(datos['cuotas'])}
       • Cuotas pagadas: {pagadas}
       • Cuotas impagas: {impagas}
       • Monto total: {datos['resumen']['monto_total_pendiente'] + datos['resumen']['monto_total_pagado']:.2f} UVAs
       • Monto pagado: {datos['resumen']['monto_total_pagado']:.2f} UVAs
       • Monto pendiente: {datos['resumen']['monto_total_pendiente']:.2f} UVAs
    """)

    conn.close()

def consultar_stats():
    """Muestra estadísticas de la BD"""
    db_path = Path(__file__).parent / 'hipoteca.db'

    if not db_path.exists():
        print("❌ Base de datos no existe. Ejecuta: python3 setup.py create")
        sys.exit(1)

    conn = sqlite3.connect(str(db_path))
    cursor = conn.cursor()

    pagadas = cursor.execute('SELECT COUNT(*) FROM cuotas_hipoteca WHERE estado = "PAGADA"').fetchone()[0]
    impagas = cursor.execute('SELECT COUNT(*) FROM cuotas_hipoteca WHERE estado = "IMPAGA"').fetchone()[0]
    total_pagado = cursor.execute('SELECT SUM(total) FROM cuotas_hipoteca WHERE estado = "PAGADA"').fetchone()[0] or 0
    total_pendiente = cursor.execute('SELECT SUM(total) FROM cuotas_hipoteca WHERE estado = "IMPAGA"').fetchone()[0] or 0

    print(f"""
    📊 Estado del Crédito Hipotecario

       • Cuotas pagadas: {pagadas}
       • Cuotas impagas: {impagas}
       • Monto pagado: {total_pagado:.2f} UVAs
       • Monto pendiente: {total_pendiente:.2f} UVAs
    """)

    # Próxima cuota impaga
    proxima = cursor.execute(
        'SELECT * FROM cuotas_hipoteca WHERE estado = "IMPAGA" ORDER BY fecha_vencimiento LIMIT 1'
    ).fetchone()

    if proxima:
        print(f"\n    🔴 Próxima cuota impaga:")
        print(f"       Cuota #{proxima[1]}: {proxima[3]} (vence {proxima[3]})")

    conn.close()

if __name__ == '__main__':
    if len(sys.argv) > 1 and sys.argv[1] == 'stats':
        consultar_stats()
    else:
        crear_basedatos()
