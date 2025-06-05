<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$message_type = '';
$user_data = [];

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
            $address = trim($_POST['address'] ?? '');
            $latitude = trim($_POST['latitude'] ?? '');
            $longitude = trim($_POST['longitude'] ?? '');
            
            $latitude = !empty($latitude) ? floatval($latitude) : null;
            $longitude = !empty($longitude) ? floatval($longitude) : null;
            
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
                    <label for="address">Adresă:</label>
                    <textarea id="address" name="address" placeholder="Introduceți adresa"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="latitude">Latitudine:</label>
                    <input type="text" id="latitude" name="latitude" 
                           value="<?php echo htmlspecialchars($user_data['latitude'] ?? ''); ?>" 
                           placeholder="">
                </div>
                
                <div class="form-group">
                    <label for="longitude">Longitudine:</label>
                    <input type="text" id="longitude" name="longitude" 
                           value="<?php echo htmlspecialchars($user_data['longitude'] ?? ''); ?>" 
                           placeholder="">
                </div>
                
                <button type="submit" class="btn">Actualizează Profilul</button>
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