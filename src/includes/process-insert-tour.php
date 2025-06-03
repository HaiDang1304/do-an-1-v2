<?php
include_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/admin.php?type=tour-management&action=insert&error=1&message=" . urlencode('Phương thức không hợp lệ'));
    exit;
}

$title = $_POST['title'] ?? '';
$image = $_POST['image'] ?? null;
$tag = $_POST['tag'] ?? '';
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$price = $_POST['price'] ?? 0;
$description = $_POST['description'] ?? '';
$gallery = $_POST['gallery'] ?? '';
$tour_program = $_POST['tour-program'] ?? '';
$note = $_POST['note'] ?? '';
$duration_days = $_POST['duration_days'] ?? 1; // Thêm trường duration_days

// Kiểm tra dữ liệu bắt buộc
if (empty($title) || empty($price) || empty($duration_days)) {
    header("Location: ../views/admin.php?type=tour-management&action=insert&error=1&message=" . urlencode('Tiêu đề, giá và số ngày là bắt buộc'));
    exit;
}

// Hàm chuyển đổi chuỗi nhập vào thành mảng JSON
function convertToJsonArray($input) {
    if (empty($input)) {
        return json_encode([]);
    }
    
    // Nếu input đã là JSON hợp lệ thì giữ nguyên
    json_decode($input);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $input;
    }
    
    // Chuyển đổi từ chuỗi thông thường (phân tách bằng dấu phẩy hoặc xuống dòng) thành mảng
    $items = preg_split('/\r\n|[\r\n,]/', $input, -1, PREG_SPLIT_NO_EMPTY);
    $items = array_map('trim', $items);
    $items = array_filter($items); // Loại bỏ các phần tử rỗng
    
    return json_encode(array_values($items)); // array_values để đảm bảo index bắt đầu từ 0
}

// Hàm xử lý dữ liệu phức tạp hơn như tour-program và note
function convertComplexToJson($input) {
    if (empty($input)) {
        return json_encode([]);
    }
    
    // Nếu input đã là JSON hợp lệ thì giữ nguyên
    json_decode($input);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $input;
    }
    
    // Xử lý trường hợp nhập text thông thường
    return json_encode(['content' => $input]);
}

// Xử lý các trường dữ liệu
$tag = convertToJsonArray($tag);
$gallery = convertToJsonArray($gallery);
$tour_program = convertComplexToJson($tour_program);
$note = convertComplexToJson($note);

$conn->begin_transaction();
try {
    // Thêm vào bảng tours (đã thêm duration_days)
    $stmt = $conn->prepare("INSERT INTO tours (title, image, tag, is_featured, duration_days) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $title, $image, $tag, $is_featured, $duration_days);
    $stmt->execute();
    $tour_id = $conn->insert_id;

    // Kiểm tra tour_id
    if ($tour_id <= 0) {
        throw new Exception('Không thể lấy ID tour vừa thêm');
    }

    // Thêm vào bảng tour-detail (không chỉ định cột id để tự động tăng)
    $stmt = $conn->prepare("INSERT INTO `tour-detail` (`id-tour`, price, description, gallery, `tour-program`, note) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $tour_id, $price, $description, $gallery, $tour_program, $note);
    $stmt->execute();

    $conn->commit();
    header("Location: ../views/admin.php?type=tour-management&action=insert&success=1");
} catch (Exception $e) {
    $conn->rollback();
    error_log("Lỗi khi thêm tour: " . $e->getMessage());
    header("Location: ../views/admin.php?type=tour-management&action=insert&error=1&message=" . urlencode($e->getMessage()));
} finally {
    $conn->close();
}
?>