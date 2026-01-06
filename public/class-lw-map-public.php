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
        $has_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, $tag );
        $auto_display = get_option('lw_map_auto_display', 'no') == 'yes';

        if ( $has_shortcode || $auto_display ) {
            wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' );
            wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true );
            
            // Public CSS/JS
            // (Bạn có thể tạo file CSS/JS riêng trong folder public nếu muốn, ở đây tôi dùng inline cho gọn vì ít logic frontend)
        }
    }

    public function render_shortcode( $atts ) {
        $raw_points = get_option('lw_map_points', []);
        $all_icons = lw_get_all_icons();
        $points = lw_prepare_points_data($raw_points);
        
        $current_theme = get_option('lw_map_theme', 'osm');
        $themes = lw_get_map_themes();
        $tile_url = isset($themes[$current_theme]) ? $themes[$current_theme]['url'] : $themes['osm']['url'];
        
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
        </style>
        <div class="lw-frontend-wrapper" style="position: relative; margin: 30px 0;">
            <div id="lw-frontend-map" style="height: 600px; width: 100%; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.08); z-index: 1; border:1px solid #ddd;"></div>
        </div>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if(document.getElementById("lw-frontend-map")) {
                var map = L.map("lw-frontend-map").setView([16.0, 108.0], 5);
                L.tileLayer("<?php echo esc_js($tile_url); ?>", { attribution: "&copy; OpenStreetMap" }).addTo(map);

                var points = <?php echo $json_points; ?>;
                var icons = <?php echo $json_icons; ?>;

                points.forEach(function(p) {
                    var iconUrl = icons[0].url;
                    var foundIcon = icons.find(i => i.name === p.icon);
                    if(foundIcon) iconUrl = foundIcon.url;
                    var customIcon = L.icon({ 
                        iconUrl: iconUrl, 
                        iconSize: [25, 25], 
                        iconAnchor: [12.5, 25], 
                        popupAnchor: [0, -25] 
                    });
                    var marker = L.marker([p.lat, p.lng], {icon: customIcon}).addTo(map);
                    
                    var popupHtml = "<div class='lw-card'>";
                    if(p.has_post) {
                        popupHtml += "<div class='lw-card-thumb-wrap'><img src='" + p.thumb + "' class='lw-card-thumb'></div>";
                        popupHtml += "<div class='lw-card-body'>";
                        popupHtml += "<div class='lw-card-header'>";
                        popupHtml += "<h3 class='lw-card-title'>" + p.title + "</h3>";
                        if(p.date) {
                            popupHtml += "<div class='lw-card-date'><i class='far fa-calendar-alt'></i> " + p.date + "</div>";
                        }
                        popupHtml += "</div>";
                        if(p.excerpt) {
                            popupHtml += "<div class='lw-card-excerpt'>" + p.excerpt + "</div>";
                        }
                        popupHtml += "<a href='" + p.link + "' target='_blank' class='lw-card-btn'>XEM CHI TIẾT</a>";
                        popupHtml += "</div>";
                    } else {
                        popupHtml += "<div class='lw-card-body'>";
                        popupHtml += "<div class='lw-card-header'>";
                        popupHtml += "<h3 class='lw-card-title'>" + p.title + "</h3>";
                        popupHtml += "</div>";
                        if(p.link) popupHtml += "<a href='" + p.link + "' target='_blank' class='lw-card-btn'>XEM LIÊN KẾT</a>";
                        popupHtml += "</div>";
                    }
                    popupHtml += "</div>";
                    marker.bindPopup(popupHtml, { maxWidth: 320, minWidth: 320, className: 'lw-map-popup' });
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function auto_display_map($content) {
        if (is_single() && get_post_type() == 'post' && get_option('lw_map_auto_display', 'no') == 'yes') {
            $shortcode_tag = get_option('lw_map_shortcode_tag', 'lw_map');
            $content .= do_shortcode('[' . $shortcode_tag . ']');
        }
        return $content;
    }
}