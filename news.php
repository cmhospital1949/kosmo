<?php
require_once __DIR__ . '/config.php';
// Specify default language and set language cookie if not present
if (!isset($_COOKIE['language'])) {
    setcookie('language', 'en', time() + (86400 * 30), "/");
    $language = 'en';
} else {
    $language = $_COOKIE['language'];
}

// Database connection
function connect_db() {
    global $host, $dbname, $username, $password;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

$pdo = connect_db();

// Get news categories
$categories = [];
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM news_categories ORDER BY name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}

// Get current page number from query string
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 6; // Number of posts per page
$offset = ($page - 1) * $perPage;

// Get category filter from query string
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';

// Get news posts
$posts = [];
$totalPosts = 0;
try {
    if ($pdo) {
        // Build query based on filters
        $whereClause = " WHERE published = 1";
        $params = [];
        
        if (!empty($categoryFilter)) {
            $whereClause .= " AND category = ?";
            $params[] = $categoryFilter;
        }
        
        // Count total posts for pagination
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM news_posts" . $whereClause);
        if (!empty($params)) {
            $countStmt->execute($params);
        } else {
            $countStmt->execute();
        }
        $totalPosts = $countStmt->fetchColumn();
        
        // Get posts for current page
        $stmt = $pdo->prepare("SELECT * FROM news_posts" . $whereClause . " ORDER BY publish_date DESC LIMIT ?, ?");
        $params[] = $offset;
        $params[] = $perPage;
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching news posts: " . $e->getMessage());
}

// Calculate total pages for pagination
$totalPages = ceil($totalPosts / $perPage);

// Translations
$translations = [
    'en' => [
        'page_title' => 'News & Updates - KOSMO Foundation',
        'meta_description' => 'Latest news, events, and press releases from the Korean Sports Medicine Support Foundation.',
        'page_heading' => 'News & Updates',
        'filter_all' => 'All',
        'filter_news' => 'News',
        'filter_events' => 'Events',
        'filter_press' => 'Press Releases',
        'read_more' => 'Read More',
        'pagination_prev' => 'Previous',
        'pagination_next' => 'Next',
        'posted_on' => 'Posted on',
        'category' => 'Category',
        'no_posts' => 'No posts found.',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'gallery' => 'Gallery',
        'donate' => 'Donate',
        'news' => 'News',
        'contact_us' => 'Contact Us',
        'donate_now' => 'Donate Now',
    ],
    'ko' => [
        'page_title' => '뉴스 & 업데이트 - 한국스포츠의료지원재단',
        'meta_description' => '한국스포츠의료지원재단의 최신 뉴스, 이벤트 및 보도자료.',
        'page_heading' => '뉴스 & 업데이트',
        'filter_all' => '전체',
        'filter_news' => '뉴스',
        'filter_events' => '이벤트',
        'filter_press' => '보도자료',
        'read_more' => '더 보기',
        'pagination_prev' => '이전',
        'pagination_next' => '다음',
        'posted_on' => '게시일',
        'category' => '카테고리',
        'no_posts' => '게시물이 없습니다.',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'gallery' => '갤러리',
        'donate' => '후원하기',
        'news' => '뉴스',
        'contact_us' => '문의하기',
        'donate_now' => '지금 후원하기',
    ]
];

// Function to get translation
function t($key, $lang, $translations) {
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $translations['en'][$key];
}

// Function to format date
function formatDate($date, $lang) {
    if ($lang == 'ko') {
        return date('Y년 m월 d일', strtotime($date));
    } else {
        return date('F j, Y', strtotime($date));
    }
}

// Function to get category name
function getCategoryName($slug, $categories, $lang) {
    foreach ($categories as $category) {
        if ($category['slug'] == $slug) {
            return $lang == 'ko' ? $category['ko_name'] : $category['name'];
        }
    }
    return $slug;
}

// Generate pagination URL
function getPaginationUrl($page, $category) {
    $url = 'news.php?page=' . $page;
    if (!empty($category)) {
        $url .= '&category=' . $category;
    }
    return $url;
}

// Get category slug from category name
function getCategorySlugFromName($name, $categories) {
    foreach ($categories as $category) {
        if ($category['name'] == $name || $category['ko_name'] == $name) {
            return $category['slug'];
        }
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo t('page_title', $language, $translations); ?></title>
    <meta name="description" content="<?php echo t('meta_description', $language, $translations); ?>">
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50 text-gray-800">
    <header class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="index.php" class="text-2xl font-bold text-blue-600">KOSMO</a>
            </div>
            <nav class="hidden md:flex space-x-6">
                <a href="index.php" class="hover:text-blue-600"><?php echo t('home', $language, $translations); ?></a>
                <a href="about.php" class="hover:text-blue-600"><?php echo t('about', $language, $translations); ?></a>
                <a href="programs.php" class="hover:text-blue-600"><?php echo t('programs', $language, $translations); ?></a>
                <a href="gallery.php" class="hover:text-blue-600"><?php echo t('gallery', $language, $translations); ?></a>
                <a href="news.php" class="hover:text-blue-600 font-semibold"><?php echo t('news', $language, $translations); ?></a>
                <a href="donate.php" class="hover:text-blue-600"><?php echo t('donate', $language, $translations); ?></a>
            </nav>
            <div class="flex items-center space-x-4">
                <div>
                    <a href="#" onclick="changeLanguage('<?php echo $language == 'en' ? 'ko' : 'en'; ?>')" class="text-sm font-medium">
                        <?php echo $language == 'en' ? '한국어' : 'English'; ?>
                    </a>
                </div>
                <a href="donate.php" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hidden md:block">
                    <?php echo t('donate_now', $language, $translations); ?>
                </a>
                <button type="button" class="md:hidden" id="menu-toggle">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
        <div class="md:hidden hidden bg-white w-full" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php" class="block px-3 py-2 rounded-md"><?php echo t('home', $language, $translations); ?></a>
                <a href="about.php" class="block px-3 py-2 rounded-md"><?php echo t('about', $language, $translations); ?></a>
                <a href="programs.php" class="block px-3 py-2 rounded-md"><?php echo t('programs', $language, $translations); ?></a>
                <a href="gallery.php" class="block px-3 py-2 rounded-md"><?php echo t('gallery', $language, $translations); ?></a>
                <a href="news.php" class="block px-3 py-2 rounded-md font-semibold bg-gray-100"><?php echo t('news', $language, $translations); ?></a>
                <a href="donate.php" class="block px-3 py-2 rounded-md"><?php echo t('donate', $language, $translations); ?></a>
                <a href="donate.php" class="block px-3 py-2 rounded-md bg-blue-600 text-white text-center mt-4">
                    <?php echo t('donate_now', $language, $translations); ?>
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div class="mb-12 text-center">
            <h1 class="text-4xl font-bold mb-4"><?php echo t('page_heading', $language, $translations); ?></h1>
        </div>
        
        <!-- Category Filter -->
        <div class="mb-8 flex flex-wrap justify-center">
            <a href="news.php" class="px-4 py-2 m-1 text-sm font-medium rounded-md <?php echo empty($categoryFilter) ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                <?php echo t('filter_all', $language, $translations); ?>
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="news.php?category=<?php echo $category['slug']; ?>" class="px-4 py-2 m-1 text-sm font-medium rounded-md <?php echo $categoryFilter == $category['slug'] ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300'; ?>">
                    <?php echo $language == 'ko' ? $category['ko_name'] : $category['name']; ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($posts) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($posts as $post): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <?php if ($post['cover_image']): ?>
                            <img src="<?php echo $post['cover_image']; ?>" alt="<?php echo $language == 'ko' ? $post['ko_title'] : $post['title']; ?>" class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <div class="text-sm text-gray-500 mb-2">
                                <span><?php echo t('posted_on', $language, $translations); ?>: <?php echo formatDate($post['publish_date'], $language); ?></span>
                                <span class="mx-2">•</span>
                                <span><?php echo t('category', $language, $translations); ?>: <?php echo getCategoryName($post['category'], $categories, $language); ?></span>
                            </div>
                            <h2 class="text-xl font-semibold mb-3"><?php echo $language == 'ko' ? $post['ko_title'] : $post['title']; ?></h2>
                            <p class="text-gray-600 mb-4"><?php echo $language == 'ko' ? $post['ko_excerpt'] : $post['excerpt']; ?></p>
                            <a href="news-post.php?slug=<?php echo $post['slug']; ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                <?php echo t('read_more', $language, $translations); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="mt-12 flex justify-center">
                    <div class="inline-flex">
                        <?php if ($page > 1): ?>
                            <a href="<?php echo getPaginationUrl($page - 1, $categoryFilter); ?>" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 rounded-l-md hover:bg-gray-100">
                                <?php echo t('pagination_prev', $language, $translations); ?>
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-medium bg-gray-100 border border-gray-300 rounded-l-md text-gray-400">
                                <?php echo t('pagination_prev', $language, $translations); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="px-4 py-2 text-sm font-medium bg-blue-600 text-white border border-gray-300">
                                    <?php echo $i; ?>
                                </span>
                            <?php else: ?>
                                <a href="<?php echo getPaginationUrl($i, $categoryFilter); ?>" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 hover:bg-gray-100">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="<?php echo getPaginationUrl($page + 1, $categoryFilter); ?>" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 rounded-r-md hover:bg-gray-100">
                                <?php echo t('pagination_next', $language, $translations); ?>
                            </a>
                        <?php else: ?>
                            <span class="px-4 py-2 text-sm font-medium bg-gray-100 border border-gray-300 rounded-r-md text-gray-400">
                                <?php echo t('pagination_next', $language, $translations); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-12">
                <p class="text-lg text-gray-500"><?php echo t('no_posts', $language, $translations); ?></p>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-semibold mb-4">KOSMO</h3>
                    <p class="text-gray-300">
                        <?php echo $language == 'en' ? 'Korean Sports Medicine Support Foundation' : '한국스포츠의료지원재단'; ?>
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4">
                        <?php echo $language == 'en' ? 'Programs' : '프로그램'; ?>
                    </h3>
                    <ul class="space-y-2">
                        <li><a href="program.php?slug=medical-support" class="text-gray-300 hover:text-white">
                            <?php echo $language == 'en' ? 'Medical Support for Students & Student-Athletes' : '학생·학생선수 의료 지원'; ?>
                        </a></li>
                        <li><a href="program.php?slug=cultural-arts" class="text-gray-300 hover:text-white">
                            <?php echo $language == 'en' ? 'Cultural & Arts Education/Events' : '문화·예술 교육 및 행사'; ?>
                        </a></li>
                        <li><a href="program.php?slug=leadership" class="text-gray-300 hover:text-white">
                            <?php echo $language == 'en' ? 'Personal Development & Leadership Training' : '자기개발·리더십 교육'; ?>
                        </a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4"><?php echo t('contact_us', $language, $translations); ?></h3>
                    <p class="text-gray-300">
                        <?php echo $language == 'en' ? '5th Floor, 187 Mullae-ro, Yeongdeungpo-gu, Seoul, Republic of Korea' : '서울 영등포구 문래로 187, 5층'; ?><br>
                        +82-2-1234-5678<br>
                        goodwill@kosmo.or.kr
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-4"><?php echo t('donate', $language, $translations); ?></h3>
                    <p class="text-gray-300 mb-4">
                        <?php echo $language == 'en' ? 'Support our mission to provide medical support and education to athletes.' : '운동선수들에게 의료 지원과 교육을 제공하는 우리의 사명을 지원해주세요.'; ?>
                    </p>
                    <a href="donate.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md inline-block">
                        <?php echo t('donate_now', $language, $translations); ?>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 <?php echo $language == 'en' ? 'Korean Sports Medicine Support Foundation (KOSMO). All Rights Reserved.' : '한국스포츠의료지원재단 (KOSMO). 모든 권리 보유.'; ?></p>
                <p class="mt-2"><a href="admin.php" class="hover:text-white">Admin</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        
        menuToggle.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Language switcher
        function changeLanguage(lang) {
            document.cookie = "language=" + lang + "; path=/; max-age=" + 60*60*24*30;
            window.location.reload();
        }
    </script>
</body>
</html>