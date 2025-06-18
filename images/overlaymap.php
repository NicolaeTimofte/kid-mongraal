<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

$response = [
    'children' => [],
    'accidents' => [],
    'user' => null
];

try {
    $stmt = $pdo->prepare("SELECT id FROM children WHERE user_id = :user_id ORDER BY id DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($children) {
        $childIds = array_column($children, 'id');
        $placeholders = implode(',', array_fill(0, count($childIds), '?'));

        $sql = "
            SELECT d.child_id, d.latitude, d.longitude
            FROM (
                SELECT child_id, latitude, longitude, timestamp,
                ROW_NUMBER() OVER (PARTITION BY child_id ORDER BY timestamp DESC) AS rn
                FROM data
                WHERE child_id IN ($placeholders)
            ) d
            WHERE d.rn = 1
        ";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute($childIds);
        $locations = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        foreach ($locations as $row) {
            if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
                $response['children'][] = [
                    'x' => (int)$row['latitude'],
                    'y' => (int)$row['longitude']
                ];
            }
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching children: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM accidents");
    $stmt->execute();
    $accidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($accidents as $row) {
        if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
            $response['accidents'][] = [
                'x' => (int)$row['latitude'],
                'y' => (int)$row['longitude']
            ];
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching accidents: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("SELECT latitude, longitude FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (is_numeric($user['latitude']) && is_numeric($user['longitude'])) {
        $response['user'] = [
            'x' => (int)$user['latitude'],
            'y' => (int)$user['longitude']
        ];
    }
} catch (PDOException $e) {
    error_log("Error fetching user location: " . $e->getMessage());
}

echo json_encode($response);
