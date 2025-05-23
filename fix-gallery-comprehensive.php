<?php
require_once __DIR__ . '/lib/Database.php';
// This script provides a comprehensive fix for the gallery functionality

echo "<h1>Comprehensive Gallery Fix</h1>";
echo "<p>Running gallery database reset and admin panel fix...</p>";

// Step 1: Reset and rebuild the gallery database tables
function reset_gallery_database() {
    // Connect to the database
    $pdo = Database::getConnection();

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Start transaction (only if we're not already in one)
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
        }
        
        // Delete all gallery images and categories
        $pdo->exec("DELETE FROM gallery_images");
        $pdo->exec("DELETE FROM gallery_categories");
        
        // Reset auto-increment counters
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
        
        // Map category names to IDs for clarity
        $categoryIds = [];
        foreach ($categoryRows as $row) {
            $categoryIds[$row['name']] = $row['id'];
        }
        
        // Import default gallery images
        $defaultGalleryImages = [
            $categoryIds['Foundation History'] => [ // Foundation History
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
            $categoryIds['Sports Medicine'] => [ // Sports Medicine
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
            $categoryIds['Athlete Support'] => [ // Athlete Support
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
            $categoryIds['Seminars & Events'] => [ // Seminars & Events
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
        
        // Commit changes (only if we're in a transaction)
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
        
        // Get counts for verification
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_categories");
        $categoryCount = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
        $imageCount = $stmt->fetchColumn();
        
        // Print the category IDs for reference
        $categoryInfo = [];
        foreach ($categoryIds as $name => $id) {
            $categoryInfo[] = "$name (ID: $id)";
        }
        
        return [
            'success' => true,
            'message' => "Gallery database reset successfully. Created $categoryCount categories and $imageCount images.",
            'categories' => $categoryInfo
        ];
        
    } catch (PDOException $e) {
        // Rollback on error (only if we're in a transaction)
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        return [
            'success' => false,
            'message' => "Database error: " . $e->getMessage()
        ];
    }
}

// Step 2: Fix the admin.php file to handle gallery display correctly
function fix_admin_php() {
    // Get the current admin.php content
    $admin_content = file_get_contents('admin.php');
    
    if (!$admin_content) {
        return [
            'success' => false,
            'message' => "Could not read admin.php file."
        ];
    }
    
    // Create a backup of the original file
    $backup_result = file_put_contents('admin.php.backup', $admin_content);
    if (!$backup_result) {
        return [
            'success' => false,
            'message' => "Could not create backup of admin.php file."
        ];
    }
    
    // Find the gallery category retrieval section
    $start_marker = "// Get gallery categories";
    $end_marker = "// Process gallery image upload";
    
    $start_pos = strpos($admin_content, $start_marker);
    $end_pos = strpos($admin_content, $end_marker);
    
    if ($start_pos === false || $end_pos === false) {
        return [
            'success' => false,
            'message' => "Could not find the gallery section in admin.php. Check for: '$start_marker' and '$end_marker'"
        ];
    }
    
    // Create the improved gallery category retrieval code
    $improved_code = "// Get gallery categories
\$categories = [];
if (\$view == 'gallery') {
    \$pdo = connect_db();
    if (\$pdo) {
        \$stmt = \$pdo->query(\"SELECT * FROM gallery_categories ORDER BY id\");
        \$allCategories = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure unique categories by name
        \$processedNames = [];
        
        foreach (\$allCategories as \$cat) {
            if (!isset(\$processedNames[\$cat['name']])) {
                \$categories[] = \$cat;
                \$processedNames[\$cat['name']] = true;
            }
        }
        
        // Get images for each category
        foreach (\$categories as &\$category) {
            \$stmt = \$pdo->prepare(\"SELECT * FROM gallery_images WHERE category_id = ? ORDER BY id DESC\");
            \$stmt->execute([\$category['id']]);
            \$category['images'] = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

";
    
    // Replace the section in the file
    $new_content = substr($admin_content, 0, $start_pos) . $improved_code . substr($admin_content, $end_pos);
    
    // Save the modified file
    $save_result = file_put_contents('admin.php', $new_content);
    
    if (!$save_result) {
        return [
            'success' => false,
            'message' => "Failed to save modified admin.php file."
        ];
    }
    
    return [
        'success' => true,
        'message' => "Successfully modified admin.php to properly handle gallery categories."
    ];
}

// Step 3: Fix the reset_gallery action code in admin.php
function fix_reset_gallery_action() {
    // Get the current admin.php content
    $admin_content = file_get_contents('admin.php');
    
    if (!$admin_content) {
        return [
            'success' => false,
            'message' => "Could not read admin.php file."
        ];
    }
    
    // Find the reset_gallery action section
    $start_marker = "// Reset gallery to fix any issues";
    $end_marker = "// Delete category";
    
    $start_pos = strpos($admin_content, $start_marker);
    $end_pos = strpos($admin_content, $end_marker);
    
    if ($start_pos === false || $end_pos === false) {
        return [
            'success' => false,
            'message' => "Could not find the reset gallery action in admin.php. Check for: '$start_marker' and '$end_marker'"
        ];
    }
    
    // Create the improved reset gallery action code
    $improved_code = "// Reset gallery to fix any issues
if (isset(\$_GET['action']) && \$_GET['action'] == 'reset_gallery') {
    \$pdo = connect_db();
    if (\$pdo) {
        try {
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
                \$categoryIds[\$row[\"name\"]] = \$row[\"id\"];
            }
            
            // Import default gallery images
            \$defaultGalleryImages = [
                \$categoryIds[\"Foundation History\"] => [ // Foundation History
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png\",
                        \"alt\" => \"KOSMO Foundation History Timeline\",
                        \"caption\" => \"Foundation establishment timeline\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png\",
                        \"alt\" => \"KOSMO Foundation Organization Chart\",
                        \"caption\" => \"Foundation organization structure\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg\",
                        \"alt\" => \"Jincheon Athletes Village Opening Ceremony\",
                        \"caption\" => \"Jincheon National Athletes Village opening ceremony\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg\",
                        \"alt\" => \"Korean Sports Association Ceremony\",
                        \"caption\" => \"Korean Sports Association 98th Anniversary Ceremony\"
                    ]
                ],
                \$categoryIds[\"Sports Medicine\"] => [ // Sports Medicine
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg\",
                        \"alt\" => \"Sports Medicine Practice\",
                        \"caption\" => \"Sports medicine professionals at work\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg\",
                        \"alt\" => \"Medical Support Team\",
                        \"caption\" => \"Medical support team for athletes\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg\",
                        \"alt\" => \"Medical Equipment\",
                        \"caption\" => \"State-of-the-art medical equipment\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg\",
                        \"alt\" => \"Medical Consultation\",
                        \"caption\" => \"Medical consultation for athletes\"
                    ]
                ],
                \$categoryIds[\"Athlete Support\"] => [ // Athlete Support
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg\",
                        \"alt\" => \"Korea-Japan Volleyball\",
                        \"caption\" => \"Korea-Japan volleyball match\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png\",
                        \"alt\" => \"Jakarta Asian Games\",
                        \"caption\" => \"Jakarta Asian Games\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg\",
                        \"alt\" => \"National Volleyball Team Doctor\",
                        \"caption\" => \"National volleyball team with medical support\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png\",
                        \"alt\" => \"Jakarta Asian Games 2\",
                        \"caption\" => \"Medical support at Jakarta Asian Games\"
                    ]
                ],
                \$categoryIds[\"Seminars & Events\"] => [ // Seminars & Events
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg\",
                        \"alt\" => \"Opening Ceremony 2\",
                        \"caption\" => \"Foundation seminar and opening ceremony\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg\",
                        \"alt\" => \"Opening Ceremony 3\",
                        \"caption\" => \"Foundation inauguration event\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg\",
                        \"alt\" => \"Korean Sports Association Ceremony 2\",
                        \"caption\" => \"Sports association anniversary celebration\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg\",
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
            
            \$message = \"Gallery has been reset successfully.\";
        } catch (PDOException \$e) {
            \$error = \"Failed to reset gallery: \" . \$e->getMessage();
        }
    }
    \$view = 'gallery';
}

";
    
    // Replace the section in the file
    $new_content = substr($admin_content, 0, $start_pos) . $improved_code . substr($admin_content, $end_pos);
    
    // Save the modified file
    $save_result = file_put_contents('admin.php', $new_content);
    
    if (!$save_result) {
        return [
            'success' => false,
            'message' => "Failed to save modified admin.php file when fixing reset gallery action."
        ];
    }
    
    return [
        'success' => true,
        'message' => "Successfully fixed the reset gallery action in admin.php."
    ];
}

// Step 4: Fix the sync_gallery_with_frontend function
function fix_sync_gallery_function() {
    // Get the current admin.php content
    $admin_content = file_get_contents('admin.php');
    
    if (!$admin_content) {
        return [
            'success' => false,
            'message' => "Could not read admin.php file."
        ];
    }
    
    // Find the sync_gallery_with_frontend function
    $start_marker = "// Synchronize gallery images from front-end to database if needed";
    $end_marker = "// Get user profile";
    
    $start_pos = strpos($admin_content, $start_marker);
    $end_pos = strpos($admin_content, $end_marker);
    
    if ($start_pos === false || $end_pos === false) {
        return [
            'success' => false,
            'message' => "Could not find the sync_gallery_with_frontend function in admin.php. Check for: '$start_marker' and '$end_marker'"
        ];
    }
    
    // Create the improved sync_gallery_with_frontend function
    $improved_code = "// Synchronize gallery images from front-end to database if needed
function sync_gallery_with_frontend() {
    \$pdo = connect_db();
    if (!\$pdo) return \"\";
    
    try {
        // Check if we need to reset gallery categories
        \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM gallery_categories\");
        \$categoryCount = \$stmt->fetchColumn();
        \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM gallery_images\");
        \$imageCount = \$stmt->fetchColumn();
        
        if (\$categoryCount < 4 || \$imageCount == 0) {
            // Delete all existing images and categories for a fresh start
            \$pdo->exec(\"DELETE FROM gallery_images\");
            \$pdo->exec(\"DELETE FROM gallery_categories\");
            \$pdo->exec(\"ALTER TABLE gallery_images AUTO_INCREMENT = 1\");
            \$pdo->exec(\"ALTER TABLE gallery_categories AUTO_INCREMENT = 1\");
            
            // Create default gallery categories
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
                \$categoryIds[\$row[\"name\"]] = \$row[\"id\"];
            }
            
            // Import default gallery images
            \$defaultGalleryImages = [
                \$categoryIds[\"Foundation History\"] => [ // Foundation History
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png\",
                        \"alt\" => \"KOSMO Foundation History Timeline\",
                        \"caption\" => \"Foundation establishment timeline\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png\",
                        \"alt\" => \"KOSMO Foundation Organization Chart\",
                        \"caption\" => \"Foundation organization structure\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg\",
                        \"alt\" => \"Jincheon Athletes Village Opening Ceremony\",
                        \"caption\" => \"Jincheon National Athletes Village opening ceremony\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg\",
                        \"alt\" => \"Korean Sports Association Ceremony\",
                        \"caption\" => \"Korean Sports Association 98th Anniversary Ceremony\"
                    ]
                ],
                \$categoryIds[\"Sports Medicine\"] => [ // Sports Medicine
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg\",
                        \"alt\" => \"Sports Medicine Practice\",
                        \"caption\" => \"Sports medicine professionals at work\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg\",
                        \"alt\" => \"Medical Support Team\",
                        \"caption\" => \"Medical support team for athletes\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg\",
                        \"alt\" => \"Medical Equipment\",
                        \"caption\" => \"State-of-the-art medical equipment\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg\",
                        \"alt\" => \"Medical Consultation\",
                        \"caption\" => \"Medical consultation for athletes\"
                    ]
                ],
                \$categoryIds[\"Athlete Support\"] => [ // Athlete Support
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg\",
                        \"alt\" => \"Korea-Japan Volleyball\",
                        \"caption\" => \"Korea-Japan volleyball match\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png\",
                        \"alt\" => \"Jakarta Asian Games\",
                        \"caption\" => \"Jakarta Asian Games\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg\",
                        \"alt\" => \"National Volleyball Team Doctor\",
                        \"caption\" => \"National volleyball team with medical support\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png\",
                        \"alt\" => \"Jakarta Asian Games 2\",
                        \"caption\" => \"Medical support at Jakarta Asian Games\"
                    ]
                ],
                \$categoryIds[\"Seminars & Events\"] => [ // Seminars & Events
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg\",
                        \"alt\" => \"Opening Ceremony 2\",
                        \"caption\" => \"Foundation seminar and opening ceremony\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg\",
                        \"alt\" => \"Opening Ceremony 3\",
                        \"caption\" => \"Foundation inauguration event\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg\",
                        \"alt\" => \"Korean Sports Association Ceremony 2\",
                        \"caption\" => \"Sports association anniversary celebration\"
                    ],
                    [
                        \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg\",
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
            
            return \"Gallery synchronized with front-end.\";
        }
        
        return \"\";
    } catch (PDOException \$e) {
        error_log(\"Gallery sync error: \" . \$e->getMessage());
        return \"Error synchronizing gallery: \" . \$e->getMessage();
    }
}

// Run synchronization when viewing the gallery
if (\$view == 'gallery') {
    \$syncResult = sync_gallery_with_frontend();
    if (!empty(\$syncResult)) {
        \$message = \$syncResult;
    }
}

";
    
    // Replace the section in the file
    $new_content = substr($admin_content, 0, $start_pos) . $improved_code . substr($admin_content, $end_pos);
    
    // Save the modified file
    $save_result = file_put_contents('admin.php', $new_content);
    
    if (!$save_result) {
        return [
            'success' => false,
            'message' => "Failed to save modified admin.php file when fixing sync_gallery_with_frontend function."
        ];
    }
    
    return [
        'success' => true,
        'message' => "Successfully fixed the sync_gallery_with_frontend function in admin.php."
    ];
}

// Step 5: Create a direct gallery reset script for fixing the database
function create_direct_gallery_reset() {
    $script = "<?php
// Direct gallery database reset that bypasses transaction logic

// Connect to the database
require_once __DIR__ . '/../lib/Database.php';

try {
    \$pdo = Database::getConnection();
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo \"<h2>Gallery Database Reset</h2>\";
    
    // Delete all existing data
    \$pdo->exec(\"DELETE FROM gallery_images\");
    echo \"<p>Deleted all gallery images</p>\";
    
    \$pdo->exec(\"DELETE FROM gallery_categories\");
    echo \"<p>Deleted all gallery categories</p>\";
    
    \$pdo->exec(\"ALTER TABLE gallery_images AUTO_INCREMENT = 1\");
    \$pdo->exec(\"ALTER TABLE gallery_categories AUTO_INCREMENT = 1\");
    echo \"<p>Reset auto-increment counters</p>\";
    
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
    echo \"<p>Created 4 gallery categories</p>\";
    
    // Get the newly created category IDs
    \$stmt = \$pdo->query(\"SELECT id, name FROM gallery_categories ORDER BY id\");
    \$categoryRows = \$stmt->fetchAll(PDO::FETCH_ASSOC);
    \$categoryIds = [];
    
    foreach (\$categoryRows as \$row) {
        \$categoryIds[\$row[\"name\"]] = \$row[\"id\"];
        echo \"<p>Category: {$row['name']} has ID: {$row['id']}</p>\";
    }
    
    // Import default gallery images
    \$defaultGalleryImages = [
        \$categoryIds[\"Foundation History\"] => [ // Foundation History
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png\",
                \"alt\" => \"KOSMO Foundation History Timeline\",
                \"caption\" => \"Foundation establishment timeline\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png\",
                \"alt\" => \"KOSMO Foundation Organization Chart\",
                \"caption\" => \"Foundation organization structure\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg\",
                \"alt\" => \"Jincheon Athletes Village Opening Ceremony\",
                \"caption\" => \"Jincheon National Athletes Village opening ceremony\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg\",
                \"alt\" => \"Korean Sports Association Ceremony\",
                \"caption\" => \"Korean Sports Association 98th Anniversary Ceremony\"
            ]
        ],
        \$categoryIds[\"Sports Medicine\"] => [ // Sports Medicine
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg\",
                \"alt\" => \"Sports Medicine Practice\",
                \"caption\" => \"Sports medicine professionals at work\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg\",
                \"alt\" => \"Medical Support Team\",
                \"caption\" => \"Medical support team for athletes\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg\",
                \"alt\" => \"Medical Equipment\",
                \"caption\" => \"State-of-the-art medical equipment\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg\",
                \"alt\" => \"Medical Consultation\",
                \"caption\" => \"Medical consultation for athletes\"
            ]
        ],
        \$categoryIds[\"Athlete Support\"] => [ // Athlete Support
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg\",
                \"alt\" => \"Korea-Japan Volleyball\",
                \"caption\" => \"Korea-Japan volleyball match\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png\",
                \"alt\" => \"Jakarta Asian Games\",
                \"caption\" => \"Jakarta Asian Games\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg\",
                \"alt\" => \"National Volleyball Team Doctor\",
                \"caption\" => \"National volleyball team with medical support\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png\",
                \"alt\" => \"Jakarta Asian Games 2\",
                \"caption\" => \"Medical support at Jakarta Asian Games\"
            ]
        ],
        \$categoryIds[\"Seminars & Events\"] => [ // Seminars & Events
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg\",
                \"alt\" => \"Opening Ceremony 2\",
                \"caption\" => \"Foundation seminar and opening ceremony\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg\",
                \"alt\" => \"Opening Ceremony 3\",
                \"caption\" => \"Foundation inauguration event\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg\",
                \"alt\" => \"Korean Sports Association Ceremony 2\",
                \"caption\" => \"Sports association anniversary celebration\"
            ],
            [
                \"src\" => \"http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg\",
                \"alt\" => \"Medical Conference\",
                \"caption\" => \"Sports medicine conference and seminar\"
            ]
        ]
    ];
    
    // Insert default gallery images
    \$stmt = \$pdo->prepare(\"INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)\");
    \$imageCount = 0;
    
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
            \$imageCount++;
        }
    }
    
    echo \"<p>Added \$imageCount gallery images</p>\";
    
    echo \"<h2>Gallery reset completed successfully</h2>\";
    echo \"<p>Please <a href='gallery.php?lang=en'>check the gallery page</a> and <a href='admin.php?view=gallery'>check the admin gallery page</a> to verify the fix worked.</p>\";

} catch (PDOException \$e) {
    echo \"<h2>Error</h2>\";
    echo \"<p>Database error: \" . \$e->getMessage() . \"</p>\";
}
?>";
    
    $save_result = file_put_contents('direct-gallery-reset.php', $script);
    
    if (!$save_result) {
        return [
            'success' => false,
            'message' => "Failed to create direct-gallery-reset.php script."
        ];
    }
    
    return [
        'success' => true,
        'message' => "Created direct-gallery-reset.php script for fallback database reset."
    ];
}

// Run all the fix functions and collect results
$results = [];

// Step 1: First try to directly fix admin.php
$admin_fix_result = fix_admin_php();
$results[] = $admin_fix_result;
echo "<h2>Admin Panel Gallery Display Fix</h2>";
echo "<p>" . $admin_fix_result['message'] . "</p>";

// Step 2: Fix reset gallery action
$reset_fix_result = fix_reset_gallery_action();
$results[] = $reset_fix_result;
echo "<h2>Reset Gallery Action Fix</h2>";
echo "<p>" . $reset_fix_result['message'] . "</p>";

// Step 3: Fix sync_gallery_with_frontend function
$sync_fix_result = fix_sync_gallery_function();
$results[] = $sync_fix_result;
echo "<h2>Sync Gallery Function Fix</h2>";
echo "<p>" . $sync_fix_result['message'] . "</p>";

// Step 4: Create direct gallery reset script
$direct_script_result = create_direct_gallery_reset();
$results[] = $direct_script_result;
echo "<h2>Direct Gallery Reset Script</h2>";
echo "<p>" . $direct_script_result['message'] . "</p>";

// Step 5: Reset gallery database (skip transaction approach due to previous errors)
echo "<h2>Gallery Database Reset</h2>";
echo "<p>To fix database issues, please use the direct gallery reset script.</p>";

// Check if all steps were successful
$all_success = true;
foreach ($results as $result) {
    if (!$result['success']) {
        $all_success = false;
        break;
    }
}

// Display overall result
echo "<h2>Overall Result</h2>";
if ($all_success) {
    echo "<p style='color: green; font-weight: bold;'>Admin panel fixes were applied successfully!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>Some fixes were not applied successfully. Please check the details above.</p>";
}

// Provide links for verification and further steps
echo "<div style='margin-top: 20px;'>";
echo "<p>Please follow these steps to complete the fix:</p>";
echo "<ol>";
echo "<li><a href='direct-gallery-reset.php' target='_blank'>Run the direct gallery reset script</a> to fix the database</li>";
echo "<li><a href='gallery.php?lang=en' target='_blank'>Check the gallery page</a> to verify the frontend is working</li>";
echo "<li><a href='admin.php?view=gallery' target='_blank'>Check the admin gallery page</a> to verify the admin panel is working</li>";
echo "</ol>";
echo "</div>";
?>