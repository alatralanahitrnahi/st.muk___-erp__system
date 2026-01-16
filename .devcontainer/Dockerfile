# PHP 8.2 Bullseye Devcontainer
FROM mcr.microsoft.com/devcontainers/php:1-8.2-bullseye

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql mbstring xml bcmath zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*
