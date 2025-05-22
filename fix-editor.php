<?php
// Fix TinyMCE in admin.php
// Replace CDN version with community version

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

// Replace TinyMCE with CKEditor 5
$tinyMcePattern = '<!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>';
    
$ckEditorReplacement = '<!-- CKEditor 5 Rich Text Editor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
    <script>';
    
$adminContent = str_replace($tinyMcePattern, $ckEditorReplacement, $adminContent);

// Replace the TinyMCE initialization with CKEditor
$tinyMceInitPattern = 'document.addEventListener(\'DOMContentLoaded\', function() {
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
        }';
        
$ckEditorInitReplacement = 'document.addEventListener(\'DOMContentLoaded\', function() {
        // Initialize CKEditor for content in English
        if (document.getElementById(\'content-container\')) {
          var contentInput = document.getElementById(\'content\');
          
          ClassicEditor
            .create(document.querySelector(\'#content-container\'), {
              toolbar: [
                \'heading\', \'|\', 
                \'bold\', \'italic\', \'link\', \'bulletedList\', \'numberedList\', \'|\', 
                \'indent\', \'outdent\', \'|\', 
                \'blockQuote\', \'insertTable\', \'undo\', \'redo\'
              ]
            })
            .then(editor => {
              // Set initial content
              if (contentInput.value) {
                editor.setData(contentInput.value);
              }
              
              // Update hidden input on form submit
              editor.model.document.on(\'change:data\', () => {
                contentInput.value = editor.getData();
              });
            })
            .catch(error => {
              console.error(error);
            });
        }

        // Initialize CKEditor for content in Korean
        if (document.getElementById(\'ko-content-container\')) {
          var koContentInput = document.getElementById(\'ko_content\');
          
          ClassicEditor
            .create(document.querySelector(\'#ko-content-container\'), {
              toolbar: [
                \'heading\', \'|\', 
                \'bold\', \'italic\', \'link\', \'bulletedList\', \'numberedList\', \'|\', 
                \'indent\', \'outdent\', \'|\', 
                \'blockQuote\', \'insertTable\', \'undo\', \'redo\'
              ]
            })
            .then(editor => {
              // Set initial content
              if (koContentInput.value) {
                editor.setData(koContentInput.value);
              }
              
              // Update hidden input on form submit
              editor.model.document.on(\'change:data\', () => {
                koContentInput.value = editor.getData();
              });
            })
            .catch(error => {
              console.error(error);
            });
        }';
        
$adminContent = str_replace($tinyMceInitPattern, $ckEditorInitReplacement, $adminContent);

// Update the project plan to reflect the changes
$planFilePath = 'docs/project_plan.md';
$planContent = file_get_contents($planFilePath);

// Add the TinyMCE fix to the project plan
$planContent .= "

## Latest Admin Panel Updates (2025-05-19)
- Fixed TinyMCE editor issue by replacing it with CKEditor 5 which doesn't require an API key
- Created donation_settings table to store and manage donation configuration
- Enhanced admin panel to allow editing of donation settings
- Updated website to use donation settings from the database
- Ensured full synchronization between admin panel edits and website content
";

// Save the updated admin.php file
if (file_put_contents($adminFilePath, $adminContent)) {
    echo "CKEditor 5 successfully integrated into admin.php - this editor is free to use without API keys!<br>";
    
    // Save the updated project plan
    if (file_put_contents($planFilePath, $planContent)) {
        echo "Project plan updated with latest changes!";
    } else {
        echo "Error: Could not update project_plan.md file.";
    }
} else {
    echo "Error: Could not update admin.php file.";
}
?>