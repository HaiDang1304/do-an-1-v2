<?php
include "../config/database.php";

header('Content-Type: application/json');

$response = ['success' => false, 'rooms' => [], 'error' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin']) && isset($_POST['checkout']) && isset($_POST['hotel_id']) && isset($_POST['room_type'])) {
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $hotel_id = (int) $_POST['hotel_id'];
    $room_type = trim($_POST['room_type']);
    $checkinDate = new DateTime($checkin);
    $checkoutDate = new DateTime($checkout);

    if ($checkinDate >= $checkoutDate) {
        $response['error'] = "Ngày trả phòng phải sau ngày nhận phòng.";
    } else {
        // Lấy room_type_id từ tên loại phòng
        $type_sql = "SELECT id FROM room_types WHERE name = ?";
        $type_stmt = mysqli_prepare($conn, $type_sql);
        mysqli_stmt_bind_param($type_stmt, 's', $room_type);
        mysqli_stmt_execute($type_stmt);
        $type_result = mysqli_stmt_get_result($type_stmt);
        $room_type_data = mysqli_fetch_assoc($type_result);
        mysqli_stmt_close($type_stmt);

        if (!$room_type_data) {
            $response['error'] = "Loại phòng không hợp lệ.";
        } else {
            $room_type_id = $room_type_data['id'];

            // Lấy danh sách phòng thuộc loại phòng và khách sạn
            $room_sql = "SELECT room_number 
                         FROM rooms 
                         WHERE hotel_id = ? AND room_type_id = ? AND status = 'available'";
            $room_stmt = mysqli_prepare($conn, $room_sql);
            mysqli_stmt_bind_param($room_stmt, 'ii', $hotel_id, $room_type_id);
            mysqli_stmt_execute($room_stmt);
            $room_result = mysqli_stmt_get_result($room_stmt);
            $all_rooms = array_column($room_result->fetch_all(MYSQLI_ASSOC), 'room_number');
            mysqli_stmt_close($room_stmt);

            if (empty($all_rooms)) {
                $response['error'] = "Không có phòng thuộc loại này.";
            } else {
                // Kiểm tra phòng đã đặt
                $booked_sql = "SELECT room_number 
                               FROM bookings 
                               WHERE hotel_id = ? 
                               AND room_number IN (" . implode(',', array_fill(0, count($all_rooms), '?')) . ")
                               AND (
                                   (checkin <= ? AND checkout > ?) OR
                                   (checkin < ? AND checkout >= ?) OR
                                   (checkin >= ? AND checkout <= ?)
                               )";
                $booked_stmt = mysqli_prepare($conn, $booked_sql);
                $params = array_merge([$hotel_id], $all_rooms, [$checkout, $checkin, $checkout, $checkin, $checkin, $checkout]);
                $types = str_repeat('i', count($all_rooms) + 1) . str_repeat('s', 6);
                mysqli_stmt_bind_param($booked_stmt, $types, ...$params);
                mysqli_stmt_execute($booked_stmt);
                $booked_result = mysqli_stmt_get_result($booked_stmt);
                $booked_rooms = array_column($booked_result->fetch_all(MYSQLI_ASSOC), 'room_number');
                mysqli_stmt_close($booked_stmt);

                $available_rooms = array_diff($all_rooms, $booked_rooms);

                if (empty($available_rooms)) {
                    $response['error'] = "Không còn phòng trống trong khoảng thời gian này.";
                } else {
                    $response['success'] = true;
                    $response['rooms'] = array_values($available_rooms);
                }
            }
        }
    }
} else {
    $response['error'] = "Dữ liệu không hợp lệ.";
}

echo json_encode($response);
mysqli_close($conn);
?>