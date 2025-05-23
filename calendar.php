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

// Get current month and year, defaulting to current if not specified
$currentMonth = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$currentYear = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));

// Validate month and year
if ($currentMonth < 1 || $currentMonth > 12) {
    $currentMonth = intval(date('m'));
}
if ($currentYear < 2020 || $currentYear > 2030) {
    $currentYear = intval(date('Y'));
}

// Calculate previous and next month/year
$prevMonth = $currentMonth - 1;
$prevYear = $currentYear;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $currentMonth + 1;
$nextYear = $currentYear;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

// Get first day of the month
$firstDayOfMonth = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$numberDays = date('t', $firstDayOfMonth);
$dateComponents = getdate($firstDayOfMonth);
$monthName = $dateComponents['month'];
$dayOfWeek = $dateComponents['wday']; // 0 for Sunday, 6 for Saturday

// Get events for the current month
$events = [];
$featuredEvents = [];
$upcomingEvents = [];

try {
    $pdo = connect_db();
    if ($pdo) {
        // Get month start and end dates for query
        $startDate = "$currentYear-$currentMonth-01 00:00:00";
        $endDate = "$currentYear-$currentMonth-" . date('t', $firstDayOfMonth) . " 23:59:59";
        
        // Get events for current month
        $stmt = $pdo->prepare("SELECT * FROM events WHERE 
            (start_date BETWEEN ? AND ?) OR 
            (end_date BETWEEN ? AND ?) OR 
            (start_date <= ? AND end_date >= ?)
            ORDER BY start_date ASC");
        $stmt->execute([$startDate, $endDate, $startDate, $endDate, $startDate, $endDate]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organize events by date for the calendar view
        $eventsByDate = [];
        foreach ($events as $event) {
            $eventStartDate = new DateTime($event['start_date']);
            $eventEndDate = $event['end_date'] ? new DateTime($event['end_date']) : $eventStartDate;
            
            // Handle multi-day events
            $currentDate = clone $eventStartDate;
            while ($currentDate <= $eventEndDate) {
                $day = $currentDate->format('j');
                $eventMonth = intval($currentDate->format('m'));
                $eventYear = intval($currentDate->format('Y'));
                
                // Only add event to calendar if it falls in the current month
                if ($eventMonth == $currentMonth && $eventYear == $currentYear) {
                    if (!isset($eventsByDate[$day])) {
                        $eventsByDate[$day] = [];
                    }
                    $eventsByDate[$day][] = $event;
                }
                
                $currentDate->modify('+1 day');
            }
        }
        
        // Get featured events (upcoming and featured)
        $today = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("SELECT * FROM events WHERE start_date >= ? AND featured = 1 ORDER BY start_date ASC LIMIT 3");
        $stmt->execute([$today]);
        $featuredEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get upcoming events (next 5 events regardless of featured status)
        $stmt = $pdo->prepare("SELECT * FROM events WHERE start_date >= ? ORDER BY start_date ASC LIMIT 5");
        $stmt->execute([$today]);
        $upcomingEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error fetching events: " . $e->getMessage());
}

// Function to format date/time for display
function formatEventDate($startDate, $endDate, $allDay, $language) {
    $start = new DateTime($startDate);
    
    if ($allDay) {
        if ($endDate) {
            $end = new DateTime($endDate);
            if ($language === 'ko') {
                return $start->format('Y년 n월 j일') . ' - ' . $end->format('Y년 n월 j일') . ' (종일)';
            } else {
                return $start->format('M j, Y') . ' - ' . $end->format('M j, Y') . ' (All day)';
            }
        } else {
            if ($language === 'ko') {
                return $start->format('Y년 n월 j일') . ' (종일)';
            } else {
                return $start->format('M j, Y') . ' (All day)';
            }
        }
    } else {
        if ($endDate) {
            $end = new DateTime($endDate);
            if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                // Same day event
                if ($language === 'ko') {
                    return $start->format('Y년 n월 j일 g:i A') . ' - ' . $end->format('g:i A');
                } else {
                    return $start->format('M j, Y g:i A') . ' - ' . $end->format('g:i A');
                }
            } else {
                // Multi-day event
                if ($language === 'ko') {
                    return $start->format('Y년 n월 j일 g:i A') . ' - ' . $end->format('Y년 n월 j일 g:i A');
                } else {
                    return $start->format('M j, Y g:i A') . ' - ' . $end->format('M j, Y g:i A');
                }
            }
        } else {
            if ($language === 'ko') {
                return $start->format('Y년 n월 j일 g:i A');
            } else {
                return $start->format('M j, Y g:i A');
            }
        }
    }
}

// Translations
$translations = [
    'en' => [
        'page_title' => 'Event Calendar - KOSMO Foundation',
        'meta_description' => 'View upcoming events from the Korean Sports Medicine Support Foundation. Join us for conferences, workshops, fundraisers, and more.',
        'page_heading' => 'Event Calendar',
        'calendar' => 'Calendar',
        'upcoming_events' => 'Upcoming Events',
        'featured_events' => 'Featured Events',
        'no_events' => 'No events scheduled for this month.',
        'register_now' => 'Register Now',
        'location' => 'Location',
        'date' => 'Date',
        'view_details' => 'View Details',
        'days' => ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        'months' => ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        'prev_month' => 'Previous Month',
        'next_month' => 'Next Month',
        'today' => 'Today',
        'view_all_events' => 'View All Events',
        'home' => 'Home',
        'about' => 'About',
        'programs' => 'Programs',
        'gallery' => 'Gallery',
        'news' => 'News',
        'events' => 'Events',
        'donate' => 'Donate',
        'contact_us' => 'Contact Us',
        'donate_now' => 'Donate Now',
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
        'page_title' => '이벤트 캘린더 - 한국스포츠의료지원재단',
        'meta_description' => '한국스포츠의료지원재단의 다가오는 이벤트를 확인하세요. 컨퍼런스, 워크샵, 기금 모금 행사 등에 참여하세요.',
        'page_heading' => '이벤트 캘린더',
        'calendar' => '캘린더',
        'upcoming_events' => '다가오는 이벤트',
        'featured_events' => '주요 이벤트',
        'no_events' => '이번 달 예정된 이벤트가 없습니다.',
        'register_now' => '지금 등록하기',
        'location' => '장소',
        'date' => '날짜',
        'view_details' => '상세 보기',
        'days' => ['일', '월', '화', '수', '목', '금', '토'],
        'months' => ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        'prev_month' => '이전 달',
        'next_month' => '다음 달',
        'today' => '오늘',
        'view_all_events' => '모든 이벤트 보기',
        'home' => '홈',
        'about' => '소개',
        'programs' => '프로그램',
        'gallery' => '갤러리',
        'news' => '뉴스',
        'events' => '이벤트',
        'donate' => '후원하기',
        'contact_us' => '문의하기',
        'donate_now' => '지금 후원하기',
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
    <style>
        .calendar-day {
            min-height: 80px;
        }
        .event-dot {
            height: 8px;
            width: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        .today {
            background-color: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.5);
        }
    </style>
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
                <a href="calendar.php" class="hover:text-blue-600 font-semibold"><?php echo t('events', $language, $translations); ?></a>
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
                <a href="calendar.php" class="block px-3 py-2 rounded-md font-semibold bg-gray-100"><?php echo t('events', $language, $translations); ?></a>
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
        
        <div class="flex flex-col-reverse lg:flex-row gap-8">
            <!-- Calendar Section -->
            <div class="w-full lg:w-8/12">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 bg-blue-50 flex justify-between items-center">
                        <h2 class="text-xl font-semibold"><?php echo t('calendar', $language, $translations); ?></h2>
                        <div class="flex space-x-2">
                            <a href="calendar.php?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="bg-white px-3 py-1 rounded shadow text-sm">
                                <?php echo t('prev_month', $language, $translations); ?>
                            </a>
                            <a href="calendar.php" class="bg-blue-600 text-white px-3 py-1 rounded shadow text-sm">
                                <?php echo t('today', $language, $translations); ?>
                            </a>
                            <a href="calendar.php?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="bg-white px-3 py-1 rounded shadow text-sm">
                                <?php echo t('next_month', $language, $translations); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-4">
                        <div class="text-center mb-4">
                            <h3 class="text-xl font-semibold">
                                <?php 
                                if ($language === 'ko') {
                                    echo $currentYear . '년 ' . t('months', $language, $translations)[$currentMonth - 1];
                                } else {
                                    echo t('months', $language, $translations)[$currentMonth - 1] . ' ' . $currentYear;
                                }
                                ?>
                            </h3>
                        </div>
                        
                        <!-- Calendar grid -->
                        <div class="grid grid-cols-7 gap-1">
                            <!-- Days of the week -->
                            <?php foreach (t('days', $language, $translations) as $day): ?>
                                <div class="text-center font-medium p-2 bg-gray-100"><?php echo $day; ?></div>
                            <?php endforeach; ?>
                            
                            <!-- Blank days before start of month -->
                            <?php for ($i = 0; $i < $dayOfWeek; $i++): ?>
                                <div class="border p-1 bg-gray-50"></div>
                            <?php endfor; ?>
                            
                            <!-- Days of the month -->
                            <?php 
                            $today = date('j');
                            $thisMonth = date('n');
                            $thisYear = date('Y');
                            $isCurrentMonth = ($currentMonth == $thisMonth && $currentYear == $thisYear);
                            
                            for ($day = 1; $day <= $numberDays; $day++): 
                                $isToday = ($day == $today && $isCurrentMonth);
                                $hasEvents = isset($eventsByDate[$day]) && count($eventsByDate[$day]) > 0;
                            ?>
                                <div class="border p-1 calendar-day <?php echo $isToday ? 'today' : ''; ?>">
                                    <div class="font-medium text-right mb-1"><?php echo $day; ?></div>
                                    <?php if ($hasEvents): ?>
                                        <div class="text-xs">
                                            <?php 
                                            $eventsShown = 0;
                                            foreach ($eventsByDate[$day] as $event): 
                                                if ($eventsShown < 3): // Limit to 3 events per day for space
                                                    $eventsShown++;
                                            ?>
                                                <div class="mb-1 truncate">
                                                    <span class="event-dot bg-blue-500 mr-1"></span>
                                                    <span class="truncate">
                                                        <?php echo $language === 'ko' ? $event['ko_title'] : $event['title']; ?>
                                                    </span>
                                                </div>
                                            <?php 
                                                endif;
                                            endforeach; 
                                            
                                            // Show count of additional events
                                            if (count($eventsByDate[$day]) > 3): 
                                            ?>
                                                <div class="text-blue-600">
                                                    +<?php echo count($eventsByDate[$day]) - 3; ?> more
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endfor; ?>
                            
                            <!-- Blank days after end of month -->
                            <?php
                            $totalDaysShown = $dayOfWeek + $numberDays;
                            $remainingDays = 7 - ($totalDaysShown % 7);
                            if ($remainingDays < 7):
                                for ($i = 0; $i < $remainingDays; $i++): 
                            ?>
                                <div class="border p-1 bg-gray-50"></div>
                            <?php 
                                endfor;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Events List for Current Month -->
                <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 bg-blue-50">
                        <h2 class="text-xl font-semibold">
                            <?php 
                            if ($language === 'ko') {
                                echo $currentYear . '년 ' . t('months', $language, $translations)[$currentMonth - 1] . ' 이벤트';
                            } else {
                                echo t('months', $language, $translations)[$currentMonth - 1] . ' ' . $currentYear . ' Events';
                            }
                            ?>
                        </h2>
                    </div>
                    
                    <div class="p-4">
                        <?php if (count($events) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach ($events as $event): ?>
                                    <div class="border rounded p-4 hover:bg-gray-50">
                                        <h3 class="text-lg font-semibold mb-1">
                                            <?php echo $language === 'ko' ? $event['ko_title'] : $event['title']; ?>
                                        </h3>
                                        <div class="flex flex-col sm:flex-row sm:justify-between mb-2 text-sm">
                                            <div class="mb-1 sm:mb-0">
                                                <span class="font-medium"><?php echo t('date', $language, $translations); ?>:</span> 
                                                <?php echo formatEventDate($event['start_date'], $event['end_date'], $event['all_day'], $language); ?>
                                            </div>
                                            <div>
                                                <span class="font-medium"><?php echo t('location', $language, $translations); ?>:</span> 
                                                <?php echo $language === 'ko' ? $event['ko_location'] : $event['location']; ?>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-3">
                                            <?php 
                                            $description = $language === 'ko' ? $event['ko_description'] : $event['description'];
                                            echo strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description; 
                                            ?>
                                        </p>
                                        <?php if ($event['registration_url']): ?>
                                            <a href="<?php echo $event['registration_url']; ?>" target="_blank" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
                                                <?php echo t('register_now', $language, $translations); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-gray-500 py-8"><?php echo t('no_events', $language, $translations); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="w-full lg:w-4/12">
                <!-- Featured Events -->
                <?php if (count($featuredEvents) > 0): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-8">
                        <div class="p-4 bg-blue-50">
                            <h2 class="text-xl font-semibold"><?php echo t('featured_events', $language, $translations); ?></h2>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <?php foreach ($featuredEvents as $event): ?>
                                    <div class="border-b pb-4 last:border-b-0 last:pb-0">
                                        <h3 class="text-lg font-semibold mb-1">
                                            <?php echo $language === 'ko' ? $event['ko_title'] : $event['title']; ?>
                                        </h3>
                                        <div class="text-sm text-gray-500 mb-2">
                                            <?php echo formatEventDate($event['start_date'], $event['end_date'], $event['all_day'], $language); ?>
                                        </div>
                                        <div class="text-sm text-gray-500 mb-2">
                                            <?php echo $language === 'ko' ? $event['ko_location'] : $event['location']; ?>
                                        </div>
                                        <?php if ($event['registration_url']): ?>
                                            <a href="<?php echo $event['registration_url']; ?>" target="_blank" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm">
                                                <?php echo t('register_now', $language, $translations); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Upcoming Events -->
                <?php if (count($upcomingEvents) > 0): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-4 bg-blue-50">
                            <h2 class="text-xl font-semibold"><?php echo t('upcoming_events', $language, $translations); ?></h2>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                <?php foreach ($upcomingEvents as $event): ?>
                                    <div class="flex items-start space-x-2 border-b pb-4 last:border-b-0 last:pb-0">
                                        <div class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-center min-w-[50px]">
                                            <?php 
                                            $date = new DateTime($event['start_date']);
                                            echo $date->format('M') . '<br>' . $date->format('j'); 
                                            ?>
                                        </div>
                                        <div>
                                            <h3 class="font-medium">
                                                <?php echo $language === 'ko' ? $event['ko_title'] : $event['title']; ?>
                                            </h3>
                                            <div class="text-xs text-gray-500">
                                                <?php echo $language === 'ko' ? $event['ko_location'] : $event['location']; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Newsletter Sign-up -->
                <div class="mt-8">
                    <div class="w-full bg-blue-50 p-6 rounded-lg shadow-md newsletter-form">
                        <h3 class="text-xl font-semibold mb-2"><?php echo t('newsletter_heading', $language, $translations); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo t('newsletter_subheading', $language, $translations); ?></p>
                        
                        <form id="newsletterForm" class="space-y-4">
                            <div>
                                <label for="newsletter_name" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('name_label', $language, $translations); ?></label>
                                <input type="text" id="newsletter_name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            
                            <div>
                                <label for="newsletter_email" class="block text-sm font-medium text-gray-700 mb-1"><?php echo t('email_label', $language, $translations); ?> *</label>
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
                                messageDiv.textContent = "<?php echo t('error_message', $language, $translations); ?>";
                                messageDiv.classList.remove('hidden', 'bg-green-100', 'text-green-800');
                                messageDiv.classList.add('bg-red-100', 'text-red-800');
                                console.error('Error:', error);
                            });
                        });
                    });
                    </script>
                </div>
            </div>
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