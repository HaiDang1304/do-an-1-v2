<?php
header('Content-Type: application/json');
include_once '../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID tour không hợp lệ']);
    exit;
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT t.id, t.title, t.image, t.tag, t.is_featured, td.price, td.description, td.gallery, td.`tour-program`, td.note
                        FROM tours t
                        LEFT JOIN `tour-detail` td ON t.id = td.`id-tour`
                        WHERE t.id = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Lỗi thực thi truy vấn: ' . $stmt->error]);
    exit;
}
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $response = [
        'id' => $row['id'],
        'title' => $row['title'],
        'image' => $row['image'],
        'tag' => json_decode($row['tag'], true) ?? explode(',', $row['tag']),
        'is_featured' => $row['is_featured'],
        'price' => $row['price'],
        'description' => $row['description'],
        'gallery' => json_decode($row['gallery'], true) ?? [],
        'tour_program' => json_decode($row['tour-program'], true) ?? [],
        'note' => json_decode($row['note'], true) ?? ['gia_bao_gom' => [], 'gia_khong_bao_gom' => []]
    ];
    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Tour không tồn tại hoặc không có chi tiết']);
}

$stmt->close();
$conn->close();
?>