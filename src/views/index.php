<?php
session_start();
require_once __DIR__ . '../../config/database.php';
require_once __DIR__ . '../../includes/send-voucher.php';

if (isset($_GET['logout'])) {
    unset($_SESSION['user']);
    session_regenerate_id(true);
    session_destroy();
    header("Location: index.php");
    exit();
}
// Xử lý form gửi email voucher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $user_id = isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : null;

    $result = sendVoucherEmail($email, $user_id, $conn);
    
    // Lưu thông báo vào session
    $_SESSION['subscription_message'] = $result['message'];
    
    // Chuyển hướng về index.php để tránh resubmission
    header("Location: index.php");
    exit();
}


// Lấy thông báo từ session và xóa sau khi hiển thị
$subscription_message = '';
if (isset($_SESSION['subscription_message'])) {
    $subscription_message = $_SESSION['subscription_message'];
    unset($_SESSION['subscription_message']);
}

// Lấy dữ liệu từ bảng hotels
$sqlhotels = "SELECT * FROM hotels";
$resulthotels = $conn->query($sqlhotels);

// Lấy dữ liệu từ bảng tours và tour_detail
$sqlactivities = "SELECT t.id, t.title AS name, td.rating
                 FROM tours t
                 LEFT JOIN `tour-detail` td ON t.id = td.`id-tour`";
$resultactivities = $conn->query($sqlactivities);

