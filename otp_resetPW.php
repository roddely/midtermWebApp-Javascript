<?php
session_start();
require_once 'src/utils/db_connect.php';
require_once 'src/utils/mail_service.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

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

function generateVerificationToken()
{
    return str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

if (isset($_POST['verify_email'])) {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Vui lòng nhập email";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() == 0) {
            $error = "Email không tồn tại";
        } else {
            $verification_id = generateUUID();
            $token = generateVerificationToken();
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $conn->prepare("INSERT INTO email_verification (id, email, token, expires_at) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$verification_id, $email, $token, $expires_at])) {
                if (sendVerificationEmail($email, $token)) {
                    $_SESSION['verify_source'] = 'reset_password';
                    $_SESSION['verify_email'] = $email;
                    $_SESSION['verification_id'] = $verification_id;
                    header("Location: verify.php");
                    exit();
                } else {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="auth-container">
        <div class="auth-card">

            <?php if (isset($error)): ?>
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

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-code.js" crossorigin="anonymous"></script>
</body>

</html>