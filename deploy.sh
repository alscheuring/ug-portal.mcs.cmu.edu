#!/bin/bash

# =============================================================================
# Production Deployment Script for CMU UG Portal
# =============================================================================
#
# This script handles the complete deployment process for the production
# Laravel application including code updates, optimization, and maintenance.
#
# Usage: ./deploy.sh [--skip-backup]
#
# Make sure to run this script from the application root directory
# =============================================================================

set -e  # Exit on any error


# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
APP_NAME="CMU UG Portal"
BACKUP_DIR="/var/backups/ug-portal"
MAINTENANCE_FILE="storage/framework/maintenance.php"
SKIP_BACKUP=false

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --skip-backup)
            SKIP_BACKUP=true
            shift
            ;;
        --help|-h)
            echo "Usage: $0 [--skip-backup] [--help]"
            echo ""
            echo "Options:"
            echo "  --skip-backup    Skip database backup (not recommended)"
            echo "  --help, -h       Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# Functions
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

check_requirements() {
    log_info "Checking deployment requirements..."

    # Check if we're in the right directory
    if [[ ! -f "artisan" ]]; then
        log_error "artisan file not found. Please run this script from the Laravel application root directory."
        exit 1
    fi

    # Check if git is available
    if ! command -v git &> /dev/null; then
        log_error "Git is not installed or not in PATH"
        exit 1
    fi

    # Check if composer is available
    if ! command -v composer &> /dev/null; then
        log_error "Composer is not installed or not in PATH"
        exit 1
    fi

    # Check if npm is available
    if ! command -v npm &> /dev/null; then
        log_warning "npm is not installed. Skipping asset compilation."
    fi

    log_success "Requirements check passed"
}

backup_database() {
    if [[ "$SKIP_BACKUP" == true ]]; then
        log_warning "Skipping database backup (--skip-backup flag used)"
        BACKUP_FILE="(skipped)"
        return 0
    fi

    log_info "Creating database backup..."

    # Create backup directory if it doesn't exist
    mkdir -p "$BACKUP_DIR"

    # Generate backup filename with timestamp
    BACKUP_FILE="$BACKUP_DIR/database-backup-$(date +%Y%m%d-%H%M%S).sql"

    # Get database credentials from .env file
    DB_HOST=$(grep DB_HOST .env | cut -d '=' -f2 | tr -d '"')
    DB_DATABASE=$(grep DB_DATABASE .env | cut -d '=' -f2 | tr -d '"')
    DB_USERNAME=$(grep DB_USERNAME .env | cut -d '=' -f2 | tr -d '"')
    DB_PASSWORD=$(grep DB_PASSWORD .env | cut -d '=' -f2 | tr -d '"')

    # Create database backup with options for limited privileges
    log_info "Using mysqldump with limited privilege options..."
    if mysqldump \
        -h "$DB_HOST" \
        -u "$DB_USERNAME" \
        -p"$DB_PASSWORD" \
        --single-transaction \
        --routines=false \
        --triggers=false \
        --no-tablespaces \
        --skip-lock-tables \
        --skip-add-locks \
        "$DB_DATABASE" > "$BACKUP_FILE"; then
        log_success "Database backup created: $BACKUP_FILE"
    else
        log_warning "Standard backup failed, trying basic backup without triggers/routines..."
        # Fallback: even more basic backup
        if mysqldump \
            -h "$DB_HOST" \
            -u "$DB_USERNAME" \
            -p"$DB_PASSWORD" \
            --single-transaction \
            --no-tablespaces \
            --skip-lock-tables \
            --skip-add-locks \
            --skip-triggers \
            --skip-routines \
            "$DB_DATABASE" > "$BACKUP_FILE"; then
            log_success "Basic database backup created: $BACKUP_FILE"
        else
            log_error "Failed to create database backup even with limited options"
            log_warning "Continuing deployment without backup (not recommended)"
            BACKUP_FILE="(failed)"
            # Don't exit - continue deployment but warn user
        fi
    fi
}

enable_maintenance_mode() {
    log_info "Enabling maintenance mode..."
    php artisan down --refresh=15 --secret="deployment-in-progress-$(date +%s)"
    log_success "Maintenance mode enabled"
}

disable_maintenance_mode() {
    log_info "Disabling maintenance mode..."
    php artisan up
    log_success "Maintenance mode disabled"
}

