<?php

if (isset($_GET['url'])) {
    // Mendapatkan path dari parameter URL
    $path = $_GET['url'];

    // Domain dasar
    $config = include 'url.php';
    $baseDomain = $url; // Jika menggunakan variabel global

    // Gabungkan domain dasar dengan path
    $url = rtrim($baseDomain, characters: '/') . '/' . ltrim($path, '/');
    
    // Validasi URL untuk keamanan
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        header("Location: url_error.html");
        exit;
    }
} else {
    header("Location: cari_apa_hayo.html");
    exit;
}

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bypass SSL certificate verification

// Execute cURL request
$html = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo "<script>
        alert('Server error');
        window.location.href = 'index.php';
    </script>";
    exit;
}


// Check the HTTP response code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode !== 200) {
    echo "<script>
        alert('Server error');
        window.location.href = 'index.php';
    </script>";
    exit;
}

// Close cURL
curl_close($ch);

// Create a new DOMDocument instance
$dom = new DOMDocument();
libxml_use_internal_errors(true); // Suppress warnings for invalid HTML
$dom->loadHTML($html);
libxml_clear_errors();

// Create a new DOMXPath instance
$xpath = new DOMXPath($dom);

// Extract title
$title = $xpath->evaluate("string(//h1[@itemprop='name'])");

// Extract description
$description = $xpath->evaluate("string(//div[@class='detail']//p)");

// Extract image (thumbnail)
$image = $xpath->evaluate("string(//div[@class='tb']//meta[@itemprop='url']/@content)");

// Add base domain to image URL if it's a relative path
if (!empty($image) && strpos($image, 'http') === false) {
    $image = rtrim($baseDomain, '/') . '/' . ltrim($image, '/');
}

// Extract video iframe source
$iframeNode = $xpath->query("//div[contains(@class, 'player-embed')]/iframe")->item(0);
$video_src = $iframeNode ? $iframeNode->getAttribute('src') : null;

if ($iframeNode) {
    $iframeSrc = $iframeNode->getAttribute('src');
    if (!empty($iframeSrc) && strpos($iframeSrc, 'http') === false) {
        $video_src = rtrim($baseDomain, '/') . '/' . ltrim($iframeSrc, '/');
    } else {
        $video_src = $iframeSrc;
    }
}

// Initialize array for navigation links
$navigation = [
    'prev' => null,
    'chapterList' => null,
    'next' => null,
];

// Extract Previous Chapter Link
$prevLink = $xpath->evaluate("string(//div[@class='naveps']//div[contains(@class, 'nvs')][1]/a/@href)");
if (!empty($prevLink)) {
    // Olah link untuk mendapatkan bagian path tertentu
    $parsedUrl = parse_url($prevLink); // Memecah URL menjadi komponen
    $path = $parsedUrl['path'] ?? ''; // Ambil bagian path dari URL
    $processedLinkprev = ltrim($path, '/'); // Hapus "/" di awal path
    $navigation['prev'] = $processedLinkprev; // Simpan hasil proses link
}

// Extract Chapter List Link (if it exists)
$chapterListLink = $xpath->evaluate("string(//div[@class='naveps']//div[@class='nvs nvsc']//a[contains(., 'Informasi Anime')]/@href)");
if (!empty($chapterListLink)) {
    // Process the link to get the path part
    $parsedUrl = parse_url($chapterListLink); // Break the URL into components
    $path = $parsedUrl['path'] ?? ''; // Get the path part of the URL
    // Process the link to remove the leading slash
    $processedLinkback = ltrim($path, '/'); // Remove the "/" at the beginning of the path
    // Store the processed link in the navigation array
    $navigation['chapterList'] = $processedLinkback;
}

// Extract Next Chapter Link
$nextLink = $xpath->evaluate("string(//div[@class='naveps']//div[contains(@class, 'nvs')][last()]/a/@href)");

if (!empty($nextLink)) {
    // Process the link to get the path part
    $parsedUrl = parse_url($nextLink); // Break the URL into components
    $path = $parsedUrl['path'] ?? ''; // Get the path part of the URL
    $processedLinknext = ltrim($path, '/'); // Remove the "/" at the beginning of the path

    // Save the processed link in the navigation array
    $navigation['next'] = $processedLinknext;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($title) ? htmlspecialchars($title) : 'AnimeID' ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
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
        
        .player-container {
            aspect-ratio: 16/9;
        }
        
        .btn-shine {
            position: relative;
            overflow: hidden;
        }
        
        .btn-shine::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            bottom: -50%;
            left: -50%;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.13) 50%, rgba(255, 255, 255, 0));
            transform: rotateZ(60deg) translate(-5em, 7.5em);
            animation: shine 3s infinite;
        }
        
        @keyframes shine {
            0% {
                transform: rotateZ(60deg) translate(-5em, 7.5em);
            }
            100% {
                transform: rotateZ(60deg) translate(0, -7.5em);
            }
        }
        
        .nav-button {
            transition: all 0.3s ease;
        }
        
        .nav-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 94, 87, 0.3);
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
        </div>
    </header>


    <!-- Main Content -->    
    <main class="container mx-auto px-4 py-8">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-1 mb-6 text-sm text-gray-400" aria-label="Breadcrumb">
            <a href="index.php" class="flex items-center hover:text-accent transition-colors">
                <i class="fas fa-home mr-1"></i>
                <span>Home</span>
            </a>
            <span class="mx-2">/</span>
            <a href="anime.php?url=<?= urlencode($navigation['chapterList']) ?>" class="hover:text-accent transition-colors">Anime</a>
            <span class="mx-2">/</span>
            <span class="text-accent truncate"><?= !empty($title) ? htmlspecialchars($title) : 'Nonton Anime' ?></span>
        </nav>

        <!-- Title Section -->
        <div class="text-center mb-8">
        <h1 class="text-2xl md:text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-yellow-400 animate-pulse-slow">
    <?= !empty($title) ? htmlspecialchars($title) : 'Nonton Anime' ?>
