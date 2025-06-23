<?php
require_once 'includes/session.php';
require_once 'includes/config.php';
requireLogin();

$message = '';
$message_type = '';
$user_data = [];

$map_locations = [
    'JUNK JUNCTION' => ['x' => 209, 'y' => 129],
    'HAUNTED HILLS' => ['x' => 227, 'y' => 200],
    'PLEASANT PARK' => ['x' => 304, 'y' => 278],
    'SNOBBY SHORES' => ['x' => 55, 'y' => 507],
    'GREASY GROVE' => ['x' => 209, 'y' => 668],
    'SHIFTY SHAFTS' => ['x' => 438, 'y' => 647],
    'FROSTY FLIGHTS' => ['x' => 69, 'y' => 775],
    'FLUSH FACTORY' => ['x' => 348, 'y' => 936],
    'LUCKY LANDING' => ['x' => 634, 'y' => 913],
    'FATAL FIELDS' => ['x' => 687, 'y' => 771],
    'SALTY SPRINGS' => ['x' => 571, 'y' => 580],
    'DUSTY DIVOT' => ['x' => 604, 'y' => 438],
    'LOOT LAKE' => ['x' => 450, 'y' => 372],
    'TILTED TOWERS' => ['x' => 387, 'y' => 573],
    'LAZY LINKS' => ['x' => 532, 'y' => 178],
    'RISKY REELS' => ['x' => 788, 'y' => 183],
    'WAILING WOODS' => ['x' => 940, 'y' => 259],
    'TOMATO TEMPLE' => ['x' => 674, 'y' => 372],
    'LONELY LODGE' => ['x' => 995, 'y' => 524],
    'RETAIL ROW' => ['x' => 875, 'y' => 515],
    'PARADISE PALMS' => ['x' => 833, 'y' => 740]
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
    $message = 'Error loading data';
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
                $message = 'Username and email fields are mandatory';
                $message_type = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Invalid email';
                $message_type = 'error';
            } else {
                try {

                    $stmt = $pdo->prepare("SELECT id FROM users WHERE (UPPER(username) = UPPER(:username) OR UPPER(email) = UPPER(:email)) AND id != :user_id");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    
                    if ($stmt->fetch()) {
                        $message = 'Username or email is already in use!';
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
                        
                        $message = 'Profile successfully updated';
                        $message_type = 'success';
                        
                        $user_data['username'] = $username;
                        $user_data['email'] = $email;
                        $user_data['address'] = $address;
                        $user_data['latitude'] = $latitude;
                        $user_data['longitude'] = $longitude;
                    }
                } catch(PDOException $e) {
                    error_log("Error updating profile: " . $e->getMessage());
                    $message = 'Error updating profile';
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] === 'change_password') {
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $message = 'All fields are mandatory!';
                $message_type = 'error';
            } elseif (strlen($new_password) < 6) {
                $message = 'Password must have at least 6 characters';
                $message_type = 'error';
            } elseif ($new_password !== $confirm_password) {
                $message = 'Passwords dont match';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
                    $stmt->bindParam(':user_id', $_SESSION['user_id']);
                    $stmt->execute();
                    $stored_password_hash = $stmt->fetchColumn();
                    
                    if (password_verify($current_password, $stored_password_hash)) {
                        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :user_id");
                        $stmt->bindParam(':password', $new_password_hash);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        
                        $message = 'Password changed successfully';
                        $message_type = 'success';
                    } else {
                        $message = 'Invalid current password';
                        $message_type = 'error';
                    }
                } catch(PDOException $e) {
                    error_log("Error changing password: " . $e->getMessage());
                    $message = 'Error changing password';
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
    <title>KiM - Profile</title>
    <link rel="stylesheet" href="css/mainprofile.css">
</head>
<body>
    <header>
        <nav>
            <h1>Profile - <?php echo htmlspecialchars($user_data['username'] ?? ''); ?></h1>
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

        <div class="profile-form">
            <h2>Profile Information</h2>
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
                    <label for="location">Location on map:</label>
                    <select id="location" name="location" class="location-dropdown">
                        <option value="">Select a location...</option>
                        <?php foreach ($map_locations as $location => $coords): ?>
                            <option value="<?php echo htmlspecialchars($location); ?>" 
                                    <?php echo ($current_location === $location) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($location); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="location-info">
                        Select your current location
                    </div>
                </div>
                
                <?php if (!empty($current_location) && isset($map_locations[$current_location])): ?>
                    <div class="coordinates-display">
                        <strong>Locație curentă:</strong> <?php echo htmlspecialchars($current_location); ?><br>
                        <strong>Coordonate:</strong> X=<?php echo $map_locations[$current_location]['x']; ?>, Y=<?php echo $map_locations[$current_location]['y']; ?>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn" style="margin-top: 20px;">Update Profile</button>
            </form>
        </div>

        <div class="profile-form">
            <h2>Change Password</h2>
            <form method="POST">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Current password:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm new password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">Change Password</button>
            </form>
        </div>
    </main>
</body>
</html>