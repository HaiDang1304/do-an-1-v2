<?php
include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/admin.php?type=hotel-management&action=insert&error=1&message=" . urlencode('Phương thức không hợp lệ'));
    exit;
}

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

// Kiểm tra dữ liệu bắt buộc
if (empty($name) || empty($price) || empty($location) || empty($start)) {
    header("Location: ../views/admin.php?type=hotel-management&action=insert&error=1&message=" . urlencode('Tên khách sạn, giá, vị trí và số sao là bắt buộc'));
    exit;
}

// Kiểm tra và giải mã dữ liệu JSON
$tags = json_decode($tags, true);
$gallery = json_decode($gallery, true);
$experience = json_decode($experience, true);
$combo_details = json_decode($combo_details, true);

if (!is_array($tags) || !is_array($gallery) || !is_array($experience) || !is_array($combo_details)) {
    header("Location: ../views/admin.php?type=hotel-management&action=insert&error=1&message=" . urlencode('Dữ liệu JSON không hợp lệ'));
    exit;
}

// Mã hóa lại JSON
$tags = json_encode(array_filter($tags, 'strlen'));
$gallery = json_encode($gallery);
$experience = json_encode($experience);
$combo_details = json_encode($combo_details);

$conn->begin_transaction();
try {
    // Thêm vào bảng hotels
    $stmt = $conn->prepare("INSERT INTO hotels (name, image, tags, price, location, rating, reviews, start) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsdii", $name, $image, $tags, $price, $location, $rating, $reviews, $start);
    $stmt->execute();
    $hotel_id = $conn->insert_id;

    // Kiểm tra hotel_id
    if ($hotel_id <= 0) {
        throw new Exception('Không thể lấy ID khách sạn vừa thêm');
    }

    // Thêm vào bảng hotels_detail
    $stmt = $conn->prepare("INSERT INTO hotels_detail (youtube_id, title_ytb, address, description, map_embed, gallery, experience, combo_details, id_hotels) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $youtube_id, $title_ytb, $address, $description, $map_embed, $gallery, $experience, $combo_details, $hotel_id);
    $stmt->execute();

    $conn->commit();
    header("Location: ../views/admin.php?type=hotel-management&action=insert&success=1");
} catch (Exception $e) {
    $conn->rollback();
    error_log("Lỗi khi thêm khách sạn: " . $e->getMessage());
    header("Location: ../views/admin.php?type=hotel-management&action=insert&error=1&message=" . urlencode($e->getMessage()));
} finally {
    $conn->close();
}
?>