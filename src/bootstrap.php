<?php
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configurations
<<<<<<< HEAD
require_once __DIR__ . '/config/db_config.php';
require_once __DIR__ . '/config/mail_config.php';
=======
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/mail.php';
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953

// Verify database connection
try {
    $conn->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Không thể kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
}

// Load utilities
require_once __DIR__ . '/utils/functions.php';
<<<<<<< HEAD
require_once __DIR__ . '/utils/mail_service.php';
=======
require_once __DIR__ . '/utils/mail.php';
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953

// Load models
require_once __DIR__ . '/models/User.php';

// Load controllers
require_once __DIR__ . '/controllers/AuthController.php';

// Initialize main objects
$auth = new AuthController($conn); 