#!/bin/bash

# PVGS ERP - Local Setup Script
# Automates the complete local environment setup

set -e  # Exit on error

echo "🚀 PVGS ERP System - Local Setup"
echo "=================================="
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Step 1: Check prerequisites
echo "📋 Step 1: Checking prerequisites..."
command -v php >/dev/null 2>&1 || { echo -e "${RED}❌ PHP is required but not installed.${NC}" >&2; exit 1; }
command -v composer >/dev/null 2>&1 || { echo -e "${RED}❌ Composer is required but not installed.${NC}" >&2; exit 1; }
command -v mysql >/dev/null 2>&1 || { echo -e "${RED}❌ MySQL is required but not installed.${NC}" >&2; exit 1; }

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${GREEN}✅ PHP $PHP_VERSION${NC}"
echo -e "${GREEN}✅ Composer installed${NC}"
echo -e "${GREEN}✅ MySQL installed${NC}"
echo ""

# Step 2: Environment setup
echo "⚙️  Step 2: Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo -e "${GREEN}✅ .env file created${NC}"
else
    echo -e "${YELLOW}⚠️  .env file already exists${NC}"
fi

php artisan key:generate --force
echo -e "${GREEN}✅ Application key generated${NC}"
echo ""

# Step 3: Database setup
echo "🗄️  Step 3: Database configuration..."
echo -e "${YELLOW}Please enter your MySQL credentials:${NC}"
read -p "Database name [pvgs_erp]: " DB_NAME
DB_NAME=${DB_NAME:-pvgs_erp}

read -p "MySQL username [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "MySQL password: " DB_PASS
echo ""

# Update .env file
sed -i.bak "s/DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
sed -i.bak "s/DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
sed -i.bak "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
sed -i.bak "s/SESSION_DRIVER=.*/SESSION_DRIVER=database/" .env
sed -i.bak "s/CACHE_DRIVER=.*/CACHE_DRIVER=database/" .env

echo -e "${GREEN}✅ Database configuration updated${NC}"

# Create database
echo "Creating database..."
mysql -u"$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo -e "${RED}❌ Failed to create database. Please create it manually.${NC}"
    exit 1
}
echo -e "${GREEN}✅ Database '$DB_NAME' created${NC}"
echo ""

# Step 4: Install dependencies
echo "📦 Step 4: Installing dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader
echo -e "${GREEN}✅ Dependencies installed${NC}"
echo ""

# Step 5: Run migrations
echo "🔄 Step 5: Running migrations..."
php artisan migrate --force
echo -e "${GREEN}✅ Migrations completed${NC}"
echo ""

# Step 6: Seed data
echo "🌱 Step 6: Seeding initial data..."
php artisan db:seed --class=DepartmentPermissionsSeeder --force
echo -e "${GREEN}✅ Permissions seeded${NC}"
echo ""

# Step 7: Backfill department data
echo "📊 Step 7: Backfilling department data..."
if php artisan list | grep -q "department:backfill"; then
    php artisan department:backfill
    php artisan department:assign-users
    echo -e "${GREEN}✅ Department data backfilled${NC}"
else
    echo -e "${YELLOW}⚠️  Department commands not found, skipping...${NC}"
fi
echo ""

# Step 8: Optimize
echo "⚡ Step 8: Optimizing application..."
php artisan config:cache
php artisan route:cache
echo -e "${GREEN}✅ Application optimized${NC}"
echo ""

# Step 9: Validation
echo "✅ Step 9: Validating setup..."
if php artisan list | grep -q "department:validate"; then
    php artisan department:validate
    echo -e "${GREEN}✅ Validation completed${NC}"
else
    echo -e "${YELLOW}⚠️  Validation command not found, skipping...${NC}"
fi
echo ""

# Summary
echo "=================================="
echo -e "${GREEN}🎉 Setup Complete!${NC}"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Start server:    php artisan serve"
echo "2. Access at:       http://localhost:8000"
echo "3. Check logs:      tail -f storage/logs/laravel.log"
echo "4. Run tests:       php artisan test"
echo ""
echo "Default credentials (if seeded):"
echo "  Email:    admin@pvgs.edu"
echo "  Password: password"
echo ""
echo "Documentation:"
echo "  Setup Guide:  LOCAL_SETUP_GUIDE.md"
echo "  API Docs:     development/api_contracts.md"
echo ""
echo -e "${YELLOW}⚠️  Remember to change default passwords in production!${NC}"
echo ""
