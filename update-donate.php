<?php
require_once __DIR__ . '/config.php';
// Update donate.php to use donation settings from database
$donateFilePath = 'donate.php';
$donateContent = file_get_contents($donateFilePath);

// Add the database code to the beginning of the file
$phpStartPattern = '<?php
// Donation form handling';
$phpStartReplacement = '<?php
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

// Get donation settings from database
$donationSettings = [];
$pdo = connect_db();
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM donation_settings LIMIT 1");
        $donationSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching donation settings: " . $e->getMessage());
    }
}

// If no settings found, use defaults
if (empty($donationSettings)) {
    $donationSettings = [
        \'bank_name\' => \'Shinhan Bank\',
        \'account_number\' => \'140-013-927125\',
        \'account_holder\' => \'한국스포츠의료지원재단\',
        \'business_number\' => \'322-82-00643\',
        \'kakaopay_enabled\' => 1,
        \'bank_transfer_enabled\' => 1,
        \'min_donation_amount\' => 1000,
        \'default_amount\' => 50000
    ];
}

// Donation form handling';
$donateContent = str_replace($phpStartPattern, $phpStartReplacement, $donateContent);

// Replace the hardcoded bank info with the database values
$bankInfoPattern = '<!-- Bank Transfer Information -->
                        <div id="bank-info" class="mt-6 bg-gray-100 p-6 rounded-lg <?php echo $paymentMethod === \'bank\' ? \'block\' : \'hidden\'; ?>">
                            <h4 class="text-lg font-semibold mb-3"><?php echo $t[\'bank_info_title\']; ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'bank_name\']; ?></p>
                                    <p class="font-medium">Shinhan Bank</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'account_number\']; ?></p>
                                    <p class="font-medium">140-013-927125</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'account_holder\']; ?></p>
                                    <p class="font-medium">한국스포츠의료지원재단</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'business_number\']; ?></p>
                                    <p class="font-medium">322-82-00643</p>
                                </div>
                            </div>';
                            
$bankInfoReplacement = '<!-- Bank Transfer Information -->
                        <div id="bank-info" class="mt-6 bg-gray-100 p-6 rounded-lg <?php echo $paymentMethod === \'bank\' ? \'block\' : \'hidden\'; ?>">
                            <h4 class="text-lg font-semibold mb-3"><?php echo $t[\'bank_info_title\']; ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'bank_name\']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings[\'bank_name\']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'account_number\']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings[\'account_number\']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'account_holder\']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings[\'account_holder\']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t[\'business_number\']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings[\'business_number\']); ?></p>
                                </div>
                            </div>';
                            
$donateContent = str_replace($bankInfoPattern, $bankInfoReplacement, $donateContent);

// Update the amount variable to use the default from settings
$amountPattern = '$amount = $_GET[\'amount\'] ?? \'50000\';';
$amountReplacement = '$amount = $_GET[\'amount\'] ?? $donationSettings[\'default_amount\'] ?? \'50000\';';
$donateContent = str_replace($amountPattern, $amountReplacement, $donateContent);

// Update the minimum amount validation
$minAmountPattern = 'elseif (!is_numeric($amount) || $amount < 1000) {
        $formError = \'Please enter a valid donation amount (minimum ₩1,000).\';';
$minAmountReplacement = 'elseif (!is_numeric($amount) || $amount < $donationSettings[\'min_donation_amount\']) {
        $formError = \'Please enter a valid donation amount (minimum ₩\' . number_format($donationSettings[\'min_donation_amount\']) . \').\';';
$donateContent = str_replace($minAmountPattern, $minAmountReplacement, $donateContent);

// Save the updated donate.php file
if (file_put_contents($donateFilePath, $donateContent)) {
    echo "Donate page successfully updated to use database settings!";
} else {
    echo "Error: Could not update donate.php file.";
}
?>