<?php
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVoucherEmail($email, $user_id, $conn) {
    $response = ['success' => false, 'message' => ''];

    if (!$user_id) {
        $response['message'] = '<p style="color: red;">Vui lòng đăng nhập để nhận voucher!</p>';
        return $response;
    }

    try {
        // Kiểm tra user_id có tồn tại không
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $response['message'] = '<p style="color: red;">Người dùng không tồn tại!</p>';
            return $response;
        }

        // Kiểm tra email đã đăng ký voucher chưa
        $stmt = $conn->prepare("SELECT id FROM subscribers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $response['message'] = '<p style="color: red;">Email này đã đăng ký nhận voucher!</p>';
            return $response;
        }

        // Tạo mã voucher
        $voucher_code = strtoupper(bin2hex(random_bytes(4)));

        // Lưu vào bảng subscribers
        $stmt = $conn->prepare("INSERT INTO subscribers (email, user_id, subscribed_at, voucher_code) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("sis", $email, $user_id, $voucher_code);
        $stmt->execute();

        // Gửi email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->setFrom(SMTP_USERNAME, 'TD Touris');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Voucher Khuyến Mãi Từ TD Touris';
        $mail->Body = "
            <h2>Chào bạn,</h2>
            <p>Cảm ơn bạn đã đăng ký nhận thông báo từ TD Touris!</p>
            <p>Mã voucher của bạn:</p>
            <h3 style='color: #007bff;'>$voucher_code</h3>
            <p>Sử dụng mã này để được giảm giá 10% cho lần đặt tour tiếp theo (hạn 30 ngày).</p>
            <p><a href='http://localhost/do-an-1/src/views/index.php'>Khám phá tour tại TD Touris</a></p>
            <p>Trân trọng,<br>TD Touris</p>
        ";
        $mail->AltBody = "Mã voucher của bạn là: $voucher_code";

        $mail->send();

        $response['success'] = true;
        $response['message'] = '<p style="color: red;">Voucher đã được gửi tới email của bạn!</p>';

    } catch (Exception $e) {
        $response['message'] = '<p style="color: red;">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }

    return $response;
}
?>
