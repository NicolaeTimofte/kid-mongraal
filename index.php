<?php
require_once 'includes/session.php';
require_once 'includes/admin.php'
requireLogin();
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Page</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header>
        <nav>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <a href="auth/logout.php" class="logout-btn">Logout</a>
        </nav>
    </header>

    <main>
    <h2>KiM - Kid Web Monitor</h2>
    
    <div class="actions">
        <a href="profile.php" class="btn">Profile</a>
        <a href="children.php" class="btn">Children</a>
        <a href="accidents.php" class="btn">Accidents</a>
        <?php if (hasAdminAccess()): ?>
        <a href="admin_accidents.php" class="btn">Admin Accidents</a>
        <?php endif; ?>
        <a href="api/export_data.php" class="btn">Export Data</a>
    </div>
    
    <div class="main-container">
        <div class="map-container">
            <h3>Real-Time Location Tracking</h3>
            
            <div class="map-wrapper">
                <img src="images/map.png" alt="Harta Fortnite" class="fortnite-map">
                <canvas id="overlay" width="1080" height="1080" style="position: absolute; top: 0; left: 0; pointer-events: none;"></canvas>
            </div>
            <div class="map-legend">
                <div class="legend-item">
                    <span class="child-marker"></span> Children
                </div>
                <div class="legend-item">
                    <span class="accident-marker"></span> Accidents
                </div>
                <div class="legend-item">
                    <span class="parent-marker"></span> Your location
                </div>
            </div>
        </div>
    </div>
    <div class="info-boxes-container">
            <div class="accident-info-box">
                <h3>Closest accidents</h3>
                <div id="accident-info-container">
                    <p class="loading">Information loading...</p>
                </div>
                <div class="last-update" id="accident-last-update"></div>
            </div>
            
            <div class="parent-info-box">
                <h3>Distance from you</h3>
                <div id="parent-info-container">
                    <p class="loading">Information loading...</p>
                </div>
                <div class="last-update" id="parent-last-update"></div>
            </div>
        </div>
</main>

<script src="js/overlay-map.js"></script>
<script src="js/movement.js"></script>
<script src="js/accident.js"></script>
<script src="js/parent.js"></script>
</body>
</html>