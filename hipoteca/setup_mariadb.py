#!/usr/bin/env python3
"""
Script para configurar MariaDB con los datos del crédito hipotecario
Compatible con MySQL 5.7+ / MariaDB 10.3+
"""

import json
import sys
from pathlib import Path

try:
    import mysql.connector
    from mysql.connector import Error
except ImportError:
    print("❌ Error: Se requiere 'mysql-connector-python'")
    print("   Instala con: pip install mysql-connector-python")
    sys.exit(1)


class GestorHipoteca:
    def __init__(self, host='localhost', user='root', password='', database='hipoteca'):
        """Inicializa conexión a MariaDB"""
        self.host = host
        self.user = user
        self.password = password
        self.database = database
        self.connection = None

    def conectar(self):
        """Conecta a MariaDB"""
        try:
            self.connection = mysql.connector.connect(
                host=self.host,
                user=self.user,
                password=self.password if self.password else None,
                database=self.database
            )
            print(f"✅ Conectado a MariaDB (host: {self.host})")
            return True
        except Error as e:
            print(f"❌ Error de conexión: {e}")
            return False

    def crear_base_datos(self):
        """Crea la base de datos si no existe"""
        try:
            conn = mysql.connector.connect(
                host=self.host,
                user=self.user,
                password=self.password if self.password else None
            )
            cursor = conn.cursor()
            cursor.execute(f"""
                CREATE DATABASE IF NOT EXISTS {self.database}
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            """)
            conn.commit()
            cursor.close()
            conn.close()
            print(f"✅ Base de datos '{self.database}' verificada/creada")
            return True
        except Error as e:
            print(f"❌ Error al crear BD: {e}")
            return False

    def crear_tabla(self):
        """Crea la tabla de cuotas"""
        sql = """
        CREATE TABLE IF NOT EXISTS cuotas_hipoteca (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nro_cuota INT NOT NULL UNIQUE,
            estado ENUM('PAGADA', 'IMPAGA') NOT NULL DEFAULT 'IMPAGA',
            fecha_vencimiento DATE NOT NULL,
            capital DECIMAL(10, 2) NOT NULL,
            interes DECIMAL(10, 2) NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            fecha_pago DATE NULL,
            observaciones VARCHAR(500),
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """

        try:
            cursor = self.connection.cursor()
            cursor.execute(sql)

            # Crear índices
            cursor.execute("CREATE INDEX IF NOT EXISTS idx_estado ON cuotas_hipoteca(estado)")
            cursor.execute("CREATE INDEX IF NOT EXISTS idx_fecha_vencimiento ON cuotas_hipoteca(fecha_vencimiento)")
            cursor.execute("CREATE INDEX IF NOT EXISTS idx_nro_cuota ON cuotas_hipoteca(nro_cuota)")

            self.connection.commit()
            cursor.close()
            print("✅ Tabla 'cuotas_hipoteca' creada/verificada")
            return True
        except Error as e:
            print(f"❌ Error al crear tabla: {e}")
            return False

    def cargar_datos(self):
        """Carga los datos del JSON a la BD"""
        json_path = Path(__file__).parent / 'datos_cuotas.json'

        if not json_path.exists():
            print(f"❌ Error: No se encontró {json_path}")
            return False

        try:
            with open(json_path, 'r', encoding='utf-8') as f:
                datos = json.load(f)

            cursor = self.connection.cursor()

            # Limpiar tabla
            cursor.execute("TRUNCATE TABLE cuotas_hipoteca")

            # Insertar datos
            sql = """
            INSERT INTO cuotas_hipoteca
            (nro_cuota, estado, fecha_vencimiento, capital, interes, total)
            VALUES (%s, %s, %s, %s, %s, %s)
            """

            for cuota in datos['cuotas']:
                cursor.execute(sql, (
                    cuota['nro_cuota'],
                    cuota['estado'],
                    cuota['fecha_vencimiento'],
                    cuota['capital'],
                    cuota['interes'],
                    cuota['total']
                ))

            self.connection.commit()
            cursor.close()

            print(f"✅ {len(datos['cuotas'])} cuotas cargadas en la BD")
            return True
        except Error as e:
            print(f"❌ Error al cargar datos: {e}")
            return False
        except json.JSONDecodeError as e:
            print(f"❌ Error al leer JSON: {e}")
            return False

    def mostrar_estadisticas(self):
        """Muestra estadísticas de la BD"""
        try:
            cursor = self.connection.cursor(dictionary=True)

            # Estadísticas generales
            cursor.execute("""
                SELECT
                    COUNT(CASE WHEN estado = 'PAGADA' THEN 1 END) as pagadas,
                    COUNT(CASE WHEN estado = 'IMPAGA' THEN 1 END) as impagas,
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'PAGADA' THEN total ELSE 0 END) as monto_pagado,
                    SUM(CASE WHEN estado = 'IMPAGA' THEN total ELSE 0 END) as monto_pendiente
                FROM cuotas_hipoteca
            """)

            stats = cursor.fetchone()

            print(f"""
    📊 Estado del Crédito Hipotecario
       • Cuotas pagadas: {stats['pagadas']}
       • Cuotas impagas: {stats['impagas']}
       • Total cuotas: {stats['total']}
       • Monto pagado: {stats['monto_pagado']:.2f} UVAs
       • Monto pendiente: {stats['monto_pendiente']:.2f} UVAs
            """)

            # Próxima cuota impaga
            cursor.execute("""
                SELECT nro_cuota, fecha_vencimiento, total
                FROM cuotas_hipoteca
                WHERE estado = 'IMPAGA'
                ORDER BY fecha_vencimiento
                LIMIT 1
            """)

            proxima = cursor.fetchone()
            if proxima:
                print(f"""
    🔴 Próxima cuota impaga:
       Cuota #{proxima['nro_cuota']}: {proxima['total']:.2f} UVAs
       Vencimiento: {proxima['fecha_vencimiento']}
                """)

            cursor.close()
        except Error as e:
            print(f"❌ Error al obtener estadísticas: {e}")

    def desconectar(self):
        """Cierra la conexión"""
        if self.connection:
            self.connection.close()
            print("✅ Desconectado de MariaDB")


def main():
    """Función principal"""
    # Configuración (EDITA ESTOS VALORES)
    HOST = 'localhost'
    USER = 'root'
    PASSWORD = ''  # Cambiar si tienes contraseña
    DATABASE = 'hipoteca'

    gestor = GestorHipoteca(host=HOST, user=USER, password=PASSWORD, database=DATABASE)

    if len(sys.argv) > 1 and sys.argv[1] == 'stats':
        # Mostrar estadísticas
        if gestor.conectar():
            gestor.mostrar_estadisticas()
            gestor.desconectar()
    else:
        # Crear/actualizar BD
        if not gestor.crear_base_datos():
            sys.exit(1)

        if not gestor.conectar():
            sys.exit(1)

        if not gestor.crear_tabla():
            gestor.desconectar()
            sys.exit(1)

        if not gestor.cargar_datos():
            gestor.desconectar()
            sys.exit(1)

        gestor.mostrar_estadisticas()
        gestor.desconectar()

        print("""
    ✅ Setup completado exitosamente

    Próximos pasos:
       1. Usa 'python3 setup_mariadb.py stats' para ver estadísticas
       2. Abre index.html en navegador para gestionar cuotas
       3. Consulta la BD desde tu aplicación web/backend
        """)


if __name__ == '__main__':
    main()
