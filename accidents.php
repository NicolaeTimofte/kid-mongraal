<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$message_type = '';
$accidents = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM accidents ORDER BY ID DESC");
    $stmt->execute();
    $accidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error fetching accidents: " . $e->getMessage());
    $message = 'Eroare la încărcarea accidentelor';
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista Accidente - KiD</title>
    <link rel="stylesheet" href="css/mainaccidents.css">
</head>
<body>
    <header>
        <nav>
            <h1>Lista Accidente</h1>
            <div>
                <a href="index.php" class="btn">Acasă</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="accidents-container">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Total Count -->
            <div class="total-count">
                <h2>Total Accidente: <?php echo count($accidents); ?></h2>
            </div>

            <!-- Accidents List -->
            <?php if (empty($accidents)): ?>
                <div class="no-accidents">
                    <h3>Nu au fost găsite accidente</h3>
                    <p>Nu există accidente în baza de date.</p>
                </div>
            <?php else: ?>
                <?php foreach ($accidents as $accident): ?>
                    <div class="accident-card">
                        <div class="accident-title">
                            Accident #<?php echo htmlspecialchars($accident['ID'] ?? $accident['id'] ?? 'N/A'); ?>
                        </div>
                        
                        <div class="accident-info">
                            <div class="info-row">
                                <span class="info-label">Locație:</span>
                                <span class="info-value"><?php echo htmlspecialchars($accident['LOCATION'] ?? $accident['location'] ?? 'Necunoscută'); ?></span>
                            </div>
                            
                            <div class="info-row">
                                <span class="info-label">Tip:</span>
                                <span class="info-value"><?php echo htmlspecialchars($accident['TYPE'] ?? $accident['type'] ?? 'Nespecificat'); ?></span>
                            </div>
                            
                            <?php if (!empty($accident['LATITUDE']) || !empty($accident['latitude'])): ?>
                            <div class="info-row">
                                <span class="info-label">Coordonate:</span>
                                <span class="info-value">
                                    <?php echo htmlspecialchars($accident['LATITUDE'] ?? $accident['latitude'] ?? ''); ?>, 
                                    <?php echo htmlspecialchars($accident['LONGITUDE'] ?? $accident['longitude'] ?? ''); ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($accident['DESCRIPTION']) || !empty($accident['description'])): ?>
                        <div class="accident-description">
                            <h4>Descriere:</h4>
                            <p><?php echo nl2br(htmlspecialchars($accident['DESCRIPTION'] ?? $accident['description'] ?? '')); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>