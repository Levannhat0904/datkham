<?php
session_start();
$loggedIn = !empty($_SESSION['patient_id']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt khám - Trang chủ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Đặt khám</a>
            <div class="navbar-nav ms-auto">
                <?php if ($loggedIn): ?>
                    <a class="nav-link" href="don-cua-toi.php">Đơn của tôi</a>
                    <a class="nav-link" href="dat-kham.php">Đặt lịch</a>
                    <a class="nav-link" href="dang-xuat.php">Đăng xuất</a>
                <?php else: ?>
                    <a class="nav-link" href="dang-nhap.php">Đăng nhập</a>
                    <a class="nav-link" href="dang-ky.php">Đăng ký</a>
                    <a class="nav-link" href="dat-kham.php">Đặt lịch</a>
                <?php endif; ?>
                <a class="nav-link" href="admin/login.php">Admin</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5">Chào mừng đến với hệ thống đặt khám</h1>
            <p class="lead text-muted">Đăng ký hoặc đăng nhập để đặt lịch khám và xem trạng thái đơn của bạn.</p>
        </div>
        <div class="row justify-content-center g-3">
            <?php if (!$loggedIn): ?>
                <div class="col-auto">
                    <a href="dang-ky.php" class="btn btn-primary btn-lg">Đăng ký</a>
                </div>
                <div class="col-auto">
                    <a href="dang-nhap.php" class="btn btn-outline-primary btn-lg">Đăng nhập</a>
                </div>
            <?php endif; ?>
            <div class="col-auto">
                <a href="dat-kham.php" class="btn btn-success btn-lg">Đặt khám</a>
            </div>
            <?php if ($loggedIn): ?>
                <div class="col-auto">
                    <a href="don-cua-toi.php" class="btn btn-outline-secondary btn-lg">Đơn của tôi</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
