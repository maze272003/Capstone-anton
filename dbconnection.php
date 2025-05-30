<?php
// dbconnection.php

$db_host = 'sql109.infinityfree.com';
$db_user = 'if0_39068751';
$db_pass = 'vRDUPITxpLga2Wk';
$db_name = 'if0_39068751_imssb';

// Declare $db as global
global $db;
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
}
?>

