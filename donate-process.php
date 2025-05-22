<?php
// Get the requested language
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

// Simple mock/sandbox implementation for payment gateway integration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    $amount = $_POST['amount'] ?? '0';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Store donation info in database (this is a mock, would be implemented with real DB)
    $donation_id = uniqid('DONATION_');
    
    // Mock payment gateway integration
    if ($payment_method === 'kakaopay' || $payment_method === 'naverpay') {
        // In a real implementation, this would use the KakaoPay/NaverPay REST API
        // For demonstration, we'll just show a success page
        $success = true;
        
        if ($success) {
            $status = 'success';
        } else {
            $status = 'failed';
        }
    } else {
        $status = 'bank_transfer';
    }
} else {
    // Handle direct access via URL with parameters
    $status = $_GET['status'] ?? 'pending';
    $amount = $_GET['amount'] ?? '0';
    $payment_method = $_GET['method'] ?? 'bank';
    $donation_id = uniqid('DONATION_');
}

// Simple translations
$translations = [
    'en' => [
        'site_title' => 'KOSMO Foundation',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'contact' => 'Contact',
        'donate' => 'Donate',
        'success_title' => 'Thank You For Your Donation!',
        'success_message' => 'Your donation has been processed successfully. A confirmation email has been sent to your email address.',
        'donation_id' => 'Donation ID',
        'donation_amount' => 'Donation Amount',
        'donation_date' => 'Donation Date',
        'donation_method' => 'Payment Method',
        'donation_status' => 'Status',
        'bank_title' => 'Bank Transfer Information',
        'bank_message' => 'Please complete your donation by transferring the amount to the following account:',
        'bank_account' => 'Account Number: 140-013-927125 (Shinhan Bank)',
        'bank_name' => 'Account Name: 한국스포츠의료지원재단',
        'bank_reference' => 'Please use your donation ID as the reference when making the transfer.',
        'back_to_home' => 'Back to Home',
        'failed_title' => 'Donation Failed',
        'failed_message' => 'We were unable to process your donation. Please try again or use a different payment method.',
        'try_again' => 'Try Again',
        'copyright' => '© 2025 KOSMO Foundation. All rights reserved.',
        'contact_us' => 'Contact Us'
    ],
    'ko' => [
        'site_title' => '코스모 재단',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'contact' => '연락처',
        'donate' => '후원하기',
        'success_title' => '기부해 주셔서 감사합니다!',
        'success_message' => '기부가 성공적으로 처리되었습니다. 확인 이메일이 귀하의 이메일 주소로 발송되었습니다.',
        'donation_id' => '기부 ID',
        'donation_amount' => '기부 금액',
        'donation_date' => '기부 날짜',
        'donation_method' => '결제 방법',
        'donation_status' => '상태',
        'bank_title' => '계좌이체 정보',
        'bank_message' => '다음 계좌로 금액을 송금하여 기부를 완료해 주세요:',
        'bank_account' => '계좌번호: 140-013-927125 (신한은행)',
        'bank_name' => '예금주: 한국스포츠의료지원재단',
        'bank_reference' => '송금 시 기부 ID를 참조로 사용해 주세요.',
        'back_to_home' => '홈으로 돌아가기',
        'failed_title' => '기부 실패',
        'failed_message' => '기부 처리에 실패했습니다. 다시 시도하거나 다른 결제 방법을 사용해 주세요.',
        'try_again' => '다시 시도',
        'copyright' => '© 2025 코스모 재단. 모든 권리 보유.',
        'contact_us' => '연락처'
    ]
];

// Get the current language translations
$t = $translations[$locale];

// Format date and amount
$date = date('Y-m-d H:i:s');
$formatted_amount = number_format($amount) . ' ' . ($isKorean ? '원' : 'KRW');

