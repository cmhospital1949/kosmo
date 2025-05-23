<?php
require_once __DIR__ . '/lib/Database.php';
// This script takes a more direct approach to fix the admin panel display issues

// First, let's reset the gallery data completely to ensure a clean state
function reset_gallery_data() {
    // Connect to the database
    $pdo = Database::getConnection();

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start transaction
        $pdo->beginTransaction();
        
        // Delete all gallery images and categories
        $pdo->exec("DELETE FROM gallery_images");
        $pdo->exec("DELETE FROM gallery_categories");
        
        // Reset auto-increment counters
        $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
        
        // Create the four categories with fixed IDs
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
        
        // Import default gallery images with fixed category IDs
        $defaultGalleryImages = [
            1 => [ // Foundation History
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
            2 => [ // Sports Medicine
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
            3 => [ // Athlete Support
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
            4 => [ // Seminars & Events
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
        
        // Commit all changes
        $pdo->commit();
        
        // Check if it worked
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_categories");
        $categoryCount = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
        $imageCount = $stmt->fetchColumn();
        
        return "Gallery data reset successfully. Created $categoryCount categories and $imageCount images with fixed IDs.";
        
    } catch (PDOException $e) {
        // Rollback if something went wrong
        if ($pdo) {
            $pdo->rollBack();
        }
        return "Error: " . $e->getMessage();
    }
}

// Now actually perform the reset
echo "<h1>Reset Gallery Data</h1>";
$result = reset_gallery_data();
echo "<p>$result</p>";
echo "<p>Please <a href='gallery.php?lang=en'>check the gallery page</a> and <a href='admin.php?view=gallery'>check the admin gallery page</a> to verify the fix worked.</p>";

// Fix admin.php to ensure it displays gallery categories without duplicates
echo "<h2>Fixing Admin Display</h2>";
$admin_content = file_get_contents("admin.php");

if ($admin_content) {
    // Find the gallery view and fix code that loads categories
    $gallery_view_start = "// Get gallery categories";
    $gallery_view_pos = strpos($admin_content, $gallery_view_start);
    
    if ($gallery_view_pos !== false) {
        $fixed_content = substr($admin_content, 0, $gallery_view_pos) . 
"// Get gallery categories
\$categories = [];
if (\$view == 'gallery') {
    \$pdo = connect_db();
    if (\$pdo) {
        // Use DISTINCT to ensure we only get unique category names
        \$stmt = \$pdo->query(\"SELECT DISTINCT id, name, ko_name, description, ko_description, created_at FROM gallery_categories ORDER BY id\");
        \$categories = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get images for each category
        foreach (\$categories as &\$category) {
            \$stmt = \$pdo->prepare(\"SELECT * FROM gallery_images WHERE category_id = ? ORDER BY id DESC\");
            \$stmt->execute([\$category['id']]);
            \$category['images'] = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}" . substr($admin_content, strpos($admin_content, "// Process gallery image upload"));
        
        // Save the fixed content
        if (file_put_contents("admin.php", $fixed_content)) {
            echo "<p>Successfully modified admin.php to display unique categories.</p>";
        } else {
            echo "<p>Failed to write modified admin.php.</p>";
        }
    } else {
        echo "<p>Could not find the gallery section in admin.php.</p>";
    }
} else {
    echo "<p>Could not read admin.php.</p>";
}
?>