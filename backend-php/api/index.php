<?php
/**
 * Portfolio API Router
 * Main entry point - routes all API requests to appropriate handlers
 *
 * Usage with PHP built-in server:
 *   php -S localhost:5000 -t backend-php backend-php/api/index.php
 *
 * Or deploy to Apache/Nginx with the included .htaccess
 */

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');

// CORS handling
require_once __DIR__ . '/../config/database.php';
$frontendUrl = getenv('FRONTEND_URL') ?: '*';

header("Access-Control-Allow-Origin: $frontendUrl");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Parse the request URI
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/api';

// Strip query string for routing
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove trailing slash
$path = rtrim($path, '/');

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Include route handlers
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/projects.php';
require_once __DIR__ . '/memories.php';
require_once __DIR__ . '/blogs.php';

// ─── Route matching ───

// Health check: GET /api/health
if ($path === "$basePath/health" && $method === 'GET') {
    echo json_encode([
        'status' => 'ok',
        'timestamp' => date('c'),
    ]);
    exit;
}

// Auth: POST /api/login
if ($path === "$basePath/login" && $method === 'POST') {
    handleLogin();
    exit;
}

// Projects routes
if (preg_match("#^$basePath/projects(?:/(\d+))?$#", $path, $matches)) {
    $id = $matches[1] ?? null;

    switch ($method) {
        case 'GET':
            handleGetProjects();
            break;
        case 'POST':
            handleCreateProject();
            break;
        case 'PUT':
            if ($id) handleUpdateProject($id);
            else { http_response_code(400); echo json_encode(['error' => 'Project ID required.']); }
            break;
        case 'DELETE':
            if ($id) handleDeleteProject($id);
            else { http_response_code(400); echo json_encode(['error' => 'Project ID required.']); }
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed.']);
    }
    exit;
}

// Memories routes
if (preg_match("#^$basePath/memories(?:/(\d+))?$#", $path, $matches)) {
    $id = $matches[1] ?? null;

    switch ($method) {
        case 'GET':
            handleGetMemories();
            break;
        case 'POST':
            handleCreateMemory();
            break;
        case 'DELETE':
            if ($id) handleDeleteMemory($id);
            else { http_response_code(400); echo json_encode(['error' => 'Memory ID required.']); }
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed.']);
    }
    exit;
}

// Blogs routes
if (preg_match("#^$basePath/blogs(?:/(\d+))?$#", $path, $matches)) {
    $id = $matches[1] ?? null;

    switch ($method) {
        case 'GET':
            if ($id) handleGetBlog($id);
            else handleGetBlogs();
            break;
        case 'POST':
            handleCreateBlog();
            break;
        case 'PUT':
            if ($id) handleUpdateBlog($id);
            else { http_response_code(400); echo json_encode(['error' => 'Blog ID required.']); }
            break;
        case 'DELETE':
            if ($id) handleDeleteBlog($id);
            else { http_response_code(400); echo json_encode(['error' => 'Blog ID required.']); }
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed.']);
    }
    exit;
}

// 404 - No matching route
http_response_code(404);
echo json_encode(['error' => 'Route not found.']);
