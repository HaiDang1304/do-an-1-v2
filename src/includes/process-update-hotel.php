<?php
include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/admin.php?type=hotel-management&action=update&error=1&message=" . urlencode('Phương thức không hợp lệ'));
    exit;
}

$id = $_POST['id'] ?? 0;
$name = $_POST['name'] ?? '';
$image = $_POST['image'] ?? null;
$tags = $_POST['tags'] ?? '';
$price = $_POST['price'] ?? 0;
$location = $_POST['location'] ?? '';
$rating = $_POST['rating'] ?? 0;
$reviews = $_POST['reviews'] ?? 0;
$start = $_POST['start'] ?? 1;
$youtube_id = $_POST['youtube_id'] ?? '';
$title_ytb = $_POST['title_ytb'] ?? '';
$address = $_POST['address'] ?? '';
$map_embed = $_POST['map_embed'] ?? '';
$description = $_POST['description'] ?? '';
$gallery = $_POST['gallery'] ?? '';
$experience = $_POST['experience'] ?? '';
$combo_details = $_POST['combo_details'] ?? '';

if (!$id || !is_numeric($id) || empty($name) || empty($price) || empty($location) || empty($start)) {
    header("Location: ../views/admin.php?type=hotel-management&action=update&error=1&message=" . urlencode('ID, tên khách sạn, giá, vị trí hoặc số sao không hợp lệ'));
    exit;
}

// Kiểm tra dữ liệu JSON
$tags = json_decode($tags, true);
$gallery = json_decode($gallery, true);
$experience = json_decode($experience, true);
$combo_details = json_decode($combo_details, true);

if (!is_array($tags) || !is_array($gallery) || !is_array($experience) || !is_array($combo_details)) {
    header("Location: ../views/admin.php?type=hotel-management&action=update&error=1&message=" . urlencode('Dữ liệu JSON không hợp lệ'));
    exit;
}

// Mã hóa lại JSON
$tags = json_encode(array_filter($tags, 'strlen'));
$gallery = json_encode($gallery);
$experience = json_encode($experience);
$combo_details = json_encode($combo_details);

$conn->begin_transaction();
try {
    // Cập nhật bảng hotels
    $stmt = $conn->prepare("UPDATE hotels SET name = ?, image = ?, tags = ?, price = ?, location = ?, rating = ?, reviews = ?, start = ? WHERE id = ?");
    $stmt->bind_param("sssdsdiii", $name, $image, $tags, $price, $location, $rating, $reviews, $start, $id);
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật bảng hotels: " . $stmt->error);
    }

    // Kiểm tra và cập nhật hoặc thêm mới vào hotels_detail
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM hotels_detail WHERE id_hotels = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        // Cập nhật nếu đã tồn tại
        $stmt = $conn->prepare("UPDATE hotels_detail SET youtube_id = ?, title_ytb = ?, address = ?, description = ?, map_embed = ?, gallery = ?, experience = ?, combo_details = ? WHERE id_hotels = ?");
        $stmt->bind_param("ssssssssi", $youtube_id, $title_ytb, $address, $description, $map_embed, $gallery, $experience, $combo_details, $id);
    } else {
        // Thêm mới nếu chưa tồn tại
        $stmt = $conn->prepare("INSERT INTO hotels_detail (id_hotels, youtube_id, title_ytb, address, description, map_embed, gallery, experience, combo_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $id, $youtube_id, $title_ytb, $address, $description, $map_embed, $gallery, $experience, $combo_details);
    }
    if (!$stmt->execute()) {
        throw new Exception("Lỗi cập nhật/ thêm bảng hotels_detail: " . $stmt->error);
    }

    $conn->commit();
    header("Location: ../views/admin.php?type=hotel-management&action=update&success=1");
} catch (Exception $e) {
    $conn->rollback();
    error_log("Lỗi khi cập nhật khách sạn: " . $e->getMessage());
    header("Location: ../views/admin.php?type=hotel-management&action=update&error=1&message=" . urlencode($e->getMessage()));
} finally {
    $conn->close();
}
?>