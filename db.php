<?php
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'skymay_db';

$conn = @new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
$dbConnected = !$conn->connect_error;

if ($dbConnected) {
  $conn->set_charset('utf8mb4');
}
?>