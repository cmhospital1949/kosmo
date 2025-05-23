<?php
require_once __DIR__ . '/config.php';
// This script fixes the duplicate "Athlete Support" category in the gallery

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

// Fix duplicate categories
function fix_duplicate_categories() {
    $pdo = connect_db();
    
    if (!$pdo) {
        echo "Database connection failed.";
        return false;
    }
    
    try {
        // Find all Athlete Support categories
        $stmt = $pdo->prepare("SELECT id, name FROM gallery_categories WHERE name = 'Athlete Support'");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($categories) > 1) {
            echo "<p>Found " . count($categories) . " duplicate 'Athlete Support' categories.</p>";
            
            // Get the first category ID (to keep)
            $keepCategoryId = $categories[0]['id'];
            
            // Go through all other categories and move their images to the first category
            for ($i = 1; $i < count($categories); $i++) {
                $duplicateId = $categories[$i]['id'];
                
                // Move images to the first category
                $stmt = $pdo->prepare("UPDATE gallery_images SET category_id = ? WHERE category_id = ?");
                $stmt->execute([$keepCategoryId, $duplicateId]);
                $movedImages = $stmt->rowCount();
                
                echo "<p>Moved $movedImages images from category ID $duplicateId to category ID $keepCategoryId.</p>";
                
                // Delete the duplicate category
                $stmt = $pdo->prepare("DELETE FROM gallery_categories WHERE id = ?");
                $stmt->execute([$duplicateId]);
                
                echo "<p>Deleted duplicate category ID $duplicateId.</p>";
            }
            
            echo "<p>Successfully fixed duplicate categories.</p>";
            return true;
        } else {
            echo "<p>No duplicate 'Athlete Support' categories found.</p>";
            
            // Check for other potential duplicates
            $stmt = $pdo->query("SELECT name, COUNT(*) as count FROM gallery_categories GROUP BY name HAVING count > 1");
            $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($duplicates)) {
                echo "<p>Found other duplicate categories:</p><ul>";
                foreach ($duplicates as $dup) {
                    echo "<li>{$dup['name']} ({$dup['count']} duplicates)</li>";
                    
                    // Get all instances of this category
                    $stmt = $pdo->prepare("SELECT id FROM gallery_categories WHERE name = ?");
                    $stmt->execute([$dup['name']]);
                    $dupCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Keep the first one, delete the rest
                    $keepCategoryId = $dupCategories[0]['id'];
                    
                    for ($i = 1; $i < count($dupCategories); $i++) {
                        $duplicateId = $dupCategories[$i]['id'];
                        
                        // Move images to the first category
                        $stmt = $pdo->prepare("UPDATE gallery_images SET category_id = ? WHERE category_id = ?");
                        $stmt->execute([$keepCategoryId, $duplicateId]);
                        $movedImages = $stmt->rowCount();
                        
                        echo "<p>Moved $movedImages images from category ID $duplicateId to category ID $keepCategoryId.</p>";
                        
                        // Delete the duplicate category
                        $stmt = $pdo->prepare("DELETE FROM gallery_categories WHERE id = ?");
                        $stmt->execute([$duplicateId]);
                        
                        echo "<p>Deleted duplicate category ID $duplicateId.</p>";
                    }
                }
                echo "</ul>";
            }
            
            return false;
        }
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
        return false;
    }
}

// List all categories to verify
function list_categories() {
    $pdo = connect_db();
    
    if (!$pdo) {
        echo "Database connection failed.";
        return;
    }
    
    try {
        $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY id");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Current Gallery Categories:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Korean Name</th><th>Image Count</th></tr>";
        
        foreach ($categories as $category) {
            // Count images in this category
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_images WHERE category_id = ?");
            $stmt->execute([$category['id']]);
            $imageCount = $stmt->fetchColumn();
            
            echo "<tr>";
            echo "<td>{$category['id']}</td>";
            echo "<td>{$category['name']}</td>";
            echo "<td>{$category['ko_name']}</td>";
            echo "<td>{$imageCount}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p>Error listing categories: " . $e->getMessage() . "</p>";
    }
}

echo "<h1>Gallery Category Fix Tool</h1>";

// First, show current categories
echo "<h2>Before Fix:</h2>";
list_categories();

// Fix duplicate categories
fix_duplicate_categories();

// Show categories after the fix
echo "<h2>After Fix:</h2>";
list_categories();

echo "<p><a href='admin.php?view=gallery'>Return to Gallery Admin</a></p>";
?>