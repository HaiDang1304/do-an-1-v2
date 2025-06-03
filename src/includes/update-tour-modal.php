<!-- Modal Cập nhật Tour -->
<div class="modal fade" id="modal-update-tour" tabindex="-1" aria-labelledby="modal-update-tour-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-update-tour-label">Cập nhật tour</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="update-tour-form" action="../includes/process-update-tour.php" method="POST">
          <input type="hidden" id="update-tour-id" name="id">
          <ul class="nav nav-tabs mb-3" id="updateTourTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="basic-update-tab" data-bs-toggle="tab" data-bs-target="#basic-update" type="button" role="tab" aria-controls="basic-update" aria-selected="true">Cơ bản</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="description-update-tab" data-bs-toggle="tab" data-bs-target="#description-update" type="button" role="tab" aria-controls="description-update" aria-selected="false">Mô tả</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="gallery-update-tab" data-bs-toggle="tab" data-bs-target="#gallery-update" type="button" role="tab" aria-controls="gallery-update" aria-selected="false">Gallery</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="program-update-tab" data-bs-toggle="tab" data-bs-target="#program-update" type="button" role="tab" aria-controls="program-update" aria-selected="false">Chương trình</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="note-update-tab" data-bs-toggle="tab" data-bs-target="#note-update" type="button" role="tab" aria-controls="note-update" aria-selected="false">Lưu ý</button>
            </li>
          </ul>
          <div class="tab-content" id="updateTourTabContent">
            <div class="tab-pane fade show active" id="basic-update" role="tabpanel" aria-labelledby="basic-update-tab">
              <div class="mb-3">
                <label for="update-tour-title" class="form-label">Tiêu đề</label>
                <input type="text" class="form-control" id="update-tour-title" name="title" required>
              </div>
              <div class="mb-3">
                <label for="update-tour-image" class="form-label">Hình ảnh đại diện</label>
                <input type="text" class="form-control" id="update-tour-image" name="image" placeholder="Tên file hình ảnh">
              </div>
              <div class="mb-3">
                <label for="update-tour-tag" class="form-label">Tag (mỗi tag trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="update-tour-tag" name="tag" rows="3" placeholder="Biển\nNúi\nRừng hoặc Biển, Núi, Rừng"></textarea>
              </div>
              <div class="mb-3">
                <label for="update-tour-is-featured" class="form-label">Nổi bật</label>
                <input type="checkbox" class="form-check-input" id="update-tour-is-featured" name="is_featured" value="1">
              </div>
              <div class="mb-3">
                <label for="update-tour-price" class="form-label">Giá (VND)</label>
                <input type="number" class="form-control" id="update-tour-price" name="price" required min="0">
              </div>
            </div>
            <div class="tab-pane fade" id="description-update" role="tabpanel" aria-labelledby="description-update-tab">
              <div class="mb-3">
                <label for="update-tour-description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="update-tour-description" name="description" rows="5"></textarea>
              </div>
            </div>
            <div class="tab-pane fade" id="gallery-update" role="tabpanel" aria-labelledby="gallery-update-tab">
              <div class="mb-3">
                <label for="update-tour-gallery" class="form-label">Gallery (mỗi tên file trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="update-tour-gallery" name="gallery" rows="5" placeholder="image1.jpg\nimage2.jpg hoặc image1.jpg, image2.jpg"></textarea>
              </div>
            </div>
            <div class="tab-pane fade" id="program-update" role="tabpanel" aria-labelledby="program-update-tab">
              <div class="mb-3">
                <label class="form-label">Chương trình tour</label>
                <div id="tour-program-entries-update">
                  <!-- Entries will be dynamically added here -->
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-program-entry-update">Thêm ngày</button>
                <input type="hidden" name="tour-program" id="tour-program-update">
              </div>
            </div>
            <div class="tab-pane fade" id="note-update" role="tabpanel" aria-labelledby="note-update-tab">
              <div class="mb-3">
                <label for="update-note-gia-bao-gom" class="form-label">Giá bao gồm (mỗi mục trên một dòng)</label>
                <textarea class="form-control" id="update-note-gia-bao-gom" rows="3" placeholder="Vé máy bay\nKhách sạn"></textarea>
              </div>
              <div class="mb-3">
                <label for="update-note-gia-khong-bao-gom" class="form-label">Giá không bao gồm (mỗi mục trên một dòng)</label>
                <textarea class="form-control" id="update-note-gia-khong-bao-gom" rows="3" placeholder="Ăn uống\nChi phí cá nhân"></textarea>
              </div>
              <input type="hidden" name="note" id="tour-note-update">
            </div>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>