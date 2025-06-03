<?php
include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../views/admin.php?type=tour-management&action=update&error=1&message=" . urlencode('Phương thức không hợp lệ'));
  exit;
}

$id = $_POST['id'] ?? 0;
$title = $_POST['title'] ?? '';
$image = $_POST['image'] ?? null;
$tag = $_POST['tag'] ?? '';
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$price = $_POST['price'] ?? 0;
$description = $_POST['description'] ?? '';
$gallery = $_POST['gallery'] ?? '';
$tour_program = $_POST['tour-program'] ?? '';
$note = $_POST['note'] ?? '';

if (!$id || !is_numeric($id) || empty($title) || empty($price)) {
  header("Location: ../views/admin.php?type=tour-management&action=update&error=1&message=" . urlencode('ID, tiêu đề hoặc giá không hợp lệ'));
  exit;
}

// Kiểm tra và xử lý dữ liệu JSON
$tag = json_decode($tag, true);
$gallery = json_decode($gallery, true);
$tour_program = json_decode($tour_program, true);
$note = json_decode($note, true);

if (!is_array($tag) || !is_array($gallery) || !is_array($tour_program) || !is_array($note)) {
  header("Location: ../views/admin.php?type=tour-management&action=update&error=1&message=" . urlencode('Dữ liệu JSON không hợp lệ'));
  exit;
}

$tag = json_encode(array_filter($tag, 'strlen'));
$gallery = json_encode(array_filter($gallery, 'strlen'));
$tour_program = json_encode($tour_program);
$note = json_encode($note);

$conn->begin_transaction();
try {
  // Cập nhật bảng tours
  $stmt = $conn->prepare("UPDATE tours SET title = ?, image = ?, tag = ?, is_featured = ? WHERE id = ?");
  $stmt->bind_param("sssii", $title, $image, $tag, $is_featured, $id);
  $stmt->execute();

  // Kiểm tra xem bản ghi trong tour-detail có tồn tại không
  $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM `tour-detail` WHERE `id-tour` = ?");
  $check_stmt->bind_param("i", $id);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();
  $row = $check_result->fetch_assoc();
  $exists = $row['count'] > 0;

  // Cập nhật hoặc thêm mới vào tour-detail
  if ($exists) {
    $update_stmt = $conn->prepare("UPDATE `tour-detail` 
      SET price = ?, description = ?, gallery = ?, `tour-program` = ?, note = ? 
      WHERE `id-tour` = ?");
    $update_stmt->bind_param("dssssi", $price, $description, $gallery, $tour_program, $note, $id);
    $update_stmt->execute();
  } else {
    $insert_stmt = $conn->prepare("INSERT INTO `tour-detail` (`id-tour`, price, description, gallery, `tour-program`, note) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("idssss", $id, $price, $description, $gallery, $tour_program, $note);
    $insert_stmt->execute();
  }

  $conn->commit();
  header("Location: ../views/admin.php?type=tour-management&action=update&success=1");
} catch (Exception $e) {
  $conn->rollback();
  error_log("Lỗi khi cập nhật tour: " . $e->getMessage());
  header("Location: ../views/admin.php?type=tour-management&action=update&error=1&message=" . urlencode($e->getMessage()));
} finally {
  $conn->close();
}
?>