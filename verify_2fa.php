<?php
require_once 'vendor/autoload.php';
session_start();
require_once 'src/utils/db_connect.php';
require_once 'src/utils/jwt_auth.php';

$ga = new PHPGangsta_GoogleAuthenticator();
$error = null;

if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['two_factor_secret'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['verify'])) {
    $code = trim($_POST['code']);
    if ($ga->verifyCode($_SESSION['two_factor_secret'], $code, 2)) {
        $jwt = create_jwt($_SESSION['temp_user_id'], $_SESSION['temp_user_name']);
        setcookie('auth_token', $jwt, time() + JWT_EXPIRE, '/', '', false, true); // HttpOnly

        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_user_name']);
        unset($_SESSION['two_factor_secret']);

        header("Location: index.php");
        exit();
    } else {
        $error = "Mã xác thực không chính xác. Vui lòng thử lại.";
    }
}

function generateUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

function generateSessionToken()
{
    return bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực hai lớp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Xác thực hai lớp</h2>
                <p class="text-muted">Vui lòng nhập mã xác thực từ ứng dụng Google Authenticator</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="code" class="form-label">Mã xác thực</label>
                    <input type="text" class="form-control" id="code" name="code" required 
                        pattern="[0-9]{6}" maxlength="6" placeholder="000000">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="verify" class="btn btn-primary">Xác thực</button>
                    <a href="login.php" class="btn btn-outline-secondary">Quay lại đăng nhập</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 