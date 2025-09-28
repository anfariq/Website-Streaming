<?php
$servername = "192.168.0.100:8080";
$username = "myuser";
$password = "mypassword";
$dbname = "mydb";

// Create connection
$db = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
?>
