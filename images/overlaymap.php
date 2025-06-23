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
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);//tells PDO to return each row as an associative array ex: [ 'id' => 42 ], [ 'id' => 57 ]

    if ($children) {
        $childIds = array_column($children, 'id');//select only the values of ids from children
        $placeholders = implode(',', array_fill(0, count($childIds), '?'));//create a string like ?,?,? if there where 3 ids found

        //selecting from an inline view (subquery) which we give the alias d
        //rn=1 to get the most recent position of a child(there are many in the table data for the same child_id)
        $sql = "
            SELECT d.latitude, d.longitude, c.first_name, c.last_name
            FROM (
                SELECT child_id, latitude, longitude, timestamp,
                ROW_NUMBER() OVER (PARTITION BY child_id ORDER BY timestamp DESC) AS rn
                FROM data
                WHERE child_id IN ($placeholders)
            ) d 
            JOIN children c ON d.child_id=c.id
            WHERE d.rn = 1
        ";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute($childIds);//map each element of $childIds to its corresponding ? in placeholders string

        //in locations there will be only the most recent data for each child(child_id)
        $locations = $stmt2->fetchAll(PDO::FETCH_ASSOC);//associative array like ex: (more)['latitude'  => '47.123456','longitude' => '27.654321','first_name'=> 'Alexander','last_name' => 'James']

        //putting in response-children the data obtained above
        foreach ($locations as $row) {
            if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
                $response['children'][] = [
                    'x' => (int)$row['latitude'],
                    'y' => (int)$row['longitude'],
                    'name' => $row['first_name'] . ' ' . $row['last_name']
                ];
            }
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching children: " . $e->getMessage());
}

//pentru accidente
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

//pentru user(parinte)
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