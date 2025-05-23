<?php
session_start();

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

// Check if DB setup is needed
function setup_database() {
    $pdo = connect_db();
    
    if (!$pdo) {
        return "Database connection failed. Check your database credentials.";
    }
    
    try {
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
            
            // Create default admin user if none exists
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO `admin_users` (`username`, `password`, `name`, `email`) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', $defaultPassword, 'KOSMO Admin', 'goodwill@kosmo.or.kr']);
            
            // Import programs from the PRD
            $programs = [
                [
                    'slug' => 'medical-support',
                    'title' => 'Medical Support for Students & Student-Athletes',
                    'ko_title' => '학생·학생선수 의료 지원',
                    'description' => 'Providing medical care and support for students and student-athletes.',
                    'ko_description' => '학생 및 학생선수를 위한 의료 지원 제공.',
                    'image' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
                    'content' => '<p>Our medical support program provides comprehensive healthcare services for students and student-athletes. We focus on prevention, diagnosis, treatment, and rehabilitation of sports-related injuries and other health issues.</p><p>Key services include:</p><ul><li>Regular health check-ups</li><li>Injury prevention workshops</li><li>Physical therapy and rehabilitation</li><li>Mental health counseling</li><li>Nutritional guidance</li></ul><p>Through partnerships with medical professionals and institutions, we ensure that our beneficiaries receive high-quality care tailored to their specific needs.</p>',
                    'ko_content' => '<p>저희 의료 지원 프로그램은 학생 및 학생선수를 위한 종합적인 의료 서비스를 제공합니다. 스포츠 관련 부상 및 기타 건강 문제의 예방, 진단, 치료 및 재활에 중점을 둡니다.</p><p>주요 서비스는 다음과 같습니다:</p><ul><li>정기 건강 검진</li><li>부상 예방 워크샵</li><li>물리 치료 및 재활</li><li>정신 건강 상담</li><li>영양 지도</li></ul><p>의료 전문가 및 기관과의 파트너십을 통해 저희는 수혜자들이 그들의 특정 요구에 맞춘 고품질 케어를 받을 수 있도록 보장합니다.</p>'
                ],
                [
                    'slug' => 'cultural-arts',
                    'title' => 'Cultural & Arts Education/Events',
                    'ko_title' => '문화·예술 교육 및 행사',
                    'description' => 'Fostering creativity and expression through cultural and arts programs.',
                    'ko_description' => '문화 및 예술 프로그램을 통한 창의성과 표현력 육성.',
                    'image' => 'https://images.unsplash.com/photo-1535982330050-f1c2fb79ff78?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
                    'content' => '<p>Our Cultural & Arts Education/Events program aims to enrich the lives of students and athletes through exposure to various forms of art and cultural activities. We believe that artistic expression is essential for holistic development and personal growth.</p><p>Program highlights include:</p><ul><li>Regular workshops in visual arts, music, dance, and theater</li><li>Cultural exchange programs</li><li>Art exhibitions showcasing student work</li><li>Performance opportunities</li><li>Field trips to museums, galleries, and cultural events</li></ul><p>Through these activities, participants develop creativity, self-expression, and an appreciation for cultural diversity, enhancing their overall educational experience.</p>',
                    'ko_content' => '<p>저희 문화 및 예술 교육/행사 프로그램은 다양한 형태의 예술 및 문화 활동을 통해 학생과 선수들의 삶을 풍요롭게 하는 것을 목표로 합니다. 예술적 표현은 전인적 발달과 개인 성장에 필수적이라고 믿습니다.</p><p>프로그램 하이라이트:</p><ul><li>시각 예술, 음악, 댄스, 연극 정기 워크샵</li><li>문화 교류 프로그램</li><li>학생 작품 전시회</li><li>공연 기회</li><li>박물관, 갤러리, 문화 행사 현장 학습</li></ul><p>이러한 활동을 통해 참가자들은 창의성, 자기표현, 문화적 다양성에 대한 이해를 발전시켜 전반적인 교육 경험을 향상시킵니다.</p>'
                ],
                [
                    'slug' => 'leadership',
                    'title' => 'Personal Development & Leadership Training',
                    'ko_title' => '자기개발·리더십 교육',
                    'description' => 'Building leadership skills and personal growth opportunities.',
                    'ko_description' => '리더십 기술 구축 및 개인 성장 기회 제공.',
                    'image' => 'https://images.unsplash.com/photo-1546512636-afb9dcf17149?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1171&q=80',
                    'content' => '<p>Our Personal Development & Leadership Training program is designed to help students and athletes develop the skills and mindset necessary for success both on and off the field. We focus on building confidence, resilience, and leadership abilities.</p><p>Key components include:</p><ul><li>Leadership workshops and seminars</li><li>Team-building activities</li><li>Communication skills training</li><li>Goal-setting and time management</li><li>Mentorship opportunities</li></ul><p>Participants emerge from our program with enhanced self-awareness, improved interpersonal skills, and a stronger ability to lead themselves and others effectively.</p>',
                    'ko_content' => '<p>저희 자기개발 및 리더십 교육 프로그램은 학생과 선수들이 경기장 안팎에서 성공하는 데 필요한 기술과 사고방식을 개발하도록 설계되었습니다. 자신감, 회복력, 리더십 능력 구축에 중점을 둡니다.</p><p>주요 구성 요소:</p><ul><li>리더십 워크샵 및 세미나</li><li>팀 빌딩 활동</li><li>의사소통 기술 훈련</li><li>목표 설정 및 시간 관리</li><li>멘토십 기회</li></ul><p>참가자들은 저희 프로그램을 통해 자기 인식을 강화하고, 대인 관계 기술을 향상시키며, 자신과 타인을 효과적으로 이끌 수 있는 능력을 키웁니다.</p>'
                ]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO `programs` (`slug`, `title`, `ko_title`, `description`, `ko_description`, `image`, `content`, `ko_content`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($programs as $program) {
                $stmt->execute([
                    $program['slug'], 
                    $program['title'], 
                    $program['ko_title'], 
                    $program['description'], 
                    $program['ko_description'],
                    $program['image'],
                    $program['content'],
                    $program['ko_content']
                ]);
            }
            
            // Create default gallery categories that match the front-end names
            $categories = [
                ['Foundation History', '재단 역사', 'Photos from our foundation history', '재단 역사의 사진'],
                ['Sports Medicine', '스포츠 의학', 'Sports medicine practices and facilities', '스포츠 의학 사진'],
                ['Athlete Support', '선수 지원', 'Our athlete support programs', '선수 지원 프로그램'],
                ['Seminars & Events', '세미나 및 행사', 'Seminars and events organized by the foundation', '재단이 주최한 세미나 및 행사']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO `gallery_categories` (`name`, `ko_name`, `description`, `ko_description`) VALUES (?, ?, ?, ?)");
            
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
            
            // Import default gallery images
            $defaultGalleryImages = [
                '1' => [ // Foundation History
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png',
                        'alt' => 'KOSMO Foundation History Timeline',
                        'caption' => 'Foundation establishment timeline'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png',
                        'alt' => 'KOSMO Foundation Organization Chart',
                        'caption' => 'Foundation organization structure'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg',
                        'alt' => 'Jincheon Athletes Village Opening Ceremony',
                        'caption' => 'Jincheon National Athletes Village opening ceremony'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg',
                        'alt' => 'Korean Sports Association Ceremony',
                        'caption' => 'Korean Sports Association 98th Anniversary Ceremony'
                    ]
                ],
                '2' => [ // Sports Medicine
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg',
                        'alt' => 'Sports Medicine Practice',
                        'caption' => 'Sports medicine professionals at work'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg',
                        'alt' => 'Medical Support Team',
                        'caption' => 'Medical support team for athletes'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg',
                        'alt' => 'Medical Equipment',
                        'caption' => 'State-of-the-art medical equipment'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg',
                        'alt' => 'Medical Consultation',
                        'caption' => 'Medical consultation for athletes'
                    ]
                ],
                '3' => [ // Athlete Support
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg',
                        'alt' => 'Korea-Japan Volleyball',
                        'caption' => 'Korea-Japan volleyball match'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png',
                        'alt' => 'Jakarta Asian Games',
                        'caption' => 'Jakarta Asian Games'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg',
                        'alt' => 'National Volleyball Team Doctor',
                        'caption' => 'National volleyball team with medical support'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png',
                        'alt' => 'Jakarta Asian Games 2',
                        'caption' => 'Medical support at Jakarta Asian Games'
                    ]
                ],
                '4' => [ // Seminars & Events
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg',
                        'alt' => 'Opening Ceremony 2',
                        'caption' => 'Foundation seminar and opening ceremony'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg',
                        'alt' => 'Opening Ceremony 3',
                        'caption' => 'Foundation inauguration event'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg',
                        'alt' => 'Korean Sports Association Ceremony 2',
                        'caption' => 'Sports association anniversary celebration'
                    ],
                    [
                        'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg',
                        'alt' => 'Medical Conference',
                        'caption' => 'Sports medicine conference and seminar'
                    ]
                ]
            ];
            
            // Insert default gallery images
            $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($defaultGalleryImages as $categoryId => $images) {
                foreach ($images as $image) {
                    $stmt->execute([
                        $categoryId,
                        $image['alt'],
                        $image['alt'], 
                        $image['caption'],
                        $image['caption'],
                        $image['src']
                    ]);
                }
            }
            
            return "Database setup completed successfully.";
        } else {
            return "Database already set up.";
        }
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

