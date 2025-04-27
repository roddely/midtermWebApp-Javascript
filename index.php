<?php
require_once 'src/bootstrap.php';
require_once 'src/utils/jwt_auth.php';

$jwt = require_jwt_auth();

$stmt = $conn->prepare("SELECT two_factor_enabled, two_factor_secret FROM users WHERE id = ?");
$stmt->execute([$jwt->user_id]);
$user_2fa = $stmt->fetch(PDO::FETCH_ASSOC);

if (isset($_POST['toggle_2fa'])) {
    if ($user_2fa['two_factor_enabled']) {
        $stmt = $conn->prepare("UPDATE users SET two_factor_enabled = FALSE, two_factor_secret = NULL WHERE id = ?");
        $stmt->execute([$jwt->user_id]);
        header("Location: index.php");
        exit();
    } else {
        header("Location: setup_2fa.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Auth System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Đăng xuất</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="main-card p-4 mt-5">
            <div class="avatar mb-2">
                <i class="fas fa-user-circle"></i>
            </div>
            <h2 class="text-center mb-2">Xin chào!</h2>
            <p class="desc text-center mb-4">Bạn đã đăng nhập thành công.</p>

            <div class="info-row">
                <div class="setting-label mb-2">
                    <i class="fas fa-shield-alt text-primary"></i> Bảo mật hai lớp (2FA)
                </div>
                <form method="POST" action="" class="d-flex align-items-center gap-2">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="2faToggle"
                            <?php echo $user_2fa['two_factor_enabled'] ? 'checked' : ''; ?> disabled>
                        <label class="form-check-label" for="2faToggle">
                            <?php echo $user_2fa['two_factor_enabled'] ? 'Đã bật' : 'Đã tắt'; ?>
                        </label>
                    </div>
                    <button type="submit" name="toggle_2fa" class="btn btn-sm <?php echo $user_2fa['two_factor_enabled'] ? 'btn-danger' : 'btn-success'; ?>">
                        <?php echo $user_2fa['two_factor_enabled'] ? 'Tắt 2FA' : 'Bật 2FA'; ?>
                    </button>
                </form>
                <?php if ($user_2fa['two_factor_enabled']): ?>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle"></i> Bạn sẽ cần xác thực qua Google Authenticator khi đăng nhập.
                    </small>
                <?php else: ?>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle"></i> Bật 2FA để tăng bảo mật tài khoản.
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 