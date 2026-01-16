#!/bin/bash
set -e

echo "🚀 Setting up PVGS ERP Development Environment..."

# Update packages
apt-get update && apt-get install -y \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    libzip-dev unzip git curl zip \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
docker-php-ext-configure gd --with-freetype --with-jpeg
docker-php-ext-install gd mbstring xml bcmath pdo_mysql zip

# Install Composer if not present
if ! command -v composer >/dev/null 2>&1; then
    echo "📥 Installing Composer..."
    EXPECTED_SIGNATURE="$(curl -s https://composer.github.io/installer.sig)"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]; then
        >&2 echo 'ERROR: Invalid Composer installer signature'
        exit 1
    fi
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    php -r "unlink('composer-setup.php');"
fi

# Ensure project dependencies
if [ -f "composer.json" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Setup .env and generate app key
if [ ! -f ".env" ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Optional: MCP packages
echo "📦 Installing MCP packages..."
npm install -g @modelcontextprotocol/server-filesystem \
    @modelcontextprotocol/server-sequential-thinking \
    @modelcontextprotocol/server-memory \
    @modelcontextprotocol/server-git \
    @benborla29/mcp-server-mysql

# Setup MCP config if exists
if [ -f ".devcontainer/mcp.json" ]; then
    mkdir -p ~/.config/Code/User/globalStorage/github.copilot-chat
    cp .devcontainer/mcp.json ~/.config/Code/User/globalStorage/github.copilot-chat/mcp.json
    chmod 600 ~/.config/Code/User/globalStorage/github.copilot-chat/mcp.json
fi

echo "✅ Setup complete!"
