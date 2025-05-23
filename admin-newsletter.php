<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// Database connection
require_once __DIR__ . '/lib/Database.php';

function connect_db() {
    try {
        return Database::getConnection();
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

$pdo = connect_db();

// Initialize variables
$message = '';
$messageType = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$subscribers = [];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Add new subscriber
        if ($_POST['action'] === 'add_subscriber') {
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
            $language = $_POST['language'] ?? 'en';
            
            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email address.";
                $messageType = 'error';
            } else {
                try {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT * FROM newsletter_subscribers WHERE email = ?");
                    $stmt->execute([$email]);
                    $subscriber = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($subscriber) {
                        // Email already exists, update record
                        $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET name = ?, language = ?, status = 'active', updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$name, $language, $subscriber['id']]);
                        $message = "Subscriber updated successfully.";
                        $messageType = 'success';
                    } else {
                        // New subscriber
                        $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, name, language) VALUES (?, ?, ?)");
                        $stmt->execute([$email, $name, $language]);
                        $message = "Subscriber added successfully.";
                        $messageType = 'success';
                    }
                    
                    // Redirect to avoid form resubmission
                    header('Location: admin-newsletter.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                    exit;
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Update subscriber status
        elseif ($_POST['action'] === 'update_status' && isset($_POST['id']) && isset($_POST['status'])) {
            $id = $_POST['id'];
            $status = $_POST['status'];
            
            // Validate status
            $validStatuses = ['active', 'unsubscribed', 'bounced'];
            if (!in_array($status, $validStatuses)) {
                $message = "Invalid status.";
                $messageType = 'error';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE newsletter_subscribers SET status = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $id]);
                    $message = "Subscriber status updated successfully.";
                    $messageType = 'success';
                    
                    // Redirect to avoid form resubmission
                    header('Location: admin-newsletter.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                    exit;
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete subscriber
        elseif ($_POST['action'] === 'delete_subscriber' && isset($_POST['id'])) {
            $id = $_POST['id'];
            
            try {
                $stmt = $pdo->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Subscriber deleted successfully.";
                $messageType = 'success';
                
                // Redirect to avoid form resubmission
                header('Location: admin-newsletter.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                exit;
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
        
        // Export subscribers to CSV
        elseif ($_POST['action'] === 'export_csv') {
            $status = $_POST['export_status'] ?? 'all';
            
            try {
                // Build query based on status
                $query = "SELECT id, email, name, language, status, created_at, updated_at FROM newsletter_subscribers";
                $params = [];
                
                if ($status !== 'all') {
                    $query .= " WHERE status = ?";
                    $params[] = $status;
                }
                
                $query .= " ORDER BY created_at DESC";
                
                // Execute query
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Generate CSV
                if (count($subscribers) > 0) {
                    // Set headers for CSV download
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename=newsletter_subscribers_' . date('Y-m-d') . '.csv');
                    
                    // Create a file pointer connected to the output stream
                    $output = fopen('php://output', 'w');
                    
                    // Output the column headings
                    fputcsv($output, array_keys($subscribers[0]));
                    
                    // Output each row of the data
                    foreach ($subscribers as $row) {
                        fputcsv($output, $row);
                    }
                    
                    // Close the file pointer
                    fclose($output);
                    exit;
                } else {
                    $message = "No subscribers found for export.";
                    $messageType = 'error';
                }
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

// Handle URL message parameters
if (isset($_GET['message']) && isset($_GET['messageType'])) {
    $message = $_GET['message'];
    $messageType = $_GET['messageType'];
}

// Get subscribers for listing
if ($action === 'list') {
    try {
        // Get filter parameters
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Build query based on filters
        $query = "SELECT * FROM newsletter_subscribers";
        $params = [];
        $whereClauses = [];
        
        if ($status !== 'all') {
            $whereClauses[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $whereClauses[] = "(email LIKE ? OR name LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $query .= " ORDER BY created_at DESC";
        
        // Execute query
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get subscriber counts by status
        $countStmt = $pdo->query("SELECT status, COUNT(*) as count FROM newsletter_subscribers GROUP BY status");
        $statusCounts = [];
        while ($row = $countStmt->fetch(PDO::FETCH_ASSOC)) {
            $statusCounts[$row['status']] = $row['count'];
        }
        
        $totalCount = array_sum($statusCounts);
    } catch (PDOException $e) {
        $message = "Error fetching subscribers: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Function to calculate stats
function getSubscriberStats($pdo) {
    $stats = [
        'total' => 0,
        'active' => 0,
        'unsubscribed' => 0,
        'bounced' => 0,
        'last_7_days' => 0,
        'last_30_days' => 0
    ];
    
    try {
        // Get total counts by status
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM newsletter_subscribers GROUP BY status");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
            $stats['total'] += $row['count'];
        }
        
        // Get counts for recent subscribers
        $stmt = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['last_7_days'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM newsletter_subscribers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['last_30_days'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error calculating stats: " . $e->getMessage());
    }
    
    return $stats;
}

// Get stats
$stats = getSubscriberStats($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Management - KOSMO Foundation Admin</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-blue-600 text-white py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold">KOSMO Foundation Admin</h1>
            <div class="flex items-center space-x-4">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                <a href="admin.php" class="text-blue-200 hover:text-white">Dashboard</a>
                <a href="admin.php?logout=1" class="text-blue-200 hover:text-white">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold">Newsletter Subscribers</h2>
            
            <div class="flex space-x-3">
                <a href="admin.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to Dashboard</a>
                <button onclick="openAddModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add Subscriber</button>
                <button onclick="openExportModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">Export to CSV</button>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-xl font-semibold text-gray-700 mb-2">Total Subscribers</div>
                <div class="text-3xl font-bold text-blue-600"><?php echo $stats['total']; ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-xl font-semibold text-gray-700 mb-2">Active Subscribers</div>
                <div class="text-3xl font-bold text-green-600"><?php echo $stats['active']; ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-xl font-semibold text-gray-700 mb-2">Last 7 Days</div>
                <div class="text-3xl font-bold text-indigo-600"><?php echo $stats['last_7_days']; ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="text-xl font-semibold text-gray-700 mb-2">Last 30 Days</div>
                <div class="text-3xl font-bold text-purple-600"><?php echo $stats['last_30_days']; ?></div>
            </div>
        </div>
        
        <!-- Filter Options -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form action="admin-newsletter.php" method="GET" class="flex flex-wrap items-center">
                <div class="mr-4 mb-2">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                    <select id="status" name="status" class="px-3 py-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                        <option value="all" <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'selected' : ''; ?>>All Subscribers (<?php echo $stats['total']; ?>)</option>
                        <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active (<?php echo $stats['active']; ?>)</option>
                        <option value="unsubscribed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'unsubscribed') ? 'selected' : ''; ?>>Unsubscribed (<?php echo $stats['unsubscribed']; ?>)</option>
                        <option value="bounced" <?php echo (isset($_GET['status']) && $_GET['status'] === 'bounced') ? 'selected' : ''; ?>>Bounced (<?php echo $stats['bounced']; ?>)</option>
                    </select>
                </div>
                <div class="mr-4 mb-2 flex-grow">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search by email or name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-2 self-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
                    <a href="admin-newsletter.php" class="ml-2 text-blue-600 hover:text-blue-800">Reset</a>
                </div>
            </form>
        </div>
        
        <!-- Subscribers List -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Language</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subscribed Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($subscribers) > 0): ?>
                        <?php foreach ($subscribers as $subscriber): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($subscriber['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($subscriber['name'] ?: '-'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo strtoupper($subscriber['language']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($subscriber['status'] === 'active'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                    <?php elseif ($subscriber['status'] === 'unsubscribed'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Unsubscribed</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Bounced</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('Y-m-d', strtotime($subscriber['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openStatusModal(<?php echo $subscriber['id']; ?>, '<?php echo $subscriber['status']; ?>')" class="text-indigo-600 hover:text-indigo-900 mr-3">Change Status</button>
                                    <button onclick="confirmDelete(<?php echo $subscriber['id']; ?>, '<?php echo addslashes($subscriber['email']); ?>')" class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No subscribers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Add Subscriber Modal -->
    <div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-lg font-semibold mb-4">Add New Subscriber</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_subscriber">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="language" class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                    <select id="language" name="language" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="en">English</option>
                        <option value="ko">Korean</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeAddModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add Subscriber</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Change Status Modal -->
    <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-lg font-semibold mb-4">Change Subscriber Status</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" id="statusId" name="id" value="">
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="statusSelect" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="active">Active</option>
                        <option value="unsubscribed">Unsubscribed</option>
                        <option value="bounced">Bounced</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeStatusModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
            <p id="deleteMessage" class="mb-6">Are you sure you want to delete this subscriber?</p>
            <div class="flex justify-end space-x-3">
                <button onclick="closeDeleteModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                <form method="POST">
                    <input type="hidden" name="action" value="delete_subscriber">
                    <input type="hidden" id="deleteId" name="id" value="">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Export Modal -->
    <div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
            <h3 class="text-lg font-semibold mb-4">Export Subscribers to CSV</h3>
            <form method="POST">
                <input type="hidden" name="action" value="export_csv">
                <div class="mb-6">
                    <label for="export_status" class="block text-sm font-medium text-gray-700 mb-1">Subscriber Status</label>
                    <select id="export_status" name="export_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="all">All Subscribers</option>
                        <option value="active">Active Subscribers Only</option>
                        <option value="unsubscribed">Unsubscribed Only</option>
                        <option value="bounced">Bounced Only</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeExportModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Export to CSV</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Add Subscriber Modal
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }
        
        // Status Modal
        function openStatusModal(id, status) {
            document.getElementById('statusId').value = id;
            document.getElementById('statusSelect').value = status;
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
        
        // Delete Modal
        function confirmDelete(id, email) {
            document.getElementById('deleteMessage').textContent = `Are you sure you want to delete subscriber "${email}"?`;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        // Export Modal
        function openExportModal() {
            document.getElementById('exportModal').classList.remove('hidden');
        }
        
        function closeExportModal() {
            document.getElementById('exportModal').classList.add('hidden');
        }
    </script>
</body>
</html>