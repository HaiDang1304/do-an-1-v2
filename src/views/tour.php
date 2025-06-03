<?php
include "../config/database.php";
session_start();

// Lấy dữ liệu GET request
$tour_name = isset($_GET['tour_name']) ? trim($_GET['tour_name']) : '';
$tags = isset($_GET['tags']) ? $_GET['tags'] : [];
$duration_days = isset($_GET['duration_days']) ? array_map('intval', $_GET['duration_days']) : [];

// Build query
$query = "
    SELECT t.*, td.price, td.description, td.rating, td.review
    FROM tours t
    LEFT JOIN `tour-detail` td ON t.id = td.`id-tour`
    WHERE 1
";

// Lọc theo tên tour (title hoặc mô tả trong tour-detail)
if ($tour_name !== '') {
    $tour_name_esc = $conn->real_escape_string($tour_name);
    $query .= " AND (t.title LIKE '%$tour_name_esc%' OR td.description LIKE '%$tour_name_esc%')";
}

// Nhận thêm giá trị tìm kiếm
$max_duration_days = isset($_GET['max_duration_days']) ? intval($_GET['max_duration_days']) : 0;
$price_min = isset($_GET['price_min']) ? intval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? intval($_GET['price_max']) : 0;
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$is_featured = isset($_GET['is_featured']) ? 1 : 0;
$is_new = isset($_GET['is_new']) ? (bool)$_GET['is_new'] : false;
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';

// Điều kiện ngày (duration_days <= max_duration_days)
if ($max_duration_days > 0) {
    $query .= " AND t.duration_days <= $max_duration_days";
}

// Điều kiện giá
if ($price_min > 0) {
    $query .= " AND td.price >= $price_min";
}
if ($price_max > 0) {
    $query .= " AND td.price <= $price_max";
}

// Điều kiện địa điểm (giả sử có trường t.location)
if ($location !== '') {
    $location_esc = $conn->real_escape_string($location);
    $query .= " AND t.location = '$location_esc'";
}

// Điều kiện tour nổi bật, tour mới
if ($is_featured) {
    $query .= " AND t.is_featured = 1";
}
if ($is_new) {
    $query .= " AND t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
}

// Điều kiện tags (giữ nguyên)
if (!empty($tags)) {
    foreach ($tags as $tag) {
        $tag_esc = $conn->real_escape_string($tag);
        $query .= " AND t.tag LIKE '%$tag_esc%'";
    }
}

// Sắp xếp dữ liệu theo dropdown
switch ($sort_by) {
    case 'price_desc':
        $query .= " ORDER BY td.price DESC";
        break;
    case 'price_asc':
        $query .= " ORDER BY td.price ASC";
        break;
    case 'rating_desc':
        $query .= " ORDER BY td.rating DESC";
        break;
    case 'review_asc':
        $query .= " ORDER BY td.review ASC";
        break;
    default:
        $query .= " ORDER BY t.id DESC"; // Mặc định, tour mới lên trước (có thể tùy chỉnh)
        break;
}


// Lọc theo tags (do tag lưu dạng JSON string, dùng LIKE đơn giản)
// if (!empty($tags)) {
//     foreach ($tags as $tag) {
//         $tag_esc = $conn->real_escape_string($tag);
//         $query .= " AND t.tag LIKE '%$tag_esc%'";
//     }
// }

// Thực thi truy vấn
$result = $conn->query($query);
if (!$result) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

