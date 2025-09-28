<?php
// Include your database connection file
include('db_connection.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['info']['email'])) {
    // If the user is not logged in, show an alert and redirect to sign-in page
    echo '<script>
                alert("Please log in to view your bookmarks.");
                window.location.href = "../sign_in"; // Redirects after the alert
              </script>';
    exit;
}


// Get the user_id from the session
$email = $_SESSION['info']['email'];

// Query to get the user_id from the 'user' table using the email
$sql = "SELECT id FROM user WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

// If user is found, get the user_id
if ($result->num_rows > 0) {
    $user_id = $result->fetch_assoc()['id'];  // Retrieve the user_id
} else {
    echo '<script>alert("User not found in database.");</script>';
    exit;
}

// Query to fetch bookmarks for the logged-in user
// Query untuk mengambil bookmark untuk user yang login
$sql = "SELECT id, title, link, image, created_at FROM bookmark_comic WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bookmarks = $stmt->get_result();

// Close the statement
$stmt->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ComicID</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
  <!-- Sticky Banner -->
  <div class="sticky top-0 z-50 bg-pink-600 text-white py-2 px-4 flex justify-between items-center">
        <span class="text-sm font-medium">Promote your brand! Contact us on <a href="https://www.instagram.com/user7193y/" target="_blank" class="underline">Instagram</a>.</span>
        <button class="text-white hover:text-gray-300" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>

  <!-- Breadcrumb Navigation -->
  <nav class="bg-pink-700 py-3 px-4 rounded-b-lg shadow-lg">
        <ol class="list-reset flex text-sm">
            <li><a href="index" class="text-white hover:underline">Home</a></li>
            <li class="mx-2">&gt;</li>
            <li class="text-pink-200">Bookmark Comic</li>
        </ol>
    </nav>

  
  <!-- Logout Button Centered -->
    <nav class="flex justify-center py-4 mt-2">
        <form action="../logout" method="POST" class="mr-4">
            <button type="submit" class="text-gray-200 bg-red-600 hover:bg-red-700 transition rounded px-4 py-2">LogOut</button>
        </form>
        <a href="../index" class="text-gray-200 bg-pink-600 hover:bg-pink-700 transition rounded px-4 py-2 ml-4">Profile</a>
    </nav>


  <!-- Dynamic Card Section -->
    <div class="py-12 px-4">
        <h2 class="text-2xl font-bold text-pink-400 mb-6">Your Bookmarks</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if ($bookmarks->num_rows > 0): ?>
                <?php while ($item = $bookmarks->fetch_assoc()): ?>
                    <div class="bg-gray-800 rounded-lg shadow-md hover:shadow-pink-500 transition p-4">
                        <!-- Image Section -->
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-48 object-cover rounded-lg mb-4">

                        <!-- Title Section -->
                        <h3 class="text-lg font-semibold text-pink-300">
                            <a href="comic?url=<?= urlencode($item['link']) ?>" class="hover:text-pink-500">
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </h3>
                        <p class="text-gray-400 text-sm">Created Bookmark at: <?= htmlspecialchars($item['created_at']) ?></p>

                        <!-- Read and Delete Button Section -->
                        <div class="flex justify-between mt-4">
                            <a href="comic?url=<?= urlencode($item['link']) ?>" class="mt-2 block text-pink-500 hover:underline">Read Now</a>
                            <form action="deleted" method="POST">
                                <input type="hidden" name="bookmark_id" value="<?= htmlspecialchars($item['id']) ?>">
                                <button type="submit" class="text-gray-200 bg-red-600 hover:bg-red-700 transition rounded px-4 py-2">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">No data found</p>
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
            <a href="index" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h11M9 21V3m11 6h-4m0 0h4m-4 0v4m0-4l-4-4m0 16V7"></path></svg>
                <span class="text-sm">Home</span>
            </a>
            <a href="release" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16l-4-4m0 0l4-4m-4 4h16"></path></svg>
                <span class="text-sm">Releases</span>
            </a>
            <a href="search" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m4 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-sm">Search</span>
            </a>
            <a href="bookmark" class="text-white hover:text-pink-300">
                <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4v16a2 2 0 002 2h14a2 2 0 002-2V4a2 2 0 00-2-2H5a2 2 0 00-2 2zm2 0h14v16H5V4z"></path>
                </svg>
                <span class="text-sm">Bookmark</span>
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
</body>
</html>
