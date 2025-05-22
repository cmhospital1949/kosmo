<?php
// Get the requested language
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

// Simple translations
$translations = [
    'en' => [
        'site_title' => 'KOSMO Foundation',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'contact' => 'Contact',
        'donate' => 'Donate',
        'error_title' => 'Page Not Found',
        'error_message' => 'The page you are looking for does not exist or has been moved.',
        'back_to_home' => 'Back to Home',
        'copyright' => '© 2025 KOSMO Foundation. All rights reserved.'
    ],
    'ko' => [
        'site_title' => '코스모 재단',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'contact' => '연락처',
        'donate' => '후원하기',
        'error_title' => '페이지를 찾을 수 없습니다',
        'error_message' => '찾으시는 페이지가 존재하지 않거나 이동되었습니다.',
        'back_to_home' => '홈으로 돌아가기',
        'copyright' => '© 2025 코스모 재단. 모든 권리 보유.'
    ]
];

// Get the current language translations
$t = $translations[$locale];

// Send 404 header
header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html>
<html lang="<?php echo $locale; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - <?php echo $t['error_title']; ?> | <?php echo $t['site_title']; ?></title>
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

    <!-- 404 Error Content -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto text-center">
                <h1 class="text-9xl font-bold text-primary mb-4">404</h1>
                <h2 class="text-3xl font-semibold mb-6"><?php echo $t['error_title']; ?></h2>
                <p class="text-gray-600 mb-8"><?php echo $t['error_message']; ?></p>
                <a href="index.php?lang=<?php echo $locale; ?>" class="inline-flex items-center justify-center bg-primary hover:bg-opacity-90 text-white px-6 py-3 rounded-md font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <?php echo $t['back_to_home']; ?>
                </a>
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
