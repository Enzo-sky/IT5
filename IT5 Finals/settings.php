<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        
        /* Standardized Header */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
            padding: 80px 40px 110px;
            color: white;
            border-bottom-left-radius: 30px;
        }

        .container { padding: 40px; margin-top: -70px; }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .settings-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.8);
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .settings-card:hover {
            transform: translateY(-5px);
            background: #f9fcff;
            border-color: #1e90ff;
        }

        .settings-card .icon-box {
            width: 50px;
            height: 50px;
            background: #eef7ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .settings-card h3 { color: #1e3c72; margin: 0; }
        .settings-card p { color: gray; font-size: 14px; margin: 0; line-height: 1.5; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-banner">
        <h1 style="margin:0;">Settings</h1>
        <p style="margin:10px 0 0; opacity:0.9;">Customize your voting experience and security preferences</p>
    </div>

    <div class="container">
        <div class="settings-grid">
            <div class="settings-card" onclick="alert('Account Settings')">
                <div class="icon-box">👤</div>
                <h3>Account Settings</h3>
                <p>Change your name, update your email, or modify your profile picture.</p>
            </div>

            <div class="settings-card" onclick="alert('Preferences')">
                <div class="icon-box">🎨</div>
                <h3>Preferences</h3>
                <p>Toggle dark mode, language settings, and display options.</p>
            </div>

            <div class="settings-card" onclick="alert('Security')">
                <div class="icon-box">🔒</div>
                <h3>Security</h3>
                <p>Manage login devices, change password, and view login history.</p>
            </div>

            <div class="settings-card" onclick="alert('Support')">
                <div class="icon-box">🎧</div>
                <h3>Help & Support</h3>
                <p>Need help? Contact the election administrator or view the user guide.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>