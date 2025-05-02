<?php
// CORS handling functions

/**
 * Set CORS headers for cross-origin requests
 */
function handleCors() {
    // Set CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Set cache headers
    setCacheHeaders();
}
