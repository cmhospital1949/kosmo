<?php
// Database connection
require_once __DIR__ . '/lib/Database.php';

function connect_db() {
    try {
        return Database::getConnection();
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
        'bank_name' => 'Shinhan Bank',
        'account_number' => '140-013-927125',
        'account_holder' => '한국스포츠의료지원재단',
        'business_number' => '322-82-00643',
        'kakaopay_enabled' => 1,
        'naverpay_enabled' => 1, // Added NaverPay enabled setting
        'bank_transfer_enabled' => 1,
        'min_donation_amount' => 1000,
        'default_amount' => 50000
    ];
}

// Donation form handling
$formSubmitted = false;
$formSuccess = false;
$formError = '';
$amount = $_GET['amount'] ?? $donationSettings['default_amount'] ?? '50000';
$paymentMethod = $_GET['method'] ?? 'kakaopay';

// Donation process will be handled by an external service for now
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] === 'donate') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $method = $_POST['payment_method'] ?? '';
    
    // Simple validation
    if (empty($name) || empty($email) || empty($amount)) {
        $formError = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Please enter a valid email address.';
    } elseif (!is_numeric($amount) || $amount < $donationSettings['min_donation_amount']) {
        $formError = 'Please enter a valid donation amount (minimum ₩' . number_format($donationSettings['min_donation_amount']) . ').';
    } else {
        // In a real scenario, we would process the payment here
        // For now, simulate a successful donation
        $formSubmitted = true;
        $formSuccess = true;
        
        // Redirect to success page or handle success state here
        header("Location: donate-process.php?status=success&amount={$amount}&method={$method}&lang=" . ($_GET['lang'] ?? 'en'));
        exit;
    }
}

// Language settings
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

// Simple translations
$translations = [
    'en' => [
        'site_title' => 'Donate to KOSMO Foundation',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'gallery' => 'Gallery',
        'contact' => 'Contact',
        'donate' => 'Donate',
        'donation_title' => 'Support Our Mission',
        'donation_description' => 'Your donation helps us provide vital services to students and athletes in need. Together, we can make a difference.',
        'donation_amount' => 'Donation Amount',
        'custom_amount' => 'Custom Amount',
        'amount_placeholder' => 'Enter amount (₩)',
        'personal_info' => 'Your Information',
        'name' => 'Name',
        'email' => 'Email',
        'phone' => 'Phone (optional)',
        'payment_method' => 'Payment Method',
        'payment_kakaopay' => 'KakaoPay',
        'payment_naverpay' => 'NaverPay', // Added NaverPay translation
        'payment_bank' => 'Bank Transfer',
        'donate_button' => 'Donate Now',
        'tax_info' => 'Donations to KOSMO Foundation are tax-deductible in South Korea.',
        'bank_info_title' => 'Bank Transfer Information',
        'bank_name' => 'Bank Name',
        'account_number' => 'Account Number',
        'account_holder' => 'Account Holder',
        'business_number' => 'Business Registration Number',
        'bank_transfer_note' => 'When making a bank transfer, please include your name in the reference field so we can properly acknowledge your donation.',
        'thank_you_message' => 'Thank you for your generous support!',
        'footer_description' => 'Dedicated to improving the lives of students and athletes.',
        'quick_links' => 'Quick Links',
        'contact_us' => 'Contact Us',
        'copyright' => '© 2025 KOSMO Foundation. All rights reserved.'
    ],
    'ko' => [
        'site_title' => '코스모 재단 후원하기',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'gallery' => '갤러리',
        'contact' => '연락처',
        'donate' => '후원하기',
        'donation_title' => '우리의 미션을 지원해주세요',
        'donation_description' => '여러분의 기부는 도움이 필요한 학생과 선수들에게 중요한 서비스를 제공하는 데 도움이 됩니다. 함께라면 변화를 만들 수 있습니다.',
        'donation_amount' => '후원 금액',
        'custom_amount' => '직접 입력',
        'amount_placeholder' => '금액 입력 (₩)',
        'personal_info' => '후원자 정보',
        'name' => '이름',
        'email' => '이메일',
        'phone' => '전화번호 (선택사항)',
        'payment_method' => '결제 방법',
        'payment_kakaopay' => '카카오페이',
        'payment_naverpay' => '네이버페이', // Added NaverPay translation
        'payment_bank' => '계좌이체',
        'donate_button' => '후원하기',
        'tax_info' => '코스모 재단에 대한 기부금은 한국에서 세금 공제 대상입니다.',
        'bank_info_title' => '계좌이체 정보',
        'bank_name' => '은행명',
        'account_number' => '계좌번호',
        'account_holder' => '예금주',
        'business_number' => '사업자등록번호',
        'bank_transfer_note' => '계좌이체 시 보내시는 분의 성함을 기재해 주시면 후원 확인이 가능합니다.',
        'thank_you_message' => '후원에 감사드립니다!',
        'footer_description' => '학생과 선수들의 삶을 개선하는 데 전념합니다.',
        'quick_links' => '빠른 링크',
        'contact_us' => '연락처',
        'copyright' => '© 2025 코스모 재단. 모든 권리 보유.'
    ]
];

