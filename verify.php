<?php
session_start();
require_once 'src/utils/db_connect.php';
require_once 'src/utils/functions.php';
require_once 'src/utils/mail_service.php';

date_default_timezone_set('Asia/Ho_Chi_Minh'); //Đặt múi giờ cho Việt Nam

// Kiểm tra xem có email đang chờ xác thực không
if(!isset($_SESSION['verify_email']) || !isset($_SESSION['verification_id'])) {
    header("Location: register.php");
    exit();
}

if(isset($_POST['verify'])) {
    $token = trim($_POST['token']);
    
    if(empty($token)) {
        $error = "Vui lòng nhập mã xác thực";
    } else {
        // Kiểm tra mã xác thực
        $stmt = $conn->prepare("
            SELECT * FROM email_verification 
            WHERE id = ? AND email = ? AND token = ? AND expires_at > NOW() AND is_used = FALSE
        ");
        $stmt->execute([$_SESSION['verification_id'], $_SESSION['verify_email'], $token]);
        
        if($row = $stmt->fetch()) {
            // Đánh dấu mã đã sử dụng
            $stmt = $conn->prepare("UPDATE email_verification SET is_used = TRUE WHERE id = ?");
            $stmt->execute([$_SESSION['verification_id']]);
            
            // Lưu email đã xác thực vào session
            $_SESSION['verified_email'] = $row['email'];
            
            // Chuyển hướng dựa trên nguồn gốc
            if (isset($_SESSION['verify_source']) && $_SESSION['verify_source'] === 'reset_password') {
                header("Location: complete_resetPW.php");
            } else {
                header("Location: complete_registration.php");
            }
        } else {
            $error = "Mã xác thực không đúng hoặc đã hết hạn";
        }
    }
}

// Gửi lại mã xác thực
if(isset($_POST['resend'])) {
    $token = generateVerificationToken();
    $verification_id = generateUUID();
    $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    $stmt = $conn->prepare("INSERT INTO email_verification (id, email, token, expires_at) VALUES (?, ?, ?, ?)");
    if($stmt->execute([$verification_id, $_SESSION['verify_email'], $token, $expires_at])) {
        if(sendVerificationEmail($_SESSION['verify_email'], $token)) {
            $_SESSION['verification_id'] = $verification_id;
            $success = "Đã gửi lại mã xác thực mới";
        } else {
            $error = "Không thể gửi email xác thực. Vui lòng thử lại sau.";
        }
    } else {
        $error = "Có lỗi xảy ra, vui lòng thử lại";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực email</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Xác thực email</h2>
                <p class="text-muted">Vui lòng nhập mã xác thực đã được gửi đến email: <strong><?php echo htmlspecialchars($_SESSION['verify_email']); ?></strong></p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="token" class="form-label">Mã xác thực (6 số)</label>
                    <input type="text" class="form-control form-control-lg text-center" 
                           name="token" id="token" maxlength="6" pattern="[0-9]{6}"
                           style="letter-spacing: 0.5em; font-size: 1.5em;" required>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" name="verify" class="btn btn-primary">Xác thực</button>
                    <button type="submit" name="resend" class="btn btn-outline-primary" formnovalidate>
                        Gửi lại mã xác thực
                    </button>
                </div>
            </form>
            
            <!-- Quay lại trang đăng ký hoặc reset mật khẩu -->
            <div class="auth-footer">
                <p class="mb-0">
                    <a href="<?php echo (isset($_SESSION['verify_source']) && $_SESSION['verify_source'] === 'reset_password') ? 'otp_resetPW.php' : 'register.php'; ?>" class="text-muted">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
    <script>
    let resendBtn = document.querySelector('button[name="resend"]');
    let countdown = 0;

    function startCountdown(seconds) {
        countdown = seconds;
        resendBtn.disabled = true;
        updateBtnText();
        let interval = setInterval(() => {
            countdown--;
            updateBtnText();
            if (countdown <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                resendBtn.textContent = "Gửi lại mã xác thực";
            }
        }, 1000);
    }

    function updateBtnText() {
        resendBtn.textContent = countdown > 0 ? `Gửi lại mã xác thực (${countdown}s)` : "Gửi lại mã xác thực";
    }

    // Nếu vừa gửi lại mã xác thực hoặc vừa chuyển từ register hoặc otp_resetPW sang, bắt đầu đếm ngược
    <?php if(isset($_POST['resend']) || (isset($_SESSION['verify_source']) && in_array($_SESSION['verify_source'], ['register', 'reset_password']))): ?>
        startCountdown(60);
    <?php endif; ?>
    </script>
</body>
</html> 