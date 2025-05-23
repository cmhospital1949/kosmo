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

$pdo = connect_db();

if (!$pdo) {
    die("Failed to connect to database");
}

try {
    // Check if the news_posts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'news_posts'");
    $tableExists = $stmt->fetchColumn();
    
    if (!$tableExists) {
        // Create the news_posts table
        $pdo->exec("CREATE TABLE `news_posts` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `slug` VARCHAR(255) NOT NULL UNIQUE,
            `title` VARCHAR(255) NOT NULL,
            `ko_title` VARCHAR(255) NOT NULL,
            `excerpt` TEXT NULL,
            `ko_excerpt` TEXT NULL,
            `content` TEXT NOT NULL,
            `ko_content` TEXT NOT NULL,
            `cover_image` VARCHAR(255) NULL,
            `category` VARCHAR(100) DEFAULT 'news',
            `author` VARCHAR(100) DEFAULT 'KOSMO Foundation',
            `featured` TINYINT(1) NOT NULL DEFAULT 0,
            `published` TINYINT(1) NOT NULL DEFAULT 1,
            `publish_date` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Create the news_categories table
        $pdo->exec("CREATE TABLE `news_categories` (
            `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL UNIQUE,
            `ko_name` VARCHAR(100) NOT NULL,
            `description` TEXT NULL,
            `ko_description` TEXT NULL,
            `slug` VARCHAR(100) NOT NULL UNIQUE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        // Insert default categories
        $pdo->exec("INSERT INTO `news_categories` (
            `name`, 
            `ko_name`, 
            `description`, 
            `ko_description`, 
            `slug`
        ) VALUES 
        ('News', '뉴스', 'Foundation news and updates', '재단 뉴스 및 업데이트', 'news'),
        ('Events', '이벤트', 'Upcoming and past events', '다가오는 및 지난 이벤트', 'events'),
        ('Press Releases', '보도자료', 'Official press releases from the foundation', '재단의 공식 보도자료', 'press')");
        
        // Insert sample news posts - first post
        $stmt = $pdo->prepare("INSERT INTO `news_posts` (
            `slug`, 
            `title`, 
            `ko_title`, 
            `excerpt`, 
            `ko_excerpt`, 
            `content`, 
            `ko_content`, 
            `category`, 
            `publish_date`,
            `featured`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'foundation-celebrates-5th-anniversary', 
            'KOSMO Foundation Celebrates 5th Anniversary with Fundraising Gala', 
            'KOSMO 재단, 기금 모금 갈라와 함께 5주년 기념행사 개최', 
            'The Korean Sports Medicine Support Foundation celebrates its 5th anniversary with a special fundraising gala event in Seoul.', 
            '한국스포츠의료지원재단이 서울에서 특별 기금 모금 갈라 이벤트와 함께 5주년을 축하합니다.', 
            '<p>The Korean Sports Medicine Support Foundation (KOSMO) celebrated its 5th anniversary with a special fundraising gala event in Seoul on May 15, 2025. The event brought together supporters, donors, medical professionals, and athletes who have benefited from the Foundation\'s programs over the years.</p><p>Since its founding in 2020, KOSMO has provided medical support to over 5,000 student-athletes, offered educational programs to more than 3,000 sports medicine professionals, and funded research initiatives that have advanced the field of sports medicine in Korea.</p><p>"We are incredibly proud of what we\'ve accomplished in our first five years," said Dr. Lee Sang-hoon, Executive Director of KOSMO. "This anniversary is not just a celebration of our past achievements, but a launching point for our ambitious goals for the future."</p><p>The gala raised over 200 million KRW, which will support the Foundation\'s flagship programs, including medical support for student-athletes, sports medicine education, and research initiatives.</p><p>KOSMO also announced the launch of a new scholarship program for medical students specializing in sports medicine, set to begin in the fall semester of 2025.</p>', 
            '<p>한국스포츠의료지원재단(KOSMO)은 2025년 5월 15일 서울에서 특별 기금 모금 갈라 이벤트와 함께 5주년을 축하했습니다. 이 행사에는 수년 동안 재단의 프로그램의 혜택을 받은 지지자, 기부자, 의료 전문가 및 운동선수들이 한자리에 모였습니다.</p><p>2020년 설립 이후 KOSMO는 5,000명 이상의 학생 운동선수에게 의료 지원을 제공하고, 3,000명 이상의 스포츠 의학 전문가에게 교육 프로그램을 제공하며, 한국의 스포츠 의학 분야를 발전시킨 연구 이니셔티브에 자금을 지원했습니다.</p><p>"우리는 첫 5년 동안 이룬 성과를 매우 자랑스럽게 생각합니다"라고 KOSMO의 이상훈 사무총장은 말했습니다. "이번 기념일은 우리의 과거 성취를 축하하는 것뿐만 아니라, 미래를 위한 야심찬 목표를 위한 출발점이기도 합니다."</p><p>갈라는 2억원 이상을 모금했으며, 이는 학생 운동선수를 위한 의료 지원, 스포츠 의학 교육 및 연구 이니셔티브를 포함한 재단의 주요 프로그램을 지원하게 됩니다.</p><p>KOSMO는 또한 2025년 가을 학기부터 시작될 스포츠 의학을 전공하는 의대생을 위한 새로운 장학금 프로그램의 시작을 발표했습니다.</p>', 
            'news', 
            '2025-05-15 18:00:00',
            1
        ]);
        
        // Insert second sample post
        $stmt->execute([
            'sports-medicine-conference-2025', 
            'KOSMO to Host Annual Sports Medicine Conference in September', 
            'KOSMO, 9월에 연례 스포츠 의학 컨퍼런스 개최 예정', 
            'The Korean Sports Medicine Support Foundation announces its annual Sports Medicine Conference, scheduled for September 2025 in Seoul.', 
            '한국스포츠의료지원재단이 2025년 9월 서울에서 열릴 연례 스포츠 의학 컨퍼런스를 발표합니다.', 
            '<p>The Korean Sports Medicine Support Foundation (KOSMO) is proud to announce its annual Sports Medicine Conference, scheduled for September 10-12, 2025, at the COEX Convention Center in Seoul.</p><p>The three-day event will feature keynote speeches, panel discussions, workshops, and networking opportunities for sports medicine professionals, researchers, and students. This year\'s theme is "Advances in Prevention and Rehabilitation of Sports Injuries."</p><p>"This conference has become one of the most important gatherings for sports medicine professionals in Asia," said Dr. Kim Min-ji, Conference Chair and Board Member of KOSMO. "We are excited to bring together experts from around the world to share knowledge and advance the field."</p><p>Featured speakers include Dr. James Andrews from the United States, a world-renowned orthopedic surgeon specializing in sports injuries, and Dr. Chen Wei from China, a leading researcher in sports injury prevention.</p><p>Registration for the conference is now open, with early bird rates available until July 31, 2025. KOSMO also offers scholarships for students and early-career professionals to attend the conference.</p>', 
            '<p>한국스포츠의료지원재단(KOSMO)은 2025년 9월 10일부터 12일까지 서울 코엑스 컨벤션 센터에서 열릴 연례 스포츠 의학 컨퍼런스를 자랑스럽게 발표합니다.</p><p>3일간의 행사는 스포츠 의학 전문가, 연구자 및 학생들을 위한 기조 연설, 패널 토론, 워크숍 및 네트워킹 기회를 제공합니다. 올해의 주제는 "스포츠 부상의 예방 및 재활의 발전"입니다.</p><p>"이 컨퍼런스는 아시아에서 스포츠 의학 전문가들을 위한 가장 중요한 모임 중 하나가 되었습니다"라고 KOSMO의 컨퍼런스 의장이자 이사인 김민지 박사는 말했습니다. "전 세계 전문가들이 모여 지식을 공유하고 분야를 발전시키는 자리를 마련하게 되어 기쁩니다."</p><p>주요 연사로는 스포츠 부상 전문 세계적으로 유명한 정형외과 의사인 미국의 제임스 앤드류스 박사와 스포츠 부상 예방 분야의 선도적 연구자인 중국의 첸 웨이 박사가 포함됩니다.</p><p>컨퍼런스 등록은 현재 오픈되어 있으며, 2025년 7월 31일까지 얼리버드 요금이 제공됩니다. KOSMO는 또한 학생 및 초기 경력 전문가들이 컨퍼런스에 참석할 수 있도록 장학금을 제공합니다.</p>', 
            'events', 
            '2025-05-10 09:00:00',
            1
        ]);
        
        // Insert third sample post
        $stmt->execute([
            'new-partnership-with-seoul-national-university', 
            'KOSMO Foundation Announces Partnership with Seoul National University', 
            'KOSMO 재단, 서울대학교와 협력 발표', 
            'The Korean Sports Medicine Support Foundation and Seoul National University announce a new partnership to advance sports medicine research and education.', 
            '한국스포츠의료지원재단과 서울대학교가 스포츠 의학 연구 및 교육을 발전시키기 위한 새로운 파트너십을 발표합니다.', 
            '<p>The Korean Sports Medicine Support Foundation (KOSMO) is pleased to announce a new strategic partnership with Seoul National University (SNU) to advance sports medicine research and education in Korea.</p><p>The partnership, formalized in a ceremony held at SNU on April 28, 2025, will establish a joint research center focused on sports injury prevention and treatment, as well as create new educational opportunities for medical students interested in sports medicine.</p><p>"This collaboration with Seoul National University represents a significant step forward in our mission to advance sports medicine in Korea," said Dr. Lee Sang-hoon, Executive Director of KOSMO. "By combining our resources and expertise, we can make greater strides in research and education."</p><p>Professor Park Ji-woo, Chair of the Department of Sports Medicine at SNU, added, "We are excited to partner with KOSMO to enhance our research capabilities and provide more opportunities for our students. This collaboration will benefit not only our institutions but also athletes and sports medicine professionals across the country."</p><p>The partnership will also create internship and scholarship opportunities for SNU medical students at KOSMO-affiliated hospitals and clinics, giving them hands-on experience in sports medicine practice.</p>', 
            '<p>한국스포츠의료지원재단(KOSMO)은 한국의 스포츠 의학 연구 및 교육을 발전시키기 위해 서울대학교(SNU)와 새로운 전략적 파트너십을 발표하게 되어 기쁘게 생각합니다.</p><p>2025년 4월 28일 서울대학교에서 열린 행사에서 공식화된 이 파트너십은 스포츠 부상 예방 및 치료에 중점을 둔 공동 연구 센터를 설립하고, 스포츠 의학에 관심 있는 의대생들을 위한 새로운 교육 기회를 만들 예정입니다.</p><p>"서울대학교와의 이번 협력은 한국의 스포츠 의학을 발전시키는 우리의 사명에 있어 중요한 진전을 의미합니다"라고 KOSMO의 이상훈 사무총장은 말했습니다. "우리의 자원과 전문성을 결합함으로써 연구와 교육에서 더 큰 발전을 이룰 수 있습니다."</p><p>서울대학교 스포츠 의학과의 박지우 학과장은 "연구 역량을 강화하고 학생들에게 더 많은 기회를 제공하기 위해 KOSMO와 협력하게 되어 기쁩니다. 이번 협력은 우리 기관뿐만 아니라 전국의 운동선수와 스포츠 의학 전문가들에게도 도움이 될 것입니다"라고 덧붙였습니다.</p><p>이 파트너십은 또한 서울대 의대생들에게 KOSMO 제휴 병원과 클리닉에서 인턴십 및 장학금 기회를 제공하여 스포츠 의학 실습에 대한 실무 경험을 제공할 것입니다.</p>', 
            'press', 
            '2025-04-28 14:30:00',
            0
        ]);
        
        echo "News tables created and populated with sample data.";
    } else {
        echo "News tables already exist.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>