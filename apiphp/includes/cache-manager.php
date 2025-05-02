<?php
/**
 * Multi-layer cache management system
 * 
 * Implements three layers of caching:
 * 1. Memory cache (fastest, lasts only for current request)
 * 2. Local cache (file-based, persists between requests)
 * 3. API (fallback when cache misses)
 */

// Memory cache storage (in-memory array)
$GLOBALS['memory_cache'] = [];

/**
 * Get data from multi-layer cache or API
 * 
 * @param string $cache_key Unique cache key
 * @param callable $api_callback Function to call if cache misses
 * @param int $cache_ttl Cache time-to-live in seconds (default: 3600 = 1 hour)
 * @return mixed Cached or fresh data
 */
function getFromCache($cache_key, $api_callback, $cache_ttl = 3600) {
    // 1. Check memory cache first (fastest)
    if (isset($GLOBALS['memory_cache'][$cache_key])) {
        return $GLOBALS['memory_cache'][$cache_key];
    }
    
    // 2. Check local file cache
    $cache_data = getFromLocalCache($cache_key, $cache_ttl);
    if ($cache_data !== null) {
        // Store in memory cache for future use in this request
        $GLOBALS['memory_cache'][$cache_key] = $cache_data;
        return $cache_data;
    }
    
    // 3. Cache miss - call API
    $fresh_data = $api_callback();
    
    // Store in both caches
    if ($fresh_data !== null) {
        $GLOBALS['memory_cache'][$cache_key] = $fresh_data;
        saveToLocalCache($cache_key, $fresh_data);
    }
    
    return $fresh_data;
}

/**
 * Get data from local file cache
 * 
 * @param string $cache_key Unique cache key
 * @param int $cache_ttl Cache time-to-live in seconds
 * @return mixed|null Cached data or null if cache miss/expired
 */
function getFromLocalCache($cache_key, $cache_ttl) {
    $cache_dir = __DIR__ . '/../cache';
    $cache_file = $cache_dir . '/' . md5($cache_key) . '.cache';
    
    // Check if cache directory exists, create if not
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    // Check if cache file exists and is not expired
    if (file_exists($cache_file)) {
        $file_time = filemtime($cache_file);
        
        // Check if cache is still valid
        if (time() - $file_time < $cache_ttl) {
            $data = file_get_contents($cache_file);
            if ($data !== false) {
                return unserialize($data);
            }
        }
    }
    
    return null;
}

/**
 * Save data to local file cache
 * 
 * @param string $cache_key Unique cache key
 * @param mixed $data Data to cache
 * @return bool Success or failure
 */
function saveToLocalCache($cache_key, $data) {
    $cache_dir = __DIR__ . '/../cache';
    $cache_file = $cache_dir . '/' . md5($cache_key) . '.cache';
    
    // Check if cache directory exists, create if not
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }
    
    // Serialize and save data
    $serialized_data = serialize($data);
    return file_put_contents($cache_file, $serialized_data) !== false;
}

/**
 * Clear a specific cache entry
 * 
 * @param string $cache_key Unique cache key
 * @return bool Success or failure
 */
function clearCache($cache_key) {
    // Clear memory cache
    if (isset($GLOBALS['memory_cache'][$cache_key])) {
        unset($GLOBALS['memory_cache'][$cache_key]);
    }
    
    // Clear file cache
    $cache_dir = __DIR__ . '/../cache';
    $cache_file = $cache_dir . '/' . md5($cache_key) . '.cache';
    
    if (file_exists($cache_file)) {
        return unlink($cache_file);
    }
    
    return true;
}

/**
 * Clear all cache entries
 * 
 * @return bool Success or failure
 */
function clearAllCache() {
    // Clear memory cache
    $GLOBALS['memory_cache'] = [];
    
    // Clear file cache
    $cache_dir = __DIR__ . '/../cache';
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    return true;
}

/**
 * Generate a cache key based on function name and parameters
 * 
 * @param string $prefix Cache key prefix
 * @param array $params Parameters that affect the result
 * @return string Unique cache key
 */
function generateCacheKey($prefix, $params) {
    return $prefix . ':' . md5(serialize($params));
}
