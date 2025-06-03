<!-- Modal Thêm Tour -->
<div class="modal fade" id="modal-insert-tour" tabindex="-1" aria-labelledby="modal-insert-tour-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">  
        <h5 class="modal-title" id="modal-insert-tour-label">Thêm tour mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="insert-tour-form" action="../includes/process-insert-tour.php" method="POST">
          <ul class="nav nav-tabs mb-3" id="insertTourTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="basic-insert-tab" data-bs-toggle="tab" data-bs-target="#basic-insert" type="button" role="tab" aria-controls="basic-insert" aria-selected="true">Cơ bản</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="description-insert-tab" data-bs-toggle="tab" data-bs-target="#description-insert" type="button" role="tab" aria-controls="description-insert" aria-selected="false">Mô tả</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="gallery-insert-tab" data-bs-toggle="tab" data-bs-target="#gallery-insert" type="button" role="tab" aria-controls="gallery-insert" aria-selected="false">Gallery</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="program-insert-tab" data-bs-toggle="tab" data-bs-target="#program-insert" type="button" role="tab" aria-controls="program-insert" aria-selected="false">Chương trình</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="note-insert-tab" data-bs-toggle="tab" data-bs-target="#note-insert" type="button" role="tab" aria-controls="note-insert" aria-selected="false">Lưu ý</button>
            </li>
          </ul>
          <div class="tab-content" id="insertTourTabContent">
            <div class="tab-pane fade show active" id="basic-insert" role="tabpanel" aria-labelledby="basic-insert-tab">
              <div class="mb-3">
                <label for="tour-title" class="form-label">Tiêu đề</label>
                <input type="text" class="form-control" id="tour-title" name="title" required>
              </div>
              <div class="mb-3">
                <label for="tour-image" class="form-label">Hình ảnh đại diện</label>
                <input type="text" class="form-control" id="tour-image" name="image" placeholder="Tên file hình ảnh">
              </div>
              <div class="mb-3">
                <label for="tour-tag" class="form-label">Tag (mỗi tag trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="tour-tag" name="tag" rows="3" placeholder="Tag1, Tag2, Tag3"></textarea>
              </div>
              <div class="mb-3">
                <label for="tour-duration" class="form-label">Số ngày</label>
                <input type="number" class="form-control" id="tour-duration" name="duration_days" min="1" value="1">
              </div>
              <div class="mb-3">
                <label for="tour-is-featured" class="form-label">Nổi bật</label>
                <input type="checkbox" class="form-check-input" id="tour-is-featured" name="is_featured" value="1">
              </div>
              <div class="mb-3">
                <label for="tour-price" class="form-label">Giá (VND)</label>
                <input type="number" class="form-control" id="tour-price" name="price" required min="0">
              </div>
            </div>
            <div class="tab-pane fade" id="description-insert" role="tabpanel" aria-labelledby="description-insert-tab">
              <div class="mb-3">
                <label for="tour-description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="tour-description" name="description" rows="5"></textarea>
              </div>
            </div>
            <div class="tab-pane fade" id="gallery-insert" role="tabpanel" aria-labelledby="gallery-insert-tab">
              <div class="mb-3">
                <label for="tour-gallery" class="form-label">Gallery (mỗi tên file trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="tour-gallery" name="gallery" rows="5" placeholder="image1.jpg\nimage2.jpg hoặc image1.jpg, image2.jpg"></textarea>
              </div>
            </div>
            <div class="tab-pane fade" id="program-insert" role="tabpanel" aria-labelledby="program-insert-tab">
              <div class="mb-3">
                <label class="form-label">Chương trình tour</label>
                <div id="tour-program-entries-insert">
                  <div class="program-entry mb-3 border p-3 rounded">
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
                  </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-program-entry-insert">Thêm ngày</button>
                <input type="hidden" name="tour-program" id="tour-program-insert">
              </div>
            </div>
            <div class="tab-pane fade" id="note-insert" role="tabpanel" aria-labelledby="note-insert-tab">
              <div class="mb-3">
                <label for="note-gia-bao-gom" class="form-label">Giá bao gồm (mỗi mục trên một dòng)</label>
                <textarea class="form-control" id="note-gia-bao-gom" rows="3" placeholder="Vé máy bay\nKhách sạn"></textarea>
              </div>
              <div class="mb-3">
                <label for="note-gia-khong-bao-gom" class="form-label">Giá không bao gồm (mỗi mục trên một dòng)</label>
                <textarea class="form-control" id="note-gia-khong-bao-gom" rows="3" placeholder="Ăn uống\nChi phí cá nhân"></textarea>
              </div>
              <input type="hidden" name="note" id="tour-note-insert">
            </div>
          </div>
          <div class="d-flex justify-content-end mt-3">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-success">Thêm</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>