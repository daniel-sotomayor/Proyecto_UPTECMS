#!/bin/bash
# ============================================================================
# Script de Instalación Automatizada - Sistema SBN_MCP
# Sistema de Gestión de Bienes Nacionales - Maternidad Concepción Palacios
# ============================================================================

set -e  # Salir en caso de error

echo "🚀 Instalación Automatizada del Sistema SBN_MCP"
echo "================================================"
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funciones de utilidad
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Verificar si se ejecuta como root (para instalación de paquetes)
check_root() {
    if [[ $EUID -eq 0 ]]; then
        log_warning "Ejecutándose como root. Algunos comandos se ajustarán."
        IS_ROOT=true
    else
        IS_ROOT=false
    fi
}

# Detectar sistema operativo
detect_os() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if [ -f /etc/debian_version ]; then
            OS="debian"
            log_info "Sistema detectado: Debian/Ubuntu"
        elif [ -f /etc/redhat-release ]; then
            OS="redhat"
            log_info "Sistema detectado: RedHat/CentOS"
        else
            OS="linux"
            log_info "Sistema detectado: Linux genérico"
        fi
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        OS="macos"
        log_info "Sistema detectado: macOS"
    else
        OS="unknown"
        log_warning "Sistema operativo no reconocido"
    fi
}

# Verificar dependencias del sistema
check_dependencies() {
    log_info "Verificando dependencias del sistema..."
    
    # PHP
    if ! command -v php &> /dev/null; then
        log_error "PHP no está instalado"
        if [[ $OS == "debian" ]]; then
            log_info "Instalar con: sudo apt-get install php php-mysql php-gd php-mbstring php-json php-curl"
        elif [[ $OS == "redhat" ]]; then
            log_info "Instalar con: sudo yum install php php-mysql php-gd php-mbstring php-json php-curl"
        fi
        exit 1
    else
        PHP_VERSION=$(php -r "echo PHP_VERSION;")
        log_success "PHP $PHP_VERSION encontrado"
    fi
    
    # Composer
    if ! command -v composer &> /dev/null; then
        log_warning "Composer no encontrado. Instalando..."
        install_composer
    else
        log_success "Composer encontrado"
    fi
    
    # MySQL/MariaDB
    if command -v mysql &> /dev/null; then
        log_success "MySQL encontrado"
    elif command -v mariadb &> /dev/null; then
        log_success "MariaDB encontrado"
    else
        log_error "MySQL/MariaDB no encontrado"
        exit 1
    fi
}

# Instalar Composer
install_composer() {
    log_info "Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php
    if [[ $IS_ROOT == true ]]; then
        mv composer.phar /usr/local/bin/composer
    else
        mkdir -p ~/.local/bin
        mv composer.phar ~/.local/bin/composer
        export PATH="$HOME/.local/bin:$PATH"
    fi
    log_success "Composer instalado"
}

# Configurar directorio del proyecto
setup_project_directory() {
    log_info "Configurando directorio del proyecto..."
    
    # Obtener directorio actual
    PROJECT_DIR=$(pwd)
    log_info "Directorio del proyecto: $PROJECT_DIR"
    
    # Crear directorios necesarios
    mkdir -p logs uploads uploads/bienes reports backups
    
    # Configurar permisos
    chmod -R 755 .
    chmod -R 777 logs uploads reports backups
    
    log_success "Directorios configurados"
}

# Instalar dependencias PHP
install_php_dependencies() {
    log_info "Instalando dependencias PHP..."
    
    if [ -f "composer.json" ]; then
        composer install --no-dev --optimize-autoloader
        log_success "Dependencias PHP instaladas"
    else
        log_error "composer.json no encontrado"
        exit 1
    fi
}

# Configurar archivo .env
setup_environment() {
    log_info "Configurando archivo de entorno..."
    
    if [ ! -f ".env" ]; then
        if [ -f ".env.example" ]; then
            cp .env.example .env
            log_success "Archivo .env creado desde .env.example"
        else
            create_default_env
        fi
    else
        log_warning "Archivo .env ya existe, no se sobrescribirá"
    fi
    
    # Generar APP_KEY si no existe
    if grep -q "CHANGE_THIS_TO_SECURE_RANDOM_KEY" .env; then
        log_info "Generando clave de aplicación..."
        APP_KEY=$(openssl rand -base64 32)
        sed -i "s/CHANGE_THIS_TO_SECURE_RANDOM_KEY_32_CHARS/$APP_KEY/" .env
        log_success "Clave de aplicación generada"
    fi
}

