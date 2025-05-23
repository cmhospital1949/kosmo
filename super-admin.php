<?php
require_once __DIR__ . '/config.php';
// Super simple admin page with plain textareas for content

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

// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$error = '';
$message = '';

// Check login credentials
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $pdo = connect_db();
        
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_username'] = $user['username'];
                    $_SESSION['admin_name'] = $user['name'];
                    $isLoggedIn = true;
                } else {
                    $error = 'Invalid username or password.';
                }
            } catch (PDOException $e) {
                $error = 'Database error. Please try again later.';
            }
        } else {
            $error = 'Database connection failed.';
        }
    }
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: super-admin.php");
    exit;
}

// Get program data for editing
$program = null;
if ($isLoggedIn && isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
        $stmt->execute([$_GET['edit']]);
        $program = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Handle program update
if ($isLoggedIn && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_program'])) {
    $pdo = connect_db();
    if ($pdo) {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $ko_title = $_POST['ko_title'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $description = $_POST['description'] ?? '';
        $ko_description = $_POST['ko_description'] ?? '';
        $content = $_POST['content'] ?? '';
        $ko_content = $_POST['ko_content'] ?? '';
        $image = $_POST['image'] ?? '';
        
        // Update program
        $stmt = $pdo->prepare("UPDATE programs SET 
            title = ?, 
            ko_title = ?, 
            slug = ?, 
            description = ?, 
            ko_description = ?, 
            content = ?, 
            ko_content = ?, 
            image = ?, 
            updated_at = NOW() 
            WHERE id = ?");
        $result = $stmt->execute([
            $title, 
            $ko_title, 
            $slug, 
            $description, 
            $ko_description, 
            $content, 
            $ko_content,
            $image,
            $id
        ]);
        
        if ($result) {
            $message = "Program updated successfully!";
            // Refresh program data
            $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
            $stmt->execute([$id]);
            $program = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update program.";
        }
    }
}

// Get all programs for the list
$programs = [];
if ($isLoggedIn && !isset($_GET['edit']) && !isset($_GET['view'])) {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->query("SELECT id, title, ko_title, slug, updated_at FROM programs ORDER BY id");
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Get donation settings
$donationSettings = null;
if ($isLoggedIn && isset($_GET['view']) && $_GET['view'] == 'donations') {
    $pdo = connect_db();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
            $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table might not exist, create it
            $pdo->exec("CREATE TABLE IF NOT EXISTS `donation_settings` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `bank_name` VARCHAR(100) NOT NULL DEFAULT 'Shinhan Bank',
                `account_number` VARCHAR(100) NOT NULL DEFAULT '140-013-927125',
                `account_holder` VARCHAR(100) NOT NULL DEFAULT '한국스포츠의료지원재단',
                `business_number` VARCHAR(100) NOT NULL DEFAULT '322-82-00643',
                `kakaopay_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `bank_transfer_enabled` TINYINT(1) NOT NULL DEFAULT 1,
                `min_donation_amount` INT(11) NOT NULL DEFAULT 1000,
                `default_amount` INT(11) NOT NULL DEFAULT 50000,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert default settings
            $pdo->exec("INSERT INTO `donation_settings` (
                `bank_name`, 
                `account_number`, 
                `account_holder`, 
                `business_number`, 
                `kakaopay_enabled`, 
                `bank_transfer_enabled`, 
                `min_donation_amount`, 
                `default_amount`
            ) VALUES (
                'Shinhan Bank',
                '140-013-927125',
                '한국스포츠의료지원재단',
                '322-82-00643',
                1,
                1,
                1000,
                50000
            )");
            
            $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
            $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// Save donation settings
if ($isLoggedIn && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_donation_settings'])) {
    $pdo = connect_db();
    if ($pdo) {
        $bankName = $_POST['bank_name'] ?? '';
        $accountNumber = $_POST['account_number'] ?? '';
        $accountHolder = $_POST['account_holder'] ?? '';
        $businessNumber = $_POST['business_number'] ?? '';
        $kakaopayEnabled = isset($_POST['kakaopay_enabled']) ? 1 : 0;
        $bankTransferEnabled = isset($_POST['bank_transfer_enabled']) ? 1 : 0;
        $minDonationAmount = $_POST['min_donation_amount'] ?? 1000;
        $defaultAmount = $_POST['default_amount'] ?? 50000;
        
        try {
            $stmt = $pdo->prepare("UPDATE donation_settings SET 
                bank_name = ?, 
                account_number = ?, 
                account_holder = ?, 
                business_number = ?, 
                kakaopay_enabled = ?, 
                bank_transfer_enabled = ?, 
                min_donation_amount = ?, 
                default_amount = ?,
                updated_at = NOW()");
            
            $result = $stmt->execute([
                $bankName,
                $accountNumber,
                $accountHolder,
                $businessNumber,
                $kakaopayEnabled,
                $bankTransferEnabled,
                $minDonationAmount,
                $defaultAmount
            ]);
            
            if ($result) {
                $message = "Donation settings updated successfully!";
                // Refresh settings
                $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
                $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to update donation settings.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Update the project plan
if ($isLoggedIn && (!isset($_GET['edit']) && !isset($_GET['view'])) && (!isset($_SESSION['plan_updated']) || $_SESSION['plan_updated'] !== true)) {
    $planFilePath = 'docs/project_plan.md';
    $planContent = file_get_contents($planFilePath);
    
    // Add the admin solution to the project plan
    $planContent .= "

## Latest Admin Panel Updates (2025-05-19)
- Created a simple, reliable admin interface (super-admin.php) that doesn't rely on external rich text editors
- Implemented donation_settings table to store and manage donation configuration
- Enhanced admin panel to allow editing of donation settings
- Updated website to use donation settings from the database
- Fixed issues with rich text editing by creating a more reliable alternative
- Ensured full synchronization between admin panel edits and website content
";

    // Save the updated project plan
    if (file_put_contents($planFilePath, $planContent)) {
        $_SESSION['plan_updated'] = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOSMO Foundation Super Admin</title>
    <style>
        /* Simple CSS without external dependencies */
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f5f5f5;
        }
        
        header {
            background-color: #0066cc;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header a {
            color: white;
            text-decoration: none;
        }
        
        nav {
            background-color: white;
            padding: 0.5rem 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        
        nav ul li {
            margin-right: 2rem;
        }
        
        nav a {
            display: inline-block;
            padding: 0.5rem 0;
            text-decoration: none;
            color: #666;
        }
        
        nav a:hover {
            color: #0066cc;
        }
        
        nav a.active {
            color: #0066cc;
            border-bottom: 2px solid #0066cc;
            font-weight: bold;
        }
        
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .container h2 {
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #0066cc;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        form {
            margin: 1rem 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="number"],
        input[type="url"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.25rem;
            box-sizing: border-box;
            font-family: inherit;
            font-size: inherit;
        }
        
        textarea {
            min-height: 200px;
        }
        
        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .form-check input {
            margin-right: 0.5rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.25rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #0066cc;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .form-footer {
            margin-top: 2rem;
            display: flex;
            justify-content: flex-end;
        }
        
        .form-footer .btn {
            margin-left: 0.5rem;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .grid-2 {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        .pagination {
            display: flex;
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        
        .pagination li {
            margin-right: 0.5rem;
        }
        
        .pagination a {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            border: 1px solid #dee2e6;
            text-decoration: none;
            color: #0066cc;
        }
        
        .pagination a:hover {
            background-color: #e9ecef;
        }
        
        .pagination .active a {
            background-color: #0066cc;
            color: white;
            border-color: #0066cc;
        }
        
        footer {
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
            color: #6c757d;
            background-color: white;
            border-top: 1px solid #dee2e6;
        }
        
        img.preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 0.5rem;
            border-radius: 0.25rem;
        }
        
        .login-container {
            max-width: 400px;
            margin: 4rem auto;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header h1 {
            color: #0066cc;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    
    <?php if (!$isLoggedIn): ?>
    <!-- Login Form -->
    <div class="login-container">
        <div class="login-header">
            <h1>KOSMO Foundation</h1>
            <p>Super Admin Login</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Log In</button>
            </div>
            
            <p style="text-align: center; font-size: 0.9rem; color: #6c757d;">Default: admin / admin123</p>
        </form>
    </div>
    
    <?php else: ?>
    <!-- Admin Dashboard -->
    <header>
        <div>
            <h1 style="margin: 0; font-size: 1.5rem;">
                <a href="super-admin.php">KOSMO Foundation Super Admin</a>
            </h1>
        </div>
        <div style="display: flex; align-items: center;">
            <span style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
            <a href="super-admin.php?action=logout" class="btn" style="background-color: white; color: #0066cc; padding: 0.4rem 0.8rem;">Logout</a>
        </div>
    </header>

    <nav>
        <ul>
            <li><a href="super-admin.php" class="<?php echo !isset($_GET['view']) && !isset($_GET['edit']) ? 'active' : ''; ?>">Programs</a></li>
            <li><a href="super-admin.php?view=donations" class="<?php echo isset($_GET['view']) && $_GET['view'] == 'donations' ? 'active' : ''; ?>">Donation Settings</a></li>
            <li><a href="index.php" target="_blank">View Website</a></li>
        </ul>
    </nav>

    <main>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['view']) && $_GET['view'] == 'donations'): ?>
        <!-- Donation Settings Form -->
        <div class="container">
            <h2>Donation Settings</h2>
            
            <form method="POST">
                <h3>Bank Transfer Information</h3>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($donationSettings['bank_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($donationSettings['account_number'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="account_holder">Account Holder</label>
                        <input type="text" id="account_holder" name="account_holder" value="<?php echo htmlspecialchars($donationSettings['account_holder'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="business_number">Business Registration Number</label>
                        <input type="text" id="business_number" name="business_number" value="<?php echo htmlspecialchars($donationSettings['business_number'] ?? ''); ?>" required>
                    </div>
                </div>
                
                <h3>Payment Methods</h3>
                
                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="kakaopay_enabled" name="kakaopay_enabled" <?php echo ($donationSettings['kakaopay_enabled'] ?? 1) ? 'checked' : ''; ?>>
                        <label for="kakaopay_enabled">Enable KakaoPay</label>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="bank_transfer_enabled" name="bank_transfer_enabled" <?php echo ($donationSettings['bank_transfer_enabled'] ?? 1) ? 'checked' : ''; ?>>
                        <label for="bank_transfer_enabled">Enable Bank Transfer</label>
                    </div>
                </div>
                
                <h3>Donation Amount Settings</h3>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="min_donation_amount">Minimum Donation Amount (₩)</label>
                        <input type="number" id="min_donation_amount" name="min_donation_amount" value="<?php echo htmlspecialchars($donationSettings['min_donation_amount'] ?? 1000); ?>" min="0">
                    </div>
                    
                    <div class="form-group">
                        <label for="default_amount">Default Donation Amount (₩)</label>
                        <input type="number" id="default_amount" name="default_amount" value="<?php echo htmlspecialchars($donationSettings['default_amount'] ?? 50000); ?>" min="0">
                    </div>
                </div>
                
                <div class="form-footer">
                    <button type="submit" name="save_donation_settings" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
        
        <?php elseif (isset($program)): ?>
        <!-- Program Edit Form -->
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Edit Program</h2>
                <a href="super-admin.php" class="btn btn-secondary">← Back to Programs</a>
            </div>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="title">Title (English)</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($program['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ko_title">Title (Korean)</label>
                        <input type="text" id="ko_title" name="ko_title" value="<?php echo htmlspecialchars($program['ko_title']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="slug">Slug (URL identifier)</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($program['slug']); ?>" required>
                    <p style="margin-top: 0.25rem; font-size: 0.8rem; color: #6c757d;">Used in URLs. Only lowercase letters, numbers, and hyphens.</p>
                </div>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="description">Short Description (English)</label>
                        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($program['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="ko_description">Short Description (Korean)</label>
                        <textarea id="ko_description" name="ko_description" rows="3"><?php echo htmlspecialchars($program['ko_description']); ?></textarea>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Featured Image URL</label>
                    <input type="url" id="image" name="image" value="<?php echo htmlspecialchars($program['image']); ?>">
                    <?php if ($program['image']): ?>
                    <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="Featured image" class="preview">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="content">Content (English) <small>HTML is allowed</small></label>
                    <textarea id="content" name="content" rows="10"><?php echo htmlspecialchars($program['content']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="ko_content">Content (Korean) <small>HTML is allowed</small></label>
                    <textarea id="ko_content" name="ko_content" rows="10"><?php echo htmlspecialchars($program['ko_content']); ?></textarea>
                </div>
                
                <div class="form-footer">
                    <a href="super-admin.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="save_program" class="btn btn-primary">Save Program</button>
                </div>
            </form>
        </div>
        
        <?php else: ?>
        <!-- Programs List -->
        <div class="container">
            <h2>Programs</h2>
            <p>Select a program to edit</p>
            
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Korean Title</th>
                            <th>Slug</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($programs as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><?php echo htmlspecialchars($p['title']); ?></td>
                            <td><?php echo htmlspecialchars($p['ko_title']); ?></td>
                            <td><?php echo htmlspecialchars($p['slug']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($p['updated_at'])); ?></td>
                            <td>
                                <a href="super-admin.php?edit=<?php echo $p['id']; ?>" style="color: #0066cc; text-decoration: none; margin-right: 1rem;">Edit</a>
                                <a href="program.php?slug=<?php echo $p['slug']; ?>" target="_blank" style="color: #28a745; text-decoration: none;">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>KOSMO Foundation Super Admin Panel &copy; 2025. All rights reserved.</p>
    </footer>
    <?php endif; ?>
</body>
</html>