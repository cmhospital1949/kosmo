<?php
// Database connection
function connect_db() {
    $host = 'localhost';
    $dbname = 'bestluck';
    $username = 'bestluck';
    $password = 'Nocpriss12!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        echo "Database connection error: " . $e->getMessage();
        return null;
    }
}

// Attempt to connect to the database
$pdo = connect_db();

if ($pdo) {
    // Check if donation_settings table exists
    $tableExists = false;
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'donation_settings'");
        $tableExists = ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        echo "Error checking if table exists: " . $e->getMessage();
        exit;
    }

    // Add naverpay_enabled field if table exists
    if ($tableExists) {
        // Check if naverpay_enabled column already exists
        $columnExists = false;
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM donation_settings LIKE 'naverpay_enabled'");
            $columnExists = ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            echo "Error checking if column exists: " . $e->getMessage();
            exit;
        }

        // Add the column if it doesn't exist
        if (!$columnExists) {
            try {
                $pdo->exec("ALTER TABLE donation_settings ADD COLUMN naverpay_enabled TINYINT(1) NOT NULL DEFAULT 1");
                echo "Successfully added 'naverpay_enabled' column to donation_settings table.<br>";
            } catch (PDOException $e) {
                echo "Error adding column: " . $e->getMessage();
                exit;
            }
        } else {
            echo "Column 'naverpay_enabled' already exists.<br>";
        }
    } else {
        // Create the donation_settings table with all fields
        try {
            $sql = "CREATE TABLE donation_settings (
                id INT(11) NOT NULL AUTO_INCREMENT,
                bank_name VARCHAR(255) NOT NULL DEFAULT 'Shinhan Bank',
                account_number VARCHAR(255) NOT NULL DEFAULT '140-013-927125',
                account_holder VARCHAR(255) NOT NULL DEFAULT '한국스포츠의료지원재단',
                business_number VARCHAR(255) NOT NULL DEFAULT '322-82-00643',
                kakaopay_enabled TINYINT(1) NOT NULL DEFAULT 1,
                naverpay_enabled TINYINT(1) NOT NULL DEFAULT 1,
                bank_transfer_enabled TINYINT(1) NOT NULL DEFAULT 1,
                min_donation_amount INT(11) NOT NULL DEFAULT 1000,
                default_amount INT(11) NOT NULL DEFAULT 50000,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $pdo->exec($sql);
            
            // Insert default data
            $sql = "INSERT INTO donation_settings (bank_name, account_number, account_holder, business_number, kakaopay_enabled, naverpay_enabled, bank_transfer_enabled, min_donation_amount, default_amount) 
                    VALUES ('Shinhan Bank', '140-013-927125', '한국스포츠의료지원재단', '322-82-00643', 1, 1, 1, 1000, 50000)";
            $pdo->exec($sql);
            
            echo "Successfully created donation_settings table with default values.<br>";
        } catch (PDOException $e) {
            echo "Error creating table: " . $e->getMessage();
            exit;
        }
    }
    
    echo "Donation settings table setup complete with NaverPay support.<br>";
    echo "<a href='donate.php'>Go to Donation Page</a>";
    
} else {
    echo "Failed to connect to the database.";
}
?>
