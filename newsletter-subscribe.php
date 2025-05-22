<?php
header('Content-Type: application/json');

// Database connection
function connect_db() {
    $host = 'localhost';
    $dbname = 'bestluck';
    $username = 'bestluck';
    $password = 'Nocpriss12!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Validate email address
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $language = $_POST['language'] ?? 'en';
    
    // Validate email
    if (empty($email) || !validateEmail($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Connect to database
    $pdo = connect_db();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }
    
    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE email = ?");
        $stmt->execute([$email]);
        $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($subscriber) {
            // Email already exists, check if unsubscribed
            if ($subscriber['status'] === 'unsubscribed') {
                // Reactivate subscription
                $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active', name = ?, language = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $language, $subscriber['id']]);
                echo json_encode(['success' => true, 'message' => 'Your subscription has been reactivated.']);
            } else {
                // Already subscribed
                echo json_encode(['success' => true, 'message' => 'You are already subscribed to our newsletter.']);
            }
        } else {
            // New subscriber
            $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, name, language) VALUES (?, ?, ?)");
            $stmt->execute([$email, $name, $language]);
            echo json_encode(['success' => true, 'message' => 'Thank you for subscribing to our newsletter!']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request.']);
    }
} else {
    // Method not allowed
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>