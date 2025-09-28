<?php
// Periksa apakah parameter pencarian ada
if (isset($_GET['query'])) {
    $searchQuery = $_GET['query'];

    // Validasi input pencarian (opsional)
    if (empty($searchQuery)) {
        die('Search query cannot be empty.');
    }

    // URL untuk melakukan pencarian
    $url = 'https://komikindo3.com/?s=' . urlencode($searchQuery);

    // Inisialisasi cURL untuk mengambil HTML
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $html = curl_exec($ch);
    curl_close($ch);

    // Pastikan HTML berhasil diambil
    if (!$html) {
        die('Failed to retrieve the page.');
    }

    // Parsing HTML menggunakan DOMDocument dan XPath
    $dom = new DOMDocument();
    @$dom->loadHTML($html);

    $xpath = new DOMXPath($dom);

    // Ambil semua elemen yang berisi informasi komik
    $results = $xpath->query('//div[@class="animepost"]');

    // Array untuk menampung hasil pencarian
    $searchResults = [];

    // Proses hasil pencarian
    foreach ($results as $result) {
        $titleNode = $xpath->query('.//h4', $result)->item(0);
        $linkNode = $xpath->query('.//a/@href', $result)->item(0);
        $thumbnailNode = $xpath->query('.//img/@src', $result)->item(0);

        $title = $titleNode ? $titleNode->nodeValue : 'No title';
        $link = $linkNode ? $linkNode->nodeValue : '#';
        $thumbnail = $thumbnailNode ? $thumbnailNode->nodeValue : 'img/No Image.jpg';


        // Olah link untuk mendapatkan bagian path tertentu
        $parsedUrl = parse_url($link); // Memecah URL menjadi komponen
        $path = $parsedUrl['path'] ?? ''; // Ambil bagian path dari URL
        $processedLink = ltrim($path, '/'); // Hapus "/" di awal path

        // Simpan data ke dalam array
        $searchResults[] = [
            'title' => trim($title),
            'link' => $processedLink,
            'thumbnail' => $thumbnail
        ];
    }

} else {
    $searchQuery = ''; // Set default value for searchQuery if not set
    $searchResults = []; // Initialize empty results
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ComicID - Search</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flowbite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</head>

<body class="bg-gray-900 text-gray-100">

    <!-- Sticky Header Banner -->
    <div class="sticky top-0 z-50 bg-pink-600 text-white py-2 px-4 flex justify-between items-center">
        <span class="text-sm font-medium">Explore the latest comics! Check out <a href="https://www.instagram.com/fa2yl/" class="underline">our promotions</a>.</span>
        <button class="text-white hover:text-gray-300" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>

    <!-- Breadcrumb Navigation -->
    <nav class="bg-pink-700 py-3 px-4 rounded-b-lg shadow-lg">
        <ol class="list-reset flex text-sm">
            <li><a href="index.php" class="text-white hover:underline">Home</a></li>
            <li class="mx-2">&gt;</li>
            <li class="text-pink-200">Search</li>
        </ol>
    </nav>

    <!-- Search Form -->
    <div class="container mx-auto p-4 flex justify-center">
        <form action="search.php" method="get" class="w-full max-w-lg flex items-center space-x-2">
            <input type="text" name="query" placeholder="Search..." class="text-pink-800 input input-bordered w-full py-3 px-4 rounded-lg shadow-lg focus:ring-2 focus:ring-pink-600">
            <button type="submit" class="btn bg-pink-500 text-white py-3 px-6 rounded-lg shadow-lg hover:bg-pink-600">Search</button>
        </form>
    </div>

    <h1 class="text-2xl text-center font-bold text-pink-400 mt-6">Search Results for : <?= htmlspecialchars($searchQuery) ?></h1>

    <!-- Search Results -->
    <div class="py-12 px-4">
        <div class="container mx-auto p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $result): ?>
                    <div class="bg-gray-800 rounded-lg shadow-md hover:shadow-pink-500 transition p-4">
                        <a href="comic.php?url=<?= urlencode($result['link']) ?>" class="flex flex-col">
                            <img src="<?= htmlspecialchars($result['thumbnail']) ?>" alt="Thumbnail" class="w-full h-48 object-cover rounded-lg mb-4">
                            <h3 class="text-lg font-semibold text-pink-300"><?= htmlspecialchars($result['title']) ?></h3>
                            <p class="text-gray-400 text-sm">Chapter Available</p>
                            <a href="comic.php?url=<?= urlencode($result['link']) ?>" class="mt-2 block text-pink-500 hover:underline">Read Now</a>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-400">No results found for your search.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 py-6 px-4 text-center">
        <p class="text-gray-500 text-sm">&copy; 2024 ComicID. All rights reserved.</p>
    </footer>

   <!-- Bottom Navigation -->
   <div class="fixed bottom-0 left-0 right-0 bg-pink-700 shadow-lg">
        <div class="flex justify-around py-3">
            <a href="index.php" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m11 6h-4m0 0h4m-4 0v4m0-4l-4-4m0 16V7"></path></svg>
                <span class="text-sm">Home</span>
            </a>
            <a href="release.php" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l-4-4m0 0l4-4m-4 4h16"></path></svg>
                <span class="text-sm">Releases</span>
            </a>
            <a href="search.php" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m4 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm">Search</span>
            </a>
            <a href="../index.php" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.24 2a1 1 0 00-1 1v2.045a1 1 0 102 0V3a1 1 0 00-1-1zm9.52 0a1 1 0 00-1 1v2.045a1 1 0 102 0V3a1 1 0 00-1-1zM12 4a8 8 0 100 16 8 8 0 000-16zm0 14.5A6.5 6.5 0 1118.5 12 6.508 6.508 0 0112 18.5z"/>
                </svg>
                <span class="text-sm">Anime</span>
            </a>
        </div>
    </div>

</body>

</html>
