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
            case 'create':
                $stmt = $db->prepare("INSERT INTO elections (title, description, start_date, end_date, status, voting_method) VALUES (?, ?, ?, ?, ?, ?)");
                try {
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $_POST['status'],
                        $_POST['voting_method']
                    ]);
                    $message = 'Election created successfully';
                } catch (PDOException $e) {
                    $error = 'Error creating election: ' . $e->getMessage();
                }
                break;
                
            case 'update':
                $stmt = $db->prepare("UPDATE elections SET title = ?, description = ?, start_date = ?, end_date = ?, status = ?, voting_method = ? WHERE id = ?");
                try {
                    $stmt->execute([
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['start_date'],
                        $_POST['end_date'],
                        $_POST['status'],
                        $_POST['voting_method'],
                        $_POST['id']
                    ]);
                    $message = 'Election updated successfully';
                } catch (PDOException $e) {
                    $error = 'Error updating election: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM elections WHERE id = ?");
                try {
                    $stmt->execute([$_POST['id']]);
                    $message = 'Election deleted successfully';
                } catch (PDOException $e) {
                    $error = 'Error deleting election';
                }
                break;
        }
    }
}

// Get filter status
$filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$sql = "SELECT * FROM elections";
if ($filter !== 'all') {
    $sql .= " WHERE status = ?";
    $elections = $db->prepare($sql);
    $elections->execute([$filter]);
    $elections = $elections->fetchAll();
} else {
    $elections = $db->query($sql . " ORDER BY created_at DESC")->fetchAll();
}

// Get election for editing
$edit_election = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_election = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Management - Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); border-right: 3px solid #fff; }
        .status-badge { @apply px-2 py-1 rounded-full text-xs font-medium; }
        .status-draft { @apply bg-gray-100 text-gray-800; }
        .status-active { @apply bg-green-100 text-green-800; }
        .status-closed { @apply bg-red-100 text-red-800; }
        .status-archived { @apply bg-blue-100 text-blue-800; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex flex-col overflow-y-auto">
            <div class="p-6 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-8 h-8 text-indigo-400"></i>
                    <div>
                        <h1 class="font-bold text-lg">VoteAdmin</h1>
                        <p class="text-xs text-slate-400">Management System</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 py-6 space-y-1">
                <a href="dashboard.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Dashboard
                </a>
                <a href="elections.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
                    <i data-lucide="vote" class="w-5 h-5"></i>
                    Elections
                </a>
                <a href="ballot_builder.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Ballot Builder
                </a>
                <a href="voters.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    Voter Management
                </a>
                <a href="results.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
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

            <div class="p-4 border-t border-slate-800">
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
                        <h2 class="text-2xl font-bold text-gray-800">Election Management</h2>
                        <p class="text-sm text-gray-500">Create, schedule, and manage elections</p>
                    </div>
                    <button onclick="document.getElementById('electionModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        New Election
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

                <!-- Filters -->
                <div class="mb-6 flex gap-2">
                    <a href="?status=all" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">All</a>
                    <a href="?status=draft" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'draft' ? 'bg-gray-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">Draft</a>
                    <a href="?status=active" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'active' ? 'bg-green-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">Active</a>
                    <a href="?status=closed" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'closed' ? 'bg-red-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">Closed</a>
                    <a href="?status=archived" class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $filter === 'archived' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">Archived</a>
                </div>

                <!-- Elections Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Election</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Voting Method</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($elections as $election): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($election['title']); ?></div>
                                    <div class="text-sm text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($election['description']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo date('M j', strtotime($election['start_date'])); ?> - <?php echo date('M j, Y', strtotime($election['end_date'])); ?></div>
                                    <div class="text-xs text-gray-500">
                                        <?php 
                                        $now = date('Y-m-d H:i:s');
                                        if ($now < $election['start_date']) echo 'Starts in ' . ceil((strtotime($election['start_date']) - strtotime($now)) / 86400) . ' days';
                                        elseif ($now > $election['end_date']) echo 'Ended';
                                        else echo 'In progress';
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        <?php echo ucfirst(str_replace('_', ' ', $election['voting_method'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php 
                                        echo $election['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                             ($election['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                             ($election['status'] === 'closed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')); 
                                    ?>">
                                        <?php echo ucfirst($election['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="ballot_builder.php?election_id=<?php echo $election['id']; ?>" class="text-indigo-600 hover:text-indigo-900" title="Edit Ballot">
                                            <i data-lucide="file-edit" class="w-4 h-4"></i>
                                        </a>
                                        <a href="?edit=<?php echo $election['id']; ?>" class="text-blue-600 hover:text-blue-900" title="Edit Election">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure? This cannot be undone if votes exist.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $election['id']; ?>">
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

    <!-- Modal for Create/Edit -->
    <div id="electionModal" class="<?php echo $edit_election ? 'fixed' : 'hidden'; ?> inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900"><?php echo $edit_election ? 'Edit Election' : 'Create New Election'; ?></h3>
                <button onclick="window.location.href='elections.php'" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="<?php echo $edit_election ? 'update' : 'create'; ?>">
                <?php if ($edit_election): ?>
                <input type="hidden" name="id" value="<?php echo $edit_election['id']; ?>">
                <?php endif; ?>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Election Title</label>
                    <input type="text" name="title" required value="<?php echo $edit_election ? htmlspecialchars($edit_election['title']) : ''; ?>" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?php echo $edit_election ? htmlspecialchars($edit_election['description']) : ''; ?></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="datetime-local" name="start_date" required 
                            value="<?php echo $edit_election ? date('Y-m-d\TH:i', strtotime($edit_election['start_date'])) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="datetime-local" name="end_date" required 
                            value="<?php echo $edit_election ? date('Y-m-d\TH:i', strtotime($edit_election['end_date'])) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="draft" <?php echo ($edit_election && $edit_election['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                            <option value="active" <?php echo ($edit_election && $edit_election['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="closed" <?php echo ($edit_election && $edit_election['status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                            <option value="archived" <?php echo ($edit_election && $edit_election['status'] === 'archived') ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Voting Method</label>
                        <select name="voting_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="plurality" <?php echo ($edit_election && $edit_election['voting_method'] === 'plurality') ? 'selected' : ''; ?>>Plurality (First Past The Post)</option>
                            <option value="ranked_choice" <?php echo ($edit_election && $edit_election['voting_method'] === 'ranked_choice') ? 'selected' : ''; ?>>Ranked Choice (RCV)</option>
                            <option value="approval" <?php echo ($edit_election && $edit_election['voting_method'] === 'approval') ? 'selected' : ''; ?>>Approval Voting</option>
                        </select>
                    </div>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="window.location.href='elections.php'" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <?php echo $edit_election ? 'Update Election' : 'Create Election'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>