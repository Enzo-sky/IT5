<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

    body {
      display: flex;
      min-height: 100vh;
      background: #f0f2f5;
    }

    /* SIDEBAR */
    .sidebar {
      width: 220px;
      background: #1E90FF;
      color: white;
      display: flex;
      flex-direction: column;
      padding: 20px;
    }
    .sidebar h2 { margin-bottom: 30px; font-size: 20px; text-align: center; }
    .sidebar a {
      color: white;
      text-decoration: none;
      margin: 10px 0;
      padding: 10px;
      display: block;
      border-radius: 5px;
      transition: 0.3s;
    }
    .sidebar a:hover { background: rgba(255,255,255,0.2); }
    #logoutBtn { color: #ff4d4d; } /* red logout text */

    /* MAIN CONTENT */
    .main { flex: 1; padding: 20px; display: flex; flex-direction: column; align-items: center; }

    /* PROFILE CARD */
    .profile-card {
      width: 100%;
      max-width: 1000px;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      text-align: center;
      position: relative;
      margin-bottom: 30px;
    }

    .cover-photo {
      width: 100%;
      height: 200px;
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      position: relative;
    }

    .profile-pic {
      width: 170px;
      height: 170px;
      border-radius: 50%;
      object-fit: cover;
      border: 5px solid white;
      position: absolute;
      top: 130px;
      left: 50%;
      transform: translateX(-50%);
      background: #ccc;
    }

    .profile-info {
      padding: 100px 30px 30px;
    }

    .profile-info h1 { font-size: 28px; margin-bottom: 8px; }
    .profile-info p { font-size: 16px; color: #555; margin: 6px 0; }
    .status { font-weight: bold; }

    /* QUICK ACTION BUTTONS */
    .action-btns {
      margin-top: 15px;
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
    }
    .btn {
      background: #1E90FF;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-size: 15px;
      transition: 0.3s;
    }
    .btn:hover { background: #0f6cd4; }

    /* ACADEMIC INFO CARDS */
    .info-cards {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-top: 25px;
      flex-wrap: wrap;
    }
    .card {
      background: #f8f9fa;
      padding: 15px 25px;
      border-radius: 10px;
      min-width: 150px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      text-align: center;
    }
    .card h3 { margin-bottom: 5px; color: #333; font-size: 16px; }
    .card p { color: #555; font-size: 14px; }

    /* VOTING BADGES */
    .badges {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 25px;
      flex-wrap: wrap;
    }
    .badge {
      background: #1E90FF;
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 13px;
    }

    /* MOBILE */
    @media(max-width: 768px) {
      body { flex-direction: column; }
      .sidebar { width: 100%; flex-direction: row; justify-content: space-around; padding: 10px; }
      .sidebar a { margin: 0; padding: 8px; }
      .profile-pic { width: 120px; height: 120px; top: 100px; }
      .profile-info { padding-top: 80px; }
      .info-cards { flex-direction: column; align-items: center; }
      .badges { flex-direction: column; align-items: center; }
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

  <!-- Date & Time -->
  <div id="dateTime" style="margin-top:auto; text-align:center; font-size:14px; color:white;"></div>
</div>

<!-- MAIN CONTENT -->
<div class="main">

  <div class="profile-card">
    <div class="cover-photo"></div>
    <img src="mj.jpg" alt="Profile Picture" class="profile-pic" id="profilePic">
    <div class="profile-info">
      <h1 id="studentName">Random Student</h1>
      <p><strong>Student ID:</strong> <span id="studentID">B2024-00432</span></p>
      <p><strong>Voting Status:</strong> <span id="votingStatus" class="status">🟢 Voting is Open</span></p>
      <p><strong>Already Voted:</strong> <span id="votedStatus">No</span></p>

      <div class="action-btns">
        <button class="btn" id="changePassBtn">Change Password</button>
        <button class="btn" id="editProfileBtn">Edit Profile</button>
      </div>

      <!-- Academic Info -->
      <div class="info-cards">
        <div class="card"><h3>Program</h3><p>BS Information Technology</p></div>
        <div class="card"><h3>Year Level</h3><p>2nd Year</p></div>
        <div class="card"><h3>Department</h3><p>College of Computer Studies</p></div>
      </div>

      <!-- Voting Badges -->
      <div class="badges">
        <div class="badge">Voted 2023 Election</div>
        <div class="badge">Eligible 2024 Election</div>
      </div>

    </div>
  </div>

</div>

<script>
  // Load student info
  const studentNameEl = document.getElementById("studentName");
  const studentIDEl = document.getElementById("studentID");
  const votedStatusEl = document.getElementById("votedStatus");
  const votingStatusEl = document.getElementById("votingStatus");

  const studentName = localStorage.getItem("user") || "Mj Allen";
  const studentID = localStorage.getItem("studentID") || "B2024-00432";
  const voted = localStorage.getItem("voted") === "true";
  const votingOpen = true;

  studentNameEl.innerText = studentName;
  studentIDEl.innerText = studentID;
  votedStatusEl.innerText = voted ? "Yes" : "No";
  votingStatusEl.innerText = votingOpen ? "🟢 Voting is Open" : "🔴 Voting is Closed";

  // Change password
  document.getElementById("changePassBtn").addEventListener("click", () => {
    const newPass = prompt("Enter your new password:");
    if(newPass) {
      localStorage.setItem("password", newPass);
      alert("Password updated successfully!");
    }
  });

  // Edit profile
  document.getElementById("editProfileBtn").addEventListener("click", () => {
    const newName = prompt("Enter your name:", studentName);
    if(newName) {
      localStorage.setItem("user", newName);
      studentNameEl.innerText = newName;
    }
    const newID = prompt("Enter your Student ID:", studentID);
    if(newID) {
      localStorage.setItem("studentID", newID);
      studentIDEl.innerText = newID;
    }
    alert("Profile updated!");
  });

  // Logout
  document.getElementById("logoutBtn").addEventListener("click", (e) => {
    e.preventDefault();
    if(confirm("Are you sure you want to logout?")) {
      // Clear all user-related localStorage
      localStorage.removeItem("user");
      localStorage.removeItem("studentID");
      localStorage.removeItem("password");
      localStorage.removeItem("voted");
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
</script>

</body>
</html>