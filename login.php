<?php
require_once 'vendor/autoload.php';
require_once 'src/utils/db_connect.php';
require_once 'src/utils/jwt_auth.php';

$ga = new PHPGangsta_GoogleAuthenticator();

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

function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

function getLockDuration($attempts) {
    if ($attempts <= 3) return 0;
    if ($attempts <= 6) return 15 * 60;
    if ($attempts <= 9) return 30 * 60;
    return 60 * 60;
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $ip = getUserIP();
    $stmt = $conn->prepare("SELECT attempts, lock_until FROM login_attempts_ip WHERE ip = ?");
    $stmt->execute([$ip]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['lock_until'] && strtotime($row['lock_until']) > time()) {
        $wait = strtotime($row['lock_until']) - time();
        $minutes = ceil($wait / 60);
        $error = "Vui lòng thử lại sau $minutes phút.";
    } else {
        if (empty($email) || empty($password)) {
            $error = "Vui lòng điền đầy đủ thông tin";
        } else {
            $stmt = $conn->prepare("SELECT id, name, password_hash, two_factor_enabled, two_factor_secret FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $stmt = $conn->prepare("DELETE FROM login_attempts_ip WHERE ip = ?");
                $stmt->execute([$ip]);

                if ($user['two_factor_enabled']) {
                    session_start();
                    $_SESSION['temp_user_id'] = $user['id'];
                    $_SESSION['temp_user_name'] = $user['name'];
                    $_SESSION['two_factor_secret'] = $user['two_factor_secret'];
                    header("Location: verify_2fa.php");
                    exit();
                } else {
                    $jwt = create_jwt($user['id'], $user['name']);
                    setcookie('auth_token', $jwt, time() + JWT_EXPIRE, '/', '', false, true);
                    header("Location: index.php");
                    exit();
                }
            } else {
                $error = "Email hoặc mật khẩu không chính xác";
                $stmt = $conn->prepare("SELECT attempts FROM login_attempts_ip WHERE ip = ?");
                $stmt->execute([$ip]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row) {
                    $attempts = $row['attempts'] + 1;
                    $lockDuration = getLockDuration($attempts);
                    $lockUntil = $lockDuration > 0 ? date('Y-m-d H:i:s', time() + $lockDuration) : null;
                    $stmt = $conn->prepare("UPDATE login_attempts_ip SET attempts = ?, last_attempt = NOW(), lock_until = ? WHERE ip = ?");
                    $stmt->execute([$attempts, $lockUntil, $ip]);
                } else {
                    $stmt = $conn->prepare("INSERT INTO login_attempts_ip (ip, attempts, last_attempt, lock_until) VALUES (?, 1, NOW(), NULL)");
                    $stmt->execute([$ip]);
                }
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
    <title>Đăng nhập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Đăng nhập</h2>
                <p class="text-muted">Vui lòng đăng nhập để tiếp tục</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" id="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="password" id="password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="text-end mt-2">
                        <a href="otp_resetPW.php" class="text-muted">Quên mật khẩu?</a>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" name="login" class="btn btn-primary">Đăng nhập</button>
                </div>
            </form>

            <div class="auth-footer">
                <p class="mb-0">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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