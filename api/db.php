<?php
// Check for local credentials first (Dev Environment)
if (file_exists(__DIR__ . '/db_local.php')) {
    require_once __DIR__ . '/db_local.php';
} else {
    // Vercel / Production Environment
    // Retrieve credentials from Environment Variables
    $host = getenv('TIDB_HOST');
    $port = getenv('TIDB_PORT') ? (int)getenv('TIDB_PORT') : 4000;
    $username = getenv('TIDB_USER');
    $password = getenv('TIDB_PASSWORD');
    $database = getenv('TIDB_DB_NAME');

    if (!$host || !$username || !$password || !$database) {
        die("Database configuration missing. Please check Environment Variables.");
    }

    $conn = mysqli_init();
    // TiDB Cloud requires SSL
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL); 
    
    // Suppress warnings to avoid leaking paths, handle errors manually
    if (!@mysqli_real_connect($conn, $host, $username, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
        die('Failed to connect to Database.');
    }

    // Set charset to utf8mb4
    mysqli_set_charset($conn, "utf8mb4");
}
?>
