<?php
require_once __DIR__ . '/config.php';
// Set the default language to English
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'en';
$validLangs = ['en', 'ko'];

// Validate the language parameter
if (!in_array($lang, $validLangs)) {
    $lang = 'en';
}

// Load the language file
$translations = [];
if ($lang == 'en') {
    $t = [
        'title' => 'KOSMO Foundation - Korean Sports Medicine Support Foundation',
        'menu_home' => 'Home',
        'menu_about' => 'About',
        'menu_programs' => 'Programs',
        'menu_gallery' => 'Gallery',
        'menu_news' => 'News',
        'menu_events' => 'Events',
        'menu_volunteer' => 'Volunteer',
        'menu_donate' => 'Donate',
        'menu_contact' => 'Contact',
        'lang_switch' => '한국어',
        'lang_switch_url' => '?lang=ko',
        'hero_title' => 'Advancing Sports Medicine & Supporting Athletes',
        'hero_subtitle' => 'Korean Sports Medicine Support Foundation',
        'hero_text' => 'We provide medical support, education, and resources to athletes and healthcare professionals.',
        'donate_button' => 'Donate Now',
        'learn_more' => 'Learn More',
        'programs_title' => 'Our Programs',
        'view_program' => 'View Program',
        'mission_title' => 'Our Mission',
        'mission_text' => 'The Korean Sports Medicine Support Foundation (KOSMO) is dedicated to advancing sports medicine and providing support to athletes through education, research, and direct services.',
        'vision_title' => 'Our Vision',
        'vision_text' => 'To create a world where all athletes have access to high-quality sports medicine care and support, enabling them to achieve their full potential safely.',
        'values_title' => 'Our Values',
        'values_text' => 'Excellence, Integrity, Innovation, Collaboration, and Compassion guide our work in supporting athletes and advancing sports medicine.',
        'about_title' => 'About KOSMO Foundation',
        'about_text' => 'Founded in 2020, KOSMO Foundation is a government-certified non-profit organization dedicated to providing medical support, education, and resources to athletes and healthcare professionals in Korea.',
        'director_title' => 'Meet Our Director',
        'director_name' => 'Dr. Lee Sang-hoon, MD, PhD',
        'director_position' => 'Executive Director',
        'director_text' => 'Dr. Lee Sang-hoon is an orthopedic surgeon and sports medicine specialist with over 20 years of experience in treating athletes of all levels, from amateur to Olympic.',
        'contact_title' => 'Contact Us',
        'address' => 'Address',
        'address_text' => '5th Floor, 187 Mullae-ro, Yeongdeungpo-gu, Seoul, Republic of Korea',
        'phone' => 'Phone',
        'phone_text' => '+82-2-1234-5678',
        'email' => 'Email',
        'email_text' => 'goodwill@kosmo.or.kr',
        'copyright' => '© 2025 Korean Sports Medicine Support Foundation (KOSMO). All Rights Reserved.'
    ];
} else {
    $t = [
        'title' => '한국스포츠의료지원재단',
        'menu_home' => '홈',
        'menu_about' => '소개',
        'menu_programs' => '프로그램',
        'menu_gallery' => '갤러리',
        'menu_news' => '뉴스',
        'menu_events' => '이벤트',
        'menu_volunteer' => '자원봉사',
        'menu_donate' => '후원',
        'menu_contact' => '문의',
        'lang_switch' => 'English',
        'lang_switch_url' => '?lang=en',
        'hero_title' => '스포츠 의학 발전 및 선수 지원',
        'hero_subtitle' => '한국 스포츠의료지원재단',
        'hero_text' => '저희는 선수와 의료 전문가들에게 의료 지원, 교육 및 자원을 제공합니다.',
        'donate_button' => '후원하기',
        'learn_more' => '자세히 보기',
        'programs_title' => '주요 프로그램',
        'view_program' => '프로그램 보기',
        'mission_title' => '미션',
        'mission_text' => '한국스포츠의료지원재단(KOSMO)은 교육, 연구 및 직접적인 서비스를 통해 스포츠 의학을 발전시키고 선수들을 지원하는 데 전념하고 있습니다.',
        'vision_title' => '비전',
        'vision_text' => '모든 선수들이 고품질의 스포츠 의학 치료와 지원에 접근할 수 있고, 안전하게 잠재력을 최대한 발휘할 수 있는 세상을 만드는 것을 목표로 합니다.',
        'values_title' => '핵심 가치',
        'values_text' => '우수성, 청렴성, 혁신, 협력, 그리고 연민은 선수 지원과 스포츠 의학 발전에 있어 우리의 일을 이끄는 가치입니다.',
        'about_title' => '한국스포츠의료지원재단 소개',
        'about_text' => '2020년에 설립된 한국스포츠의료지원재단은 한국의 선수와 의료 전문가들에게 의료 지원, 교육 및 자원을 제공하는 데 전념하는 정부 인증 비영리 단체입니다.',
        'director_title' => '재단 이사장 소개',
        'director_name' => '이상훈 의학박사',
        'director_position' => '상임이사',
        'director_text' => '이상훈 박사는 아마추어부터 올림픽까지 모든 수준의 선수들을 치료한 20년 이상의 경험을 가진 정형외과 의사이자 스포츠 의학 전문가입니다.',
        'contact_title' => '문의하기',
        'address' => '주소',
        'address_text' => '서울특별시 영등포구 문래로 187, 5층',
        'phone' => '전화',
        'phone_text' => '02-1234-5678',
        'email' => '이메일',
        'email_text' => 'goodwill@kosmo.or.kr',
        'copyright' => '© 2025 한국스포츠의료지원재단(KOSMO). 모든 권리 보유.'
    ];
}

