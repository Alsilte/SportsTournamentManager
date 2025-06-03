#!/bin/bash

echo "🚀 Iniciando build de Laravel API..."

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Generar key si no existe
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Optimizaciones de Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear enlace de storage
php artisan storage:link

echo "✅ Build completado"