<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

$user = null;
$error = '';
$success = false;
$passwordSuccess = false;

// Get user data
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Error fetching user data: " . $e->getMessage();
    }
}

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($name) || empty($email)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE admin_users SET name = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $email, $_SESSION['admin_id']]);
                
                // Update session variable
                $_SESSION['admin_name'] = $name;
                
                $success = true;
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                $error = "Error updating profile: " . $e->getMessage();
            }
        }
    }
}

// Handle form submission for password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = "Please fill in all password fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "New password and confirmation do not match.";
    } elseif (strlen($newPassword) < 8) {
        $error = "New password must be at least 8 characters long.";
    } else {
        if ($pdo && $user) {
            // Verify current password
            if (password_verify($currentPassword, $user['password'])) {
                try {
                    // Hash the new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    // Update the password
                    $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $_SESSION['admin_id']]);
                    
                    $passwordSuccess = true;
                } catch (PDOException $e) {
                    $error = "Error changing password: " . $e->getMessage();
                }
            } else {
                $error = "Current password is incorrect.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - KOSMO Foundation Admin</title>
    <!-- Link to Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Configure Tailwind with brand colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#0066cc',
                        secondary: '#4d9aff',
                        accent: '#ff6b00',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap');
        body {
            font-family: 'Open Sans', 'Noto Sans KR', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-primary text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">KOSMO Foundation Admin</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="bg-white text-primary hover:bg-gray-100 px-3 py-1 rounded text-sm">Logout</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8">
                <li><a href="dashboard.php" class="inline-block py-4 text-gray-500 hover:text-primary">Dashboard</a></li>
                <li><a href="programs.php" class="inline-block py-4 text-gray-500 hover:text-primary">Programs</a></li>
                <li><a href="gallery.php" class="inline-block py-4 text-gray-500 hover:text-primary">Gallery</a></li>
                <li><a href="profile.php" class="inline-block py-4 text-primary border-b-2 border-primary font-medium">Profile</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-6">Your Profile</h2>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Your profile was successfully updated.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($passwordSuccess): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Your password was successfully changed.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($user): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-6">Profile Information</h3>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div>
                        <label for="username" class="block text-gray-700 font-medium mb-2">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full px-4 py-2 border rounded-md bg-gray-100" readonly disabled>
                        <p class="text-sm text-gray-500 mt-1">Username cannot be changed.</p>
                    </div>
                    
                    <div>
                        <label for="name" class="block text-gray-700 font-medium mb-2">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <label for="last_login" class="block text-gray-700 font-medium mb-2">Last Login</label>
                        <input type="text" id="last_login" value="<?php echo $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'N/A'; ?>" class="w-full px-4 py-2 border rounded-md bg-gray-100" readonly disabled>
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-md font-medium">Update Profile</button>
                    </div>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-xl font-semibold mb-6">Change Password</h3>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div>
                        <label for="current_password" class="block text-gray-700 font-medium mb-2">Current Password <span class="text-red-500">*</span></label>
                        <input type="password" id="current_password" name="current_password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-gray-700 font-medium mb-2">New Password <span class="text-red-500">*</span></label>
                        <input type="password" id="new_password" name="new_password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                        <p class="text-sm text-gray-500 mt-1">Password must be at least 8 characters long.</p>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-gray-700 font-medium mb-2">Confirm New Password <span class="text-red-500">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-secondary hover:bg-secondary-dark text-white px-6 py-3 rounded-md font-medium">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-xl font-semibold mb-2">Error Loading Profile</h3>
            <p class="text-gray-600 mb-6">Your profile information could not be loaded. Please try again later or contact technical support.</p>
            <a href="dashboard.php" class="text-primary hover:underline">Return to Dashboard</a>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>