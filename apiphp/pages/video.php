<?php
// Include cache manager if not already included
require_once __DIR__ . '/../includes/cache-manager.php';

// Get file code from URL
$fileCode = $_GET['file_code'] ?? '';

if (empty($fileCode)) {
    header('Location: ' . url('dashboard'));
    exit;
}

// Get file info with caching
$file = getFileInfo($fileCode);

// Check if file exists
if (!$file) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

// Extract keywords for related videos
$keywords = extractKeywords($file['title']);
$searchQuery = implode(' ', $keywords);

// Get related videos with caching
// We use a unique cache key that includes the search query
$relatedCacheKey = generateCacheKey('related_videos', ['query' => $searchQuery, 'file_code' => $fileCode]);
$relatedData = getFromCache($relatedCacheKey, function() use ($searchQuery) {
    return searchFiles($searchQuery, 1, 12);
}, 3600); // Cache for 1 hour

$relatedVideos = $relatedData['files'];

// Filter out the current video
$filteredRelated = [];
foreach ($relatedVideos as $video) {
    $videoCode = $video['file_code'] ?? $video['filecode'] ?? '';
    if ($videoCode !== $fileCode) {
        $filteredRelated[] = $video;
    }
}
$relatedVideos = array_slice($filteredRelated, 0, 6);

// Get random videos with caching
// We use a unique cache key that includes the current time to the nearest hour
// This ensures random videos refresh periodically but are cached for the current hour
$currentHour = floor(time() / 3600);
$randomCacheKey = generateCacheKey('random_videos', ['hour' => $currentHour, 'file_code' => $fileCode]);
$randomData = getFromCache($randomCacheKey, function() {
    return getRandomFiles(1, 6);
}, 3600); // Cache for 1 hour

$randomVideos = $randomData['files'];

// Filter out the current video and any videos that are already in related
$relatedIds = [];
foreach ($relatedVideos as $video) {
    $relatedIds[] = $video['file_code'] ?? $video['filecode'] ?? '';
}

$filteredRandom = [];
foreach ($randomVideos as $video) {
    $videoCode = $video['file_code'] ?? $video['filecode'] ?? '';
    if ($videoCode !== $fileCode && !in_array($videoCode, $relatedIds)) {
        $filteredRandom[] = $video;
    }
}
$randomVideos = array_slice($filteredRandom, 0, 6);

// Generate video review
$videoReview = generateVideoReview($file);

// Generate Schema.org markup
$schemaMarkup = generateVideoSchema($file);

// Set page title and meta description
$pageTitle = $file['title'] . ' - Content API';
$pageDescription = generateMetaDescription($file);
$pageKeywords = generateMetaKeywords($file);

// Extra head content
$extraHead = <<<HTML
<style>
    .video-thumbnail {
        position: relative;
        background-size: cover;
        background-position: center;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        cursor: pointer;
    }
    
    .video-thumbnail .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    
    .play-button {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 8px;
        transition: background-color 0.2s;
    }
    
    .play-button:hover {
        background-color: rgba(255, 255, 255, 1);
    }
    
    .video-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        overflow: hidden;
    }
    
    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
    
    .selected-video {
        border: 2px solid hsl(var(--primary));
    }
    
    /* Lazy loading styles */
    .lazy-image {
        transition: opacity 0.3s;
    }
    
    .lazy-image.loaded {
        opacity: 1;
    }
    
    .lazy-image:not(.loaded) {
        opacity: 0.1;
    }
    
    /* Keyword tag styles */
    .keyword-tag {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        background-color: hsl(var(--secondary));
        color: hsl(var(--secondary-foreground));
        border-radius: 9999px;
        font-size: 0.75rem;
        line-height: 1;
        text-decoration: none;
        transition: background-color 0.2s, transform 0.2s;
    }
    
    .keyword-tag:hover {
        background-color: hsl(var(--accent));
        transform: translateY(-1px);
    }
</style>
HTML;

