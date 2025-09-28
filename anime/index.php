<?php
include ('url.php');

function get_web_content($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.5',
        'Connection: keep-alive'
    ]);

    $data = curl_exec($ch);

    if (curl_errno($ch)) {
      echo "<script>
      alert('Server error');
      window.location.href = 'per.html';
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

    foreach ($html->find("div.bs") as $menu) {
        // Cari setiap elemen "list-anime" di dalam "div.bsx"
        foreach ($menu->find("div.bsx") as $anime) {
            // Ambil judul (title) dari div dengan class "tt"
            $title = $anime->find("div.tt", 0) ? trim($anime->find("div.tt", 0)->plaintext) : null;
    
            // Ambil link (href) dari parent <a>
            $link = $anime->find("a", 0)->href ?? null;
    
            // Ambil episode (eps) dari div dengan class "bt"
            $eps = $anime->find("span.epx", 0) ? trim($anime->find("span.epx", 0)->plaintext) : null;
    
            // Ambil image (src) dari img dengan class "lazy"
            $image = $anime->find('img', 0) ? $anime->find('img', 0)->src : null;
    
            // Olah link untuk mendapatkan bagian path tertentu
            $parsedUrl = parse_url($link); // Memecah URL menjadi komponen
            $path = $parsedUrl['path'] ?? ''; // Ambil bagian path dari URL
            $processedLink = ltrim($path, '/'); // Hapus "/" di awal path
    
            // Simpan data ke dalam array
            $results[] = [
                'title' => $title,
                'link' => $processedLink,
                'chapter' => $eps,
                'image' => $image,
            ];
        }
    }
    
    return $results;
}

// URL target
$data = scrape_komikindo($url);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AnimeID - Watch Anime Online</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
    </ol>
  </nav>

  <!-- Hero Section -->
  <div class="bg-gradient-to-b from-dark-400 to-black py-12 mb-8">
    <div class="max-w-7xl mx-auto px-4">
      <div class="text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Discover Amazing Anime</h1>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto">Your ultimate destination for the latest episodes and trending series</p>
      </div>
    </div>
  </div>

  <!-- Dynamic Card Section -->
  <div class="max-w-7xl mx-auto px-4 py-6">
    <h2 class="text-2xl md:text-3xl font-bold mb-8 text-white relative inline-block after:content-[''] after:absolute after:left-0 after:-bottom-2 after:h-1 after:w-full after:bg-gradient-to-r after:from-primary after:to-purple-500">Latest Updates</h2>
    
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-5">
      <?php if (!empty($data)): ?>
        <?php foreach ($data as $item): ?>
          <div class="bg-dark-400 rounded-lg shadow-lg overflow-hidden group hover:ring-2 hover:ring-primary transition duration-300 transform hover:-translate-y-1">
            <a href="anime.php?url=<?= urlencode($item['link']) ?>" class="block">
              <div class="relative overflow-hidden">
                <!-- Image Section with improved ratio -->
                <div class="aspect-w-3 aspect-h-4">
                  <img src="<?= $item['image'] ?>" alt="<?= $item['title'] ?>" class="w-full h-64 object-cover object-center transition-transform duration-500 group-hover:scale-110">
                </div>
                <!-- Gradient overlay for better text visibility -->
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent opacity-80"></div>
                <!-- Episode Badge -->
                <span class="absolute top-3 right-3 bg-primary text-white px-3 py-1 text-sm font-bold rounded-full shadow-lg">
                  EP <?= $item['chapter'] ?>
                </span>
              </div>
              <!-- Title Section -->
              <div class="p-4">
                <h3 class="font-bold text-lg text-white line-clamp-2 group-hover:text-primary transition-colors duration-300">
                  <?= $item['title'] ?>
                </h3>
              </div>
            </a>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-span-full text-center py-12">
          <div class="text-5xl text-gray-700 mb-4"><i class="fas fa-satellite-dish"></i></div>
          <p class="text-xl text-gray-500">No anime found</p>
          <p class="text-gray-600 mt-2">Please check your connection or try again later</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

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