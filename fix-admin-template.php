<?php
// Direct fix for admin gallery display issue by modifying the template part

echo "<h1>Admin Gallery Template Fix</h1>";

// Get the admin.php content
$content = file_get_contents('admin.php');

if (!$content) {
    echo "<p>Failed to read admin.php file.</p>";
    exit;
}

// Find the gallery section in the HTML template
$galleryHtmlPattern = "/<?php elseif \\(\\\$view == 'gallery'\\): ?>.*?<?php endif; ?>/s";

if (preg_match($galleryHtmlPattern, $content, $matches)) {
    $galleryHtml = $matches[0];
    
    // Create a modified gallery HTML with a unique loop
    $newGalleryHtml = "<?php elseif (\$view == 'gallery'): ?>
        <!-- Gallery Management -->
        <div class=\"flex justify-between items-center mb-6\">
            <h2 class=\"text-2xl font-bold\">Gallery Management</h2>
            <button type=\"button\" onclick=\"document.getElementById('add-category-modal').classList.remove('hidden')\" class=\"bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded\">Add New Category</button>
        </div>
        
        <div class=\"space-y-8\">
            <?php 
            // Get unique category names
            \$uniqueCategories = [];
            \$processedNames = [];
            
            foreach (\$categories as \$cat) {
                if (!in_array(\$cat['name'], \$processedNames)) {
                    \$uniqueCategories[] = \$cat;
                    \$processedNames[] = \$cat['name'];
                }
            }
            
            if (empty(\$uniqueCategories)): 
            ?>
            <div class=\"bg-white rounded-lg shadow-md p-6 text-center\">
                <p class=\"text-gray-500 mb-4\">No gallery categories found.</p>
                <button type=\"button\" onclick=\"document.getElementById('add-category-modal').classList.remove('hidden')\" class=\"bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded\">Create First Category</button>
            </div>
            <?php else: ?>
                <?php foreach (\$uniqueCategories as \$category): ?>
                <div class=\"bg-white rounded-lg shadow-md overflow-hidden\">
                    <div class=\"px-6 py-4 bg-gray-50 flex justify-between items-center\">
                        <div>
                            <h3 class=\"text-lg font-semibold\"><?php echo htmlspecialchars(\$category['name']); ?> (<?php echo htmlspecialchars(\$category['ko_name']); ?>)</h3>
                            <p class=\"text-sm text-gray-600\"><?php echo htmlspecialchars(\$category['description']); ?></p>
                        </div>
                        <div class=\"flex items-center space-x-3\">
                            <button type=\"button\" onclick=\"document.getElementById('upload-modal-<?php echo \$category['id']; ?>').classList.remove('hidden')\" class=\"bg-secondary hover:bg-secondary/90 text-white px-3 py-1 rounded text-sm\">Add Image</button>
                            <a href=\"admin.php?view=gallery&action=delete_category&id=<?php echo \$category['id']; ?>\" class=\"text-red-600 hover:text-red-800 text-sm\" onclick=\"return confirm('Are you sure you want to delete this category and all its images?')\">Delete Category</a>
                        </div>
                    </div>
                    
                    <div class=\"p-6\">
                        <?php if (empty(\$category['images'])): ?>
                        <p class=\"text-gray-500 text-center\">No images in this category.</p>
                        <?php else: ?>
                        <div class=\"grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4\">
                            <?php foreach (\$category['images'] as \$image): ?>
                            <div class=\"border rounded-lg overflow-hidden group\">
                                <div class=\"relative h-48 bg-gray-200\">
                                    <img src=\"<?php echo htmlspecialchars(\$image['filename']); ?>\" alt=\"<?php echo htmlspecialchars(\$image['title']); ?>\" class=\"w-full h-full object-cover\">
                                    <div class=\"absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center\">
                                        <a href=\"admin.php?view=gallery&action=delete_image&id=<?php echo \$image['id']; ?>\" class=\"text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded\" onclick=\"return confirm('Are you sure you want to delete this image?')\">Delete</a>
                                    </div>
                                </div>
                                <div class=\"p-3\">
                                    <h4 class=\"font-medium\"><?php echo htmlspecialchars(\$image['title']); ?></h4>
                                    <p class=\"text-sm text-gray-600\"><?php echo htmlspecialchars(\$image['ko_title']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Upload Image Modal -->
                    <div id=\"upload-modal-<?php echo \$category['id']; ?>\" class=\"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden\">
                        <div class=\"bg-white rounded-lg shadow-lg p-6 max-w-md w-full\">
                            <div class=\"flex justify-between items-center mb-4\">
                                <h3 class=\"text-xl font-bold\">Add Image to <?php echo htmlspecialchars(\$category['name']); ?></h3>
                                <button type=\"button\" onclick=\"document.getElementById('upload-modal-<?php echo \$category['id']; ?>').classList.add('hidden')\" class=\"text-gray-500 hover:text-gray-700\">
                                    <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-6 w-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\">
                                        <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\" />
                                    </svg>
                                </button>
                            </div>
                            
                            <form method=\"POST\" class=\"space-y-4\">
                                <input type=\"hidden\" name=\"category_id\" value=\"<?php echo \$category['id']; ?>\">
                                
                                <div>
                                    <label for=\"image_url\" class=\"block text-sm font-medium text-gray-700 mb-1\">Image URL</label>
                                    <input type=\"url\" id=\"image_url\" name=\"image_url\" class=\"w-full px-4 py-2 border rounded-md\" required>
                                </div>
                                
                                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                                    <div>
                                        <label for=\"title\" class=\"block text-sm font-medium text-gray-700 mb-1\">Title (English)</label>
                                        <input type=\"text\" id=\"title\" name=\"title\" class=\"w-full px-4 py-2 border rounded-md\">
                                    </div>
                                    
                                    <div>
                                        <label for=\"ko_title\" class=\"block text-sm font-medium text-gray-700 mb-1\">Title (Korean)</label>
                                        <input type=\"text\" id=\"ko_title\" name=\"ko_title\" class=\"w-full px-4 py-2 border rounded-md\">
                                    </div>
                                </div>
                                
                                <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                                    <div>
                                        <label for=\"description\" class=\"block text-sm font-medium text-gray-700 mb-1\">Description (English)</label>
                                        <textarea id=\"description\" name=\"description\" rows=\"2\" class=\"w-full px-4 py-2 border rounded-md\"></textarea>
                                    </div>
                                    
                                    <div>
                                        <label for=\"ko_description\" class=\"block text-sm font-medium text-gray-700 mb-1\">Description (Korean)</label>
                                        <textarea id=\"ko_description\" name=\"ko_description\" rows=\"2\" class=\"w-full px-4 py-2 border rounded-md\"></textarea>
                                    </div>
                                </div>
                                
                                <div class=\"flex justify-end space-x-3\">
                                    <button type=\"button\" onclick=\"document.getElementById('upload-modal-<?php echo \$category['id']; ?>').classList.add('hidden')\" class=\"bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded\">Cancel</button>
                                    <button type=\"submit\" name=\"upload_image\" class=\"bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded\">Upload Image</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Add Category Modal -->
        <div id=\"add-category-modal\" class=\"fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden\">
            <div class=\"bg-white rounded-lg shadow-lg p-6 max-w-md w-full\">
                <div class=\"flex justify-between items-center mb-4\">
                    <h3 class=\"text-xl font-bold\">Add New Category</h3>
                    <button type=\"button\" onclick=\"document.getElementById('add-category-modal').classList.add('hidden')\" class=\"text-gray-500 hover:text-gray-700\">
                        <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"h-6 w-6\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M6 18L18 6M6 6l12 12\" />
                        </svg>
                    </button>
                </div>
                
                <form method=\"POST\" class=\"space-y-4\">
                    <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                        <div>
                            <label for=\"name\" class=\"block text-sm font-medium text-gray-700 mb-1\">Name (English)</label>
                            <input type=\"text\" id=\"name\" name=\"name\" class=\"w-full px-4 py-2 border rounded-md\" required>
                        </div>
                        
                        <div>
                            <label for=\"ko_name\" class=\"block text-sm font-medium text-gray-700 mb-1\">Name (Korean)</label>
                            <input type=\"text\" id=\"ko_name\" name=\"ko_name\" class=\"w-full px-4 py-2 border rounded-md\" required>
                        </div>
                    </div>
                    
                    <div class=\"grid grid-cols-1 md:grid-cols-2 gap-4\">
                        <div>
                            <label for=\"description\" class=\"block text-sm font-medium text-gray-700 mb-1\">Description (English)</label>
                            <textarea id=\"description\" name=\"description\" rows=\"2\" class=\"w-full px-4 py-2 border rounded-md\"></textarea>
                        </div>
                        
                        <div>
                            <label for=\"ko_description\" class=\"block text-sm font-medium text-gray-700 mb-1\">Description (Korean)</label>
                            <textarea id=\"ko_description\" name=\"ko_description\" rows=\"2\" class=\"w-full px-4 py-2 border rounded-md\"></textarea>
                        </div>
                    </div>
                    
                    <div class=\"flex justify-end space-x-3\">
                        <button type=\"button\" onclick=\"document.getElementById('add-category-modal').classList.add('hidden')\" class=\"bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded\">Cancel</button>
                        <button type=\"submit\" name=\"add_category\" class=\"bg-primary hover:bg-primary/90 text-white px-4 py-2 rounded\">Add Category</button>
                    </div>
                </form>
            </div>
        </div>";
    
    // Replace the gallery HTML
    $newContent = str_replace($galleryHtml, $newGalleryHtml, $content);
    
    // Write the updated content
    if (file_put_contents('admin.php', $newContent)) {
        echo "<p>Successfully updated the admin.php template to fix duplicate categories.</p>";
    } else {
        echo "<p>Failed to write the updated admin.php file.</p>";
    }
} else {
    echo "<p>Could not find the gallery template section in admin.php.</p>";
}

echo "<p><a href='admin.php?view=gallery'>Check the gallery admin page</a></p>";
?>