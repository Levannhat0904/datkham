<?php
require_once __DIR__ . '/includes/patient_auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$patientId = (int) $_SESSION['patient_id'];
$statusLabels = get_allowed_statuses();

$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.patient_name, a.status, a.created_at, s.name AS service_name
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE a.patient_id = " . $patientId . "
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $sql);
$appointments = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn của tôi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Đặt khám</a>
            <div class="navbar-nav ms-auto flex-row gap-2">
                <a class="nav-link" href="dat-kham.php">Đặt lịch</a>
                <a class="nav-link" href="dang-xuat.php">Đăng xuất</a>
            </div>
        </div>
    </nav>
    <div class="container py-4">
        <h1 class="mb-4">Đơn đặt khám của tôi</h1>
        <p class="text-muted">Trạng thái đặt khám chỉ hiển thị khi bạn đã đăng nhập.</p>
        <div class="table-responsive">
            <table class="table table-bordered bg-white">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Dịch vụ</th>
                        <th>Ngày</th>
                        <th>Giờ</th>
                        <th>Trạng thái</th>
                        <th>Ngày đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Bạn chưa có đơn nào. <a href="dat-kham.php">Đặt khám</a></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td><?php echo (int) $a['id']; ?></td>
                                <td><?php echo e($a['service_name']); ?></td>
                                <td><?php echo e($a['appointment_date']); ?></td>
                                <td><?php echo e(date('H:i', strtotime($a['appointment_time']))); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $a['status'] === 'confirmed' ? 'success' : ($a['status'] === 'cancelled' ? 'danger' : ($a['status'] === 'completed' ? 'info' : 'warning'));
                                    ?>"><?php echo e($statusLabels[$a['status']] ?? $a['status']); ?></span>
                                </td>
                                <td><?php echo e($a['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="dat-kham.php" class="btn btn-primary">Đặt khám mới</a>
    </div>
</body>
</html>
