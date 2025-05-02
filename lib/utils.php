<?php
// Utility functions for the API

/**
 * Send a JSON response with appropriate headers
 * 
 * @param mixed $data The data to send
 * @param int $statusCode HTTP status code
 * @param array $additionalHeaders Additional headers to send
 */
function sendJsonResponse($data, $statusCode = 200, $additionalHeaders = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    // Apply additional headers
    foreach ($additionalHeaders as $name => $value) {
        header("$name: $value");
    }
    
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Generate a cache key from request parameters
 * 
 * @return string The cache key
 */
function generateCacheKey() {
    $url = $_SERVER['REQUEST_URI'];
    $params = $_GET;
    
    // Sort parameters for consistent cache keys
    ksort($params);
    $sortedParams = http_build_query($params);
    
    return md5($url . '?' . $sortedParams);
}

/**
 * Custom error handler
 * 
 * @param int $errno Error number
 * @param string $errstr Error message
 * @param string $errfile File where error occurred
 * @param int $errline Line number where error occurred
 */
function handleError($errno, $errstr, $errfile, $errline) {
    $errorType = 'Error';
    
    switch ($errno) {
        case E_USER_ERROR:
            $errorType = 'Fatal Error';
            break;
        case E_USER_WARNING:
        case E_WARNING:
            $errorType = 'Warning';
            break;
        case E_USER_NOTICE:
        case E_NOTICE:
            $errorType = 'Notice';
            break;
    }
    
    error_log("$errorType: $errstr in $errfile on line $errline");
    
    if ($errno == E_USER_ERROR) {
        sendJsonResponse([
            'error' => 'Server error',
            'message' => 'An unexpected error occurred'
        ], 500);
        exit;
    }
    
    return true;
}

/**
 * Custom exception handler
 * 
 * @param Exception $exception The exception
 */
function handleException($exception) {
    error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    // Check if it's a "not found" exception
    if (strpos($exception->getMessage(), 'No file found') !== false) {
        sendJsonResponse([
            'error' => 'File not found',
            'message' => $exception->getMessage()
        ], 404);
    } else {
        sendJsonResponse([
            'error' => 'Server error',
            'message' => 'An unexpected error occurred'
        ], 500);
    }
    
    exit;
}

/**
 * Get current server time in ISO format
 * 
 * @return string Formatted server time
 */
function getCurrentTime() {
    return date('Y-m-d H:i:s');
}
