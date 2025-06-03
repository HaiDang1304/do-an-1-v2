<?php
include_once '../config/database.php';

// Hàm tính doanh thu theo khoảng thời gian
function getRevenueByPeriod($conn, $table, $period, $statusCheck = true) {
    $sql = "SELECT ";
    
    if ($period === 'day') {
        $sql .= "DATE(created_at) as period, ";
    } elseif ($period === 'week') {
        $sql .= "YEARWEEK(created_at, 1) as period, ";
    } elseif ($period === 'month') {
        $sql .= "DATE_FORMAT(created_at, '%Y-%m') as period, ";
    }
    
    $sql .= "SUM(total_price) as total FROM `$table`";
    
    if ($statusCheck) {
        $checkSql = "SHOW COLUMNS FROM `$table` LIKE 'status'";
        $checkResult = $conn->query($checkSql);
        if ($checkResult->num_rows > 0) {
            $sql .= " WHERE status = 'confirmed'";
        }
    }
    
    if ($period === 'day') {
        $sql .= " GROUP BY DATE(created_at)";
    } elseif ($period === 'week') {
        $sql .= " GROUP BY YEARWEEK(created_at, 1)";
    } elseif ($period === 'month') {
        $sql .= " GROUP BY DATE_FORMAT(created_at, '%Y-%m')";
    }
    
    $sql .= " ORDER BY period DESC LIMIT 7";
    
    $result = $conn->query($sql);
    if ($result === false) {
        error_log("SQL Error ($period): " . $conn->error);
        return [];
    }
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Hàm tính tổng doanh thu
function getTotalRevenue($conn, $table) {
    $checkSql = "SHOW COLUMNS FROM `$table` LIKE 'status'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        $sql = "SELECT SUM(total_price) as total FROM `$table` WHERE status = 'confirmed'";
    } else {
        $sql = "SELECT SUM(total_price) as total FROM `$table`";
    }

    $result = $conn->query($sql);
    if ($result === false) {
        error_log("SQL Error: " . $conn->error);
        return 0;
    }
    $row = $result->fetch_assoc();
    return $row['total'] ? $row['total'] : 0;
}

// Lấy dữ liệu doanh thu
$tourRevenue = getTotalRevenue($conn, 'bookings-tour');
$hotelRevenue = getTotalRevenue($conn, 'bookings');
$totalRevenue = $tourRevenue + $hotelRevenue;

// Lấy dữ liệu theo khoảng thời gian
$tourDaily = getRevenueByPeriod($conn, 'bookings-tour', 'day');
$tourWeekly = getRevenueByPeriod($conn, 'bookings-tour', 'week');
$tourMonthly = getRevenueByPeriod($conn, 'bookings-tour', 'month');
$hotelDaily = getRevenueByPeriod($conn, 'bookings', 'day');
$hotelWeekly = getRevenueByPeriod($conn, 'bookings', 'week');
$hotelMonthly = getRevenueByPeriod($conn, 'bookings', 'month');

// Lấy chi tiết giao dịch tour
$tourCheckSql = "SHOW COLUMNS FROM `bookings-tour` LIKE 'status'";
$tourCheckResult = $conn->query($tourCheckSql);
if ($tourCheckResult->num_rows > 0) {
    $tourSql = "SELECT id, tour_name, full_name, created_at, adults, children, total_price FROM `bookings-tour` WHERE status = 'confirmed' ORDER BY created_at DESC";
} else {
    $tourSql = "SELECT id, tour_name, full_name, created_at, adults, children, total_price FROM `bookings-tour` ORDER BY created_at DESC";
}
$tourResult = $conn->query($tourSql);
if ($tourResult === false) {
    error_log("SQL Error (Tour Details): " . $conn->error);
}

// Lấy chi tiết giao dịch khách sạn
$hotelCheckSql = "SHOW COLUMNS FROM `bookings` LIKE 'status'";
$hotelCheckResult = $conn->query($hotelCheckSql);
if ($hotelCheckResult->num_rows > 0) {
    $hotelSql = "SELECT id, hotel_name, name, created_at, guests, total_price FROM `bookings` WHERE status = 'confirmed' ORDER BY created_at DESC";
} else {
    $hotelSql = "SELECT id, hotel_name, name, created_at, guests, total_price FROM `bookings` ORDER BY created_at DESC";
}
$hotelResult = $conn->query($hotelSql);
if ($hotelResult === false) {
    error_log("SQL Error (Hotel Details): " . $conn->error);
}
?>

