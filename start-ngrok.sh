#!/bin/bash

# 🌍 NGROK START SCRIPT - Mac/Linux
# Sistema de Seguimiento de Documentos Contractuales
# Inicia Ngrok para exponer tu API localmente

set -e

clear

echo "╔════════════════════════════════════════════════════════════════════════╗"
echo "║                                                                        ║"
echo "║                    🌍 NGROK TUNNEL STARTER                            ║"
echo "║              Expone tu API local a través de Internet                 ║"
echo "║                                                                        ║"
echo "╚════════════════════════════════════════════════════════════════════════╝"
echo ""

# Verificar que ngrok está instalado
if ! command -v ngrok &> /dev/null; then
    echo "❌ Ngrok no está instalado"
    echo ""
    echo "Instala con:"
    echo "  npm install -g ngrok"
    echo ""
    echo "O descarga desde:"
    echo "  https://ngrok.com/download"
    echo ""
    exit 1
fi

echo "✅ Ngrok detectado"

# Verificar autenticación
if [ ! -f "$HOME/.ngrok2/ngrok.yml" ]; then
    echo "⚠️  No hay token de autenticación configurado"
    echo ""
    echo "Ejecuta primero:"
    echo "  ngrok config add-authtoken TU_TOKEN"
    echo ""
    echo "Obtén tu token en: https://dashboard.ngrok.com/auth/your-authtoken"
    echo ""
    exit 1
fi

echo "✅ Token de autenticación encontrado"
echo ""

# Menu de opciones
echo "Selecciona qué puerto exponer:"
echo ""
echo "  1) API (8000) - Recomendado"
echo "  2) Frontend (5173)"
echo "  3) Ambos (múltiples tunnels)"
echo "  4) Puerto personalizado"
echo ""

read -p "Ingresa opción (1-4): " OPTION

echo ""

case $OPTION in
    1)
        echo "🚀 Exponiendo API en puerto 8000..."
        echo ""
        ngrok http 8000
        ;;
    2)
        echo "🚀 Exponiendo Frontend en puerto 5173..."
        echo ""
        ngrok http 5173
        ;;
    3)
        echo "⚠️  Para múltiples tunnels, abre varias terminales:"
        echo ""
        echo "Terminal 1:"
        echo "  ngrok http 8000"
        echo ""
        echo "Terminal 2:"
        echo "  ngrok http 5173"
        echo ""
        ;;
    4)
        read -p "Ingresa puerto: " PORT
        echo "🚀 Exponiendo puerto $PORT..."
        echo ""
        ngrok http $PORT
        ;;
    *)
        echo "❌ Opción inválida"
        exit 1
        ;;
esac

echo ""
echo "════════════════════════════════════════════════════════════════════════"
echo ""
echo "💡 TIPS:"
echo ""
echo "  📊 Dashboard: http://localhost:4040"
echo "  🛑 Para detener: Ctrl + C"
echo "  🔗 URL pública se muestra arriba"
echo ""
echo "════════════════════════════════════════════════════════════════════════"
