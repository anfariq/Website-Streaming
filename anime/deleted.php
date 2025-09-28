<?php
// Include koneksi database
include('db_connection.php');
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['info']['email'])) {
    echo '<script>
                alert("Please log in to delete bookmarks.");
                window.location.href = "../sign_in";
          </script>';
    exit;
}

// Cek apakah `bookmark_id` dikirimkan melalui POST
if (isset($_POST['bookmark_id'])) {
    $bookmark_id = $_POST['bookmark_id'];

    // Query untuk menghapus bookmark berdasarkan ID
    $sql = "DELETE FROM bookmarks WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $bookmark_id);

    if ($stmt->execute()) {
        echo '<script>
                  alert("Bookmark deleted successfully.");
                  window.location.href = "bookmark"; // Redirect ke halaman bookmark
              </script>';
    } else {
        echo '<script>alert("Failed to delete bookmark: ' . $stmt->error . '");</script>';
    }

    $stmt->close();
} else {
    echo '<script>alert("Invalid request. No bookmark ID provided.");</script>';
}

$conn->close();
?>
