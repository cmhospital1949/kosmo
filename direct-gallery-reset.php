<?php
// Direct gallery database reset that bypasses transaction logic

// Connect to the database
$host = 'localhost';
$dbname = 'bestluck';
$username = 'bestluck';
$password = 'Nocpriss12!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Gallery Database Reset</h2>";
    
    // Delete all existing data
    $pdo->exec("DELETE FROM gallery_images");
    echo "<p>Deleted all gallery images</p>";
    
    $pdo->exec("DELETE FROM gallery_categories");
    echo "<p>Deleted all gallery categories</p>";
    
    $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
    echo "<p>Reset auto-increment counters</p>";
    
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
    echo "<p>Created 4 gallery categories</p>";
    
    // Get the newly created category IDs
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
    $categoryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categoryIds = [];
    
    foreach ($categoryRows as $row) {
        $categoryIds[$row["name"]] = $row["id"];
        echo "<p>Category:  has ID: </p>";
    }
    
    // Import default gallery images
    $defaultGalleryImages = [
        $categoryIds["Foundation History"] => [ // Foundation History
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
        $categoryIds["Sports Medicine"] => [ // Sports Medicine
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
        $categoryIds["Athlete Support"] => [ // Athlete Support
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
        $categoryIds["Seminars & Events"] => [ // Seminars & Events
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
    
    // Insert default gallery images
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
    
    echo "<p>Added $imageCount gallery images</p>";
    
    echo "<h2>Gallery reset completed successfully</h2>";
    echo "<p>Please <a href='gallery.php?lang=en'>check the gallery page</a> and <a href='admin.php?view=gallery'>check the admin gallery page</a> to verify the fix worked.</p>";

} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>