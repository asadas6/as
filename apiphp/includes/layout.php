<?php
/**
 * Main layout template
 */

// Default values
$pageTitle = $pageTitle ?? 'Content API';
$pageDescription = $pageDescription ?? 'Browse and search content from our API';
$pageKeywords = $pageKeywords ?? 'video, content, api, streaming';
$extraHead = $extraHead ?? '';
$extraScripts = $extraScripts ?? '';
$schemaMarkup = $schemaMarkup ?? '';

// Get current canonical URL
$canonicalUrl = getCurrentCanonicalUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add additional meta tags to ensure proper rendering on all devices -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= h($pageTitle) ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= h($pageDescription) ?>">
    <meta name="keywords" content="<?= h($pageKeywords) ?>">
    <link rel="canonical" href="<?= h($canonicalUrl) ?>">

    <!-- Mobile-specific meta tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">

    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:title" content="<?= h($pageTitle) ?>">
    <meta property="og:description" content="<?= h($pageDescription) ?>">
    <meta property="og:url" content="<?= h($canonicalUrl) ?>">
    <meta property="og:type" content="website">
    
    <!-- Preload Critical Resources -->
    <link rel="preload" href="/assets/css/styles.css" as="style">
    <link rel="preload" href="/assets/js/main.js" as="script">
    
    <!-- Critical CSS Inline -->
    <style>
        /* Base styles for immediate rendering */
        :root {
            --background: 0 0% 100%;
            --foreground: 240 10% 3.9%;
            --card: 0 0% 100%;
            --card-foreground: 240 10% 3.9%;
            --popover: 0 0% 100%;
            --popover-foreground: 240 10% 3.9%;
            --primary: 240 5.9% 10%;
            --primary-foreground: 0 0% 98%;
            --secondary: 240 4.8% 95.9%;
            --secondary-foreground: 240 5.9% 10%;
            --muted: 240 4.8% 95.9%;
            --muted-foreground: 240 3.8% 46.1%;
            --accent: 240 4.8% 95.9%;
            --accent-foreground: 240 5.9% 10%;
            --destructive: 0 84.2% 60.2%;
            --destructive-foreground: 0 0% 98%;
            --border: 240 5.9% 90%;
            --input: 240 5.9% 90%;
            --ring: 240 5.9% 10%;
            --radius: 0.5rem;
        }
        
        .dark {
            --background: 240 10% 3.9%;
            --foreground: 0 0% 98%;
            --card: 240 10% 3.9%;
            --card-foreground: 0 0% 98%;
            --popover: 240 10% 3.9%;
            --popover-foreground: 0 0% 98%;
            --primary: 0 0% 98%;
            --primary-foreground: 240 5.9% 10%;
            --secondary: 240 3.7% 15.9%;
            --secondary-foreground: 0 0% 98%;
            --muted: 240 3.7% 15.9%;
            --muted-foreground: 240 5% 64.9%;
            --accent: 240 3.7% 15.9%;
            --accent-foreground: 0 0% 98%;
            --destructive: 0 62.8% 30.6%;
            --destructive-foreground: 0 0% 98%;
            --border: 240 3.7% 15.9%;
            --input: 240 3.7% 15.9%;
            --ring: 240 4.9% 83.9%;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: hsl(var(--background));
            color: hsl(var(--foreground));
            line-height: 1.5;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background-color: hsl(var(--card));
            border-bottom: 1px solid hsl(var(--border));
            padding: 1rem;
        }
        
        .footer {
            background-color: hsl(var(--card));
            border-top: 1px solid hsl(var(--border));
            padding: 1rem;
            margin-top: 2rem;
        }
    </style>
    
    <!-- Non-critical CSS -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js" defer></script>
    
    <!-- Extra head content -->
    <?= $extraHead ?>
    
    <!-- Schema.org JSON-LD -->
    <?php if (!empty($schemaMarkup)): ?>
    <script type="application/ld+json">
        <?= $schemaMarkup ?>
    </script>
    <?php endif; ?>
</head>
<body class="min-h-screen flex flex-col">
    <header class="header">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <a href="<?= url('dashboard') ?>" class="text-xl font-bold">Content API</a>
                <nav>
                    <ul class="flex space-x-4">
                        <li><a href="<?= url('dashboard') ?>" class="hover:underline">Dashboard</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="flex-1">
        <?= $content ?>
    </main>
    
    <footer class="footer text-center text-sm text-muted-foreground">
        <div class="container mx-auto px-4">
            <p>&copy; <?= date('Y') ?> Content API. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Main JavaScript (deferred) -->
    <script src="/assets/js/main.js" defer></script>
    
    <!-- Initialize Lucide Icons -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
    </script>
    
    <!-- Lazy Loading Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lazy loading for images
            if ('loading' in HTMLImageElement.prototype) {
                // Browser supports native lazy loading
                const lazyImages = document.querySelectorAll('img.lazy-image');
                lazyImages.forEach(img => {
                    img.src = img.dataset.src;
                    img.loading = 'lazy';
                });
            } else if ('IntersectionObserver' in window) {
                // Use Intersection Observer for lazy loading
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.add('loaded');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                const lazyImages = document.querySelectorAll('img.lazy-image');
                lazyImages.forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback for browsers without IntersectionObserver
                const lazyImages = document.querySelectorAll('img.lazy-image');
                lazyImages.forEach(img => {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                });
            }
        });
    </script>
    
    <!-- Extra scripts -->
    <?= $extraScripts ?>
</body>
</html>
