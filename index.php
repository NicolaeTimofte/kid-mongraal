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
        <h2>Aceasta este pagina principală</h2>
        <p>Conținutul protejat al site-ului tău aici...</p>
        
        <div class="actions">
            <a href="profile.php" class="btn">Profil</a>
            <a href="children.php" class="btn">Copii</a>
            <a href="data.php" class="btn">Date</a>
            <a href="accidents.php" class="btn">Accidente</a>
        </div>
    </main>
</body>
</html>