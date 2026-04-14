<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

$db = getDB();

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = false;
$message = "";
$showMessage = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = sanitizeInput($_POST['id']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password !== $confirmPassword) {
        $error = "Passwords do not match!";
    } else {

        $check = $db->prepare("SELECT * FROM users WHERE username = ?");
        $check->execute([$username]);

        if ($check->fetch()) {
            $error = "Username already exists!";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO users (id, username, password, role)
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$id, $username, $hashedPassword, $role])) {

                $showMessage = true;

                if ($role === "admin") {
                    $message = "⏳ Pending for Approval";
                } else {
                    $message = "✅ Account Successfully Created";
                }

            } else {
                $error = "Signup failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup - URS Vote</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">

<style>
:root { 
    --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
    --accent-blue: #1e90ff;
}

* { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

body { height: 100vh; display: flex; overflow: hidden; background: #f0f2f5; }

.left { 
    width: 45%; 
    background: #ffffff; 
    padding: 40px 80px; 
    display: flex; 
    flex-direction: column; 
    justify-content: center; 
    box-shadow: 10px 0 30px rgba(0,0,0,0.05);
    z-index: 2;
}

        .school-photo-container {
            width: 160px;
            height: 160px;
            margin: 0 auto 25px;
            background: #ffffff;
            border-radius: 50%;
            border: 6px solid var(--accent-blue);
            padding: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 24px rgba(0,0,0,0.12);
            overflow: hidden;
        }

        .school-photo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
}

h1 { font-size: 30px; color: #1e3c72; margin-bottom: 8px; font-weight: 800; text-align: center; }
.subtitle { color: #888; margin-bottom: 30px; font-size: 14px; text-align: center; }

form { width: 100%; }

.user-type { 
    display: flex; 
    gap: 25px; 
    margin-bottom: 20px; 
    padding: 12px;
    background: #f8f9fa;
    border-radius: 12px;
}

.user-type label { 
    font-size: 14px; 
    font-weight: 600; 
    color: #555; 
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.user-type input[type="radio"] { accent-color: var(--accent-blue); }

label.input-label { 
    font-size: 11px; 
    font-weight: 700; 
    color: #1e3c72; 
    text-transform: uppercase; 
    margin-top: 15px; 
    display: block; 
    letter-spacing: 0.5px; 
}

input[type="text"], input[type="password"] { 
    width: 100%; 
    padding: 14px 18px; 
    margin-top: 8px; 
    border: 2px solid #eee; 
    border-radius: 12px; 
    background: #f9f9f9;
    transition: 0.3s;
    outline: none;
    font-size: 15px;
}

input:focus { 
    border-color: var(--accent-blue); 
    background: #fff; 
    box-shadow: 0 0 12px rgba(30, 144, 255, 0.1); 
}

.btn { 
    margin-top: 25px; 
    background: var(--primary-gradient); 
    color: white; 
    padding: 16px; 
    border: none; 
    border-radius: 12px; 
    width: 100%; 
    cursor: pointer; 
    font-size: 16px; 
    font-weight: 700;
    box-shadow: 0 10px 20px rgba(30, 144, 255, 0.3);
    transition: 0.3s;
}

.btn:hover { 
    transform: translateY(-3px); 
    box-shadow: 0 15px 30px rgba(30, 144, 255, 0.4); 
}

.error { 
    color: #dc3545; 
    background: #fff5f5;
    padding: 12px;
    border-radius: 10px;
    margin-top: 15px; 
    font-size: 13px; 
    text-align: center;
    border: 1px solid #ffc9c9;
}

.right { 
    width: 55%; 
    background: var(--primary-gradient); 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    position: relative;
}

.right::before {
    content: '';
    position: absolute;
    width: 450px; height: 450px;
    background: rgba(255,255,255,0.08);
    border-radius: 50%;
    top: -100px; right: -150px;
}

.right img { 
    width: 65%; 
    z-index: 1;
    filter: drop-shadow(0 25px 45px rgba(0,0,0,0.2));
    animation: float 4s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

@media(max-width: 1100px) { .left { padding: 40px; } }
@media(max-width: 768px) { .right { display: none; } .left { width: 100%; } }
</style>
</head>

<body>

<div class="left">

    <div class="school-photo-container">
        <img src="./urs.png" alt="School Logo">
    </div>

    <h1>Create Account</h1>
    <p class="subtitle">Register to access the system</p>

    <form method="POST">

        <div class="user-type">
            <label><input type="radio" name="role" value="student" required> Student</label>
            <label><input type="radio" name="role" value="admin"> Admin</label>
        </div>

        <label class="input-label">ID</label>
        <input type="text" name="id" required>

        <label class="input-label">Email</label>
        <input type="text" name="email">

        <label class="input-label">Phone</label>
        <input type="text" name="phone">

        <label class="input-label">Username</label>
        <input type="text" name="username" required>

        <label class="input-label">Password</label>
        <input type="password" name="password" required>

        <label class="input-label">Confirm Password</label>
        <input type="password" name="confirm_password" required>

        <button type="submit" class="btn">Sign Up</button>
    </form>

</div>

<div class="right">
    <img src="illustration.png" alt="Illustration">
</div>

<?php if ($showMessage): ?>
<script>
alert("<?php echo $message; ?>");
window.location.href = "login.php";
</script>
<?php endif; ?>

</body>
</html>