// Handle login
$error = '';
$message = '';
$view = 'login';

if (isset($_GET['action']) && $_GET['action'] == 'setup') {
    $message = setup_database();
}

// Process login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = connect_db();
        
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_name'] = $user['name'];
                    
                    // Update last login time
                    $stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    
                    $view = 'dashboard';
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again later.';
                error_log("Login error: " . $e->getMessage());
            }
        } else {
            $error = 'Database connection failed. Please try again later.';
        }
    }
}

// Check if logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $view = 'dashboard';
    
    // Handle logout
    if (isset($_GET['action']) && $_GET['action'] == 'logout') {
        session_destroy();
        header("Location: admin.php");
        exit;
    }
    
    // Handle view changes
    if (isset($_GET['view'])) {
        $allowed_views = ['dashboard', 'programs', 'program_edit', 'program_view', 'gallery', 'gallery_upload', 'profile', 'donations'];
        if (in_array($_GET['view'], $allowed_views)) {
            $view = $_GET['view'];
        }
    }
}

// Get program count for dashboard
$programCount = 0;
$imageCount = 0;

if ($view == 'dashboard') {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM programs");
        $programCount = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
        $imageCount = $stmt->fetchColumn();
    }
}

// Get programs for program list
$programs = [];
if ($view == 'programs') {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM programs ORDER BY id DESC");
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get program for editing
$program = null;
if ($view == 'program_edit' && isset($_GET['id'])) {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Process program updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_program'])) {
    $pdo = connect_db();
    if ($pdo) {
        $id = $_POST['id'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $title = $_POST['title'] ?? '';
        $ko_title = $_POST['ko_title'] ?? '';
        $description = $_POST['description'] ?? '';
        $ko_description = $_POST['ko_description'] ?? '';
        $image = $_POST['image'] ?? '';
        $content = $_POST['content'] ?? '';
        $ko_content = $_POST['ko_content'] ?? '';
        
        if (empty($id)) {
            // New program
            $stmt = $pdo->prepare("INSERT INTO programs (slug, title, ko_title, description, ko_description, image, content, ko_content) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$slug, $title, $ko_title, $description, $ko_description, $image, $content, $ko_content]);
            
            if ($result) {
                $message = "Program added successfully.";
                $view = 'programs';
            } else {
                $error = "Failed to add program.";
            }
        } else {
            // Update existing program
            $stmt = $pdo->prepare("UPDATE programs SET slug = ?, title = ?, ko_title = ?, description = ?, ko_description = ?, image = ?, content = ?, ko_content = ?, updated_at = NOW() WHERE id = ?");
            $result = $stmt->execute([$slug, $title, $ko_title, $description, $ko_description, $image, $content, $ko_content, $id]);
            
            if ($result) {
                $message = "Program updated successfully.";
                $view = 'programs';
            } else {
                $error = "Failed to update program.";
            }
        }
    }
}

// Delete program
if (isset($_GET['action']) && $_GET['action'] == 'delete_program' && isset($_GET['id'])) {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
        $result = $stmt->execute([$_GET['id']]);
        
        if ($result) {
            $message = "Program deleted successfully.";
        } else {
            $error = "Failed to delete program.";
        }
        $view = 'programs';
    }
}

// Get gallery categories
$categories = [];
if ($view == 'gallery') {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY id");
        $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure unique categories by name
        $processedNames = [];
        
        foreach ($allCategories as $cat) {
            if (!isset($processedNames[$cat['name']])) {
                $categories[] = $cat;
                $processedNames[$cat['name']] = true;
            }
        }
        
        // Get images for each category
        foreach ($categories as &$category) {
            $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE category_id = ? ORDER BY id DESC");
            $stmt->execute([$category['id']]);
            $category['images'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

// Process gallery image upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_image'])) {
    $pdo = connect_db();
    if ($pdo) {
        $category_id = $_POST['category_id'] ?? '';
        $title = $_POST['title'] ?? '';
        $ko_title = $_POST['ko_title'] ?? '';
        $description = $_POST['description'] ?? '';
        $ko_description = $_POST['ko_description'] ?? '';
        $image_url = $_POST['image_url'] ?? '';
        
        if (!empty($category_id) && !empty($image_url)) {
            $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$category_id, $title, $ko_title, $description, $ko_description, $image_url]);
            
            if ($result) {
                $message = "Image added successfully.";
                $view = 'gallery';
            } else {
                $error = "Failed to add image.";
            }
        } else {
            $error = "Category and image URL are required.";
        }
    }
}

// Add a new category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $pdo = connect_db();
    if ($pdo) {
        $name = $_POST['name'] ?? '';
        $ko_name = $_POST['ko_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $ko_description = $_POST['ko_description'] ?? '';
        
        if (!empty($name) && !empty($ko_name)) {
            $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$name, $ko_name, $description, $ko_description]);
            
            if ($result) {
                $message = "Category added successfully.";
                $view = 'gallery';
            } else {
                $error = "Failed to add category.";
            }
        } else {
            $error = "Category name is required in both languages.";
        }
    }
}

