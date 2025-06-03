<?php
header('Content-Type: application/json; charset=UTF-8');
include_once '../config/database.php';

// Kiểm tra kết nối cơ sở dữ liệu
if ($conn->connect_error) {
    echo json_encode(['error' => 'Không thể kết nối tới cơ sở dữ liệu: ' . $conn->connect_error]);
    exit;
}

// Kiểm tra tham số id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'ID khách sạn không hợp lệ']);
    exit;
}

$id = (int)$_GET['id'];

// Truy vấn dữ liệu từ bảng hotels và hotels_detail
$stmt = $conn->prepare("SELECT h.id, h.name, h.image, h.tags, h.price, h.location, h.rating, h.reviews, h.start,
                        hd.youtube_id, hd.title_ytb, hd.address, hd.description, hd.map_embed, hd.gallery, hd.experience, hd.combo_details
                        FROM hotels h
                        LEFT JOIN hotels_detail hd ON h.id = hd.id_hotels
                        WHERE h.id = ?");
if (!$stmt) {
    echo json_encode(['error' => 'Lỗi chuẩn bị truy vấn: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);
if (!$stmt->execute()) {
    echo json_encode(['error' => 'Lỗi thực thi truy vấn: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row) {
    // Xử lý dữ liệu trả về
    $response = [
        'id' => $row['id'],
        'name' => $row['name'] ?? '',
        'image' => $row['image'] ?? '',
        'tags' => [],
        'price' => $row['price'] ?? 0,
        'location' => $row['location'] ?? '',
        'rating' => $row['rating'] ?? 0,
        'reviews' => $row['reviews'] ?? 0,
        'start' => $row['start'] ?? 1,
        'youtube_id' => $row['youtube_id'] ?? '',
        'title_ytb' => $row['title_ytb'] ?? '',
        'address' => $row['address'] ?? '',
        'description' => $row['description'] ?? '',
        'map_embed' => $row['map_embed'] ?? '',
        'gallery' => [],
        'experience' => [],
        'combo_details' => ['combo_name' => '', 'description' => '', 'included' => [], 'conditions' => []]
    ];

    // Xử lý tags
    if ($row['tags']) {
        $tags = json_decode($row['tags'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response['tags'] = $tags;
        } else {
            $response['tags'] = explode(',', $row['tags']);
        }
    }

    // Xử lý gallery
    if ($row['gallery']) {
        $gallery = json_decode($row['gallery'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response['gallery'] = $gallery;
        }
    }

    // Xử lý experience
    if ($row['experience']) {
        $experience = json_decode($row['experience'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response['experience'] = $experience;
        }
    }

    // Xử lý combo_details
    if ($row['combo_details']) {
        $combo_details = json_decode($row['combo_details'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $response['combo_details'] = $combo_details;
        }
    }

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'Không tìm thấy khách sạn với ID: ' . $id]);
}

$stmt->close();
$conn->close();
?>