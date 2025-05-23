<?php
require_once __DIR__ . '/config.php';
// This script will fix the gallery functionality by updating the database directly

function display_message($message, $type = 'info') {
    $color = ($type == 'success') ? 'green' : (($type == 'error') ? 'red' : 'black');
    echo "<p style='color: {$color};'>{$message}</p>";
}

// Connect to database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    display_message("Connected to database successfully.");
    
    // Truncate the tables to start clean
    $pdo->exec("DELETE FROM gallery_images");
    $pdo->exec("DELETE FROM gallery_categories");
    
    display_message("Cleared existing gallery data.", 'success');
    
    // Create default gallery categories
    $categories = [
        ['Foundation History', '재단 역사', 'Photos from our foundation history', '재단 역사의 사진'],
        ['Sports Medicine', '스포츠 의학', 'Sports medicine practices and facilities', '스포츠 의학 사진'],
        ['Athlete Support', '선수 지원', 'Our athlete support programs', '선수 지원 프로그램'],
        ['Seminars & Events', '세미나 및 행사', 'Seminars and events organized by the foundation', '재단이 주최한 세미나 및 행사']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
    
    foreach ($categories as $category) {
        $stmt->execute($category);
        display_message("Created category: " . htmlspecialchars($category[0]));
    }
    
    // Get the newly created category IDs
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
    $categoryRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categoryIds = [];
    
    foreach ($categoryRows as $row) {
        if ($row['name'] == 'Foundation History') {
            $categoryIds['foundation'] = $row['id'];
        } else if ($row['name'] == 'Sports Medicine') {
            $categoryIds['medicine'] = $row['id'];
        } else if ($row['name'] == 'Athlete Support') {
            $categoryIds['athlete'] = $row['id'];
        } else if ($row['name'] == 'Seminars & Events') {
            $categoryIds['seminars'] = $row['id'];
        }
    }
    
    display_message("Category IDs mapped successfully:");
    echo "<ul>";
    foreach ($categoryIds as $key => $id) {
        echo "<li>" . htmlspecialchars($key) . ": " . $id . "</li>";
    }
    echo "</ul>";
    
    // Default images data organized by category
    $defaultImages = [
        // Category 1: Foundation History
        $categoryIds['foundation'] => [
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/히스토리1-1024x586.png',
                'alt' => 'KOSMO Foundation History Timeline',
                'caption' => 'Foundation establishment timeline'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/조직도4_230316.png',
                'alt' => 'KOSMO Foundation Organization Chart',
                'caption' => 'Foundation organization structure'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg',
                'alt' => 'Jincheon Athletes Village Opening Ceremony',
                'caption' => 'Jincheon National Athletes Village opening ceremony'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg',
                'alt' => 'Korean Sports Association Ceremony',
                'caption' => 'Korean Sports Association 98th Anniversary Ceremony'
            ]
        ],
        // Category 2: Sports Medicine
        $categoryIds['medicine'] => [
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/IMG_3319-scaled.jpg',
                'alt' => 'Sports Medicine Practice',
                'caption' => 'Sports medicine professionals at work'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/IMG_3354-scaled.jpg',
                'alt' => 'Medical Support Team',
                'caption' => 'Medical support team for athletes'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/IMG_3258-scaled.jpg',
                'alt' => 'Medical Equipment',
                'caption' => 'State-of-the-art medical equipment'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/IMG_3370-scaled.jpg',
                'alt' => 'Medical Consultation',
                'caption' => 'Medical consultation for athletes'
            ]
        ],
        // Category 3: Athlete Support
        $categoryIds['athlete'] => [
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/한일배구.jpg',
                'alt' => 'Korea-Japan Volleyball',
                'caption' => 'Korea-Japan volleyball match'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임.png',
                'alt' => 'Jakarta Asian Games',
                'caption' => 'Jakarta Asian Games'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg',
                'alt' => 'National Volleyball Team Doctor',
                'caption' => 'National volleyball team with medical support'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임-2.png',
                'alt' => 'Jakarta Asian Games 2',
                'caption' => 'Medical support at Jakarta Asian Games'
            ]
        ],
        // Category 4: Seminars & Events
        $categoryIds['seminars'] => [
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/개촌식2.jpg',
                'alt' => 'Opening Ceremony 2',
                'caption' => 'Foundation seminar and opening ceremony'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/개촌식3.jpg',
                'alt' => 'Opening Ceremony 3',
                'caption' => 'Foundation inauguration event'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/대한체육-기념식-2.jpg',
                'alt' => 'Korean Sports Association Ceremony 2',
                'caption' => 'Sports association anniversary celebration'
            ],
            [
                'src' => 'http://kosmo.center/wp-content/uploads/2023/03/170586_151041_2238.jpg',
                'alt' => 'Medical Conference',
                'caption' => 'Sports medicine conference and seminar'
            ]
        ]
    ];
    
    // Insert images for each category
    $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
    
    $imageCount = 0;
    foreach ($defaultImages as $categoryId => $images) {
        foreach ($images as $image) {
            $stmt->execute([
                $categoryId,
                $image['alt'],
                $image['alt'],
                $image['caption'],
                $image['caption'],
                $image['src']
            ]);
            $imageCount++;
        }
    }
    
    display_message("Added {$imageCount} images to the gallery.", 'success');
    
    // Now update the gallery.php file to use the correct category IDs
    $galleryFile = file_get_contents('gallery.php');
    if (!$galleryFile) {
        display_message("Error: Cannot read gallery.php file", 'error');
    } else {
        // Replace the hardcoded values in the gallery.php for category display
        $categoryMapping = [
            $categoryIds['foundation'] => 'Foundation History',
            $categoryIds['medicine'] => 'Sports Medicine',
            $categoryIds['athlete'] => 'Athlete Support',
            $categoryIds['seminars'] => 'Seminars & Events'
        ];
        
        // Create the JavaScript galleryData object with the correct category IDs
        $galleryDataJs = "const galleryData = " . json_encode($defaultImages) . ";";
        
        // Update the categoryNames JavaScript object
        $categoryNamesJs = "const categoryNames = " . json_encode(array_flip($categoryMapping)) . ";";
        
        // Replace the old galleryData and categoryNames in the gallery.php file
        $pattern1 = '/const galleryData = \{.*?\};/s';
        $pattern2 = '/const categoryNames = \{.*?\};/s';
        
        $galleryFile = preg_replace($pattern1, $galleryDataJs, $galleryFile);
        $galleryFile = preg_replace($pattern2, $categoryNamesJs, $galleryFile);
        
        // Write the updated gallery.php file
        if (file_put_contents('gallery.php', $galleryFile)) {
            display_message("Gallery.php updated successfully with the correct category IDs!", 'success');
        } else {
            display_message("Error writing to gallery.php file", 'error');
        }
    }
    
    display_message("Gallery repair completed successfully! You should now see the correct categories and images in both the admin panel and the front-end gallery page.", 'success');
    
} catch (PDOException $e) {
    display_message("Database error: " . $e->getMessage(), 'error');
}

echo "<p><a href='admin.php?view=gallery' style='display: inline-block; margin-top: 20px; padding: 8px 16px; background-color: #4d9aff; color: white; text-decoration: none; border-radius: 4px;'>Go to Gallery Management</a></p>";
echo "<p><a href='gallery.php?lang=en' style='display: inline-block; margin-top: 10px; padding: 8px 16px; background-color: #ff6b00; color: white; text-decoration: none; border-radius: 4px;'>View Gallery Page</a></p>";
?>
