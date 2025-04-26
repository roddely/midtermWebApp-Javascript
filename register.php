<?php
session_start();
<<<<<<< HEAD
require_once 'src/utils/db_connect.php';
require_once 'src/utils/mail_service.php';
=======
require_once 'src/config/database.php';
require_once 'src/utils/mail.php';
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953

// Đầu register.php, sau session_start()
date_default_timezone_set('Asia/Ho_Chi_Minh');

function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
<<<<<<< HEAD
mt_rand(0, 0xffff), 
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), 
        mt_rand(0, 0xffff), 
        mt_rand(0, 0xffff)
=======
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953
    );
}

function generateVerificationToken() {
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

if(isset($_POST['verify_email'])) {
    $email = trim($_POST['email']);
    
    if(empty($email)) {
        $error = "Vui lòng nhập email";
    } else {
        // Kiểm tra email đã tồn tại
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->rowCount() > 0) {
            $error = "Email đã được sử dụng";
        } else {
            // Tạo mã xác thực mới
            $verification_id = generateUUID();
            $token = generateVerificationToken();
<<<<<<< HEAD
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));
=======
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953
            
            // Lưu thông tin xác thực
            $stmt = $conn->prepare("INSERT INTO email_verification (id, email, token, expires_at) VALUES (?, ?, ?, ?)");
            if($stmt->execute([$verification_id, $email, $token, $expires_at])) {
                // Gửi email xác thực
<<<<<<< HEAD
                if(sendVerificationEmail($email, $token, $mailError)) {
=======
                if(sendVerificationEmail($email, $token)) {
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953
                    $_SESSION['verify_source'] = 'register';
                    $_SESSION['verify_email'] = $email;
                    $_SESSION['verification_id'] = $verification_id;
                    header("Location: verify.php");
                    exit();
<<<<<<< HEAD
                 } else {
=======
                } else {
>>>>>>> c9253647bd2e4ed82ff64d607488f450c332b953
                    $error = "Không thể gửi email xác thực. Vui lòng thử lại sau.";
                }
            } else {
                $error = "Có lỗi xảy ra, vui lòng thử lại";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Đăng ký tài khoản</h2>
                <p class="text-muted">Bước 1: Xác thực email</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                    <div class="form-text">Chúng tôi sẽ gửi mã xác thực đến email này.</div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="verify_email" class="btn btn-primary">
                        Gửi mã xác thực
                    </button>
                </div>
            </form>
            
            <div class="auth-footer">
                <p class="mb-0">Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>
</html> 