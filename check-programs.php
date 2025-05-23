<?php
// Database connection
require_once __DIR__ . '/lib/Database.php';

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all programs
    $stmt = $pdo->query("SELECT * FROM programs");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h1>Programs in Database</h1>";
    echo "<p>Total programs: " . count($programs) . "</p>";
    
    foreach ($programs as $program) {
        echo "<h2>" . htmlspecialchars($program['title']) . " (ID: " . $program['id'] . ")</h2>";
        echo "<p>Slug: " . htmlspecialchars($program['slug']) . "</p>";
        echo "<p>Korean Title: " . htmlspecialchars($program['ko_title']) . "</p>";
        echo "<hr>";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
