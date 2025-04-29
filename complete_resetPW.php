<?php
session_start();
require_once 'src/utils/db_connect.php';
require_once 'src/utils/functions.php';

// Hàm lấy địa chỉ IP (tái sử dụng từ login.php)
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

if (!isset($_SESSION['verified_email'])) {
    header("Location: register.php");
    exit();
}

if (isset($_POST['resetPW'])) {
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($password) || empty($confirm_password)) {
        $error = "Vui lòng điền đầy đủ thông tin";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->execute([$password_hash, $_SESSION['verified_email']]);

            // Xóa bản ghi IP trong bảng login_attempts_ip
            $ip = getUserIP();
            $stmt = $conn->prepare("DELETE FROM login_attempts_ip WHERE ip = ?");
            $stmt->execute([$ip]);

            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_SESSION['verified_email']]);
            $user = $stmt->fetch();

            if ($user) {
                $stmt = $conn->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                if (!$stmt->fetch()) {
                    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
                    $stmt->execute([$user['id']]);
                }
            }

            $conn->commit();

            unset($_SESSION['verify_email']);
            unset($_SESSION['verification_id']);
            unset($_SESSION['verified_email']);

            $_SESSION['success'] = "Đổi mật khẩu thành công! Vui lòng đăng nhập";
            header("Location: login.php");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Có lỗi xảy ra, vui lòng thử lại";
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Cập nhật mật khẩu</h2>
                <p class="text-muted">Email đã xác thực: <strong><?php echo htmlspecialchars($_SESSION['verified_email']); ?></strong></p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu mới</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="password"
                            pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                            title="Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ và số" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ và số</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="confirm_password"
                            id="confirm_password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                <button type="submit" name="resetPW" class="btn btn-primary">
                    Cập nhật mật khẩu
                </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
    <script>
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('mousedown', function() {
            let input = this.parentElement.querySelector('input[type="password"], input[type="text"]');
            input.type = 'text';
        });
        btn.addEventListener('mouseup', function() {
            let input = this.parentElement.querySelector('input[type="text"]');
            if(input) input.type = 'password';
        });
        btn.addEventListener('mouseleave', function() {
            let input = this.parentElement.querySelector('input[type="text"]');
            if(input) input.type = 'password';
        });
    });
    </script>
</body>

</html>