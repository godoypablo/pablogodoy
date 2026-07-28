#!/bin/bash
# Script de configuración automática para MariaDB

set -e  # Salir si hay error

echo "🚀 Configurador de Hipoteca - MariaDB"
echo "======================================"
echo ""

# 1. Verificar que Python está instalado
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 no encontrado. Instala Python3 primero."
    exit 1
fi

# 2. Verificar que MariaDB está corriendo
if ! pgrep -x "mariadbd\|mysqld" > /dev/null; then
    echo "⚠️  MariaDB no está corriendo."
    echo "   Intenta con: sudo systemctl start mariadb"
    read -p "¿Continuar de todas formas? (s/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        exit 1
    fi
fi

# 3. Instalar dependencias Python si es necesario
echo "📦 Verificando dependencias Python..."
if ! python3 -c "import mysql.connector" 2>/dev/null; then
    echo "   Instalando mysql-connector-python..."
    pip install mysql-connector-python --quiet
    echo "   ✅ Instalado"
else
    echo "   ✅ mysql-connector-python ya está instalado"
fi

# 4. Pedir credenciales
echo ""
echo "🔐 Credenciales de MariaDB"
echo "=========================="
read -p "Host [localhost]: " MARIADB_HOST
MARIADB_HOST=${MARIADB_HOST:-localhost}

read -p "Usuario [root]: " MARIADB_USER
MARIADB_USER=${MARIADB_USER:-root}

read -sp "Contraseña [vacío]: " MARIADB_PASSWORD
echo ""

read -p "Base de datos [hipoteca]: " MARIADB_DATABASE
MARIADB_DATABASE=${MARIADB_DATABASE:-hipoteca}

# 5. Guardar configuración
echo ""
echo "💾 Guardando configuración..."
cat > .env << EOF
MARIADB_HOST=$MARIADB_HOST
MARIADB_USER=$MARIADB_USER
MARIADB_PASSWORD=$MARIADB_PASSWORD
MARIADB_DATABASE=$MARIADB_DATABASE
EOF
echo "   ✅ Guardado en .env"

# 6. Ejecutar setup
echo ""
echo "🏗️  Creando base de datos y tabla..."
python3 setup_mariadb.py

# 7. Mostrar resumen
echo ""
echo "✅ ¡Setup completado!"
echo ""
echo "📊 Próximos pasos:"
echo "   1. Ver estadísticas: python3 setup_mariadb.py stats"
echo "   2. Abrir web: open hipoteca/index.html"
echo "   3. Consultar BD: mysql -u $MARIADB_USER -p $MARIADB_DATABASE"
echo ""
