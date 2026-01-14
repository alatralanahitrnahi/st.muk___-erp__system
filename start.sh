#!/bin/bash

# Quick Start - PVGS ERP Local Environment
# For existing Laravel installation

echo "🚀 Starting PVGS ERP System..."
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ .env file not found!"
    echo "Run: cp .env.example .env (or create .env manually)"
    exit 1
fi

# Check database connection
echo "📊 Checking database connection..."
php artisan db:show 2>/dev/null || {
    echo "❌ Database connection failed!"
    echo "Check your .env database settings"
    exit 1
}
echo "✅ Database connected"
echo ""

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force
echo "✅ Migrations complete"
echo ""

# Clear caches
echo "🧹 Clearing caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
echo "✅ Caches cleared"
echo ""

# Start server
echo "🌐 Starting development server..."
echo ""
echo "Access at: http://localhost:8000"
echo "Press Ctrl+C to stop"
echo ""

php artisan serve