// Kiểm tra lỗi truy vấn
if (!$resultactivities) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang chính</title>
    <link rel="stylesheet" href="../css/doan.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">
    <script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.3.3/photoswipe.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* CSS cho thông báo */
        .subscription-message {
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/video-bg.php'; ?>
    <div class="content-full">
        <div class="tieude">
            <h5 class="tblack">HÃY ĐỒNG HÀNH CÙNG NHAU NHÉ</h5>
            <span class="tracking-in-contract-bck-bottom">__________________</span>
            <h2 class="t-content">KHÁM PHÁ NGAY CÁC ĐỊA ĐIỂM
                <span class="text-blue">NỔI TIẾNG</span>
            </h2>
        </div>
        <div class="slide">
            <div class="slide-img-backgroud">
                <img src="../public/images/backgroud6.0.jpeg">
            </div>
            <div class="slide-content">
                <h1>ĐẶT TOUR NGAY HÔM NAY</h1>
                <p>Đừng bỏ lỡ cơ hội trải nghiệm những điều thú vị nhất tại Phú Quốc</p>
                <div class="slide-button">
                    <a href="../views/tour.php">Đặt Tour Ngay</a>
                </div>
            </div>
        </div>

        <div class="ticket-container">
            <h2 class="t-content">TẬN HƯỞNG THỜI GIAN TUYỆT VỜI KHI ĐẾN VỚI PHÚ QUỐC</h2>
            <div class="tickets" id="tickets-tour">
            </div>
            <div class="see-more-container">
                <a href="../views/tour.php" class="see-more-link">Xem thêm<span class="arrow"></span></a>
            </div>
        </div>
        <div class="ticket-container">
            <h2 class="t-content">ĐA DẠNG LỰA CHỌN VỚI KHÁCH SẠN</h2>
            <div class="tickets" id="tickets-hotels">
            </div>
            <div class="see-more-container">
                <a href="../views/hotels-list.php" class="see-more-link">
                    Xem thêm
                    <span class="arrow"></span>
                </a>
            </div>
        </div>

        <!-- tag-top -->
        <div class="tab-container">
            <div class="tab-header">
                <h2>Bạn muốn khám phá điều gì</h2>
            </div>
            <div class="tabs" id="tab-content">
                <div class="tab active" data-tab="hotels">Các Khách sạn hàng đầu</div>
                <div class="tab" data-tab="activities">Các Tour du lịch hàng đầu</div>
            </div>

            <div class="tab-body">
                <!-- HOTELS -->
                <div class="body active" id="hotels">
                    <?php if ($resulthotels->num_rows > 0): ?>
                        <?php
                        // Lấy tất cả khách sạn vào một mảng để sắp xếp
                        $hotels = [];
                        while ($row = $resulthotels->fetch_assoc()) {
                            // Kiểm tra và gán giá trị mặc định cho "rating" nếu không tồn tại
                            $row['rating'] = isset($row['rating']) ? floatval($row['rating']) : 0;
                            $hotels[] = $row;
                        }

                        // Sắp xếp khách sạn theo rating theo thứ tự giảm dần
                        usort($hotels, function($a, $b) {
                            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
                        });

                        // Giới hạn ở 10 khách sạn hàng đầu
                        $top_hotels = array_slice($hotels, 0, 10);

                        // Hiển thị các khách sạn
                        $count = 0;
                        echo '<ul>';
                        foreach ($top_hotels as $row):
                           // Tạo liên kết động thay vì dùng cột link
                            $tour_link = "hotels-list.php?id=" . htmlspecialchars($row['id']);
                            echo '<li><a href="' . $tour_link . '" class="footer-link">' . htmlspecialchars($row['name']) . '</a></li>';
                            $count++;
                            if ($count % 5 == 0 && $count < count($top_hotels)) {
                                echo '</ul><ul>';
                            }
                        endforeach;
                        echo '</ul>';
                        ?>
                    <?php endif; ?>
                </div>

                <!-- ACTIVITIES -->
                <div class="body" id="activities">
                    <?php if ($resultactivities->num_rows > 0): ?>
                        <?php
                        // Lấy tất cả tour vào một mảng để sắp xếp
                        $tours = [];
                        while ($row = $resultactivities->fetch_assoc()) {
                            // Kiểm tra và gán giá trị mặc định cho "rating" nếu không tồn tại
                            $row['rating'] = isset($row['rating']) ? floatval($row['rating']) : 0;
                            $tours[] = $row;
                        }

                        // Sắp xếp tour theo rating từ cao đến thấp
                        usort($tours, function($a, $b) {
                            return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);
                        });

                        // Giới hạn ở 10 tour hàng đầu
                        $top_tours = array_slice($tours, 0, 10);

                        // Hiển thị các tour
                        $count = 0;
                        echo '<ul>';
                        foreach ($top_tours as $row):
                            // Tạo liên kết động thay vì dùng cột link
                            $tour_link = "tour.php?id=" . htmlspecialchars($row['id']);
                            echo '<li><a href="' . $tour_link . '" class="footer-link">' . htmlspecialchars($row['name']) . '</a></li>';
                            $count++;
                            if ($count % 5 == 0 && $count < count($top_tours)) {
                                echo '</ul><ul>';
                            }
                        endforeach;
                        echo '</ul>';
                        ?>
                    <?php endif; ?>
                </div>

                <!-- send-email-user -->
                <div class="send-notification-container">
                    <div class="send-notification-image">
                        <img src="../public/images/imgaemail.webp" alt="Đăng ký nhận thông báo">
                    </div>
                    <div class="send-notification-content">
                        <h2 class="send-content">ĐỪNG BỎ LỠ CƠ HỘI NHẬN THÔNG BÁO MỚI NHẤT</h2>
                        <p>Đăng ký nhận thông báo để nhận thông tin mới nhất về các chương trình khuyến mãi và ưu đãi đặc biệt từ chúng tôi.</p>
                        <form action="" method="POST" class="send-notification-form">
                            <input class="input-send" type="email" name="email" placeholder="Nhập email của bạn" required>
                            <button class="button-send">
                                <div class="svg-wrapper-1">
                                    <div class="svg-wrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                                            <path fill="none" d="M0 0h24v24H0z"></path>
                                            <path fill="currentColor" d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span>Gửi</span>
                            </button>
                        </form>
                        <?php if ($subscription_message): ?>
                            <div class="subscription-message">
                                <?php echo $subscription_message; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tag lời gợi ý -->
                <div class="suggest-container">
                    <div class="suggest-container-content">
                        <h3>Lý do nên đặt chỗ với TD Touris ?</h3>
                    </div>
                    <div class="suggest-list">
                        <div class="suggest-item">
                            <img class="suggest-image" src="../public/images/item-list.webp" alt="public travis">
                            <div class="suggest-body">
                                <h4>Đáp ứng mọi nhu cầu của bạn</h4>
                                <p>Từ nơi lưu trú và tham quan, bạn có thể tin chọn sản phẩm hoàn chỉnh và Hướng dẫn cụ thể của chúng tôi.</p>
                            </div>
                        </div>
                        <div class="suggest-item">
                            <img class="suggest-image" src="../public/images/suggest-2.webp" alt="public travis">
                            <div class="suggest-body">
                                <h4>Tùy chọn chỗ linh hoạt</h4>
                                <p>Kế hoạch thay đổi bất ngờ? Đừng lo!! Đổi lịch hoạt hoàn tiền dễ dàng.</p>
                            </div>
                        </div>
                        <div class="suggest-item">
                            <img class="suggest-image" src="../public/images/suggest3.webp" alt="public travis">
                            <div class="suggest-body">
                                <h4>Thanh toán an toàn và thuận tiện</h4>
                                <p>Tận hưởng nhiều cách thanh toán an toàn, bằng loại tiền thuận tiện nhất cho bạn.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../includes/footer.php'; ?>
    </div>

    <!-- file script -->
    <script src="../js/get-hotels.js" ></script>
    <script src="../js/get-tour.js" ></script>
    <script src="../js/index-tab.js"></script>
</body>
</html>

<?php
$conn->close();
?>