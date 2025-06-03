// Hàm chuyển đổi dữ liệu thành JSON trước khi gửi form
function prepareFormData(formId, tagField, galleryField, programContainer, noteGiaBaoGom, noteGiaKhongBaoGom, programHidden, noteHidden) {
  document.getElementById(formId).addEventListener('submit', function(e) {
    e.preventDefault(); // Ngăn gửi form để kiểm tra

    // Kiểm tra dữ liệu bắt buộc
    const title = document.getElementById(formId === 'insert-tour-form' ? 'tour-title' : 'update-tour-title').value.trim();
    const price = document.getElementById(formId === 'insert-tour-form' ? 'tour-price' : 'update-tour-price').value.trim();
    if (!title || !price) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Tiêu đề và giá là bắt buộc.'
      });
      return;
    }

    // Xử lý tag
    const tagInput = document.getElementById(tagField).value.trim();
    const tags = tagInput.split(/[\n,]+/).map(item => item.trim()).filter(item => item);
    if (!tags.length) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Vui lòng nhập ít nhất một tag.'
      });
      return;
    }
    document.getElementById(tagField).value = JSON.stringify(tags);

    // Xử lý gallery
    const galleryInput = document.getElementById(galleryField).value.trim();
    const galleryItems = galleryInput.split(/[\n,]+/).map(item => item.trim()).filter(item => item);
    document.getElementById(galleryField).value = JSON.stringify(galleryItems);

    // Xử lý tour program
    const programEntries = document.querySelectorAll(`${programContainer} .program-entry`);
    const programs = [];
    let programValid = false;
    programEntries.forEach(entry => {
      const day = entry.querySelector('.program-day').value.trim();
      const title = entry.querySelector('.program-title').value.trim();
      const content = entry.querySelector('.program-content').value.trim().split('\n').map(item => item.trim()).filter(item => item);
      if (day || title || content.length) {
        programs.push({ day, title, content });
        programValid = true;
      }
    });
    if (!programValid) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Vui lòng nhập ít nhất một chương trình tour hợp lệ.'
      });
      return;
    }
    document.getElementById(programHidden).value = JSON.stringify(programs);

    // Xử lý note
    const giaBaoGom = document.getElementById(noteGiaBaoGom).value.trim().split('\n').map(item => item.trim()).filter(item => item);
    const giaKhongBaoGom = document.getElementById(noteGiaKhongBaoGom).value.trim().split('\n').map(item => item.trim()).filter(item => item);
    const note = { gia_bao_gom: giaBaoGom, gia_khong_bao_gom: giaKhongBaoGom };
    document.getElementById(noteHidden).value = JSON.stringify(note);

    // Gửi form
    this.submit();
  });
}

// Thêm entry động cho tour program
function addProgramEntry(containerId, buttonId) {
  document.getElementById(buttonId).addEventListener('click', function() {
    const container = document.getElementById(containerId);
    const entry = document.createElement('div');
    entry.className = 'program-entry mb-3 border p-3 rounded';
    entry.innerHTML = `
      <div class="mb-2">
        <label class="form-label">Ngày</label>
        <input type="text" class="form-control program-day" placeholder="Ví dụ: Ngày 1">
      </div>
      <div class="mb-2">
        <label class="form-label">Tiêu đề</label>
        <input type="text" class="form-control program-title" placeholder="Ví dụ: Khám phá">
      </div>
      <div class="mb-2">
        <label class="form-label">Nội dung (mỗi mục trên một dòng)</label>
        <textarea class="form-control program-content" rows="3" placeholder="Điểm A\nĐiểm B"></textarea>
      </div>
      <button type="button" class="btn btn-danger btn-sm remove-program-entry">Xóa</button>
    `;
    container.appendChild(entry);

    // Xử lý nút xóa
    entry.querySelector('.remove-program-entry').addEventListener('click', function() {
      container.removeChild(entry);
    });
  });
}

// Gọi hàm cho cả hai form
prepareFormData('insert-tour-form', 'tour-tag', 'tour-gallery', '#tour-program-entries-insert', 'note-gia-bao-gom', 'note-gia-khong-bao-gom', 'tour-program-insert', 'tour-note-insert');
prepareFormData('update-tour-form', 'update-tour-tag', 'update-tour-gallery', '#tour-program-entries-update', 'update-note-gia-bao-gom', 'update-note-gia-khong-bao-gom', 'tour-program-update', 'tour-note-update');

// Thêm entry động cho cả hai tab
addProgramEntry('tour-program-entries-insert', 'add-program-entry-insert');
addProgramEntry('tour-program-entries-update', 'add-program-entry-update');

