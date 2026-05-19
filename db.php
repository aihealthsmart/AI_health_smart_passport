<?php
// 1. Environment variables from Railway (will use local XAMPP defaults if offline)
$host   = getenv('MYSQLHOST')     ?: "localhost";
$user   = getenv('MYSQLUSER')     ?: "root";
$pass   = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "AI_health_smart_passport"; // Cleaned & matched to your local schema name
$port   = getenv('MYSQLPORT')     ?: "3306"; 

// 2. Establish the connection using all active server parameters
$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

// 3. Robust error testing block to ensure database stability
if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}
?>