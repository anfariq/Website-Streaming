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
$sql = "SELECT id, title, link, image, created_at FROM bookmarks WHERE user_id = ? ORDER BY created_at DESC";
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
  <title>Anime Website</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.6.5/flowbite.min.js"></script>
</head>
<body class="bg-gradient-to-r from-purple-900 via-gray-900 to-gray-800 text-gray-200">
  <!-- Sticky Banner -->
  <div class="bg-purple-700 text-white p-2 text-center sticky top-0 z-50">
    <div class="flex justify-between items-center max-w-7xl mx-auto px-4">
      <span>Advertise with us! Visit our <a href="https://www.instagram.com/user7193y" class="underline font-bold">Instagram</a>.</span>
      <button onclick="this.parentElement.parentElement.remove();" class="text-white">&times;</button>
    </div>
  </div>

  <!-- Top Navigation -->
  <header class="bg-gray-900 shadow-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-6 py-3 flex justify-between items-center">
      <a href="#" class="text-2xl font-bold text-purple-400">AnimeID</a>
      <nav>
        <ul class="flex space-x-6">
          <li><a href="index" class="text-gray-200 hover:text-purple-400 transition">Home</a></li>
          <li><a href="bookmark" class="text-gray-200 hover:text-purple-400 transition">Bookmark</a></li>
          <li><a href="search" class="text-gray-200 hover:text-purple-400 transition">Search</a></li>
        </ul>
      </nav>
    </div>
  </header>
  
  <!-- Breadcrumb -->
  <nav class="max-w-7xl mx-auto px-6 py-4 text-gray-400" aria-label="Breadcrumb">
    <ol class="flex items-center space-x-4">
      <li>
        <a href="index" class="flex items-center text-purple-400 hover:text-purple-200 transition">
          <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 3.293l6 6V17a1 1 0 01-1 1h-3v-4H8v4H5a1 1 0 01-1-1v-7.707l6-6z"></path>
          </svg>
          Home
        </a>
      </li>
      <li><span class="text-gray-500">/</span></li>
      <li class="truncate">Bookmark Anime List</li>
    </ol>
  </nav>

  
  <!-- Logout Button Centered -->
  <nav class="flex justify-center py-4">
    <form action="../logout" method="POST">
      <button type="submit" class="text-gray-200 bg-red-600 hover:bg-red-700 transition rounded px-4 py-2">LogOut</button>
    </form>
  </nav>

  <!-- Dynamic Card Section -->
  <div class="max-w-7xl mx-auto px-4 py-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if ($bookmarks->num_rows > 0): ?>
      <?php while ($item = $bookmarks->fetch_assoc()): ?>
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden group">
          <a href="anime?url=<?= urlencode($item['link']) ?>">
            <div class="relative">
              <!-- Image Section -->
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
            </div>
            <!-- Title Section -->
            <div class="p-4">
              <h3 class="font-bold text-lg text-white truncate">
                <a href="anime?url=<?= urlencode($item['link']) ?>" class="hover:text-blue-400">
                  <?= htmlspecialchars($item['title']) ?>
                </a>
              </h3>
              <p class="text-gray-400 text-sm">Created Bookmark at: <?= htmlspecialchars($item['created_at']) ?></p>
              <nav class="flex justify-center py-4">
                <form action="deleted" method="POST">
                  <input type="hidden" name="bookmark_id" value="<?= htmlspecialchars($item['id']) ?>">
                  <button type="submit" class="text-gray-200 bg-red-600 hover:bg-red-700 transition rounded px-4 py-2">Delete</button>
                </form>
              </nav>
            </div>
          </a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center text-gray-500">No data found</p>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-400 py-6 text-center">
    <p>&copy; 2024 <a href="#" class="text-purple-400">AnimeID</a>. All rights reserved.</p>
  </footer>
</body>
</html>
