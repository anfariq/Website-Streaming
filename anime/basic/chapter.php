<?php
if (isset($_GET['url'])) {
    $chapterUrl = $_GET['url'];

    // Mendapatkan path dari parameter URL
    $path = $_GET['url'];

    // Domain dasar
    $baseDomain = 'https://komikindo.wtf';

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout untuk keamanan
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Pastikan HTML berhasil diambil dan status HTTP adalah 200
    if (!$html || $httpCode !== 200) {
        return null;
    }

    // Gunakan DOMDocument untuk mem-parsing HTML
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    // Gunakan DOMXPath untuk mencari elemen navigasi
    $xpath = new DOMXPath($dom);

    // Proses prevLink
    $prevLink = $xpath->query('//a[@rel="prev"]')->item(0);
    $prevUrl = $prevLink ? parse_url($prevLink->getAttribute('href'))['path'] ?? null : null;
    $prevUrl = $prevUrl ? ltrim($prevUrl, '/') : null;

    // Proses nextLink
    $nextLink = $xpath->query('//a[@rel="next"]')->item(0);
    $nextUrl = $nextLink ? parse_url($nextLink->getAttribute('href'))['path'] ?? null : null;
    $nextUrl = $nextUrl ? ltrim($nextUrl, '/') : null;

    // Proses chapterListLink
    $chapterListLink = $xpath->query('//a[contains(@href, "https://komikindo.wtf/komik/")]')->item(0); // Cari URL daftar chapter
    $chapterListUrl = $chapterListLink ? parse_url($chapterListLink->getAttribute('href'))['path'] ?? null : null;
    $chapterListUrl = $chapterListUrl ? ltrim($chapterListUrl, '/') : null;

    return [
        'prev' => $prevUrl,
        'next' => $nextUrl,
        'chapterList' => $chapterListUrl,
    ];

}

$baseDomain = 'https://komikindo.wtf/';

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
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-semibold text-center my-4 text-white">ComicID</h1>

        <?php if (!empty($images)): ?>
            <div class="chapter-container">
                <?php foreach ($images as $img): ?>
                    <img 
                        src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" 
                        alt="Chapter Image" 
                        class="chapter-image"
                        onload="console.log('Image loaded successfully');"
                        onerror="loadImageWithRetry(this, '<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>');">
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-600">No chapter images found or failed to load.</p>
        <?php endif; ?>

        <!-- Navigation for Previous, Current, and Next Chapter -->
        <div class="navig">
            <div class="nextprev mt-4 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <!-- Previous Chapter Link -->
                <?php if (!empty($navigation['prev'])): ?>
                    <a href="chapter.php?url=<?= urlencode($navigation['prev']) ?>" rel="prev" class="text-blue-500 hover:underline flex items-center space-x-2">
                        <i class="fa fa-chevron-left"></i>
                        <span>« Chapter Sebelumnya</span>
                    </a>
                <?php endif; ?>

                <!-- Chapter List Link -->
                <?php if (!empty($navigation['chapterList'])): ?>
                    <a href="comic.php?url=<?= urlencode($navigation['chapterList']) ?>" class="icol daftarch text-blue-500 hover:underline flex items-center space-x-2">
                        <i class="fa fa-th"></i>
                        <span>Daftar Chapter</span>
                    </a>
                <?php endif; ?>

                <!-- Next Chapter Link -->
                <?php if (!empty($navigation['next'])): ?>
                    <a href="chapter.php?url=<?= urlencode($navigation['next']) ?>" rel="next" class="text-blue-500 hover:underline flex items-center space-x-2">
                        <span>Chapter Selanjutnya »</span>
                        <i class="fa fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
