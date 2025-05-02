<?php
// Title processing functions

// Additional keywords for title processing
$additionalKeywords = [
    "sotwe", "bokep dood", "twitter", "bokepsatset", "simontok", "terbaru", "video", 
    "bokep video", "simintok", "xpanas", "Full album", "baru", "montok", "memek", 
    "bokep", "asupan", "doodstream", "bokepsin", "bebasindo", "pekoblive", "terabox", 
    "streaming", "viral", "indo", "tiktok", "telegram", "Doodstream", "Doods", "Pro", 
    "Telegram", "Full", "Album", "Viral", "Videos", "Poophd", "Twitter", "Bochiel", 
    "Asupan", "Link", "Streaming", "Web", "Folder", "Cilbo", "Live", "Tele", "Terupdate", 
    "Terbaru", "Links", "Lokal", "Dodstream", "Bokep", "Pemersatu", "Video", "Update", 
    "Dood", "Doostream", "Website", "Downloader", "Indo", "Lulustream", "Sotwe", 
    "Doodsflix", "Yakwad", "Doodflix", "Tobrut", "Lagi Viral", "Stw", "Doodstreem", 
    "Sumenep", "Malam", "Jilbab", "Sesuai", "Gambar", "Colmek", "Binor", "Davis", 
    "Smp", "Vk", "Asupan viral", "Download", "New", "Movies", "Hijab", "Hijabers", 
    "Rusia", "tele", "Bangsa", "Pejuang", "Lendir", "Popstream", "Staklam", 
    "viral dood", "Cpasmieux", "Prank", "Ojol"
];

/**
 * Process a title with dynamic keyword addition
 * 
 * @param string $title The original title
 * @return string The processed title
 */
function processTitle($title) {
    global $additionalKeywords;
    
    // Handle null or empty title
    if (empty($title)) {
        return "Untitled Content";
    }
    
    try {
        // Split the title into words and limit to 7
        $words = array_slice(preg_split('/\s+/', $title), 0, 7);
        
        // Convert to lowercase for easier comparison and remove duplicates
        $words = array_unique(array_map('strtolower', $words));
        
        // Create a copy of additionalKeywords to shuffle
        $shuffledKeywords = $additionalKeywords;
        
        // Fisher-Yates shuffle algorithm
        for ($i = count($shuffledKeywords) - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $temp = $shuffledKeywords[$i];
            $shuffledKeywords[$i] = $shuffledKeywords[$j];
            $shuffledKeywords[$j] = $temp;
        }
        
        // Add additional keywords until we reach 12 words
        $index = 0;
        while (count($words) < 12 && $index < count($shuffledKeywords)) {
            $newWord = strtolower($shuffledKeywords[$index]);
            if (!in_array($newWord, $words)) {
                $words[] = $newWord;
            }
            $index++;
        }
        
        // Capitalize the first letter of each word
        $processedWords = array_map(function($word) {
            return ucfirst($word);
        }, $words);
        
        return implode(' ', $processedWords);
    } catch (Exception $e) {
        error_log("Error processing title: " . $e->getMessage());
        return "Processed Content";
    }
}
