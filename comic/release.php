<?php
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
        echo 'cURL Error: ' . curl_error($ch);
        return false;
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

    foreach ($html->find("div.animepost") as $animePost) {
        $title = $animePost->find("div.tt h4", 0) ? trim($animePost->find("div.tt h4", 0)->plaintext) : null;
        $link = $animePost->find("a", 0) ? $animePost->find("a", 0)->href : null;
        $chapter = $animePost->find("div.lsch a", 0) ? trim($animePost->find("div.lsch a", 0)->plaintext) : null;
        $chapterLink = $animePost->find("div.lsch a", 0) ? $animePost->find("div.lsch a", 0)->href : null;
        $image = $animePost->find("img", 0) ? $animePost->find("img", 0)->src : null;
        $updated = $animePost->find("span.datech", 0) ? trim($animePost->find("span.datech", 0)->plaintext) : null;

        // Olah link untuk mendapatkan bagian path tertentu
        $parsedUrl = parse_url($link); // Memecah URL menjadi komponen
        $path = $parsedUrl['path'] ?? ''; // Ambil bagian path dari URL
        $processedLink = ltrim($path, '/'); // Hapus "/" di awal path

        //olah chapter link untuk mendapatkanbagian tertentu
        $chapterurl = parse_url($chapterLink); // Memecah URL menjadi komponen
        $path1 = $chapterurl['path'] ?? ''; // Ambil bagian path dari URL
        $processedLinkChapter = ltrim($path1, '/'); // Hapus "/" di awal path

        $results[] = [
            'title' => $title,
            'link' => $processedLink,
            'chapter' => $chapter,
            'chapterLink' => $processedLinkChapter,
            'image' => $image,
            'updated' => $updated,
        ];
    }

    return $results;
}

// URL target
$url = "https://komikindo3.com/komik-terbaru/";
$data = scrape_komikindo($url);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ComicID</title>
    <!--cdn tailwind css-->
    <script src="https://cdn.tailwindcss.com"></script>
    <!--flowbite-->
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
            <li class="text-pink-200">Release</li>
        </ol>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto mt-8">
        <!-- Separator Line -->
        <hr class="border-pink-500 mb-6">

        <!-- Dynamic Card Section -->
        <div class="py-12 px-4">
            <h2 class="text-2xl font-bold text-pink-400 mb-6">Latest Releases</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php if (!empty($data)) { foreach ($data as $item) { ?>
                <div class="bg-gray-800 rounded-lg shadow-md hover:shadow-pink-500 transition p-4">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-lg font-semibold text-pink-300"> <?php echo $item['title']; ?> </h3>
                    <p class="text-gray-400 text-sm">Chapter <?php echo $item['chapter']; ?></p>
                    <a href="comic.php?url=<?php echo $item['link']; ?>" class="mt-2 block text-pink-500 hover:underline">Read Now</a>
                </div>
                <?php } } else { ?>
                <p class="text-gray-400">No data available</p>
                <?php } ?>
            </div>
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