// Extra scripts
$extraScripts = <<<HTML
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lazy loading for images
        const lazyImages = document.querySelectorAll('.lazy-image');
        
        if ('IntersectionObserver' in window) {
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
            
            lazyImages.forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            lazyImages.forEach(img => {
                img.src = img.dataset.src;
                img.classList.add('loaded');
            });
        }
        
        // Video player functionality
        const videoThumbnail = document.getElementById('video-thumbnail');
        const videoContainer = document.getElementById('video-container');
        
        if (videoThumbnail) {
            videoThumbnail.addEventListener('click', function() {
                const embedUrl = this.getAttribute('data-embed');
                if (embedUrl) {
                    videoThumbnail.style.display = 'none';
                    videoContainer.innerHTML = `<iframe src="\${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>`;
                    videoContainer.style.display = 'block';
                }
            });
        }
        
        // Sidebar video selection
        const sidebarVideos = document.querySelectorAll('.sidebar-video');
        const videoDetailsContainer = document.getElementById('video-details-container');
        const mainVideoDetails = document.getElementById('main-video-details');
        const selectedVideoDetails = document.getElementById('selected-video-details');
        
        sidebarVideos.forEach(video => {
            video.addEventListener('click', function() {
                // Remove selected class from all videos
                sidebarVideos.forEach(v => v.classList.remove('selected-video'));
                
                // Add selected class to clicked video
                this.classList.add('selected-video');
                
                // Get video data
                const videoData = JSON.parse(this.getAttribute('data-video'));
                
                // Hide main video details and show selected video details
                mainVideoDetails.style.display = 'none';
                selectedVideoDetails.style.display = 'block';
                
                // Update selected video details
                document.getElementById('selected-video-image').src = videoData.single_img || '/assets/images/placeholder.svg';
                document.getElementById('selected-video-image').alt = videoData.title;
                document.getElementById('selected-video-title').textContent = videoData.title;
                document.getElementById('selected-video-views').textContent = videoData.views;
                document.getElementById('selected-video-length').textContent = videoData.length + ' sec';
                document.getElementById('selected-video-uploaded').textContent = videoData.uploaded;
                
                const fileCode = videoData.file_code || videoData.filecode;
                document.getElementById('selected-video-link').href = BASE_URL + '/e/' + $fileCode;
                
                if (videoData.protected_dl) {
                    document.getElementById('selected-video-download').href = videoData.protected_dl;
                    document.getElementById('selected-video-download').style.display = 'inline-flex';
                } else {
                    document.getElementById('selected-video-download').style.display = 'none';
                }
            });
        });
        
        // Tab switching
        const relatedTab = document.getElementById('related-tab');
        const randomTab = document.getElementById('random-tab');
        const relatedVideosContainer = document.getElementById('related-videos');
        const randomVideosContainer = document.getElementById('random-videos');
        
        if (relatedTab && randomTab) {
            relatedTab.addEventListener('click', function() {
                relatedTab.classList.add('bg-background', 'text-foreground', 'shadow-sm');
                randomTab.classList.remove('bg-background', 'text-foreground', 'shadow-sm');
                
                relatedVideosContainer.style.display = 'block';
                randomVideosContainer.style.display = 'none';
            });
            
            randomTab.addEventListener('click', function() {
                randomTab.classList.add('bg-background', 'text-foreground', 'shadow-sm');
                relatedTab.classList.remove('bg-background', 'text-foreground', 'shadow-sm');
                
                relatedVideosContainer.style.display = 'none';
                randomVideosContainer.style.display = 'block';
            });
        }
    });
</script>
HTML;

// Add JavaScript variable for BASE_URL
$extraScripts = '<script>const BASE_URL = "' . BASE_URL . '";</script>' . $extraScripts;

// Start output buffer
ob_start();
?>

