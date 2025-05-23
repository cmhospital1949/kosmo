<?php
// Database connection
function connect_db() {
    $host = 'db.kosmo.or.kr';
    $dbname = 'dbbestluck';
    $username = 'bestluck';
    $password = 'cmhospital1949!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

$pdo = connect_db();

if (!$pdo) {
    die("Failed to connect to database");
}

try {
    // Check if the newsletter_subscribers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'newsletter_subscribers'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create the newsletter_subscribers table
        $pdo->exec("CREATE TABLE `newsletter_subscribers` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `email` VARCHAR(255) NOT NULL UNIQUE,
            `name` VARCHAR(255) NULL,
            `language` VARCHAR(10) NOT NULL DEFAULT 'en',
            `status` ENUM('active', 'unsubscribed', 'bounced') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_email` (`email`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "Newsletter subscribers table created successfully.";
    } else {
        echo "Newsletter subscribers table already exists.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>