<?php
include "../config/database.php"; // Kết nối database
session_start();

// Truy vấn dữ liệu từ bảng tours
$sql_tours = "
  SELECT 
    `id`, 
    `title`, 
    `image` 
  FROM `tours`
  ORDER BY RAND()
  LIMIT 8
";
$result_tours = $conn->query($sql_tours);
if ($result_tours === false) {
    echo "Lỗi truy vấn tours: " . $conn->error;
    exit;
}
// Lưu kết quả vào mảng
$tours = [];
while ($row = $result_tours->fetch_assoc()) {
    $tours[] = $row;
}

// Truy vấn 6 khách sạn ngẫu nhiên từ bảng hotels
$sql_hotels = "
  SELECT 
    `id`, 
    `name`, 
    `image` 
  FROM `hotels`
  ORDER BY RAND()
  LIMIT 6
";
$result_hotels = $conn->query($sql_hotels);
if ($result_hotels === false) {
    echo "Lỗi truy vấn hotels: " . $conn->error;
    exit;
}
// Lưu kết quả vào mảng
$hotels = [];
while ($row = $result_hotels->fetch_assoc()) {
    $hotels[] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám phá Việt Nam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="../css/information.css">
    <link rel="stylesheet" href="../css/doan.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>

   
    <div class="foot-container">
        <h2 class="foot-title">Các địa điểm du lịch nổi bật</h2>
        <p class="foot-description">
            Những Tour được yêu thích nhất trong tháng
        </p>
        <div class="foot-grid">
            <?php foreach ($tours as $tour): ?>
                <div class="foot-card card bg-dark text-white overflow-hidden border-0 shadow-sm">
                    <img src="../public/images-tour/bg-tickets-tour/<?php echo htmlspecialchars($tour['image']); ?>" 
                         class="card-img" 
                         alt="<?php echo htmlspecialchars($tour['title']); ?>">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-3 bg-gradient-overlay">
                        <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Nơi ở Section -->
    <div class="living-container">
        <h2 class="living-title">Nơi ở đặc biệt</h2>
        <p class="living-description">
            Trải nghiệm những nơi ở độc đáo và ấn tượng
        </p>
        <div class="living-grid">
            <?php foreach ($hotels as $hotel): ?>
                <div class="living-space-card card bg-dark text-white overflow-hidden border-0 shadow-sm">
                    <img src="../public/images-hotel/bg-tickets/<?php echo htmlspecialchars($hotel['image']); ?>" 
                         class="card-img" 
                         alt="<?php echo htmlspecialchars($hotel['name']); ?>">
                    <div class="card-img-overlay d-flex flex-column justify-content-end p-3 bg-gradient-overlay">
                        <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>

<?php $conn->close(); ?>