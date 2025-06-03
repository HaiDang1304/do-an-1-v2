<?php
session_start();
include "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tour_id = (int)$_POST['tour_id'];
    $user_id = (int)$_POST['user_id'];
    $rating = (float)$_POST['rating'];
    $review_text = mysqli_real_escape_string($conn, $_POST['review_text']);

    // Kiểm tra dữ liệu hợp lệ
    if ($rating < 0 || $rating > 10) {
        die(json_encode(['success' => false, 'message' => 'Điểm đánh giá phải từ 0 đến 10.']));
    }
    if (empty($review_text)) {
        die(json_encode(['success' => false, 'message' => 'Vui lòng nhập nội dung đánh giá.']));
    }

    // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
    mysqli_begin_transaction($conn);

    try {
        // Lưu đánh giá vào bảng tour_reviews
        $sql = "INSERT INTO tour_reviews (tour_id, user_id, rating, review_text, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'iids', $tour_id, $user_id, $rating, $review_text);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Lỗi khi gửi đánh giá: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);

        // Tính lại rating trung bình và số lượng review
        $reviews_sql = "SELECT rating FROM tour_reviews WHERE tour_id = ?";
        $reviews_stmt = mysqli_prepare($conn, $reviews_sql);
        mysqli_stmt_bind_param($reviews_stmt, 'i', $tour_id);
        mysqli_stmt_execute($reviews_stmt);
        $reviews_result = mysqli_stmt_get_result($reviews_stmt);
        $total_rating = 0;
        $review_count = mysqli_num_rows($reviews_result);
        while ($row = mysqli_fetch_assoc($reviews_result)) {
            $total_rating += $row['rating'];
        }
        $average_rating = $review_count > 0 ? round($total_rating / $review_count, 1) : 0;
        mysqli_stmt_close($reviews_stmt);

        // Cập nhật rating và review vào bảng tour-detail
        $update_sql = "UPDATE `tour-detail` SET rating = ?, review = ? WHERE `id-tour` = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        if (!$update_stmt) {
            throw new Exception("Lỗi chuẩn bị câu lệnh cập nhật: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($update_stmt, 'dii', $average_rating, $review_count, $tour_id);
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("Lỗi khi cập nhật rating: " . mysqli_stmt_error($update_stmt));
        }
        mysqli_stmt_close($update_stmt);

        // Commit transaction
        mysqli_commit($conn);

        // Chuyển hướng về tour-detail với thông báo thành công
        header("Location: tour-detail.php?id=$tour_id&review_success=1");
        exit;
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ.']);
}

mysqli_close($conn);
?>