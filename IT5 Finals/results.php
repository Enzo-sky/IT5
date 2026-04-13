<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) { header("Location: login.php"); exit(); }

$db = getDB();

// Get active or most recent closed election
$election = $db->query("SELECT * FROM elections WHERE status IN ('active','closed') ORDER BY created_at DESC LIMIT 1")->fetch();

$results = [];
if ($election) {
    $stmt = $db->prepare("SELECT * FROM positions WHERE election_id = ? ORDER BY id");
    $stmt->execute([$election['id']]);
    $positions = $stmt->fetchAll();

    foreach ($positions as $pos) {
        $stmt2 = $db->prepare("
            SELECT c.id, c.name, c.party, COUNT(v.id) as vote_count
            FROM candidates c
            LEFT JOIN votes v ON v.candidate_id = c.id AND v.position_id = c.position_id
            WHERE c.position_id = ?
            GROUP BY c.id
            ORDER BY vote_count DESC
        ");
        $stmt2->execute([$pos['id']]);
        $candidates = $stmt2->fetchAll();

        $total = array_sum(array_column($candidates, 'vote_count'));

        $results[] = [
            'position' => $pos,
            'candidates' => $candidates,
            'total' => $total
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        .welcome-banner { background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%); padding: 80px 40px 110px; color: white; border-bottom-left-radius: 30px; }
        .container { padding: 40px; margin-top: -70px; }
        .glass-card { background: #ffffff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .winner-tag { background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; float: right; }
        .progress-bar { height: 12px; background: #f0f2f5; border-radius: 10px; margin-top: 8px; overflow: hidden; }
        .progress-fill { height: 100%; background: #1e90ff; border-radius: 10px; transition: width 0.5s ease; }
        .progress-fill.winner { background: #28a745; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { text-align: left; color: #888; font-size: 12px; padding-bottom: 10px; }
        .table-custom td { padding: 15px 0; border-top: 1px solid #f0f2f5; font-weight: 500; }
        .results-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
        .candidate-row { margin-top: 12px; }
        .no-election { background: white; padding: 40px; border-radius: 20px; text-align: center; color: #888; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="welcome-banner">
        <h1 style="margin: 0; font-size: 32px;">Live Election Results</h1>
        <p style="margin: 10px 0 0; opacity: 0.9;">
            <?php echo $election ? htmlspecialchars($election['title']) : 'No results available'; ?>
        </p>
    </div>
    <div class="container">
        <?php if (!$election || empty($results)): ?>
        <div class="no-election">
            <p style="font-size:40px;">📊</p>
            <h3>No Results Yet</h3>
            <p>Results will appear here once an election is active or closed.</p>
        </div>
        <?php else: ?>

        <!-- Winners Summary Table -->
        <div class="glass-card" style="border-top: 5px solid #28a745;">
            <h3>🏆 Election Winners</h3>
            <table class="table-custom">
                <thead><tr><th>POSITION</th><th>LEADING CANDIDATE</th><th>VOTES</th></tr></thead>
                <tbody>
                    <?php foreach($results as $r): 
                        $top = !empty($r['candidates']) ? $r['candidates'][0] : null;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['position']['title']); ?></td>
                        <td><?php echo $top ? htmlspecialchars($top['name']) : 'No candidates'; ?></td>
                        <td><?php echo $top ? $top['vote_count'] . ' vote(s)' : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Per Position Breakdown -->
        <div class="results-grid">
            <?php foreach($results as $r): 
                $top = !empty($r['candidates']) ? $r['candidates'][0] : null;
            ?>
            <div class="glass-card">
                <?php if ($top && $r['total'] > 0): ?>
                    <span class="winner-tag">🥇 Leading</span>
                <?php endif; ?>
                <h4 style="color: #1e3c72; margin: 0 0 15px 0;"><?php echo htmlspecialchars($r['position']['title']); ?></h4>

                <?php foreach($r['candidates'] as $i => $c): 
                    $pct = $r['total'] > 0 ? round(($c['vote_count'] / $r['total']) * 100) : 0;
                ?>
                <div class="candidate-row">
                    <div style="display:flex; justify-content:space-between; font-size:14px;">
                        <span><?php echo htmlspecialchars($c['name']); ?></span>
                        <span style="font-weight:bold;"><?php echo $pct; ?>% (<?php echo $c['vote_count']; ?>)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $i === 0 && $r['total'] > 0 ? 'winner' : ''; ?>" style="width: <?php echo $pct; ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($r['total'] === 0): ?>
                <p style="color:#aaa; font-size:13px; margin-top:10px;">No votes cast yet.</p>
                <?php else: ?>
                <p style="color:#aaa; font-size:12px; margin-top:15px;">Total votes: <?php echo $r['total']; ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</div>
</body>
</html>