<?php
include_once '../config/database.php';

$result = $conn->query("SELECT t.id, t.title, t.image, t.tag, t.is_featured, td.price
                        FROM tours t
                        LEFT JOIN `tour-detail` td ON t.id = td.`id-tour`
                        ORDER BY t.id DESC");

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 fw-semibold">Danh sách tour</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal-insert-tour">
        <i class="fas fa-plus"></i> Thêm tour
    </button>
</div>

<div class="card shadow-sm">

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Tiêu đề</th>
                        <th scope="col">Ảnh đại diện</th>
                        <th scope="col">Tag</th>
                        <th scope="col">Nổi bật</th>
                        <th scope="col">Giá</th>
                        <th scope="col">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td>
                                <?php if ($row['image']): ?>
                                    <img src="../public/images-tour/bg-tickets-tour/<?= htmlspecialchars($row['image']) ?>" alt="Thumbnail" class="img-fluid" style="max-width: 50px; max-height: 50px;">
                                <?php else: ?>
                                    <span class="text-muted">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $tags = json_decode($row['tag'], true) ?: explode(',', $row['tag']);
                                echo htmlspecialchars(implode(', ', array_slice((array)$tags, 0, 2))) . (count((array)$tags) > 2 ? '...' : '');
                                ?>
                            </td>
                            <td>
                                <input type="checkbox" disabled <?= $row['is_featured'] ? 'checked' : '' ?>>
                            </td>
                            <td><?= number_format($row['price'] ?? 0, 0, ',', '.') ?> VND</td>
                            <td>
                              <?php $id = htmlspecialchars($row['id']); ?>
                              <button type="button" class="btn btn-warning btn-sm me-2" onclick="openUpdateModalTour(<?= $id ?>)">
                                <i class="fas fa-edit"></i>
                              </button>
                              <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteTour(<?= $id ?>)">
                                <i class="fas fa-trash"></i>
                              </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>


<?php include_once 'insert-tour-modal.php'; ?>
<?php include_once 'update-tour-modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../js/tour-management.js"></script>

<?php
$action = $_GET['action'] ?? '';
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$message = $_GET['message'] ?? '';

$messages = [
    'insert' => [
        'success' => ['icon' => 'success', 'title' => 'Thành công!', 'text' => 'Tour đã được thêm.'],
        'error' => ['icon' => 'error', 'title' => 'Thất bại!', 'text' => $message ?: 'Không thể thêm tour. Vui lòng thử lại.']
    ],
    'update' => [
        'success' => ['icon' => 'success', 'title' => 'Thành công!', 'text' => 'Tour đã được cập nhật.'],
        'error' => ['icon' => 'error', 'title' => 'Thất bại!', 'text' => $message ?: 'Không thể cập nhật tour. Vui lòng thử lại.']
    ],
    'delete' => [
        'success' => ['icon' => 'success', 'title' => 'Thành công!', 'text' => 'Tour đã được xóa.'],
        'error' => ['icon' => 'error', 'title' => 'Thất bại!', 'text' => $message ?: 'Không thể xóa tour. Vui lòng thử lại.']
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