<!-- Modal Thêm Khách sạn -->
<div class="modal fade" id="modal-insert-hotel" tabindex="-1" aria-labelledby="modal-insert-hotel-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-insert-hotel-label">Thêm khách sạn mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="insert-hotel-form" action="../includes/process-insert-hotel.php" method="POST">
          <ul class="nav nav-tabs mb-3" id="insertHotelTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="basic-insert-tab" data-bs-toggle="tab" data-bs-target="#basic-insert" type="button" role="tab" aria-controls="basic-insert" aria-selected="true">Cơ bản</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="details-insert-tab" data-bs-toggle="tab" data-bs-target="#details-insert" type="button" role="tab" aria-controls="details-insert" aria-selected="false">Chi tiết</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="address-insert-tab" data-bs-toggle="tab" data-bs-target="#address-insert" type="button" role="tab" aria-controls="address-insert" aria-selected="false">Địa chỉ</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="description-insert-tab" data-bs-toggle="tab" data-bs-target="#description-insert" type="button" role="tab" aria-controls="description-insert" aria-selected="false">Mô tả</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="gallery-insert-tab" data-bs-toggle="tab" data-bs-target="#gallery-insert" type="button" role="tab" aria-controls="gallery-insert" aria-selected="false">Gallery</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="experience-insert-tab" data-bs-toggle="tab" data-bs-target="#experience-insert" type="button" role="tab" aria-controls="experience-insert" aria-selected="false">Experience</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="combo-insert-tab" data-bs-toggle="tab" data-bs-target="#combo-insert" type="button" role="tab" aria-controls="combo-insert" aria-selected="false">Combo Details</button>
            </li>
          </ul>
          <div class="tab-content" id="insertHotelTabContent">
            <!-- Tab Cơ bản -->
            <div class="tab-pane fade show active" id="basic-insert" role="tabpanel" aria-labelledby="basic-insert-tab">
              <div class="mb-3">
                <label for="hotel-name" class="form-label">Tên khách sạn</label>
                <input type="text" class="form-control" id="hotel-name" name="name" required>
              </div>
              <div class="mb-3">
                <label for="hotel-image" class="form-label">Hình ảnh đại diện</label>
                <input type="text" class="form-control" id="hotel-image" name="image" placeholder="Tên file hình ảnh">
              </div>
              <div class="mb-3">
                <label for="hotel-tags" class="form-label">Tags (mỗi tag trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="hotel-tags" name="tags" rows="3" placeholder="Biển\nNúi\nRừng hoặc Biển, Núi, Rừng"></textarea>
              </div>
              <div class="mb-3">
                <label for="hotel-price" class="form-label">Giá (VND)</label>
                <input type="number" class="form-control" id="hotel-price" name="price" required min="0">
              </div>
              <div class="mb-3">
                <label for="hotel-location" class="form-label">Vị trí</label>
                <input type="text" class="form-control" id="hotel-location" name="location" required>
              </div>
              <div class="mb-3">
                <label for="hotel-start" class="form-label">Số sao (1-5)</label>
                <input type="number" class="form-control" id="hotel-start" name="start" required min="1" max="5">
              </div>
            </div>
            <!-- Tab Chi tiết -->
            <div class="tab-pane fade" id="details-insert" role="tabpanel" aria-labelledby="details-insert-tab">
              <div class="mb-3">
                <label for="hotel-youtube-id" class="form-label">Mã nhúng YouTube</label>
                <input type="text" class="form-control" id="hotel-youtube-id" name="youtube_id" placeholder="Ví dụ: GY_QRBTwjx8">
              </div>
              <div class="mb-3">
                <label for="hotel-title-ytb" class="form-label">Tiêu đề video YouTube</label>
                <input type="text" class="form-control" id="hotel-title-ytb" name="title_ytb">
              </div>
            </div>
            <!-- Tab Địa chỉ -->
            <div class="tab-pane fade" id="address-insert" role="tabpanel" aria-labelledby="address-insert-tab">
              <div class="mb-3">
                <label for="hotel-address" class="form-label">Địa chỉ chi tiết</label>
                <textarea class="form-control" id="hotel-address" name="address" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label for="hotel-map-embed" class="form-label">Nhúng Google Map</label>
                <textarea class="form-control" id="hotel-map-embed" name="map_embed" rows="3" placeholder="Dán mã nhúng iframe từ Google Maps"></textarea>
              </div>
            </div>
            <!-- Tab Mô tả -->
            <div class="tab-pane fade" id="description-insert" role="tabpanel" aria-labelledby="description-insert-tab">
              <div class="mb-3">
                <label for="hotel-description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="hotel-description" name="description" rows="5"></textarea>
              </div>
            </div>
            <!-- Tab Gallery -->
            <div class="tab-pane fade" id="gallery-insert" role="tabpanel" aria-labelledby="gallery-insert-tab">
              <div class="mb-3">
                <label for="hotel-gallery" class="form-label">Gallery (mỗi tên file trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="hotel-gallery" name="gallery" rows="5" placeholder="image1.jpg\nimage2.jpg hoặc image1.jpg, image2.jpg"></textarea>
              </div>
            </div>
            <!-- Tab Experience -->
            <div class="tab-pane fade" id="experience-insert" role="tabpanel" aria-labelledby="experience-insert-tab">
              <div class="mb-3">
                <label class="form-label">Trải nghiệm</label>
                <div id="hotel-experience-entries-insert">
                  <div class="experience-entry mb-3 border p-3 rounded">
                    <div class="mb-2">
                      <label class="form-label">Tiêu đề</label>
                      <input type="text" class="form-control experience-title" placeholder="Ví dụ: Công viên chủ đề">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Nội dung</label>
                      <textarea class="form-control experience-content" rows="3" placeholder="Mô tả chi tiết về trải nghiệm"></textarea>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-experience-entry">Xóa</button>
                  </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-experience-entry-insert">Thêm trải nghiệm</button>
                <input type="hidden" name="experience" id="hotel-experience-insert">
              </div>
            </div>
            <!-- Tab Combo Details -->
            <div class="tab-pane fade" id="combo-insert" role="tabpanel" aria-labelledby="combo-insert-tab">
              <div class="mb-3">
                <label for="combo-name" class="form-label">Tên combo</label>
                <input type="text" class="form-control" id="combo-name" name="combo_name">
              </div>
              <div class="mb-3">
                <label for="combo-description" class="form-label">Mô tả combo</label>
                <textarea class="form-control" id="combo-description" name="combo_description" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Bao gồm</label>
                <div id="combo-included-entries-insert">
                  <div class="included-entry mb-3 border p-3 rounded">
                    <div class="mb-2">
                      <label class="form-label">Tiêu đề</label>
                      <input type="text" class="form-control included-title" placeholder="Ví dụ: Vé máy bay">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Chi tiết</label>
                      <textarea class="form-control included-detail" rows="2" placeholder="Mô tả chi tiết"></textarea>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-included-entry">Xóa</button>
                  </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-included-entry-insert">Thêm mục</button>
                <input type="hidden" name="combo_included" id="combo-included-insert">
              </div>
              <div class="mb-3">
                <label for="combo-conditions" class="form-label">Điều kiện (mỗi điều kiện trên một dòng)</label>
                <textarea class="form-control" id="combo-conditions" name="combo_conditions" rows="3" placeholder="Điều kiện 1\nĐiều kiện 2"></textarea>
              </div>
              <input type="hidden" name="combo_details" id="hotel-combo-details-insert">
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