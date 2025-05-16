<<<<<<< HEAD
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
=======
<!-- header.php -->
<header class="fixed top-0 left-0 w-full z-50 bg-gray-500 bg-opacity-70 backdrop-blur-md shadow-md">
    <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between">
        <!-- Logo + menu -->
        <div class="flex items-center space-x-6">
            <!-- Logo -->
            <a href="#">
                <img src="../../public/images/logo/LogoTD2.webp" alt="TD Tours Logo" class="h-10 w-auto" />
            </a>

            <!-- Menu -->
            <nav>
                <ul class="hidden md:flex space-x-6 text-gray-800 font-medium">
                    <li><a href="#" class="hover:text-sky-600 hover:underline underline-offset-4 transition duration-200">Trang Chủ</a></li>
                    <li><a href="#" class="hover:text-sky-600 hover:underline underline-offset-4 transition duration-200">Giới Thiệu</a></li>
                    <li><a href="#" class="hover:text-sky-600 hover:underline underline-offset-4 transition duration-200">Dịch Vụ</a></li>
                    <li><a href="#" class="hover:text-sky-600 hover:underline underline-offset-4 transition duration-200">Liên Hệ</a></li>
                    <li><a href="#" class="hover:text-sky-600 hover:underline underline-offset-4 transition duration-200">Thông Tin</a></li>
                    <li><a href="#" class="hover:text-sky-600 hover:underline underline-offset-4 transition duration-200">Đánh Giá</a></li>
                </ul>
            </nav>
        </div>

        <!-- Nút đăng nhập -->
        <div>
            <a href="#" class="bg-sky-600 text-white font-semibold px-5 py-2 rounded-lg shadow hover:bg-sky-700 transition duration-200">
                Đăng Nhập
            </a>
        </div>
    </div>
>>>>>>> 9342825c8f4a436e585733488e997242e771f6f3
</header>
