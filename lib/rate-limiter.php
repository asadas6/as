<?php
// Rate limiting implementation

// Simple in-memory rate limiting
// Note: In a production environment, you would use Redis or a similar solution
$ipRequestCounts = [];

/**
 * Apply rate limiting based on IP address
 * 
 * @return bool True if request is allowed, false if rate limit exceeded
 */
function applyRateLimit() {
    global $ipRequestCounts;
    
    // Get client IP
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $now = time() * 1000; // Convert to milliseconds
    
    // Initialize or reset counter if window has passed
    if (!isset($ipRequestCounts[$ip]) || $now > ($ipRequestCounts[$ip]['resetTime'] ?? 0)) {
        $ipRequestCounts[$ip] = [
            'count' => 1,
            'resetTime' => $now + RATE_LIMIT_WINDOW_MS
        ];
        return true;
    }
    
    // Increment counter
    $ipRequestCounts[$ip]['count']++;
    
    // Check if rate limit exceeded
    if ($ipRequestCounts[$ip]['count'] > RATE_LIMIT_MAX_REQUESTS) {
        return false;
    }
    
    return true;
}
