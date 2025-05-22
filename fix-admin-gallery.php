<?php
// This is a direct fix to resolve the gallery sync issues between admin and frontend

// Create a simplified sync_gallery_with_frontend function
function create_fixed_admin_php() {
    $admin_php_content = file_get_contents('admin.php');
    
    if (!$admin_php_content) {
        echo "Could not read admin.php file. Please check if it exists.<br>";
        return false;
    }
    
    // Find where the sync_gallery_with_frontend function begins
    $pattern = "/function sync_gallery_with_frontend\\(\\) \\{/";
    preg_match_all($pattern, $admin_php_content, $matches, PREG_OFFSET_CAPTURE);
    
    if (count($matches[0]) > 1) {
        // First occurrence position
        $first_pos = $matches[0][0][1];
        
        // Second occurrence position
        $second_pos = $matches[0][1][1];
        
        // Find the end of the first function
        $curly_open = 1;
        $first_end_pos = $first_pos + strlen($matches[0][0][0]);
        
        for ($i = $first_end_pos; $i < $second_pos; $i++) {
            if ($admin_php_content[$i] === '{') {
                $curly_open++;
            } elseif ($admin_php_content[$i] === '}') {
                $curly_open--;
                if ($curly_open === 0) {
                    $first_end_pos = $i + 1;
                    break;
                }
            }
        }
        
        // Keep the first function and remove all duplicates
        $new_content = substr($admin_php_content, 0, $first_end_pos) . "\n\n// Run synchronization when viewing the gallery\n" . 
        substr($admin_php_content, $second_pos + strlen($matches[0][1][0]) + strpos(substr($admin_php_content, $second_pos + strlen($matches[0][1][0])), "}") + 1);
        
        // Write the new content back
        file_put_contents('admin.php', $new_content);
        
        echo "Successfully fixed admin.php by removing duplicated sync_gallery_with_frontend function.<br>";
        return true;
    } else {
        echo "No duplicate function found in admin.php. Let's try a simpler approach.<br>";
        
        // Simpler approach: Just redefine the sync_gallery_with_frontend function 
        // to use a more robust categorization
        $sync_function = '
// Synchronize gallery images from front-end to database if needed
function sync_gallery_with_frontend() {
    $pdo = connect_db();
    if (!$pdo) return "";
    
    try {
        // Check if we need to reset gallery categories
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_categories");
        $categoryCount = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
        $imageCount = $stmt->fetchColumn();
        
        if ($categoryCount < 4 || $imageCount == 0) {
            // Delete all existing images and categories for a fresh start
            $pdo->exec("DELETE FROM gallery_images");
            $pdo->exec("DELETE FROM gallery_categories");
            
            // Create default gallery categories
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
            $categoryIds = [];
            
            foreach ($categoryRows as $row) {
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
            
            // Import default gallery images
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
            
            return "Gallery synchronized with front-end.";
        }
        
        return "";
    } catch (PDOException $e) {
        error_log("Gallery sync error: " . $e->getMessage());
        return "Error synchronizing gallery: " . $e->getMessage();
    }
}';
        
        // Find where to insert the new function
        $user_profile_section = "// Get user profile";
        $user_profile_pos = strpos($admin_php_content, $user_profile_section);
        
        if ($user_profile_pos !== false) {
            // Find the last sync_gallery_with_frontend function
            $last_sync_pos = strrpos($admin_php_content, "function sync_gallery_with_frontend()");
            $end_of_function_pos = strpos($admin_php_content, "// Run synchronization when viewing the gallery", $last_sync_pos);
            
            if ($end_of_function_pos !== false) {
                // Replace all content between the start of the function and the "// Run synchronization" comment
                $new_content = substr($admin_php_content, 0, $last_sync_pos) . $sync_function . "\n\n" . 
                               substr($admin_php_content, $end_of_function_pos);
                
                file_put_contents('admin.php', $new_content);
                echo "Successfully replaced the gallery sync function with a more robust version.<br>";
                return true;
            } else {
                echo "Could not find the end of the sync function to replace.<br>";
                return false;
            }
        } else {
            echo "Could not find the user profile section to place the fixed function before.<br>";
            return false;
        }
    }
}

create_fixed_admin_php();
echo "<p>Now running the gallery sync repair tool...</p>";
include('fix-gallery-sync.php');
echo "<p>Gallery repair complete. <a href='admin.php?view=gallery'>Go to the admin panel</a></p>";
