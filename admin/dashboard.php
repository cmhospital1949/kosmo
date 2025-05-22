<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

// Get program count
$programCount = 0;
if ($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM programs");
    $programCount = $stmt->fetchColumn();
}

// Get gallery image count
$imageCount = 0;
if ($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM gallery_images");
    $imageCount = $stmt->fetchColumn();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KOSMO Foundation Admin - Dashboard</title>
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
                <li><a href="dashboard.php" class="inline-block py-4 text-primary border-b-2 border-primary font-medium">Dashboard</a></li>
                <li><a href="programs.php" class="inline-block py-4 text-gray-500 hover:text-primary">Programs</a></li>
                <li><a href="gallery.php" class="inline-block py-4 text-gray-500 hover:text-primary">Gallery</a></li>
                <li><a href="profile.php" class="inline-block py-4 text-gray-500 hover:text-primary">Profile</a></li>
                <li><a href="database.php" class="inline-block py-4 text-gray-500 hover:text-primary">Database</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Dashboard Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-2">Programs</h2>
                <p class="text-3xl font-bold text-primary"><?php echo $programCount; ?></p>
                <a href="programs.php" class="mt-4 inline-block text-primary hover:underline">Manage Programs →</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-2">Gallery Images</h2>
                <p class="text-3xl font-bold text-primary"><?php echo $imageCount; ?></p>
                <a href="gallery.php" class="mt-4 inline-block text-primary hover:underline">Manage Gallery →</a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-semibold mb-2">Website</h2>
                <p class="text-base text-gray-600 mb-4">View and manage the public-facing website.</p>
                <a href="../index.php" target="_blank" class="mt-2 inline-block text-primary hover:underline">Visit Website →</a>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="program_edit.php" class="bg-primary text-white hover:bg-primary-dark p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Program
                </a>
                <a href="gallery_upload.php" class="bg-secondary text-white hover:bg-secondary-dark p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Upload Images
                </a>
                <a href="programs.php" class="bg-accent text-white hover:bg-accent-dark p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    View All Programs
                </a>
                <a href="database.php" class="bg-gray-500 text-white hover:bg-gray-600 p-4 rounded-lg text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                    </svg>
                    Database Management
                </a>
            </div>
        </div>
        
        <!-- System Information -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4">System Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="font-medium">PHP Version</p>
                    <p class="text-gray-600"><?php echo phpversion(); ?></p>
                </div>
                <div>
                    <p class="font-medium">Server</p>
                    <p class="text-gray-600"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                </div>
                <div>
                    <p class="font-medium">Last Login</p>
                    <p class="text-gray-600"><?php echo date('Y-m-d H:i:s'); ?></p>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>