<?php
// Periksa apakah parameter pencarian ada
if (isset($_GET['query'])) {
    $searchQuery = $_GET['query'];

    // Validasi input pencarian (opsional)
    if (empty($searchQuery)) {
        die('Search query cannot be empty.');
    }

    // URL untuk melakukan pencarian
    $url = 'https://komikindo.wtf/?s=' . urlencode($searchQuery);

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
        $title = $xpath->query('.//h4', $result)->item(0)->nodeValue;
        $link = $xpath->query('.//a/@href', $result)->item(0)->nodeValue;
        $thumbnail = $xpath->query('.//img/@src', $result)->item(0)->nodeValue;

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

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

    <!-- Breadcrumb -->
    <nav class="container mx-auto p-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="index"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3 h-3 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path
                            d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-3 h-3 text-gray-400 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <a href="#" class="text-sm font-medium text-gray-700 dark:text-gray-400">Search</a>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Search Form -->
    <div class="container mx-auto p-4 flex justify-center">
        <form action="search" method="get" class="w-full max-w-lg flex items-center space-x-2">
            <input type="text" name="query" placeholder="Search..." class="text-gray-800 input input-bordered w-full py-3 px-4 rounded-lg shadow-lg focus:ring-2 focus:ring-blue-600">
            <button type="submit" class="btn bg-blue-500 text-white py-3 px-6 rounded-lg shadow-lg hover:bg-blue-600">Search</button>
        </form>
    </div>

    <h1 class="text-2xl text-center mt-6">Search Results for: <?= htmlspecialchars($searchQuery) ?></h1>

    <!-- Search Results -->
    <div class="container mx-auto p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if (!empty($searchResults)): ?>
            <?php foreach ($searchResults as $result): ?>
                <a href="comic?url=<?= urlencode($result['link']) ?>" class="flex flex-col bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:bg-gray-100 dark:hover:bg-gray-700">
                    <img src="<?= htmlspecialchars($result['thumbnail']) ?>" alt="Thumbnail" class="w-full h-48 object-cover rounded-t-lg">
                    <div class="flex flex-col p-4">
                        <h5 class="text-xl font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($result['title']) ?></h5>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-gray-700 dark:text-gray-400">No results found for your search.</p>
        <?php endif; ?>
    </div>

    <!--footer-->
    <footer class="bg-white text-center rounded-lg shadow mb-20 m-4 dark:bg-gray-800">
        <div class="w-full mx-auto max-w-screen-xl p-4">
        <span class="text-sm text-gray-500 sm:text-center dark:text-gray-400">© 2024 <a href="https://garcia.my.id/" class="hover:underline">ComicID™</a>. All Rights Reserved.
        </span>
        </div>
    </footer>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 z-50 w-full h-16 bg-white border-t border-gray-200 dark:bg-gray-700 dark:border-gray-600">
        <div class="grid h-full max-w-lg grid-cols-4 mx-auto font-medium">
            <a href="index" type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                </svg>
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Home</span>
            </a>
            <a href="release" type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 3a2 2 0 0 0-2 2v10a3 3 0 0 0 3 3h14a1 1 0 0 0 1-1V5a2 2 0 0 0-2-2H2Zm14 2v10H3a1 1 0 0 1-1-1V5h14ZM4 6h5a1 1 0 1 1 0 2H4a1 1 0 0 1 0-2Zm0 4h5a1 1 0 1 1 0 2H4a1 1 0 0 1 0-2Zm7-4h3a1 1 0 1 1 0 2h-3a1 1 0 1 1 0-2Zm0 4h3a1 1 0 1 1 0 2h-3a1 1 0 1 1 0-2Z" />
                </svg>    
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Release</span>
            </a>
            <a href="search" type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="2" />
                    <line x1="13" y1="13" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>                
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Search</span>
            </a>
            <a href="profile" type="button" class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-800 group">
                <svg class="w-5 h-5 mb-2 text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                </svg>
                <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-500">Profile</span>
            </a>
        </div>
    </div>

</body>

</html>
