<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Sử dụng đường dẫn tuyệt đối
$rootPath = dirname(__DIR__); // Lấy thư mục gốc của project
require $rootPath . '/vendor/Exception.php';
require $rootPath . '/vendor/PHPMailer.php';
require $rootPath . '/vendor/SMTP.php';

// Cấu hình email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'honhathao2805@gmail.com');
define('SMTP_PASSWORD', 'uved poqa zrzd wxxj');
define('SMTP_FROM', 'honhathao2805@gmail.com');
define('SMTP_FROM_NAME', 'Auth System');
define('SMTP_DEBUG', false); // Thêm flag để kiểm soát việc hiển thị debug

function sendVerificationEmail($to, $verificationToken) {
    try {
        // Chỉ hiển thị thông tin debug nếu SMTP_DEBUG = true
        // if (SMTP_DEBUG) {
        //     echo "<div style='background-color: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
        //     echo "<h4>Thông tin gửi email:</h4>";
        //     echo "<p><strong>Email người gửi:</strong> " . SMTP_FROM . "</p>";
        //     echo "<p><strong>Tên người gửi:</strong> " . SMTP_FROM_NAME . "</p>";
        //     echo "<p><strong>Email người nhận:</strong> " . $to . "</p>";
        //     echo "<p><strong>Mã xác thực:</strong> " . $verificationToken . "</p>";
        //     echo "</div>";
        // }

        $mail = new PHPMailer(true);
        
        // Cấu hình debug
        $mail->SMTPDebug = SMTP_DEBUG ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
        if (SMTP_DEBUG) {
            $mail->Debugoutput = function($str, $level) {
                echo "<div style='background-color: #e9ecef; padding: 5px; margin: 2px 0; font-family: monospace;'>";
                echo htmlspecialchars($str);
                echo "</div>";
            };
        }

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

        $result = $mail->send();
        
        // Chỉ hiển thị thông báo thành công nếu SMTP_DEBUG = true
        if ($result && SMTP_DEBUG) {
            echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "Email đã được gửi thành công đến: " . $to;
            echo "</div>";
        }
        return $result;
    } catch (Exception $e) {
        // Luôn hiển thị lỗi để dễ debug
        if (SMTP_DEBUG) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<h4>Lỗi khi gửi email:</h4>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "<p>Chi tiết lỗi: " . $mail->ErrorInfo . "</p>";
            echo "</div>";
        }
        return false;
    }
} 