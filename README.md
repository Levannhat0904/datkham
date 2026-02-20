# BÁO CÁO ĐỒ ÁN  
## Hệ thống đặt khám bệnh viện (Website đặt lịch khám trực tuyến)

---

## 1. Giới thiệu

### 1.1 Tên đề tài
**Hệ thống đặt khám bệnh viện** – Website cho phép bệnh nhân đăng ký tài khoản, đặt lịch khám theo dịch vụ và ngày giờ, theo dõi trạng thái đơn; phía quản trị quản lý dịch vụ khám và đơn đặt khám, cập nhật trạng thái và xuất báo cáo Excel.

### 1.2 Mục tiêu
- Xây dựng website đặt lịch khám trực tuyến cho bệnh nhân.
- Cung cấp khu vực quản trị (admin) để quản lý dịch vụ và đơn đặt khám.
- Hỗ trợ lọc đơn theo ngày, dịch vụ, trạng thái và xuất danh sách đơn ra file Excel (CSV).

### 1.3 Phạm vi
- **Người dùng (bệnh nhân):** Đăng ký, đăng nhập, cập nhật thông tin cá nhân, đặt lịch khám (chọn dịch vụ + ngày giờ), xem “Đơn của tôi”.
- **Quản trị viên:** Đăng nhập, CRUD dịch vụ khám, xem danh sách đơn đặt khám, lọc, cập nhật trạng thái đơn (Chờ xác nhận / Đã xác nhận / Đã hủy / Đã khám), xuất Excel đơn đặt khám.

---

## 2. Công nghệ sử dụng

| Thành phần      | Công nghệ                          |
|-----------------|------------------------------------|
| Backend         | PHP (thuần, không framework)       |
| Cơ sở dữ liệu   | MySQL / MariaDB (mysqli)           |
| Giao diện       | HTML5, CSS3, Bootstrap 5          |
| Server local    | XAMPP (Apache + MySQL + PHP)       |
| Xuất báo cáo    | CSV (UTF-8 BOM, dấu phân cách `;`) |

---

## 3. Phân tích hệ thống

### 3.1 Đối tượng sử dụng
- **Bệnh nhân:** Đặt lịch khám, xem đơn của mình.
- **Quản trị viên (Admin):** Quản lý dịch vụ, quản lý đơn đặt khám, xuất Excel.

### 3.2 Chức năng chính

#### 3.2.1 Phía người dùng (public)
- **Trang chủ:** Giới thiệu, link Đăng ký / Đăng nhập / Đặt lịch / Admin.
- **Đăng ký:** Tạo tài khoản (email, mật khẩu, họ tên, SĐT).
- **Đăng nhập / Đăng xuất:** Xác thực session.
- **Đặt lịch (3 bước):**
  1. **Bước 1 – Thông tin bệnh nhân:** Cập nhật họ tên, SĐT, email.
  2. **Bước 2 – Đặt lịch khám:** Chọn dịch vụ, chọn ngày giờ (datetime picker), ghi chú → Gửi đơn.
  3. **Bước 3 – Hoàn thành:** Chuyển sang trang xác nhận (`xac-nhan.php`) với mã đơn.
- **Đơn của tôi:** Danh sách đơn đặt khám của bệnh nhân đang đăng nhập, hiển thị trạng thái.

#### 3.2.2 Phía quản trị (admin)
- **Đăng nhập admin:** Tài khoản mặc định (seed) `admin` / `admin123`.
- **Dịch vụ:** Thêm, sửa, xóa dịch vụ khám (tên, mô tả).
- **Đơn đặt khám:**
  - Danh sách đơn (join bảng `services` để hiển thị tên dịch vụ).
  - Lọc theo: ngày khám, dịch vụ, trạng thái.
  - Cập nhật trạng thái: Chờ xác nhận → Đã xác nhận / Đã hủy / Đã khám.
  - **Xuất Excel:** Nút “Xuất Excel” tải file CSV (áp dụng đúng bộ lọc hiện tại), định dạng UTF-8, mỗi trường một cột.

### 3.3 Trạng thái đơn đặt khám
| Mã (status) | Hiển thị        |
|-------------|------------------|
| pending     | Chờ xác nhận     |
| confirmed   | Đã xác nhận      |
| cancelled   | Đã hủy           |
| completed   | Đã khám          |

---

## 4. Thiết kế cơ sở dữ liệu

### 4.1 Sơ đồ thực thể chính
- **patients:** Bệnh nhân (email, password_hash, full_name, phone, created_at).
- **services:** Dịch vụ khám (name, description).
- **appointments:** Đơn đặt khám (patient_id, service_id, appointment_date, appointment_time, patient_name, patient_phone, patient_email, note, status, created_at).
- **admin_users:** Tài khoản admin (username, password_hash).

### 4.2 Bảng chi tiết (tóm tắt)

**patients**
- `id` (PK), `email` (unique), `password_hash`, `full_name`, `phone`, `created_at`.

**services**
- `id` (PK), `name`, `description`.

