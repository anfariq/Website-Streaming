<?php
// Periksa apakah parameter pencarian ada
if (isset($_GET['query'])) {
  $searchQuery = $_GET['query'];

  // Validasi input pencarian (opsional)
  if (empty($searchQuery)) {
      die('Search query cannot be empty.');
  }

  // URL untuk melakukan pencarian
  $url = 'https://v9.animasu.cc/?s=' . urlencode($searchQuery);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html) {
        die('Failed to retrieve the page.');
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);
    $results = $xpath->query('//div[@class="bsx"]');
    $searchResults = [];

    foreach ($results as $result) {
        $title = '';
        $episode = '';
        $link = '#';
        $thumbnail = 'default-thumbnail.jpg';

        $titleNode = $xpath->query('.//div[@class="tt"]', $result)->item(0);
        if ($titleNode) {
            $title = trim($titleNode->nodeValue);
        }

        $episodeNode = $xpath->query('.//span[@class="epx"]', $result)->item(0);
        if ($episodeNode) {
            $episode = trim($episodeNode->nodeValue);
        }

        $linkNode = $xpath->query('.//a/@href', $result)->item(0);
        if ($linkNode) {
            $link = $linkNode->nodeValue;
            $parsedUrl = parse_url($link);
            $path = $parsedUrl['path'] ?? '';
            $processedLink = ltrim($path, '/');
        }

        $thumbNode = $xpath->query('.//img', $result)->item(0);
        $thumbnail = $thumbNode ? ($thumbNode->getAttribute('src') ?: $thumbNode->getAttribute('data-src')) : null;

        if (!empty($title) || $thumbnail !== 'default-thumbnail.jpg') {
            $searchResults[] = [
                'title' => $title,
                'link' => $processedLink ?? '',
                'thumbnail' => $thumbnail,
                'episode' => $episode
            ];
        }
    }
} else {
    $searchQuery = '';
    $searchResults = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Result - AnimeID</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0a0a0a;
            scrollbar-width: thin;
            scrollbar-color: #ff5e57 #0a0a0a;
        }
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a0a;
        }
        ::-webkit-scrollbar-thumb {
            background-color:rgb(0, 0, 0);
            border-radius: 20px;
        }
    </style>
</head>
<body class="text-gray-200">
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
                    Search
                </span>
            </li>
        </ol>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-10">
        <form action="search.php" method="get" class="w-full max-w-2xl mx-auto mb-8 flex items-center">
            <input type="text" name="query" placeholder="Search for anime..." value="<?= htmlspecialchars($searchQuery) ?>" class="w-full px-4 py-3 text-gray-800 rounded-l-lg focus:outline-none">
            <button class="bg-primary text-white px-6 py-3 rounded-r-lg hover:bg-orange-500 transition">Search</button>
        </form>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $result): ?>
                    <div class="bg-dark-400 rounded-lg shadow-lg overflow-hidden border border-gray-800">
                        <a href="anime.php?url=<?= urlencode($result['link']) ?>">
                            <img src="<?= $result['thumbnail'] ?>" alt="<?= $result['title'] ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="font-bold text-lg text-primary truncate"><?= $result['title'] ?></h3>
                                <p class="text-gray-400 text-sm">Episode <?= $result['episode'] ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center col-span-full text-gray-500">No results found for "<?= htmlspecialchars($searchQuery) ?>"</p>
            <?php endif; ?>
        </div>
    </main>
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
