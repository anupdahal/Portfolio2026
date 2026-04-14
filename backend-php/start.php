<?php
/**
 * Development Server Startup Script
 *
 * Usage: php start.php
 *
 * This starts the PHP built-in development server on port 8000.
 * All requests are routed through api/index.php.
 *
 * Prerequisites:
 *   1. MySQL running with portfolio_db database
 *   2. Run: mysql -u root -p < database/schema.sql
 *   3. Copy .env.example to .env and configure database credentials
 */

$host = '0.0.0.0';
$port = getenv('PORT') ?: 8000;
$docroot = __DIR__;
$router = __DIR__ . '/api/index.php';

echo "╔══════════════════════════════════════════════╗\n";
echo "║     Portfolio PHP API - Development Server   ║\n";
echo "╠══════════════════════════════════════════════╣\n";
echo "║  Server:  http://localhost:$port              ║\n";
echo "║  API:     http://localhost:$port/api           ║\n";
echo "║  Health:  http://localhost:$port/api/health    ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

// Check for .env file
if (!file_exists(__DIR__ . '/.env')) {
    echo "⚠  No .env file found. Copy .env.example to .env and configure it.\n";
    echo "   cp .env.example .env\n\n";
}

echo "Starting server...\n\n";

// Start PHP built-in server
$command = sprintf(
    'php -S %s:%d -t %s %s',
    $host,
    $port,
    escapeshellarg($docroot),
    escapeshellarg($router)
);

passthru($command);
