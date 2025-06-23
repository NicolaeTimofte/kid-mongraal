<?php
require_once '../includes/session.php';
require_once '../includes/config.php';
requireLogin();

try {
    $sql = "
    SELECT 
        c.id as child_id,
        c.first_name,
        c.last_name,
        c.birthdate,
        c.gender,
        d.latitude as recent_latitude,
        d.longitude as recent_longitude,
        d.location as recent_location,
        d.timestamp as last_update,
        c.user_id as parent_id,
        u.username as parent_username
    FROM children c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN data d ON c.id = d.child_id
    WHERE c.user_id = :user_id
    AND (d.timestamp IS NULL OR d.timestamp = (
        SELECT MAX(d2.timestamp) 
        FROM data d2 
        WHERE d2.child_id = c.id
    ))
    ORDER BY c.id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $children_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $export_data = [];
    
    foreach ($children_data as $child) {
        $birthdate = null;
        if (!empty($child['BIRTHDATE'] ?? $child['birthdate'])) {
            try {
                $date = new DateTime($child['BIRTHDATE'] ?? $child['birthdate']);
                $birthdate = $date->format('Y-m-d');
            } catch (Exception $e) {
                $birthdate = $child['BIRTHDATE'] ?? $child['birthdate'] ?? null;
            }
        }
        
        $last_update = null;
        if (!empty($child['LAST_UPDATE'] ?? $child['last_update'])) {
            try {
                $date = new DateTime($child['LAST_UPDATE'] ?? $child['last_update']);
                $last_update = $date->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                $last_update = $child['LAST_UPDATE'] ?? $child['last_update'] ?? null;
            }
        }
        
        $export_data[] = [
            'child_id' => $child['CHILD_ID'] ?? $child['child_id'] ?? null,
            'first_name' => $child['FIRST_NAME'] ?? $child['first_name'] ?? null,
            'last_name' => $child['LAST_NAME'] ?? $child['last_name'] ?? null,
            'birthdate' => $birthdate,
            'gender' => $child['GENDER'] ?? $child['gender'] ?? null,
            'most_recent_location' => [
                'latitude' => $child['RECENT_LATITUDE'] ?? $child['recent_latitude'] ?? null,
                'longitude' => $child['RECENT_LONGITUDE'] ?? $child['recent_longitude'] ?? null,
                'location_name' => $child['RECENT_LOCATION'] ?? $child['recent_location'] ?? null,
                'last_update' => $last_update
            ]
        ];
    }
    
    $final_export = [
        'export_info' => [
            'generated_at' => date('Y-m-d H:i:s'),
            'total_children' => count($export_data),
            'exported_by' => $_SESSION['username']
        ],
        'children_data' => $export_data
    ];
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="children_data_export_' . date('Y-m-d_H-i-s') . '.json"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo json_encode($final_export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
    
} catch (PDOException $e) {
    header('Location: index.php?export_error=1');
    exit;
} catch (Exception $e) {
    header('Location: index.php?export_error=1');
    exit;
}
?>