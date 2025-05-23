<?php
// Get the requested language
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

// Connect to the database

require_once __DIR__ . '/lib/Database.php';

try {
    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all programs from the database
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY id");
    $all_programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Fall back to hard-coded data if database connection fails
    $all_programs = [
        [
            'slug' => 'medical-support',
            'title' => 'Medical Support for Students & Student-Athletes',
            'ko_title' => '학생·학생선수 의료 지원',
            'image' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
            'description' => 'Providing medical care and support for students and student-athletes.',
            'ko_description' => '학생 및 학생선수를 위한 의료 지원 제공.'
        ],
        // Other programs would be here as fallback...
    ];
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
        'our_programs' => 'Our Programs',
        'programs_description' => 'KOSMO Foundation offers a wide range of programs to support students and athletes in their health, education, and career development.',
        'learn_more' => 'Learn more',
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
        'our_programs' => '프로그램 안내',
        'programs_description' => '코스모 재단은 학생과 선수들의 건강, 교육, 그리고 경력 개발을 지원하기 위한 다양한 프로그램을 제공합니다.',
        'learn_more' => '자세히 보기',
        'back_to_home' => '홈으로 돌아가기',
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
    <title><?php echo $t['programs']; ?> | <?php echo $t['site_title']; ?></title>
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
                    <li><a href="#" class="text-primary font-semibold px-2 py-1"><?php echo $t['programs']; ?></a></li>
                    <li><a href="index.php?lang=<?php echo $locale; ?>#contact" class="hover:text-primary px-2 py-1"><?php echo $t['contact']; ?></a></li>
                    <li>
                        <a href="index.php?lang=<?php echo $locale; ?>#donate" class="bg-accent hover:bg-opacity-90 text-white px-4 py-2 rounded-md ml-2">
                            <?php echo $t['donate']; ?>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Programs Header -->
    <section class="py-12 bg-primary bg-opacity-10">
        <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-4xl font-bold mb-6"><?php echo $t['our_programs']; ?></h1>
                <p class="text-lg text-gray-700 mb-0"><?php echo $t['programs_description']; ?></p>
            </div>
        </div>
    </section>

    <!-- Programs Grid -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach($all_programs as $program): ?>
                <!-- Program Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                    <img src="<?php echo $program['image']; ?>" alt="<?php echo $isKorean ? $program['ko_title'] : $program['title']; ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2"><?php echo $isKorean ? $program['ko_title'] : $program['title']; ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo $isKorean ? $program['ko_description'] : $program['description']; ?></p>
                        <a href="program.php?slug=<?php echo $program['slug']; ?>&lang=<?php echo $locale; ?>" class="text-primary font-medium hover:underline"><?php echo $t['learn_more']; ?></a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="mt-12 text-center">
                <a href="index.php?lang=<?php echo $locale; ?>" class="inline-flex items-center text-primary hover:underline">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4"><?php echo $t['site_title']; ?></h3>
                    <p class="text-gray-400">Dedicated to improving the lives of students and athletes.</p>
                    <p class="text-gray-400 mt-4">서울 영등포구 문래로 187, 5층</p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['home']; ?></a></li>
                        <li><a href="index.php?lang=<?php echo $locale; ?>#about" class="text-gray-400 hover:text-white"><?php echo $t['about']; ?></a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white"><?php echo $t['programs']; ?></a></li>
                        <li><a href="index.php?lang=<?php echo $locale; ?>#contact" class="text-gray-400 hover:text-white"><?php echo $t['contact']; ?></a></li>
                        <li><a href="index.php?lang=<?php echo $locale; ?>#donate" class="text-gray-400 hover:text-white"><?php echo $t['donate']; ?></a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
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
