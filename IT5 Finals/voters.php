<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

$db = getDB();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_voter':
                $stmt = $db->prepare("INSERT INTO voters (voter_id, name, email, phone, dob, status) VALUES (?, ?, ?, ?, ?, ?)");
                try {
                    $stmt->execute([
                        $_POST['voter_id'],
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['dob'],
                        $_POST['status']
                    ]);
                    $message = 'Voter added successfully';
                } catch (PDOException $e) {
                    $error = 'Error adding voter: ' . $e->getMessage();
                }
                break;
                
            case 'update_voter':
                $stmt = $db->prepare("UPDATE voters SET voter_id = ?, name = ?, email = ?, phone = ?, dob = ?, status = ? WHERE id = ?");
                try {
                    $stmt->execute([
                        $_POST['voter_id'],
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['phone'],
                        $_POST['dob'],
                        $_POST['status'],
                        $_POST['id']
                    ]);
                    $message = 'Voter updated successfully';
                } catch (PDOException $e) {
                    $error = 'Error updating voter';
                }
                break;
                
            case 'delete_voter':
                $stmt = $db->prepare("DELETE FROM voters WHERE id = ?");
                try {
                    $stmt->execute([$_POST['id']]);
                    $message = 'Voter deleted successfully';
                } catch (PDOException $e) {
                    $error = 'Error deleting voter';
                }
                break;
                
            case 'import_voters':
                $voters = json_decode($_POST['voters_json'], true);
                if ($voters && is_array($voters)) {
                    $success = 0;
                    $failed = 0;
                    foreach ($voters as $voter) {
                        $stmt = $db->prepare("INSERT OR IGNORE INTO voters (voter_id, name, email, phone, dob, status) VALUES (?, ?, ?, ?, ?, ?)");
                        try {
                            $stmt->execute([
                                $voter['voter_id'],
                                $voter['name'],
                                $voter['email'] ?? null,
                                $voter['phone'] ?? null,
                                $voter['dob'] ?? null,
                                $voter['status'] ?? 'unverified'
                            ]);
                            $success++;
                        } catch (PDOException $e) {
                            $failed++;
                        }
                    }
                    $message = "Imported $success voters" . ($failed > 0 ? ", $failed failed" : "");
                } else {
                    $error = 'Invalid JSON format';
                }
                break;
                
            case 'bulk_status':
                $ids = explode(',', $_POST['selected_ids']);
                $stmt = $db->prepare("UPDATE voters SET status = ? WHERE id = ?");
                foreach ($ids as $id) {
                    $stmt->execute([$_POST['new_status'], $id]);
                }
                $message = 'Status updated for ' . count($ids) . ' voters';
                break;
                
            case 'bulk_delete':
                $ids = explode(',', $_POST['selected_ids']);
                $stmt = $db->prepare("DELETE FROM voters WHERE id = ?");
                foreach ($ids as $id) {
                    $stmt->execute([$id]);
                }
                $message = 'Deleted ' . count($ids) . ' voters';
                break;
        }
    }
}

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'all';
$has_voted = isset($_GET['has_voted']) ? $_GET['has_voted'] : 'all';

// Build query
$sql = "SELECT * FROM voters WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (voter_id LIKE ? OR name LIKE ? OR email LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if ($has_voted !== 'all') {
    $sql .= " AND has_voted = ?";
    $params[] = $has_voted;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$voters = $stmt->fetchAll();

// Get voter for editing
$edit_voter = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM voters WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_voter = $stmt->fetch();
}

