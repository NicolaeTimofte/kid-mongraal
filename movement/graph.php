<?php
class SimpleFortniteGraph {
    private $locations = [];
    private $connections = [];
    
    public function __construct() {
        $this->initializeLocations();
        $this->buildConnections();
    }
    
    private function initializeLocations() {
        $this->locations = [
            'JUNK_JUNCTION' => ['x' => 171, 'y' => 127, 'name' => 'Junk Junction'],
            'HAUNTED_HILLS' => ['x' => 145, 'y' => 228, 'name' => 'Haunted Hills'],
            'PLEASANT_PARK' => ['x' => 261, 'y' => 324, 'name' => 'Pleasant Park'],
            'SNOBBY_SHORES' => ['x' => 90, 'y' => 428, 'name' => 'Snobby Shores'],
            'GREASY_GROVE' => ['x' => 247, 'y' => 659, 'name' => 'Greasy Grove'],
            'SHIFTY_SHAFTS' => ['x' => 392, 'y' => 664, 'name' => 'Shifty Shafts'],
            'FROSTY_FLIGHTS' => ['x' => 110, 'y' => 753, 'name' => 'Frosty Flights'],
            'FLUSH_FACTORY' => ['x' => 370, 'y' => 895, 'name' => 'Flush Factory'],
            'LUCKY_LANDING' => ['x' => 586, 'y' => 965, 'name' => 'Lucky Landing'],
            'FATAL_FIELDS' => ['x' => 625, 'y' => 790, 'name' => 'Fatal Fields'],
            'SALTY_SPRINGS' => ['x' => 608, 'y' => 631, 'name' => 'Salty Springs'],
            'DUSTY_DIVOT' => ['x' => 603, 'y' => 503, 'name' => 'Dusty Divot'],
            'LOOT_LAKE' => ['x' => 448, 'y' => 318, 'name' => 'Loot Lake'],
            'TILTED_TOWERS' => ['x' => 405, 'y' => 494, 'name' => 'Tilted Towers'],
            'LAZY_LINKS' => ['x' => 556, 'y' => 205, 'name' => 'Lazy Links'],
            'RISKY_REELS' => ['x' => 774, 'y' => 218, 'name' => 'Risky Reels'],
            'WAILING_WOODS' => ['x' => 863, 'y' => 302, 'name' => 'Wailing Woods'],
            'TOMATO_TEMPLE' => ['x' => 684, 'y' => 326, 'name' => 'Tomato Temple'],
            'LONELY_LODGE' => ['x' => 919, 'y' => 413, 'name' => 'Lonely Lodge'],
            'RETAIL_ROW' => ['x' => 762, 'y' => 545, 'name' => 'Retail Row'],
            'PARADISE_PALMS' => ['x' => 864, 'y' => 762, 'name' => 'Paradise Palms']
        ];
    }
    
    private function buildConnections() {
        $this->connections = [
            'JUNK_JUNCTION' => ['HAUNTED_HILLS', 'LAZY_LINKS'],
            'HAUNTED_HILLS' => ['JUNK_JUNCTION', 'PLEASANT_PARK', 'SNOBBY_SHORES'],
            'PLEASANT_PARK' => ['HAUNTED_HILLS', 'LOOT_LAKE', 'TILTED_TOWERS'],
            'SNOBBY_SHORES' => ['HAUNTED_HILLS', 'GREASY_GROVE', 'TILTED_TOWERS'],
            'GREASY_GROVE' => ['SNOBBY_SHORES', 'TILTED_TOWERS', 'SHIFTY_SHAFTS'],
            'SHIFTY_SHAFTS' => ['GREASY_GROVE', 'TILTED_TOWERS', 'FLUSH_FACTORY', 'SALTY_SPRINGS'],
            'FROSTY_FLIGHTS' => ['FLUSH_FACTORY', 'LUCKY_LANDING'],
            'FLUSH_FACTORY' => ['SHIFTY_SHAFTS', 'FROSTY_FLIGHTS', 'LUCKY_LANDING', 'FATAL_FIELDS'],
            'LUCKY_LANDING' => ['FROSTY_FLIGHTS', 'FLUSH_FACTORY', 'FATAL_FIELDS'],
            'FATAL_FIELDS' => ['FLUSH_FACTORY', 'LUCKY_LANDING', 'SALTY_SPRINGS', 'PARADISE_PALMS'],
            'SALTY_SPRINGS' => ['SHIFTY_SHAFTS', 'FATAL_FIELDS', 'DUSTY_DIVOT', 'RETAIL_ROW'],
            'DUSTY_DIVOT' => ['SALTY_SPRINGS', 'LOOT_LAKE', 'TILTED_TOWERS', 'RETAIL_ROW'],
            'LOOT_LAKE' => ['PLEASANT_PARK', 'DUSTY_DIVOT', 'TILTED_TOWERS', 'LAZY_LINKS'],
            'TILTED_TOWERS' => ['PLEASANT_PARK', 'SNOBBY_SHORES', 'GREASY_GROVE', 'SHIFTY_SHAFTS', 'LOOT_LAKE', 'DUSTY_DIVOT'],
            'LAZY_LINKS' => ['JUNK_JUNCTION', 'LOOT_LAKE', 'RISKY_REELS', 'TOMATO_TEMPLE'],
            'RISKY_REELS' => ['LAZY_LINKS', 'WAILING_WOODS', 'TOMATO_TEMPLE'],
            'WAILING_WOODS' => ['RISKY_REELS', 'TOMATO_TEMPLE', 'LONELY_LODGE'],
            'TOMATO_TEMPLE' => ['LAZY_LINKS', 'RISKY_REELS', 'WAILING_WOODS', 'LONELY_LODGE', 'RETAIL_ROW'],
            'LONELY_LODGE' => ['WAILING_WOODS', 'TOMATO_TEMPLE', 'RETAIL_ROW', 'PARADISE_PALMS'],
            'RETAIL_ROW' => ['DUSTY_DIVOT', 'SALTY_SPRINGS', 'TOMATO_TEMPLE', 'LONELY_LODGE', 'PARADISE_PALMS'],
            'PARADISE_PALMS' => ['FATAL_FIELDS', 'LONELY_LODGE', 'RETAIL_ROW']
        ];
    }
    
    public function getRandomConnectedLocation($currentLocation) {
        if (!isset($this->connections[$currentLocation])) {
            return null;
        }
        
        $connected = $this->connections[$currentLocation];
        return $connected[array_rand($connected)];
    }
    
    public function getLocationCoordinates($locationId) {
        return isset($this->locations[$locationId]) ? $this->locations[$locationId] : null;
    }
    
    public function findLocationByCoordinates($x, $y, $tolerance = 50) {
        foreach ($this->locations as $id => $location) {
            if (abs($location['x'] - $x) < $tolerance && abs($location['y'] - $y) < $tolerance) {
                return $id;//returning the name of the location (ex: RETAIL_ROW)
            }
        }
        return null;
    }
    
    public function getAllLocations() {
        return $this->locations;
    }
}
?>