# Crear archivo .env por defecto
create_default_env() {
    cat > .env << EOF
# Configuración de Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hospital_bienes
DB_USER=root
DB_PASS=

# Configuración de Aplicación
APP_ENV=development
APP_DEBUG=true
APP_TIMEZONE=America/Caracas
APP_KEY=$(openssl rand -base64 32)

# Configuración de Seguridad
SESSION_LIFETIME=30
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=900

# Configuración de Archivos
MAX_FILE_SIZE=5242880
ALLOWED_FILE_TYPES=jpg,jpeg,png,webp

# Rate Limiting
RATE_LIMIT_REQUESTS=60
RATE_LIMIT_WINDOW=3600

# Correo
MAIL_CONTACTO=bienes@mcp.gob.ve
EOF
    log_success "Archivo .env creado con configuración por defecto"
}

# Configurar base de datos
setup_database() {
    log_info "Configurando base de datos..."
    
    # Solicitar credenciales de MySQL
    echo ""
    read -p "Usuario de MySQL (root): " DB_USER
    DB_USER=${DB_USER:-root}
    
    read -s -p "Contraseña de MySQL: " DB_PASS
    echo ""
    
    read -p "Nombre de la base de datos (hospital_bienes): " DB_NAME
    DB_NAME=${DB_NAME:-hospital_bienes}
    
    # Actualizar .env con credenciales
    sed -i "s/DB_USER=root/DB_USER=$DB_USER/" .env
    sed -i "s/DB_PASS=/DB_PASS=$DB_PASS/" .env
    sed -i "s/DB_NAME=hospital_bienes/DB_NAME=$DB_NAME/" .env
    
    # Crear base de datos
    log_info "Creando base de datos..."
    mysql -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    # Importar estructura
    if [ -f "sql/hospital_bienes_DEFINITIVO.sql" ]; then
        log_info "Importando estructura de base de datos..."
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < sql/hospital_bienes_DEFINITIVO.sql
        log_success "Base de datos configurada"
    else
        log_error "Archivo SQL no encontrado: sql/hospital_bienes_DEFINITIVO.sql"
        exit 1
    fi
}

# Configurar servidor web
setup_webserver() {
    log_info "Configurando servidor web..."
    
    # Crear .htaccess de seguridad en uploads
    cat > uploads/.htaccess << 'EOF'
RemoveHandler .php .phtml .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi
php_flag engine off
Options -ExecCGI
AddType text/plain .php .phtml .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi
EOF
    
    log_success "Configuración de servidor web completada"
}

# Ejecutar verificaciones del sistema
run_system_check() {
    log_info "Ejecutando verificaciones del sistema..."
    
    if [ -f "check_system.php" ]; then
        php check_system.php
    else
        log_warning "Script de verificación no encontrado"
    fi
}

# Mostrar información final
show_final_info() {
    echo ""
    echo "🎉 ¡Instalación completada!"
    echo "=========================="
    echo ""
    log_success "El sistema SBN_MCP ha sido instalado correctamente"
    echo ""
    echo "📋 Información de acceso:"
    echo "   URL: http://localhost/SBN_MCP/public/"
    echo "   Usuario: admin"
    echo "   Contraseña: Admin_bn"
    echo ""
    echo "⚠️  IMPORTANTE:"
    echo "   - Cambiar la contraseña admin en el primer login"
    echo "   - Revisar configuración en .env"
    echo "   - Para producción: establecer APP_DEBUG=false"
    echo ""
    echo "📚 Documentación:"
    echo "   - docs/SISTEMA_COMPLETO.md - Documentación completa"
    echo "   - docs/SECURITY_FIXES.md - Correcciones de seguridad"
    echo "   - README.md - Guía de instalación"
    echo ""
    echo "🧪 Pruebas:"
    echo "   - php check_system.php - Verificar sistema"
    echo "   - php tests/SystemTest.php - Pruebas completas"
    echo ""
}

# Función principal
main() {
    echo "Iniciando instalación automatizada..."
    echo ""
    
    check_root
    detect_os
    check_dependencies
    setup_project_directory
    install_php_dependencies
    setup_environment
    
    # Preguntar si configurar base de datos
    echo ""
    read -p "¿Configurar base de datos? (y/N): " SETUP_DB
    if [[ $SETUP_DB =~ ^[Yy]$ ]]; then
        setup_database
    else
        log_info "Configuración de base de datos omitida"
        log_warning "Configurar manualmente en .env y ejecutar SQL"
    fi
    
    setup_webserver
    run_system_check
    show_final_info
}

# Manejo de errores
trap 'log_error "Error durante la instalación. Revise los logs."; exit 1' ERR

# Ejecutar instalación
main "$@"