<?php
// Database setup script
$dbHost = getenv('DB_HOST');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPassword = getenv('DB_PASS');

try {
    // First, check if the database exists
    $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
    $exists = $stmt->fetchColumn();
    
    if (!$exists) {
        // Create the database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "Database created successfully.<br>";
    }
    
    // Connect to the database
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create tables
        
        // Admin users table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_users` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `name` VARCHAR(100) NOT NULL,
            `email` VARCHAR(100) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `last_login` TIMESTAMP NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Programs table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `programs` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `ko_title` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `ko_description` TEXT,
            `image` VARCHAR(255),
            `content` TEXT,
            `ko_content` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Gallery categories table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `gallery_categories` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `ko_name` VARCHAR(100) NOT NULL,
            `description` TEXT,
            `ko_description` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Gallery images table
        $pdo->exec("CREATE TABLE IF NOT EXISTS `gallery_images` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `category_id` INT(11) UNSIGNED,
            `title` VARCHAR(255),
            `ko_title` VARCHAR(255),
            `description` TEXT,
            `ko_description` TEXT,
            `filename` VARCHAR(255) NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "Tables created successfully.<br>";
        
        // Create default admin user if none exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `admin_users`");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `admin_users` (`username`, `password`, `name`, `email`) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', $defaultPassword, 'KOSMO Admin', 'goodwill@kosmo.or.kr']);
            echo "Default admin user created.<br>";
        }
        
        // Import programs if none exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM `programs`");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Array of programs data from programs.php
            $all_programs = [
                [
                    'slug' => 'medical-support',
                    'title' => 'Medical Support for Students & Student-Athletes',
                    'ko_title' => '학생·학생선수 의료 지원',
                    'image' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
                    'description' => 'Providing medical care and support for students and student-athletes.',
                    'ko_description' => '학생 및 학생선수를 위한 의료 지원 제공.',
                    'content' => '<p>Our medical support program provides comprehensive healthcare services for students and student-athletes. We focus on prevention, diagnosis, treatment, and rehabilitation of sports-related injuries and other health issues.</p><p>Key services include:</p><ul><li>Regular health check-ups</li><li>Injury prevention workshops</li><li>Physical therapy and rehabilitation</li><li>Mental health counseling</li><li>Nutritional guidance</li></ul><p>Through partnerships with medical professionals and institutions, we ensure that our beneficiaries receive high-quality care tailored to their specific needs.</p>',
                    'ko_content' => '<p>저희 의료 지원 프로그램은 학생 및 학생선수를 위한 종합적인 의료 서비스를 제공합니다. 스포츠 관련 부상 및 기타 건강 문제의 예방, 진단, 치료 및 재활에 중점을 둡니다.</p><p>주요 서비스는 다음과 같습니다:</p><ul><li>정기 건강 검진</li><li>부상 예방 워크샵</li><li>물리 치료 및 재활</li><li>정신 건강 상담</li><li>영양 지도</li></ul><p>의료 전문가 및 기관과의 파트너십을 통해 저희는 수혜자들이 그들의 특정 요구에 맞춘 고품질 케어를 받을 수 있도록 보장합니다.</p>'
                ],
                [
                    'slug' => 'cultural-arts',
                    'title' => 'Cultural & Arts Education/Events',
                    'ko_title' => '문화·예술 교육 및 행사',
                    'image' => 'https://images.unsplash.com/photo-1535982330050-f1c2fb79ff78?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
                    'description' => 'Fostering creativity and expression through cultural and arts programs.',
                    'ko_description' => '문화 및 예술 프로그램을 통한 창의성과 표현력 육성.',
                    'content' => '<p>Our Cultural & Arts Education/Events program aims to enrich the lives of students and athletes through exposure to various forms of art and cultural activities. We believe that artistic expression is essential for holistic development and personal growth.</p><p>Program highlights include:</p><ul><li>Regular workshops in visual arts, music, dance, and theater</li><li>Cultural exchange programs</li><li>Art exhibitions showcasing student work</li><li>Performance opportunities</li><li>Field trips to museums, galleries, and cultural events</li></ul><p>Through these activities, participants develop creativity, self-expression, and an appreciation for cultural diversity, enhancing their overall educational experience.</p>',
                    'ko_content' => '<p>저희 문화 및 예술 교육/행사 프로그램은 다양한 형태의 예술 및 문화 활동을 통해 학생과 선수들의 삶을 풍요롭게 하는 것을 목표로 합니다. 예술적 표현은 전인적 발달과 개인 성장에 필수적이라고 믿습니다.</p><p>프로그램 하이라이트:</p><ul><li>시각 예술, 음악, 댄스, 연극 정기 워크샵</li><li>문화 교류 프로그램</li><li>학생 작품 전시회</li><li>공연 기회</li><li>박물관, 갤러리, 문화 행사 현장 학습</li></ul><p>이러한 활동을 통해 참가자들은 창의성, 자기표현, 문화적 다양성에 대한 이해를 발전시켜 전반적인 교육 경험을 향상시킵니다.</p>'
                ]
                // Add more programs here
            ];
            
            // Import programs
            $stmt = $pdo->prepare("INSERT INTO `programs` (`slug`, `title`, `ko_title`, `description`, `ko_description`, `image`, `content`, `ko_content`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($all_programs as $program) {
                $stmt->execute([
                    $program['slug'], 
                    $program['title'], 
                    $program['ko_title'], 
                    $program['description'], 
                    $program['ko_description'],
                    $program['image'],
                    $program['content'] ?? '',
                    $program['ko_content'] ?? ''
                ]);
            }
            
            echo "Programs imported successfully.<br>";
        }
    } else {
        echo "Tables already exist. No changes made.<br>";
    }
    
    echo "Database setup completed successfully.";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>