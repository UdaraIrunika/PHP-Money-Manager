<?php
// config.php
// Database configuration - edit these values for your environment

session_start();

define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'money_manager');
define('DB_USER', 'root');
define('DB_PASS', ''); // set your mysql root password if any

define('BASE_URL', '/ALL_PROJECTS/meny_manager'); // adjust if placed elsewhere

function get_pdo()
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $opts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            // For production, hide error details
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $pdo;
}

?>