<?php
require_once __DIR__ . '/config.php';
// Specify default language and set language cookie if not present
if (!isset($_COOKIE['language'])) {
    setcookie('language', 'en', time() + (86400 * 30), "/");
    $language = 'en';
} else {
    $language = $_COOKIE['language'];
}

// Get the slug from the URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// If no slug is provided, redirect to the news list
if (empty($slug)) {
    header('Location: news.php');
    exit;
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

// Get the news post
$post = null;
try {
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM news_posts WHERE slug = ? AND published = 1");
        $stmt->execute([$slug]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If post not found, redirect to news list
        if (!$post) {
            header('Location: news.php');
            exit;
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching news post: " . $e->getMessage());
}

// Get related posts (3 posts from the same category, excluding current post)
$relatedPosts = [];
try {
    if ($pdo && $post) {
        $stmt = $pdo->prepare("SELECT * FROM news_posts WHERE category = ? AND id != ? AND published = 1 ORDER BY publish_date DESC LIMIT 3");
        $stmt->execute([$post['category'], $post['id']]);
        $relatedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching related posts: " . $e->getMessage());
}

// Get category information
$category = null;
try {
    if ($pdo && $post) {
        $stmt = $pdo->prepare("SELECT * FROM news_categories WHERE slug = ?");
        $stmt->execute([$post['category']]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching category: " . $e->getMessage());
}

// Translations
$translations = [
    'en' => [
        'page_title_format' => '%s - KOSMO Foundation',
        'meta_description_format' => '%s - Read the latest from the Korean Sports Medicine Support Foundation.',
        'posted_on' => 'Posted on',
        'category' => 'Category',
        'author' => 'Author',
        'related_posts' => 'Related Posts',
        'read_more' => 'Read More',
        'back_to_news' => 'Back to News',
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
        'page_title_format' => '%s - 한국스포츠의료지원재단',
        'meta_description_format' => '%s - 한국스포츠의료지원재단의 최신 소식을 읽어보세요.',
        'posted_on' => '게시일',
        'category' => '카테고리',
        'author' => '작성자',
        'related_posts' => '관련 게시물',
        'read_more' => '더 보기',
        'back_to_news' => '뉴스 목록으로',
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

// Get page title and meta description
$pageTitle = sprintf(t('page_title_format', $language, $translations), $language == 'ko' ? $post['ko_title'] : $post['title']);
$metaDescription = sprintf(t('meta_description_format', $language, $translations), $language == 'ko' ? $post['ko_excerpt'] : $post['excerpt']);
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Open Graph tags -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDescription; ?>">
    <?php if ($post['cover_image']): ?>
        <meta property="og:image" content="<?php echo $post['cover_image']; ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:type" content="article">
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
        <div class="mb-6">
            <a href="news.php" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                </svg>
                <?php echo t('back_to_news', $language, $translations); ?>
            </a>
        </div>
        
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <?php if ($post['cover_image']): ?>
                <img src="<?php echo $post['cover_image']; ?>" alt="<?php echo $language == 'ko' ? $post['ko_title'] : $post['title']; ?>" class="w-full h-64 md:h-96 object-cover">
            <?php endif; ?>
            
            <div class="p-6 md:p-8">
                <div class="flex flex-wrap text-sm text-gray-500 mb-4">
                    <div class="mr-6 mb-2">
                        <span class="font-medium"><?php echo t('posted_on', $language, $translations); ?>:</span> 
                        <?php echo formatDate($post['publish_date'], $language); ?>
                    </div>
                    <div class="mr-6 mb-2">
                        <span class="font-medium"><?php echo t('category', $language, $translations); ?>:</span> 
                        <?php echo $category ? ($language == 'ko' ? $category['ko_name'] : $category['name']) : ''; ?>
                    </div>
                    <div class="mb-2">
                        <span class="font-medium"><?php echo t('author', $language, $translations); ?>:</span> 
                        <?php echo $post['author']; ?>
                    </div>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-bold mb-6"><?php echo $language == 'ko' ? $post['ko_title'] : $post['title']; ?></h1>
                
                <div class="prose max-w-none">
                    <?php echo $language == 'ko' ? $post['ko_content'] : $post['content']; ?>
                </div>
            </div>
        </div>
        
        <?php if (count($relatedPosts) > 0): ?>
            <div class="mt-12">
                <h2 class="text-2xl font-bold mb-6"><?php echo t('related_posts', $language, $translations); ?></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($relatedPosts as $relatedPost): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <?php if ($relatedPost['cover_image']): ?>
                                <img src="<?php echo $relatedPost['cover_image']; ?>" alt="<?php echo $language == 'ko' ? $relatedPost['ko_title'] : $relatedPost['title']; ?>" class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gray-300 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="p-6">
                                <div class="text-sm text-gray-500 mb-2">
                                    <?php echo formatDate($relatedPost['publish_date'], $language); ?>
                                </div>
                                <h3 class="text-xl font-semibold mb-3"><?php echo $language == 'ko' ? $relatedPost['ko_title'] : $relatedPost['title']; ?></h3>
                                <p class="text-gray-600 mb-4 line-clamp-2"><?php echo $language == 'ko' ? $relatedPost['ko_excerpt'] : $relatedPost['excerpt']; ?></p>
                                <a href="news-post.php?slug=<?php echo $relatedPost['slug']; ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                    <?php echo t('read_more', $language, $translations); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
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