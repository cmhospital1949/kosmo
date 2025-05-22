<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

require_once 'db_connection.php';
$pdo = get_db_connection();

$program = [
    'id' => '',
    'slug' => '',
    'title' => '',
    'ko_title' => '',
    'description' => '',
    'ko_description' => '',
    'image' => '',
    'content' => '',
    'ko_content' => ''
];

$isEdit = false;
$success = false;
$error = '';

// If editing existing program
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $isEdit = true;
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();
            
            if ($result) {
                $program = $result;
            } else {
                $error = "Program not found.";
            }
        } catch (PDOException $e) {
            $error = "Error fetching program: " . $e->getMessage();
        }
    }
}

// Form submission processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $program['slug'] = $_POST['slug'] ?? '';
    $program['title'] = $_POST['title'] ?? '';
    $program['ko_title'] = $_POST['ko_title'] ?? '';
    $program['description'] = $_POST['description'] ?? '';
    $program['ko_description'] = $_POST['ko_description'] ?? '';
    $program['image'] = $_POST['image'] ?? '';
    $program['content'] = $_POST['content'] ?? '';
    $program['ko_content'] = $_POST['ko_content'] ?? '';
    
    // Validate form data
    if (empty($program['slug']) || empty($program['title']) || empty($program['ko_title'])) {
        $error = "Please fill in all required fields.";
    } else {
        if ($pdo) {
            try {
                if ($isEdit) {
                    // Update existing program
                    $stmt = $pdo->prepare("
                        UPDATE programs 
                        SET slug = ?, title = ?, ko_title = ?, description = ?, ko_description = ?, 
                            image = ?, content = ?, ko_content = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $program['slug'],
                        $program['title'],
                        $program['ko_title'],
                        $program['description'],
                        $program['ko_description'],
                        $program['image'],
                        $program['content'],
                        $program['ko_content'],
                        $program['id']
                    ]);
                } else {
                    // Insert new program
                    $stmt = $pdo->prepare("
                        INSERT INTO programs (slug, title, ko_title, description, ko_description, image, content, ko_content)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $program['slug'],
                        $program['title'],
                        $program['ko_title'],
                        $program['description'],
                        $program['ko_description'],
                        $program['image'],
                        $program['content'],
                        $program['ko_content']
                    ]);
                    
                    // Get the ID of the new program
                    $program['id'] = $pdo->lastInsertId();
                }
                
                $success = true;
                $isEdit = true; // Now we're editing this program
            } catch (PDOException $e) {
                $error = "Error saving program: " . $e->getMessage();
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
    <title><?php echo $isEdit ? 'Edit' : 'Add'; ?> Program - KOSMO Foundation Admin</title>
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
            <h2 class="text-2xl font-bold"><?php echo $isEdit ? 'Edit' : 'Add New'; ?> Program</h2>
            <a href="programs.php" class="text-primary hover:underline inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                Back to Programs
            </a>
        </div>
        
        <?php if ($success): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>Program was successfully saved.</p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white rounded-lg shadow-md p-6">
            <input type="hidden" name="id" value="<?php echo $program['id']; ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="slug" class="block text-gray-700 font-medium mb-2">Slug <span class="text-red-500">*</span></label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($program['slug']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                    <p class="text-sm text-gray-500 mt-1">Used in URLs. Use lowercase letters, numbers, and hyphens.</p>
                </div>
                
                <div>
                    <label for="image" class="block text-gray-700 font-medium mb-2">Image URL</label>
                    <input type="text" id="image" name="image" value="<?php echo htmlspecialchars($program['image']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                    <p class="text-sm text-gray-500 mt-1">Enter a URL for the program image.</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="title" class="block text-gray-700 font-medium mb-2">Title (English) <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($program['title']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
                
                <div>
                    <label for="ko_title" class="block text-gray-700 font-medium mb-2">Title (Korean) <span class="text-red-500">*</span></label>
                    <input type="text" id="ko_title" name="ko_title" value="<?php echo htmlspecialchars($program['ko_title']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" required>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="description" class="block text-gray-700 font-medium mb-2">Short Description (English)</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($program['description']); ?></textarea>
                </div>
                
                <div>
                    <label for="ko_description" class="block text-gray-700 font-medium mb-2">Short Description (Korean)</label>
                    <textarea id="ko_description" name="ko_description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($program['ko_description']); ?></textarea>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="content" class="block text-gray-700 font-medium mb-2">Content (English)</label>
                <textarea id="content" name="content" rows="10" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($program['content']); ?></textarea>
                <p class="text-sm text-gray-500 mt-1">You can use HTML for formatting.</p>
            </div>
            
            <div class="mb-6">
                <label for="ko_content" class="block text-gray-700 font-medium mb-2">Content (Korean)</label>
                <textarea id="ko_content" name="ko_content" rows="10" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary"><?php echo htmlspecialchars($program['ko_content']); ?></textarea>
                <p class="text-sm text-gray-500 mt-1">You can use HTML for formatting.</p>
            </div>
            
            <div class="flex justify-between items-center">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-md font-medium">Save Program</button>
                <?php if ($isEdit): ?>
                <a href="program_view.php?id=<?php echo $program['id']; ?>" class="text-primary hover:underline">View Program</a>
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
    
    <script>
        // Generate slug from title
        document.getElementById('title').addEventListener('blur', function() {
            const slugField = document.getElementById('slug');
            if (slugField.value === '') {
                const title = this.value.trim();
                const slug = title.toLowerCase()
                    .replace(/[^\w\s-]/g, '') // Remove special characters
                    .replace(/\s+/g, '-')     // Replace spaces with hyphens
                    .replace(/-+/g, '-');     // Replace multiple hyphens with single hyphen
                slugField.value = slug;
            }
        });
    </script>
</body>
</html>