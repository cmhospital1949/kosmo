<?php
// Get language from cookie or default to English
$locale = $_COOKIE['locale'] ?? 'en';
$isKorean = $locale === 'ko';

// Simple translations
$translations = [
    'en' => [
        'title' => 'Server Error - KOSMO Foundation',
        'heading' => 'Server Error',
        'message' => 'We apologize, but the server encountered an internal error and was unable to complete your request. Our technical team has been notified and is working to resolve the issue.',
        'back_link' => 'Return to Home Page',
        'report_issue' => 'If this error persists, please contact us at goodwill@kosmo.or.kr.',
    ],
    'ko' => [
        'title' => '서버 오류 - 코스모 재단',
        'heading' => '서버 오류',
        'message' => '죄송합니다. 서버에 내부 오류가 발생하여 요청을 완료할 수 없습니다. 기술팀에 알림이 전송되었으며 문제 해결을 위해 노력하고 있습니다.',
        'back_link' => '홈페이지로 돌아가기',
        'report_issue' => '이 오류가 지속되면 goodwill@kosmo.or.kr로 문의해 주세요.',
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
    <title><?php echo $t['title']; ?></title>
    
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
<body class="bg-gray-100 min-h-screen flex flex-col">
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center mb-4 md:mb-0">
                <a href="/" class="text-2xl font-bold text-primary">KOSMO</a>
                <span class="ml-2 text-sm text-gray-600">Foundation</span>
            </div>
            
            <!-- Language Switcher -->
            <div class="mb-4 md:mb-0">
                <a href="javascript:document.cookie='locale=en;path=/';window.location.reload();" class="<?php echo $locale === 'en' ? 'font-bold text-primary' : 'text-gray-600'; ?> mr-2">EN</a> | 
                <a href="javascript:document.cookie='locale=ko;path=/';window.location.reload();" class="<?php echo $locale === 'ko' ? 'font-bold text-primary' : 'text-gray-600'; ?> ml-2">KO</a>
            </div>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center">
        <div class="max-w-lg mx-auto p-8 bg-white shadow-lg rounded-lg text-center mt-8 mb-8">
            <svg class="w-16 h-16 text-red-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            
            <h1 class="text-2xl font-bold text-gray-800 mb-4"><?php echo $t['heading']; ?></h1>
            <p class="text-gray-600 mb-6"><?php echo $t['message']; ?></p>
            
            <a href="/" class="inline-block bg-primary hover:bg-opacity-90 text-white px-6 py-3 rounded-md font-semibold mb-4">
                <?php echo $t['back_link']; ?>
            </a>
            
            <p class="text-gray-500 text-sm"><?php echo $t['report_issue']; ?></p>
            
            <?php if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'bestluck.dothome.co.kr'): ?>
            <div class="mt-8 p-4 bg-gray-100 text-left text-sm text-gray-600">
                <strong>Technical Information:</strong>
                <pre class="overflow-auto"><?php 
                    // Display error information if available
                    if(function_exists('error_get_last') && $error = error_get_last()) {
                        echo "Error: " . $error['message'] . "\n";
                        echo "File: " . $error['file'] . "\n";
                        echo "Line: " . $error['line'] . "\n";
                    } else {
                        echo "No error information available.";
                    }
                ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="bg-gray-800 py-6 text-white">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400">&copy; 2025 KOSMO Foundation. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>