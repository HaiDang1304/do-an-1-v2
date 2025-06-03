<?php
include "../config/database.php";
session_start();

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    $sql = "SELECT t.*, td.price, td.description, td.gallery, td.`tour-program`, td.note, td.rating, td.review
            FROM tours t
            LEFT JOIN `tour-detail` td ON t.id = td.`id-tour`
            WHERE t.id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Execute failed: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    if ($tour = mysqli_fetch_assoc($result)) {
        $gallery = json_decode($tour['gallery'], true);
        if (!is_array($gallery)) {
            $gallery = [];
        }

        $experience = $tour['tour-program'] ?? '';
        $combo = json_decode($tour['note'], true);
        if ($combo === null) {
            $combo = $tour['note'];
        }

        $title = $tour['title'] ?? 'Không có tiêu đề';
        $numStars = 4;

        // Lấy danh sách đánh giá từ bảng tour_reviews (chỉ để hiển thị chi tiết)
        $reviews_sql = "SELECT tr.*, u.username 
                        FROM tour_reviews tr 
                        JOIN users u ON tr.user_id = u.id 
                        WHERE tr.tour_id = ? 
                        ORDER BY tr.created_at DESC";
        $reviews_stmt = mysqli_prepare($conn, $reviews_sql);
        mysqli_stmt_bind_param($reviews_stmt, 'i', $id);
        mysqli_stmt_execute($reviews_stmt);
        $reviews_result = mysqli_stmt_get_result($reviews_stmt);
        $reviews = [];
        while ($review = mysqli_fetch_assoc($reviews_result)) {
            $reviews[] = $review;
        }
        mysqli_stmt_close($reviews_stmt);

        // Lấy rating và review từ tour-detail
        $average_rating = $tour['rating'] ?? 0;
        $review_count = $tour['review'] ?? 0;

        // Kiểm tra xem người dùng có đủ điều kiện đánh giá không
        $can_review = false;
        $user_id = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
        if ($user_id) {
            // Kiểm tra xem người dùng đã đặt tour này chưa
            $booking_check_sql = "SELECT id FROM `bookings-tour` WHERE tour_id = ? AND user_id = ? LIMIT 1";
            $booking_stmt = mysqli_prepare($conn, $booking_check_sql);
            mysqli_stmt_bind_param($booking_stmt, 'ii', $id, $user_id);
            mysqli_stmt_execute($booking_stmt);
            $booking_result = mysqli_stmt_get_result($booking_stmt);
            if (mysqli_num_rows($booking_result) > 0) {
                $can_review = true;
            }
            mysqli_stmt_close($booking_stmt);
        }

        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>Chi Tiết Tour - <?= htmlspecialchars($title) ?></title>
            <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
            <link rel="stylesheet" href="../css/tour-detail.css" />
            <style>
                .review-item {
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }
                .review-item:last-child {
                    border-bottom: none;
                }
            </style>
        </head>
        <body>
            <?php include '../includes/header.php'; ?>

            <div class="container py-4">
                <div class="row g-4 align-items-start">
                    <!-- Thông tin tour -->
                    <div class="col-md-8 mx-auto">
                        <div class="hotel-card p-3 border rounded shadow-sm mb-4 text-center">
                            <h2 class="fw-bold text-primary"><?= htmlspecialchars($title) ?>
                                <i class="fa-solid fa-heart text-danger ms-2"></i>
                            </h2>
                            <div class="mb-3">
                                <div class="d-flex justify-content-center align-items-center mb-2">
                                    <span class="badge bg-success"><?= number_format($average_rating, 1) ?></span>
                                    <span class="text-success fw-medium ms-2">
                                        <?= $average_rating >= 9 ? 'Tuyệt vời' : ($average_rating >= 8 ? 'Rất tốt' : 'Tốt') ?>
                                    </span>
                                    <small class="text-muted ms-2">| <?= $review_count ?> đánh giá</small>
                                </div>
                                <p class="text-muted">Mã tour: <?= htmlspecialchars($tour['id']) ?></p>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Giá chỉ từ</small>
                                <h4 class="text-danger fw-bold d-inline-block">
                                    <?= number_format($tour['price'] ?? 0, 0, ',', '.') ?> <span class="fs-6">VND</span>
                                </h4>
                                <button class="btn btn-warning fw-bold text-white px-4 mt-2" onclick="openBookingModal()">Đặt ngay</button>
                            </div>
                        </div>
                    </div>

                    <!-- Hình ảnh slideshow -->
                    <div class="col-md-8 mx-auto">
                        <div class="gallery-container mb-4">
                            <div class="swiper main-swiper mb-3">
                                <div class="swiper-wrapper">
                                    <?php foreach ($gallery as $image): ?>
                                        <div class="swiper-slide">
                                            <img src="../public/images-tour/images-book-tour/<?= htmlspecialchars($image) ?>"
                                                 alt="Hình ảnh tour" class="img-fluid w-100 h-100" />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                            <div class="swiper thumb-swiper">
                                <div class="swiper-wrapper">
                                    <?php foreach ($gallery as $image): ?>
                                        <div class="swiper-slide">
                                            <img src="../public/images-tour/images-book-tour/<?= htmlspecialchars($image) ?>"
                                                 alt="Hình ảnh tour" class="img-fluid" />
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trải nghiệm thú vị (description) -->
                    <div class="col-md-8 mx-auto">
                        <div class="card p-3 mb-4 text-center">
                            <h4 class="fw-bold text-primary mb-3">Trải nghiệm thú vị</h4>
                            <p class="text-start"><?= nl2br(htmlspecialchars($tour['description'] ?? '')) ?></p>
                        </div>
                    </div>

                    <!-- Chương trình tour (tour-program) -->
                    <div class="col-md-8 mx-auto">
                        <div class="card p-3 mb-4 text-center">
                            <h4 class="fw-bold text-primary mb-3">Chương trình tour</h4>
                            <?php
                            $tour_program = json_decode($tour['tour-program'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($tour_program) && count($tour_program) > 0):
                            ?>
                            <div class="accordion" id="tourProgramAccordion">
                                <?php foreach ($tour_program as $index => $day): 
                                    $collapseId = "collapseDay" . $index;
                                    $headingId = "headingDay" . $index;
                                    ?>
                                    <div class="accordion-item mb-3 shadow-sm rounded">
                                        <h2 class="accordion-header" id="<?= $headingId ?>">
                                            <button class="accordion-button collapsed d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                                <img src="../public/images-tour/images-book-tour/<?= htmlspecialchars($day['image'] ?? 'default.jpg') ?>" alt="Hình <?= htmlspecialchars($day['day']) ?>" style="width: 120px; height: 80px; object-fit: cover; border-radius: 8px; margin-right: 15px;">
                                                <div class="text-start">
                                                    <small class="text-muted d-block"><?= htmlspecialchars($day['day'] ?? 'Không có ngày') ?></small>
                                                    <strong><?= htmlspecialchars($day['title'] ?? 'Không có tiêu đề') ?></strong>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="<?= $collapseId ?>" class="accordion-collapse collapse" aria-labelledby="<?= $headingId ?>" data-bs-parent="#tourProgramAccordion">
                                            <div class="accordion-body text-start">
                                                <?php if (is_array($day['content'])): ?>
                                                    <ul class="list-group list-group-flush">
                                                        <?php foreach ($day['content'] as $item): ?>
                                                            <li class="list-group-item border-0 py-1"><?= nl2br(htmlspecialchars($item)) ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p><?= nl2br(htmlspecialchars($day['content'] ?? 'Không có nội dung')) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <p class="text-muted text-center">Chương trình tour chưa có thông tin chi tiết hoặc dữ liệu không hợp lệ.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Thông tin combo (note) -->
                    <div class="col-md-8 mx-auto">
                        <div class="card shadow-sm rounded border-primary">
                            <div class="card-header bg-primary text-white fw-bold">
                                Thông tin cần lưu ý
                            </div>
                            <div class="card-body">
                                <?php
                                if (is_array($combo)):
                                    $tabs = [
                                        'gia_bao_gom' => 'Giá bao gồm',
                                        'gia_khong_bao_gom' => 'Giá không bao gồm',
                                        'phu_thu' => 'Phụ thu',
                                        'huy_doi' => 'Hủy đổi',
                                        'luu_y' => 'Lưu ý',
                                        'huong_dan_vien' => 'Hướng dẫn viên',
                                    ];
                                    echo '<ul class="nav nav-tabs mb-3" id="comboTab" role="tablist">';
                                    $i = 0;
                                    foreach ($tabs as $key => $label) {
                                        $active = ($i === 0) ? 'active' : '';
                                        echo '<li class="nav-item" role="presentation">';
                                        echo '<button class="nav-link ' . $active . '" id="' . $key . '-tab" data-bs-toggle="tab" data-bs-target="#' . $key . '" type="button" role="tab" aria-controls="' . $key . '" aria-selected="' . ($i === 0 ? 'true' : 'false') . '">' . htmlspecialchars($label) . '</button>';
                                        echo '</li>';
                                        $i++;
                                    }
                                    echo '</ul>';
                                    echo '<div class="tab-content" id="comboTabContent">';
                                    $i = 0;
                                    foreach ($tabs as $key => $label) {
                                        $active = ($i === 0) ? 'show active' : '';
                                        echo '<div class="tab-pane fade ' . $active . '" id="' . $key . '" role="tabpanel" aria-labelledby="' . $key . '-tab">';
                                        if (isset($combo[$key])) {
                                            if (is_array($combo[$key])) {
                                                echo '<ul class="list-group list-group-flush">';
                                                foreach ($combo[$key] as $item) {
                                                    echo '<li class="list-group-item py-1 border-0">' . nl2br(htmlspecialchars($item)) . '</li>';
                                                }
                                                echo '</ul>';
                                            } else {
                                                echo '<p class="mb-0">' . nl2br(htmlspecialchars($combo[$key])) . '</p>';
                                            }
                                        } else {
                                            echo '<p class="text-muted">Không có thông tin.</p>';
                                        }
                                        echo '</div>';
                                        $i++;
                                    }
                                    echo '</div>';
                                elseif (is_string($combo) && !empty($combo)):
                                    echo '<p>' . nl2br(htmlspecialchars($combo)) . '</p>';
                                else:
                                    echo '<p class="text-muted text-center">Không có thông tin combo.</p>';
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Đánh giá tour -->
                    <div class="col-md-8 mx-auto">
                        <div class="card p-3 mb-4">
                            <h4 class="fw-bold text-primary mb-3 text-center">Đánh giá tour</h4>

                            <!-- Hiển thị danh sách đánh giá -->
                            <div id="reviewsList">
                                <?php if ($review_count > 0): ?>
                                    <div class="mb-4">
                                        <h5 class="text-center">Điểm trung bình: <span id="averageRating"><?= number_format($average_rating, 1) ?></span>/10 (<span id="reviewCount"><?= $review_count ?></span> đánh giá)</h5>
                                        <hr>
                                        <?php foreach ($reviews as $review): ?>
                                            <div class="review-item mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <strong><?= htmlspecialchars($review['username']) ?></strong>
                                                    <span class="badge bg-success"><?= $review['rating'] ?>/10</span>
                                                </div>
                                                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></small>
                                                <p class="mt-1"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center text-muted">Chưa có đánh giá nào cho tour này.</p>
                                <?php endif; ?>
                            </div>

                            <!-- Khung nhập đánh giá -->
                            <?php if ($can_review): ?>
                                <hr>
                                <h5 class="text-center mb-3">Thêm đánh giá của bạn</h5>
                                <form id="reviewForm" method="POST" action="../includes/submit-review-tour.php">
                                    <input type="hidden" name="tour_id" value="<?= $id ?>">
                                    <input type="hidden" name="user_id" value="<?= $user_id ?>">
                                    <div class="mb-3">
                                        <label for="rating" class="form-label">Điểm đánh giá (0-10)</label>
                                        <input type="number" class="form-control" id="rating" name="rating" min="0" max="10" step="0.1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="review_text" class="form-label">Nội dung đánh giá</label>
                                        <textarea class="form-control" id="review_text" name="review_text" rows="3" required placeholder="Nhập đánh giá của bạn"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Gửi đánh giá</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../includes/modal-booking-tour.php'; ?>
       
            <?php include '../includes/footer.php'; ?>

            <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                // Kiểm tra Bootstrap JS
                if (typeof bootstrap === 'undefined') {
                    console.error('Bootstrap JS không được tải! Vui lòng kiểm tra đường dẫn hoặc kết nối mạng.');
                }

                // Khởi tạo Swiper
                const thumbSwiper = new Swiper('.thumb-swiper', {
                    spaceBetween: 10,
                    slidesPerView: 5,
                    freeMode: true,
                    watchSlidesProgress: true,
                });

                const mainSwiper = new Swiper('.main-swiper', {
                    loop: true,
                    slidesPerView: 1,
                    spaceBetween: 10,
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    thumbs: {
                        swiper: thumbSwiper,
                    },
                    autoHeight: true,
                });

                // Khởi tạo Modal
                const bookingModalElement = document.getElementById('bookingModal');
                if (!bookingModalElement) {
                    console.error('Không tìm thấy phần tử modal với ID "bookingModal".');
                }

                const bookingModal = new bootstrap.Modal(bookingModalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });

                // Hàm mở modal
                function openBookingModal() {
                    try {
                        bookingModal.show();
                        calculateTotalPrice();
                    } catch (error) {
                        console.error('Lỗi khi mở modal:', error);
                    }
                }

                // Tính tổng giá
                function calculateTotalPrice() {
                    const adultsInput = document.getElementById('adults');
                    const childrenInput = document.getElementById('children');
                    if (!adultsInput || !childrenInput) {
                        console.error('Không tìm thấy input "adults" hoặc "children".');
                        return;
                    }

                    const adults = parseInt(adultsInput.value) || 0;
                    const children = parseInt(childrenInput.value) || 0;
                    const pricePerAdult = parseInt(<?= $tour['price'] ?>);
                    const pricePerChild = pricePerAdult * 0.5;
                    const total = (adults * pricePerAdult) + (children * pricePerChild);
                    const totalPriceElement = document.getElementById('total_price');
                    if (totalPriceElement) {
                        totalPriceElement.textContent = total.toLocaleString('vi-VN') + ' VND';
                    }
                }

                // Xử lý sự kiện thay đổi số lượng người
                const adultsInput = document.getElementById('adults');
                const childrenInput = document.getElementById('children');
                if (adultsInput) adultsInput.addEventListener('input', calculateTotalPrice);
                if (childrenInput) childrenInput.addEventListener('input', calculateTotalPrice);

                // Đặt ngày tối thiểu cho ngày khởi hành
                const departureDateInput = document.getElementById('departure_date');
                if (departureDateInput) {
                    departureDateInput.setAttribute('min', new Date().toISOString().split('T')[0]);
                }

                // Reset form khi modal đóng
                bookingModalElement.addEventListener('hidden.bs.modal', function () {
                    if (bookingForm) {
                        bookingForm.reset();
                        calculateTotalPrice();
                    }
                });

                // Hiển thị thông báo từ query string
                window.onload = function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const success = urlParams.get('success');
                    const message = urlParams.get('message');
                    const review_success = urlParams.get('review_success');
                    if (success === '1') {
                        alert('Đặt tour thành công! Email xác nhận đã được gửi.');
                        window.location.href = 'index.php'; // Chuyển hướng về trang chủ
                    } else if (success === '0' && message) {
                        alert(decodeURIComponent(message));
                    } else if (review_success === '1') {
                        alert('Gửi đánh giá thành công!');
                    }
                };
            </script>
        </body>
        </html>
        <?php
    } else {
        echo "<p class='text-center text-muted'>Tour không tồn tại hoặc đã bị xóa.</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p class='text-center text-muted'>Không có mã tour.</p>";
}
mysqli_close($conn);
?>