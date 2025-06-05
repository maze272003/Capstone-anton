<?php
require_once 'loadEnv.php';
loadEnv(__DIR__ . '/.env');
// SMTP Configuration
$smtp_host = getenv('SMTP_HOST');
$smtp_username = getenv('SMTP_USERNAME');
$smtp_password = getenv('SMTP_PASSWORD');
$from_email = getenv('FROM_EMAIL');
$from_name = getenv('FROM_NAME');


?>