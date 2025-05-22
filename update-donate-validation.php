<?php
// Update the client-side validation in donate.php

$donateFilePath = 'donate.php';
$donateContent = file_get_contents($donateFilePath);

// Update the JavaScript validation to use the PHP variable
$jsPattern = "if (amount < 1000) {
                e.preventDefault();
                alert('Minimum donation amount is ₩1,000.');
                return;
            }";
            
$jsReplacement = "if (amount < <?php echo \$donationSettings['min_donation_amount']; ?>) {
                e.preventDefault();
                alert('Minimum donation amount is ₩<?php echo number_format(\$donationSettings[\"min_donation_amount\"]); ?>.');
                return;
            }";
            
$donateContent = str_replace($jsPattern, $jsReplacement, $donateContent);

// Save the updated donate.php file
if (file_put_contents($donateFilePath, $donateContent)) {
    echo "Donation page updated to use the minimum amount from settings in client-side validation!";
} else {
    echo "Error: Could not update donate.php file.";
}
?>