<?php
require_once '../includes/config.php';
require_once 'graph.php';

class SimpleMovementSimulator {
    private $pdo;
    private $graph;
    private $baseStepsPerMove = 15; 
    
    public function __construct($pdo) {
        $this->pdo = $pdo;//handke database connection
        $this->graph = new SimpleFortniteGraph();
    }
    
    public function moveAllChildren() {
        try {
            $stmt = $this->pdo->query("
                SELECT c.id, c.first_name, c.last_name, d.latitude, d.longitude, d.location
                FROM children c
                LEFT JOIN (
                    SELECT child_id, latitude, longitude, location,
                           ROW_NUMBER() OVER (PARTITION BY child_id ORDER BY timestamp DESC) as rn
                    FROM data
                ) d ON c.id = d.child_id AND d.rn = 1
            ");
            
            $children = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            //processing every child
            foreach ($children as $child) {
                $this->processChild($child);
            }
            
        } catch (Exception $e) {
            error_log("Eroare în moveAllChildren: " . $e->getMessage());
            echo "EROARE: " . $e->getMessage();
        }
    }
    
    private function processChild($child) {
        //placing child randomly if no known coords
        if (!$child['latitude'] || !$child['longitude']) {
            $this->placeChildRandomly($child['id']);
            return;
        }
        
        $isMoving = $this->isChildMoving($child['id']);
        
        error_log("Copil {$child['id']}: lat={$child['latitude']}, lng={$child['longitude']}, location={$child['location']}, isMoving=" . ($isMoving ? 'DA' : 'NU'));
        
        if ($isMoving) {
            $this->continueMovement($child['id']);
        } else {
            error_log("Copil {$child['id']}: Începe mișcare nouă");
            $this->startNewMovement($child['id'], $child['latitude'], $child['longitude']);
        }
    }

    //verify if a child is in transit (location should be ex: JunkJunction->HauntedHills)
    private function isChildMoving($childId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as cnt
            FROM data 
            WHERE child_id = :child_id 
            AND location LIKE '%->%'
            AND timestamp > (SYSDATE - 30/86400)
        ");//looks back 30/86400 days (30sec)
        $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['cnt'] > 0;
    }
    
    //checking if the child was waiting somewhere in the past 2 minutes
    private function isChildWaiting($childId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as cnt
            FROM data 
            WHERE child_id = :child_id 
            AND location LIKE 'WAIT_%'
            AND timestamp > (SYSDATE - 2/1440)
        ");
        $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['cnt'] > 0;
    }

    private function continueMovement($childId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM (
                SELECT latitude, longitude, location 
                FROM data 
                WHERE child_id = :child_id 
                ORDER BY timestamp DESC
            ) WHERE ROWNUM = 1
        ");
        $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
        $stmt->execute();
        $lastStep = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //if there is no row or if the location field does not contain -> (not in transit)
        if (!$lastStep || strpos($lastStep['location'], '->') === false) {
            return; 
        }
        
        //for ex: GREASY_GROVE->SHIFTY_SHAFTS->3->12
        $parts = explode('->', $lastStep['location']);
        if (count($parts) < 4) return;
        
        $fromLocation = $parts[0];
        $toLocation = $parts[1];
        $currentStep = (int)$parts[2];
        $totalSteps = (int)$parts[3];
        
        if ($currentStep >= $totalSteps) {
            $this->finishMovement($childId, $toLocation);
            return;
        }
        
        $fromCoords = $this->graph->getLocationCoordinates($fromLocation);
        $toCoords = $this->graph->getLocationCoordinates($toLocation);
        
        if (!$fromCoords || !$toCoords) return;
        
        $nextStep = $currentStep + 1;
        $progress = $nextStep / $totalSteps;
        
        //setting the next point to put on map based on progress made
        $x = $fromCoords['x'] + ($toCoords['x'] - $fromCoords['x']) * $progress;
        $y = $fromCoords['y'] + ($toCoords['y'] - $fromCoords['y']) * $progress;
        
        //simulating a slight error in movement (so it does not go straight)
        $x += rand(-3, 3);
        $y += rand(-3, 3);
        
        //if the child is at least 80% progress done and is in 20px range to destination (mark it as already at destination)
        if ($progress >= 0.8) {
            $distance = sqrt(pow($x - $toCoords['x'], 2) + pow($y - $toCoords['y'], 2));
            if ($distance < 20) { 
                $this->finishMovement($childId, $toLocation);
                return;
            }
        }
        
        $stepLocation = $fromLocation . '->' . $toLocation . '->' . $nextStep . '->' . $totalSteps;
        
        $this->addMovementStep($childId, round($x), round($y), $stepLocation);
    }

    private function startNewMovement($childId, $currentX, $currentY) {
        $currentLocation = $this->graph->findLocationByCoordinates($currentX, $currentY);
        
        //if location is not null
        if (!$currentLocation) {
            $this->placeChildRandomly($childId);
            return;
        }
        

        $nextLocation = $this->graph->getRandomConnectedLocation($currentLocation);
        
        if (!$nextLocation) {
            return; 
        }
        
        $fromCoords = $this->graph->getLocationCoordinates($currentLocation);
        $toCoords = $this->graph->getLocationCoordinates($nextLocation);
        
        if (!$fromCoords || !$toCoords) return;
        
        $stepsNeeded = $this->calculateStepsNeeded($fromCoords, $toCoords);
        
        //same as for continueMovement but now its the first step (elden ring)
        $stepLocation = $currentLocation . '->' . $nextLocation . '->1->' . $stepsNeeded;
        
        $progress = 1 / $stepsNeeded;
        $x = $fromCoords['x'] + ($toCoords['x'] - $fromCoords['x']) * $progress;
        $y = $fromCoords['y'] + ($toCoords['y'] - $fromCoords['y']) * $progress;
        
        $x += rand(-3, 3);
        $y += rand(-3, 3);
        
        $this->addMovementStep($childId, round($x), round($y), $stepLocation);
    }
    
    private function continueWaiting($childId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM (
                SELECT latitude, longitude, location 
                FROM data 
                WHERE child_id = :child_id 
                AND location LIKE 'WAIT_%'
                ORDER BY timestamp DESC
            ) WHERE ROWNUM = 1
        ");
        $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
        $stmt->execute();
        $lastWait = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lastWait) return;
        
        $parts = explode('_', $lastWait['location']);
        if (count($parts) < 4) return; 
        
        $locationName = $parts[1];
        $currentStep = (int)$parts[2];
        $totalSteps = (int)$parts[3];
        
        if ($currentStep >= $totalSteps) {
            $this->startNewMovement($childId, $lastWait['latitude'], $lastWait['longitude']);
            return;
        }
        
        $nextStep = $currentStep + 1;
        $waitLocation = 'WAIT_' . $locationName . '_' . $nextStep . '_' . $totalSteps;
        
        $this->addMovementStep($childId, $lastWait['latitude'], $lastWait['longitude'], $waitLocation);
    }
    
