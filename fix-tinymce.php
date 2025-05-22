<?php
// Fix TinyMCE in admin.php
// Replace Quill with TinyMCE

// Database connection
function connect_db() {
    $host = 'localhost';
    $dbname = 'bestluck';
    $username = 'bestluck';
    $password = 'Nocpriss12!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Get the admin.php file content
$adminFilePath = 'admin.php';
$adminContent = file_get_contents($adminFilePath);

// Replace Quill-specific code with TinyMCE
$quillJsPattern = '<?php if ($view == \'program_edit\'): ?>
    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <style>
      .ql-editor {
        min-height: 300px;
        font-family: \'Open Sans\', \'Noto Sans KR\', sans-serif;
      }
    </style>
    <script>
      document.addEventListener(\'DOMContentLoaded\', function() {
        // Initialize Quill editors
        var contentOptions = {
          modules: {
            toolbar: [
              [{ \'header\': [1, 2, 3, 4, 5, 6, false] }],
              [\'bold\', \'italic\', \'underline\', \'strike\'],
              [{ \'color\': [] }, { \'background\': [] }],
              [{ \'list\': \'ordered\'}, { \'list\': \'bullet\' }],
              [{ \'align\': [] }],
              [\'link\', \'image\'],
              [\'clean\']
            ]
          },
          placeholder: \'Content goes here...\',
          theme: \'snow\'
        };
        
        // Create hidden input fields to store Quill content
        if (document.getElementById(\'content-container\')) {
          var contentQuill = new Quill(\'#content-container\', contentOptions);
          var contentInput = document.getElementById(\'content\');
          
          // Set initial content
          if (contentInput.value) {
            contentQuill.root.innerHTML = contentInput.value;
          }
          
          // Update hidden input on form submit
          var form = document.querySelector(\'form\');
          form.addEventListener(\'submit\', function() {
            contentInput.value = contentQuill.root.innerHTML;
          });
        }
        
        if (document.getElementById(\'ko-content-container\')) {
          var koContentQuill = new Quill(\'#ko-content-container\', contentOptions);
          var koContentInput = document.getElementById(\'ko_content\');
          
          // Set initial content
          if (koContentInput.value) {
            koContentQuill.root.innerHTML = koContentInput.value;
          }
          
          // Update hidden input on form submit
          var form = document.querySelector(\'form\');
          form.addEventListener(\'submit\', function() {
            koContentInput.value = koContentQuill.root.innerHTML;
          });
        }
      });
    </script>
    <?php endif; ?>';
    
$tinyMceJs = '<?php if ($view == \'program_edit\'): ?>
    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      document.addEventListener(\'DOMContentLoaded\', function() {
        // Initialize TinyMCE
        var initTinyMCE = function(selector, hiddenInput) {
          tinymce.init({
            selector: selector,
            plugins: \'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount\',
            toolbar: \'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat\',
            setup: function(editor) {
              editor.on(\'change\', function() {
                document.getElementById(hiddenInput).value = editor.getContent();
              });
            },
            height: 400,
            menu: {
              file: { title: \'File\', items: \'newdocument restoredraft | preview | export print\' },
              edit: { title: \'Edit\', items: \'undo redo | cut copy paste pastetext | selectall | searchreplace\' },
              view: { title: \'View\', items: \'code | visualaid visualchars visualblocks | spellchecker | preview fullscreen\' },
              insert: { title: \'Insert\', items: \'image link media template codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor toc | insertdatetime\' },
              format: { title: \'Format\', items: \'bold italic underline strikethrough superscript subscript codeformat | formats blockformats fontformats fontsizes align | forecolor backcolor | removeformat\' },
              tools: { title: \'Tools\', items: \'spellchecker spellcheckerlanguage | code wordcount\' },
              table: { title: \'Table\', items: \'inserttable | cell row column | tableprops deletetable\' },
              help: { title: \'Help\', items: \'help\' }
            }
          });
        };

        // Initialize TinyMCE for content in English
        if (document.getElementById(\'content-container\')) {
          var contentInput = document.getElementById(\'content\');
          if (contentInput.value) {
            document.getElementById(\'content-container\').innerHTML = contentInput.value;
          }
          initTinyMCE(\'#content-container\', \'content\');
        }

        // Initialize TinyMCE for content in Korean
        if (document.getElementById(\'ko-content-container\')) {
          var koContentInput = document.getElementById(\'ko_content\');
          if (koContentInput.value) {
            document.getElementById(\'ko-content-container\').innerHTML = koContentInput.value;
          }
          initTinyMCE(\'#ko-content-container\', \'ko_content\');
        }
      });
    </script>
    <?php endif; ?>';
    
$adminContent = str_replace($quillJsPattern, $tinyMceJs, $adminContent);

// Replace the content containers too
$contentContainerPattern = '<div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English)</label>
                    <div id="content-container" class="border rounded-md"></div>
                    <input type="hidden" id="content" name="content" value="<?php echo isset($program) ? htmlspecialchars($program[\'content\']) : \'\'; ?>">
                </div>';
$contentContainerReplacement = '<div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English)</label>
                    <textarea id="content-container" class="border rounded-md"></textarea>
                    <input type="hidden" id="content" name="content" value="<?php echo isset($program) ? htmlspecialchars($program[\'content\']) : \'\'; ?>">
                </div>';
$adminContent = str_replace($contentContainerPattern, $contentContainerReplacement, $adminContent);

$koContentContainerPattern = '<div>
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean)</label>
                    <div id="ko-content-container" class="border rounded-md"></div>
                    <input type="hidden" id="ko_content" name="ko_content" value="<?php echo isset($program) ? htmlspecialchars($program[\'ko_content\']) : \'\'; ?>">
                </div>';
$koContentContainerReplacement = '<div>
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean)</label>
                    <textarea id="ko-content-container" class="border rounded-md"></textarea>
                    <input type="hidden" id="ko_content" name="ko_content" value="<?php echo isset($program) ? htmlspecialchars($program[\'ko_content\']) : \'\'; ?>">
                </div>';
$adminContent = str_replace($koContentContainerPattern, $koContentContainerReplacement, $adminContent);

// Save the updated admin.php file
if (file_put_contents($adminFilePath, $adminContent)) {
    echo "TinyMCE successfully integrated into admin.php - You can now use it without an API key!";
} else {
    echo "Error: Could not update admin.php file.";
}
?>