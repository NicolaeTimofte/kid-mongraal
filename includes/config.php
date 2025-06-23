<?php

$host = 'localhost';
$port = '1521'; 
$service_name = 'XE'; 
$username = 'STUDENT';
$password = 'STUDENT';

try {
    $dsn = "oci:dbname={$host}:{$port}/{$service_name}";
    
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
    
    $pdo->exec("ALTER SESSION SET NLS_LANGUAGE='ROMANIAN'");
    $pdo->exec("ALTER SESSION SET NLS_TERRITORY='ROMANIA'");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "<br>DSN used: " . $dsn);
}
?>