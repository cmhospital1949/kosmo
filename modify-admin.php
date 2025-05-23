<?php
// Create donation_settings table

require_once __DIR__ . '/lib/Database.php';

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create donation_settings table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `donation_settings` (
        `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `bank_name` VARCHAR(100) NOT NULL,
        `account_number` VARCHAR(100) NOT NULL,
        `account_holder` VARCHAR(100) NOT NULL,
        `business_number` VARCHAR(50) NOT NULL,
        `kakaopay_enabled` TINYINT(1) DEFAULT 1,
        `bank_transfer_enabled` TINYINT(1) DEFAULT 1,
        `min_donation_amount` INT DEFAULT 1000,
        `default_amount` INT DEFAULT 50000,
        `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Check if any records exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM donation_settings");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insert default values
        $stmt = $pdo->prepare("INSERT INTO donation_settings 
            (bank_name, account_number, account_holder, business_number, kakaopay_enabled, bank_transfer_enabled, min_donation_amount, default_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Shinhan Bank',
            '140-013-927125',
            '한국스포츠의료지원재단',
            '322-82-00643',
            1,
            1,
            1000,
            50000
        ]);
        
        echo "Donation settings table created and default values inserted successfully!";
    } else {
        echo "Donation settings table already exists with data.";
    }

    echo "<br><br>Done!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>