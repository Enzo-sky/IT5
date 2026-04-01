<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

$db = getDB();
$message = '';
$error = '';

// Get election ID
$election_id = isset($_GET['election_id']) ? intval($_GET['election_id']) : 0;
$election = null;
$positions = [];

if ($election_id) {
    $stmt = $db->prepare("SELECT * FROM elections WHERE id = ?");
    $stmt->execute([$election_id]);
    $election = $stmt->fetch();
    
    if ($election) {
        $stmt = $db->prepare("SELECT p.*, COUNT(c.id) as candidate_count 
                              FROM positions p 
                              LEFT JOIN candidates c ON p.id = c.position_id 
                              WHERE p.election_id = ? 
                              GROUP BY p.id 
                              ORDER BY p.sort_order");
        $stmt->execute([$election_id]);
        $positions = $stmt->fetchAll();
    }
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'add_position':
            $stmt = $db->prepare("INSERT INTO positions (election_id, title, description, max_selections, sort_order) 
                                  VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM positions WHERE election_id = ?))");
            try {
                $stmt->execute([$election_id, $_POST['title'], $_POST['description'], $_POST['max_selections'], $election_id]);
                $message = 'Position added successfully';
            } catch (PDOException $e) {
                $error = 'Error adding position';
            }
            break;
            
        case 'delete_position':
            $stmt = $db->prepare("DELETE FROM positions WHERE id = ? AND election_id = ?");
            try {
                $stmt->execute([$_POST['position_id'], $election_id]);
                $message = 'Position deleted';
            } catch (PDOException $e) {
                $error = 'Error deleting position';
            }
            break;
            
        case 'add_candidate':
            $stmt = $db->prepare("INSERT INTO candidates (position_id, name, party, bio, photo_url, sort_order) 
                                  VALUES (?, ?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM candidates WHERE position_id = ?))");
            try {
                $stmt->execute([
                    $_POST['position_id'], 
                    $_POST['name'], 
                    $_POST['party'], 
                    $_POST['bio'],
                    $_POST['photo_url'],
                    $_POST['position_id']
                ]);
                $message = 'Candidate added successfully';
            } catch (PDOException $e) {
                $error = 'Error adding candidate';
            }
            break;
            
        case 'delete_candidate':
            $stmt = $db->prepare("DELETE FROM candidates WHERE id = ?");
            try {
                $stmt->execute([$_POST['candidate_id']]);
                $message = 'Candidate removed';
            } catch (PDOException $e) {
                $error = 'Error removing candidate';
            }
            break;
    }
}

