#!/bin/bash
# Day 1: Backend Validation Setup
# Execution Time: 30 minutes

set -e

echo "=========================================="
echo "PVGS ERP - Day 1 Backend Setup"
echo "=========================================="

# Step 1: Verify Laravel Installation
echo "[1/8] Verifying Laravel installation..."
cd /workspaces/st.muk___-erp__system

if [ ! -f "composer.json" ]; then
    echo "❌ ERROR: composer.json not found"
    exit 1
fi

# Step 2: Install Dependencies
echo "[2/8] Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1 | tee logs/composer-install.log

# Step 3: Environment Configuration
echo "[3/8] Configuring environment..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ Created .env file"
fi

# Step 4: Generate Application Key
echo "[4/8] Generating application key..."
php artisan key:generate --force

# Step 5: Configure SQLite Database
echo "[5/8] Configuring SQLite database..."
cat > .env << EOF
APP_NAME="PVGS ERP System"
APP_ENV=local
APP_KEY=$(grep APP_KEY .env | cut -d '=' -f2)
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite
DB_DATABASE=/workspaces/st.muk___-erp__system/database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DRIVER=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SANCTUM_STATEFUL_DOMAINS=localhost:8000
EOF

# Step 6: Create SQLite Database
echo "[6/8] Creating SQLite database..."
touch database/database.sqlite
chmod 664 database/database.sqlite

# Step 7: Verify Setup
echo "[7/8] Verifying setup..."
php artisan --version
php artisan config:clear
php artisan cache:clear

# Step 8: Create Logs Directory
echo "[8/8] Creating logs directory..."
mkdir -p logs
mkdir -p tests/results

echo ""
echo "=========================================="
echo "✅ Setup Complete!"
echo "=========================================="
echo "Next: Run 'bash scripts/day1-migrate.sh'"
