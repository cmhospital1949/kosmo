<?php
// Contact form handling
$formSubmitted = false;
$formSuccess = false;
$formError = '';
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Simple validation
    if (empty($name) || empty($email) || empty($message)) {
        $formError = $isKorean ? '모든 필수 항목을 작성해 주세요.' : 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = $isKorean ? '유효한 이메일 주소를 입력해 주세요.' : 'Please enter a valid email address.';
    } else {
        // Send email (this is a simple example - in production, use a proper mail library)
        $to = "goodwill@kosmo.or.kr";
        $subject = "Contact Form Submission from KOSMO Website";
        $emailBody = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: $email";
        
        // Attempt to send email
        $mailSent = true; // In a real environment, use mail() function
        // $mailSent = mail($to, $subject, $emailBody, $headers);
        
        if ($mailSent) {
            $formSubmitted = true;
            $formSuccess = true;
        } else {
            $formError = $isKorean ? '메시지 전송에 실패했습니다. 나중에 다시 시도해 주세요.' : 'Failed to send your message. Please try again later.';
        }
    }
}

// Simple translations
$translations = [
    'en' => [
        'site_title' => 'KOSMO Foundation',
        'contact_us' => 'Contact Us',
        'thank_you' => 'Thank You!',
        'success_message' => 'Your message has been sent successfully. We will get back to you as soon as possible.',
        'error_title' => 'Error',
        'back_to_home' => 'Back to Home',
        'try_again' => 'Try Again',
        'copyright' => '© 2025 KOSMO Foundation. All rights reserved.'
    ],
    'ko' => [
        'site_title' => '코스모 재단',
        'contact_us' => '연락처',
        'thank_you' => '감사합니다!',
        'success_message' => '메시지가 성공적으로 전송되었습니다. 최대한 빨리 답변 드리겠습니다.',
        'error_title' => '오류',
        'back_to_home' => '홈으로 돌아가기',
        'try_again' => '다시 시도',
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
    <title><?php echo $t['contact_us']; ?> | <?php echo $t['site_title']; ?></title>
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
        
        :root {
            --primary-color: #0066cc;
            --secondary-color: #4d9aff;
            --accent-color: #ff6b00;
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
                <a href="?lang=en" class="<?php echo $locale === 'en' ? 'font-bold text-primary' : 'text-gray-600'; ?> mr-2">EN</a> | 
                <a href="?lang=ko" class="<?php echo $locale === 'ko' ? 'font-bold text-primary' : 'text-gray-600'; ?> ml-2">KO</a>
            </div>
        </div>
    </header>

    <!-- Contact Form Response -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto">
                <?php if($formSuccess): ?>
                <!-- Success Message -->
                <div class="bg-green-100 border-l-4 border-green-500 p-8 rounded-md mb-8">
                    <h2 class="text-2xl font-bold text-green-800 mb-4"><?php echo $t['thank_you']; ?></h2>
                    <p class="text-green-700 mb-6"><?php echo $t['success_message']; ?></p>
                    <a href="index.php?lang=<?php echo $locale; ?>" class="inline-flex items-center text-green-600 hover:underline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        <?php echo $t['back_to_home']; ?>
                    </a>
                </div>
                <?php else: ?>
                <!-- Error Message -->
                <div class="bg-red-100 border-l-4 border-red-500 p-8 rounded-md mb-8">
                    <h2 class="text-2xl font-bold text-red-800 mb-4"><?php echo $t['error_title']; ?></h2>
                    <p class="text-red-700 mb-6"><?php echo $formError; ?></p>
                    <div class="flex space-x-4">
                        <a href="index.php?lang=<?php echo $locale; ?>#contact" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-md font-semibold inline-block"><?php echo $t['try_again']; ?></a>
                        <a href="index.php?lang=<?php echo $locale; ?>" class="inline-flex items-center text-red-600 hover:underline mt-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                            </svg>
                            <?php echo $t['back_to_home']; ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 py-12 text-white">
        <div class="container mx-auto px-4">            
            <div class="mt-12 pt-8 border-t border-gray-700 text-center text-gray-400">
                <p><?php echo $t['copyright']; ?></p>
            </div>
        </div>
    </footer>
</body>
</html>
