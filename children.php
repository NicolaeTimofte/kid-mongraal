<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$message_type = '';
$children = [];

try {
    $stmt = $pdo->prepare("SELECT * FROM children WHERE user_id = :user_id ORDER BY id DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching children: " . $e->getMessage());
    $message = 'Eroare la încărcarea copiilor';
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_child') {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        
        if (empty($first_name) || empty($last_name) || empty($birthdate)) {
            $message = 'Numele, prenumele și data nașterii sunt obligatorii';
            $message_type = 'error';
        } else {
            try {
                $birthdate_formatted = date('Y-m-d', strtotime($birthdate));
                
                $stmt = $pdo->prepare("INSERT INTO children (user_id, first_name, last_name, birthdate, gender) VALUES (:user_id, :first_name, :last_name, TO_DATE(:birthdate, 'YYYY-MM-DD'), :gender)");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':first_name', $first_name);
                $stmt->bindParam(':last_name', $last_name);
                $stmt->bindParam(':birthdate', $birthdate_formatted);
                $stmt->bindParam(':gender', $gender);
                $stmt->execute();
                
                $message = 'Copilul a fost adăugat cu succes';
                $message_type = 'success';
                
                $stmt = $pdo->prepare("SELECT * FROM children WHERE user_id = :user_id ORDER BY id DESC");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->execute();
                $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error adding child: " . $e->getMessage());
                $message = 'Eroare la adăugarea copilului';
                $message_type = 'error';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'get_max_dist' && isset($_POST['child_id'])) {
        $child_id = intval($_POST['child_id']);
        
        try {
            $stmt = $pdo->prepare("
                DECLARE 
                    v_result VARCHAR2(4000);
                BEGIN 
                    v_result := max_dist_acc(:child_id);
                    :result := v_result;
                END;
            ");
            
            $result = '';
            $stmt->bindParam(':child_id', $child_id, PDO::PARAM_INT);
            $stmt->bindParam(':result', $result, PDO::PARAM_STR, 4000);
            $stmt->execute();
            
            if (!empty($result) && $result !== 'NULL' && trim($result) !== '') {
                $_SESSION['max_dist_result_' . $child_id] = $result;
                $message = 'Distanța maximă a fost calculată cu succes';
                $message_type = 'success';
            } else {
                $stmt2 = $pdo->prepare("SELECT max_dist_acc(:child_id) AS max_distance FROM dual");
                $stmt2->bindParam(':child_id', $child_id, PDO::PARAM_INT);
                $stmt2->execute();
                $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($result2 && isset($result2['MAX_DISTANCE']) && !empty($result2['MAX_DISTANCE'])) {
                    $_SESSION['max_dist_result_' . $child_id] = $result2['MAX_DISTANCE'];
                    $message = 'Distanța maximă a fost calculată cu succes';
                    $message_type = 'success';
                } else {
                    $message = 'Nu s-au găsit date pentru acest copil';
                    $message_type = 'error';
                }
            }
            
            $stmt = $pdo->prepare("SELECT * FROM children WHERE user_id = :user_id ORDER BY id DESC");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error calling max_dist_acc: " . $e->getMessage());
            $message = 'Eroare la calcularea distanței';
            $message_type = 'error';
        }
    }
}

function getMaxDistanceResult($child_id)
{
    if (isset($_SESSION['max_dist_result_' . $child_id])) {
        $result = $_SESSION['max_dist_result_' . $child_id];
        unset($_SESSION['max_dist_result_' . $child_id]);
        return $result;
    }
    return null;
}

function calculateAge($birthdate)
{
    if (empty($birthdate)) return 'N/A';
    
    try {
        $birth = new DateTime($birthdate);
        $today = new DateTime();
        $age = $today->diff($birth);
        
        if ($age->y > 0) {
            return $age->y . ' ani';
        } elseif ($age->m > 0) {
            return $age->m . ' luni';
        } else {
            return $age->d . ' zile';
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
    <title>Copii - KiD</title>
    <link rel="stylesheet" href="css/mainchildren.css">
</head>
<body>
    <header>
        <nav>
            <h1>Copii - <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <div>
                <a href="index.php" class="btn">Acasă</a>
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
                <h2>Adaugă Copil Nou</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add_child">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">Prenume:</label>
                            <input type="text" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Nume:</label>
                            <input type="text" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthdate">Data Nașterii:</label>
                            <input type="date" id="birthdate" name="birthdate" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="gender">Gen:</label>
                            <select id="gender" name="gender">
                                <option value="">Selectează</option>
                                <option value="M">Masculin</option>
                                <option value="F">Feminin</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Adaugă Copil</button>
                </form>
            </div>

            <div class="total-count">
                <h2>Total Copii: <?php echo count($children); ?></h2>
            </div>

            <?php if (empty($children)): ?>
                <div class="no-children">
                    <h3>Nu aveți copii înregistrați</h3>
                    <p>Utilizați formularul de mai sus pentru a adăuga primul copil.</p>
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
                                    <span class="info-label">Data Nașterii:</span>
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
                                            echo 'Nespecificată';
                                        }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Vârsta:</span>
                                    <span class="info-value">
                                        <?php echo calculateAge($child['BIRTHDATE'] ?? $child['birthdate'] ?? ''); ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($child['GENDER'] ?? $child['gender'])): ?>
                                <div class="info-item">
                                    <span class="info-label">Gen:</span>
                                    <span class="info-value">
                                        <?php echo htmlspecialchars($child['GENDER'] ?? $child['gender'] ?? ''); ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <span class="info-label">Distanță Max:</span>
                                    <span class="info-value">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="get_max_dist">
                                            <input type="hidden" name="child_id" value="<?php echo $child['ID'] ?? $child['id']; ?>">
                                            <button type="submit" class="btn-small">Calculează</button>
                                        </form>
                                        <?php 
                                        $max_dist_result = getMaxDistanceResult($child['ID'] ?? $child['id']);
                                        if ($max_dist_result !== null): 
                                        ?>
                                            <div class="distance-result">
                                                <div class="distance-simple"><?php echo htmlspecialchars($max_dist_result); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>