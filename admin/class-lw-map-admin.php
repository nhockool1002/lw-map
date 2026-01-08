<?php
class LW_Map_Admin {

    public function init() {
        add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_init', array( $this, 'handle_save' ) );
        add_action( 'before_delete_post', array( $this, 'delete_associated_marker' ) );
    }

    public function add_plugin_menu() {
        add_menu_page(
            'Bản đồ hành trình', 
            'LW Map', 
            'manage_options', 
            'lw-map-manager', 
            array( $this, 'display_plugin_admin_page' ), 
            'dashicons-location-alt', 
            20
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_lw-map-manager' !== $hook ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style( 'bs5-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' );
        wp_enqueue_script( 'bs5-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [], null, true );
        wp_enqueue_style( 'fa-css', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' );
        
        // Load map libraries based on selected map type
        $map_type = get_option('lw_map_type', 'mapbox'); // Default là mapbox
        
        if ($map_type === 'mapbox') {
            // Mapbox GL JS
            wp_enqueue_style( 'mapbox-gl-css', 'https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.css' );
            wp_enqueue_script( 'mapbox-gl-js', 'https://api.mapbox.com/mapbox-gl-js/v3.0.1/mapbox-gl.js', [], null, true );
            // Mapbox Geocoder
            wp_enqueue_style( 'mapbox-geocoder-css', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css' );
            wp_enqueue_script( 'mapbox-geocoder-js', 'https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.min.js', ['mapbox-gl-js'], null, true );
        } else {
            // Leaflet
            wp_enqueue_style( 'leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' );
            wp_enqueue_script( 'leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], null, true );
            wp_enqueue_script( 'leaflet-geocoder-js', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js', ['leaflet-js'], null, true );
            wp_enqueue_style( 'leaflet-geocoder-css', 'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css' );
        }

        wp_enqueue_style( 'lw-map-admin-css', LW_MAP_PLUGIN_URL . 'admin/css/lw-map-admin.css', [], LW_MAP_VERSION );
        wp_enqueue_script( 'lw-map-admin-js', LW_MAP_PLUGIN_URL . 'admin/js/lw-map-admin.js', ['jquery'], LW_MAP_VERSION, true );

        // Dynamic Colors Logic (inline because it depends on saved options)
        $saved_grad_key = get_option('lw_map_gradient', 'default');
        $grads = lw_get_gradients(); // Helper func from includes
        $g_css = isset($grads[$saved_grad_key]) ? $grads[$saved_grad_key]['css'] : $grads['default']['css'];
        $g_hex = isset($grads[$saved_grad_key]) ? $grads[$saved_grad_key]['hex'] : $grads['default']['hex'];
        
        $custom_css = ":root { --lw-grad: $g_css; --lw-hex: $g_hex; }";
        wp_add_inline_style( 'lw-map-admin-css', $custom_css );

        // Localize Script
        $raw_points = get_option('lw_map_points', []);
        foreach($raw_points as &$p) { $p['status'] = 'saved'; }
        
        $map_type = get_option('lw_map_type', 'mapbox');
        $mapbox_key = lw_get_mapbox_api_key();
        
        if ($map_type === 'mapbox') {
            $current_style = get_option('lw_map_mapbox_style', 'streets');
            $mapbox_styles = lw_get_mapbox_styles();
            $current_style_id = isset($mapbox_styles[$current_style]) ? $mapbox_styles[$current_style]['style'] : $mapbox_styles['streets']['style'];
            
            wp_localize_script( 'lw-map-admin-js', 'lwMapData', [
                'points' => $raw_points,
                'pointsWithMeta' => lw_prepare_points_data($raw_points),
                'allIcons' => lw_get_all_icons(),
                'allPosts' => lw_get_post_list_for_js(),
                'mapType' => 'mapbox',
                'mapboxKey' => $mapbox_key,
                'mapboxStyle' => $current_style_id
            ]);
        } else {
            $current_theme = get_option('lw_map_theme', 'osm');
            $map_themes = lw_get_map_themes();
            $current_tile_url = isset($map_themes[$current_theme]) ? $map_themes[$current_theme]['url'] : $map_themes['osm']['url'];
            
            wp_localize_script( 'lw-map-admin-js', 'lwMapData', [
                'points' => $raw_points,
                'pointsWithMeta' => lw_prepare_points_data($raw_points),
                'allIcons' => lw_get_all_icons(),
                'allPosts' => lw_get_post_list_for_js(),
                'mapType' => 'leaflet',
                'tileUrl' => $current_tile_url
            ]);
        }
    }

    public function handle_save() {
        if ( ! isset( $_POST['lw_action'] ) ) return;
        if ( ! current_user_can( 'manage_options' ) ) return;

        // Save Icons Logic
        if ($_POST['lw_action'] == 'save_icons') {
            check_admin_referer('lw_save_icons_nonce');
            $icons = [];
            if(isset($_POST['icon_name'])) {
                for($i = 0; $i < count($_POST['icon_name']); $i++){
                    if(!empty($_POST['icon_url'][$i])){
                        $icons[] = [
                            'name' => sanitize_text_field($_POST['icon_name'][$i]),
                            'url'  => esc_url_raw($_POST['icon_url'][$i])
                        ];
                    }
                }
            }
            update_option('lw_map_icons', $icons);
            add_settings_error('lw_map_msg', 'lw_map_msg', 'Đã lưu danh sách Icon thành công!', 'success');
        }

        // Save Points Logic
        if ($_POST['lw_action'] == 'save_points') {
            check_admin_referer('lw_save_points_nonce');
            $points = [];
            if(isset($_POST['p_lat'])) {
                for($i = 0; $i < count($_POST['p_lat']); $i++){
                    $points[] = [
                        'lat'   => sanitize_text_field($_POST['p_lat'][$i]),
                        'lng'   => sanitize_text_field($_POST['p_lng'][$i]),
                        'title' => sanitize_text_field($_POST['p_title'][$i]),
                        'link'  => esc_url_raw($_POST['p_link'][$i]),
                        'icon'  => sanitize_text_field($_POST['p_icon'][$i])
                    ];
                }
            }
            update_option('lw_map_points', $points);
            add_settings_error('lw_map_msg', 'lw_map_msg', 'Đã lưu dữ liệu bản đồ!', 'success');
        }

        // Save Settings Logic
        if ($_POST['lw_action'] == 'save_settings') {
            check_admin_referer('lw_save_settings_nonce');
            
            // Users
            $allowed_ids = [];
            if(!empty($_POST['allowed_users_ids'])) {
                $allowed_ids = array_map('intval', explode(',', $_POST['allowed_users_ids']));
            }
            update_option('lw_map_allowed_users', $allowed_ids);
    
            // Map Type (Mapbox or Leaflet)
            if(isset($_POST['map_type'])) {
                $map_type = sanitize_text_field($_POST['map_type']);
                if(in_array($map_type, ['mapbox', 'leaflet'])) {
                    update_option('lw_map_type', $map_type);
                }
            }
            
            // Mapbox API Key
            if(isset($_POST['mapbox_key'])) {
                update_option('lw_map_mapbox_key', sanitize_text_field($_POST['mapbox_key']));
            }
            
            // Theme & Style (riêng cho từng loại map)
            if(isset($_POST['map_theme'])) update_option('lw_map_theme', sanitize_text_field($_POST['map_theme']));
            if(isset($_POST['mapbox_style'])) update_option('lw_map_mapbox_style', sanitize_text_field($_POST['mapbox_style']));
            if(isset($_POST['map_gradient'])) update_option('lw_map_gradient', sanitize_text_field($_POST['map_gradient']));
    
            // Shortcode
            if(isset($_POST['shortcode_tag'])) {
                $tag = sanitize_text_field($_POST['shortcode_tag']);
                if(empty($tag)) $tag = 'lw_map';
                update_option('lw_map_shortcode_tag', $tag);
            }
    
            // Auto Display
            $auto = isset($_POST['auto_display']) ? sanitize_text_field($_POST['auto_display']) : 'no';
            update_option('lw_map_auto_display', $auto);
            
            add_settings_error('lw_map_msg', 'lw_map_msg', 'Đã lưu cài đặt Plugin!', 'success');
        }
    }

    public function delete_associated_marker($postid) {
       $post_type = get_post_type($postid);
       if ($post_type !== 'post' && $post_type !== 'page') return;
       $permalink = get_permalink($postid);
       $points = get_option('lw_map_points', []);
       $original_count = count($points);
       $points = array_filter($points, function($p) use ($permalink) {
           return rtrim($p['link'], '/') !== rtrim($permalink, '/');
       });
       if (count($points) < $original_count) update_option('lw_map_points', array_values($points));
    }

    public function display_plugin_admin_page() {
        // Data Preparation for View
        $all_icons = lw_get_all_icons();
        $raw_points = get_option('lw_map_points', []);
        
        $custom_icons = array_filter($all_icons, function($i){ return empty($i['is_default']); });
        $default_icons = array_filter($all_icons, function($i){ return !empty($i['is_default']); });
        
        // Settings Data
        $allowed_users = get_option('lw_map_allowed_users', []);
        $map_type = get_option('lw_map_type', 'mapbox'); // Default là mapbox
        $current_theme = get_option('lw_map_theme', 'osm');
        $current_mapbox_style = get_option('lw_map_mapbox_style', 'streets');
        $current_gradient = get_option('lw_map_gradient', 'default');
        $shortcode_tag = get_option('lw_map_shortcode_tag', 'lw_map');
        $auto_display = get_option('lw_map_auto_display', 'no');
        $mapbox_key = get_option('lw_map_mapbox_key', lw_get_mapbox_api_key());
        
        $map_themes = lw_get_map_themes();
        $mapbox_styles = lw_get_mapbox_styles();
        $gradients = lw_get_gradients();
        // Shuffle gradients để hiển thị ngẫu nhiên (giữ nguyên key để không ảnh hưởng đến giá trị đã lưu)
        $gradient_keys = array_keys($gradients);
        shuffle($gradient_keys);
        $shuffled_gradients = [];
        foreach($gradient_keys as $key) {
            $shuffled_gradients[$key] = $gradients[$key];
        }
        $gradients = $shuffled_gradients;
        $users = get_users(['fields' => ['ID', 'display_name', 'user_email']]);
        $can_access_settings = lw_can_access_settings();

        // Load the view
        include LW_MAP_PLUGIN_DIR . 'admin/partials/lw-map-admin-display.php';
    }
}