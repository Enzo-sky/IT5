<div class="sidebar">
    <div class="sidebar-header">
        <span class="header-box">URS VOTE</span>
    </div>
    <ul class="nav-links">
        <li><a href="admin_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">📊</span> Dashboard</a>
        </li>

        <li><a href="vote.php" class="<?= basename($_SERVER['PHP_SELF']) == 'vote.php' ? 'active' : '' ?>">
            <span class="icon">🗳️</span> Vote Now</a>
        </li>

        <li><a href="candidates.php" class="<?= basename($_SERVER['PHP_SELF']) == 'candidates.php' ? 'active' : '' ?>">
            <span class="icon">👥</span> Candidates</a>
        </li>

        <li><a href="results.php" class="<?= basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : '' ?>">
            <span class="icon">📈</span> Results</a>
        </li>

        <li><a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
            <span class="icon">👤</span> My Profile</a>
        </li>

        <li><a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <span class="icon">⚙️</span> Settings</a>
        </li>

        <li class="logout-item">
            <a href="logout.php" id="logoutBtn">
                <span class="icon">🚪</span> Logout
            </a>
        </li>
    </ul>
    
    <div id="sidebarTime" style="margin-top: auto; padding: 20px; font-size: 11px; color: rgba(255,255,255,0.6); text-align: center; border-top: 1px solid rgba(255,255,255,0.1);">
    </div>
</div>

<style>
    :root { 
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
        --glass: rgba(255, 255, 255, 0.1);
    }

    .sidebar { 
        width: 260px; 
        background: var(--primary-gradient);
        height: 100vh; 
        position: fixed; 
        display: flex;
        flex-direction: column;
        color: #fff;
        box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        z-index: 1000;
    }

    .sidebar-header { padding: 45px 20px; text-align: center; }

    .header-box {
        border: 2px solid #fff;
        padding: 8px 18px;
        border-radius: 6px;
        font-weight: 800;
        font-size: 22px;
        letter-spacing: 1px;
        display: inline-block;
    }

    .nav-links { list-style: none; padding: 10px; flex-grow: 1; }

    .nav-links li a { 
        display: flex;
        align-items: center;
        padding: 12px 18px; 
        color: rgba(255,255,255,0.8);
        text-decoration: none; 
        margin-bottom: 5px;
        border-radius: 10px;
        transition: 0.3s;
        font-size: 14px;
    }

    .nav-links li a:hover, .nav-links li a.active { 
        background: var(--glass);
        color: #fff;
        transform: translateX(5px);
    }

    .icon { margin-right: 12px; font-size: 18px; }

    .logout-item a { color: #ff9999 !important; font-weight: bold; }
    .logout-item a:hover { background: rgba(255,0,0,0.1) !important; }
</style>

<script>
    // Sidebar Clock Sync
    function updateSidebarTime() {
        const now = new Date();
        document.getElementById("sidebarTime").innerText = now.toLocaleString();
    }
    setInterval(updateSidebarTime, 1000);
    updateSidebarTime();
</script>