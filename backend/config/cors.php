<?php

/**
 * CONFIGURACIÓN CORS PARA RAILWAY
 * 
 * Archivo: backend/config/cors.php
 * Reemplaza el contenido actual con esto
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración optimizada para Railway deployment
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'auth/*',
        'public/*'
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        // URLs locales para desarrollo
        'http://localhost:5173',
        'http://localhost:5174',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174',
        'https://torneo-deportivo.up.railway.app/',
        
        // Variables de entorno para producción
        env('FRONTEND_URL'),
        env('CORS_ALLOWED_ORIGINS'),
    ],

    'allowed_origins_patterns' => [
        // Permitir todos los subdominios de Railway
        'https://*.railway.app',
        'https://*.up.railway.app',
        
        // Patrones específicos para Railway
        '/^https:\/\/.*\.up\.railway\.app$/',
        '/^https:\/\/.*-production-.*\.up\.railway\.app$/',
    ],

    'allowed_headers' => [
        '*',
        'Accept',
        'Accept-Language',
        'Authorization',
        'Content-Type',
        'Content-Language',
        'X-Requested-With',
        'X-CSRF-Token',
        'X-XSRF-Token',
    ],

    'exposed_headers' => [
        'Cache-Control',
        'Content-Language',
        'Content-Type',
        'Expires',
        'Last-Modified',
        'Pragma',
    ],

    'max_age' => 0,

    // Importante: false para Railway
    'supports_credentials' => false,
];