<?php
session_start();
if (isset($_POST['confirm_logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$back_url = isset($_SESSION['role']) && $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logout - URS Vote</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
        }

        body { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            background: var(--primary-gradient); 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Decorative Background Circles like the Illustration theme */
        body::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            top: -100px; left: -100px;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            bottom: -50px; right: -50px;
        }

        .confirm-box { 
            background: white; 
            padding: 50px 40px; 
            border-radius: 25px; 
            text-align: center; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); 
            z-index: 2;
            width: 90%;
            max-width: 400px;
        }

        .logout-icon {
            font-size: 50px;
            margin-bottom: 20px;
            display: block;
        }

        h2 { color: #1e3c72; margin-bottom: 10px; font-weight: 800; font-size: 24px; }
        p { color: #666; margin-bottom: 30px; font-size: 15px; line-height: 1.5; }

        .btn-group { display: flex; flex-direction: column; gap: 10px; }

        .btn { 
            padding: 15px; 
            border: none; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 16px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-yes { 
            background: #ff4d4d; 
            color: white; 
            box-shadow: 0 8px 15px rgba(255, 77, 77, 0.3);
        }
        
        .btn-yes:hover { 
            background: #e60000; 
            transform: translateY(-2px);
        }

        .btn-no { 
            background: #f0f2f5; 
            color: #1e3c72; 
        }

        .btn-no:hover { 
            background: #e4e6e9; 
        }
    </style>
</head>
<body>
    <div class="confirm-box">
        <span class="logout-icon">🚪</span>
        <h2>Ready to Leave?</h2>
        <p>You are about to log out of URS Vote. You'll need to sign back in to cast any further votes.</p>
        
        <div class="btn-group">
            <form method="POST" style="display: contents;">
                <button type="submit" name="confirm_logout" class="btn btn-yes">Yes, Log Me Out</button>
            </form>
            <a href="dashboard.php" class="btn btn-no">No, Stay Logged In</a>
        </div>
    </div>
</body>
</html>