=======================================
Thiết lập Database
=======================================
Chạy file sql

Vào database.php
Chỉnh lại Host của database

=======================================
Thiết lập gửi mail
=======================================

1.  Thiết lập Gmail (Bảo mật 2 lớp và Mật khẩu ứng dụng):
    Đăng nhập vào tài khoản Gmail của bạn (your_mail@gmail.com)
    Truy cập: https://myaccount.google.com/security
    Trong mục "Bảo mật" tìm "Xác minh 2 bước" (2-Step Verification)
    Bật xác minh 2 bước nếu chưa bật
    Sau khi bật xong, quay lại trang bảo mật
    Tìm mục "Mật khẩu ứng dụng" (App passwords)
    Chọn "Chọn ứng dụng" -> Chọn "Khác" (Custom)
    Đặt tên cho ứng dụng (ví dụ: "PHP Mail App")
    Nhấn "Tạo"
    Lưu lại mã 16 ký tự được tạo ra
2.  Kiểm tra cài đặt SMTP trong Gmail:
    Đăng nhập vào Gmail
    Vào Cài đặt (Settings)
    Chọn tab "Chuyển tiếp và POP/IMAP"
    Bật "Bật IMAP"
    Lưu thay đổi
3.  Kiểm tra cài đặt tường lửa Windows:
    Mở Windows Defender Firewall (tìm trong Start menu)
    Chọn "Cài đặt nâng cao"
    Chọn "Quy tắc kết nối đến" (Inbound Rules)
    Tìm kiếm quy tắc liên quan đến port 587
    Nếu chưa có, tạo quy tắc mới:
    Chọn "Quy tắc mới" (New Rule)
    Chọn "Port"
    Nhập port 587
    Cho phép kết nối (Allow the connection)
    Áp dụng cho tất cả profile
    Đặt tên (ví dụ: "SMTP Port 587")
4.  Kiểm tra kết nối SMTP:
    Mở Command Prompt
    Thử:
    - ping smtp.gmail.com
      Kiểm tra port 587:
    - telnet smtp.gmail.com 587
5.  Kiểm tra cài đặt PHP:
    Tìm file php.ini trong máy
    Đảm bảo extension=openssl được bật (bỏ dấu ; ở đầu dòng)
    Tìm và kiểm tra các cài đặt SMTP:
    - SMTP=smtp.gmail.com
    - smtp_port=587
6.  Kiểm tra antivirus:
    Tạm thời tắt antivirus để test
    Nếu gửi mail được khi tắt antivirus, thêm ngoại lệ cho ứng dụng của bạn
7.  Test kết nối:
    Mở terminal
    Thử test kết nối đến Gmail:
    - telnet smtp.gmail.com 587
      Nếu kết nối được, bạn sẽ thấy response 220
