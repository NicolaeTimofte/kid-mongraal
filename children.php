<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$message_type = '';
$children = [];

$locations = [
    'JUNK_JUNCTION' => ['x' => 171, 'y' => 127, 'name' => 'Junk Junction'],
    'HAUNTED_HILLS' => ['x' => 145, 'y' => 228, 'name' => 'Haunted Hills'],
    'PLEASANT_PARK' => ['x' => 261, 'y' => 324, 'name' => 'Pleasant Park'],
    'SNOBBY_SHORES' => ['x' => 90, 'y' => 428, 'name' => 'Snobby Shores'],
    'GREASY_GROVE' => ['x' => 247, 'y' => 659, 'name' => 'Greasy Grove'],
    'SHIFTY_SHAFTS' => ['x' => 392, 'y' => 664, 'name' => 'Shifty Shafts'],
    'FROSTY_FLIGHTS' => ['x' => 110, 'y' => 753, 'name' => 'Frosty Flights'],
    'FLUSH_FACTORY' => ['x' => 370, 'y' => 895, 'name' => 'Flush Factory'],
    'LUCKY_LANDING' => ['x' => 586, 'y' => 965, 'name' => 'Lucky Landing'],
    'FATAL_FIELDS' => ['x' => 625, 'y' => 790, 'name' => 'Fatal Fields'],
    'SALTY_SPRINGS' => ['x' => 608, 'y' => 631, 'name' => 'Salty Springs'],
    'DUSTY_DIVOT' => ['x' => 603, 'y' => 503, 'name' => 'Dusty Divot'],
    'LOOT_LAKE' => ['x' => 448, 'y' => 318, 'name' => 'Loot Lake'],
    'TILTED_TOWERS' => ['x' => 405, 'y' => 494, 'name' => 'Tilted Towers'],
    'LAZY_LINKS' => ['x' => 556, 'y' => 205, 'name' => 'Lazy Links'],
    'RISKY_REELS' => ['x' => 774, 'y' => 218, 'name' => 'Risky Reels'],
    'WAILING_WOODS' => ['x' => 863, 'y' => 302, 'name' => 'Wailing Woods'],
    'TOMATO_TEMPLE' => ['x' => 684, 'y' => 326, 'name' => 'Tomato Temple'],
    'LONELY_LODGE' => ['x' => 919, 'y' => 413, 'name' => 'Lonely Lodge'],
    'RETAIL_ROW' => ['x' => 762, 'y' => 545, 'name' => 'Retail Row'],
    'PARADISE_PALMS' => ['x' => 864, 'y' => 762, 'name' => 'Paradise Palms']
];

