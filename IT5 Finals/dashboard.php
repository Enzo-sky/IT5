<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) { header("Location: login.php"); exit(); }

$db = getDB();
$user_id = $_SESSION['user_id'];

// Get active election
$election = $db->query("SELECT * FROM elections WHERE status = 'active' ORDER BY created_at DESC LIMIT 1")->fetch();

// Total candidates in active election
$total_candidates = 0;
if ($election) {
    $stmt = $db->prepare("
        SELECT COUNT(*) as cnt FROM candidates c
        JOIN positions p ON c.position_id = p.id
        WHERE p.election_id = ?
    ");
    $stmt->execute([$election['id']]);
    $total_candidates = $stmt->fetch()['cnt'];
}

// Check if current user has voted
$has_voted = false;
if ($election) {
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM votes WHERE election_id = ? AND voter_id = ?");
    $stmt->execute([$election['id'], $user_id]);
    $has_voted = $stmt->fetch()['cnt'] > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
            padding: 80px 40px 110px;
            color: white;
            border-bottom-left-radius: 30px;
            box-shadow: 0 10px 30px rgba(30, 144, 255, 0.2);
        }
        .container { padding: 40px; margin-top: -70px; }
        h1 { margin: 0; font-size: 32px; font-weight: 700; }
        .status-pill {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            margin-bottom: 10px;
        }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
        .glass-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.8);
            transition: 0.3s ease;
        }
        .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(30, 144, 255, 0.1); }
        .card-icon { font-size: 40px; margin-bottom: 15px; display: block; }
        h3 { margin: 0; color: #333; font-size: 18px; }
        .action-btn {
            background: #1e90ff; color: white;
            padding: 12px 25px; border-radius: 10px;
            text-decoration: none; font-weight: bold;
            display: inline-block; margin-top: 20px; transition: 0.3s;
        }
        .action-btn:hover { background: #1e3c72; transform: translateY(-2px); }
        .action-btn.disabled { background: #aaa; pointer-events: none; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-banner">
        <span class="status-pill">
            <?php echo $election ? htmlspecialchars($election['title']) : 'No Active Election'; ?>
        </span>
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p style="margin:10px 0 0; opacity:0.9;">Your voice matters. Make sure to cast your vote before the deadline.</p>
    </div>

    <div class="container">
        <div class="card-grid">
            <div class="glass-card">
                <span class="card-icon">🏛️</span>
                <h3>Election Status</h3>
                <?php if ($election): ?>
                <p style="color: #28a745; font-weight: bold; margin-top:10px;">● Active</p>
                <p style="color:#888; font-size:13px; margin:5px 0 0;">Ends: <?php echo date('M j, Y', strtotime($election['end_date'])); ?></p>
                <?php else: ?>
                <p style="color: #888; font-weight: bold; margin-top:10px;">● No Active Election</p>
                <?php endif; ?>
            </div>

            <div class="glass-card">
                <span class="card-icon">📝</span>
                <h3>My Participation</h3>
                <?php if (!$election): ?>
                <p style="color:#888; margin-top:10px;">No election running.</p>
                <?php elseif ($has_voted): ?>
                <p style="color:#28a745; font-weight:bold; margin-top:10px;">✅ Vote Submitted</p>
                <?php else: ?>
                <p style="color:orange; font-weight:bold; margin-top:10px;">⏳ Pending — You haven't voted yet</p>
                <?php endif; ?>
            </div>

            <div class="glass-card">
                <span class="card-icon">👥</span>
                <h3>Registered Candidates</h3>
                <p style="color: #666; margin-top:10px;">Total: <b><?php echo $total_candidates; ?> Candidate<?php echo $total_candidates !== 1 ? 's' : ''; ?></b></p>
            </div>
        </div>

        <div class="glass-card" style="margin-top: 30px; border-left: 6px solid #1e90ff;">
            <h3>Quick Actions</h3>
            <p style="color: #888; margin-top: 10px;">Choose an option below to begin your voting process.</p>
            <?php if (!$election): ?>
                <a href="#" class="action-btn disabled">No Active Election</a>
            <?php elseif ($has_voted): ?>
                <a href="results.php" class="action-btn">View Results</a>
            <?php else: ?>
                <a href="vote.php" class="action-btn">Start Voting Now</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>