pull_latest_code() {
    log_info "Pulling latest code from repository..."

    # Stash any local changes
    if git diff --quiet && git diff --staged --quiet; then
        log_info "No local changes to stash"
    else
        log_warning "Stashing local changes..."
        git stash push -m "Deployment stash - $(date)"
    fi

    # Pull latest code
    git pull origin main

    # Show the latest commit
    log_info "Latest commit: $(git log -1 --oneline)"
    log_success "Code updated successfully"
}

install_dependencies() {
    log_info "Installing/updating Composer dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
    log_success "Composer dependencies updated"

    if command -v npm &> /dev/null; then
        log_info "Installing/updating npm dependencies..."
        npm ci --production
        log_success "npm dependencies updated"
    fi
}

run_migrations() {
    log_info "Running database migrations..."
    php artisan migrate --force
    log_success "Database migrations completed"
}

build_assets() {
    if command -v npm &> /dev/null; then
        log_info "Building production assets..."
        npm run build
        log_success "Assets built successfully"
    else
        log_warning "Skipping asset build (npm not available)"
    fi
}

optimize_application() {
    log_info "Optimizing application for production..."

    # Clear all caches first
    log_info "Clearing existing caches..."
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan event:clear

    # Clear Filament caches
    log_info "Clearing Filament caches..."
    php artisan filament:clear-cached-components 2>/dev/null || true
    php artisan icons:clear 2>/dev/null || true

    # Clear Layup caches if available
    log_info "Clearing Layup caches..."
    php artisan layup:clear-cache 2>/dev/null || true

    # Create optimized caches
    log_info "Creating optimized caches..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # Generate Layup safelist for Tailwind
    log_info "Generating Layup Tailwind safelist..."
    php artisan layup:safelist 2>/dev/null || true

    # Optimize autoloader
    log_info "Optimizing autoloader..."
    composer dump-autoload --optimize --no-dev

    # Filament optimizations
    log_info "Optimizing Filament..."
    php artisan filament:optimize 2>/dev/null || true
    php artisan icons:cache 2>/dev/null || true

    log_success "Application optimized for production"
}

restart_services() {
    log_info "Restarting services..."

    # Restart queue workers if horizon is running
    if php artisan horizon:status 2>/dev/null | grep -q "running"; then
        log_info "Restarting Horizon queue workers..."

        # Try graceful termination first
        if php artisan horizon:terminate 2>/dev/null; then
            log_success "Horizon workers terminated gracefully"
            # Wait a moment for graceful shutdown
            sleep 5
        else
            log_warning "Horizon graceful termination failed (permission issue)"
            log_warning "Horizon workers may need manual restart by system administrator"
            log_info "Alternative: Use 'php artisan queue:restart' for regular queue workers"

            # Fall back to regular queue restart
            php artisan queue:restart
        fi
    else
        # Restart standard queue workers
        log_info "Restarting queue workers..."
        php artisan queue:restart
    fi

    # Restart PHP-FPM if available and we have sudo access
    if command -v systemctl &> /dev/null; then
        if systemctl is-active --quiet php8.3-fpm 2>/dev/null; then
            log_info "Attempting to restart PHP-FPM..."
            if sudo -n systemctl restart php8.3-fpm 2>/dev/null; then
                log_success "PHP-FPM restarted successfully"
            else
                log_warning "PHP-FPM restart failed (permission issue)"
                log_info "Manual restart may be needed: sudo systemctl restart php8.3-fpm"
            fi
        else
            log_info "PHP-FPM not running or not available via systemctl"
        fi
    fi

    log_success "Service restart completed (check warnings above for any manual steps needed)"
}

set_file_permissions() {
    log_info "Setting proper file permissions..."

    # Set general file and directory permissions
    find . -type f -exec chmod 644 {} \;
    find . -type d -exec chmod 755 {} \;

    # Make artisan executable
    chmod +x artisan

    # Set storage and bootstrap/cache permissions
    if [ -d "storage" ]; then
        chmod -R 775 storage
        log_success "Storage directory permissions set to 775"
    fi

    if [ -d "bootstrap/cache" ]; then
        chmod -R 775 bootstrap/cache
        log_success "Bootstrap cache directory permissions set to 775"
    fi

    # Try to set web server ownership (if we have sudo access)
    if command -v systemctl &> /dev/null; then
        # Try to detect web server user
        WEB_USER=""
        if id www-data &>/dev/null; then
            WEB_USER="www-data"
        elif id nginx &>/dev/null; then
            WEB_USER="nginx"
        elif id apache &>/dev/null; then
            WEB_USER="apache"
        fi

        if [ -n "$WEB_USER" ]; then
            log_info "Attempting to set web server ownership ($WEB_USER)..."
            if sudo -n chown -R "$WEB_USER:$WEB_USER" storage bootstrap/cache 2>/dev/null; then
                log_success "Web server ownership set successfully"
            else
                log_warning "Could not set web server ownership (permission issue)"
                log_info "Manual command: sudo chown -R $WEB_USER:$WEB_USER storage bootstrap/cache"
            fi
        else
            log_warning "Could not detect web server user (www-data, nginx, or apache)"
            log_info "You may need to manually set ownership of storage/ and bootstrap/cache/"
        fi
    fi

    log_success "File permissions configured"
}

