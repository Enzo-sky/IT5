<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Settings</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', sans-serif; }
    body { display:flex; min-height:100vh; background:#f0f2f5; }

    /* SIDEBAR */
    .sidebar {
      width:220px;
      background:#1E90FF;
      color:white;
      display:flex;
      flex-direction:column;
      padding:20px;
    }
    .sidebar h2 { margin-bottom:30px; font-size:20px; text-align:center; }
    .sidebar a {
      color:white;
      text-decoration:none;
      margin:10px 0;
      padding:10px;
      display:block;
      border-radius:5px;
      transition:0.3s;
    }
    .sidebar a:hover { background: rgba(255,255,255,0.2); }
    #logoutBtn { color:#ff4d4d; }

    /* MAIN CONTENT */
    .main { flex:1; padding:20px; display:flex; flex-direction:column; align-items:center; }

    /* SETTINGS CARDS */
    .settings-container { width:100%; max-width:800px; display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .settings-card {
      background:white;
      border-radius:12px;
      padding:30px;
      box-shadow:0 8px 20px rgba(0,0,0,0.15);
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      cursor:pointer;
      transition:0.3s;
      text-align:center;
    }
    .settings-card:hover { background:#e6f0ff; transform:translateY(-5px); }
    .settings-card h2 { font-size:18px; margin-bottom:10px; color:#1E90FF; }

    /* MOBILE */
    @media(max-width:768px) {
      body { flex-direction:column; }
      .sidebar { width:100%; flex-direction:row; justify-content:space-around; padding:10px; }
      .sidebar a { margin:0; padding:8px; }
      .settings-container { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <h2>Student Menu</h2>
  <a href="dashboard.php">Dashboard</a>
  <a href="vote.php">Vote</a>
  <a href="candidates.php">Candidates</a>
  <a href="profile.php">Profile</a>
  <a href="settings.php">Settings</a>
  <a href="#" id="logoutBtn">Logout</a>

  <div id="dateTime" style="margin-top:auto; text-align:center; font-size:14px; color:white;"></div>
</div>

<!-- MAIN CONTENT -->
<div class="main">
  <h1 style="margin-bottom:30px;">Settings</h1>
  <div class="settings-container">
    <div class="settings-card" id="accountSettings">
      <h2>Account Settings</h2>
      <p>Change your name, password, or profile picture</p>
    </div>
    <div class="settings-card" id="preferencesSettings">
      <h2>Preferences</h2>
      <p>Dark mode, notifications, and other options</p>
    </div>
    <div class="settings-card" id="securitySettings">
      <h2>Security</h2>
      <p>Manage login devices and change security settings</p>
    </div>
    <div class="settings-card" id="helpSettings">
      <h2>Help & Support</h2>
      <p>Contact admin or view help resources</p>
    </div>
  </div>
</div>

<script>
  // Logout with confirmation
  document.getElementById("logoutBtn").addEventListener("click", (e) => {
    e.preventDefault();
    if(confirm("Are you sure you want to logout?")) {
      localStorage.removeItem("user");
      window.location.href = "login.php";
    }
  });

  // Date & Time
  function updateDateTime() {
    const now = new Date();
    const options = { weekday:'short', year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit', second:'2-digit' };
    document.getElementById("dateTime").innerText = now.toLocaleDateString('en-US', options);
  }
  setInterval(updateDateTime, 1000);
  updateDateTime();

  // Settings buttons (placeholders)
  document.getElementById("accountSettings").addEventListener("click", () => { alert("Go to Account Settings"); });
  document.getElementById("preferencesSettings").addEventListener("click", () => { alert("Go to Preferences"); });
  document.getElementById("securitySettings").addEventListener("click", () => { alert("Go to Security"); });
  document.getElementById("helpSettings").addEventListener("click", () => { alert("Go to Help & Support"); });
</script>

</body>
</html>