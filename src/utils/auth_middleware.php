<?php
function checkAuth() {
    global $conn;
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
        header("Location: login.php");
        exit();
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND token = ?");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
        
        if (!$stmt->fetch()) {
            session_destroy();
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        session_destroy();
        header("Location: login.php?error=database");
        exit();
    }
} 