verify_deployment() {
    log_info "Verifying deployment..."

    # Check if the application is responding
    if php artisan tinker --execute="echo 'Application loaded successfully';" > /dev/null 2>&1; then
        log_success "Application is responding correctly"
    else
        log_error "Application verification failed"
        exit 1
    fi

    # Check database connection
    if php artisan db:show > /dev/null 2>&1; then
        log_success "Database connection verified"
    else
        log_error "Database connection failed"
        exit 1
    fi

    # Check Horizon status (informational only)
    log_info "Checking Horizon status..."
    if php artisan horizon:status 2>/dev/null | grep -q "running"; then
        log_success "Horizon is running"
    else
        log_warning "Horizon is not running - may need manual restart"
        log_info "To start Horizon: php artisan horizon"
    fi

    # Check queue worker status
    log_info "Queue workers should pick up new jobs automatically"
    log_success "Deployment verification completed"
}

post_deployment_notes() {
    log_info "Post-deployment manual steps (if needed)..."

    # Check if Horizon needs manual restart
    if ! php artisan horizon:status 2>/dev/null | grep -q "running"; then
        echo ""
        log_warning "Horizon Queue Workers:"
        echo "  Horizon is not running. To start it manually:"
        echo "  php artisan horizon"
        echo ""
    fi

    # Check if there were any service restart issues
    if ! systemctl is-active --quiet php8.3-fpm 2>/dev/null; then
        log_warning "PHP-FPM Status:"
        echo "  If PHP-FPM needs restarting:"
        echo "  sudo systemctl restart php8.3-fpm"
        echo ""
    fi

    echo "For ongoing monitoring:"
    echo "- Application logs: tail -f storage/logs/laravel.log"
    echo "- Horizon dashboard: /horizon (if configured)"
    echo "- Queue status: php artisan queue:work --once (to test)"
}

cleanup_old_backups() {
    log_info "Cleaning up old backups (keeping last 10)..."

    if [[ -d "$BACKUP_DIR" ]]; then
        # Keep only the 10 most recent backup files
        find "$BACKUP_DIR" -name "database-backup-*.sql" -type f | sort -r | tail -n +11 | xargs rm -f
        log_success "Old backups cleaned up"
    fi
}

# Main deployment process
main() {
    log_info "Starting deployment of $APP_NAME"
    echo "============================================="

    # Pre-deployment checks
    check_requirements

    # Create database backup
    backup_database

    # Enable maintenance mode
    enable_maintenance_mode

    # Ensure we disable maintenance mode even if something fails
    trap 'disable_maintenance_mode' EXIT

    # Update code and dependencies
    pull_latest_code
    install_dependencies

    # Run migrations
    run_migrations

    # Build assets
    build_assets

    # Optimize for production
    optimize_application

    # Set proper file permissions
    set_file_permissions

    # Restart services
    restart_services

    # Verify everything is working
    verify_deployment

    # Cleanup
    cleanup_old_backups

    # Disable maintenance mode (also done by trap)
    disable_maintenance_mode

    echo "============================================="
    log_success "Deployment completed successfully!"
    log_info "Application is now running the latest version"

    # Show deployment summary
    echo ""
    echo "Deployment Summary:"
    echo "- Latest commit: $(git log -1 --oneline)"
    echo "- Deployed at: $(date)"
    if [[ "$BACKUP_FILE" != "(skipped)" && "$BACKUP_FILE" != "(failed)" ]]; then
        echo "- Backup created: $BACKUP_FILE"
    elif [[ "$BACKUP_FILE" == "(skipped)" ]]; then
        echo "- Backup: Skipped by user request"
    else
        echo "- Backup: Failed (deployment continued)"
    fi

    # Show any required manual steps
    post_deployment_notes
}

# Run deployment
main "$@"
