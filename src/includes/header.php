<?php
// Đảm bảo session đã được khởi động
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ghi log để kiểm tra session
error_log("Session in header.php: " . print_r($_SESSION, true));
?>

<div class="header">
    <div>
        <div class="logo">
            <img src="../public/images/logousave.png">
        </div>
        <div class="menu">
            <ul>
                <li><a href="../views/index.php">Trang Chủ</a></li>
                <li><a href="../views/information.php">Giới Thiệu</a></li>
                <li class="menu-item">
                    <a href="../views/index.php">Dịch Vụ</a>
                    <ul class="sub-menu">
                        <li><a href="../views/tour.php">Tour Du Lịch</a></li>
                        <li><a href="../views/hotels-list.php">Khách Sạn</a></li>
                    </ul>
                </li>
                <li><a href="../includes/contact.php">Liên Hệ</a></li>
                <li><a href="../includes/user-info.php">Thông Tin</a></li>
            </ul>
        </div>
    </div>

    <div class="user-login">
        <?php if (isset($_SESSION['user'])): ?>
            <!-- Hiển thị tên người dùng và nút Đăng Xuất nếu đã đăng nhập -->
            <span id="user-info" style="display: inline; color: #FFFF; margin-right: 1rem;">
                Xin chào, <?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'Người dùng', ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <a href="../views/index.php?logout=true" id="logout" class="logout logout-btn" style="display: inline; text-decoration: none;">
                Đăng Xuất
            </a>
        <?php else: ?>
            <!-- Hiển thị nút Đăng Nhập nếu chưa đăng nhập -->
            <a href="../includes/login.php" class="login-button login-btn">Đăng Nhập</a>
            <span id="user-info" style="display: none;"></span>
            <a href="../views/index.php?logout=true" id="logout" class="logout logout-btn" style="display: none;">
                Đăng Xuất
            </a>
        <?php endif; ?>
    </div>
</div>