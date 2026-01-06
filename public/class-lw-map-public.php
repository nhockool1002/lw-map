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
            .lw-card { border: none; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
            .lw-card-thumb-wrap { height: 140px; border-radius: 6px 6px 0 0; overflow: hidden; position: relative; }
            .lw-card-thumb { width: 100%; height: 100%; object-fit: cover; }
            .lw-card-body { padding: 15px; }
            .lw-card-title { font-size: 16px; font-weight: 700; margin: 0 0 5px 0; color: #1e293b; }
            .lw-card-excerpt { font-size: 13px; color: #64748b; margin-bottom: 10px; }
            .lw-card-btn { color: #fff !important; text-decoration: none !important; display: block; text-align: center; padding: 8px 0; border-radius: 6px; font-size: 13px; font-weight: 600; }
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
                        popupHtml += "<h3 class='lw-card-title'>" + p.title + "</h3>";
                        popupHtml += "<div class='lw-card-excerpt'>" + p.excerpt + "</div>";
                        popupHtml += "<a href='" + p.link + "' target='_blank' class='lw-card-btn'>XEM BÀI VIẾT</a>";
                        popupHtml += "</div>";
                    } else {
                        popupHtml += "<div class='lw-card-body'>";
                        popupHtml += "<h3 class='lw-card-title' style='margin-bottom:10px'>" + p.title + "</h3>";
                        if(p.link) popupHtml += "<a href='" + p.link + "' target='_blank' class='lw-card-btn'>XEM LIÊN KẾT</a>";
                        popupHtml += "</div>";
                    }
                    popupHtml += "</div>";
                    marker.bindPopup(popupHtml, { maxWidth: 280, minWidth: 280 });
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