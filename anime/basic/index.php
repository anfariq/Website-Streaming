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
        $link = $animePost->find("a.tip", 0) ? $animePost->find("a.tip", 0)->href : null;
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
$url = "https://komikindo.wtf/manhwa";
$data = scrape_komikindo($url);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ComicID</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">



<div id="sticky-banner" tabindex="-1" class="fixed top-0 start-0 z-50 flex justify-between w-full p-4 border-b border-gray-200 bg-gray-50 dark:bg-gray-700 dark:border-gray-600">
    <div class="flex items-center mx-auto">
        <p class="flex items-center text-sm font-normal text-gray-500 dark:text-gray-400">
            <span class="inline-flex p-1 me-3 bg-gray-200 rounded-full dark:bg-gray-600 w-6 h-6 items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 19">
                    <path d="M15 1.943v12.114a1 1 0 0 1-1.581.814L8 11V5l5.419-3.871A1 1 0 0 1 15 1.943ZM7 4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2v5a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V4ZM4 17v-5h1v5H4ZM16 5.183v5.634a2.984 2.984 0 0 0 0-5.634Z"/>
                </svg>
                <span class="sr-only">Light bulb</span>
            </span>
            <span>Hai, silakan hubungi saya di Instagram untuk penempatan iklan. <a href="https://www.instagram.com/user7193y" class="inline font-medium text-blue-600 underline dark:text-blue-500 underline-offset-2 decoration-600 dark:decoration-500 decoration-solid hover:no-underline">klik disini</a></span>
        </p>
    </div>
    <div class="flex items-center">
        <button data-dismiss-target="#sticky-banner" type="button" class="flex-shrink-0 inline-flex justify-center w-7 h-7 items-center text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 dark:hover:bg-gray-600 dark:hover:text-white">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Close banner</span>
        </button>
    </div>
</div>



    <!-- Breadcrumb -->
    <nav class="container mx-auto p-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="#"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                    </svg>
                    Home
                </a>
            </li>
        </ol>
    </nav>

    <!-- Image Gallery -->
    <div class="container mx-auto p-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <div>
            <a href="#">
                <img class="w-full h-auto rounded-lg shadow-md" src="img/icon.png" alt="Gallery Image">
            </a>
        </div>
        <div>
            <a href="#">
                <img class="w-full h-auto rounded-lg shadow-md" src="img/icon.png" alt="Gallery Image">
            </a>
        </div>
        <div>
            <a href="#">
                <img class="w-full h-auto rounded-lg shadow-md" src="img/icon.png" alt="Gallery Image">
            </a>
        </div>
        <div>
            <a href="#">
                <img class="w-full h-auto rounded-lg shadow-md" src="img/icon.png" alt="Gallery Image">
            </a>
        </div>
    </div>

    <!-- Separator Line -->
    <hr class="my-8 border-gray-300 dark:border-gray-600">

    <!-- Card Section -->
    <!-- Container -->
    <div class="container mx-auto space-y-4">
        <!-- Row -->
        <?php if ($data): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($data as $item): ?>
                    <div class="flex bg-white rounded-lg shadow-lg">
                <img class="w-24 h-auto object-cover rounded-l-lg" src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" alt="Image">
                <div class="p-4 flex-1">
                    <h5 class="text-lg font-bold text-blue-600 hover:underline">
                        <a href="comic?url=<?= urlencode($item['link']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                    </h5>
                    <p class="text-sm font-medium mt-2 text-gray-700"><?= htmlspecialchars($item['chapter']) ?></p>
                    <p class="text-sm text-gray-500 mt-2">Updated: <?= htmlspecialchars($item['updated']) ?></p>
                    <div class="flex items-center mt-3 text-sm text-gray-500">
                        <a href="chapter?url=<?= urlencode($item['chapterLink']) ?>" class="text-blue-500 hover:underline">Baca Chapter</a>
                    </div>
                </div>
            </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500">No data found.</p>
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
