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
    // Check if the events table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'events'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create the events table
        $pdo->exec("CREATE TABLE `events` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `ko_title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `ko_description` TEXT NULL,
            `location` VARCHAR(255) NULL,
            `ko_location` VARCHAR(255) NULL,
            `start_date` DATETIME NOT NULL,
            `end_date` DATETIME NULL,
            `all_day` TINYINT(1) NOT NULL DEFAULT 0,
            `featured` TINYINT(1) NOT NULL DEFAULT 0,
            `registration_url` VARCHAR(255) NULL,
            `image_url` VARCHAR(255) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_start_date` (`start_date`),
            INDEX `idx_featured` (`featured`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert sample events
        $pdo->exec("INSERT INTO `events` (
            `title`, 
            `ko_title`, 
            `description`, 
            `ko_description`, 
            `location`, 
            `ko_location`, 
            `start_date`, 
            `end_date`, 
            `all_day`, 
            `featured`, 
            `registration_url`
        ) VALUES 
        (
            'Annual Sports Medicine Conference', 
            '연례 스포츠 의학 컨퍼런스', 
            'Join us for our annual sports medicine conference featuring keynote speakers, panel discussions, and networking opportunities.',
            '기조 연설자, 패널 토론 및 네트워킹 기회가 포함된 연례 스포츠 의학 컨퍼런스에 참여하세요.',
            'COEX Convention Center, Seoul',
            'COEX 컨벤션 센터, 서울',
            '2025-09-10 09:00:00',
            '2025-09-12 17:00:00',
            0,
            1,
            'https://example.com/register'
        ),
        (
            'Student-Athlete Health Workshop', 
            '학생 선수 건강 워크샵', 
            'A workshop focusing on injury prevention and health maintenance for student-athletes.',
            '학생 선수를 위한 부상 예방 및 건강 유지에 중점을 둔 워크샵입니다.',
            'Seoul National University',
            '서울대학교',
            '2025-06-15 10:00:00',
            '2025-06-15 16:00:00',
            0,
            1,
            'https://example.com/register-workshop'
        ),
        (
            'Foundation Fundraising Gala', 
            '재단 기금 모금 갈라', 
            'Join us for an evening of celebration and fundraising to support our mission.',
            '우리의 사명을 지원하기 위한 축하와 기금 모금의 저녁에 참여하세요.',
            'Grand Hyatt Hotel, Seoul',
            '그랜드 하얏트 호텔, 서울',
            '2025-07-20 18:00:00',
            '2025-07-20 22:00:00',
            0,
            1,
            'https://example.com/gala'
        ),
        (
            'Volunteer Orientation', 
            '자원봉사자 오리엔테이션', 
            'Orientation session for new volunteers. Learn about our programs and how you can get involved.',
            '새로운 자원봉사자를 위한 오리엔테이션 세션입니다. 우리의 프로그램과 참여 방법에 대해 알아보세요.',
            'KOSMO Foundation Office',
            '한국스포츠의료지원재단 사무실',
            '2025-06-05 14:00:00',
            '2025-06-05 16:00:00',
            0,
            0,
            NULL
        ),
        (
            'Sports Medicine Certificate Program', 
            '스포츠 의학 자격증 프로그램', 
            'A comprehensive program for healthcare professionals looking to specialize in sports medicine.',
            '스포츠 의학을 전문으로 하고자 하는 의료 전문가를 위한 포괄적인 프로그램입니다.',
            'Online',
            '온라인',
            '2025-08-01 00:00:00',
            '2025-10-31 23:59:59',
            1,
            0,
            'https://example.com/certificate'
        )");
        
        echo "Events table created and populated with sample data.";
    } else {
        echo "Events table already exists.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>