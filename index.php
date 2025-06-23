<?php
require_once 'includes/session.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina Principală</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header>
        <nav>
            <h1>Bun venit, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="auth/logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main>
    <h2>KiM - Monitorizare Copii</h2>
    
    <div class="actions">
        <a href="profile.php" class="btn">Profil</a>
        <a href="children.php" class="btn">Copii</a>
        <a href="accidents.php" class="btn">Accidente</a>
    </div>
    
    <div class="map-container">
        <h3>Locații în Timp Real</h3>
        <div class="map-wrapper">
            <img src="images/map.png" alt="Harta Fortnite" class="fortnite-map">
            <canvas id="overlay" width="1080" height="1080" style="position: absolute; top: 0; left: 0; pointer-events: none;"></canvas>
        </div>
        <div class="map-legend">
            <div class="legend-item">
                <span class="child-marker"></span> Copii
            </div>
            <div class="legend-item">
                <span class="accident-marker"></span> Accidente
            </div>
            <div class="legend-item">
                <span class="parent-marker"></span> Locația ta
            </div>
        </div>
    </div>
</main>
<script src="js/movement.js"></script>
<script src="js/overlay-map.js"></script>
</body>
</html>