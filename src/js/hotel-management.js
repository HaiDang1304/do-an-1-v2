// Hàm chuyển đổi dữ liệu thành JSON trước khi gửi form
function prepareFormData(formId, tagsField, galleryField, experienceContainer, comboIncludedContainer, comboHidden, experienceHidden) {
  document.getElementById(formId).addEventListener('submit', function(e) {
    e.preventDefault(); // Ngăn gửi form để kiểm tra

    // Kiểm tra dữ liệu bắt buộc
    const name = document.getElementById(formId === 'insert-hotel-form' ? 'hotel-name' : 'update-hotel-name').value.trim();
    const price = document.getElementById(formId === 'insert-hotel-form' ? 'hotel-price' : 'update-hotel-price').value.trim();
    const location = document.getElementById(formId === 'insert-hotel-form' ? 'hotel-location' : 'update-hotel-location').value.trim();
    const start = document.getElementById(formId === 'insert-hotel-form' ? 'hotel-start' : 'update-hotel-start').value.trim();
    if (!name || !price || !location || !start) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Tên khách sạn, giá, vị trí và số sao là bắt buộc.'
      });
      return;
    }

    // Xử lý tags
    const tagsInput = document.getElementById(tagsField).value.trim();
    const tags = tagsInput.split(/[\n,]+/).map(item => item.trim()).filter(item => item);
    if (!tags.length) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Vui lòng nhập ít nhất một tag.'
      });
      return;
    }
    document.getElementById(tagsField).value = JSON.stringify(tags);

    // Xử lý gallery
    const galleryInput = document.getElementById(galleryField).value.trim();
    const galleryItems = galleryInput.split(/[\n,]+/).map(item => item.trim()).filter(item => item);
    document.getElementById(galleryField).value = JSON.stringify([{ "main-images": galleryItems, "sub-images": galleryItems }]);

    // Xử lý experience
    const experienceEntries = document.querySelectorAll(`${experienceContainer} .experience-entry`);
    const experiences = [];
    let experienceValid = false;
    experienceEntries.forEach(entry => {
      const title = entry.querySelector('.experience-title').value.trim();
      const content = entry.querySelector('.experience-content').value.trim();
      if (title || content) {
        experiences.push({ title, content });
        experienceValid = true;
      }
    });
    if (!experienceValid) {
      Swal.fire({
        icon: 'error',
        title: 'Lỗi',
        text: 'Vui lòng nhập ít nhất một trải nghiệm hợp lệ.'
      });
      return;
    }
    document.getElementById(experienceHidden).value = JSON.stringify(experiences);

    // Xử lý combo details
    const comboName = document.getElementById(formId === 'insert-hotel-form' ? 'combo-name' : 'update-combo-name').value.trim();
    const comboDescription = document.getElementById(formId === 'insert-hotel-form' ? 'combo-description' : 'update-combo-description').value.trim();
    const comboConditionsInput = document.getElementById(formId === 'insert-hotel-form' ? 'combo-conditions' : 'update-combo-conditions').value.trim();
    const comboConditions = comboConditionsInput.split('\n').map(item => item.trim()).filter(item => item);
    const includedEntries = document.querySelectorAll(`${comboIncludedContainer} .included-entry`);
    const included = [];
    includedEntries.forEach(entry => {
      const title = entry.querySelector('.included-title').value.trim();
      const detail = entry.querySelector('.included-detail').value.trim();
      if (title || detail) {
        included.push({ title, detail });
      }
    });
    const comboDetails = {
      combo_name: comboName,
      description: comboDescription,
      included: included,
      conditions: comboConditions
    };
    document.getElementById(comboHidden).value = JSON.stringify(comboDetails);

    // Gửi form
    this.submit();
  });
}

// Thêm entry động cho experience
function addExperienceEntry(containerId, buttonId) {
  document.getElementById(buttonId).addEventListener('click', function() {
    const container = document.getElementById(containerId);
    const entry = document.createElement('div');
    entry.className = 'experience-entry mb-3 border p-3 rounded';
    entry.innerHTML = `
      <div class="mb-2">
        <label class="form-label">Tiêu đề</label>
        <input type="text" class="form-control experience-title" placeholder="Ví dụ: Công viên chủ đề">
      </div>
      <div class="mb-2">
        <label class="form-label">Nội dung</label>
        <textarea class="form-control experience-content" rows="3" placeholder="Mô tả chi tiết về trải nghiệm"></textarea>
      </div>
      <button type="button" class="btn btn-danger btn-sm remove-experience-entry">Xóa</button>
    `;
    container.appendChild(entry);

    // Xử lý nút xóa
    entry.querySelector('.remove-experience-entry').addEventListener('click', function() {
      container.removeChild(entry);
    });
  });
}

