<?php
header('Content-Type: application/json');
include_once '../config/database.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Phương thức không hợp lệ';
    echo json_encode($response);
    exit;
}

$id = $_POST['id'] ?? 0;
if (!$id || !is_numeric($id)) {
    $response['message'] = 'ID khách sạn không hợp lệ';
    echo json_encode($response);
    exit;
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("DELETE FROM hotels_detail WHERE id_hotels = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM hotels WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->commit();
    $response['success'] = true;
} catch (Exception $e) {
    $conn->rollback();
    error_log("Lỗi khi xóa khách sạn ID $id: " . $e->getMessage());
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
}

echo json_encode($response);
?>