// Lấy danh sách tất cả tags để hiển thị checkbox lọc
$tag_counts = [];
$tag_query = "SELECT tag FROM tours";
$tag_result = $conn->query($tag_query);
$all_tags = [];
while ($row = $tag_result->fetch_assoc()) {
    $tags_array = json_decode($row['tag'], true);
    if (is_array($tags_array)) {
        foreach ($tags_array as $tag_item) {
            $all_tags[] = $tag_item;
        }
    }
}
$tag_counts = array_count_values($all_tags);


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Danh sách tour Phú Quốc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/sell-ticket-tour.css">
    <link rel="stylesheet" href="../css/doan.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="content-full">
        <div class="d-flex gap-4 p-3">
            <div class="col-lg-3 mt-4">
                <!-- Hỗ trợ -->
                <div class="card p-3 mb-3 d-flex flex-row align-items-center" style="min-height:110px;">
                    <img src="../public/images-tour/bg-tickets-tour/avata-support.jpg" alt="Hỗ trợ viên"
                        class="rounded-circle me-3" style="width: 80px; height: 80px; object-fit: cover;" />
                    <div>
                        <h6 class="fw-bold mb-2">Cần hỗ trợ?</h6>
                        <div class="d-flex align-items-center">
                            <span>HD</span>
                            <a href="tel:0948773012" class="text-orange ms-2 text-decoration-none">0948773012</a>
                        </div>
                    </div>
                </div>

                <!-- Form lọc tour -->
                <form method="GET" class="card p-3 mb-3">
                    <!-- Tìm kiếm tên tour -->
                    <div class="input-group mb-3">
                        <input type="text" name="tour_name" class="form-control" placeholder="Nhập tên tour"
                            value="<?= htmlspecialchars($tour_name) ?>" />
                        <button class="btn btn-warning text-white" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    <!-- Khoảng giá -->
                    <label class="fw-bold">Khoảng giá (VND)</label>
                    <div class="d-flex gap-2 mb-3">
                        <input type="number" name="price_min" class="form-control" placeholder="Từ" min="0"
                            value="<?= isset($_GET['price_min']) ? intval($_GET['price_min']) : '' ?>" />
                        <input type="number" name="price_max" class="form-control" placeholder="Đến" min="0"
                            value="<?= isset($_GET['price_max']) ? intval($_GET['price_max']) : '' ?>" />
                    </div>

                    <!-- Số ngày tối đa -->
                    <label for="max_duration_days" class="fw-bold">Số ngày tối đa</label>
                    <input type="number" name="max_duration_days" id="max_duration_days" min="0" max="30" class="form-control mb-3"
                        value="<?= isset($_GET['max_duration_days']) ? intval($_GET['max_duration_days']) : '' ?>" />

                    <!-- Địa điểm (ví dụ lấy từ $locations) -->
                    <label for="location" class="fw-bold">Địa điểm</label>
                    <select name="location" id="location" class="form-select mb-3">
                        <option value="">-- Tất cả địa điểm --</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?= htmlspecialchars($loc) ?>" <?= (isset($_GET['location']) && $_GET['location'] == $loc) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Dropdown sắp xếp -->
                    <label for="sort_by" class="fw-bold mt-3">Sắp xếp theo</label>
                    <select name="sort_by" id="sort_by" class="form-select mb-3">
                        <option value="">-- Mặc định --</option>
                        <option value="price_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_desc') ? 'selected' : '' ?>>Giá giảm dần</option>
                        <option value="price_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_asc') ? 'selected' : '' ?>>Giá tăng dần</option>
                        <option value="rating_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'rating_desc') ? 'selected' : '' ?>>Đánh giá giảm dần</option>
                        <option value="review_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'review_asc') ? 'selected' : '' ?>>Review tăng dần</option>
                    </select>

                    <!-- Lọc tour nổi bật, tour mới (hiển thị ngang, gồm cả tên) -->
                    <div class="d-flex gap-4 mt-3">
                        <div class="form-check d-flex align-items-center">
                            <input class="form-check-input me-1" type="checkbox" name="is_featured" value="1" id="is_featured" <?= isset($_GET['is_featured']) ? 'checked' : '' ?> />
                            <label class="form-check-label" for="is_featured">Tour nổi bật</label>
                        </div>
                        <div class="form-check d-flex align-items-center">
                            <input class="form-check-input me-1" type="checkbox" name="is_new" value="1" id="is_new" <?= isset($_GET['is_new']) ? 'checked' : '' ?> />
                            <label class="form-check-label" for="is_new">Tour mới</label>
                        </div>
                    </div>

                    <!-- Tags (nếu vẫn cần giữ) -->
                    <div class="mb-2 fw-bold">Tags</div>
                    <?php foreach ($tag_counts as $tag => $count): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="tags[]" value="<?= htmlspecialchars($tag) ?>"
                                id="tag_<?= htmlspecialchars($tag) ?>" <?= in_array($tag, $tags) ? 'checked' : '' ?> />
                            <label class="form-check-label" for="tag_<?= htmlspecialchars($tag) ?>">
                                <?= htmlspecialchars($tag) ?> (<?= $count ?>)
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <button type="submit" class="btn btn-primary mt-3 w-100">Lọc</button>
                </form>

            </div>

            <div class="container mt-1">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): 
                        $tagsArray = json_decode($row['tag'], true);
                        $tagsHTML = '';
                        if (is_array($tagsArray)) {
                            foreach ($tagsArray as $tagItem) {
                                $tagsHTML .= '<span class="badge bg-primary me-1">' . htmlspecialchars($tagItem) . '</span>';
                            }
                        }
                    ?>
                    <div class="combo-banner mb-3">
                        <div class="deal-box card shadow-sm overflow-hidden">
                            <a href="../views/tour-detail.php?id=<?= htmlspecialchars($row['id']) ?>" class="stretched-link"></a>
                            <div class="row g-0 align-items-center">
                                <div class="col-md-3 position-relative">
                                    <div class="ribbon" style="position:absolute; top:8px; z-index:10;">
                                        <?php
                                        if (is_array($tagsArray)) {
                                            foreach ($tagsArray as $tagItem) {
                                                echo '<span class="badge bg-info me-1">' . htmlspecialchars($tagItem) . '</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <img src="../public/images-tour/bg-tickets-tour/<?= htmlspecialchars($row['image']) ?>"
                                        class="img-fluid w-100 h-100 object-fit-cover" alt="<?= htmlspecialchars($row['title']) ?>" />
                                </div>
                                <div class="col-md-6 p-3">
                                    <h5 class="fw-bold text-primary mb-2"><?= htmlspecialchars($row['title']) ?></h5>
                                    <div class="mb-2">
                                        <span class="badge bg-success ms-2">
                                            <?= number_format($row['rating'], 1) ?>
                                            <?= $row['rating'] >= 9.0 ? 'Tuyệt vời' : ($row['rating'] >= 8.0 ? 'Rất tốt' : 'Tốt') ?>
                                        </span>
                                        <small class="text-muted">| <?= intval($row['review']) ?> đánh giá</small>
                                    </div>
                                    <div class="tour-tags"><?= $tagsHTML ?></div>
                                </div>
                                <div class="col-md-3 p-3 text-center bg-light">
                                    <h6 class="text-secondary fw-bold mb-2">Giá từ</h6>
                                    <p class="text-danger fw-bold mb-0"><?= number_format($row['price'], 0, ',', '.') ?> VND</p>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted">Không tìm thấy tour nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
