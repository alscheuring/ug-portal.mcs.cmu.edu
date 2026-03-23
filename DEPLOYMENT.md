# Deployment Guide

This document explains how to deploy the CMU UG Portal to production.

## Prerequisites

Before running the deployment scripts, ensure:

1. **Server Access**: SSH access to the production server
2. **Dependencies**: Git, Composer, PHP, MySQL, and npm are installed
3. **Permissions**: Web server has proper file permissions
4. **Backup Directory**: `/var/backups/ug-portal` directory exists and is writable
5. **Environment**: `.env` file is properly configured for production

## File Permissions

The deployment script automatically sets proper file permissions:

- **General Files**: 644 permissions for security
- **Directories**: 755 permissions for proper access
- **Storage Directory**: 775 permissions for web server write access
- **Bootstrap Cache**: 775 permissions for Laravel caching
- **Web Server Ownership**: Attempts to set proper ownership for `www-data`, `nginx`, or `apache` users

## Quick Start

### Deploy Latest Code

```bash
# Standard deployment
./deploy.sh

# Deploy without database backup (if privileges are insufficient)
./deploy.sh --skip-backup

# Check database privileges first
./check-db-privileges.sh
```

This command will:
- Create a database backup
- Enable maintenance mode
- Pull the latest code from the main branch
- Install/update dependencies
- Run database migrations
- Build production assets
- Optimize the application
- Restart services
- Verify the deployment
- Disable maintenance mode

### Rollback (if needed)

```bash
# Rollback code only
./rollback.sh

# Rollback code and database
./rollback.sh --with-database
```

## Detailed Process

### 1. Pre-Deployment

- **Test Locally**: Always test changes in development first
- **Review Changes**: Review what will be deployed using `git log`
- **Backup Verification**: Ensure backup directory is accessible
- **Maintenance Window**: Consider scheduling during low-traffic periods

### 2. Deployment Script Features

The deployment script (`deploy.sh`) performs these actions in order:

1. **Requirements Check**: Verifies all necessary tools are available
2. **Database Backup**: Creates timestamped MySQL dump
3. **Maintenance Mode**: Enables Laravel maintenance mode with custom message
4. **Code Update**: Pulls latest code from Git repository
5. **Dependencies**: Updates Composer and npm packages
6. **Database**: Runs Laravel migrations
7. **Assets**: Builds production CSS/JS assets
8. **Optimization**: Caches config, routes, views, and events
9. **File Permissions**: Sets proper permissions and web server ownership
10. **Services**: Restarts queue workers and PHP-FPM
10. **Verification**: Tests application and database connectivity
11. **Cleanup**: Removes old backup files (keeps last 10)

### 3. Rollback Script Features

The rollback script (`rollback.sh`) can:

- Roll back code to the previous Git commit
- Optionally restore database from the most recent backup
- Reinstall dependencies for the rolled-back version
- Clear caches and restart services
- Verify the rollback was successful

### 4. Safety Features

- **Automatic Maintenance Mode**: Prevents user access during deployment
- **Database Backups**: Automatic backup before each deployment
- **Error Handling**: Script stops immediately if any step fails
- **Service Restart**: Ensures all services pick up new code
- **Verification**: Confirms application is working after deployment

## Environment Specific Notes

### Production Server

- **Web Server**: Nginx/Apache should point to `public/` directory
- **PHP Version**: Ensure PHP 8.3+ is active
- **Queue Workers**: Laravel Horizon manages queue processing
- **SSL Certificates**: Ensure HTTPS certificates are valid
- **File Permissions**: Web server user should own storage and bootstrap/cache

### Application-Specific Components

- **Filament Admin Panel**: Accessible at `/admin` - includes user management, content management
- **Student Panel**: Accessible at `/student` - for student-specific features
- **Layup Page Builder**: Visual page builder for creating department portal pages
- **OAuth Authentication**: CMU Google Workspace integration for user authentication
- **Multi-tenancy**: Department-based team system with separate portals

### Database

- **MySQL Version**: Ensure compatibility with Laravel requirements
- **Backup Storage**: Regular automated backups recommended
- **Migration Testing**: Test migrations on staging first

## Troubleshooting

### Common Issues

1. **Database Backup Permission Errors**:
   ```bash
   # Check what privileges your database user has
   ./check-db-privileges.sh

   # If backup fails due to insufficient privileges, deploy without backup
   ./deploy.sh --skip-backup

   # Or ask your DBA to grant additional privileges:
   # GRANT SELECT, LOCK TABLES, TRIGGER, CREATE ROUTINE, PROCESS ON database_name.* TO 'username'@'%';
   ```

2. **Permission Errors**:
   ```bash
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 755 storage bootstrap/cache
   ```

3. **Composer Memory Issues**:
   ```bash
   php -d memory_limit=512M /usr/local/bin/composer install --no-dev --optimize-autoloader
   ```

4. **Asset Build Failures**:
   ```bash
   npm cache clean --force
   rm -rf node_modules package-lock.json
   npm install
   npm run build
   ```

5. **Queue Worker Issues**:
   ```bash
   php artisan horizon:terminate
   # Wait a few seconds
   php artisan horizon
   ```

6. **Database Connection Issues**:
   ```bash
   # Test database connection
   php artisan db:show

   # Check database credentials in .env file
   grep -E "DB_HOST|DB_DATABASE|DB_USERNAME" .env
   ```

7. **Network/Proxy Issues**:
   ```bash
   # Test proxy connectivity
   curl -I --proxy http://proxy.andrew.cmu.edu:3128 https://github.com

   # Check if proxy variables are set (should be automatic in scripts)
   echo $HTTP_PROXY

   # Manually set proxy if needed
   export HTTP_PROXY="http://proxy.andrew.cmu.edu:3128"
   export HTTPS_PROXY="http://proxy.andrew.cmu.edu:3128"
   ```

### Rollback Scenarios

**Code Issues Only**:
```bash
./rollback.sh
```

**Database Migration Issues**:
```bash
./rollback.sh --with-database
```

**Manual Rollback** (if scripts fail):
```bash
# Enable maintenance
php artisan down

# Reset to previous commit
git reset --hard HEAD~1

# Clear caches
php artisan config:clear
php artisan cache:clear

# Restart services
php artisan queue:restart

# Disable maintenance
php artisan up
```

## Monitoring

After deployment, monitor:

- **Application Logs**: `storage/logs/laravel.log`
- **Web Server Logs**: Nginx/Apache error logs
- **Queue Status**: Horizon dashboard
- **Database Performance**: MySQL slow query log
- **Application Performance**: Response times and error rates

## Scheduled Tasks

Ensure these Laravel scheduler tasks are configured in cron:

```bash
# Add to crontab (crontab -e)
* * * * * cd /path/to/application && php artisan schedule:run >> /dev/null 2>&1
```

## Security Considerations

- **Environment Variables**: Never commit `.env` files
- **Database Backups**: Secure backup storage with encryption
- **File Permissions**: Restrict access to sensitive files
- **SSL/TLS**: Always use HTTPS in production
- **Updates**: Keep dependencies updated for security patches

## Contact

For deployment issues or questions, contact the development team.