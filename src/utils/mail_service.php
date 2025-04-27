<?php
require_once __DIR__ . '/../config/mail_config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$rootPath = dirname(dirname(__DIR__)); 
require $rootPath . '/vendor/phpmailer/phpmailer/src/Exception.php';
require $rootPath . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require $rootPath . '/vendor/phpmailer/phpmailer/src/SMTP.php';

function sendVerificationEmail($to, $verificationToken, &$errorMsg = null) {
    try {
        $mail = new PHPMailer(true);
        
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->Port = SMTP_PORT;
        
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
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

        if (!$mail->send()) {
            $errorMsg = $mail->ErrorInfo;
            return false;
        }
        return true;
    } catch (Exception $e) {
        $errorMsg = $mail->ErrorInfo ?? $e->getMessage();
        return false;
    }
} 