// click location
document.addEventListener("DOMContentLoaded", function () {
    // Bắt sự kiện khi người dùng chọn một địa điểm
    document.querySelectorAll(".dropdown-item").forEach(function (item) {
      item.addEventListener("click", function (e) {
        e.preventDefault(); // Ngăn hành vi mặc định của thẻ <a>
        const selectedLocation = this.getAttribute("data-location");
        document.getElementById("current-location").textContent = selectedLocation;

        // Hiển thị nút xoá khi đã chọn địa điểm
        document.getElementById("clear-location").style.display = "inline-block";
      });
    });

    // Xử lý khi người dùng nhấn nút xoá địa điểm
    document.getElementById("clear-location").addEventListener("click", function () {
      document.getElementById("current-location").textContent = "Chọn địa điểm";
      this.style.display = "none"; // Ẩn nút xoá
    });

    // Mặc định ẩn nút xoá nếu chưa có địa điểm
    document.getElementById("clear-location").style.display = "none";
  });
//click date time
document.addEventListener("DOMContentLoaded", function () {
    function formatDateWithDay(date) {
        const daysOfWeek = ["Chủ Nhật", "Thứ 2", "Thứ 3", "Thứ 4", "Thứ 5", "Thứ 6", "Thứ 7"];
        return daysOfWeek[date.getDay()] + ", " + flatpickr.formatDate(date, "d-m-Y");
    }

    flatpickr("#start-date", {
        dateFormat: "d-m-Y",
        defaultDate: "today",
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                document.getElementById("start-date").textContent = formatDateWithDay(selectedDates[0]);
            }
        }
    });

    flatpickr("#end-date", {
        dateFormat: "d-m-Y",
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                document.getElementById("end-date").textContent = formatDateWithDay(selectedDates[0]);
            }
        }
    });
});

let roomCount = 1, adultCount = 2, childCount = 0;

function updateCount(type, change) {
    if (type === 'room') roomCount = Math.max(1, roomCount + change);
    if (type === 'adult') adultCount = Math.max(1, adultCount + change);
    if (type === 'child') childCount = Math.max(0, childCount + change);
    document.getElementById('roomCount').textContent = roomCount;
    document.getElementById('adultCount').textContent = adultCount;
    document.getElementById('childCount').textContent = childCount;
    updateDisplay();
}

function updateDisplay() {
    document.getElementById('roomGuestadult').textContent = `${adultCount} người lớn`;
    document.getElementById('roomGuestchild').textContent = `${childCount} trẻ em`;
    document.getElementById('roomGuestroom').textContent = `${roomCount} phòng`;
}

document.getElementById("roomGuestBtn").addEventListener("click", function () {
    console.log("click")
    document.getElementById("roomGuestDropdown").style.display = "block";
});

document.addEventListener("click", function (event) {
    if (!event.target.closest(".dropdown-room-container")) {
        document.getElementById("roomGuestDropdown").style.display = "none";
    }
});