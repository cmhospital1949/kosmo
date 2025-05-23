<?php
require_once __DIR__ . '/config.php';
// Simplified admin page with working text editor

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
    header("Location: admin-simple.php");
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
if ($isLoggedIn && !isset($_GET['edit'])) {
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOSMO Foundation Simple Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap');
        body {
            font-family: 'Open Sans', 'Noto Sans KR', sans-serif;
        }
    </style>
    <?php if ($isLoggedIn && isset($program)): ?>
    <!-- Simplified rich text editor (Trumbowyg) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css" integrity="sha512-Fm8kRNVGCBZn0sPmwJbVXlqfJmPC13zRsMElZenX6v721g/H7OukJd8XzDEBRQ2FSATK8xNF9UYvzsCtUpfeJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js" integrity="sha512-YJgZG+6o3xSc0k5wv774GS+W1gx0vuSI/kr0E0UylL/Qg/noNspPtYwHPN9q6n59CTR/uhgXfjDXLTRI+uIryg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <?php endif; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <?php if (!$isLoggedIn): ?>
    <!-- Login Form -->
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-blue-600 mb-2">KOSMO Foundation</h1>
                <p class="text-gray-600">Simple Admin Login</p>
            </div>
            
            <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                    <input type="text" id="username" name="username" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div>
                    <button type="submit" name="login" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none">Log In</button>
                </div>
                
                <p class="text-sm text-gray-600 text-center">Default: admin / admin123</p>
            </form>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Admin Dashboard -->
    <header class="bg-blue-600 text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div>
                <a href="admin-simple.php" class="text-2xl font-bold">KOSMO Foundation</a>
                <span class="ml-2 text-sm">Simple Admin</span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="admin-simple.php?action=logout" class="bg-white text-blue-600 hover:bg-gray-100 px-3 py-1 rounded text-sm">Logout</a>
            </div>
        </div>
    </header>

    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8 overflow-x-auto">
                <li><a href="admin-simple.php" class="inline-block py-4 <?php echo !isset($_GET['view']) && !isset($_GET['edit']) ? 'text-blue-600 border-b-2 border-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600'; ?>">Programs</a></li>
                <li><a href="admin-simple.php?view=donations" class="inline-block py-4 <?php echo isset($_GET['view']) && $_GET['view'] == 'donations' ? 'text-blue-600 border-b-2 border-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600'; ?>">Donation Settings</a></li>
                <li><a href="index.php" target="_blank" class="inline-block py-4 text-gray-500 hover:text-blue-600">View Website</a></li>
            </ul>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $message; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['view']) && $_GET['view'] == 'donations'): ?>
        <!-- Donation Settings Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6">Donation Settings</h2>
            
            <form method="POST" class="space-y-6">
                <div>
                    <h3 class="text-xl font-semibold mb-4">Bank Transfer Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="bank_name" class="block text-gray-700 font-medium mb-2">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($donationSettings['bank_name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="account_number" class="block text-gray-700 font-medium mb-2">Account Number</label>
                            <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($donationSettings['account_number'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="account_holder" class="block text-gray-700 font-medium mb-2">Account Holder</label>
                            <input type="text" id="account_holder" name="account_holder" value="<?php echo htmlspecialchars($donationSettings['account_holder'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="business_number" class="block text-gray-700 font-medium mb-2">Business Registration Number</label>
                            <input type="text" id="business_number" name="business_number" value="<?php echo htmlspecialchars($donationSettings['business_number'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold mb-4">Payment Methods</h3>
                    
                    <div class="flex flex-col space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="kakaopay_enabled" <?php echo ($donationSettings['kakaopay_enabled'] ?? 1) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2 text-gray-700">Enable KakaoPay</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="bank_transfer_enabled" <?php echo ($donationSettings['bank_transfer_enabled'] ?? 1) ? 'checked' : ''; ?> class="form-checkbox h-5 w-5 text-blue-600">
                            <span class="ml-2 text-gray-700">Enable Bank Transfer</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-xl font-semibold mb-4">Donation Amount Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="min_donation_amount" class="block text-gray-700 font-medium mb-2">Minimum Donation Amount (₩)</label>
                            <input type="number" id="min_donation_amount" name="min_donation_amount" value="<?php echo htmlspecialchars($donationSettings['min_donation_amount'] ?? 1000); ?>" min="0" class="w-full px-4 py-2 border rounded-md">
                        </div>
                        
                        <div>
                            <label for="default_amount" class="block text-gray-700 font-medium mb-2">Default Donation Amount (₩)</label>
                            <input type="number" id="default_amount" name="default_amount" value="<?php echo htmlspecialchars($donationSettings['default_amount'] ?? 50000); ?>" min="0" class="w-full px-4 py-2 border rounded-md">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="save_donation_settings" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md">Save Settings</button>
                </div>
            </form>
        </div>
        
        <?php elseif (isset($program)): ?>
        <!-- Program Edit Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Edit Program</h2>
                <a href="admin-simple.php" class="text-blue-600 hover:underline">← Back to Programs</a>
            </div>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title (English)</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($program['title']); ?>" class="w-full px-4 py-2 border rounded-md" required>
                    </div>
                    
                    <div>
                        <label for="ko_title" class="block text-sm font-medium text-gray-700 mb-1">Title (Korean)</label>
                        <input type="text" id="ko_title" name="ko_title" value="<?php echo htmlspecialchars($program['ko_title']); ?>" class="w-full px-4 py-2 border rounded-md" required>
                    </div>
                </div>
                
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL identifier)</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($program['slug']); ?>" class="w-full px-4 py-2 border rounded-md" required>
                    <p class="mt-1 text-xs text-gray-500">Used in URLs. Only lowercase letters, numbers, and hyphens.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Short Description (English)</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-md"><?php echo htmlspecialchars($program['description']); ?></textarea>
                    </div>
                    
                    <div>
                        <label for="ko_description" class="block text-sm font-medium text-gray-700 mb-1">Short Description (Korean)</label>
                        <textarea id="ko_description" name="ko_description" rows="3" class="w-full px-4 py-2 border rounded-md"><?php echo htmlspecialchars($program['ko_description']); ?></textarea>
                    </div>
                </div>
                
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image URL</label>
                    <input type="url" id="image" name="image" value="<?php echo htmlspecialchars($program['image']); ?>" class="w-full px-4 py-2 border rounded-md">
                    <?php if ($program['image']): ?>
                    <div class="mt-2">
                        <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="Featured image" class="h-40 rounded">
                    </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English)</label>
                    <textarea id="content" name="content" class="editor w-full"><?php echo $program['content']; ?></textarea>
                </div>
                
                <div>
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean)</label>
                    <textarea id="ko_content" name="ko_content" class="editor w-full"><?php echo $program['ko_content']; ?></textarea>
                </div>
                
                <div class="flex justify-end">
                    <a href="admin-simple.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-5 py-2 rounded-md mr-3">Cancel</a>
                    <button type="submit" name="save_program" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md">Save Program</button>
                </div>
            </form>
        </div>
        
        <?php else: ?>
        <!-- Programs List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                <h2 class="text-2xl font-bold">Programs</h2>
                <p class="text-gray-600">Select a program to edit</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Korean Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($programs as $p): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $p['id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($p['ko_title']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($p['slug']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500"><?php echo date('Y-m-d H:i', strtotime($p['updated_at'])); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="admin-simple.php?edit=<?php echo $p['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <a href="program.php?slug=<?php echo $p['slug']; ?>" target="_blank" class="text-green-600 hover:text-green-900">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Simple Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
    
    <?php if (isset($program)): ?>
    <script>
        $(document).ready(function() {
            $('.editor').trumbowyg({
                btns: [
                    ['viewHTML'],
                    ['formatting'],
                    ['strong', 'em', 'del'],
                    ['superscript', 'subscript'],
                    ['link'],
                    ['insertImage'],
                    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                    ['unorderedList', 'orderedList'],
                    ['horizontalRule'],
                    ['removeformat']
                ],
                autogrow: true
            });
        });
    </script>
    <?php endif; ?>
    <?php endif; ?>
</body>
</html>