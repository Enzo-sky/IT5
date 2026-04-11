<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireAuth();

// Only super admin can manage users
if ($_SESSION['role'] !== 'admin') {
    header('Location: admin_dashboard.php');
    exit();
}

$db = getDB();
$message = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'create_user':
            $stmt = $db->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            try {
                $stmt->execute([
                    $_POST['username'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['role']
                ]);
                $message = 'User created successfully';
            } catch (PDOException $e) {
                $error = 'Username already exists';
            }
            break;
            
        case 'update_user':
            if ($_POST['id'] == $_SESSION['user_id'] && $_POST['role'] !== 'admin') {
                $error = 'Cannot demote yourself';
            } else {
                $sql = "UPDATE users SET username = ?, role = ?";
                $params = [$_POST['username'], $_POST['role']];
                if (!empty($_POST['password'])) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
                }
                $sql .= " WHERE id = ?";
                $params[] = $_POST['id'];
                
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute($params);
                    $message = 'User updated successfully';
                } catch (PDOException $e) {
                    $error = 'Error updating user';
                }
            }
            break;
            
        case 'delete_user':
            if ($_POST['id'] == $_SESSION['user_id']) {
                $error = 'Cannot delete yourself';
            } else {
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = 'User deleted successfully';
            }
            break;
    }
}

// Get users
$users = $db->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT id, username, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Voting System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .sidebar-link { transition: all 0.2s; }
        .sidebar-link:hover { background: rgba(255,255,255,0.1); }
        .sidebar-link.active { background: rgba(255,255,255,0.2); border-right: 3px solid #fff; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 text-white flex flex-col overflow-y-auto" style="background: linear-gradient(180deg, #1565C0 0%, #1e90ff 100%);">
            <div class="p-6 border-b border-blue-400 border-opacity-40">
                <div class="flex items-center gap-3">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white-400"></i>
                    <div>
                        <h1 class="font-bold text-lg">ADMIN</h1>
                    </div>
                </div>
            </div>

            <nav class="flex-1 py-6 space-y-1">
                <a href="admin_dashboard.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    Dashboard
                </a>
                <a href="elections.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="vote" class="w-5 h-5"></i>
                    Elections
                </a>
                <a href="ballot_builder.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Ballot Builder
                </a>
                <a href="voters.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    Voter Management
                </a>
                <a href="admin_results.php" class="sidebar-link flex items-center gap-3 px-6 py-3 text-sm font-medium text-slate-300 hover:text-white">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                    Results & Reports