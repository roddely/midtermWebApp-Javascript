<?php
session_start();
require_once 'src/utils/db_connect.php';

if(isset($_SESSION['session_token'])) {
    // Xóa session trong database
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE token = ?");
    $stmt->execute([$_SESSION['session_token']]);
}

// Xóa session PHP
session_destroy();
header("Location: login.php");
exit();
?> 