// Get payment method text
if ($payment_method === 'kakaopay') {
    $payment_method_text = 'KakaoPay';
    $payment_method_color = 'yellow';
} elseif ($payment_method === 'naverpay') {
    $payment_method_text = 'NaverPay';
    $payment_method_color = 'green';
} else {
    $payment_method_text = $isKorean ? '계좌이체' : 'Bank Transfer';
    $payment_method_color = 'blue';
}

// Get status text
if ($status === 'success') {
    $status_text = $isKorean ? '완료' : 'Completed';
} elseif ($status === 'bank_transfer') {
    $status_text = $isKorean ? '대기 중 (계좌이체)' : 'Pending (Bank Transfer)';
} else {
    $status_text = $isKorean ? '실패' : 'Failed';
}

?>
<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['donate']; ?> | <?php echo $t['site_title']; ?></title>
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
                        kakaopay: '#ffeb00',
                        naverpay: '#03c75a',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap');
        
        :root {
            --primary-color: #0066cc;
            --secondary-color: #4d9aff;
            --accent-color: #ff6b00;
            --kakaopay-color: #ffeb00;
            --naverpay-color: #03c75a;
        }
        
        body {
            font-family: <?php echo $isKorean ? "'Noto Sans KR', sans-serif" : "'Open Sans', 'Noto Sans KR', sans-serif"; ?>;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center mb-4 md:mb-0">
                <a href="index.php?lang=<?php echo $locale; ?>" class="text-2xl font-bold text-primary">KOSMO</a>
                <span class="ml-2 text-sm text-gray-600">Foundation</span>
            </div>
            
            <!-- Language Switcher -->
            <div class="mb-4 md:mb-0 md:absolute md:right-8 md:top-4">
                <a href="?status=<?php echo $status; ?>&amount=<?php echo $amount; ?>&method=<?php echo $payment_method; ?>&lang=en" class="<?php echo $locale === 'en' ? 'font-bold text-primary' : 'text-gray-600'; ?> mr-2">EN</a> | 
                <a href="?status=<?php echo $status; ?>&amount=<?php echo $amount; ?>&method=<?php echo $payment_method; ?>&lang=ko" class="<?php echo $locale === 'ko' ? 'font-bold text-primary' : 'text-gray-600'; ?> ml-2">KO</a>
            </div>
            
            <nav class="flex flex-wrap justify-center">
                <ul class="flex flex-wrap space-x-4 md:space-x-6 items-center">
                    <li><a href="index.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['home']; ?></a></li>
                    <li><a href="index.php?lang=<?php echo $locale; ?>#about" class="hover:text-primary px-2 py-1"><?php echo $t['about']; ?></a></li>
                    <li><a href="programs.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['programs']; ?></a></li>
                    <li><a href="index.php?lang=<?php echo $locale; ?>#contact" class="hover:text-primary px-2 py-1"><?php echo $t['contact']; ?></a></li>
                    <li>
                        <a href="donate.php?lang=<?php echo $locale; ?>" class="bg-accent hover:bg-opacity-90 text-white px-4 py-2 rounded-md ml-2">
                            <?php echo $t['donate']; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Donation Result Content -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto">
                <?php if ($status === 'success'): ?>
                <!-- Success Message -->
                <div class="bg-green-100 border-l-4 border-green-500 p-8 rounded-md mb-8">
                    <h2 class="text-2xl font-bold text-green-800 mb-4"><?php echo $t['success_title']; ?></h2>
                    <p class="text-green-700 mb-6"><?php echo $t['success_message']; ?></p>
                </div>
                <?php elseif ($status === 'bank_transfer'): ?>
                <!-- Bank Transfer Information -->
                <div class="bg-blue-100 border-l-4 border-blue-500 p-8 rounded-md mb-8">
                    <h2 class="text-2xl font-bold text-blue-800 mb-4"><?php echo $t['bank_title']; ?></h2>
                    <p class="text-blue-700 mb-6"><?php echo $t['bank_message']; ?></p>
                    <p class="text-blue-700 mb-2"><?php echo $t['bank_account']; ?></p>
                    <p class="text-blue-700 mb-6"><?php echo $t['bank_name']; ?></p>
                    <p class="text-blue-700 font-semibold"><?php echo $t['bank_reference']; ?></p>
                </div>
                <?php else: ?>
                <!-- Failed Message -->
                <div class="bg-red-100 border-l-4 border-red-500 p-8 rounded-md mb-8">
                    <h2 class="text-2xl font-bold text-red-800 mb-4"><?php echo $t['failed_title']; ?></h2>
                    <p class="text-red-700 mb-6"><?php echo $t['failed_message']; ?></p>
                    <a href="donate.php?lang=<?php echo $locale; ?>" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md font-semibold inline-block"><?php echo $t['try_again']; ?></a>
                </div>
                <?php endif; ?>
                
                <!-- Donation Details -->
                <div class="bg-white shadow-md rounded-lg p-8 mb-8">
                    <h3 class="text-xl font-semibold mb-6"><?php echo $isKorean ? '기부 세부정보' : 'Donation Details'; ?></h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                            <span class="text-gray-700"><?php echo $t['donation_id']; ?></span>
                            <span class="font-semibold"><?php echo $donation_id; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                            <span class="text-gray-700"><?php echo $t['donation_amount']; ?></span>
                            <span class="font-semibold"><?php echo $formatted_amount; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                            <span class="text-gray-700"><?php echo $t['donation_date']; ?></span>
                            <span class="font-semibold"><?php echo $date; ?></span>
                        </div>
                        
                        <div class="flex justify-between items-center pb-2 border-b border-gray-200">
                            <span class="text-gray-700"><?php echo $t['donation_method']; ?></span>
                            <span class="flex items-center font-semibold">
                                <?php if ($payment_method === 'kakaopay'): ?>
                                <div class="w-5 h-5 rounded-full bg-[#ffeb00] flex items-center justify-center mr-2">
                                    <span class="text-black font-bold text-xs">K</span>
                                </div>
                                <?php elseif ($payment_method === 'naverpay'): ?>
                                <div class="w-5 h-5 rounded-full bg-[#03c75a] flex items-center justify-center mr-2">
                                    <span class="text-white font-bold text-xs">N</span>
                                </div>
                                <?php endif; ?>
                                <?php echo $payment_method_text; ?>
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-700"><?php echo $t['donation_status']; ?></span>
                            <span class="font-semibold <?php echo $status === 'success' ? 'text-green-600' : ($status === 'bank_transfer' ? 'text-blue-600' : 'text-red-600'); ?>"><?php echo $status_text; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="index.php?lang=<?php echo $locale; ?>" class="inline-flex items-center text-primary hover:underline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        <?php echo $t['back_to_home']; ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 py-12 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4"><?php echo $t['site_title']; ?></h3>
                    <p class="text-gray-400"><?php echo $isKorean ? '학생과 선수들의 삶을 개선하는 데 전념합니다.' : 'Dedicated to improving the lives of students and athletes.'; ?></p>
                    <p class="text-gray-400 mt-4">서울 영등포구 문래로 187, 5층</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo $isKorean ? '빠른 링크' : 'Quick Links'; ?></h3>
                    <ul class="space-y-2">
                        <li><a href="index.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['home']; ?></a></li>
                        <li><a href="index.php?lang=<?php echo $locale; ?>#about" class="text-gray-400 hover:text-white"><?php echo $t['about']; ?></a></li>
                        <li><a href="programs.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['programs']; ?></a></li>
                        <li><a href="index.php?lang=<?php echo $locale; ?>#contact" class="text-gray-400 hover:text-white"><?php echo $t['contact']; ?></a></li>
                        <li><a href="donate.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['donate']; ?></a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo $t['contact_us']; ?></h3>
                    <p class="text-gray-400">Email: goodwill@kosmo.or.kr</p>
                    <div class="mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="mt-12 pt-8 border-t border-gray-700 text-center text-gray-400">
                <p><?php echo $t['copyright']; ?></p>
            </div>
        </div>
    </footer>
</body>
</html>