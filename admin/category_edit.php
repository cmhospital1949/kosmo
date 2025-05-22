<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

$category = [
    'id' => '',
    'name' => '',
    'ko_name' => '',
    'description' => '',
    'ko_description' => ''
];

$isEdit = false;
$success = false;
$error = '';

// If editing existing category
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $isEdit = true;
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM gallery_categories WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result) {
                $category = $result;
            } else {
                $error = "Category not found.";
            }
        } catch (PDOException $e) {
            $error = "Error fetching category: " . $e->getMessage();
        }
    }
}

// Form submission processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $category['name'] = $_POST['name'] ?? '';
    $category['ko_name'] = $_POST['ko_name'] ?? '';
    $category['description'] = $_POST['description'] ?? '';
    $category['ko_description'] = $_POST['ko_description'] ?? '';
    
    // Validate form data
    if (empty($category['name']) || empty($category['ko_name'])) {
        $error = "Please fill in all required fields.";
    } else {
        if ($pdo) {
            try {
                if ($isEdit) {
                    // Update existing category
                    $stmt = $pdo->prepare("
                        UPDATE gallery_categories 
                        SET name = ?, ko_name = ?, description = ?, ko_description = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $category['name'],
                        $category['ko_name'],
                        $category['description'],
                        $category['ko_description'],
                        $category['id']
                    ]);
                } else {
                    // Insert new category
                    $stmt = $pdo->prepare("
                        INSERT INTO gallery_categories (name, ko_name, description, ko_description)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $category['name'],
                        $category['ko_name'],
                        $category['description'],
                        $category['ko_description']
                    ]);
                    
                    // Get the ID of the new category
                    $category['id'] = $pdo->lastInsertId();
                }
                
                $success = true;
                $isEdit = true; // Now we're editing this category
            } catch (PDOException $e) {
                $error = "Error saving category: " . $e->getMessage();
            }
        } else {
            $error = "Database connection failed.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Gallery Category - KOSMO Foundation Admin</title>
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
                <li><a href="gallery.php" class="inline-block py-4 text-primary border-b-2 border-primary font-medium">Gallery</a></li>
                <li><a href="profile.php" class="inline-block py-4 text-gray-500 hover:text-primary">Profile</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold"><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Gallery Category</h2>
            <a href="gallery.php<?php echo $isEdit ? '?category_id=' . $category['id'] : ''; ?>" class="text-primary hover:underline inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Gallery
            </a>
        </div>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Category was successfully saved.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white rounded-lg shadow-md p-6">
            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="name" class="block text-gray-700 font-medium mb-2">Category Name (English) <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                
                <div>
                    <label for="ko_name" class="block text-gray-700 font-medium mb-2">Category Name (Korean) <span class="text-red-500">*</span></label>
                    <input type="text" id="ko_name" name="ko_name" value="<?php echo htmlspecialchars($category['ko_name']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description (English)</label>
                    <textarea id="description" name="description" rows="5" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>
                
                <div>
                    <label for="ko_description" class="block text-gray-700 font-medium mb-2">Description (Korean)</label>
                    <textarea id="ko_description" name="ko_description" rows="5" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($category['ko_description']); ?></textarea>
                </div>
            </div>
            
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-md font-medium">Save Category</button>
                <?php if ($isEdit): ?>
                <a href="gallery_upload.php?category_id=<?php echo $category['id']; ?>" class="text-primary hover:underline">Upload Images to This Category</a>
                <?php endif; ?>
            </div>
        </form>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>