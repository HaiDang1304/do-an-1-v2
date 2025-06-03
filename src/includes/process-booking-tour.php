<?php
session_start();
include "../config/database.php"; // Kết nối cơ sở dữ liệu

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

// Lấy dữ liệu từ POST
$tour_id = isset($_POST['tour_id']) ? (int)$_POST['tour_id'] : 0;
$tour_price = isset($_POST['tour_price']) ? (float)$_POST['tour_price'] : 0;
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$adults = isset($_POST['adults']) ? (int)$_POST['adults'] : 0;
$children = isset($_POST['children']) ? (int)$_POST['children'] : 0;
$departure_date = isset($_POST['departure_date']) ? $_POST['departure_date'] : '';
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// Log dữ liệu gửi từ form để kiểm tra
error_log("Dữ liệu từ form: " . print_r($_POST, true));

// Lấy user_id từ session (nếu người dùng đã đăng nhập)
$user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
if ($user_id === null) {
    error_log("Lỗi: user_id không được tìm thấy trong session.");
} else {
    error_log("user_id được gán: " . $user_id);
}

// Tính tổng giá (giả sử trẻ em có giá bằng 50% người lớn)
$total_price = ($adults * $tour_price) + ($children * $tour_price * 0.5);

// Kiểm tra đầu vào cơ bản
if ($tour_id <= 0) {
    error_log("Lỗi: tour_id không hợp lệ ($tour_id).");
    $error_message = "Lỗi: Vui lòng chọn một tour hợp lệ.";
}

if ($tour_price <= 0) {
    error_log("Lỗi: tour_price không hợp lệ ($tour_price).");
    $error_message = isset($error_message) ? $error_message : "Lỗi: Giá tour không hợp lệ.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Lỗi: Email không hợp lệ ($email).");
    $error_message = isset($error_message) ? $error_message : "Lỗi: Vui lòng nhập email hợp lệ.";
}

if ($adults < 1) {
    error_log("Lỗi: Số người lớn phải ít nhất là 1 ($adults).");
    $error_message = isset($error_message) ? $error_message : "Lỗi: Vui lòng chọn ít nhất 1 người lớn.";
}

if ($children < 0) {
    error_log("Lỗi: Số trẻ em không hợp lệ ($children).");
    $error_message = isset($error_message) ? $error_message : "Lỗi: Số trẻ em không hợp lệ.";
}

if (empty($phone)) {
    error_log("Lỗi: Số điện thoại không được để trống.");
    $error_message = isset($error_message) ? $error_message : "Lỗi: Vui lòng nhập số điện thoại.";
}

// Kiểm tra và xử lý ngày khởi hành từ modal
$departure_date_db = null;
$formatted_departure_date = 'Không xác định';
if (!isset($departure_date) || empty($departure_date)) {
    error_log("Lỗi: Ngày khởi hành không được gửi hoặc để trống: " . var_export($departure_date, true));
    $error_message = isset($error_message) ? $error_message : "Lỗi: Vui lòng chọn ngày khởi hành.";
} else {
    error_log("Ngày khởi hành nhận được từ form: " . $departure_date);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $departure_date)) {
        $departure_date_db = $departure_date;
        $formatted_departure_date = date('d/m/Y', strtotime($departure_date));
        error_log("Ngày khởi hành hợp lệ, lưu vào DB: " . $departure_date_db);
    } else {
        error_log("Lỗi: Định dạng ngày không đúng: " . $departure_date);
        $error_message = isset($error_message) ? $error_message : "Lỗi: Định dạng ngày không đúng (phải là YYYY-MM-DD).";
    }
}

// Kiểm tra tour_id có tồn tại trong bảng tours và lấy price từ tour-detail
$tour_name = 'Không xác định';
$tour_price_from_db = 0;
if (!isset($error_message) && $tour_id > 0) {
    $stmt_check = $conn->prepare("SELECT t.id, t.title, td.price FROM tours t LEFT JOIN `tour-detail` td ON t.id = td.`id-tour` WHERE t.id = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("i", $tour_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $row = $result_check->fetch_assoc();
            $tour_name = $row['title'] ?? 'Không xác định';
            $tour_price_from_db = $row['price'] ? (float)$row['price'] : 0;
            error_log("tour_name được gán: " . $tour_name);
            if ($tour_price != $tour_price_from_db) {
                error_log("Lỗi: tour_price từ form ($tour_price) không khớp với giá trong DB ({$tour_price_from_db}).");
                $error_message = "Lỗi: Giá tour không hợp lệ.";
            }
        } else {
            error_log("Lỗi: tour_id $tour_id không tồn tại trong bảng tours.");
            $error_message = "Lỗi: Tour không tồn tại. Vui lòng chọn một tour hợp lệ.";
        }
        $stmt_check->close();
    } else {
        error_log("Lỗi chuẩn bị truy vấn kiểm tra tour_id: " . $conn->error);
        $error_message = "Lỗi hệ thống. Vui lòng thử lại sau.";
    }
}

