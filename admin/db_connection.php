<?php
// Database connection file

function get_db_connection() {
    $dbHost = getenv('DB_HOST');
    $dbName = getenv('DB_NAME');
    $dbUser = getenv('DB_USER');
    $dbPassword = getenv('DB_PASS');
    
    try {
        // First, test if the database exists
        $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Check if database exists
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
        $exists = $stmt->fetchColumn();
        
        if (!$exists) {
            // Create the database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        
        // Connect to the database
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        // Log the error but don't expose details to users
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}
?>