<?php
class LW_Map_Public {

    public function init() {
        add_shortcode( get_option('lw_map_shortcode_tag', 'lw_map'), array( $this, 'render_shortcode' ) );
        add_filter( 'the_content', array( $this, 'auto_display_map' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
    }

    public function enqueue_public_assets() {
        // Chỉ load assets nếu có shortcode hoặc auto display để tối ưu
        global $post;
        $tag = get_option('lw_map_shortcode_tag', 'lw_map');
        $has_shortcode = false;
        
        // Kiểm tra shortcode trong post content
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, $tag)) {
            $has_shortcode = true;
        }
        
        // Kiểm tra auto display (chỉ trên single post)
        $auto_display = false;
        if (is_single() && get_post_type() == 'post' && get_option('lw_map_auto_display', 'no') == 'yes') {
            // Kiểm tra xem có điểm nào không
            $raw_points = get_option('lw_map_points', []);
            if (!empty($raw_points) && is_array($raw_points)) {
                $auto_display = true;
            }
        }

        if ($has_shortcode || $auto_display) {
            $map_type = get_option('lw_map_type', 'mapbox'); // Default là mapbox
            
            if ($map_type === 'mapbox') {
                // Mapbox GL JS
                wp_enqueue_style( 'mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css' );
                wp_enqueue_script( 'mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js', [], null, true );
            } else {
                // Leaflet
                wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' );
                wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true );
            }
            
            // Enqueue Font Awesome cho icon calendar trong popup
            wp_enqueue_style( 'fa-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' );
        }
    }

    public function render_shortcode( $atts ) {
        $raw_points = get_option('lw_map_points', []);
        if (empty($raw_points) || !is_array($raw_points)) {
            return ''; // Trả về rỗng nếu không có điểm
        }
        
        $all_icons = lw_get_all_icons();
        $points = lw_prepare_points_data($raw_points);
        
        if (empty($points)) {
            return ''; // Trả về rỗng nếu không có điểm hợp lệ
        }
        
        $map_type = get_option('lw_map_type', 'mapbox'); // Default là mapbox
        $mapbox_key = lw_get_mapbox_api_key();
        $tile_url = ''; // Initialize để tránh undefined
        
        if ($map_type === 'mapbox') {
            $current_mapbox_style = get_option('lw_map_mapbox_style', 'streets');
            $mapbox_styles = lw_get_mapbox_styles();
            $current_style_id = isset($mapbox_styles[$current_mapbox_style]) ? $mapbox_styles[$current_mapbox_style]['style'] : $mapbox_styles['streets']['style'];
        } else {
            $current_theme = get_option('lw_map_theme', 'osm');
            $themes = lw_get_map_themes();
            $tile_url = isset($themes[$current_theme]) ? $themes[$current_theme]['url'] : $themes['osm']['url'];
        }
        
        // Fallback tile URL nếu không có
        if (empty($tile_url)) {
            $themes = lw_get_map_themes();
            $tile_url = $themes['osm']['url'];
        }
        
        $current_gradient = get_option('lw_map_gradient', 'default');
        $gradients = lw_get_gradients();
        $grad_css = isset($gradients[$current_gradient]) ? $gradients[$current_gradient]['css'] : $gradients['default']['css'];

        $json_points = json_encode($points);
        $json_icons = json_encode($all_icons);

        // Output Buffer để return HTML sạch
        ob_start();
        ?>
        <style>
            .lw-card-btn, .lw-card-thumb-wrap { background: <?php echo $grad_css; ?> !important; }
            .lw-card { 
                border: none; 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
                background: #fff;
            }
            .lw-card-thumb-wrap { 
                height: 180px; 
                border-radius: 0; 
                overflow: hidden; 
                position: relative;
                background: <?php echo $grad_css; ?>;
            }
            .lw-card-thumb { 
                width: 100%; 
                height: 100%; 
                object-fit: cover;
                transition: transform 0.3s ease;
            }
            .lw-card:hover .lw-card-thumb {
                transform: scale(1.05);
            }
            .lw-card-body { 
                padding: 20px; 
            }
            .lw-card-header {
                margin-bottom: 12px;
            }
            .lw-card-title { 
                font-size: 18px; 
                font-weight: 700; 
                margin: 0 0 8px 0; 
                color: #1e293b;
                line-height: 1.4;
            }
            .lw-card-date {
                font-size: 12px;
                color: #94a3b8;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 12px;
            }
            .lw-card-date i {
                font-size: 11px;
            }
            .lw-card-excerpt { 
                font-size: 14px; 
                color: #64748b; 
                margin-bottom: 16px;
                line-height: 1.6;
                display: -webkit-box;
                -webkit-line-clamp: 3;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }
            .lw-card-btn { 
                color: #fff !important; 
                text-decoration: none !important; 
                display: block; 
                text-align: center; 
                padding: 12px 20px; 
                border-radius: 8px; 
                font-size: 14px; 
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            }
            .lw-card-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
                opacity: 0.95;
            }
            /* Map Marker Icon Size Fix */
            .leaflet-marker-icon {
                width: 25px !important;
                height: 25px !important;
                max-width: 25px !important;
                max-height: 25px !important;
            }
            .leaflet-marker-icon img {
                width: 25px !important;
                height: 25px !important;
                max-width: 25px !important;
                max-height: 25px !important;
                object-fit: contain !important;
            }
            /* Leaflet Popup Styling */
            .leaflet-popup-content-wrapper {
                border-radius: 12px !important;
                padding: 0 !important;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
            }
            .leaflet-popup-content {
                margin: 0 !important;
                width: 320px !important;
            }
            .leaflet-popup-tip {
                background: #fff !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            }
            /* Mapbox Marker */
            .mapbox-marker {
                width: 25px !important;
                height: 25px !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                cursor: pointer;
            }
            
            /* Mapbox Popup Styling */
            .mapboxgl-popup {
                max-width: 320px !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            }
            
            .mapboxgl-popup-content {
                padding: 0 !important;
                border-radius: 12px !important;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15) !important;
                overflow: hidden;
            }
            
            .mapboxgl-popup-tip {
                border-top-color: #fff !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            }
            
            .mapboxgl-popup-close-button {
                width: 28px !important;
                height: 28px !important;
                font-size: 18px !important;
                color: #64748b !important;
                background: rgba(255, 255, 255, 0.9) !important;
                border-radius: 50% !important;
                right: 8px !important;
                top: 8px !important;
                transition: all 0.2s ease !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border: 1px solid #e2e8f0 !important;
            }
            
            .mapboxgl-popup-close-button:hover {
                background: #fff !important;
                color: #ef4444 !important;
                border-color: #ef4444 !important;
                transform: scale(1.1);
            }
        </style>
        <div class="lw-frontend-wrapper" style="position: relative; margin: 30px 0;">
            <div id="lw-frontend-map" style="height: 600px; width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); z-index: 1; border:1px solid #ddd; background: #f8fafc;"></div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            var mapContainer = document.getElementById("lw-frontend-map");
            if(!mapContainer) return;
            
            var points = <?php echo $json_points; ?>;
            var icons = <?php echo $json_icons; ?>;
            var mapType = '<?php echo esc_js($map_type); ?>';
            var mapboxKey = '<?php echo esc_js($mapbox_key); ?>';
            var mapboxStyle = '<?php echo esc_js($current_style_id); ?>';
            var leafletTileUrl = "<?php echo esc_js($tile_url); ?>";

            if (!points || !Array.isArray(points) || points.length === 0) {
                mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 14px;">Chưa có địa điểm để hiển thị</div>';
                return;
            }

            if (!icons || !Array.isArray(icons) || icons.length === 0) {
                mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 14px;">Chưa có icon để hiển thị</div>';
                return;
            }

            // Helper function to create popup HTML
            function createPopupHtml(p) {
                var popupHtml = "<div class='lw-card'>";
                if(p.has_post) {
                    popupHtml += "<div class='lw-card-thumb-wrap'><img src='" + p.thumb + "' class='lw-card-thumb'></div>";
                }
                popupHtml += "<div class='lw-card-body " + (!p.has_post ? 'pt-3' : '') + "'>";
                popupHtml += "<div class='lw-card-header'>";
                popupHtml += "<h3 class='lw-card-title'>" + p.title + "</h3>";
                if(p.date) {
                    popupHtml += "<div class='lw-card-date'><i class='far fa-calendar-alt'></i> " + p.date + "</div>";
                }
                popupHtml += "</div>";
                if(p.excerpt) {
                    popupHtml += "<div class='lw-card-excerpt'>" + p.excerpt + "</div>";
                }
                if(p.link) {
                    popupHtml += "<a href='" + p.link + "' target='_blank' class='lw-card-btn'>" + (p.has_post ? 'XEM CHI TIẾT' : 'XEM LIÊN KẾT') + "</a>";
                }
                popupHtml += "</div></div>";
                return popupHtml;
            }

            // Helper function to get icon URL
            function getIconUrl(iconName) {
                var foundIcon = icons.find(i => i.name === iconName);
                return foundIcon ? foundIcon.url : icons[0].url;
            }

            // Try Mapbox first if selected
            if (mapType === 'mapbox' && typeof mapboxgl !== 'undefined' && mapboxKey) {
                try {
                    mapboxgl.accessToken = mapboxKey;
                    var map = new mapboxgl.Map({
                        container: 'lw-frontend-map',
                        style: 'mapbox://styles/' + mapboxStyle,
                        center: [108.0, 16.0],
                        zoom: 5
                    });

                    var mapboxError = false;
                    
                    map.on('error', function(e) {
                        console.error('Mapbox error:', e);
                        mapboxError = true;
                        // Fallback to Leaflet
                        initLeafletMap();
                    });

                    map.on('load', function() {
                        if (mapboxError) return;
                        
                        points.forEach(function(p) {
                            if (!p || !p.lat || !p.lng) return;
                            
                            var el = document.createElement('div');
                            el.className = 'mapbox-marker';
                            el.style.backgroundImage = 'url(' + getIconUrl(p.icon) + ')';
                            el.style.cursor = 'pointer';
                            
                            var popup = new mapboxgl.Popup({ 
                                offset: { 'bottom': [0, -10] },
                                maxWidth: '320px', 
                                minWidth: '320px',
                                anchor: 'bottom'
                            }).setHTML(createPopupHtml(p));
                            
                            var marker = new mapboxgl.Marker(el)
                                .setLngLat([p.lng, p.lat])
                                .setPopup(popup)
                                .addTo(map);
                            
                            // Click event để pan map và hiển thị popup ở giữa
                            el.addEventListener('click', function(e) {
                                e.stopPropagation(); // Ngăn event bubbling
                                
                                var container = map.getContainer();
                                var containerHeight = container.clientHeight;
                                var offsetY = containerHeight / 2 - 150; // Offset để popup ở giữa
                                
                                map.easeTo({
                                    center: [p.lng, p.lat],
                                    offset: [0, offsetY],
                                    duration: 500
                                });
                                
                                setTimeout(function() {
                                    var popup = marker.getPopup();
                                    if (popup && !popup.isOpen()) {
                                        marker.togglePopup();
                                    }
                                }, 100);
                            });
                        });
                    });

                    // Timeout fallback - if map doesn't load in 10 seconds, use Leaflet
                    setTimeout(function() {
                        if (!map.loaded()) {
                            console.warn('Mapbox load timeout, falling back to Leaflet');
                            map.remove();
                            initLeafletMap();
                        }
                    }, 10000);
                    
                } catch(e) {
                    console.error('Mapbox initialization error:', e);
                    initLeafletMap();
                }
            } else {
                // Use Leaflet
                initLeafletMap();
            }

            function initLeafletMap() {
                if (typeof L === 'undefined') {
                    mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #ef4444; font-size: 14px;">Lỗi: Không thể tải thư viện bản đồ</div>';
                    return;
                }

                try {
                    var map = L.map("lw-frontend-map").setView([16.0, 108.0], 5);
                    
                    var tileLayer = L.tileLayer(leafletTileUrl || 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
                        attribution: "&copy; OpenStreetMap",
                        errorTileUrl: 'https://via.placeholder.com/256x256/e2e8f0/94a3b8?text=Error'
                    });
                    
                    tileLayer.addTo(map);
                    
                    tileLayer.on('tileerror', function() {
                        console.warn('Tile load error, using fallback');
                        map.removeLayer(tileLayer);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
                            attribution: "&copy; OpenStreetMap" 
                        }).addTo(map);
                    });

                    points.forEach(function(p) {
                        if (!p || !p.lat || !p.lng) return;
                        
                        var customIcon = L.icon({ 
                            iconUrl: getIconUrl(p.icon), 
                            iconSize: [25, 25], 
                            iconAnchor: [12.5, 25], 
                            popupAnchor: [0, -25] 
                        });
                        var marker = L.marker([p.lat, p.lng], {icon: customIcon}).addTo(map);
                        marker.bindPopup(createPopupHtml(p), { 
                            maxWidth: 320, 
                            minWidth: 320, 
                            className: 'lw-map-popup',
                            autoPan: true,
                            autoPanPadding: [50, 50]
                        });
                        
                        // Click event để pan map và hiển thị popup ở giữa
                        marker.on('click', function(e) {
                            e.originalEvent.stopPropagation(); // Ngăn event bubbling
                            
                            var container = map.getContainer();
                            var containerHeight = container.clientHeight;
                            var containerWidth = container.clientWidth;
                            
                            // Tính toán vị trí để popup ở giữa màn hình
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
                    });
                } catch(e) {
                    console.error('Leaflet initialization error:', e);
                    mapContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #ef4444; font-size: 14px;">Lỗi khởi tạo bản đồ: ' + e.message + '</div>';
                }
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    private static $processing_auto_display = false;

    public function auto_display_map($content) {
        // Ngăn chặn vòng lặp vô hạn - nếu đang xử lý thì bỏ qua
        if (self::$processing_auto_display) {
            return $content;
        }
        
        // Chỉ hiển thị trên single post và khi auto display được bật
        if (!is_single() || get_post_type() != 'post' || get_option('lw_map_auto_display', 'no') != 'yes') {
            return $content;
        }
        
        // Kiểm tra xem có điểm nào không
        $raw_points = get_option('lw_map_points', []);
        if (empty($raw_points) || !is_array($raw_points)) {
            return $content; // Không hiển thị map nếu không có điểm
        }
        
        // Đánh dấu đang xử lý để tránh vòng lặp
        self::$processing_auto_display = true;
        
        try {
            $shortcode_tag = get_option('lw_map_shortcode_tag', 'lw_map');
            $map_content = do_shortcode('[' . $shortcode_tag . ']');
            
            // Chỉ thêm nếu shortcode trả về nội dung hợp lệ
            if (!empty($map_content) && $map_content !== '[' . $shortcode_tag . ']') {
                $content .= $map_content;
            }
        } catch (Exception $e) {
            // Nếu có lỗi, chỉ trả về content gốc
            error_log('LW Map Auto Display Error: ' . $e->getMessage());
        } finally {
            // Đảm bảo reset flag ngay cả khi có lỗi
            self::$processing_auto_display = false;
        }
        
        return $content;
    }
}