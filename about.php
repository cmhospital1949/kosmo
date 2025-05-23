<?php
// Language settings
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

// Simple translations
$translations = [
    'en' => [
        'site_title' => 'About KOSMO Foundation',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'gallery' => 'Gallery',
        'contact' => 'Contact',
        'donate' => 'Donate',
        'about_title' => 'About KOSMO Foundation',
        'about_description' => 'KOSMO Foundation (Korean Sports Medicine Support Foundation) is a government-certified non-profit organization dedicated to supporting students and athletes through various programs focusing on health, education, and personal development. We believe in creating opportunities for all individuals to reach their full potential.',
        'mission_title' => 'Our Mission',
        'mission_description' => 'To improve the lives of students and athletes through comprehensive support programs, promoting health, education, and community development.',
        'vision_title' => 'Our Vision',
        'vision_description' => 'A world where every student and athlete has access to the resources they need to succeed in their education, career, and life.',
        'director_title' => 'About Our Director',
        'director_name' => 'Dr. Lee Sang-hoon',
        'director_position' => 'Director of KOSMO Foundation, Director of CM Hospital',
        'director_description_1' => 'Dr. Lee Sang-hoon is the director of KOSMO Foundation and one of Korea\'s most distinguished figures in sports medicine. As the director of CM Hospital, he is Korea\'s first IOC-certified sports medicine specialist who has pioneered innovative approaches to sports rehabilitation and medical support for athletes.',
        'director_description_2' => 'With over 20 years of experience in sports medicine, Dr. Lee has dedicated his career to improving medical support systems for student-athletes and developing comprehensive rehabilitation programs. He has treated more than 20,000 national elite athletes and serves as the leading team doctor for Korean national sports teams at international competitions.',
        'director_description_3' => 'Under his leadership, KOSMO Foundation has established numerous initiatives to provide medical assistance, educational opportunities, and career development for students and athletes throughout Korea. Dr. Lee is known for his exceptional work at major international events including the Asian Games and his commitment to advancing sports medicine practices in Korea.',
        'director_description_4' => 'Dr. Lee also serves as the third-generation director of CM Hospital (formerly Chungmu Hospital) in Yeongdeungpo-gu, Seoul. He continues the legacy of his family while bringing his expertise from Columbia University Medical Center, where he completed his clinical fellowship, and his time as a professor at Konkuk University Medical Center.',
        'organization_title' => 'Our Organization',
        'organization_description' => 'KOSMO Foundation is structured to efficiently deliver programs and services to our beneficiaries through dedicated departments and professional staff.',
        'org_board_directors' => 'Board of Directors',
        'org_secretary_general' => 'Secretary General',
        'org_program_division' => 'Program Division',
        'org_planning_division' => 'Planning Division',
        'org_pr_finance_division' => 'PR & Finance Division',
        'foundation_history' => 'Foundation History',
        'history_description' => 'KOSMO Foundation has evolved through several key milestones since its establishment, expanding its programs and reach to better serve students and athletes.',
        'business_info_title' => 'Business Information',
        'business_number' => 'Business Registration Number',
        'business_number_value' => '322-82-00643',
        'back_to_home' => 'Back to Home',
        'contact_us' => 'Contact Us',
        'copyright' => '© 2025 KOSMO Foundation. All rights reserved.',
        'quick_links' => 'Quick Links',
        'foundation_address' => 'Foundation Address',
        'address_line' => '5th Floor, 187, Mullae-ro, Yeongdeungpo-gu, Seoul'
    ],
    'ko' => [
        'site_title' => '코스모 재단 소개',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'gallery' => '갤러리',
        'contact' => '연락처',
        'donate' => '후원하기',
        'about_title' => '코스모 재단 소개',
        'about_description' => '코스모 재단(한국스포츠의료지원재단)은 건강, 교육 및 개인 발전에 중점을 둔 다양한 프로그램을 통해 학생과 운동선수를 지원하는 정부 인증 비영리 단체입니다. 우리는 모든 개인이 잠재력을 최대한 발휘할 수 있는 기회를 창출하는 것을 믿습니다.',
        'mission_title' => '우리의 미션',
        'mission_description' => '포괄적인 지원 프로그램을 통해 학생과 선수들의 삶을 개선하고, 건강, 교육 및 커뮤니티 발전을 촉진합니다.',
        'vision_title' => '우리의 비전',
        'vision_description' => '모든 학생과 선수가 교육, 직업 및 삶에서 성공하는 데 필요한 자원에 접근할 수 있는 세상을 만듭니다.',
        'director_title' => '재단 이사장 소개',
        'director_name' => '이상훈 원장',
        'director_position' => '한국스포츠의료지원재단 이사장, CM병원 원장',
        'director_description_1' => '이상훈 원장은 코스모 재단의 이사장이자 한국 스포츠 의학 분야의 최고 권위자입니다. 그는 한국 최초 IOC 인증 스포츠 전문의로서 CM병원의 원장으로 재직하며 스포츠 재활 및 운동선수 의료 지원에 혁신적인 접근 방식을 개척해 왔습니다.',
        'director_description_2' => '스포츠 의학 분야에서 20년 이상의 경험을 가진 이상훈 원장은 학생선수를 위한 의료 지원 시스템을 개선하고 종합적인 재활 프로그램을 개발하는 데 그의 경력을 헌신해 왔습니다. 그는 2만 명이 넘는 국가대표 엘리트 선수들을 치료했으며 국제 대회에서 한국 국가대표팀의 주요 팀닥터로 활동하고 있습니다.',
        'director_description_3' => '그의 리더십 아래, 코스모 재단은 한국 전역의 학생과 운동선수들에게 의료 지원, 교육 기회, 경력 개발을 제공하는 수많은 이니셔티브를 확립했습니다. 이상훈 원장은 아시안 게임을 포함한 주요 국제 대회에서의 탁월한 활동과 한국의 스포츠 의학 발전에 대한 헌신으로 잘 알려져 있습니다.',
        'director_description_4' => '이상훈 원장은 또한 서울 영등포구에 위치한 CM병원(구 충무병원)의 3대 원장으로 재직 중입니다. 그는 콜롬비아 의대에서의 임상 강사 경험과 건국대학교 의료원 교수로서의 시간을 통해 얻은 전문 지식을 바탕으로 가족의 전통을 이어가고 있습니다.',
        'organization_title' => '조직 구성',
        'organization_description' => '코스모 재단은 전문 부서와 전문 직원을 통해 수혜자에게 프로그램과 서비스를 효율적으로 제공하도록 구성되어 있습니다.',
        'org_board_directors' => '이사장',
        'org_secretary_general' => '사무국장',
        'org_program_division' => '사업팀',
        'org_planning_division' => '기획팀',
        'org_pr_finance_division' => '홍보/재정팀',
        'foundation_history' => '재단 역사',
        'history_description' => '코스모 재단은 설립 이후 여러 주요 이정표를 거쳐 발전해 왔으며, 학생과 선수들을 더 잘 지원하기 위해 프로그램과 영향력을 확대해 왔습니다.',
        'business_info_title' => '사업자 정보',
        'business_number' => '사업자등록번호',
        'business_number_value' => '322-82-00643',
        'back_to_home' => '홈으로 돌아가기',
        'contact_us' => '연락처',
        'copyright' => '© 2025 코스모 재단. 모든 권리 보유.',
        'quick_links' => '빠른 링크',
        'foundation_address' => '재단 주소',
        'address_line' => '서울 영등포구 문래로 187, 5층'
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
    <meta name="description" content="KOSMO Foundation is dedicated to improving the lives of students and athletes through medical support, education, and community programs.">
    <meta name="keywords" content="foundation, non-profit, education, medical support, students, athletes">
    
    <!-- Open Graph meta tags for social sharing -->
    <meta property="og:title" content="<?php echo $t['site_title']; ?>">
    <meta property="og:description" content="<?php echo $t['about_description']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://www.kosmo.or.kr/about.php">
    <meta property="og:image" content="https://images.unsplash.com/photo-1571902943202-507ec2618e8f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80">
    
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
                    <li><a href="about.php?lang=<?php echo $locale; ?>" class="text-primary font-semibold px-2 py-1"><?php echo $t['about']; ?></a></li>
                    <li><a href="programs.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['programs']; ?></a></li>
                    <li><a href="gallery.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['gallery']; ?></a></li>
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

    <!-- About Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-center mb-12"><?php echo $t['about_title']; ?></h1>
                
                <div class="prose max-w-none">
                    <p class="text-lg text-gray-700 mb-8"><?php echo $t['about_description']; ?></p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-xl font-semibold mb-4"><?php echo $t['mission_title']; ?></h3>
                            <p class="text-gray-700"><?php echo $t['mission_description']; ?></p>
                        </div>
                        
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <h3 class="text-xl font-semibold mb-4"><?php echo $t['vision_title']; ?></h3>
                            <p class="text-gray-700"><?php echo $t['vision_description']; ?></p>
                        </div>
                    </div>
                    
                    <!-- Director Section -->
                    <div class="mb-12">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['director_title']; ?></h3>
                        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 bg-gray-50 p-6 rounded-lg shadow-sm">
                            <div class="w-full md:w-1/3">
                                <div class="rounded-lg overflow-hidden shadow-md bg-white flex items-center justify-center h-80">
                                    <!-- Director image using the uploaded file -->
                                    <img src="assets/images/kosmo/director/director.jpg" alt="<?php echo $t['director_name']; ?>" class="object-cover h-full w-full">
                                </div>
                            </div>
                            <div class="w-full md:w-2/3">
                                <h4 class="text-lg font-medium mb-1"><?php echo $t['director_name']; ?></h4>
                                <p class="text-primary font-medium mb-4"><?php echo $t['director_position']; ?></p>
                                <p class="text-gray-700 mb-4">
                                    <?php echo $t['director_description_1']; ?>
                                </p>
                                <p class="text-gray-700 mb-4">
                                    <?php echo $t['director_description_2']; ?>
                                </p>
                                <p class="text-gray-700 mb-4">
                                    <?php echo $t['director_description_3']; ?>
                                </p>
                                <p class="text-gray-700">
                                    <?php echo $t['director_description_4']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Foundation History Section -->
                    <div class="mb-12">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['foundation_history']; ?></h3>
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <img src="http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png" alt="Foundation History Timeline" class="w-full rounded-lg mb-4">
                            <p class="text-gray-700"><?php echo $t['history_description']; ?></p>
                        </div>
                    </div>
                    
                    <!-- Organization Structure Section -->
                    <div class="mb-12">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['organization_title']; ?></h3>
                        <p class="text-gray-700 mb-6"><?php echo $t['organization_description']; ?></p>
                        
                        <!-- Organization Chart -->
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm overflow-hidden mb-6">
                            <div class="flex flex-col items-center">
                                <div class="bg-primary text-white px-6 py-3 rounded-lg mb-4 text-center w-64 shadow-sm">
                                    <?php echo $t['org_board_directors']; ?>
                                </div>
                                <div class="h-8 w-px bg-gray-400"></div>
                                <div class="bg-secondary text-white px-6 py-3 rounded-lg mb-4 text-center w-64 shadow-sm">
                                    <?php echo $t['org_secretary_general']; ?>
                                </div>
                                <div class="h-8 w-px bg-gray-400"></div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
                                    <div class="bg-gray-200 px-4 py-2 rounded-lg text-center shadow-sm">
                                        <?php echo $t['org_program_division']; ?>
                                    </div>
                                    <div class="bg-gray-200 px-4 py-2 rounded-lg text-center shadow-sm">
                                        <?php echo $t['org_planning_division']; ?>
                                    </div>
                                    <div class="bg-gray-200 px-4 py-2 rounded-lg text-center shadow-sm">
                                        <?php echo $t['org_pr_finance_division']; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Display the full organization chart image -->
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm mb-6">
                            <img src="http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png" alt="KOSMO Foundation Organization Chart" class="w-full rounded-lg">
                        </div>
                    </div>
                    
                    <!-- Business Information Section -->
                    <div class="mb-12">
                        <h3 class="text-xl font-semibold mb-4"><?php echo $t['business_info_title']; ?></h3>
                        <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t['business_number']; ?></p>
                                    <p class="font-medium"><?php echo $t['business_number_value']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600 mb-1"><?php echo $t['foundation_address']; ?></p>
                                    <p class="font-medium"><?php echo $t['address_line']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <a href="index.php?lang=<?php echo $locale; ?>" class="inline-block bg-primary hover:bg-opacity-90 text-white px-6 py-3 rounded-md font-semibold">
                            <?php echo $t['back_to_home']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 py-12 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">KOSMO Foundation</h3>
                    <p class="text-gray-400"><?php echo $t['about_description']; ?></p>
                    <p class="text-gray-400 mt-4"><?php echo $t['foundation_address']; ?>:</p>
                    <p class="text-gray-400"><?php echo $t['address_line']; ?></p>
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
        // Simple JavaScript for smooth scrolling to anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>