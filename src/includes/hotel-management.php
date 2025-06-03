<?php
include_once '../config/database.php';

// Truy vấn lấy dữ liệu từ bảng hotels và hotels_detail
$result = $conn->query("SELECT h.id, h.name, h.image, h.tags, h.price, h.location, h.rating, h.reviews, h.start, hd.description
                        FROM hotels h
                        LEFT JOIN hotels_detail hd ON h.id = hd.id_hotels
                        ORDER BY h.id DESC");

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold">Danh sách khách sạn</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-insert-hotel">
        <i class="fas fa-plus"></i> Thêm khách sạn
    </button>
</div>

<div class="card shadow-sm">
    
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tên khách sạn</th>
                        <th scope="col">Ảnh đại diện</th>
                        <th scope="col">Tags</th>
                        <th scope="col">Giá (VND)</th>
                        <th scope="col">Mô tả</th>
                        <th scope="col">Vị trí</th>
                        <th scope="col">Sao</th>
                        <th scope="col">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="../public/images-hotel/bg-tickets/<?= htmlspecialchars($row['image']) ?>" alt="Thumbnail" class="img-fluid" style="max-width: 50px; max-height: 50px;">
                                <?php else: ?>
                                    <span class="text-muted">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $tags = json_decode($row['tags'], true) ?: explode(',', $row['tags']);
                                echo htmlspecialchars(implode(', ', array_slice((array)$tags, 0, 2))) . (count((array)$tags) > 2 ? '...' : '');
                                ?>
                            </td>
                            <td><?= number_format($row['price'], 0, ',', '.') ?> VND</td>
                            <td><?= htmlspecialchars(substr($row['description'], 0, 50)) . (strlen($row['description']) > 50 ? '...' : '') ?></td>
                            <td><?= htmlspecialchars($row['location']) ?></td>
                            <td><?= htmlspecialchars($row['start']) ?> sao</td>
                            <td>
                                <?php $id = htmlspecialchars($row['id']); ?>
                                <button type="button" class="btn btn-warning btn-sm me-2" onclick="openUpdateModalHotel(<?= $id ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteHotel(<?= $id ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


<?php include_once 'insert-hotel-modal.php'; ?>
<?php include_once 'update-hotel-modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/hotel-management.js"></script>

<?php
$action = $_GET['action'] ?? '';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$message = $_GET['message'] ?? '';

$messages = [
    'insert' => [
        'success' => ['icon' => 'success', 'title' => 'Thành công!', 'text' => 'Khách sạn đã được thêm.'],
        'error' => ['icon' => 'error', 'title' => 'Thất bại!', 'text' => $message ?: 'Không thể thêm khách sạn. Vui lòng thử lại.']
    ],
    'update' => [
        'success' => ['icon' => 'success', 'title' => 'Thành công!', 'text' => 'Khách sạn đã được cập nhật.'],
        'error' => ['icon' => 'error', 'title' => 'Thất bại!', 'text' => $message ?: 'Không thể cập nhật khách sạn. Vui lòng thử lại.']
    ],
    'delete' => [
        'success' => ['icon' => 'success', 'title' => 'Thành công!', 'text' => 'Khách sạn đã được xóa.'],
        'error' => ['icon' => 'error', 'title' => 'Thất bại!', 'text' => $message ?: 'Không thể xóa khách sạn. Vui lòng thử lại.']
    ]
];

if (isset($messages[$action]) && $action !== 'delete') {
    if ($success === '1') {
        $msg = $messages[$action]['success'];
    } elseif ($error === '1') {
        $msg = $messages[$action]['error'];
    }

    if (isset($msg)) {
        $text = json_encode($msg['text'], JSON_HEX_QUOT | JSON_HEX_APOS);
        echo "<script>
            Swal.fire({
                icon: '{$msg['icon']}',
                title: '{$msg['title']}',
                text: {$text},
                timer: 2000,
                showConfirmButton: true
            });
        </script>";
    }
}
?>

<script>
if (window.history.replaceState) {
    const url = new URL(window.location.href);
    url.searchParams.delete('success');
    url.searchParams.delete('error');
    url.searchParams.delete('action');
    url.searchParams.delete('message');
    window.history.replaceState({}, document.title, url.toString());
}
</script>