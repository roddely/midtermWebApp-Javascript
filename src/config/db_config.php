<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'midterm_webapp');
define('DB_USER', 'Phuc');
define('DB_PASS', '12345');
date_default_timezone_set('Asia/Ho_Chi_Minh');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}