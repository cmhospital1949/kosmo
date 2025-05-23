<?php
// Database connection
function connect_db() {
    $host = 'db.kosmo.or.kr';
    $dbname = 'dbbestluck';
    $username = 'bestluck';
    $password = 'cmhospital1949!';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Language settings
$locale = $_GET['lang'] ?? 'en';
$isKorean = $locale === 'ko';

// Simple translations
$translations = [
    'en' => [
        'site_title' => 'Gallery - KOSMO Foundation',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'contact' => 'Contact',
        'donate' => 'Donate',
        'gallery_title' => 'Gallery',
        'gallery_description' => 'View our collection of images from our programs, events, and activities.',
        'foundation_events' => 'Foundation Events',
        'sports_medicine' => 'Sports Medicine',
        'athlete_support' => 'Athlete Support',
        'seminars' => 'Seminars & Events',
        'back_to_home' => 'Back to Home',
        'contact_us' => 'Contact Us',
        'copyright' => '© 2025 KOSMO Foundation. All rights reserved.',
        'quick_links' => 'Quick Links',
        'foundation_address' => 'Foundation Address',
        'address_line' => '5th Floor, 187, Mullae-ro, Yeongdeungpo-gu, Seoul',
        'view_image' => 'View Image',
        'view_more_images' => 'View More Images',
        'foundation_history' => 'Foundation History',
        'foundation_activities' => 'Foundation Activities'
    ],
    'ko' => [
        'site_title' => '갤러리 - 코스모 재단',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'contact' => '연락처',
        'donate' => '후원하기',
        'gallery_title' => '갤러리',
        'gallery_description' => '프로그램, 이벤트 및 활동의 이미지 모음을 확인하세요.',
        'foundation_events' => '재단 행사',
        'sports_medicine' => '스포츠 의학',
        'athlete_support' => '선수 지원',
        'seminars' => '세미나 및 행사',
        'back_to_home' => '홈으로 돌아가기',
        'contact_us' => '연락처',
        'copyright' => '© 2025 코스모 재단. 모든 권리 보유.',
        'quick_links' => '빠른 링크',
        'foundation_address' => '재단 주소',
        'address_line' => '서울 영등포구 문래로 187, 5층',
        'view_image' => '이미지 보기',
        'view_more_images' => '더 많은 이미지 보기',
        'foundation_history' => '재단 역사',
        'foundation_activities' => '재단 활동'
    ]
];

// Get the current language translations
$t = $translations[$locale];

// Get gallery categories from database
$pdo = connect_db();
$categories = [];
$galleryImages = [];

if ($pdo) {
    try {
        // Fetch gallery categories
        $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY id");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no categories exist in the database, use the default hardcoded ones
        if (empty($categories)) {
            // Create default categories
            $defaultCategories = [
                ['Foundation History', '재단 역사', 'Photos from our foundation history', '재단 역사의 사진'],
                ['Sports Medicine', '스포츠 의학', 'Sports medicine practices and facilities', '스포츠 의학 사진'],
                ['Athlete Support', '선수 지원', 'Our athlete support programs', '선수 지원 프로그램'],
                ['Seminars & Events', '세미나 및 행사', 'Seminars and events organized by the foundation', '재단이 주최한 세미나 및 행사']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO gallery_categories (name, ko_name, description, ko_description) VALUES (?, ?, ?, ?)");
            
            foreach ($defaultCategories as $category) {
                $stmt->execute($category);
            }
            
            // Fetch the newly created categories
            $stmt = $pdo->query("SELECT * FROM gallery_categories ORDER BY id");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Fetch images for each category
        foreach ($categories as $category) {
            $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE category_id = ?");
            $stmt->execute([$category['id']]);
            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Store the category images
            $galleryImages[$category['id']] = $images;
        }
        
        // If no images are found in the database for a category, set up default ones
        // This ensures we always have images to display
        $defaultGalleryImages = [
            '8' => [ // Foundation History
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png',
                    'alt' => 'KOSMO Foundation History Timeline',
                    'caption' => 'Foundation establishment timeline'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/조직도4_230316.png',
                    'alt' => 'KOSMO Foundation Organization Chart',
                    'caption' => 'Foundation organization structure'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/진천선수촌-개촌식.jpg',
                    'alt' => 'Jincheon Athletes Village Opening Ceremony',
                    'caption' => 'Jincheon National Athletes Village opening ceremony'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육회-기념식-참가.jpg',
                    'alt' => 'Korean Sports Association Ceremony',
                    'caption' => 'Korean Sports Association 98th Anniversary Ceremony'
                ]
            ],
            '9' => [ // Sports Medicine
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3319-scaled.jpg',
                    'alt' => 'Sports Medicine Practice',
                    'caption' => 'Sports medicine professionals at work'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3354-scaled.jpg',
                    'alt' => 'Medical Support Team',
                    'caption' => 'Medical support team for athletes'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3258-scaled.jpg',
                    'alt' => 'Medical Equipment',
                    'caption' => 'State-of-the-art medical equipment'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/IMG_3370-scaled.jpg',
                    'alt' => 'Medical Consultation',
                    'caption' => 'Medical consultation for athletes'
                ]
            ],
            '10' => [ // Athlete Support
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/한일배구.jpg',
                    'alt' => 'Korea-Japan Volleyball',
                    'caption' => 'Korea-Japan volleyball match'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임.png',
                    'alt' => 'Jakarta Asian Games',
                    'caption' => 'Jakarta Asian Games'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/배구-국대-팀닥터.jpg',
                    'alt' => 'National Volleyball Team Doctor',
                    'caption' => 'National volleyball team with medical support'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/자카르타-아시안게임-2.png',
                    'alt' => 'Jakarta Asian Games 2',
                    'caption' => 'Medical support at Jakarta Asian Games'
                ]
            ],
            '11' => [ // Seminars & Events
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식2.jpg',
                    'alt' => 'Opening Ceremony 2',
                    'caption' => 'Foundation seminar and opening ceremony'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/개촌식3.jpg',
                    'alt' => 'Opening Ceremony 3',
                    'caption' => 'Foundation inauguration event'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/대한체육-기념식-2.jpg',
                    'alt' => 'Korean Sports Association Ceremony 2',
                    'caption' => 'Sports association anniversary celebration'
                ],
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/170586_151041_2238.jpg',
                    'alt' => 'Medical Conference',
                    'caption' => 'Sports medicine conference and seminar'
                ]
            ]
        ];
        
        // Look for empty categories and apply default images if needed
        foreach ($categories as $category) {
            $categoryId = (string)$category['id'];
            
            // If this category has no images and we have defaults for it
            if (empty($galleryImages[$categoryId]) && isset($defaultGalleryImages[$categoryId])) {
                // Convert default images to database format and import them
                foreach ($defaultGalleryImages[$categoryId] as $defaultImage) {
                    $stmt = $pdo->prepare("INSERT INTO gallery_images (category_id, title, ko_title, description, ko_description, filename) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $category['id'],
                        $defaultImage['alt'],
                        $defaultImage['alt'],
                        $defaultImage['caption'],
                        $defaultImage['caption'],
                        $defaultImage['src']
                    ]);
                }
                
                // Fetch the newly added images
                $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE category_id = ?");
                $stmt->execute([$category['id']]);
                $galleryImages[$categoryId] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        
    } catch (PDOException $e) {
        error_log("Gallery database error: " . $e->getMessage());
        // Fall back to default arrays if database connection fails
        $galleryImages = [
            '1' => [
                [
                    'src' => 'http://www.kosmo.or.kr/wp-content/uploads/2023/03/히스토리1-1024x586.png',
                    'alt' => 'KOSMO Foundation History Timeline',
                    'caption' => 'Foundation establishment timeline'
                ],
                // ... more images ...
            ],
            // ... more categories ...
        ];
    }
}

