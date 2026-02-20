<?php
session_start();
$_SESSION['patient_id'] = null;
session_destroy();
header('Location: index.php');
exit;
