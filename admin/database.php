<?php
require_once __DIR__ . '/../config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Database configuration

$message = '';
$error = '';
$dbInfo = [];

// Test database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get database information
    $dbInfo['status'] = 'Connected';
    
    // Get table counts
    $tables = [
        'admin_users' => 'Admin Users',
        'programs' => 'Programs',
        'gallery_categories' => 'Gallery Categories',
        'gallery_images' => 'Gallery Images'
    ];
    
    foreach ($tables as $table => $label) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $table");
            $stmt->execute();
            $dbInfo['tables'][$table] = [
                'label' => $label,
                'count' => $stmt->fetchColumn()
            ];
        } catch (PDOException $e) {
            $dbInfo['tables'][$table] = [
                'label' => $label,
                'count' => 'Table not found'
            ];
        }
    }
    
    // Get MySQL version
    $stmt = $pdo->query('SELECT VERSION()');
    $dbInfo['version'] = $stmt->fetchColumn();
    
    // Get database size
    $stmt = $pdo->prepare("SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size 
        FROM information_schema.TABLES 
        WHERE table_schema = ?");
    $stmt->execute([$dbname]);
    $dbInfo['size'] = $stmt->fetchColumn() . ' MB';
    
} catch (PDOException $e) {
    $error = "Database connection failed: " . $e->getMessage();
    $dbInfo['status'] = 'Disconnected';
}

