<?php
require_once __DIR__ . '/includes/patient_auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$patientId = (int) $_SESSION['patient_id'];

// Get patient info
$patient = null;
$res = mysqli_query($conn, "SELECT full_name, phone, email FROM patients WHERE id = " . $patientId . " LIMIT 1");
if ($res && $row = mysqli_fetch_assoc($res)) {
    $patient = $row;
}

// Step: 1 = Thông tin BN, 2 = Đặt lịch khám, (3 = Hoàn thành là xac-nhan.php)
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;
$serviceId = isset($_POST['service_id']) ? (int) $_POST['service_id'] : (isset($_GET['service_id']) ? (int) $_GET['service_id'] : 0);

$message = '';
$messageType = '';

// POST: Cập nhật thông tin BN (bước 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_patient'])) {
    $fullName = mysqli_real_escape_string($conn, trim($_POST['full_name'] ?? ''));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    if ($fullName !== '' && $phone !== '') {
        mysqli_query($conn, "UPDATE patients SET full_name = '" . $fullName . "', phone = '" . $phone . "', email = '" . $email . "' WHERE id = " . $patientId);
        $patient = ['full_name' => $fullName, 'phone' => $phone, 'email' => $email];
        header('Location: dat-kham.php?step=2');
        exit;
    }
    $message = 'Vui lòng nhập họ tên và số điện thoại.';
    $messageType = 'danger';
}

// POST: Xác nhận đặt khám (bước 2) - Ngày khám từ datetime picker (không check schedules)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $serviceId = (int) ($_POST['service_id'] ?? 0);
    $appointmentDatetime = trim($_POST['appointment_datetime'] ?? '');
    $note = mysqli_real_escape_string($conn, trim($_POST['note'] ?? ''));
    $patientName = mysqli_real_escape_string($conn, trim($patient['full_name'] ?? ''));
    $patientPhone = mysqli_real_escape_string($conn, trim($patient['phone'] ?? ''));
    $patientEmail = mysqli_real_escape_string($conn, trim($patient['email'] ?? ''));

    if ($serviceId > 0 && $patientName !== '' && $patientPhone !== '' && $appointmentDatetime !== '') {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $appointmentDatetime);
        if (!$dt) {
            $dt = DateTime::createFromFormat('Y-m-d H:i', $appointmentDatetime);
        }
        if ($dt) {
            $appointmentDate = $dt->format('Y-m-d');
            $appointmentTime = $dt->format('H:i:s');
            $appointmentTimeEsc = mysqli_real_escape_string($conn, $appointmentTime);
            $appointmentDateEsc = mysqli_real_escape_string($conn, $appointmentDate);
            $check = mysqli_query($conn, "SELECT id FROM appointments WHERE service_id = " . $serviceId . " AND appointment_date = '" . $appointmentDateEsc . "' AND appointment_time = '" . $appointmentTimeEsc . "' AND status != 'cancelled' LIMIT 1");
            if ($check && mysqli_fetch_assoc($check)) {
                $message = 'Thời gian này đã được đặt. Vui lòng chọn thời gian khác.';
                $messageType = 'danger';
            } else {
                $sql = "INSERT INTO appointments (patient_id, service_id, appointment_date, appointment_time, patient_name, patient_phone, patient_email, note, status) VALUES (" . $patientId . ", " . $serviceId . ", '" . $appointmentDateEsc . "', '" . $appointmentTimeEsc . "', '" . $patientName . "', '" . $patientPhone . "', '" . $patientEmail . "', '" . $note . "', 'pending')";
                if (mysqli_query($conn, $sql)) {
                    header('Location: xac-nhan.php?id=' . mysqli_insert_id($conn));
                    exit;
                }
                $message = 'Đặt khám thất bại.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Ngày giờ khám không hợp lệ.';
            $messageType = 'danger';
        }
    } else {
        $message = 'Vui lòng chọn đầy đủ dịch vụ và ngày giờ khám.';
        $messageType = 'danger';
    }
}

// Load services
$services = [];
$sr = mysqli_query($conn, "SELECT id, name FROM services ORDER BY name");
if ($sr) {
    while ($row = mysqli_fetch_assoc($sr)) {
        $services[] = $row;
    }
}

