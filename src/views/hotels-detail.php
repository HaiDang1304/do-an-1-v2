<?php
include "../config/database.php";
session_start();

// Check if the 'id' parameter is set and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Use prepared statement to avoid SQL injection
    $sql = "SELECT 
                hotels.*, 
                hotels_detail.*
            FROM hotels
            INNER JOIN hotels_detail ON hotels.id = hotels_detail.id_hotels
            WHERE hotels.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if the hotel is found
    if ($hotel = mysqli_fetch_assoc($result)) {
        // Decode images JSON safely
        $images = isset($hotel['images']) ? json_decode($hotel['images'], true) : [];

        // Lấy số lượng và điểm trung bình đánh giá
        $sql_reviews = "SELECT r.rating, r.comment, r.created_at, u.username 
                        FROM reviews r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        WHERE r.hotel_id = ? 
                        ORDER BY r.created_at DESC";
        $stmt_reviews = mysqli_prepare($conn, $sql_reviews);
        mysqli_stmt_bind_param($stmt_reviews, 'i', $id);
        mysqli_stmt_execute($stmt_reviews);
        $result_reviews = mysqli_stmt_get_result($stmt_reviews);

        $total_rating = 0;
        $review_count = mysqli_num_rows($result_reviews);
        $reviews = [];
        while ($row = mysqli_fetch_assoc($result_reviews)) {
            $reviews[] = $row;
            $total_rating += $row['rating'];
        }
        $average_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;
        mysqli_stmt_close($stmt_reviews);

        // Kiểm tra xem người dùng có đủ điều kiện đánh giá không
        $can_review = false;
        $user_id = isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
        if ($user_id) {
            // Kiểm tra xem người dùng đã đặt khách sạn này chưa
            $booking_check_sql = "SELECT id FROM bookings WHERE hotel_id = ? AND user_id = ? LIMIT 1";
            $booking_stmt = mysqli_prepare($conn, $booking_check_sql);
            mysqli_stmt_bind_param($booking_stmt, 'ii', $id, $user_id);
            mysqli_stmt_execute($booking_stmt);
            $booking_result = mysqli_stmt_get_result($booking_stmt);
            if (mysqli_num_rows($booking_result) > 0) {
                $can_review = true;
            }
            mysqli_stmt_close($booking_stmt);
        }
        // Lấy danh sách phòng cho khách sạn
        $available_rooms = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkin']) && isset($_POST['checkout'])) {
            $checkin = $_POST['checkin'];
            $checkout = $_POST['checkout'];
            $checkinDate = new DateTime($checkin);
            $checkoutDate = new DateTime($checkout);
            $duration = $checkinDate->diff($checkoutDate)->days;

            if ($checkinDate >= $checkoutDate) {
                $error_message = "Ngày trả phòng phải sau ngày nhận phòng.";
            } else {
                // Lấy mẫu phòng từ bảng rooms
                $room_sql = "SELECT room_number_pattern, total_rooms 
                     FROM rooms 
                     WHERE hotel_id = ?";
                $room_stmt = mysqli_prepare($conn, $room_sql);
                mysqli_stmt_bind_param($room_stmt, 'i', $id);
                mysqli_stmt_execute($room_stmt);
                $room_result = mysqli_stmt_get_result($room_stmt);
                $room_data = mysqli_fetch_assoc($room_result);
                mysqli_stmt_close($room_stmt);

                if ($room_data) {
                    $pattern = $room_data['room_number_pattern'];
                    $total_rooms = $room_data['total_rooms'];
                    $start_room = (int) substr($pattern, 0, strpos($pattern, '-'));
                    $end_room = (int) substr($pattern, strpos($pattern, '-') + 1);

                    // Tạo danh sách tất cả phòng
                    $all_rooms = range($start_room, $end_room);
                    // Debug: Kiểm tra danh sách phòng
                    error_log("All rooms: " . print_r($all_rooms, true));

                    // Kiểm tra phòng đã đặt (tối ưu hóa logic giao thoa thời gian)
                    $booked_sql = "SELECT room_number 
                           FROM bookings 
                           WHERE hotel_id = ? 
                           AND (
                               (checkin <= ? AND checkout > ?) OR
                               (checkin < ? AND checkout >= ?) OR
                               (checkin >= ? AND checkout <= ?)
                           )";
                    $booked_stmt = mysqli_prepare($conn, $booked_sql);
                    mysqli_stmt_bind_param($booked_stmt, 'issssss', $id, $checkout, $checkin, $checkout, $checkin, $checkin, $checkout);
                    mysqli_stmt_execute($booked_stmt);
                    $booked_result = mysqli_stmt_get_result($booked_stmt);
                    $booked_rooms = $booked_result->fetch_all(MYSQLI_ASSOC);
                    mysqli_stmt_close($booked_stmt);

                    // Debug: Kiểm tra phòng đã đặt
                    error_log("Booked rooms: " . print_r($booked_rooms, true));
                    $booked_room_numbers = array_column($booked_rooms, 'room_number');
                    $available_rooms = array_diff($all_rooms, array_filter(array_map('strval', $booked_room_numbers)));

                    // Debug: Kiểm tra phòng trống
                    error_log("Available rooms: " . print_r($available_rooms, true));

                    if (empty($available_rooms)) {
                        $error_message = "Không còn phòng trống trong khoảng thời gian này.";
                    }
                } else {
                    $error_message = "Không tìm thấy thông tin phòng cho khách sạn này.";
                }
            }
        }
        ?>
        <!DOCTYPE html>
        <html lang="vi">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Chi Tiết Khách Sạn - <?= htmlspecialchars($hotel['name']) ?></title>
            <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="../css/hotel-details.css">
            <link rel="stylesheet" href="../css/doan.css">

        </head>

        <body>
            <!-- Header -->
            <?php include '../includes/header.php'; ?>

            <div class="container py-4">
                <!-- Thông báo thành công/lỗi -->
                <?php
                if (isset($_GET['review_success'])) {
                    echo '<div class="alert alert-success">Đánh giá đã được gửi thành công!</div>';
                }
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
                }
                ?>

                <div class="row g-4 align-items-start">
                    <div class="col-md-3">
                        <div class="map-responsive shadow-sm rounded-4 border">
                            <?= $hotel['map_embed'] ?>
                        </div>

                        <h3 class="text-primary fw-bold mt-2" style="font-size: 17px;">
                            Trải nghiệm phải thử ở <?= htmlspecialchars($hotel['name']) ?>
                        </h3>

                        <?php
                        $experiences = json_decode($hotel['experience'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($experiences)):
                            foreach ($experiences as $exp):
                                if (!empty($exp['title']) && !empty($exp['content'])): ?>
                                    <h5 class="fw-bold mt-4 font-size"><?= htmlspecialchars($exp['title']) ?></h5>
                                    <div class="font-size">
                                        <p><?= nl2br(htmlspecialchars($exp['content'])) ?></p>
                                    </div>
                                <?php endif;
                            endforeach;
                        else:
                            echo "<p>Không có trải nghiệm nào để hiển thị.</p>";
                        endif;
                        ?>
                    </div>

                    <div class="col-md-6 flex-fill">
                        <div
                            class="hotel-card d-flex justify-content-between align-items-start p-3 border rounded shadow-sm mb-4">
                            <div>
                                <h5 class="fw-bold text-primary mb-1">
                                    <?= htmlspecialchars($hotel['name']) ?>
                                    <i class="fa-solid fa-heart text-danger"></i>
                                </h5>

                                <div class="d-flex align-items-center mb-2 flex-wrap">
                                    <div class="badge bg-success me-2"><?= number_format($hotel['rating'], 1) ?></div>
                                    <span class="text-success fw-medium me-2">
                                        <?= $hotel['rating'] >= 9.0 ? 'Tuyệt vời' : ($hotel['rating'] >= 8.0 ? 'Rất tốt' : 'Tốt') ?>
                                    </span>
                                    <small class="text-muted">| <?= $hotel['reviews'] ?> đánh giá</small>
                                </div>

                                <div class="text-muted">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?= htmlspecialchars($hotel['location']) ?>
                                </div>
                            </div>

                            <div class="text-end">
                                <small class="text-muted">Giá chỉ từ</small>
                                <h4 class="text-info fw-bold">
                                    <?= number_format($hotel['price'], 0, ',', '.') ?>
                                    <span class="fs-6">VND</span>
                                </h4>
                                <button class="btn btn-warning fw-bold text-white px-4 mt-1" onclick="openBookingModal()">Đặt
                                    ngay</button>
                            </div>
                        </div>

                        <div class="gallery-container">
                            <div class="swiper main-swiper mb-3">
                                <div class="swiper-wrapper">
                                    <?php
                                    $gallery = json_decode($hotel['gallery'], true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($gallery)):
                                        foreach ($gallery as $exp):
                                            if (!empty($exp['main-images']) && is_array($exp['main-images'])):
                                                foreach ($exp['main-images'] as $image): ?>
                                                    <div class="swiper-slide">
                                                        <img src="../public/images-hotel/image-book-hotel/<?= htmlspecialchars($image) ?>"
                                                            alt="Hình ảnh khách sạn" />
                                                    </div>
                                                <?php endforeach;
                                            endif;
                                        endforeach;
                                    else:
                                        echo "<p>Không có hình ảnh nào để hiển thị.</p>";
                                    endif;
                                    ?>
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                            <div class="swiper thumb-swiper">
                                <div class="swiper-wrapper">
                                    <?php
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($gallery)):
                                        foreach ($gallery as $exp):
                                            if (!empty($exp['sub-images']) && is_array($exp['sub-images'])):
                                                foreach ($exp['sub-images'] as $image): ?>
                                                    <div class="swiper-slide">
                                                        <img src="../public/images-hotel/image-book-hotel/<?= htmlspecialchars($image) ?>"
                                                            alt="Hình ảnh khách sạn" />
                                                    </div>
                                                <?php endforeach;
                                            endif;
                                        endforeach;
                                    else:
                                        echo "<p>Không có hình ảnh nào để hiển thị.</p>";
                                    endif;
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div id="video-tag" style="display: flex; align-items: center; gap: 15px; cursor: pointer;"
                            class="video-thumbnail" onclick="showVideo()">
                            <img style="width: 100px; margin-top: 20px;"
                                src="https://img.youtube.com/vi/<?= htmlspecialchars($hotel['youtube_id']) ?>/0.jpg"
                                alt="thumbnail">
                            <div>
                                <div class="badge">Video</div>
                                <div style="color: teal; font-size: 18px; font-weight: 500;">
                                    <?= htmlspecialchars($hotel['title_ytb']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Iframe YouTube -->
                        <div id="video-embed" style="display: none;">
                            <h3 style="color:#3366CC; margin-top: 20px"><b><?= htmlspecialchars($hotel['title_ytb']) ?></b></h3>
                            <iframe width="966" height="451"
                                src="https://www.youtube.com/embed/<?= htmlspecialchars($hotel['youtube_id']) ?>"
                                title="YouTube video player" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen></iframe>
                        </div>

                        <?php
                        $combo = json_decode($hotel['combo_details'], true);
                        ?>
                        <div class="combo-tour border rounded p-4 mt-4" style="background-color: rgba(198, 134, 88, 0.258);">
                            <h4 class="text-primary fw-bold">
                                <?= htmlspecialchars($combo['combo_name'] ?? 'Tên combo chưa có') ?>
                            </h4>
                            <p><?= htmlspecialchars($combo['description'] ?? 'Không có mô tả combo') ?></p>

                            <?php if (!empty($combo['included']) && is_array($combo['included'])): ?>
                                <ul>
                                    <?php foreach ($combo['included'] as $item): ?>
                                        <li>
                                            <strong><?= htmlspecialchars($item['title'] ?? 'Không có tiêu đề') ?>:</strong>
                                            <?= htmlspecialchars($item['detail'] ?? 'Không có mô tả') ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Không có thông tin chi tiết cho combo này.</p>
                            <?php endif; ?>

                            <?php if (!empty($combo['special_moments'])): ?>
                                <div class="special-moments mt-3">
                                    <strong>🌅 Khoảnh Khắc Đáng Nhớ:</strong>
                                    <p><?= htmlspecialchars($combo['special_moments']) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($combo['facilities']) && is_array($combo['facilities'])): ?>
                                <div class="facilities mt-3">
                                    <strong>Tiện ích đa dạng:</strong>
                                    <ul>
                                        <?php foreach ($combo['facilities'] as $facility): ?>
                                            <li><?= htmlspecialchars($facility) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($combo['extra_notes'])): ?>
                                <div class="extra-notes mt-3">
                                    <strong>Ghi chú đặc biệt:</strong>
                                    <p><?= htmlspecialchars($combo['extra_notes']) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($combo['special_note'])): ?>
                                <div class="special-note mt-3">
                                    <strong>Đặc biệt:</strong>
                                    <p><?= htmlspecialchars($combo['special_note']) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($combo['conditions']) && is_array($combo['conditions'])): ?>
                                <div class="conditions mt-3">
                                    <strong>Điều kiện áp dụng:</strong>
                                    <ul>
                                        <?php foreach ($combo['conditions'] as $condition): ?>
                                            <li><?= htmlspecialchars($condition) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Đánh giá khách sạn -->
                        <div class="col-md-20 mx-auto mt-5">
                            <div class="card p-3 mb-4">
                                <h4 class="fw-bold text-primary mb-3 text-center">Đánh giá khách sạn</h4>

                                <!-- Hiển thị danh sách đánh giá -->
                                <div id="reviewsList">
                                    <?php if ($hotel['reviews'] > 0): ?>
                                        <div class="mb-4">
                                            <h5 class="text-center">Điểm trung bình: <span
                                                    id="averageRating"><?= number_format($hotel['rating'], 1) ?></span>/10 (<span
                                                    id="reviewCount"><?= $hotel['reviews'] ?></span> đánh giá)</h5>
                                            <hr>
                                            <?php foreach ($reviews as $review): ?>
                                                <div class="review-item mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <strong><?= htmlspecialchars($review['username'] ?? 'Ẩn danh') ?></strong>
                                                        <span
                                                            class="badge bg-success"><?= number_format($review['rating'], 1) ?>/10</span>
                                                    </div>
                                                    <small
                                                        class="text-muted"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></small>
                                                    <p class="mt-1"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">Chưa có đánh giá nào cho khách sạn này.</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Khung nhập đánh giá -->
                                <?php if ($can_review): ?>
                                    <hr>
                                    <h5 class="text-center mb-3">Thêm đánh giá của bạn</h5>
                                    <form id="reviewForm" method="POST" action="../includes/submit-review-hotel.php">
                                        <input type="hidden" name="hotel_id" value="<?= $id ?>">
                                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">Điểm đánh giá (0-10)</label>
                                            <input type="number" class="form-control" id="rating" name="rating" min="0" max="10"
                                                step="0.1" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="comment" class="form-label">Nội dung đánh giá</label>
                                            <textarea class="form-control" id="comment" name="comment" rows="3" required
                                                placeholder="Nhập đánh giá của bạn"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">Gửi đánh giá</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
              <?php include('../includes/modal-booking-hotel.php');?>

  <!-- #re--> <?php include '../includes/footer.php'; ?>
            </div>
            <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="../js/hotels-detail.js"></script>
        
        </body>
        </html>
        <?php
    } else {
        echo "<p>Khách sạn không tồn tại hoặc đã bị xóa.</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p>Không có mã khách sạn.</p>";
}
mysqli_close($conn);

?>