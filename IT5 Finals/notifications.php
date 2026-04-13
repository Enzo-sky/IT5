<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

$db = getDB();

// Create notifications table if not exists
$db->exec("
    CREATE TABLE IF NOT EXISTS notifications (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        type TEXT DEFAULT 'info',
        target TEXT DEFAULT 'all',
        election_id INTEGER,
        sent_by INTEGER,
        is_read INTEGER DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (election_id) REFERENCES elections(id),
        FOREIGN KEY (sent_by) REFERENCES users(id)
    )
");

$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action'] ?? '') {
        case 'create':
            $stmt = $db->prepare("INSERT INTO notifications (title, message, type, target, election_id, sent_by) VALUES (?, ?, ?, ?, ?, ?)");
            try {
                $election_id = !empty($_POST['election_id']) ? $_POST['election_id'] : null;
                $stmt->execute([
                    $_POST['title'],
                    $_POST['message'],
                    $_POST['type'],
                    $_POST['target'],
                    $election_id,
                    $_SESSION['user_id']
                ]);
                $message = 'Notification created successfully';
            } catch (PDOException $e) {
                $error = 'Failed to create notification';
            }
            break;

        case 'mark_read':
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            break;

        case 'delete':
            $stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $message = 'Notification deleted';
            break;

        case 'mark_all_read':
            $db->exec("UPDATE notifications SET is_read = 1");
            $message = 'All notifications marked as read';
            break;
    }
}

// Fetch data
$notifications = $db->query("
    SELECT n.*, u.username as sent_by_name, e.title as election_title
    FROM notifications n
    LEFT JOIN users u ON n.sent_by = u.id
    LEFT JOIN elections e ON n.election_id = e.id
    ORDER BY n.created_at DESC
")->fetchAll();

$elections = $db->query("SELECT id, title FROM elections ORDER BY created_at DESC")->fetchAll();

$unread_count = $db->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")->fetchColumn();
$total_count   = count($notifications);
$info_count    = count(array_filter($notifications, fn($n) => $n['type'] === 'info'));
$alert_count   = count(array_filter($notifications, fn($n) => $n['type'] === 'alert'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Voting System</title>
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
            <a href="users.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
                User Management
            </a>
            <a href="notifications.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
                <i data-lucide="bell" class="w-5 h-5"></i>
                Notifications
                <?php if ($unread_count > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                    <?php echo $unread_count; ?>
                </span>
                <?php endif; ?>
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
                    <h2 class="text-2xl font-bold text-gray-800">Notifications</h2>
                    <p class="text-sm text-gray-500">Manage and send system notifications</p>
                </div>
                <div class="flex items-center gap-3">
                    <?php if ($unread_count > 0): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="flex items-center gap-2 border border-gray-300 text-gray-600 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm font-medium transition">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                            Mark All Read
                        </button>
                    </form>
                    <?php endif; ?>
                    <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        New Notification
                    </button>
                </div>
            </div>
        </header>

        <div class="p-8">
            <!-- Flash Messages -->
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
            <div class="grid grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-2 text-gray-500 mb-1">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        <span class="text-sm">Total</span>
                    </div>
                    <p class="text-2xl font-bold text-gray-800"><?php echo $total_count; ?></p>
                </div>
                <div class="bg-red-50 rounded-xl border border-red-200 p-4">
                    <div class="flex items-center gap-2 text-red-600 mb-1">
                        <i data-lucide="bell-ring" class="w-4 h-4"></i>
                        <span class="text-sm font-medium">Unread</span>
                    </div>
                    <p class="text-2xl font-bold text-red-600"><?php echo $unread_count; ?></p>
                </div>
                <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                    <div class="flex items-center gap-2 text-blue-600 mb-1">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span class="text-sm font-medium">Info</span>
                    </div>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $info_count; ?></p>
                </div>
                <div class="bg-yellow-50 rounded-xl border border-yellow-200 p-4">
                    <div class="flex items-center gap-2 text-yellow-600 mb-1">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                        <span class="text-sm font-medium">Alerts</span>
                    </div>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $alert_count; ?></p>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="space-y-3">
                <?php if (empty($notifications)): ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <i data-lucide="bell-off" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                    <p class="text-gray-500 font-medium">No notifications yet</p>
                    <p class="text-gray-400 text-sm mt-1">Create your first notification using the button above.</p>
                </div>
                <?php endif; ?>

                <?php foreach ($notifications as $notif): ?>
                <?php
                    $typeStyles = [
                        'info'    => ['bg' => 'bg-blue-50 border-blue-200',   'icon_color' => 'text-blue-500',   'badge' => 'bg-blue-100 text-blue-800',   'icon' => 'info'],
                        'alert'   => ['bg' => 'bg-yellow-50 border-yellow-200','icon_color' => 'text-yellow-500', 'badge' => 'bg-yellow-100 text-yellow-800','icon' => 'alert-triangle'],
                        'success' => ['bg' => 'bg-green-50 border-green-200',  'icon_color' => 'text-green-500',  'badge' => 'bg-green-100 text-green-800',  'icon' => 'check-circle'],
                        'error'   => ['bg' => 'bg-red-50 border-red-200',      'icon_color' => 'text-red-500',    'badge' => 'bg-red-100 text-red-800',      'icon' => 'x-circle'],
                    ];
                    $style = $typeStyles[$notif['type']] ?? $typeStyles['info'];
                    $unread_class = $notif['is_read'] ? '' : 'ring-2 ring-indigo-200';
                ?>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 <?php echo $unread_class; ?> transition hover:shadow-md">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?php echo $style['bg']; ?> border <?php echo $style['bg']; ?>">
                            <i data-lucide="<?php echo $style['icon']; ?>" class="w-5 h-5 <?php echo $style['icon_color']; ?>"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h4 class="font-semibold text-gray-800 text-sm"><?php echo htmlspecialchars($notif['title']); ?></h4>
                                <?php if (!$notif['is_read']): ?>
                                <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                                <?php endif; ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium <?php echo $style['badge']; ?>">
                                    <?php echo ucfirst($notif['type']); ?>
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    <?php echo ucfirst($notif['target']); ?>
                                </span>
                                <?php if ($notif['election_title']): ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                    <?php echo htmlspecialchars($notif['election_title']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($notif['message']); ?></p>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="user" class="w-3 h-3"></i>
                                    <?php echo htmlspecialchars($notif['sent_by_name'] ?? 'System'); ?>
                                </span>
                                <span class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <?php echo date('M j, Y H:i', strtotime($notif['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <?php if (!$notif['is_read']): ?>
                            <form method="POST">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                <button type="submit" title="Mark as read"
                                    class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Delete this notification?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $notif['id']; ?>">
                                <button type="submit" title="Delete"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<!-- Create Notification Modal -->
<div id="createModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-bold text-gray-800">New Notification</h3>
            <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="create">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none"
                    placeholder="Notification title">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea name="message" required rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none resize-none"
                    placeholder="Write your message here..."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                        <option value="info">Info</option>
                        <option value="alert">Alert</option>
                        <option value="success">Success</option>
                        <option value="error">Error</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                    <select name="target" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                        <option value="all">All Users</option>
                        <option value="admins">Admins Only</option>
                        <option value="voters">Voters Only</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Related Election <span class="text-gray-400 font-normal">(optional)</span></label>
                <select name="election_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none">
                    <option value="">— None —</option>
                    <?php foreach ($elections as $election): ?>
                    <option value="<?php echo $election['id']; ?>"><?php echo htmlspecialchars($election['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    Send Notification
                </button>
            </div>
        </form>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>