</h1>
            <?php if (!empty($description)): ?>
                <p class="mt-3 text-gray-400 max-w-3xl mx-auto"><?= htmlspecialchars($description) ?></p>
            <?php endif; ?>
        </div>

        <!-- Video Section -->
        <div class="mb-10 relative">
            <!-- Decorative elements -->
            <div class="absolute -top-2 -left-2 w-10 h-10 border-t-2 border-l-2 border-accent"></div>
            <div class="absolute -top-2 -right-2 w-10 h-10 border-t-2 border-r-2 border-accent"></div>
            <div class="absolute -bottom-2 -left-2 w-10 h-10 border-b-2 border-l-2 border-accent"></div>
            <div class="absolute -bottom-2 -right-2 w-10 h-10 border-b-2 border-r-2 border-accent"></div>
            
            <!-- Player -->
            <div class="relative bg-black rounded-lg overflow-hidden shadow-2xl border border-gray-800">
                <div class="player-container w-full">
                    <iframe src="<?= htmlspecialchars($video_src) ?>"
                            frameborder="0"
                            marginwidth="0"
                            marginheight="0"
                            scrolling="no"
                            width="100%"
                            height="100%"
                            allowfullscreen="true"
                            class="w-full h-full">
                    </iframe>
                </div>
                
                <!-- Player controls overlay (for decoration only) -->
                <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-black to-transparent opacity-80 pointer-events-none"></div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-10">
            <!-- Previous Episode -->
            <?php if (!empty($navigation['prev'])): ?>
                <a href="nonton.php?url=<?= urlencode($navigation['prev']) ?>" rel="prev" 
                   class="nav-button flex items-center justify-center text-center py-3 px-4 bg-gray-900 hover:bg-gray-800 border border-gray-700 rounded-lg text-white relative overflow-hidden">
                    <span class="flex items-center z-10">
                        <i class="fas fa-chevron-left mr-2"></i>
                        <span>Episode Sebelumnya</span>
                    </span>
                </a>
            <?php else: ?>
                <div class="py-3 px-4 bg-gray-900 border border-gray-800 rounded-lg text-gray-500 text-center opacity-50">
                    <i class="fas fa-chevron-left mr-2"></i>
                    <span>Episode Sebelumnya</span>
                </div>
            <?php endif; ?>

            <!-- Chapter List -->
            <?php if (!empty($navigation['chapterList'])): ?>
                <a href="anime.php?url=<?= urlencode($navigation['chapterList']) ?>" 
                   class="nav-button flex items-center justify-center text-center py-3 px-4 bg-accent hover:bg-accent-hover border border-gray-700 rounded-lg text-white relative overflow-hidden btn-shine">
                    <span class="flex items-center z-10">
                        <i class="fas fa-list mr-2"></i>
                        <span>Daftar Episode</span>
                    </span>
                </a>
            <?php else: ?>
                <div class="py-3 px-4 bg-gray-900 border border-gray-800 rounded-lg text-gray-500 text-center opacity-50">
                    <i class="fas fa-list mr-2"></i>
                    <span>Daftar Episode</span>
                </div>
            <?php endif; ?>

            <!-- Next Episode -->
            <?php if (!empty($navigation['next'])): ?>
                <a href="nonton?url=<?= urlencode($navigation['next']) ?>" id="next-chapter" rel="next" 
                   class="nav-button flex items-center justify-center text-center py-3 px-4 bg-gray-900 hover:bg-gray-800 border border-gray-700 rounded-lg text-white relative overflow-hidden">
                    <span class="flex items-center z-10">
                        <span>Episode Selanjutnya</span>
                        <i class="fas fa-chevron-right ml-2"></i>
                    </span>
                </a>
            <?php else: ?>
                <div class="py-3 px-4 bg-gray-900 border border-gray-800 rounded-lg text-gray-500 text-center opacity-50">
                    <span>Episode Selanjutnya</span>
                    <i class="fas fa-chevron-right ml-2"></i>
                </div>
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

    <!-- Script -->
    <script>
        // Check if there are any empty navigation links
        function checkNavigation() {
            var prevLink = "<?= $navigation['prev'] ?>";
            var chapterListLink = "<?= $navigation['chapterList'] ?>";
            var nextLink = "<?= $navigation['next'] ?>";

            // If there are no navigation links
            if (!prevLink && !chapterListLink && !nextLink) {
                // Show the alert
                alert('Tunggu Update sayang:)');
                
                // Immediately redirect to the 'index' page after the alert is closed (no .php extension)
                window.location.href = "index";
            }
        }

        // Call checkNavigation on page load
        window.onload = checkNavigation;
        
        // Add animation to buttons on hover
        document.querySelectorAll('.nav-button').forEach(button => {
            button.addEventListener('mouseover', function() {
                this.classList.add('pulse');
            });
            
            button.addEventListener('mouseout', function() {
                this.classList.remove('pulse');
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>
</html>