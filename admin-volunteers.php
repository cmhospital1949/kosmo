<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

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
$volunteer = null;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Update volunteer status
        if ($_POST['action'] === 'update_status' && isset($_POST['id']) && isset($_POST['status'])) {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $notes = $_POST['notes'] ?? '';
            
            // Validate status
            $validStatuses = ['pending', 'approved', 'rejected', 'inactive'];
            if (!in_array($status, $validStatuses)) {
                $message = "Invalid status.";
                $messageType = 'error';
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE volunteers SET status = ?, notes = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$status, $notes, $id]);
                    $message = "Volunteer status updated successfully.";
                    $messageType = 'success';
                    
                    // Redirect to avoid form resubmission
                    header('Location: admin-volunteers.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                    exit;
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete volunteer
        elseif ($_POST['action'] === 'delete_volunteer' && isset($_POST['id'])) {
            $id = $_POST['id'];
            
            try {
                $stmt = $pdo->prepare("DELETE FROM volunteers WHERE id = ?");
                $stmt->execute([$id]);
                $message = "Volunteer deleted successfully.";
                $messageType = 'success';
                
                // Redirect to avoid form resubmission
                header('Location: admin-volunteers.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                exit;
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
        
        // Export volunteers to CSV
        elseif ($_POST['action'] === 'export_csv') {
            $status = $_POST['export_status'] ?? 'all';
            
            try {
                // Build query based on status
                $query = "SELECT id, name, email, phone, interests, skills, availability, background, reason, language, status, notes, created_at, updated_at FROM volunteers";
                $params = [];
                
                if ($status !== 'all') {
                    $query .= " WHERE status = ?";
                    $params[] = $status;
                }
                
                $query .= " ORDER BY created_at DESC";
                
                // Execute query
                $stmt = $pdo->prepare($query);
                $stmt->execute($params);
                $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Generate CSV
                if (count($volunteers) > 0) {
                    // Set headers for CSV download
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename=volunteers_' . date('Y-m-d') . '.csv');
                    
                    // Create a file pointer connected to the output stream
                    $output = fopen('php://output', 'w');
                    
                    // Output the column headings
                    fputcsv($output, array_keys($volunteers[0]));
                    
                    // Output each row of the data
                    foreach ($volunteers as $row) {
                        fputcsv($output, $row);
                    }
                    
                    // Close the file pointer
                    fclose($output);
                    exit;
                } else {
                    $message = "No volunteers found for export.";
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

// Get volunteer for viewing
if ($action === 'view' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM volunteers WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $volunteer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$volunteer) {
            $message = "Volunteer not found.";
            $messageType = 'error';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $message = "Error fetching volunteer: " . $e->getMessage();
        $messageType = 'error';
        $action = 'list';
    }
}

// Get volunteers for listing
$volunteers = [];
if ($action === 'list') {
    try {
        // Get filter parameters
        $status = isset($_GET['status']) ? $_GET['status'] : 'all';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Build query based on filters
        $query = "SELECT * FROM volunteers";
        $params = [];
        $whereClauses = [];
        
        if ($status !== 'all') {
            $whereClauses[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $whereClauses[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $params[] = '%' . $search . '%';
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
        $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get volunteer counts by status
        $countStmt = $pdo->query("SELECT status, COUNT(*) as count FROM volunteers GROUP BY status");
        $statusCounts = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'inactive' => 0
        ];
        while ($row = $countStmt->fetch(PDO::FETCH_ASSOC)) {
            $statusCounts[$row['status']] = $row['count'];
        }
        
        $totalCount = array_sum($statusCounts);
    } catch (PDOException $e) {
        $message = "Error fetching volunteers: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Function to calculate stats
function getVolunteerStats($pdo) {
    $stats = [
        'total' => 0,
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'inactive' => 0,
        'last_7_days' => 0,
        'last_30_days' => 0
    ];
    
    try {
        // Get total counts by status
        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM volunteers GROUP BY status");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $stats[$row['status']] = $row['count'];
            $stats['total'] += $row['count'];
        }
        
        // Get counts for recent volunteers
        $stmt = $pdo->query("SELECT COUNT(*) FROM volunteers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['last_7_days'] = $stmt->fetchColumn();
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM volunteers WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $stats['last_30_days'] = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error calculating stats: " . $e->getMessage());
    }
    
    return $stats;
}

// Get stats
$stats = getVolunteerStats($pdo);

// Function to get status badge class
function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'inactive':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-blue-100 text-blue-800';
    }
}

// Function to format date
function formatDate($date) {
    return date('Y-m-d H:i', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Management - KOSMO Foundation Admin</title>
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
            <h2 class="text-3xl font-semibold">
                <?php echo $action === 'view' ? 'Volunteer Details' : 'Volunteer Applications'; ?>
            </h2>
            
            <div class="flex space-x-3">
                <?php if ($action === 'view'): ?>
                    <a href="admin-volunteers.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to List</a>
                <?php else: ?>
                    <a href="admin.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to Dashboard</a>
                    <button onclick="openExportModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Export to CSV</button>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-lg font-semibold text-gray-700 mb-1">Total</div>
                    <div class="text-2xl font-bold text-blue-600"><?php echo $stats['total']; ?></div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-lg font-semibold text-gray-700 mb-1">Pending</div>
                    <div class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-lg font-semibold text-gray-700 mb-1">Approved</div>
                    <div class="text-2xl font-bold text-green-600"><?php echo $stats['approved']; ?></div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-lg font-semibold text-gray-700 mb-1">Last 7 Days</div>
                    <div class="text-2xl font-bold text-indigo-600"><?php echo $stats['last_7_days']; ?></div>
                </div>
                <div class="bg-white rounded-lg shadow-md p-4">
                    <div class="text-lg font-semibold text-gray-700 mb-1">Last 30 Days</div>
                    <div class="text-2xl font-bold text-purple-600"><?php echo $stats['last_30_days']; ?></div>
                </div>
            </div>
            
            <!-- Filter Options -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form action="admin-volunteers.php" method="GET" class="flex flex-wrap items-center">
                    <div class="mr-4 mb-2">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select id="status" name="status" class="px-3 py-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                            <option value="all" <?php echo (!isset($_GET['status']) || $_GET['status'] === 'all') ? 'selected' : ''; ?>>All Applications (<?php echo $stats['total']; ?>)</option>
                            <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending (<?php echo $stats['pending']; ?>)</option>
                            <option value="approved" <?php echo (isset($_GET['status']) && $_GET['status'] === 'approved') ? 'selected' : ''; ?>>Approved (<?php echo $stats['approved']; ?>)</option>
                            <option value="rejected" <?php echo (isset($_GET['status']) && $_GET['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected (<?php echo $stats['rejected']; ?>)</option>
                            <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive (<?php echo $stats['inactive']; ?>)</option>
                        </select>
                    </div>
                    <div class="mr-4 mb-2 flex-grow">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search by name, email, or phone" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-2 self-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
                        <a href="admin-volunteers.php" class="ml-2 text-blue-600 hover:text-blue-800">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Volunteers List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interests</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($volunteers) > 0): ?>
                            <?php foreach ($volunteers as $volunteer): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($volunteer['name']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo strtoupper($volunteer['language']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($volunteer['email']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($volunteer['phone'] ?: '-'); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500">
                                            <?php 
                                            $interestsArray = explode(', ', $volunteer['interests']);
                                            echo count($interestsArray) > 0 ? implode(', ', array_slice($interestsArray, 0, 2)) : '-';
                                            if (count($interestsArray) > 2) echo '...';
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo getStatusClass($volunteer['status']); ?>">
                                            <?php echo ucfirst($volunteer['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo formatDate($volunteer['created_at']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="admin-volunteers.php?action=view&id=<?php echo $volunteer['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                        <button onclick="openStatusModal(<?php echo $volunteer['id']; ?>, '<?php echo $volunteer['status']; ?>', '<?php echo addslashes($volunteer['notes'] ?? ''); ?>')" class="text-indigo-600 hover:text-indigo-900 mr-3">Status</button>
                                        <button onclick="confirmDelete(<?php echo $volunteer['id']; ?>, '<?php echo addslashes($volunteer['name']); ?>')" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No volunteer applications found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Status Modal -->
            <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Update Volunteer Status</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" id="statusId" name="id" value="">
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="statusSelect" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea id="notesInput" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
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
                    <p id="deleteMessage" class="mb-6">Are you sure you want to delete this volunteer?</p>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeDeleteModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_volunteer">
                            <input type="hidden" id="deleteId" name="id" value="">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Export Modal -->
            <div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Export Volunteers to CSV</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="export_csv">
                        <div class="mb-6">
                            <label for="export_status" class="block text-sm font-medium text-gray-700 mb-1">Volunteer Status</label>
                            <select id="export_status" name="export_status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="all">All Applications</option>
                                <option value="pending">Pending Only</option>
                                <option value="approved">Approved Only</option>
                                <option value="rejected">Rejected Only</option>
                                <option value="inactive">Inactive Only</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeExportModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Export to CSV</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php elseif ($action === 'view' && $volunteer): ?>
            <!-- Volunteer Details -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-2xl font-semibold"><?php echo htmlspecialchars($volunteer['name']); ?></h3>
                            <div class="text-gray-600">
                                <span class="inline-block mr-4">
                                    <span class="font-medium">Email:</span> <?php echo htmlspecialchars($volunteer['email']); ?>
                                </span>
                                <?php if ($volunteer['phone']): ?>
                                    <span class="inline-block">
                                        <span class="font-medium">Phone:</span> <?php echo htmlspecialchars($volunteer['phone']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full <?php echo getStatusClass($volunteer['status']); ?>">
                                <?php echo ucfirst($volunteer['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-lg font-medium mb-2">Application Details</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p><span class="font-medium">Submitted On:</span> <?php echo formatDate($volunteer['created_at']); ?></p>
                                <p><span class="font-medium">Language:</span> <?php echo strtoupper($volunteer['language']); ?></p>
                                <?php if ($volunteer['updated_at']): ?>
                                    <p><span class="font-medium">Last Updated:</span> <?php echo formatDate($volunteer['updated_at']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-medium mb-2">Interests</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <?php 
                                $interestsArray = explode(', ', $volunteer['interests']);
                                if (count($interestsArray) > 0): 
                                ?>
                                    <ul class="list-disc list-inside">
                                        <?php foreach ($interestsArray as $interest): ?>
                                            <li><?php echo htmlspecialchars($interest); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="text-gray-500">No interests specified</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-lg font-medium mb-2">Skills & Qualifications</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <?php if ($volunteer['skills']): ?>
                                    <p><?php echo nl2br(htmlspecialchars($volunteer['skills'])); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500">No skills specified</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-medium mb-2">Availability</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <?php if ($volunteer['availability']): ?>
                                    <p><?php echo nl2br(htmlspecialchars($volunteer['availability'])); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500">No availability specified</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-lg font-medium mb-2">Background</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <?php if ($volunteer['background']): ?>
                                    <p><?php echo nl2br(htmlspecialchars($volunteer['background'])); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500">No background information provided</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-lg font-medium mb-2">Reason for Volunteering</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <?php if ($volunteer['reason']): ?>
                                    <p><?php echo nl2br(htmlspecialchars($volunteer['reason'])); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500">No reason provided</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <h4 class="text-lg font-medium mb-2">Administrative Notes</h4>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <?php if ($volunteer['notes']): ?>
                                <p><?php echo nl2br(htmlspecialchars($volunteer['notes'])); ?></p>
                            <?php else: ?>
                                <p class="text-gray-500">No administrative notes</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="admin-volunteers.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to List</a>
                        <div class="flex space-x-3">
                            <button onclick="openStatusModal(<?php echo $volunteer['id']; ?>, '<?php echo $volunteer['status']; ?>', '<?php echo addslashes($volunteer['notes'] ?? ''); ?>')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Update Status</button>
                            <button onclick="confirmDelete(<?php echo $volunteer['id']; ?>, '<?php echo addslashes($volunteer['name']); ?>')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Status Modal -->
            <div id="statusModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Update Volunteer Status</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" id="statusId" name="id" value="">
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="statusSelect" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea id="notesInput" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
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
                    <p id="deleteMessage" class="mb-6">Are you sure you want to delete this volunteer?</p>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeDeleteModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_volunteer">
                            <input type="hidden" id="deleteId" name="id" value="">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Status Modal
        function openStatusModal(id, status, notes) {
            document.getElementById('statusId').value = id;
            document.getElementById('statusSelect').value = status;
            document.getElementById('notesInput').value = notes;
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
        
        // Delete Modal
        function confirmDelete(id, name) {
            document.getElementById('deleteMessage').textContent = `Are you sure you want to delete the volunteer application for "${name}"?`;
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