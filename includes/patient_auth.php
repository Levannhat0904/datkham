<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['patient_id'])) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? 'dat-kham.php');
    header('Location: dang-nhap.php?redirect=' . $redirect);
    exit;
}
