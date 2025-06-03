<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Kiểm tra đăng nhập người dùng
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Kiểm tra kết nối database
if ($conn->connect_error) {
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
if ($stmt === false) {
    die("Lỗi prepare (users): " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy lịch sử đặt khách sạn với tên loại phòng
$stmt = $conn->prepare("
    SELECT b.*, rt.name AS room_type_name 
    FROM bookings b 
    LEFT JOIN room_types rt ON b.room_type_id = rt.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
if ($stmt === false) {
    die("Lỗi prepare (bookings): " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
$stmt->close();

// Lấy lịch sử đặt tour
$stmt = $conn->prepare("
    SELECT bt.id, bt.tour_id, bt.full_name, bt.email, bt.phone, bt.adults, bt.children, 
           bt.departure_date, bt.notes, bt.total_price, bt.created_at
    FROM `bookings-tour` bt 
    WHERE bt.user_id = ? 
    ORDER BY bt.created_at DESC
");
if ($stmt === false) {
    die("Lỗi prepare (bookings-tour): " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings_tour = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông Tin Người Dùng</title>
    <link rel="stylesheet" href="../css/doan.css">
    <link rel="stylesheet" href="../css/user_info.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .user-info-card {
            display: flex;
            align-items: center;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background-color: #007bff;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-right: 20px;
        }

        .booking-card,
        .tour-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-details {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-details:hover {
            background-color: #0056b3;
        }

        .modal-body ul {
            list-style-type: none;
            padding-left: 0;
        }

        .modal-body ul li {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <h1 class="mb-4 text-center text-primary">Thông Tin Người Dùng</h1>

        <!-- Thông tin người dùng -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="user-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-4"
                    style="width: 80px; height: 80px; font-size: 32px;">
                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                </div>
                <div>
                    <h4 class="card-title mb-1"><?= htmlspecialchars($user['username']) ?></h4>
                    <p class="mb-1"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                    <p class="mb-1">
                        <i class="fas fa-sign-in-alt"></i>
                        <span class="badge bg-info"><?= htmlspecialchars($user['login_type']) ?></span>
                    </p>
                    <p class="mb-0"><i class="fas fa-calendar-alt"></i> Ngày tạo:
                        <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Lịch sử đặt khách sạn -->
        <div class="mb-5">
            <h3 class="text-secondary mb-3">Lịch Sử Đặt Khách Sạn</h3>
            <?php if ($bookings->num_rows > 0): ?>
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Đặt phòng #<?= $booking['id'] ?></h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Khách sạn:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></p>
                                    <p><strong>Loại phòng:</strong> <?= htmlspecialchars($booking['room_type_name']) ?></p>
                                    <p><strong>Số phòng:</strong> <?= $booking['room_number'] ?> | <strong>Giá:</strong>
                                        <?= number_format($booking['total_price'], 0, ',', '.') ?> VND</p>
                                    <p><strong>Khách:</strong> <?= $booking['guests'] ?> người</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Nhận phòng:</strong> <?= $booking['checkin'] ?></p>
                                    <p><strong>Trả phòng:</strong> <?= $booking['checkout'] ?></p>
                                    <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?>
                                    </p>
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#bookingDetailModal<?= $booking['id'] ?>">Xem chi tiết</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="bookingDetailModal<?= $booking['id'] ?>" tabindex="-1"
                        aria-labelledby="bookingDetailModalLabel<?= $booking['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="bookingDetailModalLabel<?= $booking['id'] ?>">Chi tiết đặt phòng
                                        #<?= $booking['id'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <ul>
                                        <li><strong>Mã đặt phòng:</strong> <?= $booking['id'] ?></li>
                                        <li><strong>Khách sạn:</strong> <?= htmlspecialchars($booking['hotel_name']) ?></li>
                                        <li><strong>Số phòng:</strong> <?= $booking['room_number'] ?></li>
                                        <li><strong>Giá:</strong> <?= number_format($booking['total_price'], 0, ',', '.') ?> VND</li>
                                        <li><strong>Ngày nhận phòng:</strong> <?= $booking['checkin'] ?></li>
                                        <li><strong>Ngày trả phòng:</strong> <?= $booking['checkout'] ?></li>
                                        <li><strong>Số khách:</strong> <?= $booking['guests'] ?></li>
                                        <li><strong>Loại phòng:</strong> <?= htmlspecialchars($booking['room_type_name']) ?>
                                        </li>
                                        <li><strong>Tên khách:</strong> <?= htmlspecialchars($booking['name']) ?></li>
                                        <li><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></li>
                                        <li><strong>Số điện thoại:</strong> <?= htmlspecialchars($booking['phone']) ?></li>
                                        <li><strong>Ghi chú:</strong>
                                            <?= htmlspecialchars($booking['notes'] ?? 'Không có ghi chú') ?></li>
                                        <li><strong>Ngày đặt:</strong>
                                            <?= date('d/m/Y H:i', strtotime($booking['created_at'])) ?></li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center">Bạn chưa có lịch sử đặt khách sạn nào.</div>
            <?php endif; ?>
        </div>

        <!-- Lịch sử đặt tour -->
        <div>
            <h3 class="text-secondary mb-3">Lịch Sử Đặt Tour</h3>
            <?php if ($bookings_tour->num_rows > 0): ?>
                <?php while ($tour = $bookings_tour->fetch_assoc()): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-success">Đặt tour #<?= $tour['id'] ?></h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Mã tour:</strong> <?= htmlspecialchars($tour['tour_id']) ?></p>
                                    <p><strong>Giá:</strong> <?= number_format($tour['total_price'], 0, ',', '.') ?> VND</p>
                                    <p><strong>Ngày khởi hành:</strong> <?= $tour['departure_date'] ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Người lớn:</strong> <?= $tour['adults'] ?> | <strong>Trẻ em:</strong>
                                        <?= $tour['children'] ?></p>
                                    <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($tour['created_at'])) ?></p>
                                    <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#tourDetailModal<?= $tour['id'] ?>">Xem chi tiết</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="tourDetailModal<?= $tour['id'] ?>" tabindex="-1"
                        aria-labelledby="tourDetailModalLabel<?= $tour['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="tourDetailModalLabel<?= $tour['id'] ?>">Chi tiết đặt tour
                                        #<?= $tour['id'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <ul>
                                        <li><strong>Mã đặt tour:</strong> <?= $tour['id'] ?></li>
                                        <li><strong>Mã tour:</strong> <?= htmlspecialchars($tour['tour_id']) ?></li>
                                        <li><strong>Giá:</strong> <?= number_format($tour['total_price'], 0, ',', '.') ?> VND
                                        </li>
                                        <li><strong>Ngày khởi hành:</strong> <?= $tour['departure_date'] ?></li>
                                        <li><strong>Số người lớn:</strong> <?= $tour['adults'] ?></li>
                                        <li><strong>Số trẻ em:</strong> <?= $tour['children'] ?></li>
                                        <li><strong>Tên khách:</strong> <?= htmlspecialchars($tour['full_name']) ?></li>
                                        <li><strong>Email:</strong> <?= htmlspecialchars($tour['email']) ?></li>
                                        <li><strong>Số điện thoại:</strong> <?= htmlspecialchars($tour['phone']) ?></li>
                                        <li><strong>Ghi chú:</strong>
                                            <?= htmlspecialchars($tour['notes'] ?? 'Không có ghi chú') ?></li>
                                        <li><strong>Ngày đặt:</strong>
                                            <?= date('d/m/Y H:i', strtotime($tour['created_at'])) ?></li>
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-warning text-center">Bạn chưa có lịch sử đặt tour nào.</div>
            <?php endif; ?>
        </div>
    </div>


    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>