<<<<<<< HEAD
<?php
session_start();

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Giới thiệu</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet" />
</head>
<body class="flex flex-col min-h-screen">

  <?php include '../includes/header.php'; ?>



  <?php include '../includes/footer.php'; ?>

</body>
</html>
=======
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>TD Tours</title>
    <!-- Load Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white">

    <!-- Header -->
    <?php include '../includes/header.php'; ?>
    <!-- Video Backgroud-->
    <?php include '../includes/video-bg.php'; ?>

    <section class="text-center mt-10 max-w-4xl mx-auto px-4">
        <p class="italic tracking-widest text-sm text-gray-600 mb-2">HÃY ĐỒNG HÀNH CÙNG NHAU NHÉ</p>
        <div class="border-b border-gray-400 w-40 mx-auto mb-4"></div>
        <h2 class="text-3xl font-bold">
            KHÁM PHÁ NGAY CÁC ĐỊA ĐIỂM
            <span class="text-blue-600">NỔI TIẾNG</span>
        </h2>
    </section>
    <section class="relative rounded-3xl overflow-hidden mx-auto max-w-7xl mt-10">
        <!-- Background image -->
        <div class="absolute inset-0">
            <img src="../../public/images/backgroud/backgroud6.0.jpeg" alt="Background"
                class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black bg-opacity-30"></div> <!-- overlay mờ -->
        </div>

        <!-- Nội dung -->
        <div class="relative z-10 text-center text-white py-16 px-4 sm:px-8">
            <h2 class="text-4xl sm:text-5xl font-bold text-blue-600">ĐẶT TOUR NGAY HÔM NAY</h2>
            <p class="mt-4 text-lg sm:text-xl font-medium text-blue-400">
                Đừng bỏ lỡ cơ hội trải nghiệm những điều thú vị nhất tại Phú Quốc
            </p>
            <div class="mt-6">
                <a href="#"
                    class="inline-block bg-blue-700 hover:bg-blue-800 text-white text-lg font-semibold px-8 py-3 rounded-lg shadow-lg transition">
                    Đặt Tour Ngay
                </a>
            </div>
        </div>
    </section>
    <?php include '../includes/tour-list.php'; ?>



</body>

</html>
>>>>>>> 9342825c8f4a436e585733488e997242e771f6f3