// Delete image
if (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['id'])) {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
        $result = $stmt->execute([$_GET['id']]);
        
        if ($result) {
            $message = "Image deleted successfully.";
        } else {
            $error = "Failed to delete image.";
        }
        $view = 'gallery';
    }
}

// Reset gallery to fix any issues
if (isset($_GET['action']) && $_GET['action'] == 'reset_gallery') {
    $pdo = connect_db();
    if ($pdo) {
        try {
            // Delete all gallery images and categories
            $pdo->exec("DELETE FROM gallery_images");
            $pdo->exec("DELETE FROM gallery_categories");
            $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
            
            // Create the four categories
            $categories = [
                ["Foundation History", "재단 역사", "Photos from our foundation history", "재단 역사의 사진"],
                ["Sports Medicine", "스포츠 의학", "Sports medicine practices and facilities", "스포츠 의학 사진"],
                ["Athlete Support", "선수 지원", "Our athlete support programs", "선수 지원 프로그램"],
                ["Seminars & Events", "세미나 및 행사", "Seminars and events organized by the foundation", "재단이 주최한 세미나 및 행사"]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
            
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
            
            // Get the newly created category IDs
            $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
            $categoryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $categoryIds = [];
            
            foreach ($categoryRows as $row) {
                $categoryIds[$row["name"]] = $row["id"];
            }
            
            // Import default gallery images
            $defaultGalleryImages = [
                $categoryIds["Foundation History"] => [ // Foundation History
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png",
                        "alt" => "KOSMO Foundation History Timeline",
                        "caption" => "Foundation establishment timeline"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png",
                        "alt" => "KOSMO Foundation Organization Chart",
                        "caption" => "Foundation organization structure"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg",
                        "alt" => "Jincheon Athletes Village Opening Ceremony",
                        "caption" => "Jincheon National Athletes Village opening ceremony"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg",
                        "alt" => "Korean Sports Association Ceremony",
                        "caption" => "Korean Sports Association 98th Anniversary Ceremony"
                    ]
                ],
                $categoryIds["Sports Medicine"] => [ // Sports Medicine
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg",
                        "alt" => "Sports Medicine Practice",
                        "caption" => "Sports medicine professionals at work"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg",
                        "alt" => "Medical Support Team",
                        "caption" => "Medical support team for athletes"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg",
                        "alt" => "Medical Equipment",
                        "caption" => "State-of-the-art medical equipment"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg",
                        "alt" => "Medical Consultation",
                        "caption" => "Medical consultation for athletes"
                    ]
                ],
                $categoryIds["Athlete Support"] => [ // Athlete Support
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg",
                        "alt" => "Korea-Japan Volleyball",
                        "caption" => "Korea-Japan volleyball match"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png",
                        "alt" => "Jakarta Asian Games",
                        "caption" => "Jakarta Asian Games"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg",
                        "alt" => "National Volleyball Team Doctor",
                        "caption" => "National volleyball team with medical support"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png",
                        "alt" => "Jakarta Asian Games 2",
                        "caption" => "Medical support at Jakarta Asian Games"
                    ]
                ],
                $categoryIds["Seminars & Events"] => [ // Seminars & Events
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg",
                        "alt" => "Opening Ceremony 2",
                        "caption" => "Foundation seminar and opening ceremony"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg",
                        "alt" => "Opening Ceremony 3",
                        "caption" => "Foundation inauguration event"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg",
                        "alt" => "Korean Sports Association Ceremony 2",
                        "caption" => "Sports association anniversary celebration"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg",
                        "alt" => "Medical Conference",
                        "caption" => "Sports medicine conference and seminar"
                    ]
                ]
            ];
            
            // Insert default gallery images
            $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($defaultGalleryImages as $categoryId => $images) {
                foreach ($images as $image) {
                    $stmt->execute([
                        $categoryId,
                        $image["alt"],
                        $image["alt"], 
                        $image["caption"],
                        $image["caption"],
                        $image["src"]
                    ]);
                }
            }
            
            $message = "Gallery has been reset successfully.";
        } catch (PDOException $e) {
            $error = "Failed to reset gallery: " . $e->getMessage();
        }
    }
    $view = 'gallery';
}

