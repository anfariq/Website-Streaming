<?php
// Include your database connection file
if (isset($_GET['url'])) {
    // Mendapatkan path dari parameter URL
    $path = $_GET['url'];

    // Domain dasar
    $baseDomain = "https://v9.animasu.cc/"; // Jika menggunakan variabel global

    // Gabungkan domain dasar dengan path
    $url = rtrim($baseDomain, '/') . '/' . ltrim($path, '/');
    
    // Validasi URL untuk keamanan
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        header("Location: url_error.html");
        exit;
    }
} else {
    header("Location: cari_apa_hayo.html");
    exit;
}

function get_web_content($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false, // Hati-hati dengan ini di production
        CURLOPT_ENCODING => '', // Menerima semua jenis encoding
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', // User agent modern
        CURLOPT_HTTPHEADER => [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
            'Upgrade-Insecure-Requests: 1'
        ]
    ]);
    $data = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "<script>
        alert('Server error');
        window.location.href = 'index.php';
        </script>";
        exit;
    }

    curl_close($ch);
    return $data;
}

require 'simple_html_dom.php';

function scrape_komikindo($url) {
    $html_content = get_web_content($url);
    if (!$html_content) {
        echo "Failed to retrieve content.";
        return [];
    }

    $html = str_get_html($html_content);
    $results = [];

    // Extracting the title
    $title = $html->find('h1[itemprop="headline"]', 0) ? $html->find('h1[itemprop="headline"]', 0)->plaintext : null;

    // Extracting the description
    $description = $html->find('.sinopsis', 0) ? $html->find('.sinopsis', 0)->plaintext : null;

    // Extract image (thumbnail) using a more direct find method
    $imageElement = $html->find("div.thumb meta[itemprop='url']", 0);

    // Check if the image element exists and has content
    if ($imageElement && isset($imageElement->content)) {
        $image = $imageElement->content;

        // Add base domain to image URL if it's a relative path
        if (strpos($image, 'http') === false) {
            $image = rtrim($baseDomain, '/') . '/' . ltrim($image, '/');
        }
    } else {
        $image = '';  // Fallback if the image is not found
    }
    // Extracting genres

    $genres = $html->find('.spe span', 0) ? $html->find('.spe span', 0)->plaintext : null;

    // Extracting the status
    $status = $html->find('.spe span', 2) ? $html->find('.spe span', 2)->find('font', 0)->plaintext : null;

    // Extracting the emoji from the status
    $emoji = null;
    if ($status && preg_match('/&#x([0-9A-F]+);/', $html->find('.spe span', 2)->innertext, $matches)) {
        $emoji = html_entity_decode("&#x{$matches[1]};", ENT_NOQUOTES, 'UTF-8');
    }

    // Collecting data in an associative array
    $results[] = [
        'title' => $title,
        'description' => $description,
        'genres' => $genres,
        'status' => $status,
        'image' => $image,
        'emoji' => $emoji
    ];

    // Extract episodes information
    $episodes = [];
    foreach ($html->find('#daftarepisode li') as $episode) {
        $episodeLink = $episode->find('.lchx a', 0)->href ?? '';
        $episodeNumber = trim(str_replace('Episode ', '', $episode->find('.lchx a', 0)->plaintext));
    
        // Fixing the URL parsing
        $episodeUrl = parse_url($episodeLink); // No need for item() here
        $path1 = $episodeUrl['path'] ?? ''; // Get the path component from the URL
        $processedLinkEpisode = ltrim($path1, '/'); // Remove the leading "/" from the path
    
        // Collect episode data
        $episodes[] = [
            'episode' => $episodeNumber,
            'linksot' => $processedLinkEpisode,
        ];
    }

    // Extract Link information
    $links = []; // Rename to avoid conflict with the $Link variable

    foreach ($html->find('.bottom') as $element) { 
        // Get the first 'a' tag inside each '.bottom' div
        $link = $element->find('a', 0)->href ?? ''; 

        // Fixing the URL parsing
        $LinkUrl = parse_url($link); // No need for item() here
        $path1 = $LinkUrl['path'] ?? ''; // Get the path component from the URL
        $processedLink = ltrim($path1, '/'); // Remove the leading "/" from the path

        // Collect episode data in the array
        $links[] = [
            'link' => $processedLink,
        ];
    }

    return [
        'comic' => $results,
        'episodes' => $episodes,
        'links' => $links,
    ];
}

