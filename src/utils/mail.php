<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Sử dụng đường dẫn tuyệt đối
$rootPath = dirname(dirname(__DIR__)); // Lấy thư mục gốc của project
require $rootPath . '/vendor/Exception.php';
require $rootPath . '/vendor/PHPMailer.php';
require $rootPath . '/vendor/SMTP.php';

// Kiểm tra trước khi định nghĩa hằng số
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USERNAME')) define('SMTP_USERNAME', 'honhathao2805@gmail.com');
if (!defined('SMTP_PASSWORD')) define('SMTP_PASSWORD', 'uved poqa zrzd wxxj');
if (!defined('SMTP_FROM')) define('SMTP_FROM', 'honhathao2805@gmail.com');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'Auth System');

function sendVerificationEmail($to, $verificationToken) {
    try {
        $mail = new PHPMailer(true);
        
        // Tắt debug
        $mail->SMTPDebug = SMTP::DEBUG_OFF;

        // Cấu hình cơ bản
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        
        // Cấu hình xác thực
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        
        // Cấu hình SMTP nâng cao
        $mail->SMTPKeepAlive = true;
        $mail->Timeout = 60;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->Hostname = gethostname();
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Xác nhận đăng ký tài khoản';
        $mail->Body = '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2>Xác nhận đăng ký tài khoản</h2>
            <p>Cảm ơn bạn đã đăng ký tài khoản. Để hoàn tất quá trình đăng ký, vui lòng sử dụng mã xác nhận sau:</p>
            <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0;">
                ' . $verificationToken . '
            </div>
            <p>Mã xác nhận này sẽ hết hạn sau 24 giờ.</p>
            <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
            <p>Trân trọng,<br>' . SMTP_FROM_NAME . '</p>
        </div>';

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
} 