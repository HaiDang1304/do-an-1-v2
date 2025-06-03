
<?php
session_start();


if (!isset($_SESSION['user']) || $_SESSION['user']['login_type'] !== 'admin') {
    header('Location: ../includes/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bảng điều khiển Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="min-vh-100 bg-light">
  <div class="d-flex">
    <?php include_once '../includes/sidebar-admin.php'; ?>
    <main class="flex-grow-1 p-4">
      <?php
      $type = isset($_GET['type']) ? $_GET['type'] : '';
      switch ($type) {
        case 'tour-management':
          include_once '../includes/tour-management.php';
          break;
        case 'hotel-management':
          include_once '../includes/hotel-management.php';
          break;
        case 'revenue-management':
          include_once '../includes/revenue-management.php';
          break;
        case 'message-management':
          include_once '../includes/message-management.php';
          break;
        default:
          echo "<p class='p-4 text-muted'>Chọn một chức năng quản lý từ sidebar.</p>";
          break;
      }
      ?>
    </main>
  </div>
</body>
</html>