// Lưu vào bảng bookings-tour nếu không có lỗi
$booking_id = 0;
if (!isset($error_message)) {
    if ($departure_date_db === null) {
        error_log("Lỗi: departure_date_db là null trước khi INSERT. Giá trị departure_date: " . var_export($departure_date, true));
        $error_message = "Lỗi: Ngày khởi hành không hợp lệ, không thể lưu.";
    } else {
        error_log("Giá trị trước khi INSERT - departure_date_db: " . $departure_date_db . ", tour_name: " . $tour_name . ", user_id: " . $user_id);
        $stmt = $conn->prepare("INSERT INTO `bookings-tour` (tour_id, full_name, email, phone, adults, children, departure_date, total_price, notes, tour_name, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isssiisdssi", $tour_id, $full_name, $email, $phone, $adults, $children, $departure_date_db, $total_price, $notes, $tour_name, $user_id);
            if ($stmt->execute()) {
                $booking_id = $stmt->insert_id;
                error_log("Booking thành công, ID: " . $booking_id);
            } else {
                error_log("Lỗi lưu booking: " . $stmt->error);
                $error_message = "Lỗi khi lưu thông tin đặt tour. Chi tiết: " . $stmt->error;
            }
            $stmt->close();
        } else {
            error_log("Lỗi chuẩn bị truy vấn INSERT: " . $conn->error);
            $error_message = "Lỗi hệ thống. Chi tiết: " . $conn->error;
        }
    }
}

// Gửi email xác nhận
$emailSent = false;
if ($booking_id > 0) {
    try {
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
        $mail->Subject = 'Xác nhận đặt tour tại TD Touris';
        $mail->Body = "
            <h2>Xác nhận đặt tour thành công</h2>
            <p>Chào $full_name,</p>
            <p>Cảm ơn bạn đã đặt tour tại TD Touris. Dưới đây là thông tin đặt tour của bạn:</p>
            <ul>
                <li><strong>Mã đặt tour:</strong> $booking_id</li>
                <li><strong>Tên tour:</strong> " . htmlspecialchars($tour_name) . "</li>
                <li><strong>Ngày khởi hành:</strong> $formatted_departure_date</li>
                <li><strong>Số người lớn:</strong> $adults</li>
                <li><strong>Số trẻ em:</strong> $children</li>
                <li><strong>Tổng giá:</strong> " . number_format($total_price, 0, ',', '.') . " VND</li>
            </ul>
            <p>Ghi chú: " . nl2br(htmlspecialchars($notes)) . "</p>
            <p>Vui lòng liên hệ chúng tôi nếu bạn cần hỗ trợ thêm!</p>
            <p>Trân trọng,<br>TD Touris</p>
        ";
        $mail->send();
        $emailSent = true;
    } catch (Exception $e) {
        error_log("Email không gửi được: {$mail->ErrorInfo}");
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Tour Thành Công</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .success-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .success-card {
            text-align: left;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <?php if (isset($error_message)): ?>
                <h2 class="text-danger">Lỗi đặt tour</h2>
                <p><?= htmlspecialchars($error_message) ?></p>
                <a href="../views/booking-form.php" class="btn btn-primary mt-3">Quay lại form đặt tour</a>
            <?php else: ?>
                <h2 class="text-success">Đặt tour thành công!</h2>
                <p>Mã đặt tour của bạn: <?= $booking_id ?></p>
                <p>Chúng tôi đã gửi email xác nhận đến: <?= htmlspecialchars($email) ?></p>
                <?php if (!$emailSent): ?>
                    <p class="text-warning">⚠ Email xác nhận không gửi được. Vui lòng kiểm tra lại hoặc liên hệ hỗ trợ.</p>
                <?php endif; ?>
                <p><strong>Tên tour:</strong> <?= htmlspecialchars($tour_name) ?></p>
                <p><strong>Ngày khởi hành:</strong> <?= $formatted_departure_date ?></p>
                <p><strong>Số người lớn:</strong> <?= $adults ?></p>
                <p><strong>Số trẻ em:</strong> <?= $children ?></p>
                <p><strong>Tổng giá:</strong> <?= number_format($total_price, 0, ',', '.') ?> VND</p>
                <?php if (!empty($notes)): ?>
                    <p><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($notes)) ?></p>
                <?php endif; ?>
                <a href="../views/index.php" class="btn btn-primary mt-3">Quay về trang chủ</a>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>