// Get statistics
$stats = [
    'total' => $db->query("SELECT COUNT(*) FROM voters")->fetchColumn(),
    'verified' => $db->query("SELECT COUNT(*) FROM voters WHERE status = 'verified'")->fetchColumn(),
    'unverified' => $db->query("SELECT COUNT(*) FROM voters WHERE status = 'unverified'")->fetchColumn(),
    'voted' => $db->query("SELECT COUNT(*) FROM voters WHERE has_voted = 1")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Management - Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); border-right: 3px solid #fff; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 text-white flex flex-col overflow-y-auto" style="background: linear-gradient(180deg, #1565C0 0%, #1e90ff 100%);">
            <div class="p-6 border-b border-blue-400 border-opacity-40">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white-400"></i>
                    <div>
                        <h1 class="font-bold text-lg">ADMIN</h1>
                    </div>
                </div>
            </div>

            <nav class="flex-1 py-6 space-y-1">
                <a href="admin_dashboard.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Dashboard
                </a>
                <a href="elections.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="vote" class="w-5 h-5"></i>
                    Elections
                </a>
                <a href="ballot_builder.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Ballot Builder
                </a>
                <a href="voters.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    Voter Management
                </a>
                <a href="admin_results.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    Results & Reports
                </a>
                <a href="logs.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    Security Logs
                </a>
                <a href="users.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="user-cog" class="w-5 h-5"></i>
                    User Management
                </a>
                <a href="notifications.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                    Notifications
                </a>
            </nav>

            <div class="p-4 border-t border-blue-400 border-opacity-40">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-bold">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="text-xs text-slate-400 capitalize"><?php echo $_SESSION['role']; ?></p>
                    </div>
                    <a href="?logout=1" class="text-slate-400 hover:text-white">
                        <i data-lucide="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto">
            <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
                <div class="px-8 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Voter Management</h2>
                        <p class="text-sm text-gray-500">Manage registered voters and their status</p>
                    </div>
                    <button onclick="document.getElementById('voterModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Add Voter
                    </button>
                </div>
            </header>

            <div class="p-8">
                <?php if ($message): ?>
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <?php if ($error): ?>
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Total Voters</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total']); ?></p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Verified</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['verified']); ?></p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Unverified</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo number_format($stats['unverified']); ?></p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Has Voted</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['voted']); ?></p>
                    </div>
                </div>

                <!-- Filters and Actions -->
                <div class="mb-6 flex flex-wrap items-center gap-4">
                    <form method="GET" class="flex-1 min-w-0 flex gap-3">
                        <div class="flex-1 relative">
                            <i data-lucide="search" class="absolute left-3 top-3 w-4 h-4 text-gray-400"></i>
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search voters..." 
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <select name="status_filter" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="verified" <?php echo $status_filter === 'verified' ? 'selected' : ''; ?>>Verified</option>
                            <option value="unverified" <?php echo $status_filter === 'unverified' ? 'selected' : ''; ?>>Unverified</option>
                        </select>
                        <select name="has_voted" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="all" <?php echo $has_voted === 'all' ? 'selected' : ''; ?>>All Voters</option>
                            <option value="1" <?php echo $has_voted === '1' ? 'selected' : ''; ?>>Has Voted</option>
                            <option value="0" <?php echo $has_voted === '0' ? 'selected' : ''; ?>>Not Voted</option>
                        </select>
                    </form>
                    
                    <div class="flex gap-2">
                        <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                            <i data-lucide="upload" class="w-4 h-4"></i>
                            Import
                        </button>
                        <button onclick="exportToCSV()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Export CSV
                        </button>
                        <button onclick="document.getElementById('bulkActionModal').classList.remove('hidden')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700" id="bulkActionBtn" disabled>
                            Bulk Action (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>

                <!-- Voters Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Voter ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Voted</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($voters as $voter): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    <input type="checkbox" class="voter-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" value="<?php echo $voter['id']; ?>">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-900"><?php echo htmlspecialchars($voter['voter_id']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($voter['name']); ?></div>
                                    <?php if ($voter['dob']): ?>
                                    <div class="text-xs text-gray-500">DOB: <?php echo $voter['dob']; ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo $voter['email'] ? htmlspecialchars($voter['email']) : '-'; ?><br>
                                    <?php echo $voter['phone'] ? htmlspecialchars($voter['phone']) : '-'; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $voter['status'] === 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($voter['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($voter['has_voted']): ?>
                                    <span class="flex items-center gap-1 text-green-600">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        <span class="text-sm">Yes</span>
                                    </span>
                                    <?php else: ?>
                                    <span class="flex items-center gap-1 text-gray-400">
                                        <i data-lucide="circle" class="w-4 h-4"></i>
                                        <span class="text-sm">No</span>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <a href="?edit=<?php echo $voter['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Delete this voter?');">
                                            <input type="hidden" name="action" value="delete_voter">
                                            <input type="hidden" name="id" value="<?php echo $voter['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Voter Modal -->
    <div id="voterModal" class="<?php echo $edit_voter ? 'fixed' : 'hidden'; ?> inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900"><?php echo $edit_voter ? 'Edit Voter' : 'Add New Voter'; ?></h3>
                <button onclick="window.location.href='voters.php'" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_voter ? 'update_voter' : 'add_voter'; ?>">
                <?php if ($edit_voter): ?>
                <input type="hidden" name="id" value="<?php echo $edit_voter['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Voter ID</label>
                    <input type="text" name="voter_id" required value="<?php echo $edit_voter ? htmlspecialchars($edit_voter['voter_id']) : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required value="<?php echo $edit_voter ? htmlspecialchars($edit_voter['name']) : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo $edit_voter ? htmlspecialchars($edit_voter['email']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="<?php echo $edit_voter ? htmlspecialchars($edit_voter['phone']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="<?php echo $edit_voter && $edit_voter['dob'] ? $edit_voter['dob'] : ''; ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="unverified" <?php echo ($edit_voter && $edit_voter['status'] === 'unverified') ? 'selected' : ''; ?>>Unverified</option>
                        <option value="verified" <?php echo ($edit_voter && $edit_voter['status'] === 'verified') ? 'selected' : ''; ?>>Verified</option>
                    </select>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="window.location.href='voters.php'" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <?php echo $edit_voter ? 'Update Voter' : 'Add Voter'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="importModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Import Voters</h3>
                <button onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="import_voters">
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                    <p class="font-medium mb-1">Import Format (JSON):</p>
                    <pre class="bg-white rounded p-2 overflow-x-auto text-xs">[
  {"voter_id": "V001", "name": "John Doe", "email": "john@example.com", "phone": "1234567890", "dob": "1990-01-01", "status": "verified"},
  {"voter_id": "V002", "name": "Jane Smith", "email": "jane@example.com", "phone": "0987654321", "dob": "1985-05-15", "status": "unverified"}
]</pre>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">JSON Data</label>
                    <textarea name="voters_json" rows="10" required placeholder="Paste JSON array here..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"></textarea>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Import Voters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bulk Action Modal -->
    <div id="bulkActionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Bulk Actions</h3>
                <button onclick="document.getElementById('bulkActionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <form method="POST" class="space-y-4" onsubmit="return prepareBulkAction('bulk_status');">
                    <input type="hidden" name="action" value="bulk_status">
                    <input type="hidden" name="selected_ids" id="bulkStatusIds">
                    
                    <p class="text-sm text-gray-600">Change status of <span id="bulkCount">0</span> selected voters to:</p>
                    
                    <select name="new_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                    
                    <div class="pt-4 flex gap-3">
                        <button type="button" onclick="document.getElementById('bulkActionModal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Update Status
                        </button>
                    </div>
                </form>
                
                <hr class="border-gray-200">
                
                <form method="POST" class="space-y-4" onsubmit="return confirm('Are you sure? This cannot be undone.') && prepareBulkAction('bulk_delete');">
                    <input type="hidden" name="action" value="bulk_delete">
                    <input type="hidden" name="selected_ids" id="bulkDeleteIds">
                    
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        Delete Selected Voters
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        // Checkbox handling
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.voter-checkbox');
        const bulkActionBtn = document.getElementById('bulkActionBtn');
        const selectedCount = document.getElementById('selectedCount');
        const bulkCount = document.getElementById('bulkCount');
        
        function updateSelectedCount() {
            const checked = document.querySelectorAll('.voter-checkbox:checked');
            const count = checked.length;
            selectedCount.textContent = count;
            bulkCount.textContent = count;
            bulkActionBtn.disabled = count === 0;
        }
        
        selectAll.addEventListener('change', (e) => {
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            updateSelectedCount();
        });
        
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });
        
        function prepareBulkAction(action) {
            const checked = document.querySelectorAll('.voter-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value).join(',');
            
            if (action === 'bulk_status') {
                document.getElementById('bulkStatusIds').value = ids;
            } else {
                document.getElementById('bulkDeleteIds').value = ids;
            }
            return true;
        }
        
        // Export to CSV
        function exportToCSV() {
            const rows = ['Voter ID,Name,Email,Phone,DOB,Status,Has Voted'];
            document.querySelectorAll('tbody tr').forEach(row => {
                const cells = row.querySelectorAll('td');
                const data = [
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim(),
                    cells[3].textContent.trim().replace('\n', ','),
                    cells[3].textContent.trim().split('\n')[1] || '',
                    cells[2].querySelector('.text-xs')?.textContent.replace('DOB: ', '') || '',
                    cells[4].textContent.trim(),
                    cells[5].textContent.trim()
                ];
                rows.push(data.join(','));
            });
            
            const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'voters_<?php echo date('Y-m-d'); ?>.csv';
            a.click();
        }
    </script>
</body>
</html>