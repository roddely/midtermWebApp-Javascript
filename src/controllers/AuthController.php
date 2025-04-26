<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/functions.php';
<<<<<<< HEAD
require_once __DIR__ . '/../utils/mail_service.php';
=======
require_once __DIR__ . '/../utils/mail.php';
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953

class AuthController {
    private $userModel;
    
    public function __construct($conn) {
        $this->userModel = new User($conn);
    }
    
    public function register($email) {
        if (!isValidEmail($email)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }
        
        if ($this->userModel->findByEmail($email)) {
            return ['success' => false, 'message' => 'Email đã được sử dụng'];
        }
        
        $verification_id = generateUUID();
        $token = generateVerificationToken();
        
        if ($this->userModel->createVerification($verification_id, $email, $token)) {
            if (sendVerificationEmail($email, $token)) {
                $_SESSION['verify_email'] = $email;
                $_SESSION['verification_id'] = $verification_id;
                return ['success' => true];
            }
        }
        
        return ['success' => false, 'message' => 'Không thể gửi email xác thực'];
    }
    
    public function verify($verification_id, $email, $token) {
        return $this->userModel->verifyEmail($verification_id, $email, $token);
    }
    
    public function completeRegistration($name, $password) {
        if (!isset($_SESSION['verified_email'])) {
            return ['success' => false, 'message' => 'Email chưa được xác thực'];
        }
        
        if (!isStrongPassword($password)) {
            return ['success' => false, 'message' => 'Mật khẩu không đủ mạnh'];
        }
        
        $user_id = generateUUID();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($this->userModel->createUser($user_id, $name, $_SESSION['verified_email'], $password_hash)) {
            unset($_SESSION['verify_email']);
            unset($_SESSION['verification_id']);
            unset($_SESSION['verified_email']);
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Không thể tạo tài khoản'];
    }
    
    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng'];
        }
        
        $session_token = generateUUID();
        if ($this->userModel->createSession($user['id'], $session_token)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['session_token'] = $session_token;
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Không thể tạo phiên đăng nhập'];
    }
    
    public function logout() {
        if (isset($_SESSION['session_token'])) {
            $this->userModel->deleteSession($_SESSION['session_token']);
        }
        session_destroy();
    }
} 