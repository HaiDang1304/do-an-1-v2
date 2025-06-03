<?php
// Ngăn xuất bất kỳ đầu ra nào trước khi gửi JSON
ob_start();
header('Content-Type: application/json; charset=UTF-8');
include_once '../config/database.php';

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Không thể kết nối tới cơ sở dữ liệu: ' . $conn->connect_error]);
    ob_end_flush();
    exit;
}

// Query SQL để lấy dữ liệu từ bảng tours và tour-detail
$sql = "SELECT t.id, t.title, t.image, t.tag AS tags, td.price, td.rating, td.review 
        FROM tours t 
        LEFT JOIN `tour-detail` td ON t.id = td.`id-tour` 
        LIMIT 4";
$result = $conn->query($sql);

$tours = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['tags'] = json_decode($row['tags'], true) ?? [];
        $row['rating'] = $row['rating'] ?? 0;
        $row['review'] = $row['review'] ?? 0;
        $tours[] = $row;
    }
} else {
    // Nếu không có dữ liệu, trả về mảng rỗng
    $tours = [];
}

ob_end_clean(); // Xóa mọi đầu ra không mong muốn
echo json_encode($tours);
$conn->close();
?>