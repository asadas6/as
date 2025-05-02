<?php
// Include cache manager if not already included
require_once __DIR__ . '/../includes/cache-manager.php';

// Ensure the search page properly handles the query parameter from both forms and direct links

// Get search query from URL
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

// If query is empty, redirect to dashboard
if (empty($query)) {
    header('Location: ' . url('dashboard'));
    exit;
}

// Get page and per_page parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 12;

// Ensure valid values
$page = max(1, $page);
$perPage = max(1, min(50, $perPage));

// Search for files with caching
// We use a unique cache key that includes the search query and pagination
$searchCacheKey = generateCacheKey('search_page', ['query' => $query, 'page' => $page, 'per_page' => $perPage]);
$data = getFromCache($searchCacheKey, function() use ($query, $page, $perPage) {
    return searchFiles($query, $page, $perPage);
}, 3600); // Cache for 1 hour

$files = $data['files'];
$totalResults = $data['totalResults'];
$totalPages = ceil($totalResults / $perPage);

// Set page title and meta description
$pageTitle = 'Search Results for "' . h($query) . '" - Content API';
$pageDescription = 'Search results for "' . h($query) . '" - Browse and find content from our API';

// Start output buffer
ob_start();
?>

<div class="container mx-auto px-2 sm:px-4 py-4 sm:py-8">
    <h1 class="text-2xl sm:text-3xl font-bold mb-4 sm:mb-6">
        Search Results for: <?= h($query) ?>
    </h1>

    <form action="<?= url('search') ?>" method="GET" class="flex flex-col sm:flex-row gap-2 mb-6">
        <input type="text" name="query" value="<?= h($query) ?>" placeholder="Enter search query..." 
               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 flex-1">
        <button type="submit" 
                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm h-10 px-4 py-2 w-full sm:w-auto">
            <i data-lucide="search" class="w-4 h-4 mr-2"></i>
            Search
        </button>
    </form>

    <?php if (empty($files)): ?>
        <div class="text-center py-12">
            <p class="text-muted-foreground">No files found</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
            <?php foreach ($files as $file): ?>
                <?php 
                    $fileCode = $file['file_code'] ?? $file['filecode'] ?? '';
                    if (empty($fileCode)) continue;
                    $thumbnailUrl = convertToWebP($file['single_img'] ?? '', 320, 180);
                    $placeholderSvg = generatePlaceholderSVG(320, 180);
                ?>
                <a href="<?= url('e/' . $fileCode) ?>" class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md hover:translate-y-[-2px] overflow-hidden">
                    <div class="aspect-video relative">
                        <img data-src="<?= h($thumbnailUrl) ?>" 
                             src="<?= $placeholderSvg ?>"
                             alt="<?= h($file['title']) ?>"
                             class="w-full h-full object-cover lazy-image"
                             width="320" height="180">
                    </div>
                    <div class="p-3 sm:p-4">
                        <h3 class="font-medium text-sm sm:text-base line-clamp-2 h-8 sm:h-12"><?= h($file['title']) ?></h3>
                        <div class="flex justify-between text-xs sm:text-sm text-muted-foreground mt-2">
                            <span>Views: <?= h($file['views']) ?></span>
                            <span class="flex items-center">
                                <i data-lucide="info" class="w-3 h-3 sm:w-4 sm:h-4 inline mr-1"></i>
                                <?= substr($fileCode, 0, 6) ?>...
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav role="navigation" aria-label="pagination" class="mx-auto flex w-full justify-center mt-8">
                <ul class="flex flex-row items-center gap-1">
                    <li>
                        <a href="<?= url('search', ['query' => $query, 'page' => max(1, $page - 1)]) ?>" 
                           class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 gap-1 pl-2.5 <?= $page <= 1 ? 'pointer-events-none opacity-50' : '' ?>"
                           aria-label="Go to previous page">
                            <i data-lucide="chevron-left" class="h-4 w-4"></i>
                            <span>Previous</span>
                        </a>
                    </li>
                    
                    <?php
                    // Calculate which page numbers to show
                    $startPage = max(1, min($page - 2, $totalPages - 4));
                    $endPage = min($totalPages, max($page + 2, 5));
                    
                    // Ensure we show at least 5 pages if available
                    if ($endPage - $startPage + 1 < 5 && $totalPages >= 5) {
                        if ($startPage === 1) {
                            $endPage = min($totalPages, 5);
                        } else {
                            $startPage = max(1, $totalPages - 4);
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li>
                            <a href="<?= url('search', ['query' => $query, 'page' => $i]) ?>"
                               class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 w-9 <?= $i === $page ? 'border border-input bg-background' : 'hover:bg-accent hover:text-accent-foreground' ?>"
                               aria-current="<?= $i === $page ? 'page' : 'false' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li>
                        <a href="<?= url('search', ['query' => $query, 'page' => min($totalPages, $page + 1)]) ?>"
                           class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 gap-1 pr-2.5 <?= $page >= $totalPages ? 'pointer-events-none opacity-50' : '' ?>"
                           aria-label="Go to next page">
                            <span>Next</span>
                            <i data-lucide="chevron-right" class="h-4 w-4"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Get content from buffer
$content = ob_get_clean();

// Include layout
include 'includes/layout.php';
?>
