<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle status update (POST)
$message = '';
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appointmentId = isset($_POST['appointment_id']) ? (int) $_POST['appointment_id'] : 0;
    $newStatus = trim($_POST['new_status'] ?? '');
    if ($appointmentId > 0 && is_allowed_status($newStatus)) {
        $newStatus = mysqli_real_escape_string($conn, $newStatus);
        $sql = "UPDATE appointments SET status = '" . $newStatus . "' WHERE id = " . $appointmentId;
        if (mysqli_query($conn, $sql) && mysqli_affected_rows($conn) > 0) {
            $message = 'Đã cập nhật trạng thái đặt khám.';
            $messageType = 'success';
        } else {
            $message = 'Cập nhật thất bại hoặc đơn không tồn tại.';
            $messageType = 'danger';
        }
    } else {
        $message = 'Dữ liệu không hợp lệ.';
        $messageType = 'danger';
    }
    header('Location: appointments.php?msg=' . urlencode($message) . '&type=' . urlencode($messageType));
    exit;
}
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'info';
}

// Filters (optional)
$filterDate = isset($_GET['filter_date']) ? mysqli_real_escape_string($conn, $_GET['filter_date']) : '';
$filterService = isset($_GET['filter_service']) ? (int) $_GET['filter_service'] : 0;
$filterStatus = isset($_GET['filter_status']) ? mysqli_real_escape_string($conn, $_GET['filter_status']) : '';

$where = ['1=1'];
if ($filterDate !== '') {
    $where[] = "a.appointment_date = '" . $filterDate . "'";
}
if ($filterService > 0) {
    $where[] = 'a.service_id = ' . $filterService;
}
if ($filterStatus !== '' && is_allowed_status($filterStatus)) {
    $where[] = "a.status = '" . $filterStatus . "'";
}
$whereClause = implode(' AND ', $where);

$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.patient_name, a.patient_phone, a.status, a.created_at,
        s.name AS service_name
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE " . $whereClause . "
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = mysqli_query($conn, $sql);
$appointments = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

// Services for filter dropdown
$servicesResult = mysqli_query($conn, "SELECT id, name FROM services ORDER BY name");
$services = $servicesResult ? mysqli_fetch_all($servicesResult, MYSQLI_ASSOC) : [];

$statusLabels = get_allowed_statuses();

// Export Excel (CSV)
if (isset($_GET['export'])) {
    $exportSql = "SELECT a.id, a.appointment_date, a.appointment_time, a.patient_name, a.patient_phone, a.patient_email, a.note, a.status, a.created_at, s.name AS service_name
        FROM appointments a
        JOIN services s ON s.id = a.service_id
        WHERE " . $whereClause . "
        ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $exportResult = mysqli_query($conn, $exportSql);
    $rows = $exportResult ? mysqli_fetch_all($exportResult, MYSQLI_ASSOC) : [];

    $filename = 'don-dat-kham-' . date('Y-m-d-Hi') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
    $delimiter = ';'; // Excel (VN/locale) tách đúng mỗi trường thành 1 cột
    fputcsv($out, ['#', 'Dịch vụ', 'Ngày khám', 'Giờ', 'Bệnh nhân', 'SĐT', 'Email', 'Ghi chú', 'Trạng thái', 'Ngày tạo'], $delimiter);
    foreach ($rows as $r) {
        $statusLabel = $statusLabels[$r['status']] ?? $r['status'];
        fputcsv($out, [
            $r['id'],
            $r['service_name'],
            $r['appointment_date'],
            date('H:i', strtotime($r['appointment_time'])),
            $r['patient_name'],
            $r['patient_phone'],
            $r['patient_email'] ?? '',
            $r['note'] ?? '',
            $statusLabel,
            $r['created_at'],
        ], $delimiter);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn đặt khám - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="appointments.php">Admin</a>
            <div class="navbar-nav ms-auto flex-row gap-2">
                <a class="nav-link" href="services.php">Dịch vụ</a>
                <a class="nav-link active" href="appointments.php">Đơn đặt khám</a>
                <a class="nav-link" href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h1 class="mb-4">Đơn đặt khám</h1>

        <?php if ($message !== ''): ?>
            <div class="alert alert-<?php echo e($messageType); ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <form method="get" class="row g-2 mb-4">
            <div class="col-auto">
                <input type="date" name="filter_date" class="form-control" value="<?php echo e($filterDate); ?>" placeholder="Ngày">
            </div>
            <div class="col-auto">
                <select name="filter_service" class="form-select">
                    <option value="">-- Dịch vụ --</option>
                    <?php foreach ($services as $sv): ?>
                        <option value="<?php echo $sv['id']; ?>" <?php echo ($filterService === (int)$sv['id']) ? 'selected' : ''; ?>><?php echo e($sv['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="filter_status" class="form-select">
                    <option value="">-- Trạng thái --</option>
                    <?php foreach ($statusLabels as $val => $label): ?>
                        <option value="<?php echo e($val); ?>" <?php echo ($filterStatus === $val) ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">Lọc</button>
            </div>
            <div class="col-auto">
                <?php
                $exportParams = [];
                if ($filterDate !== '') $exportParams['filter_date'] = $filterDate;
                if ($filterService > 0) $exportParams['filter_service'] = $filterService;
                if ($filterStatus !== '') $exportParams['filter_status'] = $filterStatus;
                $exportParams['export'] = '1';
                $exportUrl = 'appointments.php?' . http_build_query($exportParams);
                ?>
                <a href="<?php echo e($exportUrl); ?>" class="btn btn-success">Xuất Excel</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Dịch vụ</th>
                        <th>Ngày khám</th>
                        <th>Giờ</th>
                        <th>Bệnh nhân</th>
                        <th>SĐT</th>
                        <th>Trạng thái</th>
                        <th>Cập nhật trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Chưa có đơn đặt khám nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $a): ?>
                            <tr>
                                <td><?php echo (int) $a['id']; ?></td>
                                <td><?php echo e($a['service_name']); ?></td>
                                <td><?php echo e($a['appointment_date']); ?></td>
                                <td><?php echo e(date('H:i', strtotime($a['appointment_time']))); ?></td>
                                <td><?php echo e($a['patient_name']); ?></td>
                                <td><?php echo e($a['patient_phone']); ?></td>
                                <td>
                                    <span class="badge bg-<?php
                                        echo $a['status'] === 'confirmed' ? 'success' : ($a['status'] === 'cancelled' ? 'danger' : ($a['status'] === 'completed' ? 'info' : 'warning'));
                                    ?>"><?php echo e($statusLabels[$a['status']] ?? $a['status']); ?></span>
                                </td>
                                <td>
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="update_status" value="1">
                                        <input type="hidden" name="appointment_id" value="<?php echo (int) $a['id']; ?>">
                                        <select name="new_status" class="form-select form-select-sm d-inline-block w-auto">
                                            <?php foreach ($statusLabels as $val => $label): ?>
                                                <option value="<?php echo e($val); ?>" <?php echo ($a['status'] === $val) ? 'selected' : ''; ?>><?php echo e($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary ms-1">Cập nhật</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
