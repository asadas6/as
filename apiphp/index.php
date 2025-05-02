<?php
/**
 * Main entry point for the application
 */

// Error reporting for development (comment out in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define base URL - works in any environment
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = $scriptName !== '/' ? rtrim($scriptName, '/') : '';
define('BASE_URL', $baseUrl);

// Include required files
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/api.php';
require_once __DIR__ . '/includes/cache-manager.php';

// Create cache directory if it doesn't exist
$cacheDir = __DIR__ . '/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

// Get the route from the URL
$route = isset($_GET['route']) ? trim($_GET['route'], '/') : 'home';

// Route handling
switch ($route) {
    case 'home':
    case '':
        require_once __DIR__ . '/pages/home.php';
        break;
        
    case 'dashboard':
        require_once __DIR__ . '/pages/dashboard.php';
        break;
        
    case 'search':
        require_once __DIR__ . '/pages/search.php';
        break;
        
    case (preg_match('/^e\/([a-zA-Z0-9]+)$/', $route, $matches) ? true : false):
        // Video detail page
        $_GET['file_code'] = $matches[1];
        require_once __DIR__ . '/pages/video.php';
        break;
        
    case (preg_match('/^f\/(.+)$/', $route, $matches) ? true : false):
        // Search results page
        $_GET['query'] = urldecode($matches[1]);
        require_once __DIR__ . '/pages/search.php';
        break;
        
    case 'clear-cache':
        // Clear all cache (admin function)
        if (isset($_GET['key']) && $_GET['key'] === 'admin123') {
            clearAllCache();
            echo "Cache cleared successfully!";
        } else {
            http_response_code(403);
            echo "Unauthorized";
        }
        break;
        
    default:
        http_response_code(404);
        require_once __DIR__ . '/pages/404.php';
        break;
}
