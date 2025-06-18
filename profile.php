<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$message_type = '';
$user_data = [];

$map_locations = [
    'JUNK JUNCTION' => ['x' => 189, 'y' => 110],
    'HAUNTED HILLS' => ['x' => 156, 'y' => 169],
    'PLEASANT PARK' => ['x' => 180, 'y' => 301],
    'SNOBBY SHORES' => ['x' => 69, 'y' => 467],
    'GREASY GROVE' => ['x' => 180, 'y' => 635],
    'SHIFTY SHAFTS' => ['x' => 280, 'y' => 695],
    'FROSTY FLIGHTS' => ['x' => 69, 'y' => 775],
    'FLUSH FACTORY' => ['x' => 280, 'y' => 835],
    'LUCKY LANDING' => ['x' => 400, 'y' => 950],
    'FATAL FIELDS' => ['x' => 500, 'y' => 807],
    'SALTY SPRINGS' => ['x' => 570, 'y' => 636],
    'DUSTY DIVOT' => ['x' => 570, 'y' => 486],
    'LOOT LAKE' => ['x' => 400, 'y' => 390],
    'TILTED TOWERS' => ['x' => 280, 'y' => 515],
    'LAZY LINKS' => ['x' => 540, 'y' => 226],
    'RISKY REELS' => ['x' => 760, 'y' => 205],
    'WAILING WOODS' => ['x' => 820, 'y' => 287],
    'TOMATO TEMPLE' => ['x' => 670, 'y' => 340],
    'LONELY LODGE' => ['x' => 870, 'y' => 433],
    'RETAIL ROW' => ['x' => 720, 'y' => 570],
    'PARADISE PALMS' => ['x' => 870, 'y' => 767]
];

try {
    $stmt = $pdo->prepare("SELECT id, username, email, address, latitude, longitude, created_at FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user_data) {
        header('Location: login.php');
        exit();
    }
} catch(PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $message = 'Eroare la încărcarea datelor';
    $message_type = 'error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $selected_location = trim($_POST['location'] ?? '');
            
            $latitude = null;
            $longitude = null;
            $address = '';
            
            if (!empty($selected_location) && isset($map_locations[$selected_location])) {
                $latitude = $map_locations[$selected_location]['x']; 
                $longitude = $map_locations[$selected_location]['y']; 
                $address = $selected_location;
            }
            
            if (empty($username) || empty($email)) {
                $message = 'Username și email sunt obligatorii';
                $message_type = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email invalid';
                $message_type = 'error';
            } else {
                try {

                    $stmt = $pdo->prepare("SELECT id FROM users WHERE (UPPER(username) = UPPER(:username) OR UPPER(email) = UPPER(:email)) AND id != :user_id");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    if ($stmt->fetch()) {
                        $message = 'Username-ul sau email-ul sunt deja folosite';
                        $message_type = 'error';
                    } else {

                        $stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, address = :address, latitude = :latitude, longitude = :longitude WHERE id = :user_id");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':address', $address);
                        $stmt->bindParam(':latitude', $latitude);
                        $stmt->bindParam(':longitude', $longitude);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();

                        $_SESSION['username'] = $username;
                        
                        $message = 'Profilul a fost actualizat cu succes';
                        $message_type = 'success';
                        
                        $user_data['username'] = $username;
                        $user_data['email'] = $email;
                        $user_data['address'] = $address;
                        $user_data['latitude'] = $latitude;
                        $user_data['longitude'] = $longitude;
                    }
                } catch(PDOException $e) {
                    error_log("Error updating profile: " . $e->getMessage());
                    $message = 'Eroare la actualizarea profilului';
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $message = 'Toate câmpurile pentru parola sunt obligatorii';
                $message_type = 'error';
            } elseif (strlen($new_password) < 6) {
                $message = 'Parola nouă trebuie să aibă cel puțin 6 caractere';
                $message_type = 'error';
            } elseif ($new_password !== $confirm_password) {
                $message = 'Parolele noi nu se potrivesc';
                $message_type = 'error';
            } else {
                try {
                    
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    $stored_password = $stmt->fetchColumn();
                    
                    if ($current_password === $stored_password) {
                        
                        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                        $stmt->bindParam(':password', $new_password);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        
                        $message = 'Parola a fost schimbată cu succes';
                        $message_type = 'success';
                    } else {
                        $message = 'Parola curentă este incorectă';
                        $message_type = 'error';
                    }
                } catch(PDOException $e) {
                    error_log("Error changing password: " . $e->getMessage());
                    $message = 'Eroare la schimbarea parolei';
                    $message_type = 'error';
                }
            }
        }
    }
}

$current_location = '';
if (!empty($user_data['address'])) {
    $current_location = $user_data['address'];
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - KiD</title>
    <link rel="stylesheet" href="css/mainprofile.css">
</head>
<body>
    <header>
        <nav>
            <h1>Profil - <?php echo htmlspecialchars($user_data['username'] ?? ''); ?></h1>
            <div>
                <a href="index.php" class="btn">Acasă</a>
            </div>
        </nav>
    </header>

    <main>
       <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="profile-form">
            <h2>Informații Profil</h2>
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="location">Locație pe hartă:</label>
                    <select id="location" name="location" class="location-dropdown">
                        <option value="">Selectează o locație...</option>
                        <?php foreach ($map_locations as $location => $coords): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>" 
                                    <?php echo ($current_location === $location) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($location); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="location-info">
                        Selectează locația ta preferată din harta Fortnite
                    </div>
                </div>
                
                <?php if (!empty($current_location) && isset($map_locations[$current_location])): ?>
                    <div class="coordinates-display">
                        <strong>Locație curentă:</strong> <?php echo htmlspecialchars($current_location); ?><br>
                        <strong>Coordonate:</strong> X=<?php echo $map_locations[$current_location]['x']; ?>, Y=<?php echo $map_locations[$current_location]['y']; ?>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn" style="margin-top: 20px;">Actualizează Profilul</button>
            </form>
        </div>

        <div class="profile-form">
            <h2>Schimbă Parola</h2>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Parola Curentă:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Parola Nouă:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmă Parola Nouă:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">Schimbă Parola</button>
            </form>
        </div>
    </main>
</body>
</html>