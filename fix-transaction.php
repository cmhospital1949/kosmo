<?php
// Quick fix for the transaction error in the reset gallery function

// Read the admin.php file
$admin_content = file_get_contents('admin.php');

if (!$admin_content) {
    echo "Failed to read admin.php file.";
    exit;
}

// Find the problematic rollBack call
$rollbackPos = strpos($admin_content, "// Rollback if an error occurred\n            \$pdo->rollBack();");

if ($rollbackPos !== false) {
    // Replace with a safer version that checks if a transaction is active
    $oldCode = "// Rollback if an error occurred\n            \$pdo->rollBack();";
    $newCode = "// Rollback if an error occurred\n            if (\$pdo->inTransaction()) {\n                \$pdo->rollBack();\n            }";
    
    $admin_content = str_replace($oldCode, $newCode, $admin_content);
    
    // Also fix the try/catch around the transaction
    $transactionCode = "\$pdo->beginTransaction();";
    $newTransactionCode = "try {\n                \$pdo->beginTransaction();";
    
    $admin_content = str_replace($transactionCode, $newTransactionCode, $admin_content);
    
    // Save the fixed file
    if (file_put_contents('admin.php', $admin_content)) {
        echo "<h1>Fixed Transaction Error</h1>";
        echo "<p>The transaction rollback error in the reset gallery function has been fixed.</p>";
        echo "<p>Please try to <a href='admin.php?view=gallery&action=reset_gallery'>reset the gallery</a> again.</p>";
    } else {
        echo "<h1>Error</h1>";
        echo "<p>Failed to write the updated admin.php file.</p>";
    }
} else {
    echo "<h1>Error</h1>";
    echo "<p>Could not find the rollback code in admin.php.</p>";
}
?>