<?php 
// Start session
session_start();
require_once 'config.php';
require_once 'db.php'; // Include your database connection

if (isset($_GET['code'])) {
   $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
   $client->setAccessToken($token);

   // Getting user profile
   $gauth = new Google_Service_Oauth2($client);
   $google_info = $gauth->userinfo->get();

   // Store user info in session
   $_SESSION['info'] = [
      'name' => $google_info->name, 
      'email' => $google_info->email, 
      'picture' => $google_info->picture
   ];

   // Check if user already exists in the database
   $email = $google_info->email;
   $stmt = $db->prepare("SELECT * FROM user WHERE email = ?");
   $stmt->bind_param("s", $email);
   $stmt->execute();
   $result = $stmt->get_result();

   if ($result->num_rows == 0) {
      // Insert new user into the database
      $name = $google_info->name;
      $picture = $google_info->picture;

      $insertStmt = $db->prepare("INSERT INTO user (name, email, picture) VALUES (?, ?, ?)");
      $insertStmt->bind_param("sss", $name, $email, $picture);
      $insertStmt->execute();
   }

   // Save session ID to the database (optional)
   $sessionId = session_id();
   $userId = $result->num_rows > 0 ? $result->fetch_assoc()['id'] : $db->insert_id;

   // Check if session already exists in database
   $sessionCheckStmt = $db->prepare("SELECT * FROM sessions WHERE session_id = ?");
   $sessionCheckStmt->bind_param("s", $sessionId);
   $sessionCheckStmt->execute();
   $sessionCheckResult = $sessionCheckStmt->get_result();

   if ($sessionCheckResult->num_rows == 0) {
      // Save session data in the sessions table
      $sessionInsertStmt = $db->prepare("INSERT INTO sessions (user_id, session_id) VALUES (?, ?)");
      $sessionInsertStmt->bind_param("is", $userId, $sessionId);
      $sessionInsertStmt->execute();
   }

   header('Location: index');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>
   <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
   <link href="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.css" rel="stylesheet">
</head>
<body class="bg-pink-50">

   <div class="flex items-center justify-center min-h-screen">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 w-full max-w-md">
         <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-pink-600 dark:text-pink-400 mb-4">Welcome to Our Site</h1>
            <p class="text-lg text-gray-800 dark:text-white mb-6">Please log in to continue</p>
            
            <!-- Google Login Button -->
            <a href="<?= $client->createAuthUrl() ?>" class="w-full inline-block bg-pink-600 hover:bg-pink-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300">
               Login with Google
            </a>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/flowbite@1.6.5/dist/flowbite.min.js"></script>
</body>
</html
