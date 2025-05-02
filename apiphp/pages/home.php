<?php
// Set page title
$pageTitle = 'Home - Content API';

// Start output buffer
ob_start();
?>

<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
    <div class="flex flex-col items-center text-center mb-8 sm:mb-12">
        <h1 class="text-3xl sm:text-4xl font-bold mb-2 sm:mb-4">High-Performance API Dashboard</h1>
        <p class="text-lg sm:text-xl text-muted-foreground max-w-2xl mb-4 sm:mb-8">
            Browse, search, and explore content with our optimized API interface
        </p>
        <a href="<?= url('dashboard') ?>" 
           class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm h-10 px-4 py-2 gap-2">
            Go to Dashboard
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-8 sm:mb-12">
        <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md hover:translate-y-[-2px]">
            <div class="flex flex-col space-y-1.5 p-6 pb-2">
                <h3 class="flex items-center gap-2 text-base sm:text-lg font-semibold leading-none tracking-tight">
                    <i data-lucide="list" class="w-4 h-4 sm:w-5 sm:h-5 text-primary"></i>
                    List View
                </h3>
            </div>
            <div class="p-6 pt-0">
                <p class="text-xs sm:text-sm text-muted-foreground">
                    Browse through paginated content with optimized loading
                </p>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md hover:translate-y-[-2px]">
            <div class="flex flex-col space-y-1.5 p-6 pb-2">
                <h3 class="flex items-center gap-2 text-base sm:text-lg font-semibold leading-none tracking-tight">
                    <i data-lucide="search" class="w-4 h-4 sm:w-5 sm:h-5 text-primary"></i>
                    Search
                </h3>
            </div>
            <div class="p-6 pt-0">
                <p class="text-xs sm:text-sm text-muted-foreground">
                    Find specific content with our powerful search functionality
                </p>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md hover:translate-y-[-2px]">
            <div class="flex flex-col space-y-1.5 p-6 pb-2">
                <h3 class="flex items-center gap-2 text-base sm:text-lg font-semibold leading-none tracking-tight">
                    <i data-lucide="shuffle" class="w-4 h-4 sm:w-5 sm:h-5 text-primary"></i>
                    Random
                </h3>
            </div>
            <div class="p-6 pt-0">
                <p class="text-xs sm:text-sm text-muted-foreground">
                    Discover new content with our random content generator
                </p>
            </div>
        </div>

        <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md hover:translate-y-[-2px]">
            <div class="flex flex-col space-y-1.5 p-6 pb-2">
                <h3 class="flex items-center gap-2 text-base sm:text-lg font-semibold leading-none tracking-tight">
                    <i data-lucide="info" class="w-4 h-4 sm:w-5 sm:h-5 text-primary"></i>
                    Details
                </h3>
            </div>
            <div class="p-6 pt-0">
                <p class="text-xs sm:text-sm text-muted-foreground">
                    View comprehensive details about each content item
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-6">
        <div class="p-4 sm:p-6 bg-muted rounded-lg">
            <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="database" class="w-5 h-5"></i>
                API Features
            </h2>
            <ul class="space-y-2">
                <li class="flex items-start gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4 mt-1 text-primary"></i>
                    <span>Raw data caching with dynamic title processing</span>
                </li>
                <li class="flex items-start gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4 mt-1 text-primary"></i>
                    <span>HTTP cache headers for CDN caching</span>
                </li>
                <li class="flex items-start gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4 mt-1 text-primary"></i>
                    <span>Robust error handling</span>
                </li>
                <li class="flex items-start gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4 mt-1 text-primary"></i>
                    <span>LRU cache for memory management</span>
                </li>
                <li class="flex items-start gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4 mt-1 text-primary"></i>
                    <span>Multiple data source fallbacks</span>
                </li>
            </ul>
        </div>

        <div class="p-4 sm:p-6 bg-muted rounded-lg">
            <h2 class="text-xl font-semibold mb-4">API Endpoints</h2>
            <ul class="space-y-2">
                <li>
                    <a href="<?= API_BASE_URL ?>/list?page=1&per_page=10" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>/api/list</span>
                    </a>
                </li>
                <li>
                    <a href="<?= API_BASE_URL ?>/rand?page=1&per_page=10" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>/api/rand</span>
                    </a>
                </li>
                <li>
                    <a href="<?= API_BASE_URL ?>/info?file_code=0vq5urkvffjl" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>/api/info</span>
                    </a>
                </li>
                <li>
                    <a href="<?= API_BASE_URL ?>/search?q=video&page=1&per_page=10" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>/api/search</span>
                    </a>
                </li>
                <li>
                    <a href="<?= API_BASE_URL ?>/compressed" target="_blank" class="text-primary hover:underline flex items-center gap-2">
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        <span>/api/compressed</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php
// Get content from buffer
$content = ob_get_clean();

// Include layout
include 'includes/layout.php';
?>
