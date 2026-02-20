<?php
ob_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = isset($_GET['msg']) ? $_GET['msg'] : '';
$messageType = isset($_GET['type']) ? $_GET['type'] : 'info';

// Add service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    if ($name !== '') {
        if (mysqli_query($conn, "INSERT INTO services (name, description) VALUES ('" . $name . "', '" . $description . "')")) {
            if (ob_get_level()) ob_end_clean();
            header('Location: services.php?msg=' . urlencode('Đã thêm dịch vụ.') . '&type=success', true, 303);
            exit;
        } else {
            $message = 'Thêm thất bại.';
            $messageType = 'danger';
        }
    } else {
        $message = 'Vui lòng nhập tên dịch vụ.';
        $messageType = 'danger';
    }
}

// Update service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $name = mysqli_real_escape_string($conn, trim($_POST['name'] ?? ''));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    if ($id > 0 && $name !== '') {
        if (mysqli_query($conn, "UPDATE services SET name = '" . $name . "', description = '" . $description . "' WHERE id = " . $id)) {
            if (ob_get_level()) ob_end_clean();
            header('Location: services.php?msg=' . urlencode('Đã cập nhật dịch vụ.') . '&type=success', true, 303);
            exit;
        } else {
            $message = 'Cập nhật thất bại.';
            $messageType = 'danger';
        }
    }
}

// Delete service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $count = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM appointments WHERE service_id = " . $id))[0] ?? 0;
        if ($count > 0) {
            $message = 'Không thể xóa dịch vụ đã có đơn đặt khám.';
            $messageType = 'danger';
        } elseif (mysqli_query($conn, "DELETE FROM services WHERE id = " . $id)) {
            if (ob_get_level()) ob_end_clean();
            header('Location: services.php?msg=' . urlencode('Đã xóa dịch vụ.') . '&type=success', true, 303);
            exit;
        } else {
            $message = 'Xóa thất bại.';
            $messageType = 'danger';
        }
    }
}

$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;
$editRow = null;
if ($editId > 0) {
    $res = mysqli_query($conn, "SELECT id, name, description FROM services WHERE id = " . $editId . " LIMIT 1");
    $editRow = $res ? mysqli_fetch_assoc($res) : null;
}

$list = [];
$r = mysqli_query($conn, "SELECT id, name, description FROM services ORDER BY id ASC");
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        $list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch vụ - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="appointments.php">Admin</a>
            <div class="navbar-nav ms-auto flex-row gap-2">
                <a class="nav-link active" href="services.php">Dịch vụ</a>
                <a class="nav-link" href="appointments.php">Đơn đặt khám</a>
                <a class="nav-link" href="logout.php">Đăng xuất</a>
            </div>
        </div>
    </nav>
    <div class="container py-4">
        <h1 class="mb-4">Quản lý dịch vụ</h1>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo e($messageType); ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <!-- Form thêm / sửa -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title"><?php echo $editRow ? 'Sửa dịch vụ' : 'Thêm dịch vụ'; ?></h5>
                <form method="post">
                    <?php if ($editRow): ?>
                        <input type="hidden" name="update" value="1">
                        <input type="hidden" name="id" value="<?php echo (int) $editRow['id']; ?>">
                    <?php else: ?>
                        <input type="hidden" name="add" value="1">
                    <?php endif; ?>
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo e($editRow['name'] ?? ''); ?>" placeholder="Nhập tên dịch vụ" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Mô tả</label>
                            <input type="text" name="description" class="form-control" value="<?php echo e($editRow['description'] ?? ''); ?>" placeholder="Mô tả ngắn (tùy chọn)">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <?php if ($editRow): ?>
                                <button type="submit" class="btn btn-primary me-1">Cập nhật</button>
                                <a href="services.php" class="btn btn-outline-secondary">Hủy</a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">Thêm</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Bảng danh sách -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Danh sách dịch vụ</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 60px;">#</th>
                                <th>Tên dịch vụ</th>
                                <th>Mô tả</th>
                                <th class="text-end" style="width: 160px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($list)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Chưa có dịch vụ nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($list as $s): ?>
                                    <tr>
                                        <td class="text-center"><?php echo (int) $s['id']; ?></td>
                                        <td><?php echo e($s['name']); ?></td>
                                        <td><?php echo e($s['description'] ?? '-'); ?></td>
                                        <td class="text-end">
                                            <a href="services.php?edit=<?php echo (int) $s['id']; ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa dịch vụ này?');">
                                                <input type="hidden" name="delete" value="1">
                                                <input type="hidden" name="id" value="<?php echo (int) $s['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
