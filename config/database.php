<?php
/**
 * MySQL connection (mysqli). Edit host, user, password, dbname for your environment.
 */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'datkham';

$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die('Kết nối database thất bại: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
