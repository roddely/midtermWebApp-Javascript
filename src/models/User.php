<?php
require_once __DIR__ . '/../config/db_config.php';

class User {
    private $conn;
    public $exceptions = false;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function createUser($id, $name, $email, $password_hash) {
        try {
            $this->conn->beginTransaction();
            
            // Thêm user mới
            $stmt = $this->conn->prepare("INSERT INTO users (id, name, email, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $name, $email, $password_hash]);
            
            // Tạo profile mặc định
            $stmt = $this->conn->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
            $stmt->execute([$id]);
            
            $this->conn->commit();
            return true;
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }
    
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function createSession($user_id, $token) {
        $stmt = $this->conn->prepare("INSERT INTO user_sessions (user_id, token) VALUES (?, ?)");
        return $stmt->execute([$user_id, $token]);
    }
    
    public function deleteSession($token) {
        $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE token = ?");
        return $stmt->execute([$token]);
    }
    
    public function createVerification($id, $email, $token) {
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        $stmt = $this->conn->prepare("INSERT INTO email_verification (id, email, token, expires_at) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$id, $email, $token, $expires_at]);
    }
    
    public function verifyEmail($id, $email, $token) {
        $stmt = $this->conn->prepare("
            SELECT * FROM email_verification 
            WHERE id = ? AND email = ? AND token = ? AND expires_at > NOW() AND is_used = FALSE
        ");
        $stmt->execute([$id, $email, $token]);
        
        if($row = $stmt->fetch()) {
            $stmt = $this->conn->prepare("UPDATE email_verification SET is_used = TRUE WHERE id = ?");
            $stmt->execute([$id]);
            return true;
        }
        return false;
    }
} 