// Delete category
if (isset($_GET['action']) && $_GET['action'] == 'delete_category' && isset($_GET['id'])) {
    $pdo = connect_db();
    if ($pdo) {
        // First delete all images in this category
        $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE category_id = ?");
        $stmt->execute([$_GET['id']]);
        
        // Then delete the category
        $stmt = $pdo->prepare("DELETE FROM gallery_categories WHERE id = ?");
        $result = $stmt->execute([$_GET['id']]);
        
        if ($result) {
            $message = "Category and its images deleted successfully.";
        } else {
            $error = "Failed to delete category.";
        }
        $view = 'gallery';
    }
}

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $pdo = connect_db();
    if ($pdo) {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($name) || empty($email)) {
            $error = "Name and email are required.";
        } else {
            // Update name and email
            $stmt = $pdo->prepare("UPDATE admin_users SET name = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([$name, $email, $_SESSION['admin_id']]);
            
            if ($result) {
                $_SESSION['admin_name'] = $name;
                $message = "Profile updated successfully.";
                
                // Check if password needs to be updated
                if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
                    if ($new_password !== $confirm_password) {
                        $error = "New passwords do not match.";
                    } else {
                        // Verify current password
                        $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = ?");
                        $stmt->execute([$_SESSION['admin_id']]);
                        $user = $stmt->fetch();
                        
                        if (password_verify($current_password, $user['password'])) {
                            // Update password
                            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                            $result = $stmt->execute([$new_password_hash, $_SESSION['admin_id']]);
                            
                            if ($result) {
                                $message .= " Password updated successfully.";
                            } else {
                                $error = "Failed to update password.";
                            }
                        } else {
                            $error = "Current password is incorrect.";
                        }
                    }
                }
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

// Synchronize gallery images from front-end to database if needed
function sync_gallery_with_frontend() {
    $pdo = connect_db();
    if (!$pdo) return "";
    
    try {
        // Check if we need to reset gallery categories
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_categories");
        $categoryCount = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
        $imageCount = $stmt->fetchColumn();
        
        if ($categoryCount < 4 || $imageCount == 0) {
            // Delete all existing images and categories for a fresh start
            $pdo->exec("DELETE FROM gallery_images");
            $pdo->exec("DELETE FROM gallery_categories");
            $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
            $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
            
            // Create default gallery categories
            $categories = [
                ["Foundation History", "재단 역사", "Photos from our foundation history", "재단 역사의 사진"],
                ["Sports Medicine", "스포츠 의학", "Sports medicine practices and facilities", "스포츠 의학 사진"],
                ["Athlete Support", "선수 지원", "Our athlete support programs", "선수 지원 프로그램"],
                ["Seminars & Events", "세미나 및 행사", "Seminars and events organized by the foundation", "재단이 주최한 세미나 및 행사"]
            ];
            
            $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
            
            foreach ($categories as $category) {
                $stmt->execute($category);
            }
            
            // Get the newly created category IDs
            $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
            $categoryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $categoryIds = [];
            
            foreach ($categoryRows as $row) {
                $categoryIds[$row["name"]] = $row["id"];
            }
            
            // Import default gallery images
            $defaultGalleryImages = [
                $categoryIds["Foundation History"] => [ // Foundation History
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png",
                        "alt" => "KOSMO Foundation History Timeline",
                        "caption" => "Foundation establishment timeline"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png",
                        "alt" => "KOSMO Foundation Organization Chart",
                        "caption" => "Foundation organization structure"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg",
                        "alt" => "Jincheon Athletes Village Opening Ceremony",
                        "caption" => "Jincheon National Athletes Village opening ceremony"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg",
                        "alt" => "Korean Sports Association Ceremony",
                        "caption" => "Korean Sports Association 98th Anniversary Ceremony"
                    ]
                ],
                $categoryIds["Sports Medicine"] => [ // Sports Medicine
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg",
                        "alt" => "Sports Medicine Practice",
                        "caption" => "Sports medicine professionals at work"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg",
                        "alt" => "Medical Support Team",
                        "caption" => "Medical support team for athletes"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg",
                        "alt" => "Medical Equipment",
                        "caption" => "State-of-the-art medical equipment"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg",
                        "alt" => "Medical Consultation",
                        "caption" => "Medical consultation for athletes"
                    ]
                ],
                $categoryIds["Athlete Support"] => [ // Athlete Support
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg",
                        "alt" => "Korea-Japan Volleyball",
                        "caption" => "Korea-Japan volleyball match"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png",
                        "alt" => "Jakarta Asian Games",
                        "caption" => "Jakarta Asian Games"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg",
                        "alt" => "National Volleyball Team Doctor",
                        "caption" => "National volleyball team with medical support"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png",
                        "alt" => "Jakarta Asian Games 2",
                        "caption" => "Medical support at Jakarta Asian Games"
                    ]
                ],
                $categoryIds["Seminars & Events"] => [ // Seminars & Events
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg",
                        "alt" => "Opening Ceremony 2",
                        "caption" => "Foundation seminar and opening ceremony"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg",
                        "alt" => "Opening Ceremony 3",
                        "caption" => "Foundation inauguration event"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg",
                        "alt" => "Korean Sports Association Ceremony 2",
                        "caption" => "Sports association anniversary celebration"
                    ],
                    [
                        "src" => "http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg",
                        "alt" => "Medical Conference",
                        "caption" => "Sports medicine conference and seminar"
                    ]
                ]
            ];
            
            // Insert default gallery images
            $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($defaultGalleryImages as $categoryId => $images) {
                foreach ($images as $image) {
                    $stmt->execute([
                        $categoryId,
                        $image["alt"],
                        $image["alt"], 
                        $image["caption"],
                        $image["caption"],
                        $image["src"]
                    ]);
                }
            }
            
            return "Gallery synchronized with front-end.";
        }
        
        return "";
    } catch (PDOException $e) {
        error_log("Gallery sync error: " . $e->getMessage());
        return "Error synchronizing gallery: " . $e->getMessage();
    }
}

