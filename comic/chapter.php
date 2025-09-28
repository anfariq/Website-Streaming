<?php
if (isset($_GET['url'])) {
    $chapterUrl = $_GET['url'];

    // Mendapatkan path dari parameter URL
    $path = $_GET['url'];

    // Domain dasar
    $baseDomain = 'https://komikindo.pw';

    // Gabungkan domain dasar dengan path
    $url = rtrim($baseDomain, '/') . '/' . ltrim($path, '/');

    // Validasi URL untuk keamanan
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        die('Invalid URL');
    }
} else {
    die('URL parameter is missing.');
}

// Fungsi untuk scraping gambar dari URL yang diberikan
function scrapeChapterImages($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout untuk keamanan
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Pastikan HTML berhasil diambil dan status HTTP adalah 200
    if (!$html || $httpCode !== 200) {
        return [];
    }

    // Gunakan DOMDocument untuk mem-parsing HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Gunakan DOMXPath untuk mencari elemen gambar
    $xpath = new DOMXPath($dom);
    $images = $xpath->query('//div[@id="chimg-auh"]/img');

    $imageUrls = [];
    foreach ($images as $image) {
        $src = $image->getAttribute('src');
        if (!empty($src)) {
            $imageUrls[] = $src;
        }
    }

    return $imageUrls;
}

// Fungsi untuk mengambil link previous dan next chapter
// Fungsi untuk mengambil link previous, next, dan daftar chapter
function scrapeChapterNavigation($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$html || $httpCode !== 200) {
        return null;
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new DOMXPath($dom);

    // Get previous link
    $prevLink = $xpath->query('//a[@rel="prev"]')->item(0);
    $prevUrl = $prevLink ? parse_url($prevLink->getAttribute('href'))['path'] ?? null : null;
    $prevUrl = $prevUrl ? ltrim($prevUrl, '/') : null;

    // Get next link
    $nextLink = $xpath->query('//a[@rel="next"]')->item(0);
    $nextUrl = $nextLink ? parse_url($nextLink->getAttribute('href'))['path'] ?? null : null;
    $nextUrl = $nextUrl ? ltrim($nextUrl, '/') : null;

    // Get chapter list link - corrected selector
    $chapterListLink = $xpath->query('//div[contains(@class, "daftarch")]/parent::a')->item(0);
    if (!$chapterListLink) {
        // Alternative selector if the above doesn't work
        $chapterListLink = $xpath->query('//a[.//i[contains(@class, "fa-th")]]')->item(0);
    }
    
    $chapterListUrl = $chapterListLink ? parse_url($chapterListLink->getAttribute('href'))['path'] ?? null : null;
    $chapterListUrl = $chapterListUrl ? ltrim($chapterListUrl, '/') : null;

    return [
        'prev' => $prevUrl,
        'next' => $nextUrl,
        'chapterList' => $chapterListUrl,
    ];
}

$baseDomain = 'https://komikindo3.com/';

// Panggil fungsi scraping gambar dan navigasi
$images = scrapeChapterImages($baseDomain . $chapterUrl);
$navigation = scrapeChapterNavigation($baseDomain . $chapterUrl);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter Images</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .chapter-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .chapter-image {
            width: 100%;
            max-width: 800px; /* Limit the width */
            height: auto;
            object-fit: contain;
        }
        .container {
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }
        .icol {
            display: inline-block;
            background-color: #38b2ac;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }
    </style>
    <script>
        // Retry image loading logic
        function loadImageWithRetry(imageElement, src, retries = 5) {
            let attempt = 0;
            imageElement.src = src;

            imageElement.onload = function() {
                console.log("Image loaded successfully");
            }

            imageElement.onerror = function() {
                if (attempt < retries) {
                    attempt++;
                    console.log(`Retrying image load (${attempt}/${retries})`);
                    imageElement.src = ''; // Clear the source temporarily
                    imageElement.src = src; // Reassign the source to retry
                } else {
                    console.log("Failed to load image after multiple attempts");
                }
            };
        }
    </script>
    <script>
        function autoRefresh() {
            setTimeout(() => {
                window.location.reload();
            }, 30000); // Refresh setiap 30 detik
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200" onload="autoRefresh()">
<div class="container px-4 py-6">
        <h1 class="text-4xl font-bold text-center my-6 text-pink-600 dark:text-pink-300">ComicID</h1>

        <?php if (!empty($images)): ?>
            <div class="chapter-container">
                <?php foreach ($images as $img): ?>
                    <img 
                        src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                        alt="Chapter Image" 
                        class="chapter-image border-1 border-pink-300 shadow-lg"
                        onload="console.log('Image loaded successfully');"
                        onerror="loadImageWithRetry(this, '<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>');">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-600 dark:text-gray-400">No chapter images found or failed to load.</p>
        <?php endif; ?>

<!-- Navigation for Previous, Current, and Next Chapter -->
<div class="mt-8">
    <div class="flex flex-wrap justify-between items-center gap-4 md:gap-8">
        <!-- Previous Chapter Link -->
        <?php if (!empty($navigation['prev'])): ?>
            <a href="chapter.php?url=<?= urlencode($navigation['prev']) ?>" 
               rel="prev" 
               class="flex items-center text-pink-600 dark:text-pink-300 hover:underline md:text-lg">
                <i class="fa fa-chevron-left mr-2"></i>
                <span>« Previous</span>
            </a>
        <?php endif; ?>

        <!-- Chapter List Link -->
        <?php if (!empty($navigation['chapterList'])): ?>
            <a href="comic.php?url=<?= urlencode($navigation['chapterList']) ?>" 
            rel="fa fa-th"
               class="text-white bg-pink-600 dark:bg-pink-700 hover:bg-pink-500 dark:hover:bg-pink-600 px-4 py-2 rounded-md shadow-md md:px-6 md:py-3 md:text-lg">
                Menu Chapter
            </a>
        <?php endif; ?>

        <!-- Next Chapter Link -->
        <?php if (!empty($navigation['next'])): ?>
            <a href="chapter.php?url=<?= urlencode($navigation['next']) ?>" 
               rel="next" 
               class="flex items-center text-pink-600 dark:text-pink-300 hover:underline md:text-lg">
                <span>Next»</span>
                <i class="fa fa-chevron-right ml-2"></i>
            </a>
        <?php endif; ?>
    </div>
</div>

    </div>
</body>
</html>