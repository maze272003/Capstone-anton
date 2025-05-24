<?php
// dbconnection.php

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'imssb';

// Declare $db as global
global $db;
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
?>
