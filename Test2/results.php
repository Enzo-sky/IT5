<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

$db = getDB();

// Get election for results
$election_id = isset($_GET['election_id']) ? intval($_GET['election_id']) : 0;
$election = null;
$positions = [];

if ($election_id) {
    $stmt = $db->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$election_id]);
    $election = $stmt->fetch();
    
    if ($election) {
        $stmt = $db->prepare("SELECT p.* FROM positions p WHERE p.election_id = ? ORDER BY p.sort_order");
        $stmt->execute([$election_id]);
        $positions = $stmt->fetchAll();
        
        // Get results for each position
        foreach ($positions as &$position) {
            $stmt = $db->prepare("
                SELECT c.*, COUNT(v.id) as vote_count 
                FROM candidates c 
                LEFT JOIN votes v ON c.id = v.candidate_id AND v.position_id = c.position_id
                WHERE c.position_id = ? 
                GROUP BY c.id 
                ORDER BY vote_count DESC, c.sort_order
            ");
            $stmt->execute([$position['id']]);
            $position['candidates'] = $stmt->fetchAll();
            
            // Calculate total votes for this position
            $stmt = $db->prepare("SELECT COUNT(DISTINCT voter_id) FROM votes WHERE position_id = ?");
            $stmt->execute([$position['id']]);
            $position['total_votes'] = $stmt->fetchColumn();
        }
    }
}

// Get all elections for selector
$elections = $db->query("SELECT id, title, status FROM elections ORDER BY created_at DESC")->fetchAll();

// Handle export
if (isset($_GET['export'])) {
    $format = $_GET['export'];
    if ($format === 'csv' && $election) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="results_' . $election_id . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Position', 'Candidate', 'Party', 'Votes', 'Percentage']);
        
        foreach ($positions as $pos) {
            foreach ($pos['candidates'] as $cand) {
                $pct = $pos['total_votes'] > 0 ? round(($cand['vote_count'] / $pos['total_votes']) * 100, 2) : 0;
                fputcsv($output, [
                    $pos['title'],
                    $cand['name'],
                    $cand['party'] ?? 'N/A',
                    $cand['vote_count'],
                    $pct . '%'
                ]);
            }
        }
        fclose($output);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results & Reports - Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="results.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
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
                        <h2 class="text-2xl font-bold text-gray-800">Results & Reports</h2>
                        <p class="text-sm text-gray-500">Live vote tallying and election results</p>
                    </div>
                    <?php if ($election): ?>
                    <div class="flex gap-2">
                        <a href="?election_id=<?php echo $election_id; ?>&export=csv" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            Export CSV
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </header>

            <div class="p-8">
                <!-- Election Selector -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Election</label>
                    <form method="GET" class="flex gap-3">
                        <select name="election_id" onchange="this.form.submit()" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Choose an election...</option>
                            <?php foreach ($elections as $e): ?>
                            <option value="<?php echo $e['id']; ?>" <?php echo $election_id === $e['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($e['title']); ?> (<?php echo ucfirst($e['status']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <?php if ($election && !empty($positions)): ?>
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($election['title']); ?> - Results</h3>
                        <p class="text-sm text-gray-500">Voting method: <?php echo ucfirst(str_replace('_', ' ', $election['voting_method'])); ?></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">Live Results</span>
                    </div>
                </div>

                <div class="space-y-6">
                    <?php foreach ($positions as $position): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($position['title']); ?></h4>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($position['description']); ?> • <?php echo $position['total_votes']; ?> total votes</p>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Bar Chart -->
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Vote Distribution</h5>
                                    <canvas id="chart_<?php echo $position['id']; ?>" height="250"></canvas>
                                </div>
                                
                                <!-- Results Table -->
                                <div>
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Detailed Results</h5>
                                    <div class="space-y-3">
                                        <?php 
                                        $max_votes = max(array_column($position['candidates'], 'vote_count'));
                                        foreach ($position['candidates'] as $candidate): 
                                            $percentage = $position['total_votes'] > 0 ? round(($candidate['vote_count'] / $position['total_votes']) * 100, 1) : 0;
                                            $is_winner = $candidate['vote_count'] == $max_votes && $max_votes > 0;
                                        ?>
                                        <div class="flex items-center gap-3">
                                            <img src="<?php echo $candidate['photo_url'] ?: 'https://static.photos/people/40x40/' . $candidate['id']; ?>" 
                                                alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                                class="w-10 h-10 rounded-full object-cover">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium text-gray-900 flex items-center gap-2">
                                                        <?php echo htmlspecialchars($candidate['name']); ?>
                                                        <?php if ($is_winner): ?>
                                                        <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full">Winner</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <span class="text-sm text-gray-600"><?php echo $candidate['vote_count']; ?> votes</span>
                                                </div>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-1000" 
                                                            style="width: <?php echo $percentage; ?>%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-500 w-10 text-right"><?php echo $percentage; ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <script>
                <?php foreach ($positions as $position): ?>
                new Chart(document.getElementById('chart_<?php echo $position['id']; ?>').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: <?php echo json_encode(array_map(function($c) { return $c['name']; }, $position['candidates'])); ?>,
                        datasets: [{
                            label: 'Votes',
                            data: <?php echo json_encode(array_map(function($c) { return $c['vote_count']; }, $position['candidates'])); ?>,
                            backgroundColor: ['#4f46e5', '#7c3aed', '#2563eb', '#059669', '#d97706', '#dc2626'],
                            borderRadius: 6
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
                <?php endforeach; ?>
                </script>

                <?php elseif ($election): ?>
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <i data-lucide="inbox" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900">No Positions Found</h3>
                    <p class="text-gray-500">This election has no positions defined.</p>
                    <a href="ballot_builder.php?election_id=<?php echo $election_id; ?>" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i data-lucide="file-plus" class="w-4 h-4"></i>
                        Build Ballot
                    </a>
                </div>
                <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <i data-lucide="bar-chart-2" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900">Select an Election</h3>
                    <p class="text-gray-500">Choose an election above to view its results</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>