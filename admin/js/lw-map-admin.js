document.addEventListener('DOMContentLoaded', function() {
    // 1. DATA INITIALIZATION
    if (typeof lwMapData === 'undefined') return;
    var pointsData = lwMapData.points;
    var pointsMeta = lwMapData.pointsWithMeta;
    var allIcons = lwMapData.allIcons;
    var allPosts = lwMapData.allPosts;
    var currentTileUrl = lwMapData.tileUrl;
    
    var dashboardMap, managerMap, markersLayer; 
    var activePointIndex = null;
    var vnCenter = [16.0, 108.0]; var vnZoom = 5;

    // Helper: Get Icon URL
    function getIconUrl(iconName) {
        var found = allIcons.find(i => i.name === iconName);
        return found ? found.url : allIcons[0].url;
    }

    // Helper: Create Leaflet Map
    function createMap(id) {
        if(!document.getElementById(id)) return null;
        try {
             var map = L.map(id).setView(vnCenter, vnZoom);
             L.tileLayer(currentTileUrl, { attribution: '&copy; OpenStreetMap & Contributors' }).addTo(map);
             return map;
        } catch(e) { console.error("Map init error:", e); return null; }
    }

    // 2. TABS & MAP RENDERING LOGIC
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            if (event.target.getAttribute('data-bs-target') === '#dash-pane') {
                if(!dashboardMap) initDashboardMap();
                else setTimeout(() => dashboardMap.invalidateSize(), 100);
            }
            if (event.target.getAttribute('data-bs-target') === '#map-pane') {
                if(!managerMap) initManagerMap();
                else setTimeout(() => managerMap.invalidateSize(), 100);
            }
        });
    });

    // 3. DASHBOARD MAP (Preview Mode)
    function initDashboardMap() {
        dashboardMap = createMap('lw-dashboard-map');
        if(!dashboardMap) return;

        var sidebarList = document.getElementById('lw-dash-sidebar-list');
        if(sidebarList) {
            sidebarList.innerHTML = '';
            if(pointsMeta.length === 0) sidebarList.innerHTML = '<div class="p-3 text-center text-muted small">Chưa có địa điểm.</div>';
            pointsMeta.forEach(function(p) {
        // 1. Tạo Marker trên bản đồ (Giữ nguyên logic cũ)
        var customIcon = L.icon({ 
            iconUrl: getIconUrl(p.icon), 
            iconSize: [25, 25], // Ép về 25x25
            iconAnchor: [12.5, 25], // Điểm neo (giữa-đáy)
            popupAnchor: [0, -25] 
        });
        
        var marker = L.marker([p.lat, p.lng], {icon: customIcon}).addTo(dashboardMap);
        
        // Popup Content (Giữ nguyên logic cũ)
        var popupContent = `<div class='lw-card'>${p.has_post ? `<div class='lw-card-thumb-wrap'><img src='${p.thumb}' class='lw-card-thumb'></div>` : ''}<div class='lw-card-body ${!p.has_post ? 'pt-3' : ''}'><h3 class='lw-card-title'>${p.title}</h3>${p.link ? `<a href='${p.link}' target='_blank' class='lw-card-btn d-block text-center text-decoration-none text-white small'>XEM CHI TIẾT</a>` : ''}</div></div>`;
        marker.bindPopup(popupContent, { maxWidth: 280, minWidth: 280 });

        // 2. TẠO ITEM TRONG SIDEBAR (PHẦN SỬA ĐỔI QUAN TRỌNG)
        var listItem = document.createElement('div');
        listItem.className = 'lw-dash-item'; // Class khớp với CSS mới
        
        // Lấy URL icon chuẩn xác cho từng điểm
        var iconSrc = getIconUrl(p.icon); 

        // HTML mới: Sử dụng thẻ IMG thay vì thẻ I, thêm div bao bọc để flexbox hoạt động tốt
        listItem.innerHTML = `
            <img src="${iconSrc}" class="lw-dash-icon" alt="Icon">
            <div class="lw-dash-content">
                <span class="lw-dash-title">${p.title}</span>
                </div>
            <i class="fas fa-chevron-right lw-dash-arrow"></i>
        `;

        // Sự kiện Click (Giữ nguyên logic nhưng tối ưu UX)
        listItem.onclick = function() {
            // Xóa class active cũ
            document.querySelectorAll('.lw-dash-item').forEach(el => el.classList.remove('active'));
            // Thêm class active cho item này
            this.classList.add('active');
            
            // Bay đến địa điểm
            dashboardMap.flyTo([p.lat, p.lng], 15, { duration: 1.2 }); // Zoom level 15 để nhìn rõ hơn
            marker.openPopup();
        };

        sidebarList.appendChild(listItem);
    });
        }
    }
    // Auto init if tab is active on load
    if(document.querySelector('#dash-pane') && document.querySelector('#dash-pane').classList.contains('active')) initDashboardMap();

    // 4. MANAGER MAP (Edit Mode)
    function initManagerMap() {
        managerMap = createMap('lw-manager-map');
        if(!managerMap) return;

        markersLayer = L.layerGroup().addTo(managerMap);
        // Geocoder - Modern Search Bar
        if(L.Control.Geocoder) {
            L.Control.geocoder({ 
                defaultMarkGeocode: false, 
                position: 'topright', 
                placeholder: 'Tìm địa điểm...', 
                geocoder: L.Control.Geocoder.nominatim(),
                errorMessage: 'Không tìm thấy địa điểm',
                noResultsMessage: 'Không có kết quả'
            })
            .on('markgeocode', function(e) { 
                managerMap.fitBounds(e.geocode.bbox); 
            }).addTo(managerMap);
        }

        renderPointsManager();
        managerMap.on('click', function(e) {
            pointsData.push({ lat: e.latlng.lat, lng: e.latlng.lng, title: 'New Location', link: '', icon: allIcons[0].name, status: 'new' });
            renderPointsManager();
            setTimeout(() => {
                togglePointAccordion(pointsData.length - 1, true);
                var container = document.getElementById('points-container');
                if(container) container.scrollTop = container.scrollHeight;
            }, 100);
        });
    }

    window.togglePointAccordion = function(index, forceOpen = false) {
        var item = document.getElementById(`point-item-${index}`);
        if(!item) return;
        if(forceOpen) { item.classList.add('open'); item.scrollIntoView({behavior: 'smooth', block: 'center'}); } 
        else { item.classList.toggle('open'); }
    }

    function renderPointsManager() {
        var container = document.getElementById('points-container');
        if(!container) return;
        container.innerHTML = '';
        markersLayer.clearLayers();
        
        pointsData.forEach(function(p, index) {
            var customIcon = L.icon({ 
                iconUrl: getIconUrl(p.icon), 
                iconSize: [25, 25], // Ép về 25x25
                iconAnchor: [12.5, 25], 
                popupAnchor: [0, -25] 
            });
            var m = L.marker([p.lat, p.lng], {icon: customIcon, draggable: true}).addTo(markersLayer);
            
            // Drag Event
            m.on('dragend', function(ev){
                var pos = ev.target.getLatLng();
                p.lat = pos.lat; p.lng = pos.lng;
                var latInput = document.querySelector(`input[name="p_lat[]"][data-idx="${index}"]`);
                var lngInput = document.querySelector(`input[name="p_lng[]"][data-idx="${index}"]`);
                if(latInput) latInput.value = pos.lat;
                if(lngInput) lngInput.value = pos.lng;
                document.getElementById(`coord-display-${index}`).innerText = `📍 ${pos.lat.toFixed(4)}, ${pos.lng.toFixed(4)}`;
            });
            m.on('click', function() { togglePointAccordion(index, true); });

            var iconOptions = allIcons.map(i => `<option value="${i.name}" ${i.name === p.icon ? 'selected' : ''}>${i.name}</option>`).join('');
            
            var html = `
            <div class="point-item ${p.status === 'new' ? 'status-new' : 'status-saved'}" id="point-item-${index}">
                <div class="point-header" onclick="togglePointAccordion(${index})">
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <span class="point-badge">${index+1}</span>
                        <h4 class="point-title-display" id="header-title-${index}">${p.title}</h4>
                    </div>
                    <i class="fas fa-chevron-down text-muted small ms-2"></i>
                </div>
                <div class="point-body">
                    <div class="lw-form-group">
                        <span class="lw-label">Tên địa điểm</span>
                        <input type="text" name="p_title[]" class="form-control lw-form-control fw-bold title-input" data-index="${index}" value="${p.title}" required>
                    </div>
                    <div class="lw-form-group">
                        <span class="lw-label">Liên kết</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm ${p.link ? 'btn-outline-primary' : 'btn-outline-secondary'} flex-grow-1 text-start text-truncate open-post-modal" data-index="${index}" style="height: 38px;">
                                <i class="fas fa-link me-1"></i> ${p.link || 'Chọn bài viết...'}
                            </button>
                            <input type="hidden" name="p_link[]" value="${p.link}">
                        </div>
                    </div>
                    <div class="lw-form-group">
                        <span class="lw-label">Icon</span>
                        <div class="icon-select-improved">
                            <div class="icon-preview-box"><img src="${getIconUrl(p.icon)}" class="lw-icon-preview-small" id="icon-prev-${index}"></div>
                            <select name="p_icon[]" class="form-select form-select-sm icon-selector" data-index="${index}">${iconOptions}</select>
                        </div>
                    </div>
                    <div class="point-footer-action">
                        <span class="coord-text" id="coord-display-${index}">📍 ${parseFloat(p.lat).toFixed(4)}, ${parseFloat(p.lng).toFixed(4)}</span>
                        <button type="button" class="btn-delete-custom remove-point" data-index="${index}"><i class="fas fa-trash-alt"></i> Xóa</button>
                    </div>
                    <input type="hidden" name="p_lat[]" data-idx="${index}" value="${p.lat}">
                    <input type="hidden" name="p_lng[]" data-idx="${index}" value="${p.lng}">
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
        });

        // Re-attach Event Listeners for Dynamic Elements
        document.querySelectorAll('.open-post-modal').forEach(btn => btn.addEventListener('click', function(e) { 
            e.stopPropagation(); 
            activePointIndex = this.dataset.index; 
            if(window.postModal) { renderPostList(); window.postModal.show(); }
        }));

        document.querySelectorAll('.icon-selector').forEach(sel => sel.addEventListener('change', function() { 
            var idx = this.dataset.index; 
            pointsData[idx].icon = this.value; 
            document.getElementById(`icon-prev-${idx}`).src = getIconUrl(this.value); 
            renderPointsManager(); 
            setTimeout(() => togglePointAccordion(idx, true), 50); 
        }));

        document.querySelectorAll('.title-input').forEach(inp => inp.addEventListener('input', function(){ 
            pointsData[this.dataset.index].title = this.value; 
            document.getElementById(`header-title-${this.dataset.index}`).innerText = this.value || 'Chưa đặt tên'; 
        }));

        document.querySelectorAll('.remove-point').forEach(b => b.addEventListener('click', function(e) { 
            e.stopPropagation(); 
            if(confirm('Xóa điểm này?')) { 
                pointsData.splice(this.dataset.index, 1); 
                renderPointsManager(); 
            } 
        }));
    }

    // 5. POST MODAL LOGIC
    var postModalElement = document.getElementById('postSelectorModal');
    if (postModalElement) {
        window.postModal = new bootstrap.Modal(postModalElement);
        var postListContainer = document.getElementById('modal-post-list');
        
        window.renderPostList = function(filter = '') {
            postListContainer.innerHTML = '';
            var filtered = allPosts.filter(p => p.title.toLowerCase().includes(filter.toLowerCase()));
            if(filtered.length === 0) { postListContainer.innerHTML = '<div class="text-center text-muted p-4">Không tìm thấy.</div>'; return; }
            filtered.forEach(p => {
                var item = document.createElement('button');
                item.className = 'list-group-item list-group-item-action py-3 px-4';
                item.innerHTML = `<span class="fw-medium">${p.title}</span>`;
                item.onclick = function() {
                    pointsData[activePointIndex].link = p.link;
                    if(!pointsData[activePointIndex].title || pointsData[activePointIndex].title === 'New Location') pointsData[activePointIndex].title = p.title;
                    renderPointsManager();
                    setTimeout(() => { togglePointAccordion(activePointIndex, true); window.postModal.hide(); }, 100);
                };
                postListContainer.appendChild(item);
            });
        }
        var filterInput = document.getElementById('post-search-filter');
        if(filterInput) filterInput.addEventListener('input', (e) => renderPostList(e.target.value));
    }

    // 6. ICON MANAGER LOGIC (FIXED)
    // WP Media Uploader Handler
    var mediaUploader;
    document.body.addEventListener('click', function(e) {
        if (e.target.closest('.btn-upload-img')) {
            e.preventDefault();
            var btn = e.target.closest('.btn-upload-img');
            var inputField = btn.previousElementSibling; // The input text field
            
            if (mediaUploader) {
                mediaUploader.open();
                // We need to re-bind the select event because scope changes
                mediaUploader.off('select'); // clear previous events
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    inputField.value = attachment.url;
                    // Update preview
                    var previewImg = btn.closest('tr').querySelector('.lw-icon-preview');
                    if(previewImg) previewImg.src = attachment.url;
                });
                return;
            }

            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Chọn Icon',
                button: { text: 'Sử dụng Icon này' },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                inputField.value = attachment.url;
                var previewImg = btn.closest('tr').querySelector('.lw-icon-preview');
                if(previewImg) previewImg.src = attachment.url;
            });

            mediaUploader.open();
        }
    });

    // Add New Icon Row
    var addIconBtn = document.getElementById('add-icon-row');
    if(addIconBtn) {
        addIconBtn.addEventListener('click', function() {
            var rowHtml = `
            <tr>
                <td>
                    <input type="text" name="icon_name[]" class="form-control lw-form-control" placeholder="Tên icon" required>
                </td>
                <td>
                    <div class="input-group">
                        <input type="text" name="icon_url[]" class="form-control lw-form-control img-url-input" placeholder="https://..." required>
                        <button type="button" class="btn btn-upload btn-upload-img"><i class="fas fa-cloud-upload-alt"></i></button>
                    </div>
                </td>
                <td class="text-center">
                    <div class="lw-icon-preview-box mx-auto">
                        <img src="" class="lw-icon-preview">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn-icon-delete remove-row" title="Xóa">
                        <i class="far fa-trash-alt"></i>
                    </button>
                </td>
            </tr>`;
            document.getElementById('icon-list-body').insertAdjacentHTML('beforeend', rowHtml);
        });
    }

    // Remove Icon Row
    document.body.addEventListener('click', function(e){ 
        if(e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
        }
    });

    // 7. SETTINGS PAGE LOGIC (FIXED)
    window.selectGradient = function(el, gKey, cssVal) {
        document.querySelectorAll('.gradient-list-item').forEach(c => c.classList.remove('active'));
        el.classList.add('active');
        document.getElementById('map_gradient_input').value = gKey;
        
        // Instant Visual Feedback
        var saveBtn = document.getElementById('btn-save-settings');
        if(saveBtn) {
            saveBtn.style.background = cssVal;
            saveBtn.style.borderColor = 'transparent';
        }
    }

    // Save User Permissions Logic
    window.saveUserPermissions = function() {
        var selectedIds = [];
        var displayContainer = document.getElementById('allowed-users-display');
        displayContainer.innerHTML = '';
        
        document.querySelectorAll('.user-checkbox:checked').forEach(cb => {
            selectedIds.push(cb.value);
            var badge = document.createElement('span');
            badge.className = 'badge bg-primary me-1 mb-1';
            badge.innerText = cb.getAttribute('data-name');
            displayContainer.appendChild(badge);
        });

        if(selectedIds.length === 0) displayContainer.innerHTML = '<span class="text-muted fst-italic ms-2 small">Chưa có user nào.</span>';
        
        // Update hidden input
        document.getElementById('allowed_users_ids').value = selectedIds.join(',');
        
        // Close modal
        var userModal = bootstrap.Modal.getInstance(document.getElementById('userSelectorModal'));
        if(userModal) userModal.hide();
    }
    
    // User Search Filter
    if(document.getElementById('user-search-input')) {
        document.getElementById('user-search-input').addEventListener('input', function(e){
            var val = e.target.value.toLowerCase();
            document.querySelectorAll('.user-list-item-label').forEach(label => {
                var text = label.innerText.toLowerCase();
                label.style.display = text.includes(val) ? 'flex' : 'none';
            });
        });
        
        document.getElementById('check-all-users').addEventListener('change', function(e){
             document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = e.target.checked);
        });
    }
});