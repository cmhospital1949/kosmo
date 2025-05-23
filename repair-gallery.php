<?php
// Database connection
require_once __DIR__ . '/lib/Database.php';

function connect_db() {
    try {
        return Database::getConnection();
    } catch (PDOException $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Start repair process
echo "<h1>Gallery Repair Process</h1>";
echo "<p>Starting gallery database repair...</p>";

$pdo = connect_db();

try {
    // Begin transaction to ensure all changes happen together
    $pdo->beginTransaction();
    
    // Step 1: Drop existing gallery tables
    echo "<p>Dropping existing gallery tables...</p>";
    $pdo->exec("DELETE FROM gallery_images");
    $pdo->exec("DELETE FROM gallery_categories");
    
    // Step 2: Create new gallery categories with correct IDs that match the front-end expectations
    echo "<p>Creating new gallery categories with correct IDs...</p>";
    
    // Define categories with specified IDs
    $categories = [
        ['Foundation History', '재단 역사', 'Photos from our foundation history', '재단 역사의 사진'],
        ['Sports Medicine', '스포츠 의학', 'Sports medicine practices and facilities', '스포츠 의학 사진'],
        ['Athlete Support', '선수 지원', 'Our athlete support programs', '선수 지원 프로그램'],
        ['Seminars & Events', '세미나 및 행사', 'Seminars and events organized by the foundation', '재단이 주최한 세미나 및 행사']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
    
    foreach ($categories as $category) {
        $stmt->execute($category);
        echo "<p>Created category: " . htmlspecialchars($category[0]) . " / " . htmlspecialchars($category[1]) . "</p>";
    }
    
    // Step 3: Add default images to the categories
    echo "<p>Adding default images to categories...</p>";
    
    // Default images data organized by category
    $defaultImages = [
        // Category 1: Foundation History
        [
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
        // Category 2: Sports Medicine
        [
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
        // Category 3: Athlete Support
        [
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
        // Category 4: Seminars & Events
        [
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
    
    // Prepare statement for inserting images
    $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Get the newly created category IDs
    $stmtCategory = $pdo->query("SELECT id FROM gallery_categories ORDER BY id");
    $categoryIds = $stmtCategory->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert images for each category using the correct category IDs
    foreach ($categoryIds as $index => $categoryId) {
        if (isset($defaultImages[$index])) {
            foreach ($defaultImages[$index] as $image) {
                $stmt->execute([
                    $categoryId,
                    $image['alt'],
                    $image['alt'],
                    $image['caption'],
                    $image['caption'],
                    $image['src']
                ]);
                echo "<p>Added image '" . htmlspecialchars($image['alt']) . "' to category #" . $categoryId . "</p>";
            }
        }
    }
    
    // Commit the transaction
    $pdo->commit();
    echo "<p style='color: green; font-weight: bold;'>Gallery repair completed successfully!</p>";
    echo "<p><a href='admin.php?view=gallery'>Return to Gallery Management</a></p>";
    
} catch (PDOException $e) {
    // Roll back the transaction if something failed
    $pdo->rollback();
    echo "<p style='color: red; font-weight: bold;'>Error during repair: " . $e->getMessage() . "</p>";
}
?>
