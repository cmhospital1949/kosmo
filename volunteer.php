<?php
// Specify default language and set language cookie if not present
if (!isset($_COOKIE['language'])) {
    setcookie('language', 'en', time() + (86400 * 30), "/");
    $language = 'en';
} else {
    $language = $_COOKIE['language'];
}

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

// Get volunteer interests
$interests = [];
try {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM volunteer_interests ORDER BY name");
        $interests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching interests: " . $e->getMessage());
}

// Process form submission
$formSubmitted = false;
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $selectedInterests = isset($_POST['interests']) ? $_POST['interests'] : [];
    $skills = $_POST['skills'] ?? '';
    $availability = $_POST['availability'] ?? '';
    $background = $_POST['background'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    $formSubmitted = true;
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($selectedInterests)) {
        $formSuccess = false;
        $formError = $language === 'en' ? 'Please fill in all required fields.' : '모든 필수 필드를 작성해 주세요.';
    } else {
        try {
            $pdo = connect_db();
            if ($pdo) {
                // Convert selected interests array to a string
                $interestsString = implode(', ', $selectedInterests);
                
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO volunteers (name, email, phone, interests, skills, availability, background, reason, language) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $email, $phone, $interestsString, $skills, $availability, $background, $reason, $language]);
                
                $formSuccess = true;
            } else {
                $formSuccess = false;
                $formError = $language === 'en' ? 'Database connection failed. Please try again later.' : '데이터베이스 연결에 실패했습니다. 나중에 다시 시도해 주세요.';
            }
        } catch (PDOException $e) {
            $formSuccess = false;
            $formError = $language === 'en' ? 'An error occurred while processing your application. Please try again later.' : '신청서 처리 중 오류가 발생했습니다. 나중에 다시 시도해 주세요.';
            error_log("Volunteer application error: " . $e->getMessage());
        }
    }
}

