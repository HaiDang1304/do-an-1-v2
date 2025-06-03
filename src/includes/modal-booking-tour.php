<!-- Modal Đặt Tour -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bookingModalLabel">Đặt Tour: <?= htmlspecialchars($title) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="bookingForm" method="POST" action="../includes/process-booking-tour.php">
                    <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                    <input type="hidden" name="tour_price" value="<?= $tour['price'] ?>">

                    <!-- Thông tin khách hàng -->
                    <?php if (isset($_SESSION['user']['id'])): ?>
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Họ và tên</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" readonly>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <label for="full_name" class="form-label fw-bold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" required placeholder="Nhập họ và tên">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="Nhập email">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" required placeholder="Nhập số điện thoại">
                    </div>

                    <!-- Số lượng người -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số lượng người <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <label for="adults" class="form-label">Người lớn</label>
                                <input type="number" class="form-control" id="adults" name="adults" min="1" value="1" required>
                            </div>
                            <div>
                                <label for="children" class="form-label">Trẻ em</label>
                                <input type="number" class="form-control" id="children" name="children" min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Ngày khởi hành -->
                    <div class="mb-3">
                        <label for="departure_date" class="form-label fw-bold">Ngày khởi hành <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="departure_date" name="departure_date" required>
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Nhập ghi chú (nếu có)"></textarea>
                    </div>

                    <!-- Tổng giá -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tổng giá:</label>
                        <h5 class="text-danger" id="total_price">0 VND</h5>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Xác nhận đặt tour</button>
                </form>
            </div>
        </div>
    </div>
</div>