// Handle database reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'reset_all' && isset($_POST['confirm_reset']) && $_POST['confirm_reset'] === 'RESET') {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Drop existing tables
            $pdo->exec("DROP TABLE IF EXISTS gallery_images");
            $pdo->exec("DROP TABLE IF EXISTS gallery_categories");
            $pdo->exec("DROP TABLE IF EXISTS programs");
            $pdo->exec("DROP TABLE IF EXISTS admin_users");
            
            // Include the setup script to recreate tables
            include 'db_setup.php';
            
            $message = "Database reset successfully. Tables have been recreated and initial data has been imported.";
            
            // Refresh page after 2 seconds
            header("refresh:2;url=database.php");
        } catch (PDOException $e) {
            $error = "Database reset failed: " . $e->getMessage();
        }
    } elseif ($_POST['action'] === 'import_programs' && isset($_POST['confirm_import']) && $_POST['confirm_import'] === 'IMPORT') {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Import programs from the programs.php file
            $all_programs = [
                [
                    'slug' => 'medical-support',
                    'title' => 'Medical Support for Students & Student-Athletes',
                    'ko_title' => '학생·학생선수 의료 지원',
                    'image' => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
                    'description' => 'Providing medical care and support for students and student-athletes.',
                    'ko_description' => '학생 및 학생선수를 위한 의료 지원 제공.',
                    'content' => '<p>Our medical support program provides comprehensive healthcare services for students and student-athletes. We focus on prevention, diagnosis, treatment, and rehabilitation of sports-related injuries and other health issues.</p><p>Key services include:</p><ul><li>Regular health check-ups</li><li>Injury prevention workshops</li><li>Physical therapy and rehabilitation</li><li>Mental health counseling</li><li>Nutritional guidance</li></ul><p>Through partnerships with medical professionals and institutions, we ensure that our beneficiaries receive high-quality care tailored to their specific needs.</p>',
                    'ko_content' => '<p>저희 의료 지원 프로그램은 학생 및 학생선수를 위한 종합적인 의료 서비스를 제공합니다. 스포츠 관련 부상 및 기타 건강 문제의 예방, 진단, 치료 및 재활에 중점을 둡니다.</p><p>주요 서비스는 다음과 같습니다:</p><ul><li>정기 건강 검진</li><li>부상 예방 워크샵</li><li>물리 치료 및 재활</li><li>정신 건강 상담</li><li>영양 지도</li></ul><p>의료 전문가 및 기관과의 파트너십을 통해 저희는 수혜자들이 그들의 특정 요구에 맞춘 고품질 케어를 받을 수 있도록 보장합니다.</p>'
                ],
                [
                    'slug' => 'cultural-arts',
                    'title' => 'Cultural & Arts Education/Events',
                    'ko_title' => '문화·예술 교육 및 행사',
                    'image' => 'https://images.unsplash.com/photo-1535982330050-f1c2fb79ff78?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1074&q=80',
                    'description' => 'Fostering creativity and expression through cultural and arts programs.',
                    'ko_description' => '문화 및 예술 프로그램을 통한 창의성과 표현력 육성.',
                    'content' => '<p>Our Cultural & Arts Education/Events program aims to enrich the lives of students and athletes through exposure to various forms of art and cultural activities. We believe that artistic expression is essential for holistic development and personal growth.</p><p>Program highlights include:</p><ul><li>Regular workshops in visual arts, music, dance, and theater</li><li>Cultural exchange programs</li><li>Art exhibitions showcasing student work</li><li>Performance opportunities</li><li>Field trips to museums, galleries, and cultural events</li></ul><p>Through these activities, participants develop creativity, self-expression, and an appreciation for cultural diversity, enhancing their overall educational experience.</p>',
                    'ko_content' => '<p>저희 문화 및 예술 교육/행사 프로그램은 다양한 형태의 예술 및 문화 활동을 통해 학생과 선수들의 삶을 풍요롭게 하는 것을 목표로 합니다. 예술적 표현은 전인적 발달과 개인 성장에 필수적이라고 믿습니다.</p><p>프로그램 하이라이트:</p><ul><li>시각 예술, 음악, 댄스, 연극 정기 워크샵</li><li>문화 교류 프로그램</li><li>학생 작품 전시회</li><li>공연 기회</li><li>박물관, 갤러리, 문화 행사 현장 학습</li></ul><p>이러한 활동을 통해 참가자들은 창의성, 자기표현, 문화적 다양성에 대한 이해를 발전시켜 전반적인 교육 경험을 향상시킵니다.</p>'
                ],
                [
                    'slug' => 'leadership',
                    'title' => 'Personal Development & Leadership Training',
                    'ko_title' => '자기개발·리더십 교육',
                    'image' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1170&q=80',
                    'description' => 'Developing tomorrow\'s leaders through personal development programs.',
                    'ko_description' => '개인 개발 프로그램을 통한 미래 지도자 양성.',
                    'content' => '<p>Our Leadership Training program focuses on developing essential skills that empower students and athletes to become effective leaders in their communities and future careers. We emphasize personal growth, ethical decision-making, and practical leadership experience.</p><p>Our curriculum includes:</p><ul><li>Self-awareness and emotional intelligence development</li><li>Effective communication skills</li><li>Team-building and collaboration</li><li>Problem-solving and critical thinking</li><li>Goal setting and strategic planning</li><li>Ethical leadership principles</li></ul><p>Through workshops, seminars, and hands-on leadership opportunities, participants build confidence and competence as they prepare to lead and serve in various contexts.</p>',
                    'ko_description' => '<p>저희 리더십 트레이닝 프로그램은 학생과 선수들이 지역사회와 미래 직업에서 효과적인 리더가 될 수 있도록 필수적인 기술을 개발하는 데 중점을 둡니다. 개인적 성장, 윤리적 의사결정, 실제 리더십 경험을 강조합니다.</p><p>커리큘럼에는 다음이 포함됩니다:</p><ul><li>자기 인식 및 감성 지능 개발</li><li>효과적인 의사소통 기술</li><li>팀 빌딩 및 협업</li><li>문제 해결 및 비판적 사고</li><li>목표 설정 및 전략적 계획</li><li>윤리적 리더십 원칙</li></ul><p>워크샵, 세미나, 직접 실행하는 리더십 기회를 통해, 참가자들은 다양한 상황에서 리드하고 봉사할 준비를 하면서 자신감과 역량을 구축합니다.</p>'
                ]
                // Add more programs as needed
            ];
            
            // Import programs
            $stmt = $pdo->prepare("INSERT INTO programs (slug, title, ko_title, description, ko_description, image, content, ko_content) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE 
                                  title = VALUES(title), 
                                  ko_title = VALUES(ko_title), 
                                  description = VALUES(description), 
                                  ko_description = VALUES(ko_description), 
                                  image = VALUES(image), 
                                  content = VALUES(content), 
                                  ko_content = VALUES(ko_content)");
            
            foreach ($all_programs as $program) {
                $stmt->execute([
                    $program['slug'],
                    $program['title'],
                    $program['ko_title'],
                    $program['description'],
                    $program['ko_description'],
                    $program['image'],
                    $program['content'] ?? '',
                    $program['ko_content'] ?? ''
                ]);
            }
            
            $message = "Programs imported successfully.";
            
            // Refresh page after 2 seconds
            header("refresh:2;url=database.php");
        } catch (PDOException $e) {
            $error = "Program import failed: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - KOSMO Foundation Admin</title>
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
        body {
            font-family: 'Open Sans', 'Noto Sans KR', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-primary text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">KOSMO Foundation Admin</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="logout.php" class="bg-white text-primary hover:bg-gray-100 px-3 py-1 rounded text-sm">Logout</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4">
            <ul class="flex space-x-8">
                <li><a href="dashboard.php" class="inline-block py-4 text-gray-500 hover:text-primary">Dashboard</a></li>
                <li><a href="programs.php" class="inline-block py-4 text-gray-500 hover:text-primary">Programs</a></li>
                <li><a href="gallery.php" class="inline-block py-4 text-gray-500 hover:text-primary">Gallery</a></li>
                <li><a href="profile.php" class="inline-block py-4 text-gray-500 hover:text-primary">Profile</a></li>
                <li><a href="database.php" class="inline-block py-4 text-primary border-b-2 border-primary font-medium">Database</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-6">Database Management</h2>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo $message; ?></p>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?php echo $error; ?></p>
        </div>
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Database Information -->
            <div class="md:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-6">Database Information</h3>
                    
                    <?php if ($dbInfo['status'] === 'Connected'): ?>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-1/3 font-medium">Status:</div>
                            <div class="w-2/3">
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm">Connected</span>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-1/3 font-medium">Database:</div>
                            <div class="w-2/3"><?php echo htmlspecialchars($dbname); ?></div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-1/3 font-medium">MySQL Version:</div>
                            <div class="w-2/3"><?php echo htmlspecialchars($dbInfo['version']); ?></div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-1/3 font-medium">Database Size:</div>
                            <div class="w-2/3"><?php echo htmlspecialchars($dbInfo['size']); ?></div>
                        </div>
                        
                        <div class="mt-6">
                            <h4 class="font-medium mb-3">Table Statistics:</h4>
                            <table class="min-w-full border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="py-2 px-4 border-b text-left">Table</th>
                                        <th class="py-2 px-4 border-b text-right">Records</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dbInfo['tables'] as $table => $info): ?>
                                    <tr>
                                        <td class="py-2 px-4 border-b"><?php echo htmlspecialchars($info['label']); ?></td>
                                        <td class="py-2 px-4 border-b text-right"><?php echo is_numeric($info['count']) ? number_format($info['count']) : $info['count']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h4 class="text-lg font-semibold mb-2">Database Connection Failed</h4>
                        <p class="text-gray-600"><?php echo $error; ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Database Actions -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold mb-6">Database Actions</h3>
                    
                    <div class="space-y-6">
                        <!-- Import Programs -->
                        <div>
                            <h4 class="font-medium mb-2">Import Default Programs</h4>
                            <p class="text-sm text-gray-600 mb-3">Import program data from default templates.</p>
                            <form method="POST" class="mt-2" onsubmit="return confirm('Are you sure you want to import default programs? Any existing programs with the same slug will be updated.');">
                                <input type="hidden" name="action" value="import_programs">
                                <div class="mb-2">
                                    <input type="text" name="confirm_import" class="w-full px-3 py-2 border rounded-md" placeholder="Type 'IMPORT' to confirm" required>
                                </div>
                                <button type="submit" class="w-full bg-secondary hover:bg-secondary-dark text-white py-2 px-4 rounded-md">Import Programs</button>
                            </form>
                        </div>
                        
                        <!-- Reset Database -->
                        <div class="border-t pt-6">
                            <h4 class="font-medium mb-2 text-red-600">Reset Database</h4>
                            <p class="text-sm text-gray-600 mb-3">Warning: This will delete all data and recreate the database from scratch.</p>
                            <form method="POST" class="mt-2" onsubmit="return confirm('WARNING: This will DELETE ALL DATA in the database. This action cannot be undone. Are you absolutely sure?');">
                                <input type="hidden" name="action" value="reset_all">
                                <div class="mb-2">
                                    <input type="text" name="confirm_reset" class="w-full px-3 py-2 border rounded-md" placeholder="Type 'RESET' to confirm" required>
                                </div>
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-md">Reset Database</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8 py-4">
        <div class="container mx-auto px-4 text-center text-gray-600 text-sm">
            <p>KOSMO Foundation Admin Panel &copy; 2025. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>