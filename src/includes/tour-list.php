<?php
// Kết nối CSDL
$conn = new mysqli("localhost", "root", "", "do-an-1-v2");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Truy vấn dữ liệu từ các bảng
$sql = "
    SELECT t.id, t.title, l.name AS location, t.description, t.price, t.duration_days, ti.image_url
    FROM tours t
    LEFT JOIN locations l ON t.location_id = l.id
    LEFT JOIN tour_images ti ON t.id = ti.tour_id
    LIMIT 4
";
$result = $conn->query($sql);
?>

<section class="container mx-auto p-4 mt-10">
    <h2 class="text-2xl font-bold text-center text-blue-900 mb-6">CÁC TOUR NỔI BẬT</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="tourList">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $priceFormatted = number_format($row["price"], 0, ',', '.') . 'đ';
                $isBestOffer = $row["price"] < 300000;
                ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden relative">
                    <!-- Ảnh -->
                    <img src="<?php echo $row["image_url"] ?: 'https://via.placeholder.com/300x200?text=No+Image'; ?>"
                        alt="<?php echo $row["title"]; ?>" class="w-full h-40 object-cover">

                    <!-- Địa điểm -->
                    <div class="absolute top-2 left-2 bg-white text-blue-600 text-xs px-2 py-1 rounded flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                        </svg>
                        <?php echo $row["location"]; ?>
                    </div>

                    <!-- Best Offer (nếu có) -->
                    <?php if ($isBestOffer) { ?>
                        <div class="absolute top-2 right-2 bg-yellow-400 text-black text-xs px-2 py-1 rounded">
                            BEST OFFER
                        </div>
                    <?php } ?>

                    <!-- Nội dung -->
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-blue-900"><?php echo $row["title"]; ?></h3>
                        <div class="flex items-center mt-1">
                            <!-- Đánh giá -->
                            <div class="flex text-yellow-400">
                                <?php for ($i = 0; $i < 5; $i++)
                                    echo '<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>'; ?>
                            </div>
                            <span class="text-gray-600 text-sm ml-1">(3)</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">Chỉ từ <span
                                class="text-black font-semibold"><?php echo $priceFormatted; ?></span></p>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p class='text-center text-gray-600'>Không có tour nào để hiển thị.</p>";
        }
        $conn->close();
        ?>
    </div>
</section>