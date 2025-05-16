<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_log("Session in header.php: " . print_r($_SESSION, true));
?>

<header class="bg-gray-900 text-white shadow">
  <div class="container mx-auto flex items-center justify-between py-4 px-6">
    <!-- Logo -->
    <a href="../php/index.php" class="flex items-center space-x-4">
      <img src="../public/images/images/logousave.png" alt="Logo" class="h-10 w-auto object-contain" />
    </a>

    <!-- Menu -->
    <nav>
      <ul class="flex space-x-6 text-sm font-semibold">
        <li><a href="../php/index.php" class="hover:text-yellow-400 transition">Trang Chủ</a></li>
        <li><a href="../php/gioithieu.php" class="hover:text-yellow-400 transition">Giới Thiệu</a></li>
        <li><a href="../php/dichvu.php" class="hover:text-yellow-400 transition">Dịch Vụ</a></li>
        <li><a href="../php/lienhe.php" class="hover:text-yellow-400 transition">Liên Hệ</a></li>
        <li><a href="../php/user_info.php" class="hover:text-yellow-400 transition">Thông Tin</a></li>
        <li><a href="../php/danhgia.php" class="hover:text-yellow-400 transition">Đánh Giá</a></li>
      </ul>
    </nav>

    <!-- User Login -->
    <div class="flex items-center space-x-4">
      <?php if (isset($_SESSION['user'])): ?>
        <span class="text-white">
          Xin chào, <strong><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'Người dùng', ENT_QUOTES, 'UTF-8'); ?></strong>
        </span>
        <a href="../php/index.php?logout=true"
           id="logout"
           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition"
        >
          Đăng Xuất
        </a>
      <?php else: ?>
        <a href="../php/login.php"
           class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-4 py-2 rounded font-semibold transition"
        >
          Đăng Nhập
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>
