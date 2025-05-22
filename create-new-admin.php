<?php
// Create a completely new admin_fixed.php file that should work correctly
$original_content = file_get_contents('admin.php.bak');

if (!$original_content) {
    echo "Could not read the admin.php.bak file.";
    exit;
}

// Save as a new file
if (file_put_contents('admin_fixed.php', $original_content)) {
    echo "<h1>Created New Admin File</h1>";
    echo "<p>A new admin_fixed.php file has been created from the backup.</p>";
    echo "<p>Try to access the <a href='admin_fixed.php'>new admin page</a>.</p>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Failed to create admin_fixed.php file.</p>";
}
?>