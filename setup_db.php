<?php
/**
 * Script tạo database và chạy schema (tạo bảng + seed).
 * Chạy: trình duyệt http://localhost/datkham/setup_db.php
 *   hoặc: php setup_db.php (trong thư mục datkham)
 */
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'datkham';

// Kết nối không chọn database (để tạo DB nếu chưa có)
$conn = @mysqli_connect($db_host, $db_user, $db_pass);
if (!$conn) {
    die('Kết nối MySQL thất bại: ' . mysqli_connect_error());
}

// Tạo database nếu chưa có
$sqlCreateDb = "CREATE DATABASE IF NOT EXISTS `" . $db_name . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!mysqli_query($conn, $sqlCreateDb)) {
    die('Tạo database thất bại: ' . mysqli_error($conn));
}

if (!mysqli_select_db($conn, $db_name)) {
    die('Chọn database thất bại: ' . mysqli_error($conn));
}
mysqli_set_charset($conn, 'utf8mb4');

// Đọc và chạy schema.sql
$schemaFile = __DIR__ . '/sql/schema.sql';
if (!is_readable($schemaFile)) {
    die('Không tìm thấy file sql/schema.sql');
}

$sql = file_get_contents($schemaFile);
if ($sql === false) {
    die('Không đọc được file schema.sql');
}

if (!mysqli_multi_query($conn, $sql)) {
    die('Lỗi khi chạy schema: ' . mysqli_error($conn));
}
// Drain result sets (multi_query trả về nhiều result)
do {
    if ($result = mysqli_store_result($conn)) {
        mysqli_free_result($result);
    }
} while (mysqli_next_result($conn));
if (mysqli_errno($conn)) {
    die('Lỗi schema: ' . mysqli_error($conn));
}

mysqli_close($conn);

// Nếu chạy từ CLI
if (php_sapi_name() === 'cli') {
    echo "Database '$db_name' đã được tạo và schema đã chạy xong.\n";
    exit(0);
}

// Chạy từ trình duyệt
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="alert alert-success">
            <h5>Chạy database thành công</h5>
            <p class="mb-0">Database <strong><?php echo htmlspecialchars($db_name); ?></strong> đã được tạo và các bảng + dữ liệu mẫu đã sẵn sàng.</p>
        </div>
        <p><a href="index.php" class="btn btn-primary">Vào trang chủ</a> <a href="admin/login.php" class="btn btn-outline-secondary">Đăng nhập Admin</a></p>
        <p class="text-muted small">Nên xóa hoặc đổi tên file <code>setup_db.php</code> sau khi cài xong để bảo mật.</p>
    </div>
</body>
</html>
