<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'midterm_webapp');
define('DB_USER', 'Phuc');
define('DB_PASS', '12345'); // Mật khẩu trống cho XAMPP mặc định

try {
    $dsn = "mysql:host=" . DB_HOST;
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tạo database nếu chưa tồn tại
    $conn->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Chọn database
    $conn->exec("USE `" . DB_NAME . "`");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
} 