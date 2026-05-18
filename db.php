<?php
// Railway automatically provides these environment variables when you link the database.
// If they don't exist (like on your local XAMPP), it falls back to your local settings.
$host   = getenv('MYSQLHOST') ?: "localhost";
$user   = getenv('MYSQLUSER') ?: "root";
$pass   = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "ai health passport"; 
$port   = getenv('MYSQLPORT') ?: "3306"; // Railway uses a dynamic port; XAMPP defaults to 3306

// Establish the connection, including the port
$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>