<div class="container">
    <h1 class="text-center mb-4">Quản lý Doanh thu</h1>
    
    <!-- Tổng quan doanh thu -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Doanh thu Tour</h5>
                    <p class="card-text"><?php echo number_format($tourRevenue, 0, ',', '.') . ' VNĐ'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Doanh thu Khách sạn</h5>
                    <p class="card-text"><?php echo number_format($hotelRevenue, 0, ',', '.') . ' VNĐ'; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Tổng doanh thu</h5>
                    <p class="card-text"><?php echo number_format($totalRevenue, 0, ',', '.') . ' VNĐ'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="row mb-5">
        <div class="col-md-12">
            <h3>Biểu đồ doanh thu</h3>
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link active" id="daily-tab" data-bs-toggle="tab" href="#daily">Theo ngày</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="weekly-tab" data-bs-toggle="tab" href="#weekly">Theo tuần</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="monthly-tab" data-bs-toggle="tab" href="#monthly">Theo tháng</a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="daily">
                    <canvas id="dailyRevenueChart" height="100"></canvas>
                </div>
                <div class="tab-pane fade" id="weekly">
                    <canvas id="weeklyRevenueChart" height="100"></canvas>
                </div>
                <div class="tab-pane fade" id="monthly">
                    <canvas id="monthlyRevenueChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chi tiết giao dịch tour -->
    <div class="section mb-4">
        <h2 class="mb-3">Chi tiết đặt Tour</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Tour</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Người lớn</th>
                        <th>Trẻ em</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($tourResult && $tourResult->num_rows > 0) { ?>
                        <?php while($row = $tourResult->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['tour_name']; ?></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td><?php echo $row['adults']; ?></td>
                            <td><?php echo $row['children'] ?: 0; ?></td>
                            <td><?php echo number_format($row['total_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="7" class="text-center">Không có dữ liệu đặt tour.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chi tiết giao dịch khách sạn -->
    <div class="section">
        <h2 class="mb-3">Chi tiết đặt Khách sạn</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Khách sạn</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Số khách</th>
                        <th>Tổng tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($hotelResult && $hotelResult->num_rows > 0) { ?>
                        <?php while($row = $hotelResult->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['hotel_name']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td><?php echo $row['guests']; ?></td>
                            <td><?php echo number_format($row['total_price'], 0, ',', '.') . ' VNĐ'; ?></td>
                        </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="text-center">Không có dữ liệu đặt khách sạn.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Thêm Chart.js và Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Dữ liệu cho biểu đồ
const dailyData = {
    labels: [<?php echo "'" . implode("','", array_column($tourDaily, 'period')) . "'"; ?>],
    datasets: [
        {
            label: 'Tour',
            data: [<?php echo implode(',', array_column($tourDaily, 'total')); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        },
        {
            label: 'Khách sạn',
            data: [<?php echo implode(',', array_column($hotelDaily, 'total')); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }
    ]
};

const weeklyData = {
    labels: [<?php echo "'" . implode("','", array_column($tourWeekly, 'period')) . "'"; ?>],
    datasets: [
        {
            label: 'Tour',
            data: [<?php echo implode(',', array_column($tourWeekly, 'total')); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        },
        {
            label: 'Khách sạn',
            data: [<?php echo implode(',', array_column($hotelWeekly, 'total')); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }
    ]
};

const monthlyData = {
    labels: [<?php echo "'" . implode("','", array_column($tourMonthly, 'period')) . "'"; ?>],
    datasets: [
        {
            label: 'Tour',
            data: [<?php echo implode(',', array_column($tourMonthly, 'total')); ?>],
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        },
        {
            label: 'Khách sạn',
            data: [<?php echo implode(',', array_column($hotelMonthly, 'total')); ?>],
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }
    ]
};

// Cấu hình chung cho biểu đồ
const chartOptions = {
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return value.toLocaleString('vi-VN') + ' VNĐ';
                }
            }
        }
    },
    plugins: {
        legend: {
            display: true,
            position: 'top'
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString('vi-VN') + ' VNĐ';
                }
            }
        }
    }
};

// Khởi tạo biểu đồ
const dailyChart = new Chart(document.getElementById('dailyRevenueChart').getContext('2d'), {
    type: 'bar',
    data: dailyData,
    options: chartOptions
});

const weeklyChart = new Chart(document.getElementById('weeklyRevenueChart').getContext('2d'), {
    type: 'bar',
    data: weeklyData,
    options: chartOptions
});

const monthlyChart = new Chart(document.getElementById('monthlyRevenueChart').getContext('2d'), {
    type: 'bar',
    data: monthlyData,
    options: chartOptions
});
</script>