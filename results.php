<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$results = [
    "President" => ["winner" => "Verdadero, Lawrence Esplana", "percent" => 75],
    "Vice President" => ["winner" => "Toquero, LadyMargareth Matawaran", "percent" => 68],
    "Secretary" => ["winner" => "Roque, Cymon Lavarro", "percent" => 82],
    "Treasurer" => ["winner" => "Ceñidoza, John Patrick Cequeña", "percent" => 55],
    "Auditor" => ["winner" => "Tañan, Joy Arianne Villanueva", "percent" => 91],
    "PIO" => ["winner" => "Relatorres, Alexander Parta", "percent" => 64],
    "Project Manager" => ["winner" => "Tubog, Franco Miguel Francisco", "percent" => 77],
    "Escort" => ["winner" => "Fragada, Gabriel Mikhail Navato", "percent" => 60],
    "Muse" => ["winner" => "Piñones, Sunrio Kate Murao", "percent" => 88]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Results - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        /* Standardized Header Size */
        .welcome-banner { background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%); padding: 80px 40px 110px; color: white; border-bottom-left-radius: 30px; }
        /* Standardized Spacing */
        .container { padding: 40px; margin-top: -70px; }
        .glass-card { background: #ffffff; padding: 30px; border-radius: 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.03); margin-bottom: 25px; }
        .winner-tag { background: #d4edda; color: #155724; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; float: right; }
        .progress-bar { height: 12px; background: #f0f2f5; border-radius: 10px; margin-top: 15px; overflow: hidden; }
        .progress-fill { height: 100%; background: #1e90ff; border-radius: 10px; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { text-align: left; color: #888; font-size: 12px; padding-bottom: 10px; }
        .table-custom td { padding: 15px 0; border-top: 1px solid #f0f2f5; font-weight: 500; }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>
<div class="main-content">
    <div class="welcome-banner">
        <h1 style="margin: 0; font-size: 32px;">Live Election Results</h1>
        <p style="margin: 10px 0 0; opacity: 0.9;">Real-time tally of the current election standings.</p>
    </div>
    <div class="container">
        <div class="glass-card" style="border-top: 5px solid #28a745;">
            <h3>🏆 Election Winners</h3>
            <table class="table-custom">
                <thead><tr><th>POSITION</th><th>PROCLAIMED WINNER</th></tr></thead>
                <tbody>
                    <?php foreach($results as $pos => $data): ?>
                    <tr><td><?php echo $pos; ?></td><td><?php echo $data['winner']; ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
            <?php foreach($results as $pos => $data): ?>
            <div class="glass-card">
                <span class="winner-tag">Winning</span>
                <h4 style="color: #1e3c72; margin: 0;"><?php echo $pos; ?></h4>
                <p style="margin-top: 15px; font-size: 14px;"><?php echo $data['winner']; ?> <span style="float:right; font-weight:bold;"><?php echo $data['percent']; ?>%</span></p>
                <div class="progress-bar"><div class="progress-fill" style="width: <?php echo $data['percent']; ?>%;"></div></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>