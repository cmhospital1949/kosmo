<?php
// Database connection
require_once __DIR__ . '/lib/Database.php';

function connect_db() {
    try {
        return Database::getConnection();
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
    // Check if the donation_settings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'donation_settings'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create the donation_settings table
        $pdo->exec("CREATE TABLE `donation_settings` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `bank_name` VARCHAR(100) NOT NULL DEFAULT 'Shinhan Bank',
            `account_number` VARCHAR(100) NOT NULL DEFAULT '140-013-927125',
            `account_holder` VARCHAR(100) NOT NULL DEFAULT '한국스포츠의료지원재단',
            `business_number` VARCHAR(100) NOT NULL DEFAULT '322-82-00643',
            `kakaopay_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `bank_transfer_enabled` TINYINT(1) NOT NULL DEFAULT 1,
            `min_donation_amount` INT(11) NOT NULL DEFAULT 1000,
            `default_amount` INT(11) NOT NULL DEFAULT 50000,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert default donation settings
        $pdo->exec("INSERT INTO `donation_settings` (
            `bank_name`, 
            `account_number`, 
            `account_holder`, 
            `business_number`, 
            `kakaopay_enabled`, 
            `bank_transfer_enabled`, 
            `min_donation_amount`, 
            `default_amount`
        ) VALUES (
            'Shinhan Bank',
            '140-013-927125',
            '한국스포츠의료지원재단',
            '322-82-00643',
            1,
            1,
            1000,
            50000
        )");
        
        echo "Donation settings table created and initialized with default values.";
    } else {
        echo "Donation settings table already exists.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>