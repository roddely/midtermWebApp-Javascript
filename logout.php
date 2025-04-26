<?php
session_start();
<<<<<<< HEAD
require_once 'src/utils/db_connect.php';
=======
require_once 'includes/db.php';
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953

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