// Run synchronization when viewing the gallery
if ($view == 'gallery') {
    $syncResult = sync_gallery_with_frontend();
    if (!empty($syncResult)) {
        $message = $syncResult;
    }
}

// Get user profile
$user = null;
if ($view == 'profile') {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, username, name, email, created_at, last_login FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOSMO Foundation Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap');
        body {
            font-family: 'Open Sans', 'Noto Sans KR', sans-serif;
        }
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#4d9aff',
                        accent: '#ff6b00',
                    }
                }
            }
        }
    </script>
    
    <?php if ($view == 'program_edit'): ?>
    <!-- CKEditor 5 Rich Text Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Initialize CKEditor for content in English
        if (document.getElementById('content-container')) {
          var contentInput = document.getElementById('content');
          
          ClassicEditor
            .create(document.querySelector('#content-container'), {
              toolbar: [
                'heading', '|', 
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 
                'indent', 'outdent', '|', 
                'blockQuote', 'insertTable', 'undo', 'redo'
              ]
            })
            .then(editor => {
              // Set initial content
              if (contentInput.value) {
                editor.setData(contentInput.value);
              }
              
              // Update hidden input on form submit
              editor.model.document.on('change:data', () => {
                contentInput.value = editor.getData();
              });
            })
            .catch(error => {
              console.error(error);
            });
        }

        // Initialize CKEditor for content in Korean
        if (document.getElementById('ko-content-container')) {
          var koContentInput = document.getElementById('ko_content');
          
          ClassicEditor
            .create(document.querySelector('#ko-content-container'), {
              toolbar: [
                'heading', '|', 
                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 
                'indent', 'outdent', '|', 
                'blockQuote', 'insertTable', 'undo', 'redo'
              ]
            })
            .then(editor => {
              // Set initial content
              if (koContentInput.value) {
                editor.setData(koContentInput.value);
              }
              
              // Update hidden input on form submit
              editor.model.document.on('change:data', () => {
                koContentInput.value = editor.getData();
              });
            })
            .catch(error => {
              console.error(error);
            });
        }
      });
    </script>
    <?php endif; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php if ($view == 'login'): ?>
    <!-- Login Page -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-primary mb-2">KOSMO Foundation</h1>
                <p class="text-gray-600">Admin Login</p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $message; ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <button type="submit" name="login" class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-2 px-4 rounded-md focus:outline-none">Log In</button>
                </div>
            </form>
            
            <div class="mt-8 text-center text-sm text-gray-600">
                <p>Default credentials: admin / admin123</p>
                <p class="mt-4">Return to <a href="index.php" class="text-primary hover:underline">Website</a></p>
                <p class="mt-2"><a href="admin.php?action=setup" class="text-primary hover:underline">Run Database Setup</a></p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Admin Dashboard Layout -->
    <header class="bg-primary text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="admin.php" class="text-2xl font-bold hover:text-gray-200">KOSMO Foundation Admin</a>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="admin.php?action=logout" class="bg-white text-primary hover:bg-gray-100 px-3 py-1 rounded text-sm">Logout</a>
            </div>
        </div>
    </header>

    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8 overflow-x-auto">
                <li><a href="admin.php?view=dashboard" class="inline-block py-4 <?php echo $view == 'dashboard' ? 'text-primary border-b-2 border-primary font-medium' : 'text-gray-500 hover:text-primary'; ?>">Dashboard</a></li>
                <li><a href="admin.php?view=programs" class="inline-block py-4 <?php echo $view == 'programs' || $view == 'program_edit' || $view == 'program_view' ? 'text-primary border-b-2 border-primary font-medium' : 'text-gray-500 hover:text-primary'; ?>">Programs</a></li>
                <li><a href="admin.php?view=gallery" class="inline-block py-4 <?php echo $view == 'gallery' || $view == 'gallery_upload' ? 'text-primary border-b-2 border-primary font-medium' : 'text-gray-500 hover:text-primary'; ?>">Gallery</a></li>
                <li><a href="admin.php?view=donations" class="inline-block py-4 <?php echo $view == 'donations' ? 'text-primary border-b-2 border-primary font-medium' : 'text-gray-500 hover:text-primary'; ?>">Donations</a></li>
        <li><a href="admin.php?view=profile" class="inline-block py-4 <?php echo $view == 'profile' ? 'text-primary border-b-2 border-primary font-medium' : 'text-gray-500 hover:text-primary'; ?>">Profile</a></li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $message; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($view == 'dashboard'): ?>
        <!-- Dashboard Content -->
        <h2 class="text-2xl font-bold mb-6">Dashboard</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-2">Programs</h3>
                <p class="text-3xl font-bold text-primary"><?php echo $programCount; ?></p>
                <a href="admin.php?view=programs" class="mt-4 inline-block text-primary hover:underline">Manage Programs →</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-2">Gallery Images</h3>
                <p class="text-3xl font-bold text-primary"><?php echo $imageCount; ?></p>
                <a href="admin.php?view=gallery" class="mt-4 inline-block text-primary hover:underline">Manage Gallery →</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-2">Website</h3>
                <p class="text-base text-gray-600 mb-4">View and manage the public-facing website.</p>
                <a href="index.php" target="_blank" class="mt-2 inline-block text-primary hover:underline">Visit Website →</a>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="admin.php?view=program_edit" class="bg-primary text-white hover:bg-primary/90 p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Program
                </a>
                <a href="admin.php?view=gallery" class="bg-secondary text-white hover:bg-secondary/90 p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Manage Gallery
                </a>
                <a href="admin.php?view=programs" class="bg-accent text-white hover:bg-accent/90 p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    View All Programs
                </a>
                <a href="admin.php?view=profile" class="bg-gray-500 text-white hover:bg-gray-600 p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile Settings
                </a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-4">Recent Updates</h3>
                <ul class="space-y-3">
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-5 w-5 rounded-full bg-primary flex items-center justify-center mt-1">
                            <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-700">Enhanced admin panel with simplified interface</p>
                            <p class="text-xs text-gray-500">1 hour ago</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-5 w-5 rounded-full bg-primary flex items-center justify-center mt-1">
                            <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-700">Added rich text editor for program content</p>
                            <p class="text-xs text-gray-500">2 hours ago</p>
                        </div>
                    </li>
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-5 w-5 rounded-full bg-primary flex items-center justify-center mt-1">
                            <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-700">Improved gallery management functionality</p>
                            <p class="text-xs text-gray-500">3 hours ago</p>
                        </div>
                    </li>
                </ul>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-xl font-semibold mb-4">System Information</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-medium">PHP Version</p>
                        <p class="text-gray-600"><?php echo phpversion(); ?></p>
                    </div>
                    <div>
                        <p class="font-medium">Server</p>
                        <p class="text-gray-600"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                    </div>
                    <div>
                        <p class="font-medium">Last Login</p>
                        <p class="text-gray-600"><?php echo date('Y-m-d H:i:s'); ?></p>
                    </div>
                    <div>
                        <p class="font-medium">Database</p>
                        <p class="text-gray-600">MySQL <?php echo $pdo ? $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) : 'Not connected'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php elseif ($view == 'programs'): ?>
        <!-- Programs List -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Programs</h2>
            <a href="admin.php?view=program_edit" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded">Add New Program</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Korean Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($programs)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No programs found. <a href="admin.php?view=program_edit" class="text-primary hover:underline">Add a new program</a>.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($programs as $p): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($p['ko_title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($p['slug']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo $p['updated_at'] ? date('Y-m-d H:i', strtotime($p['updated_at'])) : date('Y-m-d H:i', strtotime($p['created_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="admin.php?view=program_edit&id=<?php echo $p['id']; ?>" class="text-primary hover:underline mr-3">Edit</a>
                                <a href="program.php?slug=<?php echo $p['slug']; ?>" target="_blank" class="text-green-600 hover:underline mr-3">View</a>
                                <a href="admin.php?view=programs&action=delete_program&id=<?php echo $p['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this program?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php elseif ($view == 'program_edit'): ?>
        <!-- Program Edit Form -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold"><?php echo isset($_GET['id']) ? 'Edit Program' : 'Add New Program'; ?></h2>
            <a href="admin.php?view=programs" class="text-primary hover:underline">← Back to Programs</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6">
                <?php if (isset($_GET['id'])): ?>
                <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title (English)</label>
                        <input type="text" id="title" name="title" value="<?php echo isset($program) ? htmlspecialchars($program['title']) : ''; ?>" class="w-full px-4 py-2 border rounded-md" required>
                    </div>
                    
                    <div>
                        <label for="ko_title" class="block text-sm font-medium text-gray-700 mb-1">Title (Korean)</label>
                        <input type="text" id="ko_title" name="ko_title" value="<?php echo isset($program) ? htmlspecialchars($program['ko_title']) : ''; ?>" class="w-full px-4 py-2 border rounded-md" required>
                    </div>
                </div>
                
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL identifier)</label>
                    <input type="text" id="slug" name="slug" value="<?php echo isset($program) ? htmlspecialchars($program['slug']) : ''; ?>" class="w-full px-4 py-2 border rounded-md" required pattern="[a-z0-9-]+" title="Only lowercase letters, numbers, and hyphens are allowed">
                    <p class="mt-1 text-xs text-gray-500">Used in URLs. Only lowercase letters, numbers, and hyphens.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Short Description (English)</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-md"><?php echo isset($program) ? htmlspecialchars($program['description']) : ''; ?></textarea>
                    </div>
                    
                    <div>
                        <label for="ko_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description (Korean)</label>
                        <textarea id="ko_description" name="ko_description" rows="3" class="w-full px-4 py-2 border rounded-md"><?php echo isset($program) ? htmlspecialchars($program['ko_description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image URL</label>
                    <input type="url" id="image" name="image" value="<?php echo isset($program) ? htmlspecialchars($program['image']) : ''; ?>" class="w-full px-4 py-2 border rounded-md">
                    <?php if (isset($program) && $program['image']): ?>
                    <div class="mt-2">
                        <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="Featured image" class="h-40 rounded">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English)</label>
                    <textarea id="content-container" class="border rounded-md"></textarea>
                    <input type="hidden" id="content" name="content" value="<?php echo isset($program) ? htmlspecialchars($program['content']) : ''; ?>">
                </div>
                
                <div>
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean)</label>
                    <textarea id="ko-content-container" class="border rounded-md"></textarea>
                    <input type="hidden" id="ko_content" name="ko_content" value="<?php echo isset($program) ? htmlspecialchars($program['ko_content']) : ''; ?>">
                </div>
                
                <div class="flex justify-end">
                    <a href="admin.php?view=programs" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded-md mr-3">Cancel</a>
                    <button type="submit" name="save_program" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-md">Save Program</button>
                </div>
            </form>
        </div>
        
        <?php elseif ($view == 'gallery'): ?>
        <!-- Gallery Management -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Gallery Management</h2>
                <a href="admin.php?view=gallery&action=reset_gallery" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm ml-2" onclick="return confirm('Are you sure you want to reset the gallery? This will delete all current gallery data and restore the default categories and images.');">Reset Gallery</a>
            <button type="button" onclick="document.getElementById('add-category-modal').classList.remove('hidden')" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded">Add New Category</button>
        </div>
        
        <div class="space-y-8">
            <?php if (empty($categories)): ?>
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500 mb-4">No gallery categories found.</p>
                <button type="button" onclick="document.getElementById('add-category-modal').classList.remove('hidden')" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded">Create First Category</button>
            </div>
            <?php else: ?>
                <?php 
                // De-duplicate categories by name
                $uniqueCategories = [];
                $processedNames = [];
                
                foreach ($categories as $cat) {
                    if (!isset($processedNames[$cat['name']])) {
                        $uniqueCategories[] = $cat;
                        $processedNames[$cat['name']] = true;
                    }
                }
                
                foreach ($uniqueCategories as $category): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($category['name']); ?> (<?php echo htmlspecialchars($category['ko_name']); ?>)</h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($category['description']); ?></p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="document.getElementById('upload-modal-<?php echo $category['id']; ?>').classList.remove('hidden')" class="bg-secondary hover:bg-secondary/90 text-white px-3 py-1 rounded text-sm">Add Image</button>
                            <a href="admin.php?view=gallery&action=delete_category&id=<?php echo $category['id']; ?>" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Are you sure you want to delete this category and all its images?')">Delete Category</a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <?php if (empty($category['images'])): ?>
                        <p class="text-gray-500 text-center">No images in this category.</p>
                        <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($category['images'] as $image): ?>
                            <div class="border rounded-lg overflow-hidden group">
                                <div class="relative h-48 bg-gray-200">
                                    <img src="<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <a href="admin.php?view=gallery&action=delete_image&id=<?php echo $image['id']; ?>" class="text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded" onclick="return confirm('Are you sure you want to delete this image?')">Delete</a>
                                    </div>
                                </div>
                                <div class="p-3">
                                    <h4 class="font-medium"><?php echo htmlspecialchars($image['title']); ?></h4>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($image['ko_title']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Upload Image Modal -->
                    <div id="upload-modal-<?php echo $category['id']; ?>" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-bold">Add Image to <?php echo htmlspecialchars($category['name']); ?></h3>
                                <button type="button" onclick="document.getElementById('upload-modal-<?php echo $category['id']; ?>').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            
                            <form method="POST" class="space-y-4">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                
                                <div>
                                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                                    <input type="url" id="image_url" name="image_url" class="w-full px-4 py-2 border rounded-md" required>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title (English)</label>
                                        <input type="text" id="title" name="title" class="w-full px-4 py-2 border rounded-md">
                                    </div>
                                    
                                    <div>
                                        <label for="ko_title" class="block text-sm font-medium text-gray-700 mb-1">Title (Korean)</label>
                                        <input type="text" id="ko_title" name="ko_title" class="w-full px-4 py-2 border rounded-md">
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                                        <textarea id="description" name="description" rows="2" class="w-full px-4 py-2 border rounded-md"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label for="ko_description" class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                                        <textarea id="ko_description" name="ko_description" rows="2" class="w-full px-4 py-2 border rounded-md"></textarea>
                                    </div>
                                </div>
                                
                                <div class="flex justify-end space-x-3">
                                    <button type="button" onclick="document.getElementById('upload-modal-<?php echo $category['id']; ?>').classList.add('hidden')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancel</button>
                                    <button type="submit" name="upload_image" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded">Upload Image</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Add Category Modal -->
        <div id="add-category-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Add New Category</h3>
                    <button type="button" onclick="document.getElementById('add-category-modal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name (English)</label>
                            <input type="text" id="name" name="name" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="ko_name" class="block text-sm font-medium text-gray-700 mb-1">Name (Korean)</label>
                            <input type="text" id="ko_name" name="ko_name" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                            <textarea id="description" name="description" rows="2" class="w-full px-4 py-2 border rounded-md"></textarea>
                        </div>
                        
                        <div>
                            <label for="ko_description" class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                            <textarea id="ko_description" name="ko_description" rows="2" class="w-full px-4 py-2 border rounded-md"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('add-category-modal').classList.add('hidden')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">Cancel</button>
                        <button type="submit" name="add_category" class="bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php elseif ($view == 'donations'): ?>
        <!-- Donation Settings -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Donation Settings</h2>
            <a href="admin.php?view=dashboard" class="text-primary hover:underline">← Back to Dashboard</a>
        </div>
        
        <?php
        // Fetch donation settings
        $donationSettings = null;
        $pdo = connect_db();
        if ($pdo) {
            $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
            $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Process donation settings update
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_donation_settings'])) {
            $bankName = $_POST['bank_name'] ?? '';
            $accountNumber = $_POST['account_number'] ?? '';
            $accountHolder = $_POST['account_holder'] ?? '';
            $businessNumber = $_POST['business_number'] ?? '';
            $kakaopayEnabled = isset($_POST['kakaopay_enabled']) ? 1 : 0;
            $bankTransferEnabled = isset($_POST['bank_transfer_enabled']) ? 1 : 0;
            $minDonationAmount = $_POST['min_donation_amount'] ?? 1000;
            $defaultAmount = $_POST['default_amount'] ?? 50000;
            
            if (empty($bankName) || empty($accountNumber) || empty($accountHolder) || empty($businessNumber)) {
                $error = "All bank details are required.";
            } else {
                $stmt = $pdo->prepare("UPDATE donation_settings SET 
                    bank_name = ?, 
                    account_number = ?, 
                    account_holder = ?, 
                    business_number = ?, 
                    kakaopay_enabled = ?, 
                    bank_transfer_enabled = ?, 
                    min_donation_amount = ?, 
                    default_amount = ?");
                
                $result = $stmt->execute([
                    $bankName,
                    $accountNumber,
                    $accountHolder,
                    $businessNumber,
                    $kakaopayEnabled,
                    $bankTransferEnabled,
                    $minDonationAmount,
                    $defaultAmount
                ]);
                
                if ($result) {
                    $message = "Donation settings updated successfully.";
                    // Refresh donation settings
                    $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
                    $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = "Failed to update donation settings.";
                }
            }
        }
        ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6">
                <!-- Bank Information Section -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Bank Transfer Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="bank_name" class="block text-gray-700 font-medium mb-2">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($donationSettings['bank_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="account_number" class="block text-gray-700 font-medium mb-2">Account Number</label>
                            <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($donationSettings['account_number'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="account_holder" class="block text-gray-700 font-medium mb-2">Account Holder</label>
                            <input type="text" id="account_holder" name="account_holder" value="<?php echo htmlspecialchars($donationSettings['account_holder'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="business_number" class="block text-gray-700 font-medium mb-2">Business Registration Number</label>
                            <input type="text" id="business_number" name="business_number" value="<?php echo htmlspecialchars($donationSettings['business_number'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods Section -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Payment Methods</h3>
                    
                    <div class="flex flex-col space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="kakaopay_enabled" <?php echo ($donationSettings['kakaopay_enabled'] ?? 1) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-primary">
                            <span class="ml-2 text-gray-700">Enable KakaoPay</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="bank_transfer_enabled" <?php echo ($donationSettings['bank_transfer_enabled'] ?? 1) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-primary">
                            <span class="ml-2 text-gray-700">Enable Bank Transfer</span>
                        </label>
                    </div>
                </div>
                
                <!-- Donation Amount Settings -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Donation Amount Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="min_donation_amount" class="block text-gray-700 font-medium mb-2">Minimum Donation Amount (₩)</label>
                            <input type="number" id="min_donation_amount" name="min_donation_amount" value="<?php echo htmlspecialchars($donationSettings['min_donation_amount'] ?? 1000); ?>" min="0" class="w-full px-4 py-2 border rounded-md">
                        </div>
                        
                        <div>
                            <label for="default_amount" class="block text-gray-700 font-medium mb-2">Default Donation Amount (₩)</label>
                            <input type="number" id="default_amount" name="default_amount" value="<?php echo htmlspecialchars($donationSettings['default_amount'] ?? 50000); ?>" min="0" class="w-full px-4 py-2 border rounded-md">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="save_donation_settings" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-md">Save Settings</button>
                </div>
            </form>
        </div>
        
        <?php elseif ($view == 'profile'): ?>
        <!-- Profile Settings -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Profile Settings</h2>
            <a href="admin.php?view=dashboard" class="text-primary hover:underline">← Back to Dashboard</a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-4 py-2 border rounded-md bg-gray-100" readonly>
                        <p class="mt-1 text-xs text-gray-500">Username cannot be changed.</p>
                    </div>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full px-4 py-2 border rounded-md" required>
                    </div>
                </div>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-2 border rounded-md" required>
                </div>
                
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium mb-4">Change Password</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border rounded-md">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border rounded-md">
                            </div>
                            
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-md">
                            </div>
                        </div>
                        
                        <p class="text-xs text-gray-500">Leave password fields empty if you don't want to change your password.</p>
                    </div>
                </div>
                
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium mb-4">Account Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <p class="font-medium text-gray-700">Account Created</p>
                            <p class="text-gray-600"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                        </div>
                        
                        <div>
                            <p class="font-medium text-gray-700">Last Login</p>
                            <p class="text-gray-600"><?php echo $user['last_login'] ? date('F j, Y g:i a', strtotime($user['last_login'])) : 'Never'; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="update_profile" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-md">Save Changes</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>

    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
    <?php endif; ?>
</body>
</html>