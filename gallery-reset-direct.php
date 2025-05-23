<?php
require_once __DIR__ . '/config.php';
// Direct gallery reset that doesn't depend on admin.php

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

echo "<h1>Gallery Reset Tool</h1>";

// Connect to database
$pdo = connect_db();
if (!$pdo) {
    echo "<p>Failed to connect to database.</p>";
    exit;
}

try {
    // 1. Delete all existing gallery data
    echo "<p>Deleting existing gallery data...</p>";
    $pdo->exec("DELETE FROM gallery_images");
    $pdo->exec("DELETE FROM gallery_categories");
    $pdo->exec("ALTER TABLE gallery_images AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE gallery_categories AUTO_INCREMENT = 1");
    
    // 2. Create the four categories
    echo "<p>Creating new gallery categories...</p>";
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
    
    // 3. Get the IDs of the newly created categories
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
    $categoryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Category IDs:</p><ul>";
    $categoryIds = [];
    foreach ($categoryRows as $row) {
        echo "<li>ID: {$row['id']} - {$row['name']}</li>";
        
        if ($row["name"] == "Foundation History") {
            $categoryIds["foundation"] = $row["id"];
        } else if ($row["name"] == "Sports Medicine") {
            $categoryIds["medicine"] = $row["id"];
        } else if ($row["name"] == "Athlete Support") {
            $categoryIds["athlete"] = $row["id"];
        } else if ($row["name"] == "Seminars & Events") {
            $categoryIds["seminars"] = $row["id"];
        }
    }
    echo "</ul>";
    
    // 4. Create the images for each category
    echo "<p>Adding images to categories...</p>";
    
    $defaultGalleryImages = [
        $categoryIds["foundation"] => [ // Foundation History
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
        $categoryIds["medicine"] => [ // Sports Medicine
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
        $categoryIds["athlete"] => [ // Athlete Support
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
        $categoryIds["seminars"] => [ // Seminars & Events
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
    
    echo "<p>Added $imageCount images to gallery.</p>";
    
    // 5. Fix the admin.php display
    echo "<p>Updating admin.php to show unique categories...</p>";
    
    $admin_content = file_get_contents('admin.php');
    if ($admin_content) {
        // Look for the category foreach loop
        $loop_start = "<?php foreach (\$categories as \$category): ?>";
        $new_loop = "<?php 
            // Remove duplicate categories
            \$uniqueCategories = [];
            \$processedNames = [];
            
            foreach (\$categories as \$cat) {
                if (!isset(\$processedNames[\$cat['name']])) {
                    \$uniqueCategories[] = \$cat;
                    \$processedNames[\$cat['name']] = true;
                }
            }
            
            foreach (\$uniqueCategories as \$category): 
        ?>";
        
        if (strpos($admin_content, $loop_start) !== false) {
            $admin_content = str_replace($loop_start, $new_loop, $admin_content);
            
            if (file_put_contents('admin.php', $admin_content)) {
                echo "<p>Successfully updated admin.php to prevent duplicate categories.</p>";
            } else {
                echo "<p>Failed to update admin.php file.</p>";
            }
        } else {
            echo "<p>Could not find the category loop in admin.php. It may already have been updated.</p>";
        }
    } else {
        echo "<p>Could not read admin.php file for updating.</p>";
    }
    
    echo "<h2>Gallery Reset Complete!</h2>";
    echo "<p>The gallery has been reset successfully. There are now $imageCount images in " . count($categoryRows) . " categories.</p>";
    echo "<p><a href='gallery.php?lang=en'>Check the front-end gallery</a> | <a href='admin.php?view=gallery'>Check the admin gallery</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>An error occurred: " . $e->getMessage() . "</p>";
}
?>