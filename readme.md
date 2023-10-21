# Tải Và Cài Đặt Composer Về Máy

https://getcomposer.org/

> Cách Cài Đặt Xem Tại Đây:
> https://www.geeksforgeeks.org/how-to-install-php-composer-on-windows/

# Thư Viện JWT

> Xem Hướng Dẫn Cài Đặt Và Sử Dụng Tại Đây:
> https://github.com/firebase/php-jwt

# Thư Viện Lưu Trữ Hình Ảnh Cloudinary:

> Xem Hướng Dẫn Tại Đây:
> https://packagist.org/packages/cloudinary/cloudinary_php

# Cấu Trúc Thư Mục

1. Code API ở thư mục `/api/v1`
2. API Có Dạng: localhost:xxxx://api/v1/xxx/xxx.php
3. Thư Mục `utils` chứa các function dùng chung cho toàn app
4. Thư Mục Chứa Các Thư Viện `vendor`
5. File Cấu Hình Thư Viện `composer.json` và `composer.lock`
6. Thư Mục `configs` Chứa Các Config về Database, Các Biến Cần Bảo Mật
7. Thư Mục `database` Lưu Trữ Các Thông Tin Về Database Hệ Thống

# Thuật Toán Cơ Bản

### Phân Trang

> Công Thức: `(page - 1) * limit`
> Trong Đó:
> page: Trang Hiện Tại
> limit: Số Lượng Phần Tử Client Cần Cho 1 Trang
> Ví Dụ:
> Client gọi api: `localhost:3001/product?page=2&limit=20`
> Với page = 2 và limit = 20 => Bỏ Qua 20 Sản Phẩm Ban Đầu Lấy 20 Sản Phẩm Kế Tiếp => Viết Câu SQL
