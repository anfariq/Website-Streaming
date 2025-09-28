<?php
if (isset($_GET['url'])) {
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
    die('cURL error: ' . curl_error($ch));
}

// Check the HTTP response code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode !== 200) {
    die('Error: Unable to fetch the page, HTTP code: ' . $httpCode);
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
$title = $xpath->evaluate("string(//h1[@class='entry-title'])");

// Extract description
$description = $xpath->evaluate("string(//div[@class='shortcsc'])");

// Extract image (thumbnail)
$image = $xpath->evaluate("string(//div[@class='thumb']//img/@src)");

// Extract rating
$rating = $xpath->evaluate("string(//i[@itemprop='ratingValue'])");

// Extract chapter links and additional information
$chapters = [];
$chapterNodes = $xpath->query("//span[@class='lchx']/a");
$dateNodes = $xpath->query("//span[@class='dt']");

// Loop through each chapter link and process it
if ($chapterNodes->length === $dateNodes->length) {
    for ($i = 0; $i < $chapterNodes->length; $i++) {
        // Extract and process the URL for each chapter
        $chapterUrl = parse_url($chapterNodes->item($i)->getAttribute('href')); // Memecah URL menjadi komponen
        $path1 = $chapterUrl['path'] ?? ''; // Ambil bagian path dari URL
        $processedLinkChapter = ltrim($path1, '/'); // Hapus "/" di awal path

        $chapters[] = [
            'link' => $processedLinkChapter,
            'name' => trim($chapterNodes->item($i)->textContent),
            'date' => trim($dateNodes->item($i)->textContent),
        ];
    }
}

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
    <style>
        .search { margin-bottom: 20px; }
        .bxcl ul { list-style: none; padding: 0; }
        .bxcl ul li { margin-bottom: 10px; }
        .scrolling { max-height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; }
    </style>
    <script>
        function searchlistchapt() {
            const input = document.getElementById('searchchapter').value.toUpperCase();
            const list = document.getElementById('chapter_list').getElementsByTagName('li');

            for (let i = 0; i < list.length; i++) {
                const chapterLink = list[i].getElementsByTagName('a')[0];
                if (chapterLink) {
                    const textValue = chapterLink.textContent || chapterLink.innerText;
                    list[i].style.display = textValue.toUpperCase().includes(input) ? '' : 'none';
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200">

<div class="container mx-auto p-4">

    <!-- Breadcrumb -->
    <nav class="container mx-auto p-4" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="index"
                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                    <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z" />
                    </svg>
                    Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 mx-1" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 9 4-4-4-4" />
                    </svg>
                    <a href="#"
                        class="ms-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ms-2 dark:text-gray-400 dark:hover:text-white">Baca Comic</a>
                </div>
            </li>
        </ol>
    </nav>

    <div class="flex flex-col md:flex-row gap-6 mb-6">
        <a href="#" class="flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow-md w-full hover:bg-gray-100 transition-all transform hover:scale-105">
            <img class="object-cover w-full h-64 rounded-t-lg md:w-48 md:h-auto md:rounded-none md:rounded-s-lg" src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>">
            <div class="flex flex-col justify-between p-4 leading-normal">
                <h5 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($title) ?></h5>
                <p class="text-gray-700"><?= htmlspecialchars($description) ?></p>
                <p class="text-gray-500 mt-2">Rating: <?= htmlspecialchars($rating) ?></p>
            </div>
        </a>
    </div>

    <div class="search mb-4">
        <input type="text" id="searchchapter" onkeyup="searchlistchapt()" placeholder="Search by Chapter Ex: 99" class="w-full p-3 border rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="bxcl scrolling" id="chapter_list">
        <ul>
            <?php foreach ($chapters as $chapter): ?>
                <li class="mb-4 p-3 bg-gray rounded-lg shadow-sm hover:bg-gray-50 transition-all">
                    <a href="chapter.php?url=<?= urlencode($chapter['link']) ?>" class="text-blue-500 hover:underline"><?= htmlspecialchars($chapter['name']) ?></a>
                    <span class="text-gray-500 text-sm"><?= htmlspecialchars($chapter['date']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

</body>
</html>
