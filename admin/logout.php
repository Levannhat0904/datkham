<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['admin_id'] = null;
session_destroy();
header('Location: login.php');
exit;
