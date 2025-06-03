document.addEventListener("DOMContentLoaded", function () {
    fetch('../includes/get-tour-index.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById("tickets-tour");

            if (!data || data.length === 0) {
                container.innerHTML = "<p>Không có tour nào.</p>";
                return;
            }
            container.innerHTML = '';
            data.slice(0, 4).forEach(tour => {
                const ticket = document.createElement("div");
                ticket.className = "ticket";
            
                ticket.innerHTML = `
                <a class="ticket" href="../views/tour-detail.php?id=${tour.id}">
                    <div class="ticket-image">
                        <img src="../public/images-tour/bg-tickets-tour/${tour.image}" alt="${tour.title}">
                    </div>
                    <div class="ticket-content">
                        <h3>${tour.title}</h3>
                        <p>⭐ ${tour.rating}/10 (${tour.review} đánh giá)</p>
                        <p>Chỉ từ ${Number(tour.price).toLocaleString('vi-VN')}đ</p>
                    </div>
                    <div class="ticket-localtion-tag">
                        ${Array.isArray(tour.tags) ? tour.tags.map(tag => `
                            <div class="ticket-localtion-hatag">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-tag-fill" viewBox="0 0 16 16">
                                    <path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1z" />
                                </svg>
                                <span>${tag}</span>
                            </div>
                        `).join('') : ''}
                    </div>
                </a>
                `;
            
                container.appendChild(ticket);
            });
            
        })
        .catch(error => {
            console.error("Lỗi khi fetch tours:", error);
        });
});