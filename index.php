<?php 
session_start();
require_once 'db.php'; // Include the database connection

// Check if the user is logged in
if (!isset($_SESSION['info'])) {
    header('Location: sign_in'); // Replace with the actual path to your sign_in page
    exit();
}

// Get the session ID
$sessionId = session_id();
$userEmail = $_SESSION['info']['email']; // Get user email from session

// Check if the session ID exists in the database
$stmt = $db->prepare("SELECT * FROM sessions WHERE session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

// Check if the session is valid
if ($result->num_rows == 0) {
    // Session not found, redirect to sign in page
    header('Location: sign_in'); // Replace with actual path
    exit();
}

// Optionally, check if the user exists in the users table
$stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows == 0) {
    // User not found in the database, redirect to sign in page
    header('Location: sign_in'); // Replace with actual path
    exit();
}

// User is logged in and session is valid, continue with the page
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Hai <?= $_SESSION['info']['name'] ?></title>
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet">
</head>
<body class="bg-pink-50">

   <div class="flex items-center justify-center min-h-screen">
      <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 w-full max-w-md">
         <!-- Profile Section -->
         <div class="text-center mb-6">
            <img src="<?= $_SESSION['info']['picture'] ?>" alt="Profile Picture" class="w-24 h-24 rounded-full mx-auto mb-4">
            <p class="text-2xl font-semibold text-pink-600 dark:text-pink-400 mb-2">Welcome!</p>
            <p class="text-lg font-medium text-gray-800 dark:text-white"><?= $_SESSION['info']['name'] ?> - <?= $_SESSION['info']['email'] ?></p>
         </div>

         <!-- Menu Section -->
         <div class="mb-6">
            <ul class="space-y-6">
               <!-- comic -->
               <li>
                  <a href="comic/index" class="block p-4 bg-gradient-to-r from-pink-500 via-pink-600 to-pink-700 text-white font-semibold rounded-2xl shadow-xl hover:from-pink-600 hover:to-pink-800 hover:scale-105 transition-all duration-300 ease-in-out transform">
                     <span class="text-xl font-bold">Comic</span>
                  </a>
               </li>
               <!-- anime -->
               <li>
                  <a href="anime/index" class="block p-4 bg-gradient-to-r from-pink-500 via-pink-600 to-pink-700 text-white font-semibold rounded-2xl shadow-xl hover:from-pink-600 hover:to-pink-800 hover:scale-105 transition-all duration-300 ease-in-out transform">
                     <span class="text-xl font-bold">Anime</span>
                  </a>
               </li>
               <!-- film -->
               <li>
                  <a href="javascript:void(0);" onclick="alert('Maaf ya sayang, web film belum jadi');" class="block p-4 bg-gradient-to-r from-pink-500 via-pink-600 to-pink-700 text-white font-semibold rounded-2xl shadow-xl hover:from-pink-600 hover:to-pink-800 hover:scale-105 transition-all duration-300 ease-in-out transform">
                     <span class="text-xl font-bold">Film</span>
                  </a>
               </li>
            </ul>
         </div>

         <!-- Logout Button -->
         <div class="text-center">
            <a href="/crap/logout.php" class="btn btn-primary bg-pink-600 hover:bg-pink-700 text-white font-semibold py-2 px-4 rounded-md transition duration-300">
               Log Out
            </a>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.js"></script>
</body>
</html>