// Get the current language translations
$t = $translations[$locale];

?>
<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['site_title']; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="assets/images/favicon.svg">
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="Donate to KOSMO Foundation to support health and education programs for students and athletes.">
    <meta name="keywords" content="donate, foundation, non-profit, support, charity, students, athletes">
    
    <!-- Open Graph meta tags for social sharing -->
    <meta property="og:title" content="<?php echo $t['site_title']; ?>">
    <meta property="og:description" content="<?php echo $t['donation_description']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://www.kosmo.or.kr/donate.php">
    <meta property="og:image" content="https://images.unsplash.com/photo-1593113616828-6f22bca04804?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1170&q=80">
    
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
                        naverpay: '#03c75a', // Added NaverPay brand color
                        kakaopay: '#ffeb00'  // Added KakaoPay brand color
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
            --naverpay-color: #03c75a; /* Added NaverPay brand color */
            --kakaopay-color: #ffeb00; /* Added KakaoPay brand color */
        }
        
        body {
            font-family: <?php echo $isKorean ? "'Noto Sans KR', sans-serif" : "'Open Sans', 'Noto Sans KR', sans-serif"; ?>;
        }
        
        .amount-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        /* Payment method styling */
        .payment-option {
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .payment-option:hover {
            border-color: var(--primary-color);
        }
        
        .payment-option.selected {
            border-color: var(--primary-color);
            background-color: rgba(0, 102, 204, 0.05);
        }
        
        /* Payment icons */
        .payment-icon {
            width: 24px;
            height: 24px;
            margin-right: 8px;
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
                <a href="?lang=en<?php echo isset($amount) ? "&amount={$amount}" : ""; ?><?php echo isset($paymentMethod) ? "&method={$paymentMethod}" : ""; ?>" class="<?php echo $locale === 'en' ? 'font-bold text-primary' : 'text-gray-600'; ?> mr-2">EN</a> | 
                <a href="?lang=ko<?php echo isset($amount) ? "&amount={$amount}" : ""; ?><?php echo isset($paymentMethod) ? "&method={$paymentMethod}" : ""; ?>" class="<?php echo $locale === 'ko' ? 'font-bold text-primary' : 'text-gray-600'; ?> ml-2">KO</a>
            </div>
            
            <nav class="flex flex-wrap justify-center">
                <ul class="flex flex-wrap space-x-4 md:space-x-6 items-center">
                    <li><a href="index.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['home']; ?></a></li>
                    <li><a href="about.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['about']; ?></a></li>
                    <li><a href="programs.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['programs']; ?></a></li>
                    <li><a href="gallery.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['gallery']; ?></a></li>
                    <li><a href="index.php?lang=<?php echo $locale; ?>#contact" class="hover:text-primary px-2 py-1"><?php echo $t['contact']; ?></a></li>
                    <li>
                        <a href="#" class="bg-accent hover:bg-opacity-90 text-white px-4 py-2 rounded-md ml-2 font-semibold">
                            <?php echo $t['donate']; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Donation Form Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-center mb-6"><?php echo $t['donation_title']; ?></h1>
                <p class="text-lg text-gray-700 text-center mb-12 max-w-2xl mx-auto"><?php echo $t['donation_description']; ?></p>
                
                <?php if($formError): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo $formError; ?></p>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="donate.php?lang=<?php echo $locale; ?>" class="bg-white rounded-lg shadow-md p-8">
                    <input type="hidden" name="form_type" value="donate">
                    
                    <!-- Donation Amount Section -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['donation_amount']; ?></h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-md font-medium <?php echo $amount === '10000' ? 'active' : ''; ?>" data-amount="10000">₩10,000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-md font-medium <?php echo $amount === '30000' ? 'active' : ''; ?>" data-amount="30000">₩30,000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-md font-medium <?php echo $amount === '50000' ? 'active' : ''; ?>" data-amount="50000">₩50,000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-md font-medium <?php echo $amount === '100000' ? 'active' : ''; ?>" data-amount="100000">₩100,000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-md font-medium <?php echo $amount === '200000' ? 'active' : ''; ?>" data-amount="200000">₩200,000</button>
                            <button type="button" class="amount-btn bg-gray-100 hover:bg-gray-200 py-3 rounded-md font-medium <?php echo !in_array($amount, ['10000', '30000', '50000', '100000', '200000']) ? 'active' : ''; ?>" data-amount="custom"><?php echo $t['custom_amount']; ?></button>
                        </div>
                        
                        <div id="custom-amount-container" class="<?php echo !in_array($amount, ['10000', '30000', '50000', '100000', '200000']) ? 'block' : 'hidden'; ?> mb-4">
                            <label for="custom-amount" class="sr-only"><?php echo $t['custom_amount']; ?></label>
                            <input type="number" id="custom-amount" name="custom_amount" placeholder="<?php echo $t['amount_placeholder']; ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary" value="<?php echo !in_array($amount, ['10000', '30000', '50000', '100000', '200000']) ? $amount : ''; ?>">
                        </div>
                        
                        <input type="hidden" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>">
                    </div>
                    
                    <!-- Personal Information Section -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['personal_info']; ?></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="name" class="block text-gray-700 font-medium mb-2"><?php echo $t['name']; ?> *</label>
                                <input type="text" id="name" name="name" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-gray-700 font-medium mb-2"><?php echo $t['email']; ?> *</label>
                                <input type="email" id="email" name="email" required class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-gray-700 font-medium mb-2"><?php echo $t['phone']; ?></label>
                            <input type="tel" id="phone" name="phone" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    
                    <!-- Payment Method Section -->
                    <div class="mb-8">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['payment_method']; ?></h3>
                        
                        <!-- Enhanced payment method UI -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- KakaoPay Option -->
                            <div class="payment-option rounded-lg p-4 cursor-pointer <?php echo $paymentMethod === 'kakaopay' ? 'selected' : ''; ?>" data-method="kakaopay">
                                <input type="radio" name="payment_method" value="kakaopay" <?php echo $paymentMethod === 'kakaopay' ? 'checked' : ''; ?> class="form-radio h-5 w-5 text-primary hidden" id="kakaopay">
                                <label for="kakaopay" class="flex items-center cursor-pointer">
                                    <div class="w-8 h-8 rounded-full bg-[#ffeb00] flex items-center justify-center mr-3">
                                        <span class="text-black font-bold text-xs">K</span>
                                    </div>
                                    <span class="text-gray-700 font-medium"><?php echo $t['payment_kakaopay']; ?></span>
                                </label>
                            </div>
                            
                            <!-- NaverPay Option -->
                            <div class="payment-option rounded-lg p-4 cursor-pointer <?php echo $paymentMethod === 'naverpay' ? 'selected' : ''; ?>" data-method="naverpay">
                                <input type="radio" name="payment_method" value="naverpay" <?php echo $paymentMethod === 'naverpay' ? 'checked' : ''; ?> class="form-radio h-5 w-5 text-primary hidden" id="naverpay">
                                <label for="naverpay" class="flex items-center cursor-pointer">
                                    <div class="w-8 h-8 rounded-full bg-[#03c75a] flex items-center justify-center mr-3">
                                        <span class="text-white font-bold text-xs">N</span>
                                    </div>
                                    <span class="text-gray-700 font-medium"><?php echo $t['payment_naverpay']; ?></span>
                                </label>
                            </div>
                            
                            <!-- Bank Transfer Option -->
                            <div class="payment-option rounded-lg p-4 cursor-pointer <?php echo $paymentMethod === 'bank' ? 'selected' : ''; ?>" data-method="bank">
                                <input type="radio" name="payment_method" value="bank" <?php echo $paymentMethod === 'bank' ? 'checked' : ''; ?> class="form-radio h-5 w-5 text-primary hidden" id="bank">
                                <label for="bank" class="flex items-center cursor-pointer">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                                        <span class="text-gray-700 font-bold text-xs">B</span>
                                    </div>
                                    <span class="text-gray-700 font-medium"><?php echo $t['payment_bank']; ?></span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Bank Transfer Information -->
                        <div id="bank-info" class="mt-6 bg-gray-100 p-6 rounded-lg <?php echo $paymentMethod === 'bank' ? 'block' : 'hidden'; ?>">
                            <h4 class="text-lg font-semibold mb-3"><?php echo $t['bank_info_title']; ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t['bank_name']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings['bank_name']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t['account_number']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings['account_number']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t['account_holder']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings['account_holder']); ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t['business_number']; ?></p>
                                    <p class="font-medium"><?php echo htmlspecialchars($donationSettings['business_number']); ?></p>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-gray-700">
                                <?php echo $t['bank_transfer_note']; ?>
                            </p>
                        </div>
                        
                        <!-- KakaoPay Info -->
                        <div id="kakaopay-info" class="mt-6 bg-yellow-50 p-6 rounded-lg <?php echo $paymentMethod === 'kakaopay' ? 'block' : 'hidden'; ?>">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-[#ffeb00] flex items-center justify-center mr-4">
                                    <span class="text-black font-bold text-lg">K</span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold">KakaoPay</h4>
                                    <p class="text-sm text-gray-600"><?php echo $isKorean ? '결제가 간편하고 안전합니다.' : 'Fast and secure payment.'; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- NaverPay Info -->
                        <div id="naverpay-info" class="mt-6 bg-green-50 p-6 rounded-lg <?php echo $paymentMethod === 'naverpay' ? 'block' : 'hidden'; ?>">
                            <div class="flex items-center">
                                <div class="w-12 h-12 rounded-full bg-[#03c75a] flex items-center justify-center mr-4">
                                    <span class="text-white font-bold text-lg">N</span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold">NaverPay</h4>
                                    <p class="text-sm text-gray-600"><?php echo $isKorean ? '네이버페이로 빠르고 안전하게 결제하세요.' : 'Quick and secure payment with NaverPay.'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <button type="submit" class="bg-accent hover:bg-opacity-90 text-white px-6 py-3 rounded-md font-semibold w-full"><?php echo $t['donate_button']; ?></button>
                        <p class="mt-4 text-sm text-gray-600 text-center"><?php echo $t['tax_info']; ?></p>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 py-12 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">KOSMO Foundation</h3>
                    <p class="text-gray-400"><?php echo $t['footer_description']; ?></p>
                    <p class="text-gray-400 mt-4">서울 영등포구 문래로 187, 5층</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo $t['quick_links']; ?></h3>
                    <ul class="space-y-2">
                        <li><a href="index.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['home']; ?></a></li>
                        <li><a href="about.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['about']; ?></a></li>
                        <li><a href="programs.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['programs']; ?></a></li>
                        <li><a href="gallery.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['gallery']; ?></a></li>
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

    <script>
        // Donation amount buttons
        const amountButtons = document.querySelectorAll('.amount-btn');
        const customAmountContainer = document.getElementById('custom-amount-container');
        const customAmountInput = document.getElementById('custom-amount');
        const amountInput = document.getElementById('amount');
        
        amountButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                amountButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                const amount = this.getAttribute('data-amount');
                
                if (amount === 'custom') {
                    customAmountContainer.classList.remove('hidden');
                    customAmountInput.focus();
                    if (customAmountInput.value) {
                        amountInput.value = customAmountInput.value;
                    }
                } else {
                    customAmountContainer.classList.add('hidden');
                    amountInput.value = amount;
                }
            });
        });
        
        // Update hidden amount input when custom amount changes
        customAmountInput.addEventListener('input', function() {
            amountInput.value = this.value;
        });
        
        // Enhanced Payment Method Selection
        const paymentOptions = document.querySelectorAll('.payment-option');
        const bankInfoSection = document.getElementById('bank-info');
        const kakaopayInfoSection = document.getElementById('kakaopay-info');
        const naverpayInfoSection = document.getElementById('naverpay-info');
        
        paymentOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Get payment method
                const method = this.getAttribute('data-method');
                
                // Update radio button
                document.querySelector(`input[value="${method}"]`).checked = true;
                
                // Remove selected class from all options
                paymentOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Show appropriate info section
                bankInfoSection.classList.add('hidden');
                kakaopayInfoSection.classList.add('hidden');
                naverpayInfoSection.classList.add('hidden');
                
                if (method === 'bank') {
                    bankInfoSection.classList.remove('hidden');
                } else if (method === 'kakaopay') {
                    kakaopayInfoSection.classList.remove('hidden');
                } else if (method === 'naverpay') {
                    naverpayInfoSection.classList.remove('hidden');
                }
            });
        });
        
        // Simple form validation
        const donationForm = document.querySelector('form');
        
        donationForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const amount = amountInput.value;
            
            if (!name || !email || !amount) {
                e.preventDefault();
                alert('<?php echo $isKorean ? '모든 필수 필드를 작성해 주세요.' : 'Please fill in all required fields.'; ?>');
                return;
            }
            
            if (amount < <?php echo $donationSettings['min_donation_amount']; ?>) {
                e.preventDefault();
                alert('<?php echo $isKorean ? '최소 기부 금액은 ₩' . number_format($donationSettings["min_donation_amount"]) . '입니다.' : 'Minimum donation amount is ₩' . number_format($donationSettings["min_donation_amount"]) . '.'; ?>');
                return;
            }
            
            // For bank transfers, just show a thank you message
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            if (paymentMethod === 'bank') {
                e.preventDefault();
                alert('<?php echo $t['thank_you_message']; ?>');
                window.location.href = 'donate-process.php?status=success&amount=' + amount + '&method=bank&lang=<?php echo $locale; ?>';
            }
        });
    </script>
</body>
</html>