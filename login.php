<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <script src="urs.png"></script>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      height: 100vh;
      display: flex;
    }

    /* LEFT SIDE */
    .left {
      width: 50%;
      background: #ffffff;
      padding: 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .logo img {
      height: 100px;   /* small logo */
      width: auto;
      display: block;
      margin-bottom: 50px;
    }

    h1 {
      font-size: 36px;
      margin-bottom: 10px;
    }

    .subtitle {
      color: gray;
      margin-bottom: 30px;
    }

    label {
      font-size: 14px;
      margin-top: 15px;
      display: block;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .user-type {
      margin-top: 15px;
      display: flex;
      gap: 20px;
    }

    .options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 15px;
      font-size: 13px;
    }

    .options a {
      color: #1E90FF;
      text-decoration: none;
    }

    .btn {
      margin-top: 20px;
      background: #1E90FF;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 6px;
      width: 100%;
      cursor: pointer;
      font-size: 16px;
    }

    .btn:hover {
      background: #0f6cd4;
    }

    .error {
      color: red;
      margin-top: 10px;
      display: none;
      font-size: 13px;
    }

    /* RIGHT SIDE */
    .right {
      width: 50%;
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .right img {
      width: 70%;      /* bigger illustration */
      max-width: none; /* remove old limit */
      display: block;
    }

    /* MOBILE */
    @media(max-width: 768px) {
      .right {
        display: none;
      }
      .left {
        width: 100%;
        padding: 30px;
      }
      .logo img {
        height: 40px;
      }
    }
  </style>
</head>

<body>

<!-- LEFT SIDE -->
<div class="left">

  <div class="logo">
    <img src="urs.png" alt="Logo">
  </div>

  <h1>Welcome back</h1>
  <p class="subtitle">Please enter your details</p>

  <form id="loginForm">

    <!-- USER TYPE -->
    <div class="user-type">
      <label><input type="radio" name="userType" value="student" checked> Student</label>
      <label><input type="radio" name="userType" value="admin"> Admin</label>
    </div>

    <!-- ID -->
    <label>ID</label>
    <input type="text" id="studentID" required>

    <!-- PASSWORD -->
    <label>Password</label>
    <input type="password" id="password" required>

    <!-- OPTIONS -->
    <div class="options">
      <label>
        <input type="checkbox" id="showPass"> Show Password
      </label>
      <a href="#">Forgot password?</a>
    </div>

    <p id="errorMsg" class="error">Invalid credentials</p>

    <button type="submit" class="btn">Sign in</button>

  </form>

</div>

<!-- RIGHT SIDE -->
<div class="right">
  <!-- PLACEHOLDER IMAGE -->
  <img src="illustration.png" alt="Illustration">
</div>

<script>
  const showPass = document.getElementById("showPass");
  const password = document.getElementById("password");

  showPass.addEventListener("change", () => {
    password.type = showPass.checked ? "text" : "password";
  });

  const loginForm = document.getElementById("loginForm");
  const errorMsg = document.getElementById("errorMsg");

  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const id = document.getElementById("studentID").value;
    const pass = document.getElementById("password").value;
    const userType = document.querySelector('input[name="userType"]:checked').value;

    if(userType === "student" && id === "12345" && pass === "password") {
      localStorage.setItem("user", "Student");
      localStorage.setItem("userType", "student");
      alert("Student login success");
    }
    else if(userType === "admin" && id === "admin" && pass === "admin123") {
      localStorage.setItem("user", "Admin");
      localStorage.setItem("userType", "admin");
      alert("Admin login success");
    }
    else {
      errorMsg.style.display = "block";
    }
  });
</script>

</body>
</html>