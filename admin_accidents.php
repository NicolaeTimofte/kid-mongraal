<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
require_once 'includes/admin.php';
requireLogin();
requireAdmin();

$message = '';
$message_type = '';
$accidents = [];
$edit_accident = null;
$mode = 'view'; // view, add, edit

// Check mode
if (isset($_GET['add'])) {
    $mode = 'add';
} 
// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $type = trim($_POST['type'] ?? '');
            $latitude = $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
            $longitude = $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
            $description = trim($_POST['description'] ?? '');
            
            if (empty($type)) {
                $message = 'Type is required';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $pdo->prepare("INSERT INTO accidents (type, latitude, longitude, description) VALUES (:type, :latitude, :longitude, :description)");
                    $stmt->bindParam(':type', $type);
                    $stmt->bindParam(':latitude', $latitude);
                    $stmt->bindParam(':longitude', $longitude);
                    $stmt->bindParam(':description', $description);
                    $stmt->execute();
                    
                    $message = 'Accident added successfully!';
                    $message_type = 'success';
                    $mode = 'view';
                } catch(PDOException $e) {
                    error_log("Error adding accident: " . $e->getMessage());
                    $message = 'Error adding accident';
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $accident_id = intval($_POST['accident_id'] ?? 0);
            
            if ($accident_id > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM accidents WHERE id = :id");
                    $stmt->bindParam(':id', $accident_id);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $message = 'Accident deleted successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Accident not found';
                        $message_type = 'error';
                    }
                } catch(PDOException $e) {
                    error_log("Error deleting accident: " . $e->getMessage());
                    $message = 'Error deleting accident';
                    $message_type = 'error';
                }
            }
        }
    }
}

// Fetch accidents for view mode
if ($mode === 'view') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM accidents ORDER BY ID DESC");
        $stmt->execute();
        $accidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching accidents: " . $e->getMessage());
        $message = 'Error fetching accidents';
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KiM - Admin Accidents</title>
    <link rel="stylesheet" href="css/mainaccidentsadmin.css">
</head>
<body>
    <header>
        <nav>
            <h1><?php echo $mode === 'add' ? 'Add Accident' : 'Admin - Accident Management'; ?></h1>
            <div>
                <a href="index.php" class="home-btn">Map Page</a>
            </div>
        </nav>
    </header>

    <main>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'view'): ?>
            <div class="accidents-container">
                <div class="add-button">
                    <a href="admin_accidents.php?add=1" class="btn-add">+ Add New Accident</a>
                </div>

                <div class="total-count">
                    <h2>Total Accidents: <?php echo count($accidents); ?></h2>
                </div>

                <?php if (empty($accidents)): ?>
                    <div class="no-accidents">
                        <h3>No accidents found</h3>
                        <p>No existing accidents in the database.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($accidents as $accident): ?>
                        <div class="accident-card">
                            <div class="accident-title">
                                Accident #<?php echo htmlspecialchars($accident['ID'] ?? $accident['id'] ?? 'N/A'); ?>
                            </div>
                            
                            <div class="accident-info">
                                <div class="info-row">
                                    <span class="info-label">Type:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($accident['TYPE'] ?? $accident['type'] ?? 'Unspecified'); ?></span>
                                </div>
                                
                                <?php if (!empty($accident['LATITUDE']) || !empty($accident['latitude'])): ?>
                                <div class="info-row">
                                    <span class="info-label">Coordinates:</span>
                                    <span class="info-value">
                                        <?php echo htmlspecialchars($accident['LATITUDE'] ?? $accident['latitude'] ?? ''); ?>, 
                                        <?php echo htmlspecialchars($accident['LONGITUDE'] ?? $accident['longitude'] ?? ''); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($accident['DESCRIPTION']) || !empty($accident['description'])): ?>
                            <div class="accident-description">
                                <h4>Description:</h4>
                                <p><?php echo nl2br(htmlspecialchars($accident['DESCRIPTION'] ?? $accident['description'] ?? '')); ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="admin-buttons">
                                <form style="display: inline;" method="POST" onsubmit="return confirm('Are you sure you want to delete this accident?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="accident_id" value="<?php echo htmlspecialchars($accident['ID'] ?? $accident['id']); ?>">
                                    <button type="submit" class="btn-small btn-delete">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <?php elseif ($mode === 'add'): ?>
            <div class="form-container">
                <h2>Add New Accident</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label for="type">Type:</label>
                        <input type="text" name="type" id="type" required placeholder="Type">
                    </div>
                    
                    <div class="form-group">
                        <label for="latitude">Latitude:</label>
                        <input type="number" name="latitude" id="latitude" step="0.000001" placeholder="Latitude of location">
                    </div>
                    
                    <div class="form-group">
                        <label for="longitude">Longitude:</label>
                        <input type="number" name="longitude" id="longitude" step="0.000001" placeholder="Longitude of location">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description:</label>
                        <textarea name="description" id="description" placeholder="Optional description of the accident..."></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Add Accident</button>
                        <a href="admin_accidents.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>