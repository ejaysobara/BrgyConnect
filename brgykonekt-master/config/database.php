<?php
// Prevent multiple session starts if already initialized elsewhere.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// XAMPP defaults. Change these only if you changed your MySQL account in XAMPP.
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "barangayconnect_db";
$port = 3306;

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @mysqli_connect($servername, $username, $password, "", $port);

if (!$conn) {
    die("Database server connection failed. Start MySQL in XAMPP, then reload this page. Details: " . mysqli_connect_error());
}

$safe_dbname = str_replace("`", "``", $dbname);
$created = mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$safe_dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

if (!$created || !mysqli_select_db($conn, $dbname)) {
    die("Database selection failed: " . mysqli_error($conn));
}

mysqli_set_charset($conn, "utf8mb4");
?>