<article class="container mx-auto px-4 py-4 md:py-6" itemscope itemtype="http://schema.org/VideoObject">
    <!-- Main content - Video player and details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
        <section class="lg:col-span-2">
            <!-- Main video title -->
            <h1 class="text-xl md:text-2xl font-bold mb-3" itemprop="name"><?= h($file['title']) ?></h1>

            <!-- Custom Video Player - Moved above the back button -->
            <?php if (!empty($file['protected_embed'])): ?>
                <div class="aspect-video w-full rounded-md overflow-hidden border border-border mb-4 bg-black relative">
                    <div id="video-thumbnail" class="video-thumbnail" 
                         style="background-image: url('<?= convertToWebP($file['splash_img'] ?: $file['single_img'], 1280, 720) ?>');"
                         data-embed="<?= h($file['protected_embed']) ?>">
                        <div class="overlay">
                            <div class="play-button">
                                <i data-lucide="play" class="w-8 h-8 ml-1"></i>
                            </div>
                            <p class="text-white font-medium text-sm sm:text-base">Click to play video</p>
                        </div>
                    </div>
                    <div id="video-container" class="video-container" style="display: none;"></div>
                    <meta itemprop="thumbnailUrl" content="<?= h($file['single_img']) ?>">
                    <meta itemprop="contentUrl" content="<?= h($file['protected_embed']) ?>">
                    <meta itemprop="uploadDate" content="<?= h($file['uploaded']) ?>">
                    <meta itemprop="duration" content="PT<?= h($file['length']) ?>S">
                </div>
            <?php endif; ?>

            <!-- Back button - Moved below the video player -->
            <div class="flex items-center mb-4">
                <button onclick="history.back()" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-transparent hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2">
                    <i data-lucide="arrow-left" class="mr-2 h-4 w-4"></i> Back
                </button>
            </div>

            <!-- Brief review -->
            <div class="bg-muted p-3 rounded-md mb-4 text-sm">
                <div class="flex items-start gap-2">
                    <i data-lucide="file-text" class="w-4 h-4 mt-0.5 text-muted-foreground flex-shrink-0"></i>
                    <p itemprop="description"><?= h($videoReview) ?></p>
                </div>

                <?php if (!empty($keywords)): ?>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="flex items-center text-xs text-muted-foreground">
                            <i data-lucide="tag" class="w-3.5 h-3.5 mr-1"></i>
                            Keywords:
                        </span>
                        <?php foreach ($keywords as $keyword): ?>
                            <!-- Made keywords clickable links to search page with proper URL format -->
                            <a href="<?= BASE_URL ?>/f/<?= urlencode($keyword) ?>" class="keyword-tag">
                                <?= h($keyword) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap gap-2 mb-4">
                <?php if (!empty($file['protected_dl'])): ?>
                    <a href="<?= h($file['protected_dl']) ?>" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm h-9 px-4 py-2 gap-2">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        Download
                    </a>
                <?php endif; ?>
                <?php if (!empty($file['protected_embed'])): ?>
                    <a href="<?= h($file['protected_embed']) ?>" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-transparent hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 gap-2">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        Open in New Tab
                    </a>
                <?php endif; ?>
            </div>

            <!-- Video details container -->
            <div id="video-details-container">
                <!-- Main video details -->
                <div id="main-video-details" class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
                    <div class="bg-card rounded-md p-3">
                        <h3 class="text-xs font-medium text-muted-foreground mb-1">Views</h3>
                        <p class="text-base flex items-center gap-1">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span itemprop="interactionCount"><?= h($file['views']) ?></span>
                        </p>
                    </div>
                    <div class="bg-card rounded-md p-3">
                        <h3 class="text-xs font-medium text-muted-foreground mb-1">Uploaded</h3>
                        <p class="text-base flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            <?= h($file['uploaded']) ?>
                        </p>
                    </div>
                    <div class="bg-card rounded-md p-3">
                        <h3 class="text-xs font-medium text-muted-foreground mb-1">Length</h3>
                        <p class="text-base flex items-center gap-1">
                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                            <?= h($file['length']) ?> sec
                        </p>
                    </div>
                    <div class="bg-card rounded-md p-3">
                        <h3 class="text-xs font-medium text-muted-foreground mb-1">File Code</h3>
                        <p class="text-base"><?= h($file['filecode'] ?? $file['file_code']) ?></p>
                    </div>
                    <div class="bg-card rounded-md p-3">
                        <h3 class="text-xs font-medium text-muted-foreground mb-1">Size</h3>
                        <p class="text-base"><?= h($file['size'] ?? 'Unknown') ?></p>
                    </div>
                    <div class="bg-card rounded-md p-3">
                        <h3 class="text-xs font-medium text-muted-foreground mb-1">Can Play</h3>
                        <p class="text-base"><?= $file['canplay'] ? 'Yes' : 'No' ?></p>
                    </div>
                </div>

                <!-- Selected video details (initially hidden) -->
                <div id="selected-video-details" class="mt-4 border-t pt-4" style="display: none;">
                    <h2 class="text-lg font-bold mb-3">Selected Video Details</h2>

                    <div class="flex flex-col md:flex-row gap-4 mb-4">
                        <div class="md:w-1/3">
                            <img id="selected-video-image" src="<?= BASE_URL ?>/assets/images/placeholder.svg" alt="Selected video" 
                                 class="w-full aspect-video object-cover rounded-md lazy-image"
                                 width="400" height="225">
                        </div>

                        <div class="md:w-2/3">
                            <h3 id="selected-video-title" class="text-base font-semibold mb-2"></h3>

                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                    <span>Views: <span id="selected-video-views"></span></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                    <span>Length: <span id="selected-video-length"></span></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                    <span>Uploaded: <span id="selected-video-uploaded"></span></span>
                                </div>
                            </div>

                            <div class="mt-3 flex gap-2">
                                <a id="selected-video-link" href="#" 
                                   class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm h-8 px-3 py-2">
                                    View Full Details
                                </a>
                                <a id="selected-video-download" href="#" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-transparent hover:bg-accent hover:text-accent-foreground h-8 px-3 py-2">
                                    <i data-lucide="download" class="w-3.5 h-3.5 mr-1"></i>
                                    Download
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sidebar - Content Dashboard -->
        <aside class="lg:col-span-1">
            <!-- Search form -->
            <form action="<?= url('search') ?>" method="GET" class="flex flex-col sm:flex-row gap-2 mb-4">
                <input type="text" name="query" placeholder="Search for content..." 
                       class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 flex-1">
                <button type="submit" 
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm h-10 px-4 py-2 w-full sm:w-auto">
                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                    Search
                </button>
            </form>

            <!-- Tabs for Related and Random -->
            <div class="mb-3">
                <div class="inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground w-full grid grid-cols-2 mb-3">
                    <button id="related-tab" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-background text-foreground shadow-sm">Related</button>
                    <button id="random-tab" class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">Random</button>
                </div>
            </div>

            <!-- Related Videos -->
            <div id="related-videos" class="space-y-3">
                <?php if (empty($relatedVideos)): ?>
                    <p class="text-sm text-muted-foreground">No related videos found</p>
                <?php else: ?>
                    <?php foreach ($relatedVideos as $video): ?>
                        <?php 
                            $videoCode = $video['file_code'] ?? $video['filecode'] ?? '';
                            $videoJson = htmlspecialchars(json_encode($video), ENT_QUOTES, 'UTF-8');
                            $thumbnailUrl = convertToWebP($video['single_img'] ?? '', 320, 180);
                            $placeholderSvg = generatePlaceholderSVG(320, 180);
                        ?>
                        <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md cursor-pointer sidebar-video" data-video="<?= $videoJson ?>">
                            <div class="flex flex-row h-20">
                                <div class="w-1/3 h-full relative">
                                    <img data-src="<?= h($thumbnailUrl) ?>" 
                                         src="<?= $placeholderSvg ?>"
                                         alt="<?= h($video['title']) ?>"
                                         class="w-full h-full object-cover lazy-image"
                                         width="320" height="180">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 opacity-0 hover:opacity-100 transition-opacity">
                                        <i data-lucide="play" class="w-6 h-6 text-white"></i>
                                    </div>
                                </div>
                                <div class="w-2/3 p-2">
                                    <p class="text-xs font-medium line-clamp-2"><?= h($video['title']) ?></p>
                                    <p class="text-xs text-muted-foreground mt-1 flex items-center">
                                        <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                                        <?= h($video['views']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Random Videos (initially hidden) -->
            <div id="random-videos" class="space-y-3" style="display: none;">
                <?php if (empty($randomVideos)): ?>
                    <p class="text-sm text-muted-foreground">No random videos found</p>
                <?php else: ?>
                    <?php foreach ($randomVideos as $video): ?>
                        <?php 
                            $videoCode = $video['file_code'] ?? $video['filecode'] ?? '';
                            $videoJson = htmlspecialchars(json_encode($video), ENT_QUOTES, 'UTF-8');
                            $thumbnailUrl = convertToWebP($video['single_img'] ?? '', 320, 180);
                            $placeholderSvg = generatePlaceholderSVG(320, 180);
                        ?>
                        <div class="rounded-lg border border-border bg-card text-card-foreground shadow-sm transition-all duration-200 hover:shadow-md cursor-pointer sidebar-video" data-video="<?= $videoJson ?>">
                            <div class="flex flex-row h-20">
                                <div class="w-1/3 h-full relative">
                                    <img data-src="<?= h($thumbnailUrl) ?>" 
                                         src="<?= $placeholderSvg ?>"
                                         alt="<?= h($video['title']) ?>"
                                         class="w-full h-full object-cover lazy-image"
                                         width="320" height="180">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 opacity-0 hover:opacity-100 transition-opacity">
                                        <i data-lucide="play" class="w-6 h-6 text-white"></i>
                                    </div>
                                </div>
                                <div class="w-2/3 p-2">
                                    <p class="text-xs font-medium line-clamp-2"><?= h($video['title']) ?></p>
                                    <p class="text-xs text-muted-foreground mt-1 flex items-center">
                                        <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                                        <?= h($video['views']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</article>

<?php
// Get content from buffer
$content = ob_get_clean();

// Include layout
include __DIR__ . '/../includes/layout.php';
?>
