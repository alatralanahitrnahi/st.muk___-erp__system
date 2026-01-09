#!/bin/bash

echo "🚀 Setting up PVGS ERP Development Environment..."

# Update packages
sudo apt-get update -y

# Install required packages
sudo apt-get install -y git curl zip unzip

# Install Composer
if ! command -v composer &> /dev/null; then
    echo "📥 Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# Configure Composer
composer config --global process-timeout 2000
composer global require laravel/installer

# Add to PATH
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.bashrc

# Install MCP packages
echo "📦 Installing MCP packages..."
npm install -g @modelcontextprotocol/server-filesystem
npm install -g @modelcontextprotocol/server-sequential-thinking
npm install -g @modelcontextprotocol/server-memory
npm install -g @modelcontextprotocol/server-git
npm install -g @benborla29/mcp-server-mysql

# Setup MCP config
mkdir -p ~/.config/Code/User/globalStorage/github.copilot-chat
if [ -f ".devcontainer/mcp.json" ]; then
    cp .devcontainer/mcp.json ~/.config/Code/User/globalStorage/github.copilot-chat/mcp.json
    chmod 600 ~/.config/Code/User/globalStorage/github.copilot-chat/mcp.json
fi

# Git config
git config --global core.autocrlf input
git config --global init.defaultBranch main

# Create project structure
mkdir -p app/Models/{Academic,Financial,User,Attendance,Examination}
mkdir -p app/Http/Controllers/Api/{Academic,Financial,Attendance,Examination}
mkdir -p app/Services
mkdir -p app/Repositories
mkdir -p app/Http/Requests
mkdir -p database/migrations/{2024_01_foundation,2024_02_user_management,2024_03_academic,2024_04_attendance,2024_05_financial,2024_06_examination}

# Pull GitHub MCP Docker image
docker pull ghcr.io/github/github-mcp-server

echo "✅ Setup complete!"