<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name 
        FROM children 
        WHERE user_id = :user_id
        ORDER BY id
    ");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = ['children' => []];
    
    foreach ($children as $child) {
        $stmt = $pdo->prepare("SELECT dist_parinte(:child_id) AS parent FROM dual");
        $stmt->execute(['child_id' => $child['id']]);
        $distance_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $result['children'][] = [
            'id' => $child['id'],
            'first_name' => $child['first_name'],
            'last_name' => $child['last_name'],
            'distance_parent' => $distance_info['parent']  
        ];
    }
    
    echo json_encode($result);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}