<?php
// Database connection
$host = 'localhost';
$dbname = 'bestluck';
$username = 'bestluck';
$password = 'Nocpriss12!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define all programs data from programs.php
    $all_programs = [
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
            'image' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
            'description' => 'Developing tomorrow\'s leaders through personal development programs.',
            'ko_description' => '개인 개발 프로그램을 통한 미래 지도자 양성.'
        ],
        [
            'slug' => 'sports-med-edu',
            'title' => 'Sports-Medicine Education for Staff & Athletes',
            'ko_title' => '스포츠 의학 교육 지원',
            'image' => 'https://images.unsplash.com/photo-1576678927484-cc907957088c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=687&q=80',
            'description' => 'Supporting the education of sports medicine practices for staff and athletes.',
            'ko_description' => '스태프와 선수를 위한 스포츠 의학 교육 지원.'
        ],
        [
            'slug' => 'meal-support',
            'title' => 'Nutritious Meal Support',
            'ko_title' => '급식 지원',
            'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=880&q=80',
            'description' => 'Providing nutritious meals for students and athletes to support their development.',
            'ko_description' => '학생과 선수의 발전을 지원하기 위한 영양가 있는 식사 제공.'
        ],
        [
            'slug' => 'supplements',
            'title' => 'Dietary Supplement Guidance & Aid',
            'ko_title' => '건강보조식품 지원',
            'image' => 'https://images.unsplash.com/photo-1577041677443-8bbdfd8cce62?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
            'description' => 'Guiding and providing appropriate dietary supplements for optimal performance.',
            'ko_description' => '최적의 퍼포먼스를 위한 적절한 건강보조식품 가이드 및 지원.'
        ],
        [
            'slug' => 'career-consult',
            'title' => 'Career Education & Consulting',
            'ko_title' => '진로 교육·컨설팅',
            'image' => 'https://images.unsplash.com/photo-1551836022-d5d88e9218df?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
            'description' => 'Helping students and athletes plan and prepare for successful careers.',
            'ko_description' => '학생과 선수들이 성공적인 경력을 계획하고 준비하도록 돕습니다.'
        ],
        [
            'slug' => 'rehab-cert',
            'title' => 'Sports Rehab & Trainer Certification',
            'ko_title' => '스포츠 재활·트레이닝·자격증',
            'image' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
            'description' => 'Providing certification programs for sports rehabilitation and training professionals.',
            'ko_description' => '스포츠 재활 및 트레이닝 전문가를 위한 자격증 프로그램 제공.'
        ],
        [
            'slug' => 'seminars',
            'title' => 'Seminars & Events Hosting',
            'ko_title' => '세미나·행사 개최',
            'image' => 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
            'description' => 'Organizing educational seminars and events for knowledge sharing and networking.',
            'ko_description' => '지식 공유와 네트워킹을 위한 교육 세미나 및 행사 조직.'
        ],
        [
            'slug' => 'publications',
            'title' => 'Publications & Journals',
            'ko_title' => '서적·잡지·간행물 출판',
            'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1073&q=80',
            'description' => 'Publishing educational materials, research papers, and journals on sports and health.',
            'ko_description' => '스포츠 및 건강에 관한 교육 자료, 연구 논문 및 저널 출판.'
        ],
        [
            'slug' => 'musculo-edu',
            'title' => 'Sports, MSK & Medical Education',
            'ko_title' => '스포츠·근골격계·의료 교육',
            'image' => 'https://images.unsplash.com/photo-1551076805-e1869033e561?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1332&q=80',
            'description' => 'Educating on sports medicine, musculoskeletal health, and medical practices.',
            'ko_description' => '스포츠 의학, 근골격계 건강 및 의료 실습에 대한 교육.'
        ],
        [
            'slug' => 'elite-support',
            'title' => 'National-Team / School Athlete Support',
            'ko_title' => '국가대표·팀·학교 선수 지원',
            'image' => 'https://images.unsplash.com/photo-1531415074968-036ba1b575da?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1167&q=80',
            'description' => 'Providing comprehensive support for national team and school athletes.',
            'ko_description' => '국가대표 및 학교 선수들을 위한 종합적인 지원 제공.'
        ],
        [
            'slug' => 'ai-automation',
            'title' => 'AI-Driven Automation & Assessment Tools',
            'ko_title' => 'AI 기반 자동화·평가 도구',
            'image' => 'https://images.unsplash.com/photo-1507146153580-69a1fe6d8aa1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
            'description' => 'Developing AI-powered tools for performance assessment and training optimization.',
            'ko_description' => '성능 평가 및 트레이닝 최적화를 위한 AI 기반 도구 개발.'
        ],
        [
            'slug' => 'govt-projects',
            'title' => 'Government-Funded Projects',
            'ko_title' => '국책과제 사업',
            'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
            'description' => 'Managing and implementing projects supported by government funding.',
            'ko_description' => '정부 지원 프로젝트 관리 및 실행.'
        ]
    ];
    
    // Check for existing programs by slug
    $added = 0;
    $skipped = 0;
    $updated = 0;
    
    // First, get existing slugs
    $stmt = $pdo->query("SELECT slug FROM programs");
    $existingSlugs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Add content to new programs
    $sampleContent = '<p>This program provides comprehensive support for students and athletes.</p>
    <h3>Key Features</h3>
    <ul>
        <li>Personalized assistance</li>
        <li>Expert guidance</li>
        <li>Regular assessments</li>
        <li>Feedback and improvement</li>
    </ul>
    <p>We are committed to helping each individual reach their full potential through our dedicated team of professionals.</p>';
    
    $sampleKoContent = '<p>이 프로그램은 학생과 선수를 위한 종합적인 지원을 제공합니다.</p>
    <h3>주요 특징</h3>
    <ul>
        <li>맞춤형 지원</li>
        <li>전문가 지도</li>
        <li>정기적인 평가</li>
        <li>피드백 및 개선</li>
    </ul>
    <p>저희는 전문 팀을 통해 각 개인이 잠재력을 최대한 발휘할 수 있도록 최선을 다하고 있습니다.</p>';
    
    // Prepare statements for insert and update
    $insertStmt = $pdo->prepare("INSERT INTO programs (slug, title, ko_title, description, ko_description, image, content, ko_content) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $updateStmt = $pdo->prepare("UPDATE programs SET title = ?, ko_title = ?, description = ?, ko_description = ?, image = ?, content = ?, ko_content = ? WHERE slug = ?");
    
    foreach ($all_programs as $program) {
        // Check if program already exists
        if (in_array($program['slug'], $existingSlugs)) {
            // Update existing program
            $updateStmt->execute([
                $program['title'],
                $program['ko_title'],
                $program['description'],
                $program['ko_description'],
                $program['image'],
                $sampleContent,
                $sampleKoContent,
                $program['slug']
            ]);
            $updated++;
        } else {
            // Insert new program
            $insertStmt->execute([
                $program['slug'],
                $program['title'],
                $program['ko_title'],
                $program['description'],
                $program['ko_description'],
                $program['image'],
                $sampleContent,
                $sampleKoContent
            ]);
            $added++;
        }
    }
    
    echo "<h1>Program Sync Results</h1>";
    echo "<p>Added: $added new programs</p>";
    echo "<p>Updated: $updated existing programs</p>";
    echo "<p>Total programs: " . ($added + count($existingSlugs)) . "</p>";
    
    // Get updated program list
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY id");
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Current Programs in Database</h2>";
    echo "<ul>";
    foreach ($programs as $program) {
        echo "<li>" . htmlspecialchars($program['title']) . " (ID: " . $program['id'] . ", Slug: " . htmlspecialchars($program['slug']) . ")</li>";
    }
    echo "</ul>";
    
    echo "<p><a href='/admin.php'>Go to Admin Panel</a></p>";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>
