<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$candidates = [
    "President" => "Verdadero, Lawrence Esplana",
    "Vice President" => "Toquero, LadyMargareth Matawaran",
    "Secretary" => "Roque, Cymon Lavarro",
    "Treasurer" => "Ceñidoza, John Patrick Cequeña",
    "Auditor" => "Tañan, Joy Arianne Villanueva",
    "PIO" => "Relatorres, Alexander Parta",
    "Project Manager" => "Tubog, Franco Miguel Francisco",
    "Escort" => "Fragada, Gabriel Mikhail Navato",
    "Muse" => "Piñones, Sunrio Kate Murao"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidates - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        
        /* Standardized Header */
        .welcome-banner {
            background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%);
            padding: 80px 40px 110px;
            color: white;
            border-bottom-left-radius: 30px;
            box-shadow: 0 10px 30px rgba(30, 144, 255, 0.2);
        }

        .container { padding: 40px; margin-top: -70px; }

        .card-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
        }

        .glass-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.03);
            border: 1px solid rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: 0.3s;
        }

        .glass-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(30, 144, 255, 0.1); }

        .avatar-circle {
            width: 65px;
            height: 65px;
            background: #eef7ff;
            color: #1e90ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 22px;
            border: 2px solid #1e90ff;
        }

        .role-label {
            color: #1e90ff;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 5px;
        }

        h3 { margin: 0; color: #333; font-size: 18px; }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-banner">
        <h1 style="margin:0;">Official Candidates</h1>
        <p style="margin:10px 0 0; opacity:0.9;">Academic Year 2025-2026</p>
    </div>

    <div class="container">
        <div class="card-grid">
            <?php foreach($candidates as $pos => $name): ?>
            <div class="glass-card">
                <div class="avatar-circle"><?php echo substr($name, 0, 1); ?></div>
                <div>
                    <p class="role-label"><?php echo $pos; ?></p>
                    <h3><?php echo $name; ?></h3>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>