<?php
// Update admin.php to add donation settings management
$adminFilePath = 'admin.php';
$adminContent = file_get_contents($adminFilePath);

// 1. Add donation_settings to the main menu
$navPattern = '<li><a href="admin.php?view=profile" class="inline-block py-4 ';
$navReplacement = '<li><a href="admin.php?view=donations" class="inline-block py-4 <?php echo $view == \'donations\' ? \'text-primary border-b-2 border-primary font-medium\' : \'text-gray-500 hover:text-primary\'; ?>">Donations</a></li>
        <li><a href="admin.php?view=profile" class="inline-block py-4 ';
$adminContent = str_replace($navPattern, $navReplacement, $adminContent);

// 2. Add donations to allowed views
$viewsPattern = 'if (isset($_GET[\'view\'])) {
        $allowed_views = [\'dashboard\', \'programs\', \'program_edit\', \'program_view\', \'gallery\', \'gallery_upload\', \'profile\'];';
$viewsReplacement = 'if (isset($_GET[\'view\'])) {
        $allowed_views = [\'dashboard\', \'programs\', \'program_edit\', \'program_view\', \'gallery\', \'gallery_upload\', \'profile\', \'donations\'];';
$adminContent = str_replace($viewsPattern, $viewsReplacement, $adminContent);

// 3. Add donation settings section before the profile section
$profilePattern = '<?php elseif ($view == \'profile\'): ?>';
$donationsCode = '<?php elseif ($view == \'donations\'): ?>
        <!-- Donation Settings -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Donation Settings</h2>
            <a href="admin.php?view=dashboard" class="text-primary hover:underline">← Back to Dashboard</a>
        </div>
        
        <?php
        // Fetch donation settings
        $donationSettings = null;
        $pdo = connect_db();
        if ($pdo) {
            $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
            $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Process donation settings update
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[\'save_donation_settings\'])) {
            $bankName = $_POST[\'bank_name\'] ?? \'\';
            $accountNumber = $_POST[\'account_number\'] ?? \'\';
            $accountHolder = $_POST[\'account_holder\'] ?? \'\';
            $businessNumber = $_POST[\'business_number\'] ?? \'\';
            $kakaopayEnabled = isset($_POST[\'kakaopay_enabled\']) ? 1 : 0;
            $bankTransferEnabled = isset($_POST[\'bank_transfer_enabled\']) ? 1 : 0;
            $minDonationAmount = $_POST[\'min_donation_amount\'] ?? 1000;
            $defaultAmount = $_POST[\'default_amount\'] ?? 50000;
            
            if (empty($bankName) || empty($accountNumber) || empty($accountHolder) || empty($businessNumber)) {
                $error = "All bank details are required.";
            } else {
                $stmt = $pdo->prepare("UPDATE donation_settings SET 
                    bank_name = ?, 
                    account_number = ?, 
                    account_holder = ?, 
                    business_number = ?, 
                    kakaopay_enabled = ?, 
                    bank_transfer_enabled = ?, 
                    min_donation_amount = ?, 
                    default_amount = ?");
                
                $result = $stmt->execute([
                    $bankName,
                    $accountNumber,
                    $accountHolder,
                    $businessNumber,
                    $kakaopayEnabled,
                    $bankTransferEnabled,
                    $minDonationAmount,
                    $defaultAmount
                ]);
                
                if ($result) {
                    $message = "Donation settings updated successfully.";
                    // Refresh donation settings
                    $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
                    $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $error = "Failed to update donation settings.";
                }
            }
        }
        ?>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <form method="POST" class="space-y-6">
                <!-- Bank Information Section -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Bank Transfer Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label for="bank_name" class="block text-gray-700 font-medium mb-2">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($donationSettings[\'bank_name\'] ?? \'\'); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="account_number" class="block text-gray-700 font-medium mb-2">Account Number</label>
                            <input type="text" id="account_number" name="account_number" value="<?php echo htmlspecialchars($donationSettings[\'account_number\'] ?? \'\'); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="account_holder" class="block text-gray-700 font-medium mb-2">Account Holder</label>
                            <input type="text" id="account_holder" name="account_holder" value="<?php echo htmlspecialchars($donationSettings[\'account_holder\'] ?? \'\'); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                        
                        <div>
                            <label for="business_number" class="block text-gray-700 font-medium mb-2">Business Registration Number</label>
                            <input type="text" id="business_number" name="business_number" value="<?php echo htmlspecialchars($donationSettings[\'business_number\'] ?? \'\'); ?>" class="w-full px-4 py-2 border rounded-md" required>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Methods Section -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Payment Methods</h3>
                    
                    <div class="flex flex-col space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="kakaopay_enabled" <?php echo ($donationSettings[\'kakaopay_enabled\'] ?? 1) ? \'checked\' : \'\'; ?> class="form-checkbox h-5 w-5 text-primary">
                            <span class="ml-2 text-gray-700">Enable KakaoPay</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="bank_transfer_enabled" <?php echo ($donationSettings[\'bank_transfer_enabled\'] ?? 1) ? \'checked\' : \'\'; ?> class="form-checkbox h-5 w-5 text-primary">
                            <span class="ml-2 text-gray-700">Enable Bank Transfer</span>
                        </label>
                    </div>
                </div>
                
                <!-- Donation Amount Settings -->
                <div>
                    <h3 class="text-xl font-semibold mb-4">Donation Amount Settings</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="min_donation_amount" class="block text-gray-700 font-medium mb-2">Minimum Donation Amount (₩)</label>
                            <input type="number" id="min_donation_amount" name="min_donation_amount" value="<?php echo htmlspecialchars($donationSettings[\'min_donation_amount\'] ?? 1000); ?>" min="0" class="w-full px-4 py-2 border rounded-md">
                        </div>
                        
                        <div>
                            <label for="default_amount" class="block text-gray-700 font-medium mb-2">Default Donation Amount (₩)</label>
                            <input type="number" id="default_amount" name="default_amount" value="<?php echo htmlspecialchars($donationSettings[\'default_amount\'] ?? 50000); ?>" min="0" class="w-full px-4 py-2 border rounded-md">
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end">
                    <button type="submit" name="save_donation_settings" class="bg-primary hover:bg-primary/90 text-white px-5 py-2 rounded-md">Save Settings</button>
                </div>
            </form>
        </div>
        
        <?php elseif ($view == \'profile\'): ?>';

$adminContent = str_replace($profilePattern, $donationsCode, $adminContent);

// 4. Replace TinyMCE with Quill
$tinyMCEPattern = '<!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: \'#content, #ko_content\',
        height: 500,
        menubar: false,
        plugins: [
          \'advlist\', \'autolink\', \'lists\', \'link\', \'image\', \'charmap\', \'preview\', \'anchor\',
          \'searchreplace\', \'visualblocks\', \'code\', \'fullscreen\',
          \'insertdatetime\', \'media\', \'table\'
        ],
        toolbar: \'undo redo | blocks | \' +
          \'bold italic | alignleft aligncenter \' +
          \'alignright alignjustify | bullist numlist outdent indent | \' +
          \'removeformat | table | link image\',
        content_style: \'body { font-family:Open Sans,Noto Sans KR,sans-serif; font-size:16px }\'
      });
    </script>';

$quillReplacement = '<!-- Quill Rich Text Editor -->
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
    </script>';

$adminContent = str_replace($tinyMCEPattern, $quillReplacement, $adminContent);

// 5. Update the content textareas for Quill editor
$contentTextareaPattern = '<div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English)</label>
                    <textarea id="content" name="content"><?php echo isset($program) ? htmlspecialchars($program[\'content\']) : \'\'; ?></textarea>
                </div>
                
                <div>
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean)</label>
                    <textarea id="ko_content" name="ko_content"><?php echo isset($program) ? htmlspecialchars($program[\'ko_content\']) : \'\'; ?></textarea>
                </div>';

$contentDivReplacement = '<div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Content (English)</label>
                    <div id="content-container" class="border rounded-md"></div>
                    <input type="hidden" id="content" name="content" value="<?php echo isset($program) ? htmlspecialchars($program[\'content\']) : \'\'; ?>">
                </div>
                
                <div>
                    <label for="ko_content" class="block text-sm font-medium text-gray-700 mb-1">Content (Korean)</label>
                    <div id="ko-content-container" class="border rounded-md"></div>
                    <input type="hidden" id="ko_content" name="ko_content" value="<?php echo isset($program) ? htmlspecialchars($program[\'ko_content\']) : \'\'; ?>">
                </div>';

$adminContent = str_replace($contentTextareaPattern, $contentDivReplacement, $adminContent);

// Save the updated admin.php file
if (file_put_contents($adminFilePath, $adminContent)) {
    echo "Admin panel successfully updated!";
} else {
    echo "Error: Could not update admin.php file.";
}
?>