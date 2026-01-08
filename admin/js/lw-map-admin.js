document.addEventListener('DOMContentLoaded', function() {
    // 1. DATA INITIALIZATION
    if (typeof lwMapData === 'undefined') return;
    var pointsData = lwMapData.points;
    var pointsMeta = lwMapData.pointsWithMeta;
    var allIcons = lwMapData.allIcons;
    var allPosts = lwMapData.allPosts;
    var mapType = lwMapData.mapType || 'leaflet'; // Default fallback
    var currentTileUrl = lwMapData.tileUrl;
    var mapboxKey = lwMapData.mapboxKey;
    var mapboxStyle = lwMapData.mapboxStyle;
    
    var dashboardMap, managerMap, markersLayer; 
    var activePointIndex = null;
    var vnCenter = [16.0, 108.0]; var vnZoom = 5;

    // Helper: Get Icon URL
    function getIconUrl(iconName) {
        var found = allIcons.find(i => i.name === iconName);
        return found ? found.url : allIcons[0].url;
    }

    // Helper: Create Map (Mapbox or Leaflet)
    function createMap(id) {
        if(!document.getElementById(id)) return null;
        try {
            if (mapType === 'mapbox' && typeof mapboxgl !== 'undefined' && mapboxKey) {
                // Mapbox
                mapboxgl.accessToken = mapboxKey;
                var styleId = mapboxStyle || 'mapbox/streets-v12';
                var map = new mapboxgl.Map({
                    container: id,
                    style: 'mapbox://styles/' + styleId,
                    center: [vnCenter[1], vnCenter[0]], // Mapbox uses [lng, lat]
                    zoom: vnZoom
                });
                return { type: 'mapbox', instance: map };
            } else {
                // Leaflet (default or fallback)
                var map = L.map(id).setView(vnCenter, vnZoom);
                L.tileLayer(currentTileUrl || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap & Contributors' }).addTo(map);
                return { type: 'leaflet', instance: map };
            }
        } catch(e) { 
            console.error("Map init error:", e); 
            // Fallback to Leaflet if Mapbox fails
            try {
                var map = L.map(id).setView(vnCenter, vnZoom);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap & Contributors' }).addTo(map);
                return { type: 'leaflet', instance: map };
            } catch(e2) {
                console.error("Leaflet fallback error:", e2);
                return null;
            }
        }
    }

    // 2. TABS & MAP RENDERING LOGIC
    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tabEl => {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            if (event.target.getAttribute('data-bs-target') === '#dash-pane') {
                if(!dashboardMap) initDashboardMap();
                else {
                    if (dashboardMap.type === 'mapbox') {
                        dashboardMap.instance.resize();
                    } else {
                        setTimeout(() => dashboardMap.instance.invalidateSize(), 100);
                    }
                }
            }
            if (event.target.getAttribute('data-bs-target') === '#map-pane') {
                if(!managerMap) initManagerMap();
                else {
                    if (managerMap.type === 'mapbox') {
                        managerMap.instance.resize();
                    } else {
                        setTimeout(() => managerMap.instance.invalidateSize(), 100);
                    }
                }
            }
        });
    });

    // Helper: Create Popup HTML
    function createPopupHtml(p) {
        var popupContent = `<div class='lw-card'>`;
        if(p.has_post) {
            popupContent += `<div class='lw-card-thumb-wrap'><img src='${p.thumb}' class='lw-card-thumb'></div>`;
        }
        popupContent += `<div class='lw-card-body ${!p.has_post ? 'pt-3' : ''}'>`;
        popupContent += `<div class='lw-card-header'>`;
        popupContent += `<h3 class='lw-card-title'>${p.title}</h3>`;
        if(p.date) {
            popupContent += `<div class='lw-card-date'><i class='far fa-calendar-alt'></i> ${p.date}</div>`;
        }
        popupContent += `</div>`;
        if(p.excerpt) {
            popupContent += `<div class='lw-card-excerpt'>${p.excerpt}</div>`;
        }
        if(p.link) {
            popupContent += `<a href='${p.link}' target='_blank' class='lw-card-btn d-block text-center text-decoration-none text-white small'>XEM CHI TIẾT</a>`;
        }
        popupContent += `</div></div>`;
        return popupContent;
    }

    // Helper: Add Marker to Map (supports both Mapbox and Leaflet)
    function addMarkerToMap(mapObj, point, popupHtml, options) {
        if (mapObj.type === 'mapbox') {
            // Mapbox marker
            var el = document.createElement('div');
            el.className = 'mapbox-marker';
            el.style.width = '25px';
            el.style.height = '25px';
            el.style.backgroundImage = 'url(' + getIconUrl(point.icon) + ')';
            el.style.backgroundSize = 'contain';
            el.style.backgroundRepeat = 'no-repeat';
            el.style.cursor = 'pointer';
            
            var popup = new mapboxgl.Popup({ 
                offset: { 'bottom': [0, -10] },
                maxWidth: '320px', 
                minWidth: '320px',
                anchor: 'bottom'
            }).setHTML(popupHtml);
            
            var marker = new mapboxgl.Marker(el)
                .setLngLat([point.lng, point.lat])
                .setPopup(popup)
                .addTo(mapObj.instance);
            
            // Click event để pan map và hiển thị popup ở giữa
            el.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn event bubbling
                
                var map = mapObj.instance;
                var container = map.getContainer();
                var containerHeight = container.clientHeight;
                var containerWidth = container.clientWidth;
                
                // Tính toán offset để popup ở giữa màn hình
                // Popup height khoảng 200-300px, cần offset lên trên
                var offsetY = containerHeight / 2 - 150; // 150px là khoảng cách từ center đến popup
                
                // Pan map để marker ở vị trí center với offset
                map.easeTo({
                    center: [point.lng, point.lat],
                    offset: [0, offsetY],
                    duration: 500
                });
                
                // Mở popup sau khi pan (dùng openPopup thay vì togglePopup)
                setTimeout(function() {
                    if (!marker.getPopup().isOpen()) {
                        marker.openPopup();
                    }
                }, 100);
            });
            
            return marker;
        } else {
            // Leaflet marker
            var customIcon = L.icon({ 
                iconUrl: getIconUrl(point.icon), 
                iconSize: [25, 25],
                iconAnchor: [12.5, 25],
                popupAnchor: [0, -25] 
            });
            var marker = L.marker([point.lat, point.lng], {icon: customIcon}).addTo(mapObj.instance);
            marker.bindPopup(popupHtml, { 
                maxWidth: 320, 
                minWidth: 320, 
                className: 'lw-map-popup',
                autoPan: true,
                autoPanPadding: [50, 50]
            });
            
            // Click event để pan map và hiển thị popup ở giữa
            marker.on('click', function(e) {
                e.originalEvent.stopPropagation(); // Ngăn event bubbling
                
                var map = mapObj.instance;
                var container = map.getContainer();
                var containerHeight = container.clientHeight;
                var containerWidth = container.clientWidth;
                
                // Tính toán vị trí để popup ở giữa màn hình
                // Marker cần ở vị trí trên center một chút (150px) để popup hiển thị ở giữa
                var newLatLng = map.containerPointToLatLng([
                    containerWidth / 2,
                    containerHeight / 2 - 150
                ]);
                
                // Tạm thời tắt autoPan để tránh conflict
                var popup = marker.getPopup();
                var autoPanWasEnabled = popup.options.autoPan;
                popup.options.autoPan = false;
                
                // Pan map với animation
                map.panTo(newLatLng, { animate: true, duration: 0.5 });
                
                // Mở popup sau khi pan xong
                setTimeout(function() {
                    marker.openPopup();
                    // Khôi phục autoPan sau khi mở popup
                    if (autoPanWasEnabled) {
                        popup.options.autoPan = true;
                    }
                }, 550);
            });
            
            return marker;
        }
    }

    // 3. DASHBOARD MAP (Preview Mode)
    function initDashboardMap() {
        dashboardMap = createMap('lw-dashboard-map');
        if(!dashboardMap) return;

        var sidebarList = document.getElementById('lw-dash-sidebar-list');
        if(sidebarList) {
            sidebarList.innerHTML = '';
            if(pointsMeta.length === 0) sidebarList.innerHTML = '<div class="p-3 text-center text-muted small">Chưa có địa điểm.</div>';
            pointsMeta.forEach(function(p) {
                // Create popup HTML
                var popupHtml = createPopupHtml(p);
                
                // Add marker to map
                var marker = addMarkerToMap(dashboardMap, p, popupHtml);

                // 2. TẠO ITEM TRONG SIDEBAR
                var listItem = document.createElement('div');
                listItem.className = 'lw-dash-item';
                
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

                // Sự kiện Click
                listItem.onclick = function() {
                    // Xóa class active cũ
                    document.querySelectorAll('.lw-dash-item').forEach(el => el.classList.remove('active'));
                    // Thêm class active cho item này
                    this.classList.add('active');
                    
                    // Bay đến địa điểm
                    if (dashboardMap.type === 'mapbox') {
                        dashboardMap.instance.flyTo({ center: [p.lng, p.lat], zoom: 15, duration: 1200 });
                        if (marker.getPopup) marker.getPopup().addTo(dashboardMap.instance);
                    } else {
                        dashboardMap.instance.flyTo([p.lat, p.lng], 15, { duration: 1.2 });
                        marker.openPopup();
                    }
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

        // Initialize markers layer based on map type
        if (managerMap.type === 'mapbox') {
            markersLayer = []; // Array for Mapbox markers
            // Geocoder for Mapbox
            if (typeof mapboxgl !== 'undefined' && typeof MapboxGeocoder !== 'undefined') {
                var geocoder = new MapboxGeocoder({
                    accessToken: mapboxKey || '',
                    mapboxgl: mapboxgl,
                    marker: false
                });
                managerMap.instance.addControl(geocoder);
                geocoder.on('result', function(e) {
                    managerMap.instance.flyTo({ center: e.result.center, zoom: 15 });
                });
            }
        } else {
            markersLayer = L.layerGroup().addTo(managerMap.instance);
            // Geocoder for Leaflet
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
                    managerMap.instance.fitBounds(e.geocode.bbox); 
                }).addTo(managerMap.instance);
            }
        }

        renderPointsManager();
        
        // Click event handler
        if (managerMap.type === 'mapbox') {
            managerMap.instance.on('click', function(e) {
                pointsData.push({ lat: e.lngLat.lat, lng: e.lngLat.lng, title: 'New Location', link: '', icon: allIcons[0].name, status: 'new' });
                renderPointsManager();
                setTimeout(() => {
                    togglePointAccordion(pointsData.length - 1, true);
                    var container = document.getElementById('points-container');
                    if(container) container.scrollTop = container.scrollHeight;
                }, 100);
            });
        } else {
            managerMap.instance.on('click', function(e) {
                pointsData.push({ lat: e.latlng.lat, lng: e.latlng.lng, title: 'New Location', link: '', icon: allIcons[0].name, status: 'new' });
                renderPointsManager();
                setTimeout(() => {
                    togglePointAccordion(pointsData.length - 1, true);
                    var container = document.getElementById('points-container');
                    if(container) container.scrollTop = container.scrollHeight;
                }, 100);
            });
        }
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
        
        // Clear markers based on map type
        if (managerMap.type === 'mapbox') {
            if (markersLayer && markersLayer.length > 0) {
                markersLayer.forEach(function(marker) {
                    marker.remove();
                });
            }
            markersLayer = [];
        } else {
            if (markersLayer && markersLayer.clearLayers) {
                markersLayer.clearLayers();
            }
        }
        
        pointsData.forEach(function(p, index) {
            var m;
            
            if (managerMap.type === 'mapbox') {
                // Mapbox marker
                var el = document.createElement('div');
                el.className = 'mapbox-marker';
                el.style.width = '25px';
                el.style.height = '25px';
                el.style.backgroundImage = 'url(' + getIconUrl(p.icon) + ')';
                el.style.backgroundSize = 'contain';
                el.style.backgroundRepeat = 'no-repeat';
                el.style.cursor = 'move';
                
                m = new mapboxgl.Marker({ element: el, draggable: true })
                    .setLngLat([p.lng, p.lat])
                    .addTo(managerMap.instance);
                
                // Drag Event for Mapbox
                m.on('dragend', function() {
                    var pos = m.getLngLat();
                    p.lat = pos.lat;
                    p.lng = pos.lng;
                    var latInput = document.querySelector(`input[name="p_lat[]"][data-idx="${index}"]`);
                    var lngInput = document.querySelector(`input[name="p_lng[]"][data-idx="${index}"]`);
                    if(latInput) latInput.value = pos.lat;
                    if(lngInput) lngInput.value = pos.lng;
                    document.getElementById(`coord-display-${index}`).innerText = `📍 ${pos.lat.toFixed(4)}, ${pos.lng.toFixed(4)}`;
                });
                
                // Click event
                el.addEventListener('click', function() { togglePointAccordion(index, true); });
                
                markersLayer.push(m);
            } else {
                // Leaflet marker
                var customIcon = L.icon({ 
                    iconUrl: getIconUrl(p.icon), 
                    iconSize: [25, 25],
                    iconAnchor: [12.5, 25], 
                    popupAnchor: [0, -25] 
                });
                m = L.marker([p.lat, p.lng], {icon: customIcon, draggable: true}).addTo(markersLayer);
                
                // Drag Event for Leaflet
                m.on('dragend', function(ev){
                    var pos = ev.target.getLatLng();
                    p.lat = pos.lat;
                    p.lng = pos.lng;
                    var latInput = document.querySelector(`input[name="p_lat[]"][data-idx="${index}"]`);
                    var lngInput = document.querySelector(`input[name="p_lng[]"][data-idx="${index}"]`);
                    if(latInput) latInput.value = pos.lat;
                    if(lngInput) lngInput.value = pos.lng;
                    document.getElementById(`coord-display-${index}`).innerText = `📍 ${pos.lat.toFixed(4)}, ${pos.lng.toFixed(4)}`;
                });
                
                m.on('click', function() { togglePointAccordion(index, true); });
            }

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