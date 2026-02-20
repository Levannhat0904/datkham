<?php
session_start();
if (!empty($_SESSION['patient_id'])) {
    header('Location: ' . ($_GET['redirect'] ?? 'index.php'));
    exit;
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Vui lòng nhập email và mật khẩu.';
    } else {
        $email = mysqli_real_escape_string($conn, $email);
        $res = mysqli_query($conn, "SELECT id, password_hash, full_name FROM patients WHERE email = '" . $email . "' LIMIT 1");
        $row = $res ? mysqli_fetch_assoc($res) : null;
        if ($row && password_verify($password, $row['password_hash'])) {
            $_SESSION['patient_id'] = (int) $row['id'];
            $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
            header('Location: ' . $redirect);
            exit;
        }
        $error = 'Email hoặc mật khẩu không đúng.';
    }
}
$redirect = isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Đặt khám</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dang-ky.php">Đăng ký</a>
            </div>
        </div>
    </nav>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Đăng nhập</h5>
                        <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
                        <form method="post" action="dang-nhap.php<?php echo e($redirect); ?>">
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mật khẩu</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                        </form>
                        <p class="mt-2 mb-0 text-center"><a href="dang-ky.php">Chưa có tài khoản? Đăng ký</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