// Thêm entry động cho combo included
function addIncludedEntry(containerId, buttonId) {
  document.getElementById(buttonId).addEventListener('click', function() {
    const container = document.getElementById(containerId);
    const entry = document.createElement('div');
    entry.className = 'included-entry mb-3 border p-3 rounded';
    entry.innerHTML = `
      <div class="mb-2">
        <label class="form-label">Tiêu đề</label>
        <input type="text" class="form-control included-title" placeholder="Ví dụ: Vé máy bay">
      </div>
      <div class="mb-2">
        <label class="form-label">Chi tiết</label>
        <textarea class="form-control included-detail" rows="2" placeholder="Mô tả chi tiết"></textarea>
      </div>
      <button type="button" class="btn btn-danger btn-sm remove-included-entry">Xóa</button>
    `;
    container.appendChild(entry);

    // Xử lý nút xóa
    entry.querySelector('.remove-included-entry').addEventListener('click', function() {
      container.removeChild(entry);
    });
  });
}

// Gọi hàm cho cả hai form
prepareFormData('insert-hotel-form', 'hotel-tags', 'hotel-gallery', '#hotel-experience-entries-insert', '#combo-included-entries-insert', 'hotel-combo-details-insert', 'hotel-experience-insert');
prepareFormData('update-hotel-form', 'update-hotel-tags', 'update-hotel-gallery', '#hotel-experience-entries-update', '#combo-included-entries-update', 'hotel-combo-details-update', 'hotel-experience-update');

// Thêm entry động cho cả hai tab
addExperienceEntry('hotel-experience-entries-insert', 'add-experience-entry-insert');
addExperienceEntry('hotel-experience-entries-update', 'add-experience-entry-update');
addIncludedEntry('combo-included-entries-insert', 'add-included-entry-insert');
addIncludedEntry('combo-included-entries-update', 'add-included-entry-update');