// Get all elections for selector
$elections = $db->query("SELECT id, title FROM elections ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ballot Builder - Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); border-right: 3px solid #fff; }
        .ballot-item { transition: all 0.2s; }
        .ballot-item:hover { transform: translateX(4px); }
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
                <a href="ballot_builder.php" class="sidebar-link active flex items-center gap-3 px-6 py-3 text-sm font-medium text-white">
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
                        <h2 class="text-2xl font-bold text-gray-800">Ballot Builder</h2>
                        <p class="text-sm text-gray-500">Design ballots, add positions and candidates</p>
                    </div>
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
                                <?php echo htmlspecialchars($e['title']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($election): ?>
                        <a href="elections.php?edit=<?php echo $election_id; ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                            <i data-lucide="settings" class="w-4 h-4"></i>
                            Settings
                        </a>
                        <?php endif; ?>
                    </form>
                </div>

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

                <?php if ($election): ?>
                <!-- Ballot Preview Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($election['title']); ?></h3>
                        <p class="text-sm text-gray-500">
                            <?php echo ucfirst($election['voting_method']); ?> voting method • 
                            <?php echo count($positions); ?> position(s)
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="document.getElementById('positionModal').classList.remove('hidden')" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            Add Position
                        </button>
                    </div>
                </div>

                <!-- Positions List -->
                <div class="space-y-6">
                    <?php if (empty($positions)): ?>
                    <div class="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
                        <i data-lucide="file-plus" class="w-12 h-12 text-gray-300 mx-auto mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900">No positions yet</h3>
                        <p class="text-gray-500 mb-4">Start building your ballot by adding positions</p>
                        <button onclick="document.getElementById('positionModal').classList.remove('hidden')" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg">
                            Add First Position
                        </button>
                    </div>
                    <?php else: ?>
                        <?php foreach ($positions as $position): ?>
                        <?php 
                        $candidates = $db->prepare("SELECT * FROM candidates WHERE position_id = ? ORDER BY sort_order");
                        $candidates->execute([$position['id']]);
                        $candidates = $candidates->fetchAll();
                        ?>
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($position['title']); ?></h4>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($position['description']); ?> • Max <?php echo $position['max_selections']; ?> selection(s)</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="openCandidateModal(<?php echo $position['id']; ?>)" 
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center gap-1">
                                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                                        Add Candidate
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Delete this position and all its candidates?');">
                                        <input type="hidden" name="action" value="delete_position">
                                        <input type="hidden" name="position_id" value="<?php echo $position['id']; ?>">
                                        <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 p-1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="divide-y divide-gray-100">
                                <?php if (empty($candidates)): ?>
                                <div class="px-6 py-8 text-center text-gray-500">
                                    No candidates added yet for this position
                                </div>
                                <?php else: ?>
                                    <?php foreach ($candidates as $candidate): ?>
                                    <div class="ballot-item px-6 py-4 flex items-center gap-4">
                                        <img src="<?php echo $candidate['photo_url'] ?: 'https://static.photos/people/64x64/' . $candidate['id']; ?>" 
                                            alt="<?php echo htmlspecialchars($candidate['name']); ?>"
                                            class="w-12 h-12 rounded-full object-cover bg-gray-200">
                                        <div class="flex-1">
                                            <h5 class="font-medium text-gray-900"><?php echo htmlspecialchars($candidate['name']); ?></h5>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($candidate['party']); ?></p>
                                            <?php if ($candidate['bio']): ?>
                                            <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars(substr($candidate['bio'], 0, 100)) . '...'; ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <form method="POST" class="inline" onsubmit="return confirm('Remove this candidate?');">
                                            <input type="hidden" name="action" value="delete_candidate">
                                            <input type="hidden" name="candidate_id" value="<?php echo $candidate['id']; ?>">
                                            <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                                            <button type="submit" class="text-gray-400 hover:text-red-600">
                                                <i data-lucide="x" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <i data-lucide="vote" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Select an Election</h3>
                    <p class="text-gray-500">Choose an election from the dropdown above to start building the ballot</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Add Position Modal -->
    <div id="positionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Add Position</h3>
                <button onclick="document.getElementById('positionModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="add_position">
                <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Position Title</label>
                    <input type="text" name="title" required placeholder="e.g., President, Senator"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Brief description of the position"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Max Selections</label>
                    <input type="number" name="max_selections" min="1" value="1" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Number of candidates a voter can select for this position</p>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="document.getElementById('positionModal').classList.add('hidden')" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Add Position
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div id="candidateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Add Candidate</h3>
                <button onclick="closeCandidateModal()" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="action" value="add_candidate">
                <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                <input type="hidden" name="position_id" id="candidate_position_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Party/Affiliation</label>
                    <input type="text" name="party" placeholder="e.g., Independent, Democratic Party"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Photo URL</label>
                    <input type="url" name="photo_url" placeholder="https://example.com/photo.jpg"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Leave blank for auto-generated placeholder</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Biography</label>
                    <textarea name="bio" rows="3" placeholder="Candidate background and platform"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"></textarea>
                </div>
                
                <div class="pt-4 flex gap-3">
                    <button type="button" onclick="closeCandidateModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Add Candidate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        lucide.createIcons();
        
        function openCandidateModal(positionId) {
            document.getElementById('candidate_position_id').value = positionId;
            document.getElementById('candidateModal').classList.remove('hidden');
        }
        
        function closeCandidateModal() {
            document.getElementById('candidateModal').classList.add('hidden');
        }
    </script>
</body>
</html>