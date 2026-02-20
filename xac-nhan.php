<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$appointment = null;
if ($id > 0) {
    $res = mysqli_query($conn, "SELECT a.*, s.name AS service_name FROM appointments a JOIN services s ON s.id = a.service_id WHERE a.id = " . $id . " LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $appointment = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $appointment ? 'Đặt khám thành công' : 'Không tìm thấy'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .confirm-card { max-width: 420px; margin: 0 auto; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08); overflow: hidden; }
        .confirm-card .card-body { padding: 2rem; }
        .confirm-icon { width: 56px; height: 56px; margin: 0 auto 1rem; background: rgba(25, 135, 84, .12); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .confirm-icon svg { width: 28px; height: 28px; color: #198754; }
        .confirm-title { font-size: 1.25rem; font-weight: 600; color: #198754; margin-bottom: 1.25rem; }
        .confirm-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid rgba(0,0,0,.06); font-size: .9375rem; }
        .confirm-row:last-child { border-bottom: none; }
        .confirm-row .label { color: #6c757d; }
        .confirm-row .value { font-weight: 500; color: #212529; }
        .confirm-actions { display: flex; gap: .75rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
        .confirm-actions .btn { min-width: 140px; border-radius: 8px; }
        .notfound-card { max-width: 380px; margin: 0 auto; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Đặt khám</a>
            <div class="navbar-nav ms-auto flex-row gap-2">
                <a class="nav-link" href="don-cua-toi.php">Đơn của tôi</a>
                <a class="nav-link" href="dat-kham.php">Đặt lịch</a>
            </div>
        </div>
    </nav>
    <div class="container py-5">
        <?php if ($appointment): ?>
            <div class="card confirm-card bg-white">
                <div class="card-body text-center">
                    <div class="confirm-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                    </div>
                    <h1 class="confirm-title">Đặt khám thành công</h1>
                    <div class="text-start">
                        <div class="confirm-row">
                            <span class="label">Dịch vụ</span>
                            <span class="value"><?php echo e($appointment['service_name']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="label">Ngày</span>
                            <span class="value"><?php echo e($appointment['appointment_date']); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="label">Giờ</span>
                            <span class="value"><?php echo e(date('H:i', strtotime($appointment['appointment_time']))); ?></span>
                        </div>
                        <div class="confirm-row">
                            <span class="label">Trạng thái</span>
                            <span class="value text-success">Chờ xác nhận</span>
                        </div>
                    </div>
                    <div class="confirm-actions">
                        <a href="don-cua-toi.php" class="btn btn-primary">Xem đơn của tôi</a>
                        <a href="dat-kham.php" class="btn btn-outline-secondary">Đặt thêm</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card notfound-card">
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-3">Không tìm thấy thông tin đơn đặt khám.</p>
                    <a href="index.php" class="btn btn-primary rounded-3">Về trang chủ</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