$komikData = scrape_komikindo($url);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($komikData['comic'][0]['title'] ?? 'Anime Details') ?> - AnimeID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#FF5F1F',
                        dark: {
                            100: '#333333',
                            200: '#222222',
                            300: '#1a1a1a',
                            400: '#141414',
                            500: '#0a0a0a',
                            600: '#000000',
                        },
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s linear infinite',
                    }
                }
            }
        }
    </script>
    <style>
        #bookmark-alert {
            transition: opacity 0.5s ease-in-out;
        }
        .episode-list::-webkit-scrollbar {
            width: 8px;
        }
        .episode-list::-webkit-scrollbar-track {
            background: #1a1a1a;
        }
        .episode-list::-webkit-scrollbar-thumb {
            background: #FF5F1F;
            border-radius: 4px;
        }
        .episode-list::-webkit-scrollbar-thumb:hover {
            background: #e04e12;
        }
    </style>
</head>
<body class="bg-black text-gray-300 min-h-screen flex flex-col">
    <!-- Top Navigation -->
    <header class="bg-dark-500 shadow-lg sticky top-0 z-40 border-b border-gray-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php" class="flex items-center">
                <span class="text-3xl font-bold bg-gradient-to-r from-primary to-purple-500 text-transparent bg-clip-text">AnimeID</span>
            </a>
            <nav class="hidden md:block">
                <ul class="flex space-x-8">
                    <li>
                        <a href="index.php" class="text-lg hover:text-primary transition-colors duration-300 flex items-center">
                            <i class="fas fa-home mr-2"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="comic/index.php" class="text-lg hover:text-primary transition-colors duration-300 flex items-center">
                            <i class="fas fa-book mr-2"></i> Comic
                        </a>
                    </li>
                    <li>
                        <a href="search.php" class="text-lg hover:text-primary transition-colors duration-300 flex items-center">
                            <i class="fas fa-search mr-2"></i> Search
                        </a>
                    </li>
                </ul>
            </nav>
            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button type="button" class="text-gray-300 hover:text-primary focus:outline-none" id="mobile-menu-button">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile menu, hidden by default -->
        <div class="md:hidden hidden bg-dark-400 pb-4 border-b border-gray-800" id="mobile-menu">
            <div class="px-4 pt-2 pb-3 space-y-4">
                <a href="index.php" class="block py-2 hover:text-primary transition-colors duration-300">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
                <a href="comic/index.php" class="block py-2 hover:text-primary transition-colors duration-300">
                    <i class="fas fa-book mr-2"></i> Comic
                </a>
                <a href="search.php" class="block py-2 hover:text-primary transition-colors duration-300">
                    <i class="fas fa-search mr-2"></i> Search
                </a>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <nav class="max-w-7xl mx-auto px-4 py-4 text-gray-500" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li>
                <a href="index.php" class="flex items-center text-primary hover:text-primary-light transition">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li class="flex items-center space-x-2">
                <span class="text-gray-600">/</span>
                <span class="truncate text-gray-400">
                    <?= htmlspecialchars($komikData['comic'][0]['title'] ?? 'Anime Details') ?>
                </span>
            </li>
        </ol>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-6">
        <!-- Comic Info -->
        <?php if (!empty($komikData['comic'])): ?>
            <div class="bg-dark-400 rounded-xl shadow-xl overflow-hidden mb-8">
                <div class="relative mb-8 pb-32">
                    <!-- Backdrop Image with Gradient Overlay -->
                    <div class="w-full h-64 lg:h-80 overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-b from-dark-500/30 to-dark-500"></div>
                        <img src="<?= htmlspecialchars($komikData['comic'][0]['image']) ?>" 
                             alt="<?= htmlspecialchars($komikData['comic'][0]['title']) ?>" 
                             class="w-full h-full object-cover blur-sm opacity-40">
                    </div>
                    
                    <!-- Content overlay -->
                    <div class="absolute inset-0 flex flex-col md:flex-row p-6 items-center md:items-start">
                        <!-- Main Image (Poster) -->
                        <div class="w-48 h-64 flex-shrink-0 shadow-2xl rounded-lg overflow-hidden border-2 border-gray-800 transform md:translate-y-8">
                            <img src="<?= htmlspecialchars($komikData['comic'][0]['image']) ?>" 
                                 alt="<?= htmlspecialchars($komikData['comic'][0]['title']) ?>" 
                                 class="w-full h-full object-cover">
                        </div>
                        
                        <!-- Title and basic info -->
                        <div class="md:ml-8 mt-4 md:mt-2 text-center md:text-left">
                            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">
                                <?= htmlspecialchars($komikData['comic'][0]['title']) ?>
                            </h1>
                            
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-3">
                                <?php if (!empty($komikData['comic'][0]['status'])): ?>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-primary text-white">
                                    <i class="fas fa-circle text-xs mr-1"></i>
                                    <?= htmlspecialchars_decode($komikData['comic'][0]['status'], ENT_QUOTES) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Details Section -->
                <div class="p-6 md:pt-16 grid grid-cols-1 md:grid-cols-3 gap-8 mt-8">
                    <!-- Left Column: Additional Info -->
                    <div class="md:order-1">
                        <!-- Genres -->
                        <?php if (!empty($komikData['comic'][0]['genres'])): ?>
                        <div class="bg-dark-300 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-white mb-3 flex items-center">
                                <i class="fas fa-tags mr-2 text-primary"></i> Genres
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                $genres = explode(',', strip_tags($komikData['comic'][0]['genres']));
                                foreach ($genres as $genre):
                                    $genre = trim($genre);
                                    if (!empty($genre)):
                                ?>
                                <span class="bg-dark-200 text-gray-300 px-3 py-1 rounded-md text-sm">
                                    <?= htmlspecialchars($genre) ?>
                                </span>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Center Column: Description -->
                    <div class="md:col-span-2 md:order-2">
                        <!-- Description -->
                        <div class="bg-dark-300 rounded-lg p-5 mb-6">
                            <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-primary"></i> Synopsis
                            </h3>
                            <div class="text-gray-300 leading-relaxed">
                                <p><?= nl2br(htmlspecialchars($komikData['comic'][0]['description'])) ?></p>
                            </div>
                        </div>
                        
                        <!-- Episodes List -->
                        <?php if (!empty($komikData['episodes'])): ?>
                        <div class="bg-dark-300 rounded-lg p-5">
                            <h3 class="text-xl font-semibold text-white mb-4 flex items-center">
                                <i class="fas fa-list mr-2 text-primary"></i> Episodes
                            </h3>
                            <div class="episode-list max-h-96 overflow-y-auto pr-2">
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <?php 
                                    // Sort episodes in reverse order (newest first)
                                    $episodes = $komikData['episodes'];
                                    usort($episodes, function($a, $b) {
                                        return (float)$b['episode'] - (float)$a['episode'];
                                    });
                                    
                                    foreach ($episodes as $episode): 
                                    ?>
                                    <a href="nonton.php?url=<?= urlencode($episode['linksot']) ?>"
                                        class="bg-dark-400 hover:bg-dark-200 transition-colors duration-300 rounded-lg p-3 flex items-center justify-between group">
                                        <span class="flex items-center">
                                            <i class="fas fa-play-circle text-primary mr-2 group-hover:text-white transition-colors"></i>
                                            Episode <?= htmlspecialchars($episode['episode']) ?>
                                        </span>
                                        <i class="fas fa-chevron-right text-gray-500 group-hover:text-primary transition-colors"></i>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="bg-dark-300 rounded-lg p-5 text-center">
                            <div class="text-5xl text-gray-700 mb-4"><i class="fas fa-film"></i></div>
                            <p class="text-xl text-gray-500">No episodes available</p>
                            <p class="text-gray-600 mt-2">Episodes for this anime will be added soon</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-dark-400 rounded-lg p-8 text-center">
                <div class="text-6xl text-gray-700 mb-4"><i class="fas fa-satellite-dish"></i></div>
                <p class="text-2xl text-gray-500">No anime data available</p>
                <p class="text-gray-600 mt-2">Please check your connection or try again later</p>
                <a href="index.php" class="mt-6 inline-block bg-primary hover:bg-opacity-90 text-white px-6 py-3 rounded-lg transition-colors">
                    <i class="fas fa-home mr-2"></i> Return to Home
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Related Anime Suggestion - Could be added here -->

    <!-- Footer -->
    <footer class="mt-auto bg-dark-500 text-gray-400 py-8">
        <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="mb-4 md:mb-0">
            <p class="text-2xl font-bold text-gradient bg-gradient-to-r from-primary to-purple-500 text-transparent bg-clip-text">AnimeID</p>
            <p class="text-sm mt-2">&copy; 2024 AnimeID. All rights reserved.</p>
            </div>
            <div class="flex space-x-6">
            <a href="https://www.instagram.com/fa2yl/" class="text-gray-400 hover:text-primary transition-colors duration-300">
                <i class="fab fa-instagram text-xl"></i>
            </a>
            <a href="https://github.com/AnFariq" class="text-gray-400 hover:text-primary transition-colors duration-300">
                <i class="fab fa-github text-xl"></i>
            </a>
            </div>
        </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>