**appointments**
- `id` (PK), `patient_id` (FK → patients), `service_id` (FK → services), `appointment_date`, `appointment_time`, `patient_name`, `patient_phone`, `patient_email`, `note`, `status` (default: `pending`), `created_at`.

**admin_users**
- `id` (PK), `username` (unique), `password_hash`, `created_at`.

*(Chi tiết đầy đủ xem file `sql/schema.sql`.)*

---

## 5. Luồng nghiệp vụ chính

### 5.1 Luồng đặt khám (bệnh nhân)
1. Đăng nhập (hoặc đăng ký rồi đăng nhập).
2. Vào “Đặt lịch” → Bước 1: Cập nhật thông tin → Bước 2: Chọn dịch vụ + ngày giờ + ghi chú → Gửi.
3. Hệ thống kiểm tra trùng lịch (cùng dịch vụ + ngày + giờ, chưa hủy); nếu trùng báo lỗi, không trùng thì tạo đơn `pending`.
4. Chuyển sang trang xác nhận, hiển thị mã đơn.

### 5.2 Luồng quản lý đơn (admin)
1. Đăng nhập admin.
2. Vào “Đơn đặt khám” → Xem danh sách, có thể lọc theo ngày / dịch vụ / trạng thái.
3. Cập nhật trạng thái từng đơn (pending → confirmed / cancelled / completed).
4. Bấm “Xuất Excel” → Tải file CSV với dữ liệu đúng bộ lọc, UTF-8, mỗi cột một trường (#, Dịch vụ, Ngày khám, Giờ, Bệnh nhân, SĐT, Email, Ghi chú, Trạng thái, Ngày tạo).

---

## 6. Cấu trúc thư mục (chính)

```
datkham/
├── config/
│   └── database.php          # Kết nối MySQL
├── includes/
│   ├── functions.php         # Hàm dùng chung (e, get_allowed_statuses, is_allowed_status)
│   └── patient_auth.php      # Kiểm tra đăng nhập bệnh nhân
├── admin/
│   ├── includes/
│   │   └── auth.php          # Kiểm tra đăng nhập admin
│   ├── appointments.php      # Danh sách đơn, lọc, cập nhật trạng thái, xuất Excel
│   ├── services.php          # CRUD dịch vụ
│   ├── login.php, logout.php
│   └── index.php             # Redirect
├── sql/
│   └── schema.sql            # Tạo bảng + seed admin, dịch vụ mẫu
├── index.php                 # Trang chủ
├── dang-ky.php, dang-nhap.php, dang-xuat.php
├── dat-kham.php              # Đặt lịch (step 1, 2)
├── xac-nhan.php              # Trang xác nhận sau khi đặt
├── don-cua-toi.php           # Đơn của tôi
└── setup_db.php              # Chạy schema (tạo DB + bảng)
```

---

## 7. Hướng dẫn cài đặt và chạy thử

### 7.1 Yêu cầu
- XAMPP (Apache, MySQL, PHP) hoặc môi trường tương đương.
- PHP hỗ trợ mysqli, session.

### 7.2 Các bước
1. Đặt source vào thư mục web (ví dụ: `htdocs/datkham`).
2. Mở XAMPP, khởi động Apache và MySQL.
3. Tạo database: trong phpMyAdmin hoặc MySQL tạo database `datkham`, sau đó truy cập `http://localhost/datkham/setup_db.php` để chạy schema (tạo bảng, seed admin và dịch vụ mẫu). Hoặc import file `sql/schema.sql` (đã có `CREATE DATABASE` / `USE` tùy phiên bản).
4. Cấu hình kết nối DB trong `config/database.php` (host, user, password, tên DB) nếu khác mặc định.
5. Truy cập:
   - Trang chủ: `http://localhost/datkham/`
   - Admin: `http://localhost/datkham/admin/login.php` (admin / admin123).

### 7.3 Tài khoản mặc định
- **Admin:** username `admin`, mật khẩu `admin123` (được seed trong `schema.sql`).

---

## 8. Kết luận và hướng phát triển

### 8.1 Kết luận
Đồ án đã xây dựng được hệ thống đặt khám cơ bản với đầy đủ luồng: đăng ký/đăng nhập bệnh nhân, đặt lịch 2 bước, xem đơn của tôi; phía admin quản lý dịch vụ và đơn đặt khám, cập nhật trạng thái và xuất báo cáo Excel (CSV) theo bộ lọc.

### 8.2 Hướng phát triển (gợi ý)
- Gửi email/SMS xác nhận khi đơn được duyệt hoặc hủy.
- Quản lý lịch khả dụng (slots) theo dịch vụ/ngày thay vì chỉ chọn ngày giờ tự do.
- Thêm phân quyền admin (nhiều vai trò).
- Giao diện responsive và trải nghiệm người dùng (UX) tốt hơn.
- Bảo mật: chuẩn hóa prepared statements (hoặc PDO) để chống SQL injection; bảo vệ CSRF cho form.

---

*Tài liệu phục vụ báo cáo đồ án – Cập nhật theo phiên bản hiện tại của mã nguồn.*
