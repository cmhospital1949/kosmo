<?php
// Database connection
function connect_db() {
    $host = 'db.kosmo.or.kr';
    $dbname = 'dbbestluck';
    $username = 'bestluck';
    $password = 'cmhospital1949!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Start repair process
echo "<h1>Gallery Admin Fix Process</h1>";
echo "<p>Starting gallery admin fix process...</p>";

$pdo = connect_db();

try {
    // Begin transaction to ensure all changes happen together
    $pdo->beginTransaction();
    
    // Check if we have duplicate Athlete Support category
    $stmt = $pdo->prepare("SELECT id, name FROM gallery_categories WHERE name = ?");
    $stmt->execute(['Athlete Support']);
    $athleteSupportCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($athleteSupportCategories) > 1) {
        echo "<p>Found duplicate Athlete Support categories. Fixing...</p>";
        
        // Keep the first one, delete the others
        $keepId = $athleteSupportCategories[0]['id'];
        
        for ($i = 1; $i < count($athleteSupportCategories); $i++) {
            $deleteId = $athleteSupportCategories[$i]['id'];
            
            // First, move any images from the category being deleted to the one we're keeping
            $stmt = $pdo->prepare("UPDATE gallery_images SET category_id = ? WHERE category_id = ?");
            $stmt->execute([$keepId, $deleteId]);
            echo "<p>Moved images from category ID {$deleteId} to {$keepId}</p>";
            
            // Then delete the duplicate category
            $stmt = $pdo->prepare("DELETE FROM gallery_categories WHERE id = ?");
            $stmt->execute([$deleteId]);
            echo "<p>Deleted duplicate category ID {$deleteId}</p>";
        }
    } else {
        echo "<p>No duplicate Athlete Support categories found.</p>";
    }
    
    // Check if we have all necessary categories now
    $stmt = $pdo->query("SELECT id, name FROM gallery_categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Current gallery categories:</p>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: " . $category['id'] . " - " . htmlspecialchars($category['name']) . "</li>";
    }
    echo "</ul>";
    
    // Check for missing category - Seminars & Events
    $stmt = $pdo->prepare("SELECT id FROM gallery_categories WHERE name = ?");
    $stmt->execute(['Seminars & Events']);
    $seminarsCategory = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seminarsCategory) {
        echo "<p>Adding missing 'Seminars & Events' category...</p>";
        
        $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'Seminars & Events',
            '세미나 및 행사',
            'Seminars and events organized by the foundation',
            '재단이 주최한 세미나 및 행사'
        ]);
        
        $newCategoryId = $pdo->lastInsertId();
        echo "<p>Created 'Seminars & Events' category with ID: {$newCategoryId}</p>";
        
        // Add default images for the new category
        $defaultImages = [
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
        ];
        
        $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($defaultImages as $image) {
            $stmt->execute([
                $newCategoryId,
                $image['alt'],
                $image['alt'],
                $image['caption'],
                $image['caption'],
                $image['src']
            ]);
            echo "<p>Added image '" . htmlspecialchars($image['alt']) . "' to Seminars & Events category</p>";
        }
    }
    
    // Fix the gallery categories to match what we need for the front-end
    // Update admin.php to fix the sync_gallery_with_frontend function to work with the correct IDs
    echo "<p>Creating PHP script to update the admin.php file...</p>";
    
    // Create a PHP file to update the sync_gallery_with_frontend function
    $updateScript = "<?php
// This script will update the admin.php file to fix the sync_gallery_with_frontend function

// Get current category IDs
\$pdo = new PDO('mysql:host=db.kosmo.or.kr;dbname=dbbestluck;charset=utf8mb4', 'bestluck', 'cmhospital1949!');
\$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

\$stmt = \$pdo->query('SELECT id, name FROM gallery_categories ORDER BY id');
\$categories = \$stmt->fetchAll(PDO::FETCH_ASSOC);

// Map category names to IDs
\$categoryMap = [];
foreach (\$categories as \$category) {
    \$categoryMap[\$category['name']] = \$category['id'];
}

// Read the admin.php file
\$adminFile = file_get_contents('/home/bestluck/html/admin.php');

// Find the sync_gallery_with_frontend function
\$pattern = '/function sync_gallery_with_frontend\\(\\) \\{.*?\\}/s';
preg_match(\$pattern, \$adminFile, \$matches);

