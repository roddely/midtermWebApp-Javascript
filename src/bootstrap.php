<?php
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configurations
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';

// Verify database connection
try {
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Load utilities
require_once __DIR__ . '/utils/functions.php';
require_once __DIR__ . '/utils/mail.php';

// Load models
require_once __DIR__ . '/models/User.php';

// Load controllers
require_once __DIR__ . '/controllers/AuthController.php';

// Initialize main objects
$auth = new AuthController($conn); 