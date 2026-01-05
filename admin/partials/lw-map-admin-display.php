<?php
/**
 * Provide a admin area view for the plugin
 */
?>
<div class="wrap container-fluid mt-4 lw-admin-page" style="max-width: 1400px;">
    <div class="d-flex justify-content-between align-items-end mb-3 pb-2 border-bottom">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="fas fa-map-marked-alt text-primary me-2"></i>LW Map Pro</h2>
            <p class="text-muted mb-0 small">Quản lý bản đồ hành trình chuyên nghiệp v<?php echo LW_MAP_VERSION; ?></p>
        </div>
        <div>
            <button type="button" class="btn btn-white border shadow-sm btn-sm fw-bold text-secondary" onclick="location.reload()"><i class="fas fa-sync me-1"></i> Tải lại</button>
        </div>
    </div>
    
    <?php settings_errors('lw_map_msg'); ?>

    <ul class="nav nav-pills mb-4" id="lwTabs" role="tablist">
        <li class="nav-item me-2"><button class="nav-link active fw-bold px-4" id="dash-tab" data-bs-toggle="tab" data-bs-target="#dash-pane" type="button"><i class="fas fa-chart-pie me-2"></i> Dashboard</button></li>
        <li class="nav-item me-2"><button class="nav-link fw-bold px-4" id="map-tab" data-bs-toggle="tab" data-bs-target="#map-pane" type="button"><i class="fas fa-map-pin me-2"></i> Điểm ghim</button></li>
        <li class="nav-item me-2"><button class="nav-link fw-bold px-4" id="icon-tab" data-bs-toggle="tab" data-bs-target="#icon-pane" type="button"><i class="fas fa-images me-2"></i> Quản lý Icon</button></li>
        <?php if($can_access_settings): ?>
            <li class="nav-item"><button class="nav-link fw-bold px-4" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button"><i class="fas fa-cogs me-2"></i> Cài đặt Plugin</button></li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="lwTabsContent">
        
        <div class="tab-pane fade show active" id="dash-pane">
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="lw-stat-card blue">
                        <div><div class="lw-stat-title">Địa điểm</div><div class="lw-stat-number"><?php echo count($raw_points); ?></div></div>
                        <i class="fas fa-map-marker-alt text-primary opacity-25 fa-lg"></i>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="lw-stat-card green">
                        <div><div class="lw-stat-title">Icon tùy chỉnh</div><div class="lw-stat-number"><?php echo count($custom_icons); ?></div></div>
                        <i class="fas fa-images text-success opacity-25 fa-lg"></i>
                    </div>
                </div>
                 <div class="col-md-3">
                    <div class="lw-stat-card purple">
                        <div><div class="lw-stat-title">Theme</div><div class="lw-stat-number"><?php echo $map_themes[$current_theme]['name']; ?></div></div>
                        <i class="fas fa-layer-group text-white opacity-25 fa-lg" style="color: #8b5cf6 !important;"></i>
                    </div>
                </div>
            </div>
            <div class="lw-admin-card p-0" style="height: auto;">
                 <div class="lw-card-header"><h5 class="text-uppercase text-muted small fw-bold mb-0">Bản đồ tổng quan</h5></div>
                 <div class="row g-0">
                    <div class="col-md-3"><div class="lw-dash-sidebar-wrapper" id="lw-dash-sidebar-list"></div></div>
                    <div class="col-md-9"><div id="lw-dashboard-map" class="lw-map-container lw-dash-map-wrapper" style="height: 500px;"></div></div>
                 </div>
            </div>
        </div>

        <div class="tab-pane fade" id="map-pane">
             <div class="row g-4">
                <div class="col-lg-4">
                    <div class="lw-admin-card" style="height: 660px;">
                        <div class="lw-card-header bg-white">
                            <h5 class="mb-0">Danh sách Điểm</h5>
                            <button type="submit" form="points-form" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm"><i class="fas fa-save me-1"></i> LƯU</button>
                        </div>
                        <div class="p-2 flex-grow-1 bg-light">
                            <form id="points-form" method="post" class="h-100 d-flex flex-column">
                                <?php wp_nonce_field('lw_save_points_nonce'); ?>
                                <input type="hidden" name="lw_action" value="save_points">
                                <div id="points-container" class="lw-admin-col-left flex-grow-1"></div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="lw-admin-card p-0" style="height: 660px;">
                         <div class="lw-map-wrapper">
                            <div id="lw-manager-map" class="lw-map-container" style="border:none; height: 660px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="icon-pane">
            <form method="post" id="icon-form">
                <?php wp_nonce_field('lw_save_icons_nonce'); ?>
                <input type="hidden" name="lw_action" value="save_icons">
                <div class="row g-4">
                     <div class="col-md-4">
                        <div class="lw-admin-card" style="height: auto;">
                            <div class="lw-card-header">
                                <h5 class="text-secondary"><i class="fas fa-shield-alt me-2"></i>Icon Hệ thống</h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table lw-modern-table">
                                    <thead><tr><th>Tên loại</th><th class="text-end">Preview</th></tr></thead>
                                    <tbody>
                                        <?php foreach($default_icons as $def): ?>
                                        <tr>
                                            <td class="fw-bold text-muted"><?php echo esc_html($def['name']); ?></td>
                                            <td class="text-end"><div class="lw-icon-preview-box d-inline-flex"><img src="<?php echo esc_url($def['url']); ?>" class="lw-icon-preview"></div></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                     </div>

                     <div class="col-md-8">
                        <div class="lw-admin-card" style="height: auto;">
                            <div class="lw-card-header bg-white">
                                <h5 class="text-primary"><i class="fas fa-magic me-2"></i>Icon Tùy chỉnh</h5>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="add-icon-row"><i class="fas fa-plus me-1"></i> Thêm mới</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table lw-modern-table table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%;">Tên Icon</th>
                                            <th>URL Hình ảnh / Upload</th>
                                            <th class="text-center" style="width: 80px;">Xem</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="icon-list-body">
                                        <?php foreach($custom_icons as $ic): ?>
                                        <tr>
                                            <td><input type="text" name="icon_name[]" class="form-control lw-form-control" value="<?php echo esc_attr($ic['name']); ?>" placeholder="Nhập tên..." required></td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="text" name="icon_url[]" class="form-control lw-form-control img-url-input" value="<?php echo esc_url($ic['url']); ?>" placeholder="https://..." required>
                                                    <button type="button" class="btn btn-upload btn-upload-img"><i class="fas fa-cloud-upload-alt"></i></button>
                                                </div>
                                            </td>
                                            <td class="text-center"><div class="lw-icon-preview-box mx-auto"><img src="<?php echo esc_url($ic['url']); ?>" class="lw-icon-preview"></div></td>
                                            <td class="text-center">
                                                <button type="button" class="btn-icon-delete remove-row" title="Xóa">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 border-top bg-light text-end">
                                <button type="submit" class="btn btn-success fw-bold px-4 shadow-sm"><i class="fas fa-save me-2"></i> LƯU DANH SÁCH ICON</button>
                            </div>
                        </div>
                     </div>
                </div>
            </form>
        </div>

        <?php if($can_access_settings): ?>
        <div class="tab-pane fade" id="settings-pane">
            <form method="post" id="settings-form" class="h-100">
                <?php wp_nonce_field('lw_save_settings_nonce'); ?>
                <input type="hidden" name="lw_action" value="save_settings">
                
                <div class="row g-4 pb-5">
                    <div class="col-md-5 d-flex flex-column gap-4">
                        <div class="lw-admin-card" style="height: auto;">
                            <div class="lw-card-header">
                                <h5><i class="fas fa-users-cog me-2"></i>Phân quyền Truy cập</h5>
                            </div>
                            <div class="p-4">
                                <p class="text-muted small mb-3">Mặc định <strong>Admin</strong> và <strong>User ID = 1</strong> luôn có quyền.</p>
                                <div class="mb-2">
                                    <div class="border rounded p-2 bg-light mb-2" id="allowed-users-display" style="min-height: 40px;">
                                        <?php 
                                        if(empty($allowed_users)) echo '<span class="text-muted fst-italic ms-2 small">Chưa có user nào.</span>';
                                        else {
                                            foreach($users as $u) {
                                                if(in_array($u->ID, $allowed_users)) echo '<span class="badge bg-primary me-1 mb-1">'.esc_html($u->display_name).'</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <input type="hidden" name="allowed_users_ids" id="allowed_users_ids" value="<?php echo implode(',', $allowed_users); ?>">
                                    <button type="button" class="btn btn-outline-primary btn-sm w-100" data-bs-toggle="modal" data-bs-target="#userSelectorModal">
                                        <i class="fas fa-user-plus me-1"></i> Quản lý User
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="lw-admin-card" style="height: auto;">
                            <div class="lw-card-header">
                                <h5><i class="fas fa-code me-2"></i>Cài đặt hiển thị</h5>
                            </div>
                            <div class="p-4">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-uppercase text-muted">Shortcode Tag</label>
                                    <input type="text" name="shortcode_tag" class="form-control lw-form-control fw-bold" value="<?php echo esc_attr($shortcode_tag); ?>" placeholder="lw_map">
                                    <div class="form-text small">Sử dụng: <code>[<?php echo esc_html($shortcode_tag); ?>]</code></div>
                                </div>
                                <div class="form-check form-switch d-flex justify-content-between align-items-center">
                                    <label class="form-check-label fw-bold small mb-0" for="autoDisplaySwitch">Tự động hiển thị bản đồ dưới bài Post</label>
                                    <input class="form-check-input" type="checkbox" id="autoDisplaySwitch" name="auto_display" value="yes" <?php checked($auto_display, 'yes'); ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7 d-flex flex-column gap-4">
                        <div class="lw-admin-card" style="height: auto;">
                            <div class="lw-card-header">
                                <h5><i class="fas fa-map me-2"></i>Giao diện Bản đồ (Tile)</h5>
                            </div>
                            <div class="p-4">
                                <select name="map_theme" id="map_theme_select" class="form-select lw-form-control fw-bold">
                                    <?php foreach($map_themes as $key => $theme): ?>
                                        <option value="<?php echo esc_attr($key); ?>" <?php selected($current_theme, $key); ?>>
                                            <?php echo esc_html($theme['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="lw-admin-card" style="height: auto;">
                            <div class="lw-card-header">
                                <h5><i class="fas fa-palette me-2"></i>Màu sắc chủ đạo (Gradient)</h5>
                            </div>
                            <div class="p-0">
                                <div class="gradient-scroll-box">
                                    <?php foreach($gradients as $gKey => $gVal): ?>
                                    <div class="gradient-list-item <?php echo ($current_gradient === $gKey) ? 'active' : ''; ?>" onclick="selectGradient(this, '<?php echo $gKey; ?>', '<?php echo $gVal['css']; ?>')">
                                        <div class="gradient-preview-circle" style="background: <?php echo $gVal['css']; ?>;"></div>
                                        <div class="gradient-label"><?php echo $gVal['name']; ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <input type="hidden" name="map_gradient" id="map_gradient_input" value="<?php echo esc_attr($current_gradient); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="lw-sticky-footer">
                    <button type="submit" id="btn-save-settings" class="btn btn-success btn-lg px-5 shadow fw-bold"><i class="fas fa-save me-2"></i> LƯU CÀI ĐẶT</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<div class="modal fade" id="postSelectorModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fs-6 fw-bold"><i class="fas fa-link text-primary me-2"></i> Chọn bài viết liên kết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-white sticky-top">
                    <input type="text" class="form-control lw-form-control" id="post-search-filter" placeholder="🔍 Nhập tiêu đề bài viết để tìm...">
                </div>
                <div class="list-group list-group-flush" id="modal-post-list"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userSelectorModal" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-users-cog me-2 text-primary"></i> Quản lý quyền truy cập</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="lw-user-search-wrapper">
                     <input type="text" class="form-control lw-form-control mb-2" id="user-search-input" placeholder="🔍 Tìm kiếm user...">
                     <div class="form-check ms-1">
                        <input class="form-check-input" type="checkbox" id="check-all-users">
                        <label class="form-check-label small text-muted" for="check-all-users">Chọn tất cả</label>
                     </div>
                </div>
                <div class="lw-user-list" id="user-list-container">
                    <?php foreach($users as $u): 
                        $is_checked = in_array($u->ID, $allowed_users) ? 'checked' : '';
                        $initials = strtoupper(substr($u->display_name, 0, 1));
                    ?>
                    <label class="user-list-item-label">
                        <input class="form-check-input me-3 user-checkbox" type="checkbox" value="<?php echo $u->ID; ?>" data-name="<?php echo esc_attr($u->display_name); ?>" <?php echo $is_checked; ?>>
                        <div class="user-avatar-placeholder"><?php echo $initials; ?></div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark"><?php echo esc_html($u->display_name); ?></div>
                            <div class="small text-muted"><?php echo esc_html($u->user_email); ?> (ID: <?php echo $u->ID; ?>)</div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-primary btn-sm fw-bold px-4 rounded-pill shadow-sm" onclick="saveUserPermissions()">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>