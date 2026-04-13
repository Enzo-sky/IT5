<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) { header("Location: login.php"); exit(); }

$db = getDB();

// Get active election
$election = $db->query("SELECT * FROM elections WHERE status = 'active' ORDER BY created_at DESC LIMIT 1")->fetch();

$candidates = [];
if ($election) {
    $stmt = $db->prepare("
        SELECT c.*, p.title as position 
        FROM candidates c 
        JOIN positions p ON c.position_id = p.id 
        WHERE p.election_id = ?
        ORDER BY p.id, c.sort_order
    ");
    $stmt->execute([$election['id']]);
    $candidates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidates - URS Vote</title>
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
        .card-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
        }
        .glass-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.3s;
        }
        .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(30, 144, 255, 0.1); }
        .avatar-circle {
            width: 65px; height: 65px;
            background: #eef7ff; color: #1e90ff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 22px;
            border: 2px solid #1e90ff;
            flex-shrink: 0;
        }
        .role-label { color: #1e90ff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 5px; }
        h3 { margin: 0; color: #333; font-size: 18px; }
        .party-tag { font-size: 12px; color: #888; margin-top: 4px; }
        .no-election { background: white; padding: 40px; border-radius: 20px; text-align: center; color: #888; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="welcome-banner">
        <h1 style="margin:0;">Official Candidates</h1>
        <p style="margin:10px 0 0; opacity:0.9;">
            <?php echo $election ? htmlspecialchars($election['title']) : 'No active election'; ?>
        </p>
    </div>
    <div class="container">
        <?php if (!$election): ?>
        <div class="no-election">
            <p style="font-size:40px;">🗳️</p>
            <h3>No Active Election</h3>
            <p>There is currently no active election. Please check back later.</p>
        </div>
        <?php elseif (empty($candidates)): ?>
        <div class="no-election">
            <p style="font-size:40px;">👥</p>
            <h3>No Candidates Yet</h3>
            <p>Candidates have not been added to this election yet.</p>
        </div>
        <?php else: ?>
        <div class="card-grid">
            <?php foreach($candidates as $c): ?>
            <div class="glass-card">
                <div class="avatar-circle"><?php echo strtoupper(substr($c['name'], 0, 1)); ?></div>
                <div>
                    <p class="role-label"><?php echo htmlspecialchars($c['position']); ?></p>
                    <h3><?php echo htmlspecialchars($c['name']); ?></h3>
                    <?php if (!empty($c['party'])): ?>
                    <p class="party-tag">🏳️ <?php echo htmlspecialchars($c['party']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>