// Translations
$translations = [
    'en' => [
        'page_title' => 'Volunteer Application - KOSMO Foundation',
        'meta_description' => 'Apply to volunteer with the Korean Sports Medicine Support Foundation and help us support athletes and advance sports medicine in Korea.',
        'page_heading' => 'Volunteer Application',
        'page_subheading' => 'Join our team and make a difference in the lives of athletes and students',
        'form_instructions' => 'Please fill out the form below to apply as a volunteer. Fields marked with * are required.',
        'name_label' => 'Full Name *',
        'email_label' => 'Email Address *',
        'phone_label' => 'Phone Number',
        'interests_label' => 'Areas of Interest *',
        'interests_help' => 'Select one or more areas where you would like to volunteer',
        'skills_label' => 'Skills & Qualifications',
        'skills_help' => 'Please list any relevant skills, qualifications, or certifications',
        'availability_label' => 'Availability',
        'availability_help' => 'When are you available to volunteer? (weekdays, weekends, evenings, etc.)',
        'background_label' => 'Background',
        'background_help' => 'Please briefly describe your background and experience',
        'reason_label' => 'Why do you want to volunteer?',
        'required_fields' => 'Fields marked with * are required',
        'submit_button' => 'Submit Application',
        'volunteer_success_message' => 'Thank you for your volunteer application! We will review your information and contact you soon.',
        'volunteer_error_message' => 'An error occurred while submitting your application. Please try again.',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'gallery' => 'Gallery',
        'news' => 'News',
        'donate' => 'Donate',
        'volunteer' => 'Volunteer',
        'contact_us' => 'Contact Us',
        'donate_now' => 'Donate Now',
        'newsletter_heading' => 'Subscribe to Our Newsletter',
        'newsletter_subheading' => 'Stay updated on our latest news, events, and programs.',
        'newsletter_name_label' => 'Name (optional)',
        'newsletter_email_label' => 'Email Address',
        'subscribe_button' => 'Subscribe',
        'language_label' => 'Preferred Language',
        'english' => 'English',
        'korean' => 'Korean',
        'newsletter_success_message' => 'Thank you for subscribing to our newsletter!',
        'newsletter_error_message' => 'An error occurred. Please try again.',
        'email_required' => 'Please enter a valid email address.',
        'already_subscribed' => 'You are already subscribed to our newsletter.',
        'subscription_reactivated' => 'Your subscription has been reactivated.',
    ],
    'ko' => [
        'page_title' => '자원봉사 신청 - 한국스포츠의료지원재단',
        'meta_description' => '한국스포츠의료지원재단과 함께 자원봉사를 통해 운동선수를 지원하고 한국의 스포츠 의학을 발전시키는 데 도움을 주세요.',
        'page_heading' => '자원봉사 신청',
        'page_subheading' => '우리 팀에 합류하여 운동선수와 학생들의 삶에 변화를 가져오세요',
        'form_instructions' => '자원봉사자로 지원하려면 아래 양식을 작성해 주세요. *가 표시된 필드는 필수 항목입니다.',
        'name_label' => '이름 *',
        'email_label' => '이메일 주소 *',
        'phone_label' => '전화번호',
        'interests_label' => '관심 분야 *',
        'interests_help' => '자원봉사를 하고 싶은 분야를 하나 이상 선택하세요',
        'skills_label' => '기술 및 자격',
        'skills_help' => '관련 기술, 자격 또는 자격증을 나열해 주세요',
        'availability_label' => '가능 시간',
        'availability_help' => '언제 자원봉사가 가능합니까? (평일, 주말, 저녁 등)',
        'background_label' => '배경',
        'background_help' => '귀하의 배경과 경험을 간략히 설명해 주세요',
        'reason_label' => '자원봉사를 원하는 이유',
        'required_fields' => '*가 표시된 필드는 필수 항목입니다',
        'submit_button' => '신청서 제출',
        'volunteer_success_message' => '자원봉사 신청해 주셔서 감사합니다! 귀하의 정보를 검토하고 곧 연락드리겠습니다.',
        'volunteer_error_message' => '신청서 제출 중 오류가 발생했습니다. 다시 시도해 주세요.',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'gallery' => '갤러리',
        'news' => '뉴스',
        'donate' => '후원하기',
        'volunteer' => '자원봉사',
        'contact_us' => '문의하기',
        'donate_now' => '지금 후원하기',
        'newsletter_heading' => '뉴스레터 구독',
        'newsletter_subheading' => '최신 뉴스, 이벤트 및 프로그램에 대한 업데이트를 받아보세요.',
        'newsletter_name_label' => '이름 (선택사항)',
        'newsletter_email_label' => '이메일 주소',
        'subscribe_button' => '구독하기',
        'language_label' => '선호하는 언어',
        'english' => '영어',
        'korean' => '한국어',
        'newsletter_success_message' => '뉴스레터 구독해 주셔서 감사합니다!',
        'newsletter_error_message' => '오류가 발생했습니다. 다시 시도해 주세요.',
        'email_required' => '유효한 이메일 주소를 입력해 주세요.',
        'already_subscribed' => '이미 뉴스레터를 구독하고 계십니다.',
        'subscription_reactivated' => '구독이 다시 활성화되었습니다.'
    ]
];

