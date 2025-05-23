<?php
// This script will fix the admin panel's gallery functionality by:
// 1. Checking and correcting the gallery category IDs
// 2. Ensuring all gallery images are properly linked

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

// Connect to database
$pdo = connect_db();

if (!$pdo) {
    echo "Failed to connect to database.";
    exit;
}

echo "<h1>Gallery Sync Repair Tool</h1>";

try {
    // Step 1: Check if tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'gallery_categories'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        echo "<p>Gallery tables do not exist. Please run the database setup first.</p>";
        exit;
    }
    
    // Step 2: Clear all existing gallery data for a fresh start
    echo "<p>Clearing existing gallery data...</p>";
    
    $pdo->exec("DELETE FROM gallery_images");
    $pdo->exec("DELETE FROM gallery_categories");
    
    // Step 3: Create the correct gallery categories
    echo "<p>Creating gallery categories...</p>";
    
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
    
    // Step 4: Get the newly created categories
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Created categories:</p><ul>";
    foreach ($categories as $category) {
        echo "<li>ID: {$category['id']} - Name: {$category['name']}</li>";
    }
    echo "</ul>";
    
    // Step 5: Map category names to IDs
    $categoryIds = [];
    foreach ($categories as $category) {
        switch ($category['name']) {
            case 'Foundation History':
                $categoryIds['foundation'] = $category['id'];
                break;
            case 'Sports Medicine':
                $categoryIds['medicine'] = $category['id'];
                break;
            case 'Athlete Support':
                $categoryIds['athlete'] = $category['id'];
                break;
            case 'Seminars & Events':
                $categoryIds['seminars'] = $category['id'];
                break;
        }
    }
    
    // Step 6: Import gallery images with correct category IDs
    echo "<p>Importing gallery images...</p>";
    
    $defaultGalleryImages = [
        $categoryIds['foundation'] => [ // Foundation History
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
        $categoryIds['medicine'] => [ // Sports Medicine
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
        $categoryIds['athlete'] => [ // Athlete Support
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
        $categoryIds['seminars'] => [ // Seminars & Events
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
    
    // Insert gallery images
    $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
    
    $imageCount = 0;
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
            $imageCount++;
        }
    }
    
    echo "<p>Added $imageCount images to the gallery.</p>";
    
    // Fix the admin.php by removing the duplicate sync_gallery_with_frontend function
    // This step is now handled in a separate script
    
    echo "<p><strong>Gallery repair completed successfully!</strong></p>";
    echo "<p><a href='admin.php?view=gallery'>Return to Admin Panel</a></p>";
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>