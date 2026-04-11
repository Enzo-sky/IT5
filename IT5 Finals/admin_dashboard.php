
<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$db = getDB();

// Get statistics
$stats = [
    'total_voters' => $db->query("SELECT COUNT(*) FROM voters")->fetchColumn(),
    'votes_cast' => $db->query("SELECT COUNT(DISTINCT voter_id) FROM votes")->fetchColumn(),
    'active_elections' => $db->query("SELECT COUNT(*) FROM elections WHERE status = 'active' AND start_date <= datetime('now') AND end_date >= datetime('now')")->fetchColumn(),
    'total_elections' => $db->query("SELECT COUNT(*) FROM elections")->fetchColumn()
];

$turnout = $stats['total_voters'] > 0 ? round(($stats['votes_cast'] / $stats['total_voters']) * 100, 1) : 0;

// Recent elections
$recent_elections = $db->query("SELECT * FROM elections ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Monthly voting data for chart
$monthly_data = $db->query("
    SELECT strftime('%Y-%m', voted_at) as month, COUNT(*) as count 
    FROM votes 
    GROUP BY month 
    ORDER BY month DESC 
    LIMIT 6
")->fetchAll();
$monthly_data = array_reverse($monthly_data);

// Recent activity
$recent_activity = $db->query("
    SELECT 'vote' as type, voted_at as timestamp, NULL as description 
    FROM votes 
    ORDER BY voted_at DESC 
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=desvice-width, initial-scale=1.0">
    <title>Dashboard - Voting Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: rgba(25 5,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); border-right: 3px solid #fff; }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
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
                <a href="admin_dashboard.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
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
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
                <div class="px-8 py-4 flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-800">Dashboard Overview</h2>
                    <div class="flex items-center gap-4">
                        <button onclick="window.location.reload()" class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                            <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                        </button>
                        <span class="text-sm text-gray-500" id="current-time"></span>
                    </div>
                </div>
            </header>

            <div class="p-8 space-y-6">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Voters</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['total_voters']); ?></p>
                                <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                                    Registered
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="users" class="w-6 h-6 text-indigo-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Votes Cast</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo number_format($stats['votes_cast']); ?></p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <?php echo $turnout; ?>% Turnout
                                </p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="check-circle" class="w-6 h-6 text-green-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Active Elections</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $stats['active_elections']; ?></p>
                                <p class="text-xs text-blue-600 mt-1">Currently Running</p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="activity" class="w-6 h-6 text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Voter Turnout</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo $turnout; ?>%</p>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: <?php echo min($turnout, 100); ?>%"></div>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i data-lucide="percent" class="w-6 h-6 text-purple-600"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Voting Activity (Last 6 Months)</h3>
                        <canvas id="votingChart" height="300"></canvas>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Election Status Distribution</h3>
                        <canvas id="electionChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Recent Elections & Activity -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Recent Elections</h3>
                            <a href="elections.php" class="text-sm text-indigo-600 hover:text-indigo-800">View All</a>
                        </div>
                        <div class="space-y-3">
                            <?php foreach ($recent_elections as $election): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($election['title']); ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('M j, Y', strtotime($election['start_date'])); ?> - 
                                        <?php echo date('M j, Y', strtotime($election['end_date'])); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php 
                                    echo $election['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                         ($election['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                    <?php echo ucfirst($election['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">System Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-sm font-medium text-gray-900">System Operational</span>
                                </div>
                                <span class="text-xs text-green-600">100% Uptime</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-100">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="database" class="w-4 h-4 text-blue-600"></i>
                                    <span class="text-sm font-medium text-gray-900">Database Connection</span>
                                </div>
                                <span class="text-xs text-blue-600">Active</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-100">
                                <div class="flex items-center gap-3">
                                    <i data-lucide="lock" class="w-4 h-4 text-purple-600"></i>
                                    <span class="text-sm font-medium text-gray-900">Security Level</span>
                                </div>
                                <span class="text-xs text-purple-600">High</span>
                            </div>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <h4 class="text-sm font-semibold text-gray-800 mb-2">Quick Actions</h4>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="elections.php?action=create" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-white rounded border border-gray-200 hover:shadow-sm transition">
                                        <i data-lucide="plus-circle" class="w-4 h-4 text-indigo-600"></i>
                                        New Election
                                    </a>
                                    <a href="voters.php?action=import" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-white rounded border border-gray-200 hover:shadow-sm transition">
                                        <i data-lucide="upload" class="w-4 h-4 text-green-600"></i>
                                        Import Voters
                                    </a>
                                    <a href="results.php" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-white rounded border border-gray-200 hover:shadow-sm transition">
                                        <i data-lucide="download" class="w-4 h-4 text-blue-600"></i>
                                        Export Results
                                    </a>
                                    <a href="notifications.php" class="flex items-center gap-2 p-2 text-sm text-gray-700 hover:bg-white rounded border border-gray-200 hover:shadow-sm transition">
                                        <i data-lucide="send" class="w-4 h-4 text-purple-600"></i>
                                        Send Alert
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
        
        // Update current time
        function updateTime() {
            document.getElementById('current-time').textContent = new Date().toLocaleString();
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Voting Activity Chart
        const ctx1 = document.getElementById('votingChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_map(function($d) { 
                    return date('M Y', strtotime($d['month'] . '-01')); 
                }, $monthly_data)); ?>,
                datasets: [{
                    label: 'Votes Cast',
                    data: <?php echo json_encode(array_map(function($d) { 
                        return $d['count']; 
                    }, $monthly_data)); ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        // Election Status Chart
        const ctx2 = document.getElementById('electionChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Draft', 'Active', 'Closed', 'Archived'],
                datasets: [{
                    data: [
                        <?php echo $db->query("SELECT COUNT(*) FROM elections WHERE status = 'draft'")->fetchColumn(); ?>,
                        <?php echo $db->query("SELECT COUNT(*) FROM elections WHERE status = 'active'")->fetchColumn(); ?>,
                        <?php echo $db->query("SELECT COUNT(*) FROM elections WHERE status = 'closed'")->fetchColumn(); ?>,
                        <?php echo $db->query("SELECT COUNT(*) FROM elections WHERE status = 'archived'")->fetchColumn(); ?>
                    ],
                    backgroundColor: ['#9ca3af', '#22c55e', '#ef4444', '#6b7280']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>