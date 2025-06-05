<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Toate câmpurile sunt obligatorii']);
    exit();
}

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
        
        echo json_encode([
            'success' => true, 
            'message' => 'Login successful',
            'redirect' => 'index.php'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Username/email sau parola incorecte'
        ]);
    }
    
} catch(PDOException $e) {
    error_log("Oracle login error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Eroare de conectare la baza de date'
    ]);
} catch(Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Eroare de server'
    ]);
}
?>