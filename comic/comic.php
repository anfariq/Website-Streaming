<?php
if (isset($_GET['url'])) {
    // Mendapatkan path dari parameter URL
    $path = $_GET['url'];

    // Domain dasar
    $baseDomain = 'https://komikindo3.com';

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
$description = $xpath->evaluate("string(//div[@class='desc']//p)");

// Extract image (thumbnail)
$image = $xpath->evaluate("string(//div[@class='thumb']//img/@src)");

// Extract rating
$rating = $xpath->evaluate("string(//i[@itemprop='ratingValue'])");

$Links = [];
$Linknode = $xpath->query("//div[@class='sharemanga']/a");

if ($Linknode !== null) {
    // Loop through each <a> node
    foreach ($Linknode as $node) {
        if ($node instanceof DOMElement) {
            $href = $node->getAttribute('href'); // Get the href attribute

            // Extract the actual URL from the Facebook sharing link
            if (preg_match('/^https:\/\/facebook\.com\/sharer\/sharer\.php\?u=(.*)$/', $href, $matches)) {
                $sharedUrl = $matches[1]; // The actual URL shared

                // Parse the shared URL
                $parsedUrl = parse_url($sharedUrl);
                
                // Extract the path after 'komik/'
                if (isset($parsedUrl['path']) && strpos($parsedUrl['path'], 'komik/') !== false) {
                    // Extract only the part after 'komik/'
                    $relativePath = substr($parsedUrl['path'], strpos($parsedUrl['path'], 'komik/'));
                    
                    // Add the relative path to the array
                    $Links[] = $relativePath; // e.g., komik/529782-eleceed/
                }
            }

        }
    }
} else {
    echo "XPath query returned no nodes.\n";
}

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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white">
    <!-- Sticky Banner -->
    <div class="sticky top-0 z-50 bg-pink-600 text-white py-2 px-4 flex justify-between items-center">
        <span class="text-sm font-medium">Explore comics and more! Follow us on <a href="https://www.instagram.com/fa2yl/" target="_blank" class="underline">Instagram</a>.</span>
        <button class="text-white hover:text-gray-300" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>

    <!-- Breadcrumb Navigation -->
    <nav class="bg-pink-700 py-3 px-4">
        <ol class="list-reset flex">
            <li><a href="index.php" class="text-white hover:text-pink-300">Home</a></li>
            <li class="mx-2">/</li>
            <li><a href="#" class="text-pink-300">Baca Comic</a></li>
        </ol>
    </nav>

    <!-- Hero Section -->
    <div class="text-center py-12">
        <h1 class="text-4xl font-extrabold text-pink-400 mb-4">Discover Futuristic Comics</h1>
        <p class="text-gray-300">Search and dive into a vibrant comic universe.</p>
    </div>

    <!-- Comic Details Section -->
    <div class="max-w-4xl mx-auto p-6 bg-gray-800 rounded-lg shadow-lg">
        <div class="flex flex-col md:flex-row gap-6">
            <img class="object-cover w-full h-64 rounded-lg md:w-48 md:h-auto" src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>">
            <div class="flex flex-col justify-between w-full">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-2xl font-bold text-pink-300"><?= htmlspecialchars($title) ?></h5>
                </div>
                <p class="text-gray-300 mb-4"><?= htmlspecialchars($description) ?></p>
                <p class="text-pink-400">Rating: <?= htmlspecialchars($rating) ?></p>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="my-6 max-w-4xl mx-auto">
        <input type="text" id="searchchapter" onkeyup="searchlistchapt()" placeholder="Search Chapters..." class="w-full p-3 border border-pink-500 rounded-lg shadow-md bg-gray-700 text-gray-200 focus:outline-none focus:ring-2 focus:ring-pink-500">
    </div>

    <!-- Chapter List -->
    <div class="max-w-4xl mx-auto bg-gray-800 rounded-lg shadow-lg p-6">
        <ul id="chapter_list" class="space-y-4">
            <?php foreach ($chapters as $chapter): ?>
                <li class="bg-gray-700 p-4 rounded-lg hover:bg-pink-700 transition-all">
                    <a href="chapter.php?url=<?= urlencode($chapter['link']) ?>" class="text-pink-300 hover:underline text-lg font-medium">
                        <?= htmlspecialchars($chapter['name']) ?>
                    </a>
                    <p class="text-gray-400 text-sm">Released: <?= htmlspecialchars($chapter['date']) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 py-6 px-4 text-center my-6">
        <p class="text-gray-500 text-sm">&copy; 2024 ComicID. All rights reserved.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
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
</body>
</html>
