<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

// Get all gallery categories for dropdown
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY name");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error fetching categories: " . $e->getMessage();
    }
}

// Set selected category if provided
$selectedCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// Handle form submission
$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoryId = $_POST['category_id'] ?? null;
    $title = $_POST['title'] ?? '';
    $koTitle = $_POST['ko_title'] ?? '';
    $description = $_POST['description'] ?? '';
    $koDescription = $_POST['ko_description'] ?? '';
    
    if (empty($categoryId)) {
        $error = "Please select a category.";
    } elseif (!isset($_FILES['image']) || empty($_FILES['image']['name'])) {
        $error = "Please select an image to upload.";
    } else {
        // Check if the uploads directory exists, create if not
        $uploadDir = '../assets/images/gallery/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Process the uploaded file
        $file = $_FILES['image'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];
        
        // Get file extension
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Allowed extensions
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($fileExt, $allowedExts)) {
            if ($fileError === 0) {
                if ($fileSize < 5000000) { // 5MB max
                    // Generate unique filename
                    $newFileName = uniqid('kosmo_gallery_') . '.' . $fileExt;
                    $destination = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpName, $destination)) {
                        // Insert into database
                        if ($pdo) {
                            try {
                                $stmt = $pdo->prepare("
                                    INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                ");
                                $stmt->execute([
                                    $categoryId,
                                    $title,
                                    $koTitle,
                                    $description,
                                    $koDescription,
                                    $newFileName
                                ]);
                                
                                $success = true;
                            } catch (PDOException $e) {
                                $error = "Error saving image details: " . $e->getMessage();
                            }
                        } else {
                            $error = "Database connection failed.";
                        }
                    } else {
                        $error = "Failed to move uploaded file.";
                    }
                } else {
                    $error = "File size is too large. Maximum allowed is 5MB.";
                }
            } else {
                $error = "Error uploading file. Error code: " . $fileError;
            }
        } else {
            $error = "Invalid file type. Allowed types: " . implode(', ', $allowedExts);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gallery Images - KOSMO Foundation Admin</title>
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
            <h2 class="text-2xl font-bold">Upload Gallery Images</h2>
            <a href="gallery.php<?php echo $selectedCategoryId ? '?category_id=' . $selectedCategoryId : ''; ?>" class="text-primary hover:underline inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Gallery
            </a>
        </div>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Image was successfully uploaded.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if (empty($categories)): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6" role="alert">
            <p>No categories found. <a href="category_edit.php" class="font-bold underline">Create a category</a> before uploading images.</p>
        </div>
        <?php else: ?>
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <div class="mb-6">
                <label for="category_id" class="block text-gray-700 font-medium mb-2">Category <span class="text-red-500">*</span></label>
                <select id="category_id" name="category_id" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($selectedCategoryId == $category['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?> (<?php echo htmlspecialchars($category['ko_name']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mb-6">
                <label for="image" class="block text-gray-700 font-medium mb-2">Image <span class="text-red-500">*</span></label>
                <input type="file" id="image" name="image" accept="image/*" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                <p class="text-sm text-gray-500 mt-1">Accepted formats: JPG, JPEG, PNG, GIF, WebP. Maximum size: 5MB.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="title" class="block text-gray-700 font-medium mb-2">Title (English)</label>
                    <input type="text" id="title" name="title" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label for="ko_title" class="block text-gray-700 font-medium mb-2">Title (Korean)</label>
                    <input type="text" id="ko_title" name="ko_title" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description (English)</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
                
                <div>
                    <label for="ko_description" class="block text-gray-700 font-medium mb-2">Description (Korean)</label>
                    <textarea id="ko_description" name="ko_description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>
            </div>
            
            <div>
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-md font-medium">Upload Image</button>
            </div>
        </form>
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