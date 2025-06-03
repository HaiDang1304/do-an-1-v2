// hotels-detail.js

// Khởi tạo Swiper
const thumbSwiper = new Swiper('.thumb-swiper', {
    spaceBetween: 2,
    slidesPerView: 5,
    freeMode: true,
    watchSlidesProgress: true,
});

const mainSwiper = new Swiper('.main-swiper', {
    loop: true,
    slidesPerView: 1,
    spaceBetween: 2,
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    thumbs: {
        swiper: thumbSwiper,
    },
});

document.addEventListener('DOMContentLoaded', function () {
    const today = new Date().toISOString().split('T')[0];
    const checkinInput = document.getElementById('checkin');
    const checkoutInput = document.getElementById('checkout');
    const roomTypeSelect = document.getElementById('room_type');
    const roomSelect = document.getElementById('room_number');
    const roomError = document.getElementById('room_error');
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submit_booking_btn');

    // Đặt ngày tối thiểu là hôm nay cho check-in và check-out
    if (checkinInput) checkinInput.setAttribute('min', today);
    if (checkoutInput) checkoutInput.setAttribute('min', today);

    // Tính tổng giá phòng
    const priceInput = document.querySelector('input[name="price"]');
    const totalPriceElement = document.querySelector('.price-display');

    function calculateTotalPrice() {
        const checkin = new Date(checkinInput.value);
        const checkout = new Date(checkoutInput.value);
        if (checkin && checkout && checkin < checkout) {
            const diffTime = Math.abs(checkout - checkin);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const basePrice = parseFloat(priceInput.value) || 0;
            const totalPrice = diffDays * basePrice;
            if (totalPriceElement) {
                totalPriceElement.textContent = `${totalPrice.toLocaleString()} VND`;
            }
            let totalPriceInput = document.querySelector('input[name="total_price"]');
            if (!totalPriceInput) {
                totalPriceInput = document.createElement('input');
                totalPriceInput.type = 'hidden';
                totalPriceInput.name = 'total_price';
                bookingForm.appendChild(totalPriceInput);
            }
            totalPriceInput.value = totalPrice;
        } else {
            if (totalPriceElement) totalPriceElement.textContent = '0 VND';
        }
    }

    // Gọi hàm khi thay đổi ngày
    checkinInput.addEventListener('change', function () {
        updateMinCheckout();
        checkAvailableRooms();
        calculateTotalPrice();
    });

    checkoutInput.addEventListener('change', function () {
        checkAvailableRooms();
        calculateTotalPrice();
    });

    // Gọi hàm ban đầu khi modal mở
    calculateTotalPrice();

    // Khi người dùng chọn ngày check-in
    function updateMinCheckout() {
        const checkinDate = new Date(checkinInput.value);
        if (isNaN(checkinDate)) return;

        const minCheckoutDate = new Date(checkinDate);
        minCheckoutDate.setDate(minCheckoutDate.getDate() + 1);
        const minCheckoutStr = minCheckoutDate.toISOString().split('T')[0];

        checkoutInput.setAttribute('min', minCheckoutStr);
        if (new Date(checkoutInput.value) <= checkinDate) {
            checkoutInput.value = minCheckoutStr;
        }
    }

    checkinInput.addEventListener('change', function () {
        updateMinCheckout();
        checkAvailableRooms();
    });

    checkoutInput.addEventListener('change', checkAvailableRooms);
    roomTypeSelect.addEventListener('change', checkAvailableRooms);

    // Hàm kiểm tra phòng trống qua AJAX
    function checkAvailableRooms() {
        if (!checkinInput.value || !checkoutInput.value || !roomTypeSelect.value) {
            roomSelect.innerHTML = '<option value="">-- Chọn ngày và loại phòng trước --</option>';
            return;
        }

        const formData = new FormData();
        formData.append('hotel_id', document.querySelector('input[name="hotel_id"]').value);
        formData.append('checkin', checkinInput.value);
        formData.append('checkout', checkoutInput.value);
        formData.append('room_type', roomTypeSelect.value);

        fetch('../includes/check_rooms.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                roomSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
                roomError.textContent = '';

                if (data.success) {
                    data.rooms.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room;
                        option.textContent = `Phòng ${room}`;
                        roomSelect.appendChild(option);
                    });
                } else {
                    roomSelect.innerHTML += '<option value="" disabled>Không có phòng trống</option>';
                    roomError.textContent = data.error;
                }
            })
            .catch(error => {
                roomError.textContent = 'Đã có lỗi xảy ra. Vui lòng thử lại.';
                console.error('Error:', error);
            });
    }

    // Xử lý đặt phòng qua AJAX với SweetAlert2
    if (bookingForm && submitBtn) {
        submitBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!checkinInput.value || !checkoutInput.value || !roomSelect.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: 'Vui lòng chọn ngày, loại phòng và phòng hợp lệ.',
                    timer: 2000,
                    showConfirmButton: true
                });
                return;
            }

            const formData = new FormData(bookingForm);

            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang xử lý...';

            fetch('../includes/process-booking.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Xác nhận đặt phòng';

                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Đặt phòng thành công!',
                            text: data.message || 'Đặt phòng thành công! Vui lòng kiểm tra email để xem chi tiết.',
                            timer: 2000,
                            showConfirmButton: true
                        }).then(() => {
                            const bookingModal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                            if (bookingModal) {
                                bookingModal.hide();
                            }
                            bookingForm.reset();
                            roomSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
                            // Chuyển hướng (tùy chọn)
                            window.location.reload();// Hoặc trang chi tiết đặt phòng
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Đặt phòng thất bại!',
                            text: data.error || 'Có lỗi xảy ra. Vui lòng thử lại.',
                            timer: 2000,
                            showConfirmButton: true
                        });
                    }

                    if (data.warning) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Cảnh báo',
                            text: data.warning || 'Đặt phòng thành công nhưng có vấn đề với email xác nhận.',
                            timer: 2000,
                            showConfirmButton: true
                        });
                    }
                })
                .catch(error => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Xác nhận đặt phòng';
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi kết nối!',
                        text: 'Không thể kết nối đến server. Vui lòng thử lại.',
                        timer: 2000,
                        showConfirmButton: true
                    });
                    console.error('Error:', error);
                });
        });
    }
});