    private function startWaiting($childId, $currentX, $currentY) {
        $currentLocation = $this->graph->findLocationByCoordinates($currentX, $currentY);
        
        if (!$currentLocation) {
            $this->placeChildRandomly($childId);
            return;
        }
        
        $waitSteps = 2; 
        
        $coords = $this->graph->getLocationCoordinates($currentLocation);
        $waitLocation = 'WAIT_' . $currentLocation . '_1_' . $waitSteps;
        
        $this->addMovementStep($childId, $coords['x'], $coords['y'], $waitLocation);
    }
    
    private function hasBeenStationaryTooLong($childId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM (
                SELECT location, timestamp
                FROM data 
                WHERE child_id = :child_id 
                AND location NOT LIKE '%->%'
                ORDER BY timestamp DESC
            ) WHERE ROWNUM <= 2
        ");
        $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
        $stmt->execute();
        $recentPositions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($recentPositions) < 2) {
            return false;
        }
        
        $firstLocation = $recentPositions[0]['location'];
        foreach ($recentPositions as $position) {
            if ($position['location'] !== $firstLocation) {
                return false;
            }
        }
        
        return true; 
    }
    
    private function forceNewMovement($childId, $currentX, $currentY) {
        $currentLocation = $this->graph->findLocationByCoordinates($currentX, $currentY);
        
        if (!$currentLocation) {
            $this->placeChildRandomly($childId);
            return;
        }
        
        $nextLocation = $this->graph->getRandomConnectedLocation($currentLocation);
        
        if (!$nextLocation) {
            return; 
        }
        
        $fromCoords = $this->graph->getLocationCoordinates($currentLocation);
        $toCoords = $this->graph->getLocationCoordinates($nextLocation);
        
        if (!$fromCoords || !$toCoords) return;
        
        $stepsNeeded = $this->calculateStepsNeeded($fromCoords, $toCoords);
        
        $stepLocation = $currentLocation . '->' . $nextLocation . '->1->' . $stepsNeeded;
        
        $progress = 1 / $stepsNeeded;
        $x = $fromCoords['x'] + ($toCoords['x'] - $fromCoords['x']) * $progress;
        $y = $fromCoords['y'] + ($toCoords['y'] - $fromCoords['y']) * $progress;
        
        $x += rand(-3, 3);
        $y += rand(-3, 3);
        
        $this->addMovementStep($childId, round($x), round($y), $stepLocation);
    }
    
    
    private function finishMovement($childId, $destinationLocation) {
        $coords = $this->graph->getLocationCoordinates($destinationLocation);
        if ($coords) {
            $this->addMovementStep($childId, $coords['x'], $coords['y'], $coords['name']);

            $this->addMovementStep($childId, $coords['x'], $coords['y'], $coords['name']);
        }
    }
    
    private function calculateStepsNeeded($fromCoords, $toCoords) {
        $distance = sqrt(pow($toCoords['x'] - $fromCoords['x'], 2) + pow($toCoords['y'] - $fromCoords['y'], 2));
        $stepsNeeded = max(8, ceil($distance / 12)); 
        return min($stepsNeeded, 25);
    }
    
    private function placeChildRandomly($childId) {
        $locations = array_keys($this->graph->getAllLocations());
        $randomLocation = $locations[array_rand($locations)];
        $coords = $this->graph->getLocationCoordinates($randomLocation);
        
        if ($coords) {
            $this->addMovementStep($childId, $coords['x'], $coords['y'], $coords['name']);
        }
    }
    
    private function addMovementStep($childId, $x, $y, $locationName) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO data (child_id, latitude, longitude, location, timestamp) 
                VALUES (:child_id, :latitude, :longitude, :location, SYSDATE)
            ");
            
            $stmt->bindParam(':child_id', $childId, PDO::PARAM_INT);
            $stmt->bindParam(':latitude', $x, PDO::PARAM_INT);
            $stmt->bindParam(':longitude', $y, PDO::PARAM_INT);
            $stmt->bindParam(':location', $locationName, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Eroare addMovementStep: " . $e->getMessage());
            return false;
        }
    }
}

if (basename($_SERVER['PHP_SELF']) == 'movement.php') {
    try {
        $simulator = new SimpleMovementSimulator($pdo);
        $simulator->moveAllChildren();
        echo "OK";
    } catch (Exception $e) {
        echo "EROARE: " . $e->getMessage();
    }
}
?>