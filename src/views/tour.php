<?php
include "../config/database.php";
session_start();

$conn = (new Database())->getConnection();
if (!$conn) {
    die("Kết nối cơ sở dữ liệu thất bại.");
}

// Lấy filter từ GET
$tour_name = isset($_GET['tour_name']) ? trim($_GET['tour_name']) : '';
$tags = isset($_GET['tags']) ? (array)$_GET['tags'] : [];

// Query lấy tour với ảnh đại diện
$sql = "
SELECT t.*, ti.image_url
FROM tours t
LEFT JOIN (
    SELECT tour_id, image_url
    FROM tour_images
    WHERE id IN (
        SELECT MIN(id) FROM tour_images GROUP BY tour_id
    )
) ti ON t.id = ti.tour_id
WHERE 1
";

if ($tour_name !== '') {
    $safe_name = $conn->real_escape_string($tour_name);
    $sql .= " AND (t.title LIKE '%$safe_name%' OR t.description LIKE '%$safe_name%')";
}

if (!empty($tags)) {
    foreach ($tags as $tag) {
        $safe_tag = $conn->real_escape_string($tag);
        $sql .= " AND t.tag LIKE '%$safe_tag%'";
    }
}

$sql .= " ORDER BY t.id ASC";

$result = $conn->query($sql);
if (!$result) {
    die("Lỗi truy vấn SQL: " . $conn->error);
}

// Lấy tag_counts cho phần lọc
$tag_counts = [];
$tag_query = "SELECT tag FROM tours";
$tag_result = $conn->query($tag_query);
$all_tags = [];
while ($row = $tag_result->fetch_assoc()) {
    $tags_array = json_decode($row['tag'], true);
    if (is_array($tags_array)) {
        foreach ($tags_array as $tag) {
            $all_tags[] = $tag;
        }
    }
}
$tag_counts = array_count_values($all_tags);
?>

<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Danh sách Tour - TD Touris</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 flex flex-col">

<?php include '../includes/header.php'; ?>

<main class="flex-grow container mx-auto px-6 py-10 flex gap-8">

  <!-- Phần filter bên trái -->
  <?php include '../includes/filter.php'; ?>

  <!-- Danh sách tour bên phải -->
<section class="flex-grow flex flex-col gap-6">

<?php if ($result->num_rows > 0): ?>
  <?php while ($tour = $result->fetch_assoc()): ?>
    <?php
    $tags = json_decode($tour['tag'], true);
    ?>
    <div class="bg-white rounded-lg shadow p-3 flex gap-4 border border-gray-200">
      <div class="relative w-40 h-32 rounded overflow-hidden flex-shrink-0">

        <?php if (!empty($tour['image_url'])): ?>
          <img src="../public/images/images-tour/<?= htmlspecialchars($tour['image_url']) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="w-full h-full object-cover" />
        <?php else: ?>
          <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400 text-sm">Không có ảnh</div>
        <?php endif; ?>

        <!-- Tag label màu cam góc trái ảnh -->
        <div class="absolute top-2 left-2 flex flex-col space-y-1">
          <?php
          if (is_array($tags)) {
            foreach ($tags as $tag) {
              echo '<span class="bg-orange-400 text-white text-xs font-semibold rounded px-2 py-0.5">' . htmlspecialchars($tag) . '</span>';
            }
          }
          ?>
        </div>
      </div>

      <div class="flex flex-col justify-between flex-grow">
        <h3 class="text-blue-700 font-bold text-lg leading-snug mb-1"><?= htmlspecialchars($tour['title']) ?></h3>

        <div class="flex items-center mb-2 space-x-3">
          <!-- Đánh giá số + text -->
          <span class="bg-green-700 text-white rounded px-2 text-xs font-semibold">
            <?= number_format($tour['rating'], 1) ?>
            <?= $tour['rating'] >= 9 ? 'Tuyệt vời' : ($tour['rating'] >= 8 ? 'Rất tốt' : 'Tốt') ?>
          </span>

          <span class="text-gray-600 text-sm">
            | <?= intval($tour['review']) ?> đánh giá
          </span>
        </div>

        <!-- Tags nút xanh nhỏ dưới -->
        <div class="mb-3 flex flex-wrap gap-2">
          <?php
          if (is_array($tags)) {
            foreach ($tags as $tag) {
              echo '<span class="bg-blue-600 text-white text-xs font-semibold rounded px-2 py-0.5">' . htmlspecialchars($tag) . '</span>';
            }
          }
          ?>
        </div>

        <div class="text-center border-l border-gray-200 pl-4 flex flex-col justify-center min-w-[130px]">
          <div class="text-green-700 font-semibold mb-1">Giá chỉ từ</div>
          <div class="text-red-600 font-bold text-xl"><?= number_format($tour['price'], 0, ',', '.') ?> VND</div>
        </div>

      </div>
    </div>
  <?php endwhile; ?>
<?php else: ?>
  <p class="text-center text-gray-500">Không tìm thấy tour nào phù hợp.</p>
<?php endif; ?>

</section>


</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>

<?php
$conn->close();
?>
