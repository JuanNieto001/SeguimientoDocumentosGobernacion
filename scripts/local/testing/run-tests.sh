#!/bin/bash

# ============================================================================
# SCRIPT DE EJECUCION DE PRUEBAS CYPRESS - FASE 3
# Sistema de Seguimiento de Documentos Contractuales - Gobernación de Caldas
# ============================================================================

set -e

echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║  FASE 3 - AUTOMATIZACIÓN CON CYPRESS                                      ║"
echo "║  Sistema de Seguimiento de Documentos Contractuales                        ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
REPORT_DIR="cypress/reports"
SCREENSHOTS_DIR="cypress/screenshots"
VIDEOS_DIR="cypress/videos"
RESULTS_FILE="$REPORT_DIR/results_${TIMESTAMP}.json"

# ============================================================================
# FUNCIONES AUXILIARES
# ============================================================================

print_step() {
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}▶ $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

check_requirement() {
    if ! command -v $1 &> /dev/null; then
        print_error "$1 no está instalado"
        exit 1
    fi
    print_success "$1 disponible"
}

# ============================================================================
# VALIDACIONES PREVIAS
# ============================================================================

print_step "VALIDACIONES PREVIAS"

check_requirement "node"
check_requirement "npm"

# Verificar que estamos en el directorio correcto
if [ ! -f "package.json" ]; then
    print_error "package.json no encontrado. Ejecuta este script desde la raíz del proyecto."
    exit 1
fi
print_success "Configuración del proyecto verificada"

# Crear directorios si no existen
mkdir -p "$REPORT_DIR"
mkdir -p "$SCREENSHOTS_DIR"
mkdir -p "$VIDEOS_DIR"
print_success "Directorios de reporte creados"

# Verificar .env.testing
if [ ! -f ".env.testing" ] && [ ! -f "cypress.config.js" ]; then
    print_warning "No se encontró configuración de ambiente. Usando valores por defecto."
    print_warning "Para usar credenciales personalizadas, crea .env.testing"
fi
print_success "Configuración de ambiente verificada"

# ============================================================================
# INSTALACION DE DEPENDENCIAS
# ============================================================================

print_step "INSTALACION DE DEPENDENCIAS"

if [ ! -d "node_modules" ]; then
    echo "Instalando dependencias npm..."
    npm install
    print_success "Dependencias instaladas"
else
    print_success "Dependencias ya instaladas"
fi

# Verificar Cypress
if ! npx cypress --version > /dev/null 2>&1; then
    print_error "Cypress no está disponible"
    echo "Ejecuta: npm install cypress"
    exit 1
fi
print_success "Cypress verificado"

# ============================================================================
# SELECCION DE TIPO DE EJECUCIÓN
# ============================================================================

print_step "SELECCION DE TIPO DE EJECUCIÓN"

echo -e "${YELLOW}Selecciona el tipo de ejecución:${NC}"
echo ""
echo "  1) Ejecutar TODOS los tests (modo headless)"
echo "  2) Ejecutar module específico"
echo "  3) Modo interactivo (Cypress UI)"
echo "  4) Ejecutar con video y reporte"
echo "  5) Ejecutar autenticación (AUTH)"
echo "  6) Ejecutar dashboard (DASH)"
echo "  7) Ejecutar procesos (PROC)"
echo "  8) Ejecutar contratación directa (CDPN)"
echo "  9) Ejecutar dashboard builder (BUILD)"
echo " 10) Ejecutar seguridad (SEC)"
echo ""

read -p "Ingresa opción (1-10): " OPTION

# ============================================================================
# EJECUCIÓN SEGUN OPCION
# ============================================================================

case $OPTION in
    1)
        print_step "EJECUTANDO TODOS LOS TESTS (modo headless)"
        npm run cypress:run
        ;;
    2)
        print_step "SELECCIONA MÓDULO"
        echo "Módulos disponibles:"
        echo "  1) 01-authentication"
        echo "  2) 02-dashboard"
        echo "  3) 03-procesos"
        echo "  4) 04-contratacion-directa"
        echo "  5) 05-dashboard-builder"
        echo "  6) 06-seguridad-rendimiento"
        read -p "Ingresa módulo: " MODULE
        npx cypress run --spec "cypress/e2e/$MODULE/*.cy.js"
        ;;
    3)
        print_step "ABRIENDO CYPRESS EN MODO INTERACTIVO"
        npx cypress open
        ;;
    4)
        print_step "EJECUTANDO CON VIDEO Y REPORTE"
        npx cypress run --spec "cypress/e2e/**/*.cy.js" --record --reporter json --reporter-options reportDir="$REPORT_DIR",reportFilename="results_${TIMESTAMP}.json"
        ;;
    5)
        print_step "EJECUTANDO TESTS DE AUTENTICACION (AUTH-001 a AUTH-011)"
        npx cypress run --spec "cypress/e2e/01-authentication/auth-completo.cy.js"
        ;;
    6)
        print_step "EJECUTANDO TESTS DE DASHBOARD (DASH-001 a DASH-015)"
        npx cypress run --spec "cypress/e2e/02-dashboard/dashboard-completo.cy.js"
        ;;
    7)
        print_step "EJECUTANDO TESTS DE PROCESOS (PROC-001 a PROC-020)"
        npx cypress run --spec "cypress/e2e/03-procesos/procesos-completo.cy.js"
        ;;
    8)
        print_step "EJECUTANDO TESTS CONTRATACION DIRECTA (CDPN-001 a CDPN-033)"
        npx cypress run --spec "cypress/e2e/04-contratacion-directa/cdpn-completo.cy.js"
        ;;
    9)
        print_step "EJECUTANDO TESTS DASHBOARD BUILDER (BUILD-001 a BUILD-040)"
        npx cypress run --spec "cypress/e2e/05-dashboard-builder/dashboard-builder.cy.js"
        ;;
    10)
        print_step "EJECUTANDO TESTS DE SEGURIDAD (SEC-001 a SEC-008)"
        npx cypress run --spec "cypress/e2e/06-seguridad-rendimiento/seguridad-rendimiento.cy.js"
        ;;
    *)
        print_error "Opción inválida"
        exit 1
        ;;
esac

# ============================================================================
# POST-EJECUCIÓN
# ============================================================================

print_step "GENERACION DE REPORTE"

echo ""
print_success "Tests completados"
echo ""
echo -e "${GREEN}📊 Resultados guardados en:${NC}"
echo "   Screenshots: $SCREENSHOTS_DIR"
echo "   Videos: $VIDEOS_DIR"
echo "   Reportes: $REPORT_DIR"
echo ""

if [ -f "$RESULTS_FILE" ]; then
    TOTAL_TESTS=$(jq '.stats.tests' "$RESULTS_FILE" 2>/dev/null || echo "N/A")
    PASSED=$(jq '.stats.passes' "$RESULTS_FILE" 2>/dev/null || echo "N/A")
    FAILED=$(jq '.stats.failures' "$RESULTS_FILE" 2>/dev/null || echo "N/A")
    DURATION=$(jq '.stats.duration' "$RESULTS_FILE" 2>/dev/null || echo "N/A")

    echo -e "${BLUE}RESUMEN:${NC}"
    echo "   Total: $TOTAL_TESTS"
    echo "   ✓ Exitosos: $PASSED"
    echo "   ✗ Fallidos: $FAILED"
    echo "   ⏱ Duración: ${DURATION}ms"
fi

echo ""
echo -e "${GREEN}✅ FASE 3 completada exitosamente${NC}"
echo ""
