<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
if (!isLoggedIn()) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - URS Vote</title>
    <style>
        /* Base Layout */
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        
        /* THE EXACT HEADER MATCH */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
            padding: 80px 40px 110px; /* Exact padding from your working pages */
            color: white;
            border-bottom-left-radius: 30px;
            box-shadow: 0 10px 30px rgba(30, 144, 255, 0.2);
        }

        /* THE EXACT CONTAINER MATCH */
        .container { padding: 40px; margin-top: -70px; }

        /* Typography consistency */
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

        /* Content Grid */
        .card-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 25px; 
        }

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
            background: #1e90ff; 
            color: white; 
            padding: 12px 25px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-weight: bold; 
            display: inline-block; 
            margin-top: 20px;
            transition: 0.3s;
        }
        .action-btn:hover { background: #1e3c72; transform: translateY(-2px); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-banner">
        <span class="status-pill">Academic Year 2025-2026</span>
        <h1>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p style="margin:10px 0 0; opacity:0.9;">Your voice matters. Make sure to cast your vote before the deadline.</p>
    </div>

    <div class="container">
        <div class="card-grid">
            <div class="glass-card">
                <span class="card-icon">🏛️</span>
                <h3>Election Status</h3>
                <p style="color: #28a745; font-weight: bold; margin-top:10px;">● System Live & Active</p>
            </div>

            <div class="glass-card">
                <span class="card-icon">📝</span>
                <h3>My Participation</h3>
                <p style="color: #666; margin-top:10px;">Status: <span style="color: orange; font-weight: bold;">Pending</span></p>
            </div>

            <div class="glass-card">
                <span class="card-icon">👥</span>
                <h3>Registered Candidates</h3>
                <p style="color: #666; margin-top:10px;">Total: <b>18 Candidates</b></p>
            </div>
        </div>

        <div class="glass-card" style="margin-top: 30px; border-left: 6px solid #1e90ff;">
            <h3>Quick Actions</h3>
            <p style="color: #888; margin-top: 10px;">Choose an option below to begin your voting process.</p>
            <a href="vote.php" class="action-btn">Start Voting Now</a>
        </div>
    </div>
</div>

</body>
</html>