$serviceName = '';
foreach ($services as $s) {
    if ((int) $s['id'] === $serviceId) {
        $serviceName = $s['name'];
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt khám</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-bar { display: flex; align-items: center; margin-bottom: 1.5rem; }
        .step-item { flex: 1; text-align: center; padding: 0.5rem; font-weight: 500; color: #6c757d; }
        .step-item.active { color: #0d6efd; }
        .step-item.done { color: #198754; }
        .step-num { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #e9ecef; color: #6c757d; margin-right: 0.25rem; }
        .step-item.active .step-num { background: #0d6efd; color: #fff; }
        .step-item.done .step-num { background: #198754; color: #fff; }
        .card-step { border-left: 4px solid #0d6efd; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Đặt khám</a>
            <div class="navbar-nav ms-auto flex-row gap-2">
                <a class="nav-link" href="don-cua-toi.php">Đơn của tôi</a>
                <a class="nav-link" href="dang-xuat.php">Đăng xuất</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Thanh tiến trình: 3 bước (bỏ Xác thực CCCD) -->
        <div class="step-bar">
            <div class="step-item <?php echo $step === 1 ? 'active' : ($step > 1 ? 'done' : ''); ?>">
                <span class="step-num">1</span> Thông tin BN
            </div>
            <div class="step-item <?php echo $step === 2 ? 'active' : ($step > 2 ? 'done' : ''); ?>">
                <span class="step-num">2</span> Đặt lịch khám
            </div>
            <div class="step-item <?php echo $step > 2 ? 'done' : ''; ?>">
                <span class="step-num">3</span> Hoàn thành
            </div>
        </div>

        <h2 class="mb-4">Đặt khám</h2>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo e($messageType); ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <!-- Bước 1: Thông tin bệnh nhân -->
            <div class="card card-step shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><span class="text-primary">1.</span> Thông tin bệnh nhân</h5>
                    <form method="post">
                        <input type="hidden" name="update_patient" value="1">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo e($patient['full_name'] ?? ''); ?>" placeholder="Nhập họ tên" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control" value="<?php echo e($patient['phone'] ?? ''); ?>" placeholder="Nhập số điện thoại" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e($patient['email'] ?? ''); ?>" placeholder="Nhập email">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Tiếp tục</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($step === 2): ?>
            <!-- Bước 2: Đặt lịch khám (có block thông tin BN + form chọn dịch vụ, ngày, giờ) -->
            <div class="card card-step shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title text-muted">Thông tin bệnh nhân</h5>
                    <div class="row g-2 small">
                        <div class="col-md-6"><strong>Họ tên:</strong> <?php echo e($patient['full_name'] ?? '-'); ?></div>
                        <div class="col-md-6"><strong>Số điện thoại:</strong> <?php echo e($patient['phone'] ?? '-'); ?></div>
                        <div class="col-md-6"><strong>Email:</strong> <?php echo e($patient['email'] ?? '-'); ?></div>
                    </div>
                    <a href="dat-kham.php?step=1" class="btn btn-link btn-sm p-0 mt-1">Sửa thông tin</a>
                </div>
            </div>

            <div class="card card-step shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><span class="text-primary">2.</span> Đặt lịch khám</h5>

                    <?php if (empty($services)): ?>
                        <p class="text-muted">Chưa có dịch vụ nào. Vui lòng liên hệ quản trị.</p>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="confirm_booking" value="1">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Dịch vụ <span class="text-danger">*</span></label>
                                    <select name="service_id" class="form-select" required>
                                        <option value="">Chọn dịch vụ</option>
                                        <?php foreach ($services as $s): ?>
                                            <option value="<?php echo $s['id']; ?>" <?php echo ($serviceId === (int)$s['id']) ? 'selected' : ''; ?>><?php echo e($s['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Ngày khám <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="appointment_datetime" class="form-control" required placeholder="Chọn ngày, giờ khám" min="<?php echo date('Y-m-d\T00:00'); ?>" value="<?php echo e($_POST['appointment_datetime'] ?? ''); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Thêm ghi chú cho bác sĩ (tùy chọn)"></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <a href="dat-kham.php?step=1" class="btn btn-outline-secondary">Quay lại</a>
                                <button type="submit" class="btn btn-primary">Đặt khám</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted">Bước không hợp lệ. <a href="dat-kham.php">Bắt đầu lại</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>
