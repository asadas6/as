<?php
/**
 * API functions for interacting with the backend
 */

// Include cache manager if not already included
require_once __DIR__ . '/cache-manager.php';

// API base URL
define('API_BASE_URL', 'https://v0-webapi7-ky1jd5.vercel.app/api');

/**
 * Make an API request
 * 
 * @param string $endpoint API endpoint
 * @param array $params Query parameters
 * @return array|null Response data or null on error
 */
function apiRequest($endpoint, $params = []) {
    $url = API_BASE_URL . '/' . $endpoint;
    
    // Add query parameters
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    // Make the request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check for errors
    if ($httpCode !== 200 || $response === false) {
        return null;
    }
    
    // Parse JSON response
    $data = json_decode($response, true);
    
    return $data;
}

/**
 * Get file information by file code
 * 
 * @param string $fileCode File code
 * @return array|null File data or null on error
 */
function getFileInfo($fileCode) {
    // Generate a unique cache key
    $cacheKey = generateCacheKey('file_info', ['file_code' => $fileCode]);
    
    // Use the cache system with a TTL of 1 hour (3600 seconds)
    $data = getFromCache($cacheKey, function() use ($fileCode) {
        $apiData = apiRequest('info', ['file_code' => $fileCode]);
        
        if ($apiData && isset($apiData['result']) && !empty($apiData['result'])) {
            return $apiData['result'][0];
        }
        
        return null;
    }, 3600);
    
    return $data;
}

/**
 * Get a list of files
 * 
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array List data
 */
function getFileList($page = 1, $perPage = 12) {
    // Generate a unique cache key
    $cacheKey = generateCacheKey('file_list', ['page' => $page, 'per_page' => $perPage]);
    
    // Use the cache system with a TTL of 30 minutes (1800 seconds)
    $data = getFromCache($cacheKey, function() use ($page, $perPage) {
        $apiData = apiRequest('list', [
            'page' => $page,
            'per_page' => $perPage
        ]);
        
        if ($apiData && isset($apiData['result'])) {
            return [
                'files' => $apiData['result']['files'] ?? [],
                'totalPages' => $apiData['result']['total_pages'] ?? 1,
                'totalResults' => $apiData['result']['results_total'] ?? 0
            ];
        }
        
        return [
            'files' => [],
            'totalPages' => 1,
            'totalResults' => 0
        ];
    }, 1800);
    
    return $data;
}

/**
 * Get random files
 * 
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array Random files data
 */
function getRandomFiles($page = 1, $perPage = 12) {
    // Generate a unique cache key
    $cacheKey = generateCacheKey('random_files', ['page' => $page, 'per_page' => $perPage]);
    
    // Use the cache system with a shorter TTL of 15 minutes (900 seconds)
    // Random content should refresh more often
    $data = getFromCache($cacheKey, function() use ($page, $perPage) {
        $apiData = apiRequest('rand', [
            'page' => $page,
            'per_page' => $perPage
        ]);
        
        if ($apiData && isset($apiData['result'])) {
            return [
                'files' => $apiData['result']['files'] ?? [],
                'totalPages' => $apiData['result']['total_pages'] ?? 1,
                'totalResults' => $apiData['result']['results_total'] ?? 0
            ];
        }
        
        return [
            'files' => [],
            'totalPages' => 1,
            'totalResults' => 0
        ];
    }, 900);
    
    return $data;
}

/**
 * Search for files
 * 
 * @param string $query Search query
 * @param int $page Page number
 * @param int $perPage Items per page
 * @return array Search results
 */
function searchFiles($query, $page = 1, $perPage = 12) {
    // Generate a unique cache key
    $cacheKey = generateCacheKey('search_files', ['q' => $query, 'page' => $page, 'per_page' => $perPage]);
    
    // Use the cache system with a TTL of 1 hour (3600 seconds)
    $data = getFromCache($cacheKey, function() use ($query, $page, $perPage) {
        $apiData = apiRequest('search', [
            'q' => $query,
            'page' => $page,
            'per_page' => $perPage
        ]);
        
        if ($apiData && isset($apiData['result'])) {
            return [
                'files' => $apiData['result'],
                'totalResults' => count($apiData['result'])
            ];
        }
        
        return [
            'files' => [],
            'totalResults' => 0
        ];
    }, 3600);
    
    return $data;
}
