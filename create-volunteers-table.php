<?php
require_once __DIR__ . '/config.php';
// Database connection
function connect_db() {
    global $host, $dbname, $username, $password;
    
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
    // Check if the volunteers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'volunteers'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create the volunteers table
        $pdo->exec("CREATE TABLE `volunteers` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `email` VARCHAR(255) NOT NULL,
            `phone` VARCHAR(50) NULL,
            `interests` TEXT NULL,
            `skills` TEXT NULL,
            `availability` TEXT NULL,
            `background` TEXT NULL,
            `reason` TEXT NULL,
            `language` VARCHAR(10) NOT NULL DEFAULT 'en',
            `status` ENUM('pending', 'approved', 'rejected', 'inactive') NOT NULL DEFAULT 'pending',
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_email` (`email`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create volunteer_interests table for storing predefined interests
        $pdo->exec("CREATE TABLE `volunteer_interests` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `ko_name` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `ko_description` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert default volunteer interests
        $pdo->exec("INSERT INTO `volunteer_interests` (`name`, `ko_name`, `description`, `ko_description`) VALUES 
            ('Medical Support', '의료 지원', 'Assisting with medical support for athletes and students', '운동선수 및 학생들을 위한 의료 지원 지원'),
            ('Event Organization', '행사 조직', 'Helping organize and run events and workshops', '이벤트 및 워크샵 조직 및 운영 지원'),
            ('Educational Programs', '교육 프로그램', 'Supporting educational programs and training sessions', '교육 프로그램 및 훈련 세션 지원'),
            ('Administrative Support', '행정 지원', 'Assisting with administrative tasks and office work', '행정 업무 및 사무실 업무 지원'),
            ('Translation Services', '번역 서비스', 'Providing translation services for documents and events', '문서 및 이벤트에 대한 번역 서비스 제공'),
            ('Fundraising', '모금 활동', 'Helping with fundraising activities and donor relations', '모금 활동 및 기부자 관계 지원'),
            ('Marketing & Communication', '마케팅 및 커뮤니케이션', 'Supporting marketing, social media, and communication efforts', '마케팅, 소셜 미디어 및 커뮤니케이션 노력 지원')
        ");
        
        echo "Volunteers tables created and populated with default interests.";
    } else {
        echo "Volunteers tables already exist.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>