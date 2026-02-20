<?php
session_start();
if (!empty($_SESSION['patient_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($email === '' || $password === '' || $fullName === '' || $phone === '') {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu tối thiểu 6 ký tự.';
    } else {
        $email = mysqli_real_escape_string($conn, $email);
        $exists = mysqli_query($conn, "SELECT id FROM patients WHERE email = '" . $email . "' LIMIT 1");
        if ($exists && mysqli_fetch_assoc($exists)) {
            $error = 'Email này đã được đăng ký.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $fullName = mysqli_real_escape_string($conn, $fullName);
            $phone = mysqli_real_escape_string($conn, $phone);
            $sql = "INSERT INTO patients (email, password_hash, full_name, phone) VALUES ('" . $email . "', '" . mysqli_real_escape_string($conn, $hash) . "', '" . $fullName . "', '" . $phone . "')";
            if (mysqli_query($conn, $sql)) {
                $success = 'Đăng ký thành công. Bạn có thể đăng nhập.';
            } else {
                $error = 'Đăng ký thất bại.';
            }
        }
    }
}
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Đặt khám</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dang-nhap.php">Đăng nhập</a>
            </div>
        </div>
    </nav>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Đăng ký tài khoản</h5>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
                        <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
                        <form method="post">
                            <div class="mb-2">
                                <label class="form-label">Họ tên</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo e($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Số điện thoại</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo e($_POST['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng ký</button>
                        </form>
                        <p class="mt-2 mb-0 text-center"><a href="dang-nhap.php">Đã có tài khoản? Đăng nhập</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
