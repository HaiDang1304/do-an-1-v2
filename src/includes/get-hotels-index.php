<?php
header('Content-Type: application/json; charset=UTF-8');
include_once '../config/database.php';

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) {
    echo json_encode(['error' => 'Không thể kết nối tới cơ sở dữ liệu: ' . $conn->connect_error]);
    exit;
}

$sql = "SELECT * FROM hotels LIMIT 4";
$result = $conn->query($sql);

$hotels = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['tags'] = json_decode($row['tags'], true);
        $hotels[] = $row;
    }
}

echo json_encode($hotels);
$conn->close();