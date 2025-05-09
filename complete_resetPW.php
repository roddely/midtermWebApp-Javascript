<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Kiểm tra xem email đã được xác thực chưa
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
        // Tạo mật khẩu mới
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $conn->beginTransaction();

            // Cập nhật mật khẩu mớimới
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->execute([$password_hash, $_SESSION['verified_email']]);

            // Kiểm tra và tạo profile nếu chưa có
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$_SESSION['verified_email']]);
            $user = $stmt->fetch();

            if ($user) {
                $stmt = $conn->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
                $stmt->execute([$user['id']]);
                if (!$stmt->fetch()) {
                    // Tạo profile mặc định
                    $stmt = $conn->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
                    $stmt->execute([$user['id']]);
                }
            }

            $conn->commit();

            // Xóa các session liên quan đến xác thực
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <input type="password" class="form-control" name="password" id="password"
                        pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$"
                        title="Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ và số" required>
                    <div class="form-text">Mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ và số</div>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                    <input type="password" class="form-control" name="confirm_password"
                        id="confirm_password" required>
                </div>

                <div class="d-grid gap-2">
                <button type="submit" name="resetPW" class="btn btn-primary">
                    Cập nhật mật khẩu
                </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>

</html>