if (!empty(\$matches[0])) {
    // Replace the function with an updated version
    \$newFunction = 'function sync_gallery_with_frontend() {
    \$pdo = connect_db();
    if (!\$pdo) return \"\";
    
    try {
        // Check if we need to reset gallery categories to match the frontend
        \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM gallery_categories\");
        \$categoryCount = \$stmt->fetchColumn();
        \$stmt = \$pdo->query(\"SELECT COUNT(*) FROM gallery_images\");
        \$imageCount = \$stmt->fetchColumn();
        
        // If categories are missing or no images, start fresh
        if (\$categoryCount < 4 || \$imageCount == 0) {
            // Delete all existing categories and images for a fresh start
            \$pdo->exec(\"DELETE FROM gallery_images\");
            \$pdo->exec(\"DELETE FROM gallery_categories\");
            
            // Create default gallery categories that match the front-end names
            \$categories = [
                [\'Foundation History\', \'재단 역사\', \'Photos from our foundation history\', \'재단 역사의 사진\'],
                [\'Sports Medicine\', \'스포츠 의학\', \'Sports medicine practices and facilities\', \'스포츠 의학 사진\'],
                [\'Athlete Support\', \'선수 지원\', \'Our athlete support programs\', \'선수 지원 프로그램\'],
                [\'Seminars & Events\', \'세미나 및 행사\', \'Seminars and events organized by the foundation\', \'재단이 주최한 세미나 및 행사\']
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
                if (\$row[\'name\'] == \'Foundation History\') {
                    \$categoryIds[\'Foundation History\'] = \$row[\'id\'];
                } else if (\$row[\'name\'] == \'Sports Medicine\') {
                    \$categoryIds[\'Sports Medicine\'] = \$row[\'id\'];
                } else if (\$row[\'name\'] == \'Athlete Support\') {
                    \$categoryIds[\'Athlete Support\'] = \$row[\'id\'];
                } else if (\$row[\'name\'] == \'Seminars & Events\') {
                    \$categoryIds[\'Seminars & Events\'] = \$row[\'id\'];
                }
            }
            
            // Import default gallery images
            \$defaultGalleryImages = [
                \$categoryIds[\'Foundation History\'] => [ // Foundation History
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/히스토리1-1024x586.png\',
                        \'alt\' => \'KOSMO Foundation History Timeline\',
                        \'caption\' => \'Foundation establishment timeline\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/조직도4_230316.png\',
                        \'alt\' => \'KOSMO Foundation Organization Chart\',
                        \'caption\' => \'Foundation organization structure\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg\',
                        \'alt\' => \'Jincheon Athletes Village Opening Ceremony\',
                        \'caption\' => \'Jincheon National Athletes Village opening ceremony\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg\',
                        \'alt\' => \'Korean Sports Association Ceremony\',
                        \'caption\' => \'Korean Sports Association 98th Anniversary Ceremony\'
                    ]
                ],
                \$categoryIds[\'Sports Medicine\'] => [ // Sports Medicine
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/IMG_3319-scaled.jpg\',
                        \'alt\' => \'Sports Medicine Practice\',
                        \'caption\' => \'Sports medicine professionals at work\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/IMG_3354-scaled.jpg\',
                        \'alt\' => \'Medical Support Team\',
                        \'caption\' => \'Medical support team for athletes\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/IMG_3258-scaled.jpg\',
                        \'alt\' => \'Medical Equipment\',
                        \'caption\' => \'State-of-the-art medical equipment\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/IMG_3370-scaled.jpg\',
                        \'alt\' => \'Medical Consultation\',
                        \'caption\' => \'Medical consultation for athletes\'
                    ]
                ],
                \$categoryIds[\'Athlete Support\'] => [ // Athlete Support
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/한일배구.jpg\',
                        \'alt\' => \'Korea-Japan Volleyball\',
                        \'caption\' => \'Korea-Japan volleyball match\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임.png\',
                        \'alt\' => \'Jakarta Asian Games\',
                        \'caption\' => \'Jakarta Asian Games\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg\',
                        \'alt\' => \'National Volleyball Team Doctor\',
                        \'caption\' => \'National volleyball team with medical support\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/자카르타-아시안게임-2.png\',
                        \'alt\' => \'Jakarta Asian Games 2\',
                        \'caption\' => \'Medical support at Jakarta Asian Games\'
                    ]
                ],
                \$categoryIds[\'Seminars & Events\'] => [ // Seminars & Events
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/개촌식2.jpg\',
                        \'alt\' => \'Opening Ceremony 2\',
                        \'caption\' => \'Foundation seminar and opening ceremony\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/개촌식3.jpg\',
                        \'alt\' => \'Opening Ceremony 3\',
                        \'caption\' => \'Foundation inauguration event\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/대한체육-기념식-2.jpg\',
                        \'alt\' => \'Korean Sports Association Ceremony 2\',
                        \'caption\' => \'Sports association anniversary celebration\'
                    ],
                    [
                        \'src\' => \'http://kosmo.center/wp-content/uploads/2023/03/170586_151041_2238.jpg\',
                        \'alt\' => \'Medical Conference\',
                        \'caption\' => \'Sports medicine conference and seminar\'
                    ]
                ]
            ];
            
            // Insert default gallery images
            \$stmt = \$pdo->prepare(\"INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)\");
            
            foreach (\$defaultGalleryImages as \$categoryId => \$images) {
                foreach (\$images as \$image) {
                    \$stmt->execute([
                        \$categoryId,
                        \$image[\'alt\'],
                        \$image[\'alt\'], 
                        \$image[\'caption\'],
                        \$image[\'caption\'],
                        \$image[\'src\']
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
}';

    // Replace the old function with the new one
    \$adminFile = preg_replace(\$pattern, \$newFunction, \$adminFile);
    
    // Write the updated file
    file_put_contents('/home/bestluck/html/admin.php', \$adminFile);
    
    echo '<p style=\"color: green; font-weight: bold;\">Admin.php file updated successfully!</p>';
} else {
    echo '<p style=\"color: red; font-weight: bold;\">Could not find sync_gallery_with_frontend function in admin.php</p>';
}

echo '<p><a href=\"admin.php?view=gallery\">Return to Gallery Management</a></p>';
?>";
    
    // Write the update script to a file
    file_put_contents('/home/bestluck/html/fix-admin-gallery.php', $updateScript);
    echo "<p>Created fix-admin-gallery.php script.</p>";
    
    // Commit the transaction
    $pdo->commit();
    echo "<p style='color: green; font-weight: bold;'>Gallery admin fix completed successfully!</p>";
    echo "<p>Next step: Please run the <a href='fix-admin-gallery.php'>fix-admin-gallery.php</a> script to update the admin.php file.</p>";
    echo "<p>After that, you should see the gallery working correctly.</p>";
    echo "<p><a href='admin.php?view=gallery'>Return to Gallery Management</a></p>";
    
} catch (PDOException $e) {
    // Roll back the transaction if something failed
    $pdo->rollback();
    echo "<p style='color: red; font-weight: bold;'>Error during gallery admin fix: " . $e->getMessage() . "</p>";
}
?>
