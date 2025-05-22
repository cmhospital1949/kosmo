<?php
// Directly fix the admin.php page to resolve display issues

// Fix categories display in admin.php
function fix_admin_gallery_display() {
    // Read the admin.php file content
    $admin_php = file_get_contents('admin.php');
    
    if (!$admin_php) {
        echo "Failed to read admin.php file.";
        return false;
    }
    
    // Fix the issue that causes duplicate category display
    // Look for the gallery view section
    $gallery_view_pattern = "/\\\$view == 'gallery'.*?categories = \\\$stmt->fetchAll\(PDO::FETCH_ASSOC\);/s";
    
    if (preg_match($gallery_view_pattern, $admin_php, $matches)) {
        $gallery_section = $matches[0];
        
        // Create a replacement that ensures categories are unique
        $fixed_gallery_section = str_replace(
            "categories = \$stmt->fetchAll(PDO::FETCH_ASSOC);",
            "categories = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure unique categories by name
        \$uniqueCategories = [];
        \$processedNames = [];
        
        foreach (\$categories as \$category) {
            if (!isset(\$processedNames[\$category['name']])) {
                \$uniqueCategories[] = \$category;
                \$processedNames[\$category['name']] = true;
            }
        }
        
        \$categories = \$uniqueCategories;",
            $gallery_section
        );
        
        // Replace in the original file
        $fixed_admin_php = str_replace($gallery_section, $fixed_gallery_section, $admin_php);
        
        // Write the fixed content back
        if (file_put_contents('admin.php', $fixed_admin_php)) {
            echo "<p>Successfully fixed the gallery display in admin.php. The page should now show each category only once.</p>";
            return true;
        } else {
            echo "<p>Failed to write updated admin.php file.</p>";
            return false;
        }
    } else {
        echo "<p>Could not find the gallery view section in admin.php.</p>";
        return false;
    }
}

echo "<h1>Admin Gallery Display Fix</h1>";
fix_admin_gallery_display();
echo "<p><a href='admin.php?view=gallery'>Check Gallery Admin Now</a></p>";
?>