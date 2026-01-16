#!/bin/bash
echo "🔒 PVGS ERP - Production Hardening Setup"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 1. Create required directories
echo "📁 Creating directories..."
mkdir -p storage/logs
mkdir -p backups
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
chmod -R 775 storage backups

# 2. Setup log rotation
echo "📝 Configuring log rotation..."
cat > /etc/logrotate.d/pvgs-erp << 'EOFLOG'
/var/www/pvgs-erp/storage/logs/*.log {
    daily
    rotate 7
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
EOFLOG

# 3. Setup daily backup cron
echo "⏰ Setting up automated backups..."
(crontab -l 2>/dev/null; echo "0 2 * * * cd /var/www/pvgs-erp && bash scripts/backup-database.sh >> storage/logs/backup.log 2>&1") | crontab -

# 4. Setup monitoring cron
echo "📊 Setting up monitoring..."
(crontab -l 2>/dev/null; echo "*/5 * * * * curl -s http://localhost:8000/health-check.php >> storage/logs/health.log 2>&1") | crontab -

# 5. Configure firewall
echo "🔥 Configuring firewall..."
ufw allow 8000/tcp 2>/dev/null || echo "UFW not available, skipping"

# 6. Set secure permissions
echo "🔐 Setting secure permissions..."
chmod 600 database/database.sqlite
chmod 755 public/*.php
chmod 755 scripts/*.sh

# 7. Generate secure JWT secret
echo "🔑 Generating JWT secret..."
JWT_SECRET=$(openssl rand -base64 64 | tr -d '\n')
echo "JWT_SECRET=$JWT_SECRET" >> .env

# 8. Setup systemd service (optional)
echo "⚙️  Creating systemd service..."
cat > /etc/systemd/system/pvgs-erp.service << 'EOFSVC'
[Unit]
Description=PVGS ERP API Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/pvgs-erp/public
ExecStart=/usr/bin/php -S 0.0.0.0:8000
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
EOFSVC

echo ""
echo "✅ Production hardening complete!"
echo ""
echo "Next steps:"
echo "  1. Review .env file for JWT_SECRET"
echo "  2. Enable systemd service: systemctl enable pvgs-erp"
echo "  3. Start service: systemctl start pvgs-erp"
echo "  4. Check status: systemctl status pvgs-erp"
