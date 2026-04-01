<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        
        /* Standardized Header */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
            padding: 80px 40px 110px;
            color: white;
            border-bottom-left-radius: 30px;
            text-align: center;
        }

        .container { padding: 40px; margin-top: -70px; display: flex; flex-direction: column; align-items: center; }

        .profile-card {
            background: white;
            width: 100%;
            max-width: 900px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.8);
        }

        .profile-header {
            padding: 40px;
            text-align: center;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: -110px;
            background: #eee;
            object-fit: cover;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #fafbfc;
        }

        .info-item {
            background: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid #eee;
        }

        .info-item h4 { color: #1e90ff; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; }

        .btn-group { padding: 20px; display: flex; justify-content: center; gap: 10px; }
        .btn-action {
            background: #1e90ff;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-action:hover { background: #1e3c72; transform: translateY(-2px); }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-banner">
        <h1 id="studentNameDisplay" style="margin:0;">Student Profile</h1>
        <p style="margin:10px 0 0; opacity:0.9;">Manage your academic identity and voting credentials</p>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <img src="mj.jpg" alt="Profile" class="profile-pic" id="profilePic">
                <h2 id="studentName" style="margin-top:15px;">Random Student</h2>
                <p style="color: gray;">ID: <span id="studentID">B2024-00432</span></p>
                
                <div style="margin-top: 15px;">
                    <span id="votingStatus" style="background:#eef7ff; color:#1e90ff; padding:5px 15px; border-radius:20px; font-weight:bold; font-size:13px;">🟢 Voting is Open</span>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item"><h4>Program</h4><p>BS Information Technology</p></div>
                <div class="info-item"><h4>Year Level</h4><p>2nd Year</p></div>
                <div class="info-item"><h4>Already Voted</h4><p id="votedStatus">No</p></div>
            </div>

            <div class="btn-group">
                <button class="btn-action" id="editProfileBtn">Edit Profile</button>
                <button class="btn-action" id="changePassBtn" style="background:#333;">Change Password</button>
            </div>
        </div>
    </div>
</div>

<script>
    const studentNameEl = document.getElementById("studentName");
    const studentIDEl = document.getElementById("studentID");
    const votedStatusEl = document.getElementById("votedStatus");

    const studentName = localStorage.getItem("user") || "Mj Allen";
    const studentID = localStorage.getItem("studentID") || "B2024-00432";
    const voted = localStorage.getItem("voted") === "true";

    studentNameEl.innerText = studentName;
    document.getElementById("studentNameDisplay").innerText = studentName;
    studentIDEl.innerText = studentID;
    votedStatusEl.innerText = voted ? "Yes" : "No";

    document.getElementById("editProfileBtn").addEventListener("click", () => {
        const newName = prompt("Enter your name:", studentName);
        if(newName) {
            localStorage.setItem("user", newName);
            location.reload();
        }
    });

    document.getElementById("changePassBtn").addEventListener("click", () => {
        const newPass = prompt("Enter new password:");
        if(newPass) alert("Password updated successfully!");
    });
</script>
</body>
</html>