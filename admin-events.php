<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// Database connection
function connect_db() {
    $host = 'localhost';
    $dbname = 'bestluck';
    $username = 'bestluck';
    $password = 'Nocpriss12!';
    
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

// Initialize variables
$message = '';
$messageType = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$event = null;

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Create or update event
        if ($_POST['action'] === 'save_event') {
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $title = $_POST['title'] ?? '';
            $ko_title = $_POST['ko_title'] ?? '';
            $description = $_POST['description'] ?? '';
            $ko_description = $_POST['ko_description'] ?? '';
            $location = $_POST['location'] ?? '';
            $ko_location = $_POST['ko_location'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '00:00';
            $end_date = $_POST['end_date'] ?? '';
            $end_time = $_POST['end_time'] ?? '00:00';
            $all_day = isset($_POST['all_day']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;
            $registration_url = $_POST['registration_url'] ?? '';
            $image_url = $_POST['image_url'] ?? '';
            
            // Format date/time
            $start_datetime = date('Y-m-d H:i:s', strtotime("$start_date $start_time"));
            $end_datetime = !empty($end_date) ? date('Y-m-d H:i:s', strtotime("$end_date $end_time")) : null;
            
            // Validate required fields
            if (empty($title) || empty($ko_title) || empty($start_date)) {
                $message = "Please fill in all required fields.";
                $messageType = 'error';
            } else {
                try {
                    if ($id) {
                        // Update existing event
                        $stmt = $pdo->prepare("UPDATE events SET 
                            title = ?, ko_title = ?, description = ?, ko_description = ?, 
                            location = ?, ko_location = ?, start_date = ?, end_date = ?, 
                            all_day = ?, featured = ?, registration_url = ?, image_url = ?,
                            updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $title, $ko_title, $description, $ko_description, 
                            $location, $ko_location, $start_datetime, $end_datetime, 
                            $all_day, $featured, $registration_url, $image_url, $id
                        ]);
                        $message = "Event updated successfully.";
                        $messageType = 'success';
                    } else {
                        // Create new event
                        $stmt = $pdo->prepare("INSERT INTO events 
                            (title, ko_title, description, ko_description, location, ko_location, 
                            start_date, end_date, all_day, featured, registration_url, image_url) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $title, $ko_title, $description, $ko_description, 
                            $location, $ko_location, $start_datetime, $end_datetime, 
                            $all_day, $featured, $registration_url, $image_url
                        ]);
                        $message = "Event created successfully.";
                        $messageType = 'success';
                        
                        // Redirect to list after successful creation
                        header('Location: admin-events.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                        exit;
                    }
                } catch (PDOException $e) {
                    $message = "Database error: " . $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
        
        // Delete event
        elseif ($_POST['action'] === 'delete_event' && isset($_POST['id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = "Event deleted successfully.";
                $messageType = 'success';
                
                // Redirect to list after successful deletion
                header('Location: admin-events.php?message=' . urlencode($message) . '&messageType=' . $messageType);
                exit;
            } catch (PDOException $e) {
                $message = "Error deleting event: " . $e->getMessage();
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

// Get event for editing
if ($action === 'edit' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$event) {
            $message = "Event not found.";
            $messageType = 'error';
            $action = 'list';
        }
    } catch (PDOException $e) {
        $message = "Error fetching event: " . $e->getMessage();
        $messageType = 'error';
        $action = 'list';
    }
}

// Get events for listing
$events = [];
if ($action === 'list') {
    try {
        // Get filter parameters
        $period = isset($_GET['period']) ? $_GET['period'] : 'upcoming';
        $featured = isset($_GET['featured']) ? $_GET['featured'] : 'all';
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        // Build query based on filters
        $query = "SELECT * FROM events";
        $params = [];
        $whereClauses = [];
        
        if ($period === 'upcoming') {
            $whereClauses[] = "start_date >= ?";
            $params[] = date('Y-m-d');
        } elseif ($period === 'past') {
            $whereClauses[] = "start_date < ?";
            $params[] = date('Y-m-d');
        }
        
        if ($featured === 'featured') {
            $whereClauses[] = "featured = 1";
        }
        
        if (!empty($search)) {
            $whereClauses[] = "(title LIKE ? OR ko_title LIKE ? OR description LIKE ? OR ko_description LIKE ?)";
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }
        
        if (!empty($whereClauses)) {
            $query .= " WHERE " . implode(' AND ', $whereClauses);
        }
        
        $query .= " ORDER BY start_date ASC";
        
        // Execute query
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get event counts
        $totalCount = count($events);
        
        // Get featured count
        $featuredStmt = $pdo->query("SELECT COUNT(*) FROM events WHERE featured = 1");
        $featuredCount = $featuredStmt->fetchColumn();
        
        // Get upcoming count
        $upcomingStmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE start_date >= ?");
        $upcomingStmt->execute([date('Y-m-d')]);
        $upcomingCount = $upcomingStmt->fetchColumn();
        
        // Get past count
        $pastStmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE start_date < ?");
        $pastStmt->execute([date('Y-m-d')]);
        $pastCount = $pastStmt->fetchColumn();
    } catch (PDOException $e) {
        $message = "Error fetching events: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Function to format date for display
function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

// Function to format time for display
function formatTime($date) {
    return date('H:i', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - KOSMO Foundation Admin</title>
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
                <?php 
                if ($action === 'add') echo 'Add New Event';
                elseif ($action === 'edit') echo 'Edit Event';
                else echo 'Event Management';
                ?>
            </h2>
            
            <div class="flex space-x-3">
                <?php if ($action !== 'list'): ?>
                    <a href="admin-events.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to List</a>
                <?php else: ?>
                    <a href="admin.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Back to Dashboard</a>
                    <a href="admin-events.php?action=add" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Add New Event</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="mb-6 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list'): ?>
            <!-- Filter Options -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form action="admin-events.php" method="GET" class="flex flex-wrap items-center">
                    <div class="mr-4 mb-2">
                        <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Time Period</label>
                        <select id="period" name="period" class="px-3 py-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                            <option value="all" <?php echo (!isset($_GET['period']) || $_GET['period'] === 'all') ? 'selected' : ''; ?>>All Events (<?php echo $totalCount; ?>)</option>
                            <option value="upcoming" <?php echo (isset($_GET['period']) && $_GET['period'] === 'upcoming') ? 'selected' : ''; ?>>Upcoming Events (<?php echo $upcomingCount; ?>)</option>
                            <option value="past" <?php echo (isset($_GET['period']) && $_GET['period'] === 'past') ? 'selected' : ''; ?>>Past Events (<?php echo $pastCount; ?>)</option>
                        </select>
                    </div>
                    <div class="mr-4 mb-2">
                        <label for="featured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                        <select id="featured" name="featured" class="px-3 py-2 border border-gray-300 rounded-md" onchange="this.form.submit()">
                            <option value="all" <?php echo (!isset($_GET['featured']) || $_GET['featured'] === 'all') ? 'selected' : ''; ?>>All Events</option>
                            <option value="featured" <?php echo (isset($_GET['featured']) && $_GET['featured'] === 'featured') ? 'selected' : ''; ?>>Featured Only (<?php echo $featuredCount; ?>)</option>
                        </select>
                    </div>
                    <div class="mr-4 mb-2 flex-grow">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" placeholder="Search events" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div class="mb-2 self-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Filter</button>
                        <a href="admin-events.php" class="ml-2 text-blue-600 hover:text-blue-800">Reset</a>
                    </div>
                </form>
            </div>
            
            <!-- Events List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (count($events) > 0): ?>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($event['title']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($event['ko_title']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php echo formatDate($event['start_date']); ?>
                                            <?php if (!$event['all_day']): ?>
                                                <?php echo formatTime($event['start_date']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($event['end_date']): ?>
                                            <div class="text-xs text-gray-500">
                                                to <?php echo formatDate($event['end_date']); ?>
                                                <?php if (!$event['all_day']): ?>
                                                    <?php echo formatTime($event['end_date']); ?>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($event['all_day']): ?>
                                            <div class="text-xs text-gray-500">All Day</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($event['location']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($event['ko_location']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (strtotime($event['start_date']) > time()): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Upcoming</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Past</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($event['featured']): ?>
                                            <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Featured</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="admin-events.php?action=edit&id=<?php echo $event['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <button onclick="confirmDelete(<?php echo $event['id']; ?>, '<?php echo addslashes($event['title']); ?>')" class="text-red-600 hover:text-red-900">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No events found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
                <div class="bg-white rounded-lg p-8 max-w-md mx-auto">
                    <h3 class="text-lg font-semibold mb-4">Confirm Deletion</h3>
                    <p id="deleteMessage" class="mb-6">Are you sure you want to delete this event?</p>
                    <div class="flex justify-end space-x-3">
                        <button onclick="closeDeleteModal()" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Cancel</button>
                        <form method="POST">
                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" id="deleteId" name="id" value="">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Event Form -->
            <form method="POST" class="bg-white rounded-lg shadow-md overflow-hidden p-6">
                <input type="hidden" name="action" value="save_event">
                <?php if ($action === 'edit' && $event): ?>
                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title (English) *</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="ko_title" class="block text-sm font-medium text-gray-700 mb-1">Title (Korean) *</label>
                        <input type="text" id="ko_title" name="ko_title" value="<?php echo htmlspecialchars($event['ko_title'] ?? ''); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (English)</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-6">
                    <label for="ko_description" class="block text-sm font-medium text-gray-700 mb-1">Description (Korean)</label>
                    <textarea id="ko_description" name="ko_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"><?php echo htmlspecialchars($event['ko_description'] ?? ''); ?></textarea>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location (English)</label>
                        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="ko_location" class="block text-sm font-medium text-gray-700 mb-1">Location (Korean)</label>
                        <input type="text" id="ko_location" name="ko_location" value="<?php echo htmlspecialchars($event['ko_location'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <div class="flex items-center mb-2">
                            <input type="checkbox" id="all_day" name="all_day" <?php echo (isset($event['all_day']) && $event['all_day']) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded" onchange="toggleTimeFields()">
                            <label for="all_day" class="ml-2 block text-sm text-gray-700">All Day Event</label>
                        </div>
                        
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" id="start_date" name="start_date" value="<?php echo $event ? formatDate($event['start_date']) : date('Y-m-d'); ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <input type="time" id="start_time" name="start_time" value="<?php echo $event ? formatTime($event['start_date']) : '09:00'; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="mb-4"></div> <!-- Spacer for alignment -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="date" id="end_date" name="end_date" value="<?php echo ($event && $event['end_date']) ? formatDate($event['end_date']) : ''; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <input type="time" id="end_time" name="end_time" value="<?php echo ($event && $event['end_date']) ? formatTime($event['end_date']) : '17:00'; ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-6">
                    <label for="registration_url" class="block text-sm font-medium text-gray-700 mb-1">Registration URL</label>
                    <input type="url" id="registration_url" name="registration_url" value="<?php echo htmlspecialchars($event['registration_url'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-xs text-gray-500 mt-1">Leave empty if no registration is required</p>
                </div>
                
                <div class="mb-6">
                    <label for="image_url" class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                    <input type="url" id="image_url" name="image_url" value="<?php echo htmlspecialchars($event['image_url'] ?? ''); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for no image</p>
                </div>
                
                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="featured" name="featured" <?php echo (isset($event['featured']) && $event['featured']) ? 'checked' : ''; ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        <label for="featured" class="ml-2 block text-sm text-gray-700">Featured Event (displayed prominently on the website)</label>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <a href="admin-events.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save Event</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        // Delete confirmation
        function confirmDelete(id, title) {
            document.getElementById('deleteMessage').textContent = `Are you sure you want to delete the event "${title}"?`;
            document.getElementById('deleteId').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }
        
        // Toggle time fields based on all-day checkbox
        function toggleTimeFields() {
            const allDayCheckbox = document.getElementById('all_day');
            const startTimeField = document.getElementById('start_time');
            const endTimeField = document.getElementById('end_time');
            
            startTimeField.disabled = allDayCheckbox.checked;
            endTimeField.disabled = allDayCheckbox.checked;
            
            if (allDayCheckbox.checked) {
                startTimeField.value = '00:00';
                endTimeField.value = '23:59';
            } else {
                startTimeField.value = '09:00';
                endTimeField.value = '17:00';
            }
        }
        
        // Initialize time fields
        document.addEventListener('DOMContentLoaded', function() {
            const allDayCheckbox = document.getElementById('all_day');
            if (allDayCheckbox) {
                toggleTimeFields();
            }
        });
    </script>
</body>
</html>