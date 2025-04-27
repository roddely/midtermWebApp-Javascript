<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once 'vendor/autoload.php';
require_once 'src/bootstrap.php';
require_once 'src/utils/auth_middleware.php';

checkAuth();

$ga = new PHPGangsta_GoogleAuthenticator();
$error = null;
$success = null;

$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user['two_factor_secret']) {
    $secret = $ga->createSecret();
    $stmt = $conn->prepare("UPDATE users SET two_factor_secret = ? WHERE id = ?");
    $stmt->execute([$secret, $_SESSION['user_id']]);
} else {
    $secret = $user['two_factor_secret'];
}

if (isset($_POST['verify'])) {
    $code = trim($_POST['code']);
    if ($ga->verifyCode($secret, $code, 2)) {
        $stmt = $conn->prepare("UPDATE users SET two_factor_enabled = TRUE WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $success = "Thiết lập 2FA thành công!";
        header("refresh:2;url=index.php");
    } else {
        $error = "Mã xác thực không chính xác. Vui lòng thử lại.";
    }
}

$qrCodeUrl = $ga->getQRCodeGoogleUrl('Auth System', $secret);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập 2FA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card p-4">
            <div class="auth-header">
                <h2>Thiết lập xác thực hai lớp</h2>
                <p class="text-muted">Bảo vệ tài khoản của bạn với Google Authenticator</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="mb-4 text-center">
                <div class="qr-label mb-2">1. Tải ứng dụng Google Authenticator trên điện thoại</div>
                <div class="qr-label mb-2">2. Quét mã QR hoặc nhập mã thủ công</div>
                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid mb-3" style="max-width:180px;">
                <p class="text-muted">Mã thủ công: <code><?php echo $secret; ?></code></p>
            </div>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="code" class="form-label">Nhập mã xác thực từ ứng dụng</label>
                    <input type="text" class="form-control" id="code" name="code" required pattern="[0-9]{6}" maxlength="6" placeholder="000000">
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" name="verify" class="btn btn-primary">Xác thực</button>
                    <a href="index.php" class="btn btn-outline-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 