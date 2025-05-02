<?php
/**
 * Utility functions for the application
 */

/**
 * Escape HTML output
 * 
 * @param string $text Text to escape
 * @return string Escaped text
 */
function h($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate URL for a route
 * 
 * @param string $route Route name
 * @param array $params URL parameters
 * @return string Full URL
 */
function url($route, $params = []) {
    // Clean route for SEO-friendly URLs
    $cleanRoute = trim($route, '/');
    
    // For clean URLs without query parameters
    if (empty($params)) {
        return BASE_URL . '/' . $cleanRoute;
    }
    
    // For URLs with query parameters
    $url = BASE_URL . '/' . $cleanRoute;
    $queryString = http_build_query($params);
    
    return $url . ($queryString ? '?' . $queryString : '');
}

/**
 * Get current canonical URL
 * 
 * @return string Canonical URL
 */
function getCurrentCanonicalUrl() {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Remove trailing slashes for consistency
    $uri = rtrim($uri, '/');
    
    return $protocol . "://" . $host . $uri;
}

/**
 * Format time in seconds to a readable format
 * 
 * @param int $seconds Time in seconds
 * @return string Formatted time
 */
function formatTime($seconds) {
    $minutes = floor($seconds / 60);
    $remainingSeconds = $seconds % 60;
    
    if ($minutes > 0) {
        return $minutes . ' min ' . $remainingSeconds . ' sec';
    } else {
        return $seconds . ' seconds';
    }
}

/**
 * Extract keywords from a title
 * 
 * @param string $title Video title
 * @return array Array of keywords
 */
function extractKeywords($title) {
    if (empty($title)) return [];
    
    // Remove common stop words and split by spaces
    $stopWords = ["a", "an", "the", "and", "or", "but", "in", "on", "at", "to", "for", "with", "by", "about", "as"];
    $words = preg_split('/\s+/', strtolower($title));
    
    // Filter out stop words and short words (less than 3 characters)
    $filteredWords = [];
    foreach ($words as $word) {
        if (!in_array($word, $stopWords) && strlen($word) >= 3 && !preg_match('/^\d+$/', $word)) {
            $filteredWords[] = $word;
        }
    }
    
    // Get unique words and limit to 4 most relevant (longer words tend to be more specific)
    $uniqueWords = array_unique($filteredWords);
    usort($uniqueWords, function($a, $b) {
        return strlen($b) - strlen($a);
    });
    
    return array_slice($uniqueWords, 0, 4);
}

/**
 * Generate a brief review of the video
 * 
 * @param array $file Video data
 * @return string Review text
 */
function generateVideoReview($file) {
    if (empty($file)) return "";
    
    $lengthInMinutes = intval($file['length']) / 60;
    $formattedLength = $lengthInMinutes < 1 
        ? intval($file['length']) . " seconds" 
        : number_format($lengthInMinutes, 1) . " minutes";
    
    $viewCount = intval($file['views']);
    $popularityDesc = "new";
    if ($viewCount > 10000) $popularityDesc = "extremely popular";
    else if ($viewCount > 5000) $popularityDesc = "very popular";
    else if ($viewCount > 1000) $popularityDesc = "popular";
    else if ($viewCount > 100) $popularityDesc = "moderately viewed";
    
    // Calculate approximate upload date
    $uploadTimeDesc = "recently";
    if (!empty($file['uploaded'])) {
        try {
            $uploadDate = new DateTime($file['uploaded']);
            $now = new DateTime();
            $diffDays = $now->diff($uploadDate)->days;
            
            if ($diffDays > 365) $uploadTimeDesc = floor($diffDays / 365) . " years ago";
            else if ($diffDays > 30) $uploadTimeDesc = floor($diffDays / 30) . " months ago";
            else if ($diffDays > 0) $uploadTimeDesc = $diffDays . " days ago";
            else $uploadTimeDesc = "today";
        } catch (Exception $e) {
            // If date parsing fails, use the default "recently"
        }
    }
    
    // Generate a more detailed review
    $review = "This {$formattedLength} video was uploaded {$uploadTimeDesc} and has been viewed {$viewCount} times, making it {$popularityDesc}. ";
    
    // Add more details based on available data
    if (!empty($file['title'])) {
        $keywords = extractKeywords($file['title']);
        if (!empty($keywords)) {
            $review .= "The content appears to be about " . implode(", ", array_slice($keywords, 0, 3)) . ". ";
        }
    }
    
    $review .= (isset($file['canplay']) && $file['canplay'] ? "It's available for playback and streaming. " : "");
    
    if (!empty($file['size'])) {
        $sizeInMB = round(intval($file['size']) / (1024 * 1024), 2);
        $review .= "The file size is approximately {$sizeInMB} MB. ";
    }
    
    return $review;
}

/**
 * Generate Schema.org markup for a video
 * 
 * @param array $file Video data
 * @return string JSON-LD markup
 */
function generateVideoSchema($file) {
    if (empty($file)) return "";
    
    $fileCode = $file['file_code'] ?? $file['filecode'] ?? '';
    $videoUrl = getCurrentCanonicalUrl();
    $thumbnailUrl = $file['single_img'] ?? '';
    $uploadDate = !empty($file['uploaded']) ? date('c', strtotime($file['uploaded'])) : date('c');
    
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "VideoObject",
        "name" => $file['title'] ?? 'Video',
        "description" => generateVideoReview($file),
        "thumbnailUrl" => $thumbnailUrl,
        "uploadDate" => $uploadDate,
        "contentUrl" => $file['protected_embed'] ?? $videoUrl,
        "duration" => "PT" . ($file['length'] ?? 0) . "S",
        "interactionStatistic" => [
            "@type" => "InteractionCounter",
            "interactionType" => "https://schema.org/WatchAction",
            "userInteractionCount" => $file['views'] ?? 0
        ]
    ];
    
    return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

/**
 * Convert image URL to WebP format
 * 
 * @param string $url Original image URL
 * @param int $width Desired width
 * @param int $height Desired height
 * @return string WebP image URL
 */
function convertToWebP($url, $width = 0, $height = 0) {
    if (empty($url)) {
        return BASE_URL . '/assets/images/placeholder.svg';
    }
    
    // Check if URL already has query parameters
    $separator = (strpos($url, '?') !== false) ? '&' : '?';
    
    // Add WebP conversion parameter
    $webpUrl = $url . $separator . 'output=webp';
    
    // Add dimensions if provided
    if ($width > 0 && $height > 0) {
        $webpUrl .= "&width={$width}&height={$height}";
    }
    
    return $webpUrl;
}

/**
 * Generate a placeholder SVG for lazy loading
 * 
 * @param int $width Width of the placeholder
 * @param int $height Height of the placeholder
 * @param string $color Background color (hex without #)
 * @return string Data URL of SVG
 */
function generatePlaceholderSVG($width = 300, $height = 200, $color = '1f1f1f') {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '"><rect width="100%" height="100%" fill="#' . $color . '"/></svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

/**
 * Generate meta description from video data
 * 
 * @param array $file Video data
 * @return string Meta description
 */
function generateMetaDescription($file) {
    if (empty($file)) return "";
    
    $title = $file['title'] ?? 'Video';
    $views = $file['views'] ?? '0';
    $length = $file['length'] ?? '0';
    
    $lengthInMinutes = intval($length) / 60;
    $formattedLength = $lengthInMinutes < 1 
        ? intval($length) . " seconds" 
        : number_format($lengthInMinutes, 1) . " minutes";
    
    $keywords = extractKeywords($title);
    $keywordsText = !empty($keywords) ? implode(", ", $keywords) : "";
    
    return "Watch {$title} - a {$formattedLength} video with {$views} views. " . 
           (!empty($keywordsText) ? "Keywords: {$keywordsText}. " : "") . 
           "Stream or download now.";
}

/**
 * Generate meta keywords from video data
 * 
 * @param array $file Video data
 * @return string Meta keywords
 */
function generateMetaKeywords($file) {
    if (empty($file)) return "";
    
    $keywords = extractKeywords($file['title'] ?? '');
    
    // Add some generic keywords
    $genericKeywords = ['video', 'streaming', 'content', 'media', 'watch online'];
    
    // Combine and make unique
    $allKeywords = array_unique(array_merge($keywords, $genericKeywords));
    
    return implode(', ', $allKeywords);
}

// Add a function to ensure URLs are properly formatted for search queries

/**
 * Format a search query for URL usage
 * 
 * @param string $query Search query
 * @return string Formatted query
 */
function formatSearchQuery($query) {
    // Remove special characters that might cause issues in URLs
    $query = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $query);
    
    // Trim whitespace and ensure proper encoding
    $query = trim($query);
    
    return $query;
}

/**
 * Check if a file exists and is readable
 * 
 * @param string $path File path
 * @return bool True if file exists and is readable
 */
function fileExists($path) {
    return file_exists($path) && is_readable($path);
}

/**
 * Create a directory if it doesn't exist
 * 
 * @param string $path Directory path
 * @return bool True if directory exists or was created
 */
function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

/**
 * Debug function - only outputs in development
 * 
 * @param mixed $data Data to debug
 * @param bool $die Whether to die after output
 */
function debug($data, $die = false) {
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        if ($die) die();
    }
}
