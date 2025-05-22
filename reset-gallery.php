<?php
// This script fixes the gallery by completely rebuilding it

// Connect to the database
$host = 'localhost';
$dbname = 'bestluck';
$username = 'bestluck';
$password = 'Nocpriss12!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Gallery Reset Tool</h1>";
    
    // Step 1: Delete all gallery data
    $pdo->exec("DELETE FROM gallery_images");
    $pdo->exec("DELETE FROM gallery_categories");
    $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
    
    echo "<p>Deleted all existing gallery data</p>";
    
    // Step 2: Create the four categories
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
    
    echo "<p>Created new categories:</p>";
    echo "<ul>";
    foreach ($categories as $index => $category) {
        echo "<li>#{$index}: {$category[0]} - {$category[1]}</li>";
    }
    echo "</ul>";
    
    // Step 3: Get the newly created category IDs
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
    $categoryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>New category IDs:</p>";
    echo "<ul>";
    foreach ($categoryRows as $row) {
        echo "<li>ID: {$row['id']} - {$row['name']}</li>";
    }
    echo "</ul>";
    
    // Step 4: Create gallery images with the correct category IDs
    $defaultGalleryImages = [
        $categoryRows[0]['id'] => [ // Foundation History
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/히스토리1-1024x586.png",
                "alt" => "KOSMO Foundation History Timeline",
                "caption" => "Foundation establishment timeline"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/조직도4_230316.png",
                "alt" => "KOSMO Foundation Organization Chart",
                "caption" => "Foundation organization structure"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg",
                "alt" => "Jincheon Athletes Village Opening Ceremony",
                "caption" => "Jincheon National Athletes Village opening ceremony"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg",
                "alt" => "Korean Sports Association Ceremony",
                "caption" => "Korean Sports Association 98th Anniversary Ceremony"
            ]
        ],
        $categoryRows[1]['id'] => [ // Sports Medicine
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/IMG_3319-scaled.jpg",
                "alt" => "Sports Medicine Practice",
                "caption" => "Sports medicine professionals at work"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/IMG_3354-scaled.jpg",
                "alt" => "Medical Support Team",
                "caption" => "Medical support team for athletes"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/IMG_3258-scaled.jpg",
                "alt" => "Medical Equipment",
                "caption" => "State-of-the-art medical equipment"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/IMG_3370-scaled.jpg",
                "alt" => "Medical Consultation",
                "caption" => "Medical consultation for athletes"
            ]
        ],
        $categoryRows[2]['id'] => [ // Athlete Support
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/한일배구.jpg",
                "alt" => "Korea-Japan Volleyball",
                "caption" => "Korea-Japan volleyball match"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임.png",
                "alt" => "Jakarta Asian Games",
                "caption" => "Jakarta Asian Games"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg",
                "alt" => "National Volleyball Team Doctor",
                "caption" => "National volleyball team with medical support"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임-2.png",
                "alt" => "Jakarta Asian Games 2",
                "caption" => "Medical support at Jakarta Asian Games"
            ]
        ],
        $categoryRows[3]['id'] => [ // Seminars & Events
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/개촌식2.jpg",
                "alt" => "Opening Ceremony 2",
                "caption" => "Foundation seminar and opening ceremony"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/개촌식3.jpg",
                "alt" => "Opening Ceremony 3",
                "caption" => "Foundation inauguration event"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/대한체육-기념식-2.jpg",
                "alt" => "Korean Sports Association Ceremony 2",
                "caption" => "Sports association anniversary celebration"
            ],
            [
                "src" => "http://kosmo.center/wp-content/uploads/2023/03/170586_151041_2238.jpg",
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
    
    echo "<p>Added $imageCount images to the gallery</p>";
    
    // Check the final state of the gallery
    $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_categories");
    $categoryCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
    $imageCount = $stmt->fetchColumn();
    
    echo "<p>Final state: $categoryCount categories and $imageCount images</p>";
    
    echo "<p>Gallery has been reset successfully!</p>";
    echo "<p><a href='gallery.php?lang=en'>Check the front-end gallery</a> | <a href='admin.php?view=gallery'>Check the admin gallery</a></p>";
    
} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>