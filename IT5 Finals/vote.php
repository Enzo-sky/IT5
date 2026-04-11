<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$ballot = [
    "President" => ["Verdadero, Lawrence Esplana", "Opposition A"],
    "Vice President" => ["Toquero, LadyMargareth Matawaran", "Opposition B"],
    "Secretary" => ["Roque, Cymon Lavarro", "Opposition C"],
    "Treasurer" => ["Ceñidoza, John Patrick Cequeña", "Opposition D"],
    "Auditor" => ["Tañan, Joy Arianne Villanueva", "Opposition E"],
    "PIO" => ["Relatorres, Alexander Parta", "Opposition F"],
    "Project Manager" => ["Tubog, Franco Miguel Francisco", "Opposition G"],
    "Escort" => ["Fragada, Gabriel Mikhail Navato", "Opposition H"],
    "Muse" => ["Piñones, Sunrio Kate Murao", "Opposition I"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vote Now - URS Vote</title>
    <style>
        body { margin: 0; background: #f0f2f5; font-family: 'Inter', sans-serif; display: flex; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        /* Standardized Header Size */
        .banner { background: linear-gradient(135deg, #1e3c72 0%, #1e90ff 100%); padding: 80px 40px 110px; color: white; border-bottom-left-radius: 30px; }
        /* Standardized Spacing */
        .container { padding: 40px; margin-top: -70px; max-width: 900px; }
        .vote-card { background: white; padding: 25px; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .option { display: block; padding: 15px; border: 2px solid #f0f2f5; border-radius: 12px; margin-top: 10px; cursor: pointer; transition: 0.2s; }
        .option:hover { border-color: #1e90ff; background: #f9fcff; }
        input[type="radio"] { margin-right: 10px; accent-color: #1e90ff; }
        .btn-submit { background: #1e90ff; color: white; border: none; padding: 20px; border-radius: 15px; width: 100%; font-weight: bold; font-size: 18px; cursor: pointer; box-shadow: 0 10px 20px rgba(30, 144, 255, 0.3); }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="banner">
            <h1 style="margin: 0; font-size: 32px;">Cast Your Vote</h1>
            <p style="margin: 10px 0 0; opacity: 0.9;">Select your preferred candidates carefully.</p>
        </div>
        <form action="results.php" method="POST" class="container">
            <?php foreach($ballot as $pos => $names): ?>
            <div class="vote-card">
                <h3 style="color: #1e3c72; border-bottom: 2px solid #f0f2f5; padding-bottom: 10px; margin-bottom: 15px;"><?php echo $pos; ?></h3>
                <?php foreach($names as $n): ?>
                <label class="option"><input type="radio" name="<?php echo $pos; ?>" value="<?php echo $n; ?>" required> <?php echo $n; ?></label>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" name="submit_vote" class="btn-submit">Confirm & Submit Ballot</button>
        </form>
    </div>
</body>
</html>