<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

// Only super admin can manage users
if ($_SESSION['role'] !== 'admin') {
    header('Location: admin_dashboard.php');
    exit();
}

$db = getDB();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'create_user':
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            try {
                $stmt->execute([
                    $_POST['username'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['role']
                ]);
                $message = 'User created successfully';
            } catch (PDOException $e) {
                $error = 'Username already exists';
            }
            break;
            
        case 'update_user':
            if ($_POST['id'] == $_SESSION['user_id'] && $_POST['role'] !== 'admin') {
                $error = 'Cannot demote yourself';
            } else {
                $sql = "UPDATE users SET username = ?, role = ?";
                $params = [$_POST['username'], $_POST['role']];
                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                $sql .= " WHERE id = ?";
                $params[] = $_POST['id'];
                
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute($params);
                    $message = 'User updated successfully';
                } catch (PDOException $e) {
                    $error = 'Error updating user';
                }
            }
            break;
            
        case 'delete_user':
            if ($_POST['id'] == $_SESSION['user_id']) {
                $error = 'Cannot delete yourself';
            } else {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'User deleted successfully';
            }
            break;
    }
}

// Get users
$users = $db->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT id, username, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Voting System</title>
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
            <div class="p-6 border-b border-slate-800">
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
                <a href="voters.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
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
                <a href="users.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
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
                        <h2 class="text-2xl font-bold text-gray-800">User Management</h2>
                        <p class="text-sm text-gray-500">Manage administrator accounts and permissions</p>
                    </div>
                    <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Add User
                    </button>
                </div>
            </header>

            <div class="p-8">
                <!-- Messages -->
                <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1"><?php echo count($users); ?></p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Admins</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">
                            <?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?>
                        </p>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                        <p class="text-sm text-gray-500">Viewers</p>
                        <p class="text-2xl font-bold text-gray-600 mt-1">
                            <?php echo count(array_filter($users, fn($u) => $u['role'] === 'viewer')); ?>
                        </p>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm">
                                            <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['username']); ?></p>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                            <span class="text-xs text-indigo-500">(You)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $user['role'] === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="?edit=<?php echo $user['id']; ?>"
                                            class="text-indigo-600 hover:text-indigo-800 p-1 rounded hover:bg-indigo-50 transition"
                                            title="Edit">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" onsubmit="return confirm('Delete this user?');" class="inline">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 transition" title="Delete">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
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

    <!-- Create User Modal -->
    <div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">Create New User</h3>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create_user">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                        placeholder="e.g. jsmith">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                        placeholder="Minimum 8 characters">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                        <option value="admin">Admin</option>
                        <option value="viewer">Viewer</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                        Create User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <?php if ($edit_user): ?>
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">Edit User</h3>
                <a href="users.php" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </a>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" name="username" required
                        value="<?php echo htmlspecialchars($edit_user['username']); ?>"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
                    <input type="password" name="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                        placeholder="Leave blank to keep unchanged">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                        <option value="admin" <?php echo $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="viewer" <?php echo $edit_user['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
                    </select>
                </div>
                <div class="flex gap-3 pt-2">
                    <a href="users.php"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition text-center">
                        Cancel
                    </a>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>lucide.createIcons();</script>
</body>
</html>