function getLocationCoordinates($locationName, $locations) {
    $cleanInput = strtoupper(trim($locationName));
    $locationKey = str_replace(' ', '_', $cleanInput);
    
    if (isset($locations[$locationKey])) {
        return $locations[$locationKey];
    }
    
    foreach ($locations as $key => $location) {
        if ($cleanInput === strtoupper($location['name'])) {
            return $location;
        }
    }
    
    return null;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM children WHERE user_id = :user_id ORDER BY id DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = 'Error fetching children';
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_child') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($birthdate)) {
            $message = 'First name, last name and birthdate are mandatory';
            $message_type = 'error';
        } else {
            try {
                $pdo->beginTransaction();
                
                $birthdate_formatted = date('Y-m-d', strtotime($birthdate));
                
                $stmt = $pdo->prepare("INSERT INTO children (user_id, first_name, last_name, birthdate, gender) VALUES (:user_id, :first_name, :last_name, TO_DATE(:birthdate, 'YYYY-MM-DD'), :gender)");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':birthdate', $birthdate_formatted);
                $stmt->bindParam(':gender', $gender);
                $stmt->execute();
                
                $stmt = $pdo->prepare("SELECT MAX(id) as child_id FROM children WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (isset($result['CHILD_ID'])) {
                    $child_id = $result['CHILD_ID'];
                } elseif (isset($result['child_id'])) {
                    $child_id = $result['child_id'];
                } else {
                    $child_id = reset($result);
                }
                
                $stmt = $pdo->prepare("SELECT address, latitude, longitude FROM users WHERE id = :user_id");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $parent = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $parentAddress = '';
                if (!empty($parent['ADDRESS'])) {
                    $parentAddress = $parent['ADDRESS'];
                } elseif (!empty($parent['address'])) {
                    $parentAddress = $parent['address'];
                }
                
                if (!empty($parentAddress)) {
                    $locationData = getLocationCoordinates($parentAddress, $locations);
                    
                    if ($locationData) {
                        $stmt = $pdo->prepare("INSERT INTO data (child_id, location, latitude, longitude) VALUES (:child_id, :location, :latitude, :longitude)");
                        $stmt->bindParam(':child_id', $child_id);
                        $stmt->bindParam(':location', $locationData['name']);
                        $stmt->bindParam(':latitude', $locationData['x']);
                        $stmt->bindParam(':longitude', $locationData['y']);
                        $stmt->execute();
                    } else {
                        if (!empty($parent['LATITUDE']) && !empty($parent['LONGITUDE'])) {
                            $stmt = $pdo->prepare("INSERT INTO data (child_id, location, latitude, longitude) VALUES (:child_id, :location, :latitude, :longitude)");
                            $stmt->bindParam(':child_id', $child_id);
                            $stmt->bindParam(':location', $parentAddress);
                            $stmt->bindParam(':latitude', $parent['LATITUDE']);
                            $stmt->bindParam(':longitude', $parent['LONGITUDE']);
                            $stmt->execute();
                        }
                    }
                }
                
                $pdo->commit();
                
                $message = 'Child successfully added';
                $message_type = 'success';
                
                $stmt = $pdo->prepare("SELECT * FROM children WHERE user_id = :user_id ORDER BY id DESC");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $message = 'Error adding child';
                $message_type = 'error';
            }
        }
    } 
}

function calculateAge($birthdate)
{
    if (empty($birthdate)) return 'N/A';
    
    try {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birth);
        
        if ($age->y > 0) {
            return $age->y . ' years';
        } elseif ($age->m > 0) {
            return $age->m . ' months';
        } else {
            return $age->d . ' days';
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KiM - Children</title>
    <link rel="stylesheet" href="css/mainchildren.css">
</head>
<body>
    <header>
        <nav>
            <h1>Children - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <div>
                <a href="index.php" class="home-btn">Map Page</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="children-container">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="add-child-form">
                <h2>Add New Child</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_child">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthdate">Birthdate:</label>
                            <input type="date" id="birthdate" name="birthdate" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender">
                                <option value="">Select</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Add Child</button>
                </form>
            </div>

            <div class="total-count">
                <h2>Total Children: <?php echo count($children); ?></h2>
            </div>

            <?php if (empty($children)): ?>
                <div class="no-children">
                    <h3>No children registered</h3>
                    <p>Use the form above to add your first child.</p>
                </div>
            <?php else: ?>
                <div class="children-grid">
                    <?php foreach ($children as $child): ?>
                        <div class="child-card">
                            <div class="child-header">
                                <h3><?php echo htmlspecialchars(($child['FIRST_NAME'] ?? $child['first_name'] ?? '') . ' ' . ($child['LAST_NAME'] ?? $child['last_name'] ?? '')); ?></h3>
                            </div>
                            
                            <div class="child-info">
                                <div class="info-item">
                                    <span class="info-label">Birthdate:</span>
                                    <span class="info-value">
                                        <?php 
                                        $birthdate = $child['BIRTHDATE'] ?? $child['birthdate'] ?? '';
                                        if (!empty($birthdate)) {
                                            try {
                                                $date = new DateTime($birthdate);
                                                echo $date->format('d.m.Y');
                                            } catch (Exception $e) {
                                                echo htmlspecialchars($birthdate);
                                            }
                                        } else {
                                            echo 'Unspecified';
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Age:</span>
                                    <span class="info-value">
                                        <?php echo calculateAge($child['BIRTHDATE'] ?? $child['birthdate'] ?? ''); ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($child['GENDER'] ?? $child['gender'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Gender:</span>
                                    <span class="info-value">
                                        <?php echo htmlspecialchars($child['GENDER'] ?? $child['gender'] ?? ''); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>