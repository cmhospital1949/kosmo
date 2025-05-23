<?php
require_once __DIR__ . '/lib/Database.php';

try {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->execute(['admin']);
    $id = $stmt->fetchColumn();

    $hashed = password_hash('admin123', PASSWORD_DEFAULT);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $id]);
        echo "Admin password reset successfully.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, name, email) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', $hashed, 'KOSMO Admin', 'goodwill@kosmo.or.kr']);
        echo "Admin user created successfully.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
