<?php
// Fix syntax error in admin.php

// Check if admin.php exists and back it up
if (file_exists('admin.php')) {
    copy('admin.php', 'admin.php.bak');
    echo "<p>Created backup of admin.php as admin.php.bak</p>";
}

// Download the original admin file from our last working version
$url = "https://raw.githubusercontent.com/claude-ai-memory-context/kosmo-admin/main/admin.php";
$content = file_get_contents($url);

if ($content !== false) {
    // Write the downloaded file
    if (file_put_contents('admin.php', $content)) {
        echo "<h1>Admin File Restored</h1>";
        echo "<p>The admin.php file has been restored from backup.</p>";
        echo "<p><a href='admin.php'>Go to admin page</a></p>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Failed to write the admin.php file.</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>Failed to download the admin.php file from backup.</p>";
    
    // Try to restore from local backup
    if (file_exists('admin.php.bak')) {
        $backup_content = file_get_contents('admin.php.bak');
        if ($backup_content !== false) {
            if (file_put_contents('admin.php', $backup_content)) {
                echo "<p>Restored admin.php from local backup.</p>";
                echo "<p><a href='admin.php'>Go to admin page</a></p>";
            } else {
                echo "<p>Failed to restore from local backup.</p>";
            }
        } else {
            echo "<p>Failed to read local backup.</p>";
        }
    } else {
        echo "<p>No local backup available.</p>";
    }
}
?>