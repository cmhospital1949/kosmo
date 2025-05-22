<?php
// Fix the syntax error in admin.php by copying the backup

// Get the backup content
$backup_content = file_get_contents('admin.php.bak');

if ($backup_content) {
    // Write it back to admin.php
    if (file_put_contents('admin.php', $backup_content)) {
        echo "<h1>Admin File Fixed</h1>";
        echo "<p>The admin.php file has been restored from backup.</p>";
        
        // Now add the fix for duplicate category display with the correct syntax
        $admin_content = file_get_contents('admin.php');
        
        // Find the start of the gallery section
        $galleryViewStart = strpos($admin_content, "// Get gallery categories");
        
        if ($galleryViewStart !== false) {
            $galleryViewCode = "// Get gallery categories
\$categories = [];
if (\$view == 'gallery') {
    \$pdo = connect_db();
    if (\$pdo) {
        \$stmt = \$pdo->query(\"SELECT * FROM gallery_categories ORDER BY id\");
        \$categories = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get images for each category
        foreach (\$categories as &\$category) {
            \$stmt = \$pdo->prepare(\"SELECT * FROM gallery_images WHERE category_id = ? ORDER BY id DESC\");
            \$stmt->execute([\$category['id']]);
            \$category['images'] = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}";
            
            $newGalleryViewCode = "// Get gallery categories
\$categories = [];
if (\$view == 'gallery') {
    \$pdo = connect_db();
    if (\$pdo) {
        \$stmt = \$pdo->query(\"SELECT * FROM gallery_categories ORDER BY id\");
        \$categoriesRaw = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Deduplicate categories by name
        \$processed = [];
        foreach (\$categoriesRaw as \$cat) {
            if (!isset(\$processed[\$cat['name']])) {
                \$categories[] = \$cat;
                \$processed[\$cat['name']] = true;
            }
        }
        
        // Get images for each category
        foreach (\$categories as &\$category) {
            \$stmt = \$pdo->prepare(\"SELECT * FROM gallery_images WHERE category_id = ? ORDER BY id DESC\");
            \$stmt->execute([\$category['id']]);
            \$category['images'] = \$stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}";
            
            // Replace the gallery view code
            $admin_content = str_replace($galleryViewCode, $newGalleryViewCode, $admin_content);
            
            // Save the modified file
            if (file_put_contents('admin.php', $admin_content)) {
                echo "<p>Successfully updated the admin.php file to handle duplicate categories.</p>";
            } else {
                echo "<p>Failed to modify admin.php file.</p>";
            }
        } else {
            echo "<p>Could not find the gallery section in admin.php.</p>";
        }
        
        echo "<p><a href='admin.php?view=gallery'>Go to gallery admin page</a></p>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Failed to write the admin.php file.</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>Failed to read the admin.php.bak file.</p>";
}
?>