function openUpdateModalTour(id) {
  fetch(`../includes/get-tour-details.php?id=${id}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Data received:', data);
      if (data.error) {
        Swal.fire({
          icon: 'error',
          title: 'Lỗi',
          text: data.error,
          timer: 2000
        });
        return;
      }
      document.getElementById('update-tour-id').value = data.id;
      document.getElementById('update-tour-title').value = data.title;
      document.getElementById('update-tour-image').value = data.image || '';
      document.getElementById('update-tour-tag').value = data.tag ? (Array.isArray(data.tag) ? data.tag.join('\n') : data.tag) : '';
      document.getElementById('update-tour-is-featured').checked = data.is_featured == 1;
      document.getElementById('update-tour-price').value = data.price || 0;
      document.getElementById('update-tour-description').value = data.description || '';

      // Hiển thị gallery
      document.getElementById('update-tour-gallery').value = data.gallery ? data.gallery.join('\n') : '';

      // Hiển thị tour program
      const programContainer = document.getElementById('tour-program-entries-update');
      programContainer.innerHTML = ''; // Xóa các entry cũ
      if (data.tour_program && data.tour_program.length) {
        data.tour_program.forEach(program => {
          const entry = document.createElement('div');
          entry.className = 'program-entry mb-3 border p-3 rounded';
          entry.innerHTML = `
            <div class="mb-2">
              <label class="form-label">Ngày</label>
              <input type="text" class="form-control program-day" value="${program.day || ''}" placeholder="Ví dụ: Ngày 1">
            </div>
            <div class="mb-2">
              <label class="form-label">Tiêu đề</label>
              <input type="text" class="form-control program-title" value="${program.title || ''}" placeholder="Ví dụ: Khám phá">
            </div>
            <div class="mb-2">
              <label class="form-label">Nội dung (mỗi mục trên một dòng)</label>
              <textarea class="form-control program-content" rows="3" placeholder="Điểm A\nĐiểm B">${program.content ? program.content.join('\n') : ''}</textarea>
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-program-entry">Xóa</button>
          `;
          programContainer.appendChild(entry);
          entry.querySelector('.remove-program-entry').addEventListener('click', function() {
            programContainer.removeChild(entry);
          });
        });
      } else {
        // Thêm một entry mặc định nếu không có dữ liệu
        const entry = document.createElement('div');
        entry.className = 'program-entry mb-3 border p-3 rounded';
        entry.innerHTML = `
          <div class="mb-2">
            <label class="form-label">Ngày</label>
            <input type="text" class="form-control program-day" placeholder="Ví dụ: Ngày 1">
          </div>
          <div class="mb-2">
            <label class="form-label">Tiêu đề</label>
            <input type="text" class="form-control program-title" placeholder="Ví dụ: Khám phá">
          </div>
          <div class="mb-2">
            <label class="form-label">Nội dung (mỗi mục trên một dòng)</label>
            <textarea class="form-control program-content" rows="3" placeholder="Điểm A\nĐiểm B"></textarea>
          </div>
          <button type="button" class="btn btn-danger btn-sm remove-program-entry">Xóa</button>
        `;
        programContainer.appendChild(entry);
        entry.querySelector('.remove-program-entry').addEventListener('click', function() {
          programContainer.removeChild(entry);
        });
      }

      // Hiển thị note
      const note = data.note || { gia_bao_gom: [], gia_khong_bao_gom: [] };
      document.getElementById('update-note-gia-bao-gom').value = note.gia_bao_gom ? note.gia_bao_gom.join('\n') : '';
      document.getElementById('update-note-gia-khong-bao-gom').value = note.gia_khong_bao_gom ? note.gia_khong_bao_gom.join('\n') : '';

      // Sửa lỗi ID modal
      new bootstrap.Modal(document.getElementById('modal-update-tour')).show();
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Không thể tải dữ liệu tour.',
        timer: 2000
      });
    });
}

function confirmDeleteTour(id) {
  Swal.fire({
    title: 'Bạn có chắc chắn không?',
    text: "Bạn sẽ không thể khôi phục tour này sau khi xóa!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      // Gửi yêu cầu xóa chỉ khi người dùng xác nhận
      const formData = new FormData();
      formData.append('id', id);

      fetch('../includes/process-delete-tour.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: 'Đã xóa!',
            text: 'Tour đã được xóa thành công.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            // Tải lại trang để cập nhật danh sách
            window.location.href = 'admin.php?type=tour-management';
          });
        } else {
          Swal.fire({
            title: 'Lỗi!',
            text: data.message || 'Không thể xóa tour. Vui lòng thử lại.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      })
      .catch(error => {
        Swal.fire({
          title: 'Lỗi!',
          text: 'Đã xảy ra lỗi khi xóa tour: ' + error.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      });
    }
  });
}