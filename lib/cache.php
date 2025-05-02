<?php
// Cache management functions

/**
 * LRU Cache implementation for PHP
 */
class LRUCache {
    private $capacity;
    private $cache = [];
    private $timestamps = [];
    
    /**
     * Constructor
     * 
     * @param int $capacity Maximum number of items in cache
     */
    public function __construct($capacity) {
        $this->capacity = $capacity;
        $this->loadFromDisk();
    }
    
    /**
     * Get an item from the cache
     * 
     * @param string $key Cache key
     * @return mixed|null The cached value or null if not found
     */
    public function get($key) {
        if (!isset($this->cache[$key])) {
            return null;
        }
        
        // Update timestamp to mark as recently used
        $this->timestamps[$key] = time();
        $this->saveToDisk();
        
        return $this->cache[$key];
    }
    
    /**
     * Put an item in the cache
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     */
    public function put($key, $value) {
        // If cache is at capacity and this is a new key, remove least recently used
        if (count($this->cache) >= $this->capacity && !isset($this->cache[$key])) {
            $this->removeLRU();
        }
        
        $this->cache[$key] = $value;
        $this->timestamps[$key] = time();
        $this->saveToDisk();
    }
    
    /**
     * Remove an item from the cache
     * 
     * @param string $key Cache key
     */
    public function remove($key) {
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
            unset($this->timestamps[$key]);
            $this->saveToDisk();
        }
    }
    
    /**
     * Clear the entire cache
     */
    public function clear() {
        $this->cache = [];
        $this->timestamps = [];
        $this->saveToDisk();
    }
    
    /**
     * Remove the least recently used item
     */
    private function removeLRU() {
        if (empty($this->timestamps)) {
            return;
        }
        
        // Find key with oldest timestamp
        $oldestKey = array_keys($this->timestamps, min($this->timestamps))[0];
        
        unset($this->cache[$oldestKey]);
        unset($this->timestamps[$oldestKey]);
    }
    
    /**
     * Save cache to disk
     */
    private function saveToDisk() {
        $data = [
            'cache' => $this->cache,
            'timestamps' => $this->timestamps
        ];
        
        file_put_contents(LRU_CACHE_FILE, json_encode($data));
    }
    
    /**
     * Load cache from disk
     */
    private function loadFromDisk() {
        if (file_exists(LRU_CACHE_FILE)) {
            $data = json_decode(file_get_contents(LRU_CACHE_FILE), true);
            
            if (isset($data['cache']) && isset($data['timestamps'])) {
                $this->cache = $data['cache'];
                $this->timestamps = $data['timestamps'];
            }
        }
    }
}

// Initialize the LRU cache
$lruCache = new LRUCache(MAX_CACHE_ITEMS);

/**
 * Get cached data or fetch fresh data
 * 
 * @param string $cacheKey Cache key
 * @param callable $dataFetcher Function to fetch fresh data
 * @return mixed The cached or fresh data
 */
function getCachedData($cacheKey, $dataFetcher) {
    global $lruCache;
    
    // Check if we have cached data
    $cachedData = $lruCache->get($cacheKey);
    
    if ($cachedData !== null) {
        error_log("Data cache hit for: $cacheKey");
        return $cachedData;
    }
    
    // No cache, fetch fresh data
    error_log("Data cache miss for: $cacheKey, fetching fresh data");
    
    try {
        $data = $dataFetcher();
        
        // Store data in cache
        $lruCache->put($cacheKey, $data);
        
        return $data;
    } catch (Exception $e) {
        error_log("Error in getCachedData for key $cacheKey: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Set cache headers for optimal performance
 * 
 * @param int $maxAge Cache duration in seconds
 */
function setCacheHeaders($maxAge = CACHE_DEFAULT_MAX_AGE) {
    header("Cache-Control: public, max-age=$maxAge, s-maxage=$maxAge, stale-while-revalidate=86400");
}
