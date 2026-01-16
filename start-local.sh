#!/bin/bash

echo "🚀 Starting PVGS ERP System..."
echo ""

# Check if database exists
if [ ! -f "database/database.sqlite" ]; then
    echo "❌ Database not found!"
    echo "Please run migrations first"
    exit 1
fi

# Kill any existing PHP server
pkill -f "php -S localhost:8000" 2>/dev/null

# Start PHP server
echo "Starting PHP server on http://localhost:8000"
php -S localhost:8000 -t public > /tmp/pvgs-server.log 2>&1 &
SERVER_PID=$!

# Wait for server to start
sleep 2

# Test if server is running
if curl -s http://localhost:8000/health.php > /dev/null; then
    echo "✅ Server started successfully!"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  PVGS ERP - Ready for Testing"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "📱 React App:      http://localhost:8000/app"
    echo "🔌 API Endpoint:   http://localhost:8000/api/*"
    echo "⚙️  Workflow API:   http://localhost:8000/workflow-api.php/*"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "  Test Accounts"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Super Admin:"
    echo "  Email:    admin@pvgs.edu"
    echo "  Password: password123"
    echo ""
    echo "Principal:"
    echo "  Email:    principal@pvgs.edu"
    echo "  Password: password123"
    echo ""
    echo "Faculty:"
    echo "  Email:    faculty1@pvgs.edu"
    echo "  Password: password123"
    echo ""
    echo "Student:"
    echo "  Email:    student1@pvgs.edu"
    echo "  Password: password123"
    echo ""
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Server PID: $SERVER_PID"
    echo "Logs: /tmp/pvgs-server.log"
    echo ""
    echo "To stop: kill $SERVER_PID"
    echo "Or run: pkill -f 'php -S localhost:8000'"
    echo ""
else
    echo "❌ Server failed to start"
    echo "Check logs: /tmp/pvgs-server.log"
    exit 1
fi