// Helper function to map database images to gallery format
function mapDatabaseImages($images) {
    $result = [];
    foreach ($images as $image) {
        $result[] = [
            'src' => $image['filename'],
            'alt' => $isKorean && !empty($image['ko_title']) ? $image['ko_title'] : $image['title'],
            'caption' => $isKorean && !empty($image['ko_description']) ? $image['ko_description'] : $image['description']
        ];
    }
    return $result;
}

// Get category names for display
$categoryNames = [];
$categoryKoNames = [];
foreach ($categories as $category) {
    $categoryNames[$category['id']] = $category['name'];
    $categoryKoNames[$category['id']] = $category['ko_name'];
}
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
    <meta name="description" content="View the gallery of KOSMO Foundation, featuring images from our programs, events, and activities in sports medicine and athlete support.">
    <meta name="keywords" content="gallery, foundation, non-profit, sports medicine, athletes, events, programs">
    
    <!-- Open Graph meta tags for social sharing -->
    <meta property="og:title" content="<?php echo $t['site_title']; ?>">
    <meta property="og:description" content="<?php echo $t['gallery_description']; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://www.kosmo.or.kr/gallery.php">
    <meta property="og:image" content="https://images.unsplash.com/photo-1571902943202-507ec2618e8f">
    
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
        
        .gallery-item {
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.02);
        }
        
        .lightbox {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .lightbox.active {
            display: flex;
        }
        
        .lightbox-img {
            max-width: 90%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .lightbox-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            color: white;
            font-size: 2rem;
            cursor: pointer;
        }
        
        .lightbox-caption {
            position: absolute;
            bottom: 1rem;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            padding: 0.5rem;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .gallery-section:not(:last-child) {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
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
                    <li><a href="about.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['about']; ?></a></li>
                    <li><a href="programs.php?lang=<?php echo $locale; ?>" class="hover:text-primary px-2 py-1"><?php echo $t['programs']; ?></a></li>
                    <li><a href="gallery.php?lang=<?php echo $locale; ?>" class="text-primary font-semibold px-2 py-1"><?php echo $t['gallery_title']; ?></a></li>
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

    <!-- Gallery Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <h1 class="text-3xl font-bold text-center mb-6"><?php echo $t['gallery_title']; ?></h1>
            <p class="text-lg text-gray-700 text-center mb-12 max-w-2xl mx-auto"><?php echo $t['gallery_description']; ?></p>
            
            <?php foreach ($categories as $category): ?>
                <?php
                    $categoryId = $category['id'];
                    $categoryName = $isKorean ? $category['ko_name'] : $category['name'];
                    $images = $galleryImages[$categoryId] ?? [];
                ?>
                <div class="gallery-section mb-16">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-semibold"><?php echo $categoryName; ?></h2>
                        <button 
                            class="bg-primary text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-opacity-90 transition-all"
                            onclick="showCategoryGallery('<?php echo $categoryId; ?>')"
                        >
                            <?php echo $t['view_more_images']; ?>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php 
                        // Display only the first 3 images
                        $displayCount = min(3, count($images));
                        for($i = 0; $i < $displayCount; $i++): 
                            $image = $images[$i];
                            $title = $isKorean && !empty($image['ko_title']) ? $image['ko_title'] : $image['title'];
                            $description = $isKorean && !empty($image['ko_description']) ? $image['ko_description'] : $image['description'];
                        ?>
                        <div class="gallery-item bg-white rounded-lg shadow-md overflow-hidden h-64">
                            <img 
                                src="<?php echo $image['filename']; ?>" 
                                alt="<?php echo $title; ?>" 
                                class="w-full h-48 object-cover cursor-pointer" 
                                onclick="openLightbox('<?php echo $categoryId; ?>', <?php echo $i; ?>)"
                            >
                            <div class="p-2">
                                <p class="text-sm text-gray-700 truncate"><?php echo $description; ?></p>
                            </div>
                        </div>
                        <?php endfor; ?>
                        
                        <?php if (empty($images)): ?>
                        <div class="col-span-3 text-center py-8">
                            <p class="text-gray-600">No images available in this category.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="text-center mt-8">
                <a href="index.php?lang=<?php echo $locale; ?>" class="inline-block bg-primary hover:bg-opacity-90 text-white px-6 py-3 rounded-md font-semibold">
                    <?php echo $t['back_to_home']; ?>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Category Modal -->
    <div id="category-modal" class="lightbox">
        <div class="lightbox-close" onclick="closeCategoryModal()">&times;</div>
        <div class="bg-white rounded-lg p-6 w-11/12 md:w-3/4 lg:w-2/3 max-h-[90vh] overflow-y-auto">
            <h3 id="category-title" class="text-2xl font-semibold mb-6 text-center"></h3>
            <div id="category-images" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <!-- Images will be populated here via JavaScript -->
            </div>
        </div>
    </div>
    
    <!-- Lightbox -->
    <div id="lightbox" class="lightbox">
        <div class="lightbox-close" onclick="closeLightbox()">&times;</div>
        <img id="lightbox-img" class="lightbox-img" src="" alt="">
        <p id="lightbox-caption" class="lightbox-caption"></p>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 py-12 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">KOSMO Foundation</h3>
                    <p class="text-gray-400"><?php echo $t['gallery_description']; ?></p>
                    <p class="text-gray-400 mt-4"><?php echo $t['foundation_address']; ?>:</p>
                    <p class="text-gray-400"><?php echo $t['address_line']; ?></p>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4"><?php echo $t['quick_links']; ?></h3>
                    <ul class="space-y-2">
                        <li><a href="index.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['home']; ?></a></li>
                        <li><a href="about.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['about']; ?></a></li>
                        <li><a href="programs.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['programs']; ?></a></li>
                        <li><a href="gallery.php?lang=<?php echo $locale; ?>" class="text-gray-400 hover:text-white"><?php echo $t['gallery_title']; ?></a></li>
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
        // Gallery images data - prepared from database
        const galleryData = <?php echo json_encode($galleryImages); ?>;
        const translations = <?php echo json_encode($t); ?>;
        const categoryNames = <?php echo json_encode($isKorean ? $categoryKoNames : $categoryNames); ?>;
        const isKorean = <?php echo json_encode($isKorean); ?>;
        
        // Lightbox elements
        const lightbox = document.getElementById('lightbox');
        const lightboxImg = document.getElementById('lightbox-img');
        const lightboxCaption = document.getElementById('lightbox-caption');
        
        // Category Modal elements
        const categoryModal = document.getElementById('category-modal');
        const categoryTitle = document.getElementById('category-title');
        const categoryImages = document.getElementById('category-images');
        
        // Gallery Lightbox functions
        function openLightbox(category, index) {
            const images = galleryData[category] || [];
            if (images.length === 0 || !images[index]) return;
            
            const image = images[index];
            lightboxImg.src = image.filename;
            lightboxImg.alt = isKorean && image.ko_title ? image.ko_title : image.title;
            lightboxCaption.textContent = isKorean && image.ko_description ? image.ko_description : image.description;
            lightbox.classList.add('active');
            
            // Prevent body scrolling when lightbox is open
            document.body.style.overflow = 'hidden';
        }
        
        function closeLightbox() {
            lightbox.classList.remove('active');
            
            // Re-enable body scrolling
            document.body.style.overflow = 'auto';
        }
        
        // Category Modal functions
        function showCategoryGallery(category) {
            // Set the title based on category
            categoryTitle.textContent = categoryNames[category] || '';
            
            // Clear previous images
            categoryImages.innerHTML = '';
            
            // Get images for this category
            const images = galleryData[category] || [];
            
            if (images.length === 0) {
                const noImagesDiv = document.createElement('div');
                noImagesDiv.className = 'col-span-2 text-center py-8';
                noImagesDiv.innerHTML = '<p class="text-gray-600">No images available in this category.</p>';
                categoryImages.appendChild(noImagesDiv);
            } else {
                // Add all images from the category
                images.forEach((image, index) => {
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'gallery-item bg-white rounded-lg shadow-md overflow-hidden';
                    
                    const title = isKorean && image.ko_title ? image.ko_title : image.title;
                    const description = isKorean && image.ko_description ? image.ko_description : image.description;
                    
                    imageDiv.innerHTML = `
                        <img 
                            src="${image.filename}" 
                            alt="${title}" 
                            class="w-full h-64 object-cover cursor-pointer" 
                            onclick="openLightbox('${category}', ${index})"
                        >
                        <div class="p-4">
                            <p class="text-gray-700">${description}</p>
                            <button class="mt-2 text-primary font-medium hover:underline" onclick="openLightbox('${category}', ${index})">
                                ${translations.view_image}
                            </button>
                        </div>
                    `;
                    
                    categoryImages.appendChild(imageDiv);
                });
            }
            
            // Show the category modal
            categoryModal.classList.add('active');
            
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
        }
        
        function closeCategoryModal() {
            categoryModal.classList.remove('active');
            
            // Re-enable body scrolling
            document.body.style.overflow = 'auto';
        }
        
        // Close lightbox when clicking outside the image
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
        
        // Close category modal when clicking outside the content
        categoryModal.addEventListener('click', function(e) {
            if (e.target === categoryModal) {
                closeCategoryModal();
            }
        });
        
        // Close lightbox and category modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (lightbox.classList.contains('active')) {
                    closeLightbox();
                }
                if (categoryModal.classList.contains('active')) {
                    closeCategoryModal();
                }
            }
        });
    </script>
</body>
</html>