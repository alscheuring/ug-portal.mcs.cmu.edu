# Deployment Quick Reference

## 🚀 Quick Deployment Commands

**Note**: All scripts automatically configure CMU's proxy server (`http://proxy.andrew.cmu.edu:3128`) for network access.

### Standard Deployment
```bash
./deploy.sh
```

### Check Database Privileges First
```bash
./check-db-privileges.sh
```

### Deploy Without Database Backup
```bash
./deploy.sh --skip-backup
```

### Rollback if Issues Occur
```bash
# Code rollback only
./rollback.sh

# Code + database rollback
./rollback.sh --with-database
```

### Check Production Environment
```bash
./check-production.sh
```

### Verify Deployment Success
```bash
./check-deployment-status.sh
```

### Test Network Connectivity
```bash
./test-network.sh
```

## 🔍 Common Database Issues

### Error: "Access denied; you need PROCESS privilege(s)"
**Solution:** Use the improved deployment script
```bash
./deploy.sh
```
The script now automatically handles limited database privileges.

### Error: "Access denied when using LOCK TABLES"
**Solution:** The script will automatically use `--single-transaction` instead.

### Error: "Got error: 1044: Access denied to database"
**Solution 1:** Check your database privileges:
```bash
./check-db-privileges.sh
```

**Solution 2:** Deploy without backup (not recommended):
```bash
./deploy.sh --skip-backup
```

## 📋 Pre-Deployment Checklist

1. ✅ Test changes locally first
2. ✅ Check production environment: `./check-production.sh`
3. ✅ Check database privileges: `./check-db-privileges.sh`
4. ✅ Schedule during low-traffic period
5. ✅ Have rollback plan ready

## 🛠️ What Each Script Does

### `deploy.sh`
- Creates database backup (with privilege-aware options)
- Enables maintenance mode
- Pulls latest code
- Updates dependencies
- Runs migrations
- Builds assets
- Optimizes application
- Restarts services
- Verifies deployment

### `rollback.sh`
- Reverts to previous Git commit
- Optionally restores database from backup
- Reinstalls dependencies
- Restarts services
- Verifies rollback

### `check-production.sh`
- Verifies Laravel environment settings
- Checks file permissions
- Tests database connectivity
- Validates PHP extensions
- Confirms web server setup

### `check-deployment-status.sh`
- Verifies deployment completed successfully
- Checks application health and responsiveness
- Monitors Horizon and queue worker status
- Validates cache optimization
- Reviews recent error logs
- Provides next steps and troubleshooting guidance

### `check-db-privileges.sh`
- Tests database connection
- Shows current user privileges
- Recommends appropriate backup options
- Provides privilege upgrade suggestions

### `test-network.sh`
- Tests proxy connectivity to external services
- Verifies Git, Composer, and npm network access
- Diagnoses CMU proxy configuration issues
- Provides network troubleshooting information

## 🚨 Emergency Procedures

### If Deployment Fails Mid-Process
```bash
# Check if maintenance mode is still enabled
php artisan up

# If application is broken, rollback immediately
./rollback.sh
```

### If Database Issues Persist
```bash
# Skip backup and deploy
./deploy.sh --skip-backup

# Or manually create backup with limited options
mysqldump -u username -p --single-transaction --no-tablespaces --skip-lock-tables database_name > backup.sql
```

### If Services Don't Restart
```bash
# Manually restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Manually restart queue workers
php artisan queue:restart

# Check Horizon status
php artisan horizon:status
```

### If Network Operations Fail
```bash
# Check if proxy is working
curl -I --proxy http://proxy.andrew.cmu.edu:3128 https://github.com

# Manually set proxy for current session
export HTTP_PROXY="http://proxy.andrew.cmu.edu:3128"
export HTTPS_PROXY="http://proxy.andrew.cmu.edu:3128"

# Test git access through proxy
git ls-remote origin
```

## 📞 Support

- For deployment issues: Check `storage/logs/laravel.log`
- For database issues: Run `./check-db-privileges.sh`
- For permission issues: Check file ownership and permissions
- For service issues: Check system logs with `journalctl`