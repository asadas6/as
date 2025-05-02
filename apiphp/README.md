# Content API PHP Application

This PHP application provides a user interface for browsing and searching content from the Content API.

## Features

- Multi-layer caching system for improved performance
- Responsive design for all device sizes
- SEO optimizations
- Image optimizations with WebP format
- Lazy loading for images and videos
- Clean URL structure

## Caching System

The application implements a three-layer caching system:

1. **Memory Cache**: Fastest, lasts only for the current request
2. **Local File Cache**: Persists between requests, stored in the `/cache` directory
3. **API**: Fallback when cache misses

### Cache Configuration

Cache TTL (Time To Live) values:
- File info: 1 hour (3600 seconds)
- Search results: 1 hour (3600 seconds)
- File list: 30 minutes (1800 seconds)
- Random files: 15 minutes (900 seconds)

### Cache Management

To clear the cache manually, you can use the following functions:

\`\`\`php
// Clear a specific cache entry
clearCache('cache_key');

// Clear all cache entries
clearAllCache();
\`\`\`

## Installation

1. Upload the files to your web server
2. Ensure the `/cache` directory is writable by the web server
3. Configure your web server to handle clean URLs (see .htaccess file)

## Requirements

- PHP 7.4 or higher
- Apache with mod_rewrite enabled (or equivalent for Nginx)
- Write permissions for the `/cache` directory

## License

All rights reserved.
\`\`\`

Let's update the main index.php file to include our caching system:
