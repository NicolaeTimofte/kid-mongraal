<?php
require_once 'includes/session.php';
redirectIfLoggedIn();

$message = '';
$message_type = '';
$show_register = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            require_once 'includes/config.php';
            
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $message = 'Toate câmpurile sunt obligatorii';
                $message_type = 'error';
            } else {
                try {
                    if (!isset($pdo)) {
                        throw new Exception('Database connection not available');
                    }

                    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE UPPER(username) = UPPER(:username) OR UPPER(email) = UPPER(:email)");
                    
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $username, PDO::PARAM_STR);
                    
                    $stmt->execute();
                    
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    $user_id = $user['ID'] ?? $user['id'] ?? null;
                    $username_db = $user['USERNAME'] ?? $user['username'] ?? '';
                    $password_hash = $user['PASSWORD'] ?? $user['password'] ?? '';

                    if ($user && !empty($password_hash) && password_verify($password, $password_hash)) {
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username_db;
                        
                        header('Location: index.php');
                        exit();
                    } else {
                        $message = 'Username/email sau parola incorecte';
                        $message_type = 'error';
                    }
                    
                } catch(PDOException $e) {
                    error_log("Oracle login error: " . $e->getMessage());
                    $message = 'Eroare de conectare la baza de date';
                    $message_type = 'error';
                } catch(Exception $e) {
                    error_log("Login error: " . $e->getMessage());
                    $message = 'Eroare de server';
                    $message_type = 'error';
                }
            }
        } elseif ($_POST['action'] === 'register') {
            require_once 'includes/config.php';
            
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            $show_register = true; 
            
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                $message = 'Toate câmpurile sunt obligatorii';
                $message_type = 'error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email invalid';
                $message_type = 'error';
            } elseif (strlen($password) < 6) {
                $message = 'Parola trebuie să aibă cel puțin 6 caractere';
                $message_type = 'error';
            } elseif ($password !== $confirm_password) {
                $message = 'Parolele nu se potrivesc';
                $message_type = 'error';
            } else {
                try {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE UPPER(username) = UPPER(:username) OR UPPER(email) = UPPER(:email)");
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    
                    if ($stmt->fetch()) {
                        $message = 'Username-ul sau email-ul există deja';
                        $message_type = 'error';
                    } else {
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->execute();

                        $message = 'Cont creat cu succes! Poți să te loghezi acum.';
                        $message_type = 'success';
                        $show_register = false;
                    }
                } catch(PDOException $e) {
                    error_log("Oracle register error: " . $e->getMessage());
                    $message = 'Eroare la crearea contului';
                    $message_type = 'error';
                }
            }
        }
    }
}

if (isset($_GET['show']) && $_GET['show'] === 'register') {
    $show_register = true;
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form<?php echo $show_register ? ' hidden' : ''; ?>" id="loginForm">
            <h2>Login</h2>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="text" name="username" placeholder="Username sau Email" required 
                       value="<?php echo isset($_POST['username']) && $_POST['action'] === 'login' ? htmlspecialchars($_POST['username']) : ''; ?>">
                <input type="password" name="password" placeholder="Parola" required>
                <button type="submit">Login</button>
                <p class="switch-form">Nu ai cont? <a href="?show=register">Înregistrează-te</a></p>
            </form>
        </div>

        <div class="auth-form<?php echo !$show_register ? ' hidden' : ''; ?>" id="registerForm">
            <h2>Înregistrare</h2>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <input type="text" name="username" placeholder="Username" required 
                       value="<?php echo isset($_POST['username']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['username']) : ''; ?>">
                <input type="email" name="email" placeholder="Email" required 
                       value="<?php echo isset($_POST['email']) && $_POST['action'] === 'register' ? htmlspecialchars($_POST['email']) : ''; ?>">
                <input type="password" name="password" placeholder="Parola" required>
                <input type="password" name="confirm_password" placeholder="Confirmă parola" required>
                <button type="submit">Înregistrează-te</button>
                <p class="switch-form">Ai deja cont? <a href="login.php">Login</a></p>
            </form>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>" style="display: block;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>