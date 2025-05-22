<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

// Get all gallery categories
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY name");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error fetching categories: " . $e->getMessage();
    }
}

// Get category information if one is selected
$selectedCategory = null;
$images = [];
if (isset($_GET['category_id']) && is_numeric($_GET['category_id'])) {
    $categoryId = (int)$_GET['category_id'];
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM gallery_categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $selectedCategory = $stmt->fetch();
            
            if ($selectedCategory) {
                $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE category_id = ? ORDER BY id DESC");
                $stmt->execute([$categoryId]);
                $images = $stmt->fetchAll();
            }
        } catch (PDOException $e) {
            $error = "Error fetching category: " . $e->getMessage();
        }
    }
}

// Handle image deletion
if (isset($_GET['delete_image']) && is_numeric($_GET['delete_image'])) {
    $imageId = (int)$_GET['delete_image'];
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    
    if ($pdo) {
        try {
            // Get image filename first for deletion
            $stmt = $pdo->prepare("SELECT filename FROM gallery_images WHERE id = ?");
            $stmt->execute([$imageId]);
            $image = $stmt->fetch();
            
            if ($image) {
                // Delete the image record from database
                $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
                $stmt->execute([$imageId]);
                
                // Delete the file if it exists
                $imagePath = '../assets/images/gallery/' . $image['filename'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                
                // Redirect to avoid resubmission
                header("Location: gallery.php?category_id={$categoryId}&deleted=1");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error deleting image: " . $e->getMessage();
        }
    }
}

// Handle category deletion
if (isset($_GET['delete_category']) && is_numeric($_GET['delete_category'])) {
    $categoryId = (int)$_GET['delete_category'];
    
    if ($pdo) {
        try {
            // Get all images in this category for deletion
            $stmt = $pdo->prepare("SELECT filename FROM gallery_images WHERE category_id = ?");
            $stmt->execute([$categoryId]);
            $categoryImages = $stmt->fetchAll();
            
            // Delete image files
            foreach ($categoryImages as $img) {
                $imagePath = '../assets/images/gallery/' . $img['filename'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete category and all its images (cascade)
            $stmt = $pdo->prepare("DELETE FROM gallery_categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            
            // Redirect to avoid resubmission
            header("Location: gallery.php?category_deleted=1");
            exit;
        } catch (PDOException $e) {
            $error = "Error deleting category: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Management - KOSMO Foundation Admin</title>
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
            <h2 class="text-2xl font-bold">Gallery Management</h2>
            <div class="flex space-x-4">
                <a href="category_edit.php" class="bg-secondary text-white hover:bg-secondary-dark px-4 py-2 rounded-md inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Add Category
                </a>
                <a href="gallery_upload.php<?php echo $selectedCategory ? '?category_id=' . $selectedCategory['id'] : ''; ?>" class="bg-primary text-white hover:bg-primary-dark px-4 py-2 rounded-md inline-flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                    </svg>
                    Upload Images
                </a>
            </div>
        </div>
        
        <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Image was successfully deleted.</p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['category_deleted'])): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Category and all its images were successfully deleted.</p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Categories Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-semibold mb-4">Categories</h3>
                    
                    <?php if (empty($categories)): ?>
                    <p class="text-gray-500">No categories found. <a href="category_edit.php" class="text-primary hover:underline">Add your first category</a>.</p>
                    <?php else: ?>
                    <ul class="space-y-2">
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a href="?category_id=<?php echo $category['id']; ?>" class="flex items-center justify-between hover:bg-gray-50 p-2 rounded <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $category['id']) ? 'bg-primary-light text-primary font-medium' : ''; ?>">
                                <span><?php echo htmlspecialchars($category['name']); ?></span>
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($category['ko_name']); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Images Content -->
            <div class="md:col-span-3">
                <?php if ($selectedCategory): ?>
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-xl font-semibold"><?php echo htmlspecialchars($selectedCategory['name']); ?></h3>
                            <p class="text-gray-600"><?php echo htmlspecialchars($selectedCategory['ko_name']); ?></p>
                        </div>
                        <div class="flex space-x-2">
                            <a href="category_edit.php?id=<?php echo $selectedCategory['id']; ?>" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                                Edit
                            </a>
                            <button onclick="confirmDeleteCategory(<?php echo $selectedCategory['id']; ?>, '<?php echo addslashes($selectedCategory['name']); ?>')" class="text-red-600 hover:text-red-900 inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Delete
                            </button>
                        </div>
                    </div>
                    
                    <?php if (!empty($selectedCategory['description'])): ?>
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Description</h4>
                        <p class="text-gray-600"><?php echo htmlspecialchars($selectedCategory['description']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($selectedCategory['ko_description'])): ?>
                    <div class="mb-6">
                        <h4 class="font-medium mb-2">Korean Description</h4>
                        <p class="text-gray-600"><?php echo htmlspecialchars($selectedCategory['ko_description']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <h4 class="font-medium mb-4">Images (<?php echo count($images); ?>)</h4>
                    
                    <?php if (empty($images)): ?>
                    <p class="text-gray-500">No images in this category yet. <a href="gallery_upload.php?category_id=<?php echo $selectedCategory['id']; ?>" class="text-primary hover:underline">Upload images</a>.</p>
                    <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($images as $image): ?>
                        <div class="bg-gray-50 rounded-lg overflow-hidden shadow-sm border">
                            <img src="../assets/images/gallery/<?php echo htmlspecialchars($image['filename']); ?>" alt="<?php echo htmlspecialchars($image['title'] ?? 'Gallery image'); ?>" class="w-full h-48 object-cover">
                            <div class="p-3">
                                <?php if (!empty($image['title'])): ?>
                                <p class="font-medium"><?php echo htmlspecialchars($image['title']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($image['ko_title'])): ?>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($image['ko_title']); ?></p>
                                <?php endif; ?>
                                <div class="mt-3 flex justify-end">
                                    <a href="image_edit.php?id=<?php echo $image['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                    <button onclick="confirmDeleteImage(<?php echo $image['id']; ?>, <?php echo $selectedCategory['id']; ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold mb-2">Select a Category</h3>
                    <p class="text-gray-600 mb-6">Select a category from the sidebar to view its images or create a new one.</p>
                    <a href="category_edit.php" class="bg-primary text-white hover:bg-primary-dark px-4 py-2 rounded-md inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Create Category
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
    
    <script>
        function confirmDeleteImage(imageId, categoryId) {
            if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
                window.location.href = `gallery.php?category_id=${categoryId}&delete_image=${imageId}`;
            }
        }
        
        function confirmDeleteCategory(categoryId, categoryName) {
            if (confirm(`Are you sure you want to delete the category "${categoryName}" and ALL its images? This action cannot be undone.`)) {
                window.location.href = `gallery.php?delete_category=${categoryId}`;
            }
        }
    </script>
</body>
</html>