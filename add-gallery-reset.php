<?php
// This script will add a "Reset Gallery" button to the admin panel and provide a reset feature
// First: Add the reset function to the admin.php file

// Get the admin.php content
$admin_content = file_get_contents('admin.php');

if (!$admin_content) {
    echo "Failed to read admin.php file.";
    exit;
}

// Add the reset gallery function after the delete_relations function
$resetGalleryFunction = "
// Reset gallery to fix any issues
if (isset(\$_GET['action']) && \$_GET['action'] == 'reset_gallery') {
    \$pdo = connect_db();
    if (\$pdo) {
        try {
            // Start transaction
            \$pdo->beginTransaction();
            
            // Delete all gallery images and categories
            \$pdo->exec(\"DELETE FROM gallery_images\");
            \$pdo->exec(\"DELETE FROM gallery_categories\");
            \$pdo->exec(\"ALTER TABLE gallery_images AUTO_INCREMENT = 1\");
            \$pdo->exec(\"ALTER TABLE gallery_categories AUTO_INCREMENT = 1\");
            
            // Create the four categories
            \$categories = [
                [\"Foundation History\", \"재단 역사\", \"Photos from our foundation history\", \"재단 역사의 사진\"],
                [\"Sports Medicine\", \"스포츠 의학\", \"Sports medicine practices and facilities\", \"스포츠 의학 사진\"],
                [\"Athlete Support\", \"선수 지원\", \"Our athlete support programs\", \"선수 지원 프로그램\"],
                [\"Seminars & Events\", \"세미나 및 행사\", \"Seminars and events organized by the foundation\", \"재단이 주최한 세미나 및 행사\"]
            ];
            
            \$stmt = \$pdo->prepare(\"INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)\");
            
            foreach (\$categories as \$category) {
                \$stmt->execute(\$category);
            }
            
            // Get the newly created category IDs
            \$stmt = \$pdo->query(\"SELECT id, name FROM gallery_categories ORDER BY id\");
            \$categoryRows = \$stmt->fetchAll(PDO::FETCH_ASSOC);
            \$categoryIds = [];
            
            foreach (\$categoryRows as \$row) {
                if (\$row[\"name\"] == \"Foundation History\") {
                    \$categoryIds[\"foundation\"] = \$row[\"id\"];
                } else if (\$row[\"name\"] == \"Sports Medicine\") {
                    \$categoryIds[\"medicine\"] = \$row[\"id\"];
                } else if (\$row[\"name\"] == \"Athlete Support\") {
                    \$categoryIds[\"athlete\"] = \$row[\"id\"];
                } else if (\$row[\"name\"] == \"Seminars & Events\") {
                    \$categoryIds[\"seminars\"] = \$row[\"id\"];
                }
            }
            
            // Import default gallery images
            \$defaultGalleryImages = [
                \$categoryIds[\"foundation\"] => [ // Foundation History
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/히스토리1-1024x586.png\",
                        \"alt\" => \"KOSMO Foundation History Timeline\",
                        \"caption\" => \"Foundation establishment timeline\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/조직도4_230316.png\",
                        \"alt\" => \"KOSMO Foundation Organization Chart\",
                        \"caption\" => \"Foundation organization structure\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg\",
                        \"alt\" => \"Jincheon Athletes Village Opening Ceremony\",
                        \"caption\" => \"Jincheon National Athletes Village opening ceremony\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg\",
                        \"alt\" => \"Korean Sports Association Ceremony\",
                        \"caption\" => \"Korean Sports Association 98th Anniversary Ceremony\"
                    ]
                ],
                \$categoryIds[\"medicine\"] => [ // Sports Medicine
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/IMG_3319-scaled.jpg\",
                        \"alt\" => \"Sports Medicine Practice\",
                        \"caption\" => \"Sports medicine professionals at work\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/IMG_3354-scaled.jpg\",
                        \"alt\" => \"Medical Support Team\",
                        \"caption\" => \"Medical support team for athletes\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/IMG_3258-scaled.jpg\",
                        \"alt\" => \"Medical Equipment\",
                        \"caption\" => \"State-of-the-art medical equipment\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/IMG_3370-scaled.jpg\",
                        \"alt\" => \"Medical Consultation\",
                        \"caption\" => \"Medical consultation for athletes\"
                    ]
                ],
                \$categoryIds[\"athlete\"] => [ // Athlete Support
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/한일배구.jpg\",
                        \"alt\" => \"Korea-Japan Volleyball\",
                        \"caption\" => \"Korea-Japan volleyball match\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임.png\",
                        \"alt\" => \"Jakarta Asian Games\",
                        \"caption\" => \"Jakarta Asian Games\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg\",
                        \"alt\" => \"National Volleyball Team Doctor\",
                        \"caption\" => \"National volleyball team with medical support\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임-2.png\",
                        \"alt\" => \"Jakarta Asian Games 2\",
                        \"caption\" => \"Medical support at Jakarta Asian Games\"
                    ]
                ],
                \$categoryIds[\"seminars\"] => [ // Seminars & Events
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/개촌식2.jpg\",
                        \"alt\" => \"Opening Ceremony 2\",
                        \"caption\" => \"Foundation seminar and opening ceremony\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/개촌식3.jpg\",
                        \"alt\" => \"Opening Ceremony 3\",
                        \"caption\" => \"Foundation inauguration event\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/대한체육-기념식-2.jpg\",
                        \"alt\" => \"Korean Sports Association Ceremony 2\",
                        \"caption\" => \"Sports association anniversary celebration\"
                    ],
                    [
                        \"src\" => \"http://kosmo.center/wp-content/uploads/2023/03/170586_151041_2238.jpg\",
                        \"alt\" => \"Medical Conference\",
                        \"caption\" => \"Sports medicine conference and seminar\"
                    ]
                ]
            ];
            
            // Insert default gallery images
            \$stmt = \$pdo->prepare(\"INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)\");
            
            foreach (\$defaultGalleryImages as \$categoryId => \$images) {
                foreach (\$images as \$image) {
                    \$stmt->execute([
                        \$categoryId,
                        \$image[\"alt\"],
                        \$image[\"alt\"], 
                        \$image[\"caption\"],
                        \$image[\"caption\"],
                        \$image[\"src\"]
                    ]);
                }
            }
            
            // Commit all changes
            \$pdo->commit();
            
            \$message = \"Gallery has been reset successfully.\";
        } catch (PDOException \$e) {
            // Rollback if an error occurred
            \$pdo->rollBack();
            \$error = \"Failed to reset gallery: \" . \$e->getMessage();
        }
    }
    \$view = 'gallery';
}";

// Find a suitable position to add the function
$deleteCategoryPos = strpos($admin_content, "// Delete category");
if ($deleteCategoryPos !== false) {
    // Insert the function after the delete category section
    $admin_content = substr_replace($admin_content, $resetGalleryFunction, $deleteCategoryPos - 1, 0);
    
    // Now add the reset button to the gallery page HTML
    $galleryHeaderPos = strpos($admin_content, "<h2 class=\"text-2xl font-bold\">Gallery Management</h2>");
    if ($galleryHeaderPos !== false) {
        $galleryHeader = "<h2 class=\"text-2xl font-bold\">Gallery Management</h2>";
        $newGalleryHeader = "<h2 class=\"text-2xl font-bold\">Gallery Management</h2>
                <a href=\"admin.php?view=gallery&action=reset_gallery\" class=\"bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm ml-2\" onclick=\"return confirm('Are you sure you want to reset the gallery? This will delete all current gallery data and restore the default categories and images.');\">Reset Gallery</a>";
        
        $admin_content = str_replace($galleryHeader, $newGalleryHeader, $admin_content);
        
        // Also modify the foreach loop to prevent duplicates
        $categoriesLoopStart = "<?php foreach (\$categories as \$category): ?>";
        $newCategoriesLoopStart = "<?php 
                // De-duplicate categories by name
                \$uniqueCategories = [];
                \$processedNames = [];
                
                foreach (\$categories as \$cat) {
                    if (!isset(\$processedNames[\$cat['name']])) {
                        \$uniqueCategories[] = \$cat;
                        \$processedNames[\$cat['name']] = true;
                    }
                }
                
                foreach (\$uniqueCategories as \$category): ?>";
        
        $admin_content = str_replace($categoriesLoopStart, $newCategoriesLoopStart, $admin_content);
        
        // Save the modified file
        if (file_put_contents('admin.php', $admin_content)) {
            echo "<h1>Gallery Reset Button Added</h1>";
            echo "<p>A 'Reset Gallery' button has been added to the admin panel and a deduplication function has been implemented.</p>";
            echo "<p>Please <a href='admin.php?view=gallery'>check the admin gallery page</a> to see the improvements.</p>";
        } else {
            echo "<h1>Error</h1>";
            echo "<p>Failed to write the updated admin.php file.</p>";
        }
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Could not find the gallery header section in admin.php.</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>Could not find the delete category section in admin.php.</p>";
}
?>