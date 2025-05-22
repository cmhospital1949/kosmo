<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

$program = null;
$error = '';

// Get program by ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
            $stmt->execute([$id]);
            $program = $stmt->fetch();
            
            if (!$program) {
                $error = "Program not found.";
            }
        } catch (PDOException $e) {
            $error = "Error fetching program: " . $e->getMessage();
        }
    } else {
        $error = "Database connection failed.";
    }
} else {
    $error = "Invalid program ID.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Program - KOSMO Foundation Admin</title>
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
                <li><a href="programs.php" class="inline-block py-4 text-primary border-b-2 border-primary font-medium">Programs</a></li>
                <li><a href="gallery.php" class="inline-block py-4 text-gray-500 hover:text-primary">Gallery</a></li>
                <li><a href="profile.php" class="inline-block py-4 text-gray-500 hover:text-primary">Profile</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">View Program</h2>
            <div class="flex space-x-4">
                <a href="programs.php" class="text-primary hover:underline inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Back to Programs
                </a>
                <?php if ($program): ?>
                <a href="program_edit.php?id=<?php echo $program['id']; ?>" class="text-blue-600 hover:underline inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit Program
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php elseif ($program): ?>
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Program Header -->
            <div class="relative">
                <?php if (!empty($program['image'])): ?>
                <img src="<?php echo htmlspecialchars($program['image']); ?>" alt="<?php echo htmlspecialchars($program['title']); ?>" class="w-full h-64 object-cover">
                <?php else: ?>
                <div class="w-full h-64 bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-500">No Image Available</span>
                </div>
                <?php endif; ?>
                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 p-6">
                    <h1 class="text-white text-2xl font-bold"><?php echo htmlspecialchars($program['title']); ?></h1>
                    <p class="text-white opacity-90 mt-1"><?php echo htmlspecialchars($program['ko_title']); ?></p>
                </div>
            </div>
            
            <!-- Program Details -->
            <div class="p-6">
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Slug</h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($program['slug']); ?></p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Description (English)</h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($program['description']); ?></p>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-2">Description (Korean)</h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($program['ko_description']); ?></p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Content (English)</h2>
                    <div class="prose max-w-none border rounded-md p-4 bg-gray-50">
                        <?php echo $program['content']; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Content (Korean)</h2>
                    <div class="prose max-w-none border rounded-md p-4 bg-gray-50">
                        <?php echo $program['ko_content']; ?>
                    </div>
                </div>
                
                <div class="border-t pt-4 text-sm text-gray-500">
                    <p>Created: <?php echo date('Y-m-d H:i', strtotime($program['created_at'])); ?></p>
                    <?php if ($program['updated_at']): ?>
                    <p>Last Updated: <?php echo date('Y-m-d H:i', strtotime($program['updated_at'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- View on Website -->
        <div class="mt-6 text-center">
            <a href="../program.php?slug=<?php echo $program['slug']; ?>" target="_blank" class="inline-flex items-center justify-center bg-secondary text-white px-6 py-3 rounded-md hover:bg-secondary-dark">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                </svg>
                View on Website
            </a>
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