// Hiển thị video YouTube
function showVideo() {
    document.getElementById("video-embed").style.display = "block";
    document.getElementById("video-tag").style.display = "none";
}

// Khởi tạo Modal
const bookingModalElement = document.getElementById('bookingModal');
if (bookingModalElement) {
    const bookingModal = new bootstrap.Modal(bookingModalElement, {
        backdrop: true,
        keyboard: true,
        focus: true
    });

    function openBookingModal() {
        bookingModal.show();
    }

    bookingModalElement.addEventListener('hidden.bs.modal', function () {
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) bookingForm.reset();
    });
}

// Xác thực form đánh giá phía client
const reviewForm = document.getElementById('reviewForm');
if (reviewForm) {
    reviewForm.addEventListener('submit', function (e) {
        const rating = parseFloat(document.getElementById('rating').value);
        const comment = document.getElementById('comment').value.trim();

        if (isNaN(rating) || rating < 0 || rating > 10) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Điểm đánh giá phải từ 0 đến 10!',
                timer: 2000,
                showConfirmButton: true
            });
            e.preventDefault();
        }

        if (!comment) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi!',
                text: 'Vui lòng nhập nội dung đánh giá!',
                timer: 2000,
                showConfirmButton: true
            });
            e.preventDefault();
        }
    });
}

// Hiển thị thông báo từ query string
window.addEventListener('load', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const reviewSuccess = urlParams.get('review_success');
    if (reviewSuccess === '1') {
        Swal.fire({
            icon: 'success',
            title: 'Thành công!',
            text: 'Gửi đánh giá thành công!',
            timer: 2000,
            showConfirmButton: true
        });
    }
});