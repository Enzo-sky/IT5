<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) { header("Location: login.php"); exit(); }

$db = getDB();
$user_id = $_SESSION['user_id'];

// Get active election
$election = $db->query("SELECT * FROM elections WHERE status = 'active' ORDER BY created_at DESC LIMIT 1")->fetch();

// Check if user already voted
$already_voted = false;
if ($election) {
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM votes WHERE election_id = ? AND voter_id = ?");
    $stmt->execute([$election['id'], $user_id]);
    $already_voted = $stmt->fetch()['cnt'] > 0;
}

// Handle vote submission
$success = false;
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote']) && $election && !$already_voted) {
    try {
        $db->beginTransaction();
        foreach ($_POST as $key => $value) {
            if ($key === 'submit_vote') continue;
            // $key = position_id, $value = candidate_id
            $stmt = $db->prepare("INSERT INTO votes (election_id, voter_id, position_id, candidate_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$election['id'], $user_id, $key, $value]);
        }
        $db->commit();
        $already_voted = true;
        $success = true;
    } catch (Exception $e) {
        $db->rollBack();
        $error = 'Error submitting vote. Please try again.';
    }
}

// Get positions and candidates for the active election
$positions = [];
if ($election && !$already_voted) {
    $stmt = $db->prepare("SELECT * FROM positions WHERE election_id = ? ORDER BY id");
    $stmt->execute([$election['id']]);
    $pos_list = $stmt->fetchAll();

    foreach ($pos_list as $pos) {
        $stmt2 = $db->prepare("SELECT * FROM candidates WHERE position_id = ? ORDER BY sort_order");
        $stmt2->execute([$pos['id']]);
        $positions[] = [
            'position' => $pos,
            'candidates' => $stmt2->fetchAll()
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote Now - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .banner { background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%); padding: 80px 40px 110px; color: white; border-bottom-left-radius: 30px; }
        .container { padding: 40px; margin-top: -70px; max-width: 900px; }
        .vote-card { background: white; padding: 25px; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .option { display: block; padding: 15px; border: 2px solid #f0f2f5; border-radius: 12px; margin-top: 10px; cursor: pointer; transition: 0.2s; }
        .option:hover { border-color: #1e90ff; background: #f9fcff; }
        input[type="radio"] { margin-right: 10px; accent-color: #1e90ff; }
        .btn-submit { background: #1e90ff; color: white; border: none; padding: 20px; border-radius: 15px; width: 100%; font-weight: bold; font-size: 18px; cursor: pointer; box-shadow: 0 10px 20px rgba(30, 144, 255, 0.3); }
        .alert { padding: 20px; border-radius: 15px; margin-bottom: 20px; font-weight: bold; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .no-election { background: white; padding: 40px; border-radius: 20px; text-align: center; color: #888; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="banner">
            <h1 style="margin: 0; font-size: 32px;">Cast Your Vote</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">
                <?php echo $election ? htmlspecialchars($election['title']) : 'No active election'; ?>
            </p>
        </div>
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success">✅ Your vote has been submitted successfully! Thank you for participating.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (!$election): ?>
                <div class="no-election">
                    <p style="font-size:40px;">🗳️</p>
                    <h3>No Active Election</h3>
                    <p>There is currently no active election. Please check back later.</p>
                </div>
            <?php elseif ($already_voted && !$success): ?>
                <div class="alert alert-warning">⚠️ You have already cast your vote in this election. Each voter may only vote once.</div>
            <?php elseif (empty($positions)): ?>
                <div class="no-election">
                    <p style="font-size:40px;">📋</p>
                    <h3>No Ballot Available</h3>
                    <p>No positions or candidates have been added to this election yet.</p>
                </div>
            <?php elseif (!$success): ?>
                <form action="vote.php" method="POST" class="">
                    <?php foreach($positions as $item): ?>
                    <div class="vote-card">
                        <h3 style="color: #1e3c72; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 15px; margin-top:0;">
                            <?php echo htmlspecialchars($item['position']['title']); ?>
                        </h3>
                        <?php if (empty($item['candidates'])): ?>
                            <p style="color:#888;">No candidates for this position.</p>
                        <?php else: ?>
                            <?php foreach($item['candidates'] as $c): ?>
                            <label class="option">
                                <input type="radio" name="<?php echo $item['position']['id']; ?>" value="<?php echo $c['id']; ?>" required>
                                <?php echo htmlspecialchars($c['name']); ?>
                                <?php if (!empty($c['party'])): ?>
                                    <span style="color:#888; font-size:13px;"> — <?php echo htmlspecialchars($c['party']); ?></span>
                                <?php endif; ?>
                            </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit" name="submit_vote" class="btn-submit">Confirm & Submit Ballot</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>