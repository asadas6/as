<?php
// Set page title
$pageTitle = '404 Not Found - Content API';

// Start output buffer
ob_start();
?>

<div class="container mx-auto px-4 py-8 flex flex-col items-center justify-center min-h-[70vh] text-center">
    <h1 class="text-4xl font-bold mb-4">404</h1>
    <p class="text-xl mb-6">Page not found</p>
    <p class="text-muted-foreground mb-8">The page you are looking for doesn't exist or has been moved.</p>
    <div class="flex gap-4">
        <a href="<?= url('home') ?>" 
           class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm h-10 px-4 py-2">
            Go Home
        </a>
        <a href="<?= url('dashboard') ?>" 
           class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-transparent hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
            Dashboard
        </a>
    </div>
</div>

<?php
// Get content from buffer
$content = ob_get_clean();

// Include layout
include 'includes/layout.php';
?>