// Function to get translation
function t($key, $lang, $translations) {
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $translations['en'][$key];
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
                <a href="news.php" class="hover:text-blue-600"><?php echo t('news', $language, $translations); ?></a>
                <a href="volunteer.php" class="hover:text-blue-600 font-semibold"><?php echo t('volunteer', $language, $translations); ?></a>
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
                <a href="news.php" class="block px-3 py-2 rounded-md"><?php echo t('news', $language, $translations); ?></a>
                <a href="volunteer.php" class="block px-3 py-2 rounded-md font-semibold bg-gray-100"><?php echo t('volunteer', $language, $translations); ?></a>
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
            <p class="text-xl text-gray-600"><?php echo t('page_subheading', $language, $translations); ?></p>
        </div>
        
        <?php if ($formSubmitted): ?>
            <?php if ($formSuccess): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
                    <p class="text-center"><?php echo t('volunteer_success_message', $language, $translations); ?></p>
                </div>
                
                <div class="text-center">
                    <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md text-lg font-medium">
                        <?php echo t('home', $language, $translations); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
                    <p class="text-center"><?php echo $formError ?: t('volunteer_error_message', $language, $translations); ?></p>
                </div>
                
                <!-- Show the form again if submission failed -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6 md:p-8">
                        <p class="text-gray-700 mb-6"><?php echo t('form_instructions', $language, $translations); ?></p>
                        
                        <form method="POST" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('name_label', $language, $translations); ?></label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('email_label', $language, $translations); ?></label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('phone_label', $language, $translations); ?></label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('interests_label', $language, $translations); ?></label>
                                <p class="text-xs text-gray-500 mb-2"><?php echo t('interests_help', $language, $translations); ?></p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    <?php foreach ($interests as $interest): ?>
                                        <div class="flex items-start">
                                            <input type="checkbox" id="interest_<?php echo $interest['id']; ?>" name="interests[]" value="<?php echo $language === 'en' ? $interest['name'] : $interest['ko_name']; ?>" <?php echo in_array($language === 'en' ? $interest['name'] : $interest['ko_name'], $selectedInterests) ? 'checked' : ''; ?> class="h-4 w-4 mt-1 text-blue-600 border-gray-300 rounded">
                                            <label for="interest_<?php echo $interest['id']; ?>" class="ml-2 block text-sm text-gray-700">
                                                <?php echo $language === 'en' ? $interest['name'] : $interest['ko_name']; ?>
                                                <?php if ($interest['description'] || $interest['ko_description']): ?>
                                                    <span class="block text-xs text-gray-500"><?php echo $language === 'en' ? $interest['description'] : $interest['ko_description']; ?></span>
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label for="skills" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('skills_label', $language, $translations); ?></label>
                                <p class="text-xs text-gray-500 mb-2"><?php echo t('skills_help', $language, $translations); ?></p>
                                <textarea id="skills" name="skills" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($skills); ?></textarea>
                            </div>
                            
                            <div>
                                <label for="availability" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('availability_label', $language, $translations); ?></label>
                                <p class="text-xs text-gray-500 mb-2"><?php echo t('availability_help', $language, $translations); ?></p>
                                <textarea id="availability" name="availability" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($availability); ?></textarea>
                            </div>
                            
                            <div>
                                <label for="background" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('background_label', $language, $translations); ?></label>
                                <p class="text-xs text-gray-500 mb-2"><?php echo t('background_help', $language, $translations); ?></p>
                                <textarea id="background" name="background" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($background); ?></textarea>
                            </div>
                            
                            <div>
                                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('reason_label', $language, $translations); ?></label>
                                <textarea id="reason" name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($reason); ?></textarea>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500"><?php echo t('required_fields', $language, $translations); ?></p>
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                                    <?php echo t('submit_button', $language, $translations); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6 md:p-8">
                    <p class="text-gray-700 mb-6"><?php echo t('form_instructions', $language, $translations); ?></p>
                    
                    <form method="POST" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('name_label', $language, $translations); ?></label>
                            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('email_label', $language, $translations); ?></label>
                                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('phone_label', $language, $translations); ?></label>
                                <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('interests_label', $language, $translations); ?></label>
                            <p class="text-xs text-gray-500 mb-2"><?php echo t('interests_help', $language, $translations); ?></p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                <?php foreach ($interests as $interest): ?>
                                    <div class="flex items-start">
                                        <input type="checkbox" id="interest_<?php echo $interest['id']; ?>" name="interests[]" value="<?php echo $language === 'en' ? $interest['name'] : $interest['ko_name']; ?>" class="h-4 w-4 mt-1 text-blue-600 border-gray-300 rounded">
                                        <label for="interest_<?php echo $interest['id']; ?>" class="ml-2 block text-sm text-gray-700">
                                            <?php echo $language === 'en' ? $interest['name'] : $interest['ko_name']; ?>
                                            <?php if ($interest['description'] || $interest['ko_description']): ?>
                                                <span class="block text-xs text-gray-500"><?php echo $language === 'en' ? $interest['description'] : $interest['ko_description']; ?></span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div>
                            <label for="skills" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('skills_label', $language, $translations); ?></label>
                            <p class="text-xs text-gray-500 mb-2"><?php echo t('skills_help', $language, $translations); ?></p>
                            <textarea id="skills" name="skills" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        
                        <div>
                            <label for="availability" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('availability_label', $language, $translations); ?></label>
                            <p class="text-xs text-gray-500 mb-2"><?php echo t('availability_help', $language, $translations); ?></p>
                            <textarea id="availability" name="availability" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        
                        <div>
                            <label for="background" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('background_label', $language, $translations); ?></label>
                            <p class="text-xs text-gray-500 mb-2"><?php echo t('background_help', $language, $translations); ?></p>
                            <textarea id="background" name="background" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('reason_label', $language, $translations); ?></label>
                            <textarea id="reason" name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500"><?php echo t('required_fields', $language, $translations); ?></p>
                        </div>
                        
                        <div>
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                                <?php echo t('submit_button', $language, $translations); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Newsletter Sign-up -->
        <div class="mt-12">
            <div class="w-full bg-blue-50 p-6 rounded-lg shadow-md newsletter-form">
                <h3 class="text-xl font-semibold mb-2"><?php echo t('newsletter_heading', $language, $translations); ?></h3>
                <p class="text-gray-600 mb-4"><?php echo t('newsletter_subheading', $language, $translations); ?></p>
                
                <form id="newsletterForm" class="space-y-4">
                    <div>
                        <label for="newsletter_name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('newsletter_name_label', $language, $translations); ?></label>
                        <input type="text" id="newsletter_name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="newsletter_email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('newsletter_email_label', $language, $translations); ?> *</label>
                        <input type="email" id="newsletter_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="newsletter_language" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('language_label', $language, $translations); ?></label>
                        <select id="newsletter_language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="en" <?php echo $language === 'en' ? 'selected' : ''; ?>><?php echo t('english', $language, $translations); ?></option>
                            <option value="ko" <?php echo $language === 'ko' ? 'selected' : ''; ?>><?php echo t('korean', $language, $translations); ?></option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md">
                        <?php echo t('subscribe_button', $language, $translations); ?>
                    </button>
                </form>
                
                <div id="newsletterMessage" class="mt-4 p-3 rounded-md hidden"></div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('newsletterForm');
                const messageDiv = document.getElementById('newsletterMessage');
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Get form data
                    const formData = new FormData(form);
                    
                    // Simple validation
                    const email = formData.get('email');
                    if (!email || !email.includes('@')) {
                        messageDiv.textContent = "<?php echo t('email_required', $language, $translations); ?>";
                        messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                        messageDiv.classList.add('bg-red-100', 'text-red-800');
                        return;
                    }
                    
                    // Submit form via AJAX
                    fetch('newsletter-subscribe.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Display result message
                        messageDiv.textContent = data.message;
                        messageDiv.classList.remove('hidden');
                        
                        if (data.success) {
                            messageDiv.classList.remove('bg-red-100', 'text-red-800');
                            messageDiv.classList.add('bg-green-100', 'text-green-800');
                            form.reset();
                        } else {
                            messageDiv.classList.remove('bg-green-100', 'text-green-800');
                            messageDiv.classList.add('bg-red-100', 'text-red-800');
                        }
                    })
                    .catch(error => {
                        // Display error message
                        messageDiv.textContent = "<?php echo t('newsletter_error_message', $language, $translations); ?>";
                        messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                        messageDiv.classList.add('bg-red-100', 'text-red-800');
                        console.error('Error:', error);
                    });
                });
            });
            </script>
        </div>
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