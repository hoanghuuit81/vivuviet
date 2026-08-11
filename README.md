# Vi Vu Việt

Website cộng đồng giới thiệu địa danh du lịch Việt Nam, xây dựng bằng PHP thuần, MySQL, HTML, CSS và JavaScript.

## Truy cập

- Website: `http://localhost.com/miniproject`
- Cổng admin: `http://localhost.com/miniproject/admin`
- Customer mẫu: `user@vivuviet.vn` / `User@123`
- Admin mẫu: `admin@vivuviet.vn` / `Admin@123`

## Chức năng chính

- Khám phá địa danh theo miền → tỉnh/thành → địa điểm → bài viết.
- Tìm kiếm, lọc địa điểm và bài viết.
- Customer đăng ký, đăng nhập, đánh giá, thả tim và bình luận.
- Customer gửi địa danh, theo dõi trạng thái và chỉnh sửa theo yêu cầu của admin.
- Hai cổng đăng nhập tách biệt: tài khoản admin chỉ đăng nhập được tại `/admin`.
- Customer cập nhật ảnh đại diện, xem lại bình luận và đánh giá của mình.
- Trang liên hệ lưu nội dung trong admin và dùng hàm `mail()` của PHP để gửi đến `taisaokhong81@gmail.com`.
- Admin duyệt địa danh, quản lý bài viết, bình luận, người dùng và nội dung nổi bật.
- Form thêm địa danh của admin độc lập tại `/admin/places/new`; cả hai form dùng CKEditor 5 cục bộ.
- Upload ảnh JPG, PNG, WebP tối đa 5MB.

## Cơ sở dữ liệu

Cấu hình mặc định nằm tại `config/app.php` và có thể ghi đè bằng các biến môi trường `MINIPROJECT_DB_HOST`, `MINIPROJECT_DB_PORT`, `MINIPROJECT_DB_NAME`, `MINIPROJECT_DB_USER`, `MINIPROJECT_DB_PASS`.

Để khởi tạo lại dữ liệu trên một database trống:

```bash
mysql -u <user> -p <database> < database/schema.sql
mysql -u <user> -p <database> < database/seed.sql
```

Hãy đổi mật khẩu hai tài khoản mẫu trước khi sử dụng ở môi trường công khai.

Để email thực sự được chuyển đi ở máy chủ triển khai, PHP cần có `sendmail` hoặc SMTP được cấu hình; môi trường cục bộ hiện chưa có dịch vụ gửi mail.
