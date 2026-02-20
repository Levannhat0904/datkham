<?php
/**
 * Common helper functions.
 */

function e($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function redirect($url, $statusCode = 302) {
    header('Location: ' . $url, true, $statusCode);
    exit;
}

/** Allowed appointment statuses (admin update). */
function get_allowed_statuses() {
    return [
        'pending'   => 'Chờ xác nhận',
        'confirmed' => 'Đã xác nhận',
        'cancelled' => 'Đã hủy',
        'completed' => 'Đã khám',
    ];
}

function is_allowed_status($status) {
    return array_key_exists($status, get_allowed_statuses());
}
