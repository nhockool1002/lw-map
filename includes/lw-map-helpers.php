<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function lw_get_all_icons() {
    $defaults = [
        ['name' => 'Blue (Mặc định)',   'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png', 'is_default' => true],
        ['name' => 'Red (Nổi bật)',      'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png', 'is_default' => true],
        ['name' => 'Green (Thiên nhiên)','url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png', 'is_default' => true],
        ['name' => 'Gold (Đặc biệt)',    'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-gold.png', 'is_default' => true],
        ['name' => 'Violet (Mộng mơ)',   'url' => 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-violet.png', 'is_default' => true],
    ];
    $customs = get_option('lw_map_icons', []);
    if (!is_array($customs)) $customs = [];
    return array_merge($defaults, $customs);
}

function lw_get_map_themes() {
    return [
        'osm' => ['name' => 'OpenStreetMap (Chuẩn)', 'url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'],
        'carto_light' => ['name' => 'CartoDB Positron (Sáng)', 'url' => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'],
        'carto_dark' => ['name' => 'CartoDB Dark Matter (Tối)', 'url' => 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'],
        'esri_sat' => ['name' => 'Esri World Imagery (Vệ tinh)', 'url' => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}']
    ];
}

function lw_get_gradients() {
    return [
        'default' => ['name' => 'Ocean Blue', 'css' => 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)', 'hex' => '#2563eb'],
        'sunset'  => ['name' => 'Sunset Orange', 'css' => 'linear-gradient(135deg, #f59e0b 0%, #ea580c 100%)', 'hex' => '#ea580c'],
        'nature'  => ['name' => 'Lush Green', 'css' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)', 'hex' => '#059669'],
        // ... (Giữ nguyên các gradient khác để tiết kiệm không gian hiển thị)
        'coffee'  => ['name' => 'Coffee Bean', 'css' => 'linear-gradient(135deg, #78350f 0%, #451a03 100%)', 'hex' => '#78350f']
    ];
}

function lw_can_access_settings() {
    $current_user_id = get_current_user_id();
    if (current_user_can('administrator') || $current_user_id == 1) return true;
    $allowed_users = get_option('lw_map_allowed_users', []);
    return in_array($current_user_id, $allowed_users);
}

function lw_prepare_points_data($points) {
    if (empty($points)) return [];
    foreach($points as &$p) {
        $p['has_post'] = false;
        $p['thumb'] = ''; 
        if(!empty($p['link'])) {
            $post_id = url_to_postid($p['link']);
            if($post_id) {
                $p['has_post'] = true;
                $p['date'] = get_the_date('d/m/Y', $post_id);
                $excerpt = get_the_excerpt($post_id);
                if(empty($excerpt)) { $post = get_post($post_id); $excerpt = strip_tags($post->post_content); }
                $p['excerpt'] = wp_trim_words($excerpt, 20, '...');
                $thumb = get_the_post_thumbnail_url($post_id, 'medium'); 
                if(!$thumb) $thumb = 'https://via.placeholder.com/300x150/e0e0e0/999999?text=No+Image';
                $p['thumb'] = $thumb;
            }
        }
    }
    return $points;
}

function lw_get_post_list_for_js() {
    $args = ['numberposts' => -1, 'post_status' => 'publish', 'post_type' => 'post', 'orderby' => 'date', 'order' => 'DESC'];
    $posts = get_posts($args);
    $data = [];
    foreach ($posts as $p) {
        $data[] = ['id' => $p->ID, 'title' => $p->post_title, 'link' => get_permalink($p->ID)];
    }
    return $data;
}