function openUpdateModalHotel(id) {
    console.log('Đang gọi API với ID:', id);

    fetch(`../includes/get-hotel-details.php?id=${id}`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
        .then(response => {
            console.log('Phản hồi từ server:', response);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dữ liệu nhận được:', data);
            if (data.error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: data.error,
                    timer: 2000
                });
                return;
            }

            // Đảm bảo modal đã được tải trước khi điền dữ liệu
            const modal = document.getElementById('modal-update-hotel');
            if (!modal) {
                console.error('Modal không tồn tại!');
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Modal cập nhật khách sạn không được tìm thấy.',
                    timer: 2000
                });
                return;
            }

            // Kiểm tra và điền dữ liệu vào form
            const form = document.getElementById('update-hotel-form');
            if (!form) {
                console.error('Form không tồn tại!');
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi',
                    text: 'Form cập nhật khách sạn không được tìm thấy.',
                    timer: 2000
                });
                return;
            }

            const setValue = (id, value) => {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value;
                } else {
                    console.warn(`Phần tử với ID '${id}' không tồn tại!`);
                }
            };

            setValue('update-hotel-id', data.id);
            setValue('update-hotel-name', data.name || '');
            setValue('update-hotel-image', data.image || '');
            setValue('update-hotel-tags', data.tags ? data.tags.join('\n') : '');
            setValue('update-hotel-price', data.price || 0);
            setValue('update-hotel-location', data.location || '');
            setValue('update-hotel-rating', data.rating || 0);
            setValue('update-hotel-reviews', data.reviews || 0);
            setValue('update-hotel-start', data.start || 1);
            setValue('update-hotel-youtube-id', data.youtube_id || '');
            setValue('update-hotel-title-ytb', data.title_ytb || '');
            setValue('update-hotel-address', data.address || '');
            setValue('update-hotel-map-embed', data.map_embed || '');
            setValue('update-hotel-description', data.description || '');

            // Gallery
            const galleryData = data.gallery && data.gallery[0] && data.gallery[0]["main-images"] ? data.gallery[0]["main-images"] : [];
            setValue('update-hotel-gallery', galleryData.join('\n'));

            // Experience
            const experienceContainer = document.getElementById('hotel-experience-entries-update');
            if (experienceContainer) {
                experienceContainer.innerHTML = '';
                (data.experience || []).forEach(exp => {
                    const entry = document.createElement('div');
                    entry.className = 'experience-entry mb-3 border p-3 rounded';
                    entry.innerHTML = `
                        <div class="mb-2">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control experience-title" value="${exp.title || ''}" placeholder="Ví dụ: Công viên chủ đề">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nội dung</label>
                            <textarea class="form-control experience-content" rows="3" placeholder="Mô tả chi tiết về trải nghiệm">${exp.content || ''}</textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-experience-entry">Xóa</button>
                    `;
                    experienceContainer.appendChild(entry);
                    entry.querySelector('.remove-experience-entry').addEventListener('click', function() {
                        experienceContainer.removeChild(entry);
                    });
                });
                if (!data.experience || data.experience.length === 0) {
                    const entry = document.createElement('div');
                    entry.className = 'experience-entry mb-3 border p-3 rounded';
                    entry.innerHTML = `
                        <div class="mb-2">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control experience-title" placeholder="Ví dụ: Công viên chủ đề">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nội dung</label>
                            <textarea class="form-control experience-content" rows="3" placeholder="Mô tả chi tiết về trải nghiệm"></textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-experience-entry">Xóa</button>
                    `;
                    experienceContainer.appendChild(entry);
                    entry.querySelector('.remove-experience-entry').addEventListener('click', function() {
                        experienceContainer.removeChild(entry);
                    });
                }
            } else {
                console.error('Container experience không tồn tại!');
            }

            // Combo Details
            const setComboValue = (id, value) => {
                const element = document.getElementById(id);
                if (element) {
                    element.value = value;
                } else {
                    console.warn(`Phần tử với ID '${id}' không tồn tại!`);
                }
            };
            const combo = data.combo_details || { combo_name: '', description: '', included: [], conditions: [] };
            setComboValue('update-combo-name', combo.combo_name || '');
            setComboValue('update-combo-description', combo.description || '');
            setComboValue('update-combo-conditions', combo.conditions ? combo.conditions.join('\n') : '');

            const includedContainer = document.getElementById('combo-included-entries-update');
            if (includedContainer) {
                includedContainer.innerHTML = '';
                (combo.included || []).forEach(item => {
                    const entry = document.createElement('div');
                    entry.className = 'included-entry mb-3 border p-3 rounded';
                    entry.innerHTML = `
                        <div class="mb-2">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control included-title" value="${item.title || ''}" placeholder="Ví dụ: Vé máy bay">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Chi tiết</label>
                            <textarea class="form-control included-detail" rows="2" placeholder="Mô tả chi tiết">${item.detail || ''}</textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-included-entry">Xóa</button>
                    `;
                    includedContainer.appendChild(entry);
                    entry.querySelector('.remove-included-entry').addEventListener('click', function() {
                        includedContainer.removeChild(entry);
                    });
                });
                if (!combo.included || combo.included.length === 0) {
                    const entry = document.createElement('div');
                    entry.className = 'included-entry mb-3 border p-3 rounded';
                    entry.innerHTML = `
                        <div class="mb-2">
                            <label class="form-label">Tiêu đề</label>
                            <input type="text" class="form-control included-title" placeholder="Ví dụ: Vé máy bay">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Chi tiết</label>
                            <textarea class="form-control included-detail" rows="2" placeholder="Mô tả chi tiết"></textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm remove-included-entry">Xóa</button>
                    `;
                    includedContainer.appendChild(entry);
                    entry.querySelector('.remove-included-entry').addEventListener('click', function() {
                        includedContainer.removeChild(entry);
                    });
                }
            } else {
                console.error('Container combo included không tồn tại!');
            }

            // Hiển thị modal
            new bootstrap.Modal(modal).show();
        })
        .catch(error => {
            console.error('Lỗi khi gọi API:', error);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể tải dữ liệu khách sạn. Vui lòng kiểm tra kết nối hoặc dữ liệu.',
                timer: 2000
            });
        });
}

function confirmDeleteHotel(id) {
  Swal.fire({
    title: 'Bạn có chắc chắn không?',
    text: "Bạn sẽ không thể khôi phục khách sạn này sau khi xóa!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Xóa',
    cancelButtonText: 'Hủy'
  }).then((result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append('id', id);

      fetch('../includes/process-delete-hotel.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          Swal.fire({
            title: 'Đã xóa!',
            text: 'Khách sạn đã được xóa thành công.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            window.location.href = '../views/admin.php?type=hotel-management';
          });
        } else {
          Swal.fire({
            title: 'Lỗi!',
            text: data.message || 'Không thể xóa khách sạn. Vui lòng thử lại.',
            icon: 'error',
            confirmButtonText: 'OK'
          });
        }
      })
      .catch(error => {
        Swal.fire({
          title: 'Lỗi!',
          text: 'Đã xảy ra lỗi khi xóa khách sạn: ' + error.message,
          icon: 'error',
          confirmButtonText: 'OK'
        });
      });
    }
  });
}