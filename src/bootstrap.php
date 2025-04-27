<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/config/mail_config.php';

try {
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}


require_once __DIR__ . '/utils/functions.php';
require_once __DIR__ . '/utils/mail_service.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/controllers/AuthController.php';

$auth = new AuthController($conn); 