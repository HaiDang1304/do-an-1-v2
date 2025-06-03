<!-- Modal Cập nhật Khách sạn -->
<div class="modal fade" id="modal-update-hotel" tabindex="-1" aria-labelledby="modal-update-hotel-label" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modal-update-hotel-label">Cập nhật khách sạn</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="update-hotel-form" action="../includes/process-update-hotel.php" method="POST">
          <input type="hidden" id="update-hotel-id" name="id">
          <ul class="nav nav-tabs mb-3" id="updateHotelTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="basic-update-tab" data-bs-toggle="tab" data-bs-target="#basic-update" type="button" role="tab" aria-controls="basic-update" aria-selected="true">Cơ bản</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="details-update-tab" data-bs-toggle="tab" data-bs-target="#details-update" type="button" role="tab" aria-controls="details-update" aria-selected="false">Chi tiết</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="address-update-tab" data-bs-toggle="tab" data-bs-target="#address-update" type="button" role="tab" aria-controls="address-update" aria-selected="false">Địa chỉ</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="description-update-tab" data-bs-toggle="tab" data-bs-target="#description-update" type="button" role="tab" aria-controls="description-update" aria-selected="false">Mô tả</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="gallery-update-tab" data-bs-toggle="tab" data-bs-target="#gallery-update" type="button" role="tab" aria-controls="gallery-update" aria-selected="false">Gallery</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="experience-update-tab" data-bs-toggle="tab" data-bs-target="#experience-update" type="button" role="tab" aria-controls="experience-update" aria-selected="false">Experience</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="combo-update-tab" data-bs-toggle="tab" data-bs-target="#combo-update" type="button" role="tab" aria-controls="combo-update" aria-selected="false">Combo Details</button>
            </li>
          </ul>
          <div class="tab-content" id="updateHotelTabContent">
            <!-- Tab Cơ bản -->
            <div class="tab-pane fade show active" id="basic-update" role="tabpanel" aria-labelledby="basic-update-tab">
              <div class="mb-3">
                <label for="update-hotel-name" class="form-label">Tên khách sạn</label>
                <input type="text" class="form-control" id="update-hotel-name" name="name" required>
              </div>
              <div class="mb-3">
                <label for="update-hotel-image" class="form-label">Hình ảnh đại diện</label>
                <input type="text" class="form-control" id="update-hotel-image" name="image" placeholder="Tên file hình ảnh">
              </div>
              <div class="mb-3">
                <label for="update-hotel-tags" class="form-label">Tags (mỗi tag trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="update-hotel-tags" name="tags" rows="3" placeholder="Biển\nNúi\nRừng hoặc Biển, Núi, Rừng"></textarea>
              </div>
              <div class="mb-3">
                <label for="update-hotel-price" class="form-label">Giá (VND)</label>
                <input type="number" class="form-control" id="update-hotel-price" name="price" required min="0">
              </div>
              <div class="mb-3">
                <label for="update-hotel-location" class="form-label">Vị trí</label>
                <input type="text" class="form-control" id="update-hotel-location" name="location" required>
              </div>
              <div class="mb-3">
                <label for="update-hotel-start" class="form-label">Số sao (1-5)</label>
                <input type="number" class="form-control" id="update-hotel-start" name="start" required min="1" max="5">
              </div>
            </div>
            <!-- Tab Chi tiết -->
            <div class="tab-pane fade" id="details-update" role="tabpanel" aria-labelledby="details-update-tab">
              <div class="mb-3">
                <label for="update-hotel-youtube-id" class="form-label">Mã nhúng YouTube</label>
                <input type="text" class="form-control" id="update-hotel-youtube-id" name="youtube_id" placeholder="Ví dụ: GY_QRBTwjx8">
              </div>
              <div class="mb-3">
                <label for="update-hotel-title-ytb" class="form-label">Tiêu đề video YouTube</label>
                <input type="text" class="form-control" id="update-hotel-title-ytb" name="title_ytb">
              </div>
            </div>
            <!-- Tab Địa chỉ -->
            <div class="tab-pane fade" id="address-update" role="tabpanel" aria-labelledby="address-update-tab">
              <div class="mb-3">
                <label for="update-hotel-address" class="form-label">Địa chỉ chi tiết</label>
                <textarea class="form-control" id="update-hotel-address" name="address" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label for="update-hotel-map-embed" class="form-label">Nhúng Google Map</label>
                <textarea class="form-control" id="update-hotel-map-embed" name="map_embed" rows="3" placeholder="Dán mã nhúng iframe từ Google Maps"></textarea>
              </div>
            </div>
            <!-- Tab Mô tả -->
            <div class="tab-pane fade" id="description-update" role="tabpanel" aria-labelledby="description-update-tab">
              <div class="mb-3">
                <label for="update-hotel-description" class="form-label">Mô tả</label>
                <textarea class="form-control" id="update-hotel-description" name="description" rows="5"></textarea>
              </div>
            </div>
            <!-- Tab Gallery -->
            <div class="tab-pane fade" id="gallery-update" role="tabpanel" aria-labelledby="gallery-update-tab">
              <div class="mb-3">
                <label for="update-hotel-gallery" class="form-label">Gallery (mỗi tên file trên một dòng hoặc phân cách bằng dấu phẩy)</label>
                <textarea class="form-control" id="update-hotel-gallery" name="gallery" rows="5" placeholder="image1.jpg\nimage2.jpg hoặc image1.jpg, image2.jpg"></textarea>
              </div>
            </div>
            <!-- Tab Experience -->
            <div class="tab-pane fade" id="experience-update" role="tabpanel" aria-labelledby="experience-update-tab">
              <div class="mb-3">
                <label class="form-label">Trải nghiệm</label>
                <div id="hotel-experience-entries-update">
                  <!-- Entries will be dynamically added here -->
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-experience-entry-update">Thêm trải nghiệm</button>
                <input type="hidden" name="experience" id="hotel-experience-update">
              </div>
            </div>
            <!-- Tab Combo Details -->
            <div class="tab-pane fade" id="combo-update" role="tabpanel" aria-labelledby="combo-update-tab">
              <div class="mb-3">
                <label for="update-combo-name" class="form-label">Tên combo</label>
                <input type="text" class="form-control" id="update-combo-name" name="combo_name">
              </div>
              <div class="mb-3">
                <label for="update-combo-description" class="form-label">Mô tả combo</label>
                <textarea class="form-control" id="update-combo-description" name="combo_description" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Bao gồm</label>
                <div id="combo-included-entries-update">
                  <!-- Entries will be dynamically added here -->
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="add-included-entry-update">Thêm mục</button>
                <input type="hidden" name="combo_included" id="combo-included-update">
              </div>
              <div class="mb-3">
                <label for="update-combo-conditions" class="form-label">Điều kiện (mỗi điều kiện trên một dòng)</label>
                <textarea class="form-control" id="update-combo-conditions" name="combo_conditions" rows="3" placeholder="Điều kiện 1\nĐiều kiện 2"></textarea>
              </div>
              <input type="hidden" name="combo_details" id="hotel-combo-details-update">
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