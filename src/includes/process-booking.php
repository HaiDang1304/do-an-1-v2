<?php
session_start();
include '../config/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');
$response = ['success' => false, 'error' => '', 'warning' => '', 'message' => '', 'booking_id' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    if (!$user_id) {
        $response['error'] = "Vui lòng đăng nhập để đặt phòng.";
        echo json_encode($response);
        exit();
    }

    $hotel_id = isset($_POST['hotel_id']) ? (int) $_POST['hotel_id'] : 0;
    $hotel_name = isset($_POST['hotel_name']) ? trim($_POST['hotel_name']) : '';
    $price = isset($_POST['price']) ? (float) $_POST['price'] : 0;
    $checkin = isset($_POST['checkin']) ? $_POST['checkin'] : '';
    $checkout = isset($_POST['checkout']) ? $_POST['checkout'] : '';
    $guests = isset($_POST['guests']) ? (int) $_POST['guests'] : 0;
    $room_type = isset($_POST['room_type']) ? trim($_POST['room_type']) : '';
    $room_number = isset($_POST['room_number']) ? (int) $_POST['room_number'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $staff = null;

    if (!$hotel_id || !$hotel_name || !$price || !$checkin || !$checkout || !$guests || !$room_type || !$room_number || !$name || !$email || !$phone) {
        $response['error'] = "Vui lòng điền đầy đủ thông tin.";
        echo json_encode($response);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = "Email không hợp lệ.";
        echo json_encode($response);
        exit();
    }

    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);
    if ($checkinDate >= $checkoutDate) {
        $response['error'] = "Ngày trả phòng phải sau ngày nhận phòng.";
        echo json_encode($response);
        exit();
    }

    $interval = $checkinDate->diff($checkoutDate);
    $numberOfDays = $interval->days;
    $total_price = $price * $numberOfDays;

    $type_sql = "SELECT id FROM room_types WHERE name = ?";
    $type_stmt = mysqli_prepare($conn, $type_sql);
    mysqli_stmt_bind_param($type_stmt, 's', $room_type);
    mysqli_stmt_execute($type_stmt);
    $type_result = mysqli_stmt_get_result($type_stmt);
    $room_type_data = mysqli_fetch_assoc($type_result);
    mysqli_stmt_close($type_stmt);

    if (!$room_type_data) {
        $response['error'] = "Loại phòng không hợp lệ.";
        echo json_encode($response);
        exit();
    }
    $room_type_id = $room_type_data['id'];

    $room_check_sql = "SELECT room_number FROM rooms WHERE hotel_id = ? AND room_number = ? AND room_type_id = ?";
    $room_check_stmt = mysqli_prepare($conn, $room_check_sql);
    mysqli_stmt_bind_param($room_check_stmt, 'iii', $hotel_id, $room_number, $room_type_id);
    mysqli_stmt_execute($room_check_stmt);
    $room_check_result = mysqli_stmt_get_result($room_check_stmt);
    $room_exists = mysqli_fetch_assoc($room_check_result);
    mysqli_stmt_close($room_check_stmt);

    if (!$room_exists) {
        $response['error'] = "Phòng không hợp lệ hoặc không thuộc loại phòng đã chọn.";
        echo json_encode($response);
        exit();
    }

    $booked_sql = "SELECT room_number 
                   FROM bookings 
                   WHERE hotel_id = ? 
                   AND room_number = ?
                   AND (
                       (checkin <= ? AND checkout > ?) OR
                       (checkin < ? AND checkout >= ?) OR
                       (checkin >= ? AND checkout <= ?)
                   )";
    $booked_stmt = mysqli_prepare($conn, $booked_sql);
    mysqli_stmt_bind_param($booked_stmt, 'iissssss', $hotel_id, $room_number, $checkout, $checkin, $checkout, $checkin, $checkin, $checkout);
    mysqli_stmt_execute($booked_stmt);
    $booked_result = mysqli_stmt_get_result($booked_stmt);
    $booked_rooms = $booked_result->fetch_all(MYSQLI_ASSOC);
    mysqli_stmt_close($booked_stmt);

    if (!empty($booked_rooms)) {
        $response['error'] = "Phòng $room_number đã được đặt trong khoảng thời gian này.";
        echo json_encode($response);
        exit();
    }

    $sql = "INSERT INTO bookings (user_id, hotel_id, hotel_name, price, checkin, checkout, guests, room_type_id, room_number, name, email, phone, notes, total_price, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisissiiissssd", $user_id, $hotel_id, $hotel_name, $price, $checkin, $checkout, $guests, $room_type_id, $room_number, $name, $email, $phone, $notes, $total_price);

    if (mysqli_stmt_execute($stmt)) {
        $booking_id = mysqli_stmt_insert_id($stmt);

        $emailSent = true;
        $mail = new PHPMailer(true);
        try {
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
            $mail->Subject = 'Xác nhận đặt phòng tại ' . $hotel_name;
            $mail->Body = "
                <h2>Xác nhận đặt phòng thành công</h2>
                <p>Chào $name,</p>
                <p>Cảm ơn bạn đã đặt phòng tại TD Touris. Dưới đây là thông tin đặt phòng của bạn:</p>
                <ul>
                    <li><strong>Mã đặt phòng:</strong> $booking_id</li>
                    <li><strong>Khách sạn:</strong> $hotel_name</li>
                    <li><strong>Phòng số:</strong> $room_number</li>
                    <li><strong>Ngày đi:</strong> " . date('d/m/Y', strtotime($checkin)) . "</li>
                    <li><strong>Ngày về:</strong> " . date('d/m/Y', strtotime($checkout)) . "</li>
                    <li><strong>Số người:</strong> $guests</li>
                    <li><strong>Loại phòng:</strong> $room_type</li>
                    <li><strong>Giá mỗi ngày:</strong> " . number_format($price, 0, ',', '.') . " VND</li>
                    <li><strong>Tổng giá:</strong> " . number_format($total_price, 0, ',', '.') . " VND</li>
                </ul>
                <p>Ghi chú: " . ($notes ? $notes : "Không có ghi chú.") . "</p>
                <p>Vui lòng liên hệ chúng tôi nếu bạn cần hỗ trợ thêm!</p>
                <p>Trân trọng,<br>TD Touris</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email không gửi được: {$mail->ErrorInfo}");
            $emailSent = false;
        }

        $response['success'] = true;
        $response['booking_id'] = $booking_id;
        if ($emailSent) {
            $response['message'] = "Đặt phòng thành công! Mã đặt phòng: $booking_id. Vui lòng kiểm tra email để xem chi tiết.";
        } else {
            $response['message'] = "Đặt phòng thành công! Mã đặt phòng: $booking_id. Tuy nhiên, không thể gửi email xác nhận.";
            $response['warning'] = "Không thể gửi email xác nhận.";
        }
    } else {
        $response['error'] = "Đặt phòng thất bại: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
} else {
    $response['error'] = "Yêu cầu không hợp lệ.";
}

echo json_encode($response);
mysqli_close($conn);
exit();
?>