// Get the featured programs from database
try {
    // Database connection
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the first 3 programs (top priority programs should be the ones with lowest IDs)
    // Using ORDER BY id to ensure consistency
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY id LIMIT 3");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // If no programs found or less than 3, we should check if table exists
    if (count($programs) < 3) {
        // Check if programs table exists and has data
        $stmt = $pdo->query("SELECT COUNT(*) FROM programs");
        $programCount = $stmt->fetchColumn();
        
        if ($programCount == 0) {
            // No programs in database, run sync-programs.php to populate
            header("Location: sync-programs.php");
            exit;
        }
    }
    
} catch (PDOException $e) {
    // Fallback to hard-coded data if database connection fails
    $programs = [
        [
            'slug' => 'medical-support',
            'title' => 'Medical Support for Students & Student-Athletes',
            'ko_title' => '학생·학생선수 의료 지원',
            'image' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
            'description' => 'Providing medical care and support for students and student-athletes.',
            'ko_description' => '학생 및 학생선수를 위한 의료 지원 제공.'
        ],
        [
            'slug' => 'cultural-arts',
            'title' => 'Cultural & Arts Education/Events',
            'ko_title' => '문화·예술 교육 및 행사',
            'image' => 'https://images.unsplash.com/photo-1535982330050-f1c2fb79ff78?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
            'description' => 'Fostering creativity and expression through cultural and arts programs.',
            'ko_description' => '문화 및 예술 프로그램을 통한 창의성과 표현력 육성.'
        ],
        [
            'slug' => 'leadership',
            'title' => 'Personal Development & Leadership Training',
            'ko_title' => '자기개발·리더십 교육',
            'image' => 'https://images.unsplash.com/photo-1546512636-afb9dcf17149?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1171&q=80',
            'description' => 'Building leadership skills and personal growth opportunities.',
            'ko_description' => '리더십 기술 구축 및 개인 성장 기회 제공.'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <meta name="description" content="KOSMO Foundation provides medical support, education, and resources to athletes and healthcare professionals in Korea.">
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap');
        body {
            font-family: 'Open Sans', 'Noto Sans KR', sans-serif;
        }
    </style>
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
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="index.php<?php echo "?lang=$lang"; ?>" class="text-2xl font-bold text-primary">KOSMO</a>
            <nav class="hidden md:flex space-x-6">
                <a href="index.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_home']; ?></a>
                <a href="about.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_about']; ?></a>
                <a href="programs.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_programs']; ?></a>
                <a href="gallery.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_gallery']; ?></a>
                <a href="news.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_news']; ?></a>
                <a href="calendar.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_events']; ?></a>
                <a href="volunteer.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_volunteer']; ?></a>
                <a href="donate.php<?php echo "?lang=$lang"; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['menu_donate']; ?></a>
            </nav>
            <div class="flex items-center space-x-4">
                <a href="index.php<?php echo $t['lang_switch_url']; ?>" class="text-gray-700 hover:text-primary"><?php echo $t['lang_switch']; ?></a>
                <a href="donate.php<?php echo "?lang=$lang"; ?>" class="hidden md:block bg-accent hover:bg-accent/90 text-white px-4 py-2 rounded"><?php echo $t['donate_button']; ?></a>
                <button class="md:hidden text-gray-700" id="mobile-menu-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-white" id="mobile-menu">
            <div class="px-4 py-2 space-y-3">
                <a href="index.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_home']; ?></a>
                <a href="about.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_about']; ?></a>
                <a href="programs.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_programs']; ?></a>
                <a href="gallery.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_gallery']; ?></a>
                <a href="news.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_news']; ?></a>
                <a href="calendar.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_events']; ?></a>
                <a href="volunteer.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_volunteer']; ?></a>
                <a href="donate.php<?php echo "?lang=$lang"; ?>" class="block text-gray-700 hover:text-primary"><?php echo $t['menu_donate']; ?></a>
                <a href="donate.php<?php echo "?lang=$lang"; ?>" class="block bg-accent hover:bg-accent/90 text-white px-4 py-2 rounded text-center mt-4"><?php echo $t['donate_button']; ?></a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="relative bg-primary text-white">
        <div class="absolute inset-0 bg-black/40 z-10"></div>
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1610926950565-29427d641b01?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80');"></div>
        <div class="container mx-auto px-4 py-24 relative z-20">
            <div class="max-w-2xl">
                <p class="text-xl mb-2"><?php echo $t['hero_subtitle']; ?></p>
                <h1 class="text-4xl md:text-5xl font-bold mb-6"><?php echo $t['hero_title']; ?></h1>
                <p class="text-xl mb-8"><?php echo $t['hero_text']; ?></p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="donate.php<?php echo "?lang=$lang"; ?>" class="bg-accent hover:bg-accent/90 text-white font-medium py-3 px-6 rounded text-center"><?php echo $t['donate_button']; ?></a>
                    <a href="about.php<?php echo "?lang=$lang"; ?>" class="bg-white hover:bg-gray-100 text-primary font-medium py-3 px-6 rounded text-center"><?php echo $t['learn_more']; ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12"><?php echo $t['programs_title']; ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach($programs as $program): ?>
                <div class="rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                    <img src="<?php echo $program['image']; ?>" alt="<?php echo $lang == 'en' ? $program['title'] : $program['ko_title']; ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2"><?php echo $lang == 'en' ? $program['title'] : $program['ko_title']; ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo $lang == 'en' ? $program['description'] : $program['ko_description']; ?></p>
                        <a href="program.php?slug=<?php echo $program['slug']; ?>&lang=<?php echo $lang; ?>" class="text-primary hover:underline"><?php echo $t['view_program']; ?> →</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-12">
                <a href="programs.php<?php echo "?lang=$lang"; ?>" class="bg-primary hover:bg-primary/90 text-white font-medium py-3 px-8 rounded inline-block"><?php echo $t['view_program']; ?></a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-6"><?php echo $t['about_title']; ?></h2>
                    <p class="text-gray-600 mb-6"><?php echo $t['about_text']; ?></p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-primary"><?php echo $t['mission_title']; ?></h3>
                            <p class="text-gray-600"><?php echo $t['mission_text']; ?></p>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-primary"><?php echo $t['vision_title']; ?></h3>
                            <p class="text-gray-600"><?php echo $t['vision_text']; ?></p>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2 text-primary"><?php echo $t['values_title']; ?></h3>
                            <p class="text-gray-600"><?php echo $t['values_text']; ?></p>
                        </div>
                    </div>
                </div>
                <div>
                    <img src="https://images.unsplash.com/photo-1599058917212-d750089bc07e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1169&q=80" alt="KOSMO Foundation" class="rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Director Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12"><?php echo $t['director_title']; ?></h2>
            <div class="flex flex-col md:flex-row items-center justify-center space-y-8 md:space-y-0 md:space-x-12">
                <div class="w-64 h-64 rounded-full overflow-hidden">
                    <img src="assets/images/kosmo/director/director.jpg" alt="<?php echo $t['director_name']; ?>" class="w-full h-full object-cover">
                </div>
                <div class="max-w-lg text-center md:text-left">
                    <h3 class="text-2xl font-bold text-primary"><?php echo $t['director_name']; ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo $t['director_position']; ?></p>
                    <p class="text-gray-700"><?php echo $t['director_text']; ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12"><?php echo $t['contact_title']; ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-primary mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <h3 class="text-xl font-bold mb-2"><?php echo $t['address']; ?></h3>
                    <p class="text-gray-600"><?php echo $t['address_text']; ?></p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-primary mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                    </svg>
                    <h3 class="text-xl font-bold mb-2"><?php echo $t['phone']; ?></h3>
                    <p class="text-gray-600"><?php echo $t['phone_text']; ?></p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-primary mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                    </svg>
                    <h3 class="text-xl font-bold mb-2"><?php echo $t['email']; ?></h3>
                    <p class="text-gray-600"><?php echo $t['email_text']; ?></p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4 max-w-4xl">
            <?php 
            // Define newsletter translations for the component
            function getNewsletterTranslations($language) {
                return [
                    'en' => [
                        'newsletter_heading' => 'Subscribe to Our Newsletter',
                        'newsletter_subheading' => 'Stay updated on our latest news, events, and programs.',
                        'name_label' => 'Name (optional)',
                        'email_label' => 'Email Address',
                        'subscribe_button' => 'Subscribe',
                        'language_label' => 'Preferred Language',
                        'english' => 'English',
                        'korean' => 'Korean',
                        'success_message' => 'Thank you for subscribing to our newsletter!',
                        'error_message' => 'An error occurred. Please try again.',
                        'email_required' => 'Please enter a valid email address.',
                        'already_subscribed' => 'You are already subscribed to our newsletter.',
                        'subscription_reactivated' => 'Your subscription has been reactivated.',
                    ],
                    'ko' => [
                        'newsletter_heading' => '뉴스레터 구독',
                        'newsletter_subheading' => '최신 뉴스, 이벤트 및 프로그램에 대한 업데이트를 받아보세요.',
                        'name_label' => '이름 (선택사항)',
                        'email_label' => '이메일 주소',
                        'subscribe_button' => '구독하기',
                        'language_label' => '선호하는 언어',
                        'english' => '영어',
                        'korean' => '한국어',
                        'success_message' => '뉴스레터 구독해 주셔서 감사합니다!',
                        'error_message' => '오류가 발생했습니다. 다시 시도해 주세요.',
                        'email_required' => '유효한 이메일 주소를 입력해 주세요.',
                        'already_subscribed' => '이미 뉴스레터를 구독하고 계십니다.',
                        'subscription_reactivated' => '구독이 다시 활성화되었습니다.',
                    ]
                ][$language] ?? [
                    'newsletter_heading' => 'Subscribe to Our Newsletter',
                    'newsletter_subheading' => 'Stay updated on our latest news, events, and programs.',
                    'name_label' => 'Name (optional)',
                    'email_label' => 'Email Address',
                    'subscribe_button' => 'Subscribe',
                    'language_label' => 'Preferred Language',
                    'english' => 'English',
                    'korean' => 'Korean',
                    'success_message' => 'Thank you for subscribing to our newsletter!',
                    'error_message' => 'An error occurred. Please try again.',
                    'email_required' => 'Please enter a valid email address.',
                    'already_subscribed' => 'You are already subscribed to our newsletter.',
                    'subscription_reactivated' => 'Your subscription has been reactivated.',
                ];
            }
            
            $trans = getNewsletterTranslations($lang);
            include 'components/newsletter-form.php'; 
            ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">KOSMO</h3>
                    <p class="text-gray-400">Korean Sports Medicine Support Foundation</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4"><?php echo $t['menu_programs']; ?></h3>
                    <ul class="space-y-2 text-gray-400">
                        <?php foreach(array_slice($programs, 0, 3) as $program): ?>
                        <li><a href="program.php?slug=<?php echo $program['slug']; ?>&lang=<?php echo $lang; ?>" class="hover:text-white"><?php echo $lang == 'en' ? $program['title'] : $program['ko_title']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4"><?php echo $t['contact_title']; ?></h3>
                    <p class="text-gray-400 mb-2"><?php echo $t['address_text']; ?></p>
                    <p class="text-gray-400 mb-2"><?php echo $t['phone_text']; ?></p>
                    <p class="text-gray-400"><?php echo $t['email_text']; ?></p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4"><?php echo $lang == 'en' ? 'Get Involved' : '참여하기'; ?></h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="donate.php?lang=<?php echo $lang; ?>" class="hover:text-white"><?php echo $t['menu_donate']; ?></a></li>
                        <li><a href="volunteer.php?lang=<?php echo $lang; ?>" class="hover:text-white"><?php echo $t['menu_volunteer']; ?></a></li>
                        <li><a href="news.php?lang=<?php echo $lang; ?>" class="hover:text-white"><?php echo $t['menu_news']; ?></a></li>
                        <li><a href="calendar.php?lang=<?php echo $lang; ?>" class="hover:text-white"><?php echo $t['menu_events']; ?></a></li>
                    </ul>
                    <a href="donate.php<?php echo "?lang=$lang"; ?>" class="bg-accent hover:bg-accent/90 text-white px-4 py-2 rounded inline-block mt-4"><?php echo $t['donate_button']; ?></a>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-700 text-center text-gray-400">
                <p><?php echo $t['copyright']; ?></p>
                <p class="mt-2 text-sm"><a href="admin.php" class="text-gray-500 hover:text-gray-300">Admin</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
