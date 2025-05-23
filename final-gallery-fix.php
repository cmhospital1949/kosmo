<?php
// Super focused gallery database fix with detailed debugging

// Connect to the database
require_once __DIR__ . '/lib/Database.php';

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Final Gallery Database Fix</h1>";
    
    // Delete all existing data
    $pdo->exec("DELETE FROM gallery_images");
    echo "<p>Deleted all gallery images</p>";
    
    $pdo->exec("DELETE FROM gallery_categories");
    echo "<p>Deleted all gallery categories</p>";
    
    $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
    echo "<p>Reset auto-increment counters</p>";
    
    // Create the four categories
    echo "<p>Creating categories:</p>";
    echo "<pre>";
    var_dump([
        "Foundation History", "재단 역사", "Photos from our foundation history", "재단 역사의 사진"
    ]);
    echo "</pre>";
    
    // Insert first category
    $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        "Foundation History", "재단 역사", "Photos from our foundation history", "재단 역사의 사진"
    ]);
    $foundationId = $pdo->lastInsertId();
    echo "<p>Inserted Foundation History category with ID: $foundationId</p>";
    
    // Insert second category
    $stmt->execute([
        "Sports Medicine", "스포츠 의학", "Sports medicine practices and facilities", "스포츠 의학 사진"
    ]);
    $medicineId = $pdo->lastInsertId();
    echo "<p>Inserted Sports Medicine category with ID: $medicineId</p>";
    
    // Insert third category
    $stmt->execute([
        "Athlete Support", "선수 지원", "Our athlete support programs", "선수 지원 프로그램"
    ]);
    $athleteId = $pdo->lastInsertId();
    echo "<p>Inserted Athlete Support category with ID: $athleteId</p>";
    
    // Insert fourth category
    $stmt->execute([
        "Seminars & Events", "세미나 및 행사", "Seminars and events organized by the foundation", "재단이 주최한 세미나 및 행사"
    ]);
    $seminarsId = $pdo->lastInsertId();
    echo "<p>Inserted Seminars & Events category with ID: $seminarsId</p>";
    
    // Verify categories were created
    $stmt = $pdo->query("SELECT id, name, ko_name FROM gallery_categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Category verification:</p>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: {$category['id']} - Name: {$category['name']} - Korean Name: {$category['ko_name']}</li>";
    }
    echo "</ul>";
    
    // Import default gallery images for Foundation History
    $foundationImages = [
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
    ];
    
    $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($foundationImages as $image) {
        $stmt->execute([
            $foundationId,
            $image["alt"],
            $image["alt"],
            $image["caption"],
            $image["caption"],
            $image["src"]
        ]);
    }
    echo "<p>Added Foundation History images</p>";
    
    // Import default gallery images for Sports Medicine
    $medicineImages = [
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
    ];
    
    foreach ($medicineImages as $image) {
        $stmt->execute([
            $medicineId,
            $image["alt"],
            $image["alt"],
            $image["caption"],
            $image["caption"],
            $image["src"]
        ]);
    }
    echo "<p>Added Sports Medicine images</p>";
    
    // Import default gallery images for Athlete Support
    $athleteImages = [
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
    ];
    
    foreach ($athleteImages as $image) {
        $stmt->execute([
            $athleteId,
            $image["alt"],
            $image["alt"],
            $image["caption"],
            $image["caption"],
            $image["src"]
        ]);
    }
    echo "<p>Added Athlete Support images</p>";
    
    // Import default gallery images for Seminars & Events
    $seminarImages = [
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
    ];
    
    foreach ($seminarImages as $image) {
        $stmt->execute([
            $seminarsId,
            $image["alt"],
            $image["alt"],
            $image["caption"],
            $image["caption"],
            $image["src"]
        ]);
    }
    echo "<p>Added Seminars & Events images</p>";
    
    // Verify images were created
    $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
    $imageCount = $stmt->fetchColumn();
    echo "<p>Total images: $imageCount</p>";
    
    // Check count by category
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_images WHERE category_id = ?");
    
    $stmt->execute([$foundationId]);
    $foundationCount = $stmt->fetchColumn();
    
    $stmt->execute([$medicineId]);
    $medicineCount = $stmt->fetchColumn();
    
    $stmt->execute([$athleteId]);
    $athleteCount = $stmt->fetchColumn();
    
    $stmt->execute([$seminarsId]);
    $seminarCount = $stmt->fetchColumn();
    
    echo "<p>Images per category:</p>";
    echo "<ul>";
    echo "<li>Foundation History: $foundationCount</li>";
    echo "<li>Sports Medicine: $medicineCount</li>";
    echo "<li>Athlete Support: $athleteCount</li>";
    echo "<li>Seminars & Events: $seminarCount</li>";
    echo "</ul>";
    
    echo "<h2>Gallery reset completed successfully</h2>";
    echo "<div class='links'>";
    echo "<p>Please <a href='gallery.php?lang=en'>check the gallery page</a> and <a href='admin.php?view=gallery'>check the admin gallery page</a> to verify the fix worked.</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>Database error: " . $e->getMessage() . "</p>";
}
?>