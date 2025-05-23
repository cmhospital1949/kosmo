<?php
require_once __DIR__ . '/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

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

$pdo = connect_db();

// Initialize variables
$message = '';
$messageType = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$post = null;
$categories = [];

// Get categories for dropdown
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM news_categories ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $message = "Error fetching categories: " . $e->getMessage();
    $messageType = 'error';
}

// Validate slug
function validateSlug($slug) {
    return preg_match('/^[a-z0-9-]+$/', $slug);
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Create or update post
        if ($_POST['action'] === 'save_post') {
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $title = $_POST['title'] ?? '';
            $ko_title = $_POST['ko_title'] ?? '';
            $excerpt = $_POST['excerpt'] ?? '';
            $ko_excerpt = $_POST['ko_excerpt'] ?? '';
            $content = $_POST['content'] ?? '';
            $ko_content = $_POST['ko_content'] ?? '';
            $category = $_POST['category'] ?? '';
            $author = $_POST['author'] ?? 'KOSMO Foundation';
            $slug = $_POST['slug'] ?? '';
            $publish_date = $_POST['publish_date'] ?? date('Y-m-d H:i:s');
            $published = isset($_POST['published']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;
            $cover_image = $_POST['cover_image'] ?? '';
            
            // Validate required fields
            if (empty($title) || empty($ko_title) || empty($content) || empty($ko_content) || empty($slug) || empty($category)) {
                $message = "Please fill in all required fields.";
                $messageType = 'error';
            } elseif (!validateSlug($slug)) {
                $message = "Slug must contain only lowercase letters, numbers, and hyphens.";
                $messageType = 'error';
            } else {
                try {
                    if ($id) {
                        // Update existing post
                        $stmt = $pdo->prepare("UPDATE news_posts SET 
                            title = ?, ko_title = ?, excerpt = ?, ko_excerpt = ?, content = ?, ko_content = ?, 
                            category = ?, author = ?, slug = ?, publish_date = ?, published = ?, featured = ?, 
                            cover_image = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $title, $ko_title, $excerpt, $ko_excerpt, $content, $ko_content, 
                            $category, $author, $slug, $publish_date, $published, $featured,
                            $cover_image, $id
                        ]);
                        $message = "Post updated successfully.";
                        $messageType = 'success';
                    } else {
                        // Check if slug already exists
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM news_posts WHERE slug = ?");
                        $stmt->execute([$slug]);
                        $slugExists = $stmt->fetchColumn();
                        
                        if ($slugExists) {
                            $message = "A post with this slug already exists.";
                            $messageType = 'error';
                        } else {
                            // Create new post
                            $stmt = $pdo->prepare("INSERT INTO news_posts 
                                (title, ko_title, excerpt, ko_excerpt, content, ko_content, category, author, slug, 
                                publish_date, published, featured, cover_image, created_at) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                            $stmt->execute([
                                $title, $ko_title, $excerpt, $ko_excerpt, $content, $ko_content, 
                                $category, $author, $slug, $publish_date, $published, $featured, $cover_image
                            ]);
                            $message = "Post created successfully.";
                            $messageType = 'success';
                            
                            // Redirect to list after successful creation
                            header('Location: admin-news.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                            exit;
                        }
                    }
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete post
        elseif ($_POST['action'] === 'delete_post' && isset($_POST['id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM news_posts WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = "Post deleted successfully.";
                $messageType = 'success';
                
                // Redirect to list after successful deletion
                header('Location: admin-news.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                exit;
            } catch (PDOException $e) {
                $message = "Error deleting post: " . $e->getMessage();
                $messageType = 'error';
            }
        }
        
        // Save category
        elseif ($_POST['action'] === 'save_category') {
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $name = $_POST['name'] ?? '';
            $ko_name = $_POST['ko_name'] ?? '';
            $description = $_POST['description'] ?? '';
            $ko_description = $_POST['ko_description'] ?? '';
            $slug = $_POST['slug'] ?? '';
            
            // Validate required fields
            if (empty($name) || empty($ko_name) || empty($slug)) {
                $message = "Please fill in all required fields.";
                $messageType = 'error';
            } elseif (!validateSlug($slug)) {
                $message = "Slug must contain only lowercase letters, numbers, and hyphens.";
                $messageType = 'error';
            } else {
                try {
                    if ($id) {
                        // Update existing category
                        $stmt = $pdo->prepare("UPDATE news_categories SET 
                            name = ?, ko_name = ?, description = ?, ko_description = ?, 
                            slug = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $name, $ko_name, $description, $ko_description, $slug, $id
                        ]);
                        $message = "Category updated successfully.";
                        $messageType = 'success';
                    } else {
                        // Check if slug or name already exists
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM news_categories WHERE slug = ? OR name = ?");
                        $stmt->execute([$slug, $name]);
                        $exists = $stmt->fetchColumn();
                        
                        if ($exists) {
                            $message = "A category with this slug or name already exists.";
                            $messageType = 'error';
                        } else {
                            // Create new category
                            $stmt = $pdo->prepare("INSERT INTO news_categories 
                                (name, ko_name, description, ko_description, slug, created_at) 
                                VALUES (?, ?, ?, ?, ?, NOW())");
                            $stmt->execute([
                                $name, $ko_name, $description, $ko_description, $slug
                            ]);
                            $message = "Category created successfully.";
                            $messageType = 'success';
                            
                            // Refresh categories list
                            $stmt = $pdo->query("SELECT * FROM news_categories ORDER BY name");
                            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            // Redirect to category list
                            header('Location: admin-news.php?action=categories&message=' . urlencode($message) . '&messageType=' . $messageType);
                            exit;
                        }
                    }
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete category
        elseif ($_POST['action'] === 'delete_category' && isset($_POST['id'])) {
            try {
                // Check if there are posts using this category
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM news_posts WHERE category = (SELECT slug FROM news_categories WHERE id = ?)");
                $stmt->execute([$_POST['id']]);
                $postCount = $stmt->fetchColumn();
                
                if ($postCount > 0) {
                    $message = "Cannot delete category: there are {$postCount} posts using this category.";
                    $messageType = 'error';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM news_categories WHERE id = ?");
                    $stmt->execute([$_POST['id']]);
                    $message = "Category deleted successfully.";
                    $messageType = 'success';
                    
                    // Redirect to category list
                    header('Location: admin-news.php?action=categories&message=' . urlencode($message) . '&messageType=' . $messageType);
                    exit;
                }
            } catch (PDOException $e) {
                $message = "Error deleting category: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Handle URL message parameters
if (isset($_GET['message']) && isset($_GET['messageType'])) {
    $message = $_GET['message'];
    $messageType = $_GET['messageType'];
}

// Get post for editing
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM news_posts WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$post) {
            $message = "Post not found.";
            $messageType = 'error';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $message = "Error fetching post: " . $e->getMessage();
        $messageType = 'error';
        $action = 'list';
    }
}

// Get category for editing
if ($action === 'edit_category' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM news_categories WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$category) {
            $message = "Category not found.";
            $messageType = 'error';
            $action = 'categories';
        }
    } catch (PDOException $e) {
        $message = "Error fetching category: " . $e->getMessage();
        $messageType = 'error';
        $action = 'categories';
    }
}

// Get posts for listing
$posts = [];
if ($action === 'list') {
    try {
        $stmt = $pdo->query("SELECT * FROM news_posts ORDER BY publish_date DESC");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching posts: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get all categories for category listing
$allCategories = [];
if ($action === 'categories') {
    try {
        $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as post_count 
                            FROM news_categories c
                            LEFT JOIN news_posts p ON c.slug = p.category
                            GROUP BY c.id
                            ORDER BY c.name");
        $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $message = "Error fetching categories: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Function to get category name by slug
function getCategoryName($slug, $categories) {
    foreach ($categories as $category) {
        if ($category['slug'] === $slug) {
            return $category['name'];
        }
    }
    return $slug;
}

// Function to truncate long text
function truncate($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Management - KOSMO Foundation Admin</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.2/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.2/dist/quill.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">KOSMO Foundation Admin</h1>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="admin.php" class="text-blue-200 hover:text-white">Dashboard</a>
                <a href="admin.php?logout=1" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold">
                <?php 
                if ($action === 'add') echo 'Add New Post';
                elseif ($action === 'edit') echo 'Edit Post';
                elseif ($action === 'categories') echo 'Manage Categories';
                elseif ($action === 'add_category') echo 'Add New Category';
                elseif ($action === 'edit_category') echo 'Edit Category';
                else echo 'News Posts';
                ?>
            </h2>
            
            <div class="flex space-x-3">
                <?php if ($action !== 'list' && $action !== 'categories'): ?>
                    <a href="admin-news.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to List</a>
                <?php endif; ?>
                
                <?php if ($action === 'list'): ?>
                    <a href="admin-news.php?action=add" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add New Post</a>
                    <a href="admin-news.php?action=categories" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">Manage Categories</a>
                <?php endif; ?>
                
                <?php if ($action === 'categories'): ?>
                    <a href="admin-news.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to Posts</a>
                    <a href="admin-news.php?action=add_category" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add New Category</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <!-- Posts List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Publish Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($posts) > 0): ?>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($post['title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($post['ko_title']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars(getCategoryName($post['category'], $categories)); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('Y-m-d H:i', strtotime($post['publish_date'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($post['published']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Published
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Draft
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($post['featured']): ?>
                                            <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Featured
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="admin-news.php?action=edit&id=<?php echo $post['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <a href="#" onclick="confirmDelete(<?php echo $post['id']; ?>, '<?php echo addslashes($post['title']); ?>')" class="text-red-600 hover:text-red-900">Delete</a>
                                        <a href="news-post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="text-green-600 hover:text-green-900 ml-3">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No posts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
                    <p id="deleteMessage" class="mb-6">Are you sure you want to delete this post?</p>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeDeleteModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                        <form id="deleteForm" method="POST">
                            <input type="hidden" name="action" value="delete_post">
                            <input type="hidden" id="deleteId" name="id" value="">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Post Form -->
            <form method="POST" class="bg-white rounded-lg shadow-md overflow-hidden p-6">
                <input type="hidden" name="action" value="save_post">
                <?php if ($post): ?>
                    <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title (English) *</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="ko_title" class="block text-sm font-medium text-gray-700 mb-1">Title (Korean) *</label>
                        <input type="text" id="ko_title" name="ko_title" value="<?php echo htmlspecialchars($post['ko_title'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                        <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <p class="text-xs text-gray-500 mt-1">Use lowercase letters, numbers, and hyphens only.</p>
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select id="category" name="category" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="">Select a category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['slug']; ?>" <?php echo (isset($post['category']) && $post['category'] === $cat['slug']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt (English)</label>
                        <textarea id="excerpt" name="excerpt" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label for="ko_excerpt" class="block text-sm font-medium text-gray-700 mb-1">Excerpt (Korean)</label>
                        <textarea id="ko_excerpt" name="ko_excerpt" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($post['ko_excerpt'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="cover_image" class="block text-sm font-medium text-gray-700 mb-1">Cover Image URL</label>
                    <input type="text" id="cover_image" name="cover_image" value="<?php echo htmlspecialchars($post['cover_image'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-xs text-gray-500 mt-1">Enter a full URL to an image. Leave empty for no cover image.</p>
                </div>
                
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English) *</label>
                    <div id="content-editor" style="height: 250px;" class="border border-gray-300 rounded-md"></div>
                    <input type="hidden" id="content" name="content" value="<?php echo htmlspecialchars($post['content'] ?? ''); ?>">
                </div>
                
                <div class="mb-6">
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean) *</label>
                    <div id="ko-content-editor" style="height: 250px;" class="border border-gray-300 rounded-md"></div>
                    <input type="hidden" id="ko_content" name="ko_content" value="<?php echo htmlspecialchars($post['ko_content'] ?? ''); ?>">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label for="author" class="block text-sm font-medium text-gray-700 mb-1">Author</label>
                        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($post['author'] ?? 'KOSMO Foundation'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="publish_date" class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                        <input type="datetime-local" id="publish_date" name="publish_date" value="<?php echo isset($post['publish_date']) ? date('Y-m-d\TH:i', strtotime($post['publish_date'])) : date('Y-m-d\TH:i'); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="published" name="published" <?php echo (isset($post['published']) && $post['published']) || !isset($post['published']) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="published" class="ml-2 block text-sm text-gray-700">Published</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="featured" name="featured" <?php echo (isset($post['featured']) && $post['featured']) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            <label for="featured" class="ml-2 block text-sm text-gray-700">Featured</label>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="admin-news.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save Post</button>
                </div>
            </form>
        <?php endif; ?>
        
        <?php if ($action === 'categories'): ?>
            <!-- Categories List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posts</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($allCategories) > 0): ?>
                            <?php foreach ($allCategories as $cat): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($cat['name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($cat['ko_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($cat['slug']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo htmlspecialchars(truncate($cat['description'], 50)); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $cat['post_count']; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="admin-news.php?action=edit_category&id=<?php echo $cat['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <?php if ($cat['post_count'] == 0): ?>
                                            <a href="#" onclick="confirmDeleteCategory(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>')" class="text-red-600 hover:text-red-900">Delete</a>
                                        <?php else: ?>
                                            <span class="text-gray-400" title="Cannot delete categories with posts">Delete</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Delete Category Confirmation Modal -->
            <div id="deleteCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
                    <p id="deleteCategoryMessage" class="mb-6">Are you sure you want to delete this category?</p>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeDeleteCategoryModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                        <form id="deleteCategoryForm" method="POST">
                            <input type="hidden" name="action" value="delete_category">
                            <input type="hidden" id="deleteCategoryId" name="id" value="">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add_category' || $action === 'edit_category'): ?>
            <!-- Category Form -->
            <form method="POST" class="bg-white rounded-lg shadow-md overflow-hidden p-6">
                <input type="hidden" name="action" value="save_category">
                <?php if (isset($category)): ?>
                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name (English) *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="ko_name" class="block text-sm font-medium text-gray-700 mb-1">Name (Korean) *</label>
                        <input type="text" id="ko_name" name="ko_name" value="<?php echo htmlspecialchars($category['ko_name'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($category['slug'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-xs text-gray-500 mt-1">Use lowercase letters, numbers, and hyphens only.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                        <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label for="ko_description" class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                        <textarea id="ko_description" name="ko_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($category['ko_description'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="admin-news.php?action=categories" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save Category</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        // Set up Quill editors for post content
        <?php if ($action === 'add' || $action === 'edit'): ?>
            var contentEditor = new Quill('#content-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });
            
            var koContentEditor = new Quill('#ko-content-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });
            
            // Set initial content if editing
            <?php if ($post): ?>
                contentEditor.root.innerHTML = <?php echo json_encode($post['content']); ?>;
                koContentEditor.root.innerHTML = <?php echo json_encode($post['ko_content']); ?>;
            <?php endif; ?>
            
            // Update hidden fields before form submission
            document.querySelector('form').onsubmit = function() {
                document.getElementById('content').value = contentEditor.root.innerHTML;
                document.getElementById('ko_content').value = koContentEditor.root.innerHTML;
                return true;
            };
            
            // Auto-generate slug from title
            document.getElementById('title').addEventListener('blur', function() {
                const titleField = document.getElementById('title');
                const slugField = document.getElementById('slug');
                
                // Only generate slug if it's empty
                if (slugField.value === '' && titleField.value !== '') {
                    // Convert to lowercase, replace non-alphanumeric with hyphens, remove consecutive hyphens
                    const slug = titleField.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');
                    
                    slugField.value = slug;
                }
            });
        <?php endif; ?>
        
        // Delete confirmation
        function confirmDelete(id, title) {
            document.getElementById('deleteMessage').textContent = `Are you sure you want to delete the post "${title}"?`;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        // Delete category confirmation
        function confirmDeleteCategory(id, name) {
            document.getElementById('deleteCategoryMessage').textContent = `Are you sure you want to delete the category "${name}"?`;
            document.getElementById('deleteCategoryId').value = id;
            document.getElementById('deleteCategoryModal').classList.remove('hidden');
        }
        
        function closeDeleteCategoryModal() {
            document.getElementById('deleteCategoryModal').classList.add('hidden');
        }
        
        // Auto-generate slug for category
        <?php if ($action === 'add_category' || $action === 'edit_category'): ?>
            document.getElementById('name').addEventListener('blur', function() {
                const nameField = document.getElementById('name');
                const slugField = document.getElementById('slug');
                
                // Only generate slug if it's empty
                if (slugField.value === '' && nameField.value !== '') {
                    // Convert to lowercase, replace non-alphanumeric with hyphens, remove consecutive hyphens
                    const slug = nameField.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '');
                    
                    slugField.value = slug;
                }
            });
        <?php endif; ?>
    </script>
</body>
</html>