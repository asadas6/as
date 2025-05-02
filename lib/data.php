<?php
// Data fetching and management functions

// Global variable to store the cached data
$cachedData = null;
$isDataFetching = false;

/**
 * Fetch data from external sources with fallbacks
 * 
 * @return array The fetched data
 */
function fetchDataWithCache() {
    global $cachedData, $isDataFetching;
    
    // If we already have cached data, return it immediately
    if ($cachedData !== null) {
        return $cachedData;
    }
    
    // If a fetch is already in progress, wait for it to complete
    if ($isDataFetching) {
        // In a real implementation, we might use a semaphore or lock file
        // For simplicity, we'll just wait a bit and check again
        sleep(1);
        return fetchDataWithCache();
    }
    
    // Check if we have data cached on disk
    if (file_exists(DATA_CACHE_FILE)) {
        $cachedData = json_decode(file_get_contents(DATA_CACHE_FILE), true);
        
        if (is_array($cachedData) && !empty($cachedData)) {
            return $cachedData;
        }
    }
    
    // First time fetch - get the data and store it permanently
    $isDataFetching = true;
    
    try {
        error_log("Fetching data from external source (one-time only)");
        
        // Try multiple data sources in case one fails
        $sources = json_decode(DATA_SOURCE_URLS, true);
        $response = null;
        $error = null;
        
        // Try each source until one succeeds
        foreach ($sources as $source) {
            try {
                $context = stream_context_create([
                    'http' => [
                        'header' => 'Accept-Encoding: gzip, deflate, br',
                        'timeout' => 30
                    ]
                ]);
                
                $data = file_get_contents($source, false, $context);
                
                if ($data !== false) {
                    $response = $data;
                    break;
                }
            } catch (Exception $e) {
                $error = $e;
                error_log("Failed to fetch from $source: " . $e->getMessage());
            }
        }
        
        if ($response === null) {
            throw $error ?: new Exception("All data sources failed");
        }
        
        $data = json_decode($response, true);
        
        // Validate that the data is an array
        if (!is_array($data)) {
            throw new Exception("Invalid data format: expected an array");
        }
        
        // Filter out any invalid entries
        $validData = array_filter($data, function($item) {
            return is_array($item) && isset($item['file_code']);
        });
        
        $cachedData = array_values($validData); // Reset array keys
        
        // Save to disk cache
        file_put_contents(DATA_CACHE_FILE, json_encode($cachedData));
        
        return $cachedData;
    } catch (Exception $e) {
        error_log("Failed to fetch data: " . $e->getMessage());
        // Return an empty array instead of throwing to prevent cascading failures
        $cachedData = [];
        return $cachedData;
    } finally {
        $isDataFetching = false;
    }
}

/**
 * Get the cached data
 * 
 * @return array The cached data or empty array if not yet fetched
 */
function getCachedData() {
    global $cachedData;
    
    if ($cachedData === null) {
        // Try to load from disk cache first
        if (file_exists(DATA_CACHE_FILE)) {
            $cachedData = json_decode(file_get_contents(DATA_CACHE_FILE), true);
        } else {
            $cachedData = [];
        }
    }
    
    return $cachedData ?: [];
}
