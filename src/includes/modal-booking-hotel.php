    <!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="bookingModalLabel">
                    Đặt phòng tại <?= htmlspecialchars($hotel['name']) ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form id="bookingForm" method="POST">
                    <!-- Hidden Inputs -->
                    <input type="hidden" name="hotel_id" value="<?= $id ?>">
                    <input type="hidden" name="hotel_name" value="<?= htmlspecialchars($hotel['name']) ?>">
                    <input type="hidden" name="price" value="<?= $hotel['price'] ?>">

                    <!-- Thông tin khách hàng -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Thông tin khách hàng</h6>
                        <?php if (isset($_SESSION['user']['id'])): ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Họ và tên</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" readonly>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Nhập họ và tên">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Nhập email">
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <label for="phone" class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" required
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
                        </div>
                    </div>

                    <!-- Ngày và số lượng -->
                    <div class="mb-4">
                        <h6 class="text-primary fw-bold border-bottom pb-2 mb-3">Thông tin đặt phòng</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="checkin" class="form-label fw-semibold">Ngày đi <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="checkin" name="checkin" required
                                    value="<?= htmlspecialchars($_POST['checkin'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="checkout" class="form-label fw-semibold">Ngày về <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="checkout" name="checkout" required
                                    value="<?= htmlspecialchars($_POST['checkout'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="guests" class="form-label fw-semibold">Số người <span class="text-danger">*</span></label>
                                <select id="guests" class="form-select" name="guests" required>
                                    <option value="1" <?= ($_POST['guests'] ?? '') == '1' ? 'selected' : '' ?>>1 người</option>
                                    <option value="2" <?= ($_POST['guests'] ?? '') == '2' ? 'selected' : '' ?>>2 người</option>
                                    <option value="3" <?= ($_POST['guests'] ?? '') == '3' ? 'selected' : '' ?>>3 người</option>
                                    <option value="4" <?= ($_POST['guests'] ?? '') == '4' ? 'selected' : '' ?>>4 người</option>
                                    <option value="5" <?= ($_POST['guests'] ?? '') == '5' ? 'selected' : '' ?>>5 người trở lên</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="room_type" class="form-label fw-semibold">Loại phòng <span class="text-danger">*</span></label>
                                <select id="room_type" class="form-select" name="room_type" required>
                                    <option value="Standard" <?= ($_POST['room_type'] ?? '') == 'Standard' ? 'selected' : '' ?>>Phòng Standard</option>
                                    <option value="Deluxe" <?= ($_POST['room_type'] ?? '') == 'Deluxe' ? 'selected' : '' ?>>Phòng Deluxe</option>
                                    <option value="Suite" <?= ($_POST['room_type'] ?? '') == 'Suite' ? 'selected' : '' ?>>Phòng Suite</option>
                                    <option value="Villa" <?= ($_POST['room_type'] ?? '') == 'Villa' ? 'selected' : '' ?>>Villa</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="room_number" class="form-label fw-semibold">Chọn phòng <span class="text-danger">*</span></label>
                                <select id="room_number" class="form-select" name="room_number" required>
                                    <option value="">-- Chọn phòng --</option>
                                </select>
                                <div id="room_error" class="text-danger small mt-1"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Ghi chú -->
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Nhập ghi chú (nếu có)"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="mt-3">
                        <h6 class="fw-bold">Tổng giá: <span class="price-display">0 VND</span></h6>
                    </div>
                    <div>
                        <button type="submit" id="submit_booking_btn" class="btn btn-primary w-100 py-2 fw-bold">Xác nhận đặt phòng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>