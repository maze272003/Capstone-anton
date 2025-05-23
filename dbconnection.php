<?php
$host = 'localhost';
$dbname = 'IMSSB';
$user = 'root';
$password = ''; // change this if needed

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Set error mode to exceptions
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
